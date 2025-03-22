<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GajiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $gaji;

    public function __construct($gaji)
    {
        $this->gaji = $gaji;
    }

    public function collection()
    {
        $user = Auth::user();
        // Filter gaji berdasarkan status
        return collect($this->gaji)->filter(function ($gaji) {
            return $gaji->status !== 'pending'; // Sesuaikan dengan properti status gaji Anda
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'ID TRANSAKSI',
            'NAMA KARYAWAN',
            'NO REKENING',
            'BANK',
            'TOTAL GAJI',
            'TANGGAL PEMBAYARAN'
        ];
    }

    public function map($gaji): array
    {
        // Mengatur nomor urut secara otomatis
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
        $nama_bank = array_key_exists($gaji->bank, $bankNames) ? $bankNames[$gaji->bank] : 'Bank Name Not Found';

        // Format total gaji ke dalam format mata uang rupiah
        $formatted_total = number_format($gaji->total, 0, ',', '.');

        // Format tanggal ke dalam format yang diminta (dd-nama bulan-yyyy jam-menit)
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
        return [
            1 => ['font' => ['bold' => true]],
            'A1:G1' => ['alignment' => ['horizontal' => 'center']],
            'A' => ['alignment' => ['horizontal' => 'center']],
            'B' => ['alignment' => ['horizontal' => 'center']],
            'C' => ['alignment' => ['horizontal' => 'center']],
            'D' => ['alignment' => ['horizontal' => 'center']],
            'E' => ['alignment' => ['horizontal' => 'center']],
            'F' => ['alignment' => ['horizontal' => 'center']],
            'G' => ['alignment' => ['horizontal' => 'center']]
        ];
    }
}
