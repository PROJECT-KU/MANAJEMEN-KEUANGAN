<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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

class PresensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $presensi;

    public function __construct($presensi)
    {
        $this->presensi = $presensi;
    }

    public function collection()
    {
        return $this->presensi;
    }

    public function headings(): array
    {
        return [
            ['NO', 'NAMA KARYAWAN', 'TANGGAL PRESENSI', 'WAKTU KEHADIRAN', 'WAKTU KEPULANGAN', 'LAMA KERJA', 'STATUS KEHADIRAN', 'STATUS KEPULANGAN', 'LOKASI PRESENSI']
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        // Pastikan created_at valid
        $tanggalPresensi = '-';
        $waktuHadir = '-';
        if (!empty($row->created_at) && strtotime($row->created_at)) {
            $tanggalPresensi = Carbon::parse($row->created_at)->translatedFormat('l d F Y H:i');
            $waktuHadir = date('H:i', strtotime($row->created_at));
        }

        $waktuPulang = '-';
        $lamaKerja = '-';
        if (!empty($row->time_pulang) && strtotime($row->time_pulang)) {
            $waktuPulang = date('H:i', strtotime($row->time_pulang));
            if (!empty($row->created_at) && strtotime($row->created_at)) {
                $start = strtotime($row->created_at);
                $end = strtotime($row->time_pulang);
                if ($end > $start) {
                    $lamaKerja = gmdate('H:i', $end - $start);
                }
            }
        }

        $lokasi = '-';
        if (!empty($row->latitude) && !empty($row->longitude) && is_numeric($row->latitude) && is_numeric($row->longitude)) {
            $link = "https://www.google.com/maps?q={$row->latitude},{$row->longitude}";
            $lokasi = '=HYPERLINK("' . $link . '","Lihat di Google Maps")';
        }

        return [
            $no,
            $row->full_name ?? '-',
            $tanggalPresensi,
            $waktuHadir,
            $waktuPulang,
            $lamaKerja,
            $row->status ?? '-',
            $row->status_pulang ?? '-',
            $lokasi,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function startCell(): string
    {
        return 'A8'; // Heading mulai dari A8
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $tanggal_awal = request('tanggal_awal');
                $tanggal_akhir = request('tanggal_akhir');

                if ($tanggal_awal && $tanggal_akhir) {
                    $periode = 'Periode: ' . date('d-m-Y', strtotime($tanggal_awal)) . ' s.d. ' . date('d-m-Y', strtotime($tanggal_akhir));
                } else {
                    $periode = 'Periode: ' . date('01-m-Y') . ' s.d. ' . date('t-m-Y');
                }

                // Judul
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'LAPORAN PRESENSI KARYAWAN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Periode
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', $periode);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Garis separator
                $sheet->getStyle('A3:I3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Alamat
                $sheet->mergeCells('A4:I4');
                $sheet->setCellValue('A4', 'Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman, Yogyakarta 55551');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // Kontak
                $sheet->mergeCells('A5:I5');
                $sheet->setCellValue('A5', 'Email : info@rumahscopusfoundation.com Telp : 0812-2688-3280');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Garis separator kedua
                $sheet->getStyle('A6:I6')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Heading kolom
                $sheet->getStyle('A8:I8')->getFont()->setBold(true);
                $sheet->getStyle('A8:I8')->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
