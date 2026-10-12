<?php

namespace App\Exports;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Pendaftar seluruh layanan sebagai lembar kerja.
 *
 * Berdampingan dengan ekspor PDF, tidak menggantikannya: PDF untuk dibaca dan
 * dilampirkan, lembar kerja untuk diolah — disaring, dijumlahkan, dan dipakai
 * menyusun daftar hadir atau sertifikat.
 *
 * FromArray, bukan FromCollection: nilainya perlu dirakit dulu — status mentah
 * diterjemahkan, nomor telepon dijaga tetap untaian, dan nama layanan dibaca
 * dari katalog.
 */
class PendaftaranLayananExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $baris,
        private array $katalog,
        private array $saringan = [],
    ) {}

    public function title(): string
    {
        return 'Pendaftar Layanan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Layanan',
            'Nomor pendaftaran',
            'Nama',
            'Email',
            'Telepon',
            'Afiliasi',
            'Sesi / angkatan',
            'Angkatan ke-',
            'Jumlah orang',
            'Total bayar',
            'Kode unik',
            'Kode diskon',
            'Keadaan',
            'Status asli',
            'Bukti bayar',
            'Tanggal daftar',
        ];
    }

    public function array(): array
    {
        $hasil = [];

        foreach ($this->baris->values() as $i => $b) {
            $keadaan = Pendaftaran::keadaanDari($b->status);
            $bukti = Pendaftaran::buktiBaris($b);

            $hasil[] = [
                $i + 1,
                $this->katalog[$b->layanan]['nama'] ?? $b->layanan,
                $b->nomor,
                $b->nama_orang,
                $b->email,
                /*
                 * TANPA kutip tunggal di depannya.
                 *
                 * Kutip itu trik mengetik di Excel, bukan nilai: diserahkan
                 * lewat PhpSpreadsheet ia tertulis apa adanya dan yang
                 * tampil di sel "'0812-2017-7011" — ikut tersalin saat
                 * nomornya di-copy. Nol depannya dijaga dengan cara yang
                 * benar: selnya ditulis ulang sebagai untaian tegas di
                 * registerEvents(), dan lajurnya diberi format teks.
                 */
                (string) ($b->telp ?? ''),
                $b->affiliasi ?? '',
                Pendaftaran::sesiBaris($b) ?? '',
                /*
                 * Nomor angkatan jadi KOLOM TERSENDIRI, bukan disambung ke
                 * nama sesinya.
                 *
                 * Lembar kerja ini diunduh untuk direkap — disaring,
                 * dikelompokkan, dan dijumlahkan. Nomor yang menempel di
                 * dalam untaian nama tidak bisa dipakai mengelompokkan;
                 * sebagai kolom sendiri ia bisa. Dan tetap untaian, bukan
                 * bilangan: nomornya penanda, bukan angka yang dijumlahkan.
                 */
                Pendaftaran::nomorAngkatanBaris($b) ?? '',
                (int) $b->jumlah,
                // Angka, bukan untaian berformat: lembar kerja memang diunduh
                // supaya kolom ini bisa dijumlahkan sendiri oleh penerimanya.
                (int) $b->total,
                $b->kode_unik !== null && $b->kode_unik !== '' ? (int) $b->kode_unik : '',
                $b->kode_diskon ?? '',
                $keadaan === 'lain'
                    ? 'Belum dikenali'
                    : Pendaftaran::KEADAAN[$keadaan]['label'],
                // Status mentah ikut dibawa. Terjemahannya menyatukan sembilan
                // nilai jadi empat keadaan, jadi tanpa kolom ini selisih antar
                // layanan — 'expired' vs 'Pendaftaran Dibatalkan' — hilang dari
                // berkasnya dan tidak bisa ditelusuri lagi.
                $b->status,
                $this->sebutanBukti($bukti),
                Pendaftaran::waktuBaris($b)?->format('d/m/Y H:i') ?? '',
            ];
        }

        return $hasil;
    }

    /**
     * Keadaan bukti bayar dalam satu kata yang jujur.
     *
     * Tiga keadaan, bukan dua: 72 dari 183 nilai menunjuk berkas yang sudah
     * tidak ada di cakram, dan menuliskannya "Ada" membuat penerima berkas
     * ini mengira buktinya masih bisa dibuka.
     */
    private function sebutanBukti(array $bukti): string
    {
        if (! $bukti['nilai']) {
            return 'Belum diunggah';
        }

        return $bukti['ada'] ? 'Ada' : 'Berkasnya hilang';
    }

    /**
     * Lajur mana yang memuat apa, dihitung dari headings().
     *
     * Ditulis sebagai tetapan huruf, satu kolom yang disisipkan di tengah
     * membuat seluruh gaya menunjuk lajur yang keliru — dan gaya yang
     * meleset tidak pernah menimbulkan galat, cuma berkas yang salah warna.
     */
    private function lajur(string $kepala): string
    {
        $i = array_search($kepala, $this->headings(), true);

        return $i === false
            ? 'A'
            : \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                /*
                 * Keterangan berkas disisipkan DI ATAS kepala kolom.
                 *
                 * Tanpa ini, lembar kerja berisi 29 baris tersaring terbaca
                 * persis seperti daftar pendaftar yang lengkap — dan lembar
                 * kerja justru berkas yang paling sering diteruskan ke orang
                 * lain, terlepas dari layar tempat ia diunduh.
                 */
                $lembar->insertNewRowBefore(1, 3);

                $orang = 0;
                $uang = 0;

                foreach ($this->baris as $b) {
                    $orang += (int) $b->jumlah;

                    if (Pendaftaran::keadaanDari($b->status) === 'lunas') {
                        $uang += (int) $b->total;
                    }
                }

                $akhirLajur = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    count($this->headings())
                );
                $barisTerakhir = 4 + $this->baris->count();

                $lembar->setCellValue('A1', 'Pendaftar Layanan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($this->baris->count(), 0, ',', '.') . ' pendaftaran'
                    . ' · ' . number_format($orang, 0, ',', '.') . ' orang'
                    . ' · lunas Rp ' . number_format($uang, 0, ',', '.'));
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan === []
                    ? 'tanpa saringan (seluruh pendaftar)'
                    : implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($this->saringan),
                        $this->saringan
                    ))));

                /*
                 * Ketiga baris kepala DIGABUNG selebar tabelnya.
                 *
                 * Tanpa itu ShouldAutoSize mengukur lajur A dari kalimat
                 * terpanjang di A1–A3 — terukur pada berkas yang benar-benar
                 * terunduh: lajur A selebar 90 huruf, berisi angka "No." yang
                 * cuma butuh tiga, sementara seluruh kolom lain terdorong jauh
                 * ke kanan.
                 */
                foreach (['1', '2', '3'] as $n) {
                    $lembar->mergeCells('A' . $n . ':' . $akhirLajur . $n);
                }

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(14)
                    ->getColor()->setRGB('0F172A');
                $lembar->getStyle('A2:A3')->getFont()->setSize(10)
                    ->getColor()->setRGB('64748B');
                $lembar->getRowDimension(1)->setRowHeight(22);

                // ------------------------------------------------ kepala kolom
                $kepala = 'A4:' . $akhirLajur . '4';

                $lembar->getStyle($kepala)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $lembar->getRowDimension(4)->setRowHeight(26);

                // ------------------------------------------------------- isi
                if ($this->baris->count() > 0) {
                    $isi = 'A5:' . $akhirLajur . $barisTerakhir;

                    $lembar->getStyle($isi)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    /*
                     * Baris berselang-seling. Lembar berisi ratusan baris
                     * dibaca dengan jari di layar, dan mata kehilangan
                     * barisnya tanpa pembeda apa pun.
                     */
                    for ($r = 6; $r <= $barisTerakhir; $r += 2) {
                        $lembar->getStyle('A' . $r . ':' . $akhirLajur . $r)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }

                    /*
                     * Nomor telepon ditulis ULANG sebagai untaian tegas.
                     *
                     * fromArray() melewatkan nilainya ke penilai bawaan, yang
                     * membaca "081100000001" sebagai bilangan lalu membuang
                     * nol depannya. Nomor yang kehilangan nol depannya tidak
                     * bisa dipakai menghubungi siapa pun — dan yang
                     * membacanya tidak punya cara tahu nomor itu pernah utuh.
                     */
                    $lajurTelp = $this->lajur('Telepon');

                    foreach ($this->baris->values() as $i => $b) {
                        $lembar->setCellValueExplicit(
                            $lajurTelp . (5 + $i),
                            (string) ($b->telp ?? ''),
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }

                    $lembar->getStyle($lajurTelp . '5:' . $lajurTelp . $barisTerakhir)
                        ->getNumberFormat()->setFormatCode('@');

                    // Nominal dibaca sebagai uang, bukan deret angka telanjang.
                    $lajurTotal = $this->lajur('Total bayar');
                    $lembar->getStyle($lajurTotal . '5:' . $lajurTotal . $barisTerakhir)
                        ->getNumberFormat()->setFormatCode('#,##0');

                    foreach (['No.', 'Jumlah orang', 'Angkatan ke-', 'Kode unik'] as $tengah) {
                        $l = $this->lajur($tengah);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    /*
                     * Penyaring bawaan Excel. Lembar ini memang diunduh untuk
                     * disaring dan dikelompokkan — memasangnya sendiri satu
                     * langkah yang hampir selalu dikerjakan penerimanya.
                     */
                    /*
                     * Dirakit dari 'A4', BUKAN dari $kepala.
                     *
                     * $kepala sudah berupa rentang ("A4:Q4"), jadi
                     * menyambungnya menghasilkan "A4:Q4:Q9" — rentang rusak
                     * yang diterima PhpSpreadsheet tanpa mengeluh dan
                     * membuat penyaringnya tidak pernah muncul di Excel.
                     * Terukur pada berkas yang benar-benar dihasilkan.
                     */
                    $lembar->setAutoFilter('A4:' . $akhirLajur . $barisTerakhir);
                }

                /*
                 * Lajur "No." dipatok sempit SESUDAH autosize.
                 *
                 * Isinya paling banyak tiga angka; dibiarkan dihitung
                 * otomatis ia mengikuti lebar tulisan "No." berikut kepala
                 * kolomnya yang tebal.
                 */
                $lembar->getColumnDimension($this->lajur('No.'))
                    ->setAutoSize(false)->setWidth(6);

                /*
                 * Lajur teks panjang DIBATASI lebarnya.
                 *
                 * Autosize mengikuti isi terpanjang: terukur pada berkas yang
                 * dihasilkan, lajur Nama selebar 52 huruf karena satu gelar
                 * panjang, dan seluruh kolom sesudahnya terdorong keluar
                 * layar. Dibatasi, isinya tetap utuh — cuma perlu dilebarkan
                 * sendiri kalau memang mau dibaca penuh.
                 */
                foreach (['Nama' => 32, 'Afiliasi' => 30, 'Sesi / angkatan' => 26,
                    'Email' => 26, 'Status asli' => 20] as $kolom => $lebar) {
                    /*
                     * Dipatok TANPA SYARAT, bukan "hanya kalau lebih lebar".
                     *
                     * Saat AfterSheet berjalan, autosize BELUM dihitung —
                     * getWidth() masih mengembalikan -1, jadi syarat
                     * "lebih lebar dari 32" tidak pernah benar dan
                     * pembatasnya diam. Terukur: lajur Nama tetap 51,8
                     * sesudah pembatas bersyarat dipasang.
                     */
                    $lembar->getColumnDimension($this->lajur($kolom))
                        ->setAutoSize(false)->setWidth($lebar);
                }

                // Dibekukan di bawah kepala kolom supaya ia tetap terlihat saat
                // digulung — 187 baris tidak bisa dibaca tanpa itu.
                $lembar->freezePane('A5');
            },
        ];
    }
}
