<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Riskihajar\Terbilang\Facades\Terbilang;

class GajiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $gaji;
    protected $totalGaji;

    public function __construct($gaji)
    {
        $this->gaji = $gaji;
        $this->totalGaji = collect($gaji)->sum('total');
    }

    public function collection()
    {
        return collect($this->gaji)->filter(function ($gaji) {
            return $gaji->status !== 'pending';
        });
    }

    public function headings(): array
    {
        return [
            ['NO', 'ID TRANSAKSI', 'NAMA KARYAWAN', 'NO REKENING', 'BANK', 'TOTAL GAJI', 'TANGGAL DIBAYARKAN']
        ];
    }

    public function map($gaji): array
    {
        static $row = 0;
        $row++;

        $bankNames = [
            '002' => 'BRI',
            '008' => 'BANK MANDIRI',
            '009' => 'BNI',
            '200' => 'BANK TABUNGAN NEGARA',
            '011' => 'BANK DANAMON',
            '013' => 'BANK PERMATA',
            '014' => 'BCA',
            '016' => 'MAYBANK',
            '019' => 'PANINBANK',
            '022' => 'CIMB NIAGA',
            '023' => 'BANK UOB INDONESIA',
            '028' => 'BANK OCBC NISP',
            '087' => 'BANK HSBC INDONESIA',
            '147' => 'BANK MUAMALAT',
            '153' => 'BANK SINARMAS',
            '426' => 'BANK MEGA',
            '441' => 'BANK BUKOPIN',
            '451' => 'BSI',
            '484' => 'BANK KEB HANA INDONESIA',
            '494' => 'BANK RAYA INDONESIA',
            '506' => 'BANK MEGA SYARIAH',
            '046' => 'BANK DBS INDONESIA',
            '947' => 'BANK ALADIN SYARIAH',
            '950' => 'BANK COMMONWEALTH',
            '213' => 'BANK BTPN',
            '490' => 'BANK NEO COMMERCE',
            '501' => 'BANK DIGITAL BCA',
            '521' => 'BANK BUKOPIN SYARIAH',
            '535' => 'SEABANK INDONESIA',
            '542' => 'BANK JAGO',
            '567' => 'ALLO BANK',
            '110' => 'BPD JAWA BARAT',
            '111' => 'BPD DKI',
            '112' => 'BPD DAERAH ISTIMEWA YOGYAKARTA',
            '113' => 'BPD JAWA TENGAH',
            '114' => 'BPD JAWA TIMUR',
            '115' => 'BPD JAMBI',
            '116' => 'BANK ACEH SYARIAH',
            '117' => 'BPD SUMATERA UTARA',
            '118' => 'BANK NAGARI',
            '119' => 'BPD RIAU KEPRI SYARIAH',
            '120' => 'BPD SUMATERA SELATAN DAN BANGKA BELITUNG',
            '121' => 'BPD LAMPUNG',
            '122' => 'BPD KALIMANTAN SELATAN',
            '123' => 'BPD KALIMANTAN BARAT',
            '124' => 'BPD KALIMANTAN TIMUR DAN KALIMANTAN UTARA',
            '125' => 'BPD KALIMANTAN TENGAH',
            '126' => 'BPD SULAWESI SELATAN DAN SULAWESI BARAT',
            '127' => 'BPD SULAWESI UTARA DAN GORONTALO',
            '128' => 'BANK NTB SYARIAH',
            '129' => 'BPD BALI',
            '130' => 'BPD NUSA TENGGARA TIMUR',
            '131' => 'BPD MALUKU DAN MALUKU UTARA',
            '132' => 'BPD PAPUA',
            '133' => 'BPD BENGKULU',
            '134' => 'BPD SULAWESI TENGAH',
            '135' => 'BPD SULAWESI TENGGARA',
            '137' => 'BPD BANTEN'
        ];

        $nama_bank = $bankNames[$gaji->bank] ?? 'Bank Name Not Found';
        $formatted_date = date('d M Y, H:i', strtotime($gaji->tanggal));

        return [
            $row,
            $gaji->id_transaksi,
            $gaji->full_name,
            $gaji->norek . " ", // Spasi agar tidak jadi format scientific di excel
            $nama_bank,
            'Rp ' . number_format($gaji->total ?? 0, 0, ',', '.'),
            $formatted_date
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

                // 1. LOGIKA PERIODE
                $tanggal_awal = request('tanggal_awal');
                $tanggal_akhir = request('tanggal_akhir');

                if ($tanggal_awal && $tanggal_akhir) {
                    $periode = 'Periode: ' . date('d M Y', strtotime($tanggal_awal)) . ' - ' . date('d M Y', strtotime($tanggal_akhir));
                } else {
                    $periode = 'Periode: ' . date('01 M Y') . ' - ' . date('t M Y');
                }

                // 2. STYLING KOP LAPORAN (MINIMALIST)
                $sheet->setShowGridlines(false);

                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'LAPORAN GAJI KARYAWAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', $periode);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'Rumah Scopus Foundation');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells('A5:G5');
                $sheet->setCellValue('A5', 'Bangunsari, Jl. Bangunsari, Bangun Kerto, Turi, Sleman, Yogyakarta 55551 | info@rumahscopusfoundation.com');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['argb' => 'FF94A3B8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // 3. STYLING HEADER TABEL (CLEAN & SUBTLE BACKGROUND)
                $sheet->getStyle('A8:G8')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF475569']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF8FAFC']
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FFCBD5E1']],
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFCBD5E1']],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $sheet->getRowDimension(8)->setRowHeight(30);

                // 4. STYLING BODY TABEL
                $dataCount = $this->collection()->count();
                $lastDataRow = 8 + $dataCount;

                if ($dataCount > 0) {
                    for ($r = 9; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(24);
                        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_HAIR,
                                    'color' => ['argb' => 'FFE2E8F0']
                                ]
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                    }

                    $sheet->getStyle('A9:B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C9:C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('D9:D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E9:E' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('F9:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('G9:G' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // 5. STYLING TOTAL (DIUBAH MENJADI CENTER)
                $totalRow = $lastDataRow + 1;
                $terbilangRow = $totalRow + 1;

                $formattedTotal = 'Rp ' . number_format($this->totalGaji, 0, ',', '.');

                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL GAJI DIBAYARKAN');
                $sheet->mergeCells("F{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("F{$totalRow}", $formattedTotal);

                $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF94A3B8']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF94A3B8']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // Ubah teks total dan angkanya menjadi Center
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($totalRow)->setRowHeight(30);

                // 6. STYLING TERBILANG (DIUBAH MENJADI CENTER)
                $terbilang = \Riskihajar\Terbilang\Facades\Terbilang::make($this->totalGaji);
                $sheet->mergeCells("A{$terbilangRow}:G{$terbilangRow}");
                $sheet->setCellValue("A{$terbilangRow}", 'Terbilang: ' . ucwords($terbilang) . ' Rupiah');

                $sheet->getStyle("A{$terbilangRow}:G{$terbilangRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER, // Ubah ke Center
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $sheet->getRowDimension($terbilangRow)->setRowHeight(25);
            },
        ];
    }
}
