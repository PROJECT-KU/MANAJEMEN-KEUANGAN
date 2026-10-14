<?php

namespace App\Exports;

use App\Support\PesananPelanggan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Daftar pelanggan sebagai lembar kerja.
 *
 * Berdampingan dengan ekspor PDF, tidak menggantikannya: PDF untuk dibaca dan
 * dilampirkan, lembar kerja untuk diolah — disaring, diurutkan, dan disalin ke
 * tempat lain. Daftar pelanggan hampir selalu berakhir di spreadsheet.
 *
 * FromArray, bukan FromCollection: nilainya perlu dirakit dulu (nomor telepon
 * dijaga tetap untaian, jumlah pesanan datang dari sumber lain), dan
 * FromCollection akan menumpahkan seluruh kolom tabel users apa adanya —
 * termasuk kata sandi tersandi dan token ingat-saya.
 */
class PelangganExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(
        private Collection $pelanggan,
        private array $pesanan,
        private array $saringan = [],
    ) {}

    public function title(): string
    {
        return 'Data Pelanggan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama lengkap',
            'Username',
            'Email',
            'Email terverifikasi',
            'Telepon',
            'Status akun',
            'Jumlah pesanan',
            'Pesanan terakhir',
            'Bergabung',
        ];
    }

    public function array(): array
    {
        $baris = [];

        foreach ($this->pelanggan->values() as $i => $orang) {
            // Bawaannya array, bukan null: sebagian besar pelanggan memang
            // belum pernah memesan, dan $p['terakhir'] pada null menimbulkan
            // peringatan yang di Laravel jadi galat dan menggagalkan seluruh
            // unduhan.
            $p = $this->pesanan[$orang->id] ?? ['jumlah' => 0, 'terakhir' => null];

            $baris[] = [
                $i + 1,
                $orang->full_name ?: $orang->username,
                $orang->username,
                $orang->email,
                $orang->email_verified_at ? 'Sudah' : 'Belum',
                /*
                 * Ditulis apa adanya di sini; yang menjaganya tetap untaian
                 * setCellValueExplicit di registerEvents, sama seperti ekspor
                 * Pendaftar Layanan.
                 *
                 * Dulu diawali kutip tunggal, dan itu bekerja — tetapi dua
                 * cara berbeda untuk perkara yang sama di dua berkas ekspor
                 * berarti yang satu bisa diperbaiki sementara yang lain
                 * ketinggalan. Tanpa penjagaan apa pun, "081234567890" dibaca
                 * sebagai bilangan, nol depannya dibuang, dan nomor sepanjang
                 * itu tampil sebagai 8,12346E+10.
                 */
                (string) ($orang->telp ?: ''),
                $orang->status === 'active' ? 'Aktif' : 'Nonaktif',
                $p['jumlah'],
                $p['terakhir']?->format('d/m/Y') ?? '',
                optional($orang->created_at)->format('d/m/Y') ?? '',
            ];
        }

        return $baris;
    }

    /** Huruf lajur sebuah kepala kolom; 'A' kalau kepalanya tidak ada. */
    private function lajur(string $kepala): string
    {
        $i = array_search($kepala, $this->headings(), true);

        return $i === false
            ? 'A'
            : \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    }

    /**
     * Rupa lembarnya SAMA PERSIS dengan ekspor Pendaftar Layanan.
     *
     * Dua berkas yang keluar dari sistem yang sama tidak boleh terbaca seperti
     * datang dari dua tempat. Sebelum ini kepala berkas Pelanggan cuma tiga
     * baris teks polos sementara Pendaftar Layanan punya pita berjudul
     * terpusat, kepala kolom nila, dan baris berselang-seling.
     *
     * Lajurnya dihitung dari jumlah kepala kolom, bukan ditulis 'J': ditulis
     * tetap, menambah satu kolom membuat pita dan kepala kolomnya berhenti
     * tepat sebelum kolom terakhir — tanpa galat apa pun.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                /*
                 * Keterangan berkas disisipkan DI ATAS kepala kolom.
                 *
                 * Tanpa ini, lembar kerja berisi 29 baris terbaca persis
                 * seperti daftar pelanggan yang lengkap — dan lembar kerja
                 * justru berkas yang paling sering diteruskan ke orang lain,
                 * terlepas dari layar tempat ia diunduh.
                 */
                $lembar->insertNewRowBefore(1, 3);

                $aktif = $this->pelanggan->where('status', 'active')->count();

                $memesan = $this->pelanggan->filter(
                    fn ($orang) => ($this->pesanan[$orang->id]['jumlah'] ?? 0) > 0
                )->count();

                $akhirLajur = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    count($this->headings())
                );
                $barisTerakhir = 4 + $this->pelanggan->count();

                $lembar->setCellValue('A1', 'Data Pelanggan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($this->pelanggan->count(), 0, ',', '.') . ' pelanggan'
                    . ' · ' . number_format($aktif, 0, ',', '.') . ' akun aktif'
                    . ' · ' . number_format($memesan, 0, ',', '.') . ' pernah memesan');
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan === []
                    ? 'tanpa saringan (seluruh pelanggan)'
                    : implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($this->saringan),
                        $this->saringan
                    ))));

                /*
                 * Ketiga baris kepala DIGABUNG selebar tabelnya.
                 *
                 * Tanpa itu ShouldAutoSize mengukur lajur A dari kalimat
                 * terpanjang di A1-A3 — lajur A jadi selebar puluhan huruf
                 * untuk menampung nomor urut yang cuma butuh tiga, dan seluruh
                 * kolom lain terdorong jauh ke kanan.
                 */
                foreach (['1', '2', '3'] as $n) {
                    $lembar->mergeCells('A' . $n . ':' . $akhirLajur . $n);
                }

                /*
                 * Kepala berkas DIPUSATKAN, bukan rata kiri: gabungannya
                 * selebar seluruh tabel, jadi rata kiri judulnya menempel di
                 * pojok sementara sisa pitanya membentang kosong — terlihat
                 * seperti sel yang kelupaan diisi.
                 */
                $pita = 'A1:' . $akhirLajur . '3';

                $lembar->getStyle($pita)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEF2FF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(16)
                    ->getColor()->setRGB('312E81');
                $lembar->getStyle('A2')->getFont()->setSize(10)->setBold(true)
                    ->getColor()->setRGB('4F46E5');
                $lembar->getStyle('A3')->getFont()->setSize(10)->setItalic(true)
                    ->getColor()->setRGB('64748B');

                /*
                 * Garis penutup pita, sewarna kepala kolom. Latar pita dan
                 * latar lembar sama-sama terang; tanpa garis ini batas
                 * bawahnya tidak kelihatan dan ketiga baris keterangan
                 * mengambang di atas tabel.
                 */
                $lembar->getStyle('A3:' . $akhirLajur . '3')
                    ->getBorders()->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('4F46E5');

                $lembar->getRowDimension(1)->setRowHeight(30);
                $lembar->getRowDimension(2)->setRowHeight(18);
                $lembar->getRowDimension(3)->setRowHeight(20);

                // ------------------------------------------------ kepala kolom
                $lembar->getStyle('A4:' . $akhirLajur . '4')->applyFromArray([
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
                if ($this->pelanggan->count() > 0) {
                    $lembar->getStyle('A5:' . $akhirLajur . $barisTerakhir)->applyFromArray([
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
                     * membaca "081234567890" sebagai bilangan lalu membuang
                     * nol depannya — dan nomor yang kehilangan nol depannya
                     * tidak bisa dipakai menghubungi siapa pun.
                     */
                    $lajurTelp = $this->lajur('Telepon');

                    foreach ($this->pelanggan->values() as $i => $orang) {
                        $lembar->setCellValueExplicit(
                            $lajurTelp . (5 + $i),
                            (string) ($orang->telp ?? ''),
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }

                    $lembar->getStyle($lajurTelp . '5:' . $lajurTelp . $barisTerakhir)
                        ->getNumberFormat()->setFormatCode('@');

                    // Angka pendek dipusatkan; dibiarkan rata kanan bersama
                    // kolom teks, lajurnya terbaca bergerigi.
                    foreach (['No.', 'Jumlah pesanan'] as $tengah) {
                        $l = $this->lajur($tengah);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Dibekukan di bawah kepala kolom supaya ia tetap terlihat saat
                // digulung — daftar seratus baris tidak bisa dibaca tanpa itu.
                $lembar->freezePane('A5');
            },
        ];
    }
}
