<?php

namespace App\Exports;

use App\ClinikScopusBiayaPersesi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Daftar harga layanan sebagai lembar kerja.
 *
 * Berdampingan dengan unduhan PDF, tidak menggantikannya: PDF untuk dibagikan
 * dan dilampirkan, lembar kerja untuk dihitung — menyusun penawaran, menandai
 * harga yang akan naik, atau menyalinnya ke proposal.
 *
 * Rupanya SATU CETAKAN dengan ekspor Pendaftar Layanan dan Data Pelanggan.
 * Tiga berkas yang keluar dari sistem yang sama tidak boleh terbaca seperti
 * datang dari tiga tempat.
 *
 * FromArray, bukan FromCollection: tiap baris dirakit dari katalog layanan
 * DITAMBAH barisan tarifnya, dan fasilitasnya perlu diratakan jadi satu sel.
 */
class TarifLayananExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /** @param list<array<string, mixed>> $baris */
    public function __construct(
        private array $baris,
        private array $saringan = [],
    ) {}

    public function title(): string
    {
        return 'Tarif Layanan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Layanan',
            'Varian',
            'Satuan',
            'Tarif',
            'PPN (%)',
            'Total dibayar',
            'Potongan alumni (%)',
            'Rombongan mulai',
            'Potongan rombongan (%)',
            'Fasilitas',
            'Berlaku sejak',
            'Disetel oleh',
        ];
    }

    public function array(): array
    {
        $keluar = [];

        foreach ($this->baris as $i => $b) {
            /** @var ClinikScopusBiayaPersesi $t */
            $t = $b['tarif'];

            $keluar[] = [
                $i + 1,
                $b['nama'],
                $b['namaVarian'] ?: '—',
                $b['satuan'],
                (int) $t->biaya_persesi,
                $t->ppn_persen ?: null,
                $t->total_dibayar,

                // Nol ditulis tanda pisah, bukan angka 0: potongan yang memang
                // belum disetel berbeda artinya dari potongan nol persen, dan
                // kolom penuh angka nol terbaca seperti aturan yang berlaku.
                (int) $t->diskon_alumni_persen ?: '—',
                (int) $t->diskon_rombongan_min ?: '—',
                (int) $t->diskon_rombongan_persen ?: '—',

                /*
                 * Fasilitas diratakan jadi satu sel bernomor.
                 *
                 * Satu baris per fasilitas akan membuat satu layanan memakan
                 * tujuh baris dan kolom harganya kosong di enam di antaranya —
                 * lembar seperti itu tidak bisa disaring maupun dijumlah.
                 */
                $t->daftar_fasilitas === []
                    ? '—'
                    : implode("\n", array_map(
                        fn ($f, $n) => ($n + 1) . '. ' . $f,
                        $t->daftar_fasilitas,
                        array_keys($t->daftar_fasilitas)
                    )),

                $t->berlaku_mulai?->format('d/m/Y') ?? '—',
                $t->penginput?->full_name ?: '—',
            ];
        }

        return $keluar;
    }

    /** Huruf lajur sebuah kepala kolom; 'A' kalau kepalanya tidak ada. */
    private function lajur(string $kepala): string
    {
        $i = array_search($kepala, $this->headings(), true);

        return $i === false ? 'A' : Coordinate::stringFromColumnIndex($i + 1);
    }

    /**
     * Rupa lembarnya SAMA PERSIS dengan ekspor Pendaftar Layanan.
     *
     * Lajurnya dihitung dari jumlah kepala kolom, bukan ditulis tetap:
     * menambah satu kolom membuat pita dan kepala kolomnya berhenti tepat
     * sebelum kolom terakhir — tanpa galat apa pun.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                /*
                 * Keterangan berkas disisipkan DI ATAS kepala kolom.
                 *
                 * Daftar harga justru berkas yang paling sering diteruskan ke
                 * calon peserta, dan tanpa tanggalnya tidak ada yang tahu
                 * harga itu masih berlaku atau sudah lewat.
                 */
                $lembar->insertNewRowBefore(1, 3);

                $berppn = 0;

                foreach ($this->baris as $b) {
                    if ((int) $b['tarif']->ppn_persen > 0) {
                        $berppn++;
                    }
                }

                $akhirLajur = Coordinate::stringFromColumnIndex(count($this->headings()));
                $barisTerakhir = 4 + count($this->baris);

                $lembar->setCellValue('A1', 'Daftar Harga Layanan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . count($this->baris) . ' tarif berlaku'
                    . ' · ' . ($berppn > 0 ? $berppn . ' dikenakan PPN' : 'tanpa PPN'));
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan === []
                    ? 'seluruh tarif yang sedang berlaku'
                    : implode(' · ', array_map(
                        fn ($nama, $nilai) => $nama . ': ' . $nilai,
                        array_keys($this->saringan),
                        $this->saringan
                    ))));

                /*
                 * Ketiga baris kepala DIGABUNG selebar tabelnya. Tanpa itu
                 * ShouldAutoSize mengukur lajur A dari kalimat terpanjang di
                 * A1-A3, dan lajur nomor urut jadi selebar puluhan huruf.
                 */
                foreach (['1', '2', '3'] as $n) {
                    $lembar->mergeCells('A' . $n . ':' . $akhirLajur . $n);
                }

                $lembar->getStyle('A1:' . $akhirLajur . '3')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(16)
                    ->getColor()->setRGB('312E81');
                $lembar->getStyle('A2')->getFont()->setSize(10)->setBold(true)
                    ->getColor()->setRGB('4F46E5');
                $lembar->getStyle('A3')->getFont()->setSize(10)->setItalic(true)
                    ->getColor()->setRGB('64748B');

                $lembar->getStyle('A3:' . $akhirLajur . '3')
                    ->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('4F46E5');

                $lembar->getRowDimension(1)->setRowHeight(30);
                $lembar->getRowDimension(2)->setRowHeight(18);
                $lembar->getRowDimension(3)->setRowHeight(20);

                // ------------------------------------------------ kepala kolom
                $lembar->getStyle('A4:' . $akhirLajur . '4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $lembar->getRowDimension(4)->setRowHeight(26);

                // ------------------------------------------------------- isi
                if ($this->baris !== []) {
                    $lembar->getStyle('A5:' . $akhirLajur . $barisTerakhir)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Baris berselang-seling; mata kehilangan barisnya pada
                    // lembar selebar tiga belas kolom tanpa pembeda apa pun.
                    for ($r = 6; $r <= $barisTerakhir; $r += 2) {
                        $lembar->getStyle('A' . $r . ':' . $akhirLajur . $r)
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }

                    // Nominal dibaca sebagai uang, bukan deret angka telanjang.
                    foreach (['Tarif', 'Total dibayar'] as $uang) {
                        $l = $this->lajur($uang);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getNumberFormat()->setFormatCode('#,##0');
                    }

                    foreach (['No.', 'PPN (%)', 'Potongan alumni (%)', 'Rombongan mulai',
                        'Potongan rombongan (%)', 'Berlaku sejak'] as $tengah) {
                        $l = $this->lajur($tengah);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    /*
                     * Kolom fasilitas DIBATASI lebarnya dan dibungkus.
                     *
                     * Isinya daftar bernomor berbaris banyak; dibiarkan
                     * autosize, lajurnya selebar fasilitas terpanjang dan
                     * seluruh kolom lain terdorong keluar layar.
                     */
                    $lFasilitas = $this->lajur('Fasilitas');

                    $lembar->getColumnDimension($lFasilitas)->setAutoSize(false)->setWidth(52);
                    $lembar->getStyle($lFasilitas . '5:' . $lFasilitas . $barisTerakhir)
                        ->getAlignment()->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                // Dibekukan di bawah kepala kolom supaya ia tetap terlihat saat
                // digulung ke samping maupun ke bawah.
                $lembar->freezePane('A5');
            },
        ];
    }
}
