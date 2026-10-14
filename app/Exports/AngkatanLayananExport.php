<?php

namespace App\Exports;

use App\KategoriLayanan;
use App\Support\RentangTanggal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Angkatan layanan untuk Excel.
 *
 * Menerima daftar yang SUDAH disaring dan diurutkan oleh layarnya — yang
 * diunduh orang hampir selalu yang sedang dilihatnya, bukan seluruh tabel.
 */
class AngkatanLayananExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private Collection $angkatan,
        private string $saringan = '',
    ) {}

    public function title(): string
    {
        return 'Angkatan Layanan';
    }

    public function collection(): Collection
    {
        return $this->angkatan;
    }

    public function headings(): array
    {
        return [
            'Angkatan', 'Ke-', 'Layanan', 'Varian', 'Lokasi',
            'Tanggal', 'Total kuota', 'Sisa kuota', 'Pendaftar',
            'Biaya', 'Harga promo', 'Kode promo', 'Status', 'Perlu dicek',
        ];
    }

    /** @param KategoriLayanan $a */
    public function map($a): array
    {
        $rupiah = fn ($n) => (int) $n > 0 ? (int) $n : null;

        return [
            $a->nama,
            $a->nama_ke,
            $a->nama_layanan,
            $a->nama_varian ?: '—',
            $a->lokasi ?: '—',
            RentangTanggal::tulis(
                $a->mulai ? Carbon::parse($a->mulai) : null,
                $a->selesai ? Carbon::parse($a->selesai) : null
            ) ?: '—',
            $a->total_kuota !== null ? (int) $a->total_kuota : null,
            $a->sisa_kuota !== null ? (int) $a->sisa_kuota : null,
            $a->jumlah_pendaftar,
            $rupiah($a->biaya),
            // Promo hanya diisi kalau memang berbeda dari biayanya; kalau tidak,
            // kolomnya penuh angka yang mengulang kolom sebelahnya.
            (int) $a->total_biaya > 0 && (int) $a->total_biaya !== (int) $a->biaya
                ? (int) $a->total_biaya : null,
            $a->kode_diskon ?: '—',
            ['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'][$a->status] ?? $a->status,
            /*
             * Daftar yang diunduh untuk DITINDAKLANJUTI kehilangan satu-satunya
             * keterangan yang membuatnya perlu ditindaklanjuti kalau kolom ini
             * tidak ada — orangnya harus kembali ke layar satu per satu untuk
             * tahu baris mana yang bermasalah.
             */
            $a->perlu_dicek ? ucfirst(implode('; ', $a->perlu_dicek)) : '—',
        ];
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
     * Sebelum ini satu-satunya gayanya `1 => ['font' => ['bold' => true]]` —
     * kepala kolom tebal, tanpa keterangan berkas sama sekali. Lembar berisi
     * 29 dari 258 angkatan terbaca persis seperti daftar yang lengkap, dan
     * lembar kerja justru berkas yang paling sering diteruskan ke orang lain.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                $lembar->insertNewRowBefore(1, 3);

                $aktif = $this->angkatan->where('status', 'active')->count();
                $pendaftar = $this->angkatan->sum(fn ($a) => (int) $a->jumlah_pendaftar);

                /*
                 * Dihitung dari baris yang BENAR-BENAR tercetak, bukan dari
                 * seluruh tabel: angka ringkasan yang menghitung seluruh tabel
                 * sementara daftarnya tersaring bercerita tentang kumpulan
                 * yang berbeda dari yang ada di bawahnya.
                 */
                $perluDicek = $this->angkatan->filter(fn ($a) => ! empty($a->perlu_dicek))->count();

                $akhirLajur = Coordinate::stringFromColumnIndex(count($this->headings()));
                $barisTerakhir = 4 + $this->angkatan->count();

                $lembar->setCellValue('A1', 'Angkatan Layanan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($this->angkatan->count(), 0, ',', '.') . ' angkatan'
                    . ' · ' . number_format($aktif, 0, ',', '.') . ' aktif'
                    . ' · ' . number_format($pendaftar, 0, ',', '.') . ' pendaftar'
                    . ' · ' . number_format($perluDicek, 0, ',', '.') . ' perlu dicek');
                $lembar->setCellValue('A3', 'Saringan: ' . ($this->saringan !== ''
                    ? $this->saringan
                    : 'seluruh angkatan, tanpa saringan'));

                /*
                 * Ketiga baris kepala DIGABUNG selebar tabelnya. Tanpa itu
                 * ShouldAutoSize mengukur lajur A dari kalimat terpanjang di
                 * A1-A3, dan lajur nama angkatan jadi selebar puluhan huruf.
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
                if ($this->angkatan->count() > 0) {
                    $lembar->getStyle('A5:' . $akhirLajur . $barisTerakhir)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    for ($r = 6; $r <= $barisTerakhir; $r += 2) {
                        $lembar->getStyle('A' . $r . ':' . $akhirLajur . $r)
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }

                    // Nominal dibaca sebagai uang, bukan deret angka telanjang.
                    foreach (['Biaya', 'Harga promo'] as $uang) {
                        $l = $this->lajur($uang);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getNumberFormat()->setFormatCode('#,##0');
                    }

                    foreach (['Ke-', 'Total kuota', 'Sisa kuota', 'Pendaftar'] as $tengah) {
                        $l = $this->lajur($tengah);
                        $lembar->getStyle($l . '5:' . $l . $barisTerakhir)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    /*
                     * Kolom "Perlu dicek" DIBATASI lebarnya dan dibungkus.
                     *
                     * Isinya kalimat panjang yang kadang memuat dua sebab
                     * sekaligus; dibiarkan autosize, lajurnya selebar kalimat
                     * terpanjang dan seluruh kolom lain terdorong keluar layar.
                     */
                    $lDicek = $this->lajur('Perlu dicek');

                    $lembar->getColumnDimension($lDicek)->setAutoSize(false)->setWidth(46);
                    $lembar->getStyle($lDicek . '5:' . $lDicek . $barisTerakhir)
                        ->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                }

                $lembar->freezePane('A5');
            },
        ];
    }
}
