<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class KategoriAnalisisExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $categories;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function collection()
    {
        $tanggal_awal = request('tanggal_awal');
        $tanggal_akhir = request('tanggal_akhir');

        return collect($this->categories)->filter(function ($category) use ($tanggal_awal, $tanggal_akhir) {
            $tanggal_mulai = Carbon::parse($category->mulai);

            if ($tanggal_awal && $tanggal_akhir) {
                return $tanggal_mulai->between(Carbon::parse($tanggal_awal), Carbon::parse($tanggal_akhir));
            }

            return true; // Jika tidak ada filter tanggal, semua data selain draft dikembalikan
        });
    }

    public function headings(): array
    {
        return [
            ['NO', 'NAMA KATEGORI', 'BATCH', 'TANGGAL MULAI', 'TANGGAL SELESAI', 'TOTAL KUOTA', 'SISA KUOTA', 'STATUS']
        ];
    }

    public function map($category): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $category->nama,
            '# ' . $category->nama_ke,
            Carbon::parse($category->mulai)->translatedFormat('d F Y'),
            Carbon::parse($category->selesai)->translatedFormat('d F Y'),
            $category->total_kuota,
            $category->sisa_kuota,
            $category->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $tanggal_awal = request('tanggal_awal');
                $tanggal_akhir = request('tanggal_akhir');

                if ($tanggal_awal && $tanggal_akhir) {
                    $periode = 'Periode: ' .
                        Carbon::parse($tanggal_awal)->translatedFormat('d F Y') .
                        ' s.d. ' .
                        Carbon::parse($tanggal_akhir)->translatedFormat('d F Y');
                } else {
                    $firstDay = Carbon::now()->startOfMonth()->translatedFormat('d F Y');
                    $lastDay = Carbon::now()->endOfMonth()->translatedFormat('d F Y');
                    $periode = 'Periode: ' . $firstDay . ' s.d. ' . $lastDay;
                }

                // Judul
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'DATA KATEGORI ANALISIS BIBLIOMETRIK');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Periode
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', $periode);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Garis separator
                $sheet->getStyle('A3:H3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Alamat
                $sheet->mergeCells('A4:H4');
                $sheet->setCellValue('A4', 'Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman, Yogyakarta 55551');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // Kontak
                $sheet->mergeCells('A5:H5');
                $sheet->setCellValue('A5', 'Email : info@rumahscopusfoundation.com | Telp : 0812-2688-3280');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Garis separator kedua
                $sheet->getStyle('A6:H6')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Heading kolom
                $sheet->getStyle('A8:H8')->getFont()->setBold(true);
                $sheet->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
