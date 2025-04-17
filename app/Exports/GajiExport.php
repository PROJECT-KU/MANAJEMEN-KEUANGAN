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
            ['NO', 'ID TRANSAKSI', 'NAMA KARYAWAN', 'NO REKENING', 'BANK', 'TOTAL GAJI', 'TANGGAL PEMBAYARAN']
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
        $formatted_total = number_format($gaji->total, 0, ',', '.');
        $formatted_date = date('d-F-Y H:i', strtotime($gaji->tanggal));

        return [
            $row,
            $gaji->id_transaksi,
            $gaji->full_name,
            $gaji->norek,
            $nama_bank,
            'Rp ' . $formatted_total,
            $formatted_date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function startCell(): string
    {
        return 'A8'; // Heading kolom mulai dari A8, data mulai dari A9
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Periode dari filter (jika ada), jika tidak ambil bulan ini
                $tanggal_awal = request('tanggal_awal');
                $tanggal_akhir = request('tanggal_akhir');

                if ($tanggal_awal && $tanggal_akhir) {
                    $periode = 'Periode: ' . date('d-m-Y', strtotime($tanggal_awal)) . ' s.d. ' . date('d-m-Y', strtotime($tanggal_akhir));
                } else {
                    $firstDay = date('01-m-Y');
                    $lastDay = date('t-m-Y');
                    $periode = 'Periode: ' . $firstDay . ' s.d. ' . $lastDay;
                }

                // Judul
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'LAPORAN GAJI KARYAWAN');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Periode
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A2', $periode);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Garis separator
                $sheet->getStyle('A3:G3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Alamat
                $sheet->mergeCells('A4:G4');
                $sheet->setCellValue('A4', 'Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman, Yogyakarta 55551');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // Kontak
                $sheet->mergeCells('A5:G5');
                $sheet->setCellValue('A5', 'Email : info@rumahscopusfoundation.com Telp : 0812-2688-3280');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Garis separator kedua
                $sheet->getStyle('A6:G6')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Heading kolom
                $sheet->getStyle('A8:G8')->getFont()->setBold(true);
                $sheet->getStyle('A8:G8')->getAlignment()->setHorizontal('center');

                // Hitung jumlah data yang ditampilkan
                $dataCount = $this->collection()->count();
                $lastDataRow = 8 + $dataCount;
                $totalRow = $lastDataRow + 2; // beri 1 baris kosong sebelum total
                $terbilangRow = $totalRow + 1;

                // Format total angka
                $formattedTotal = 'Rp ' . number_format($this->totalGaji, 0, ',', '.');

                // Garis pemisah di atas total
                $sheet->getStyle('A' . ($totalRow - 1) . ':G' . ($totalRow - 1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // TOTAL - merge kolom A sampai E
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->getStyle("A{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal('center');

                // NILAI TOTAL - merge kolom F sampai G
                $sheet->mergeCells("F{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("F{$totalRow}", $formattedTotal);
                $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getAlignment()->setHorizontal('center');

                // Garis pemisah di bawah total
                $sheet->getStyle('A' . ($totalRow + 1) . ':G' . ($totalRow + 1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // TERBILANG - merge kolom A sampai G
                $terbilang = \Riskihajar\Terbilang\Facades\Terbilang::make($this->totalGaji);
                $sheet->mergeCells("A{$terbilangRow}:G{$terbilangRow}");
                $sheet->setCellValue("A{$terbilangRow}", ucwords($terbilang) . ' Rupiah');
                $sheet->getStyle("A{$terbilangRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A{$terbilangRow}")->getFont()->setItalic(true);
            },
        ];
    }
}
