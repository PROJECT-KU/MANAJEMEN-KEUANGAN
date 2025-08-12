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

class PendaftaranAnalisisBibliometrikExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $datas;

    public function __construct($datas)
    {
        $this->datas = $datas;
    }

    public function collection()
    {
        $tanggal_awal = request('tanggal_awal');
        $tanggal_akhir = request('tanggal_akhir');

        return collect($this->datas)->filter(function ($datas) use ($tanggal_awal, $tanggal_akhir) {
            $created_at = Carbon::parse($datas->created_at);

            if ($tanggal_awal && $tanggal_akhir) {
                return $created_at->between(Carbon::parse($tanggal_awal), Carbon::parse($tanggal_akhir));
            }

            return true; // Jika tidak ada filter tanggal, semua data selain draft dikembalikan
        });
    }

    public function headings(): array
    {
        return [
            ['NO', 'ID TRANSAKSI', 'NAMA BATCH', 'TANGGAL PEMESANAN', 'NAMA PENDAFTAR', 'EMAIL', 'AFFILIASI', 'NOMOR WHATSAPP', 'JUMLAH PENDAFTAR', 'BIAYA', 'PPN', 'KODE UNIK PEMBAYARAN', 'NOMINAL DISKON', 'TOTAL PEMBAYARAN', 'TANGGAL RESCHEDULE', 'LINK GRUP WHATSAPP', 'NOTE', 'STATUS']
        ];
    }

    public function map($item): array
    {
        static $row = 0;
        $row++;

        return [
            $row, // NO
            $item->id_transaksi, // ID TRANSAKSI
            ($item->kategori_nama ?? '-') . ' #' . ($item->kategori_nama_ke ?? '-'), // NAMA BATCH
            Carbon::parse($item->created_at)->translatedFormat('d F Y'), // TANGGAL PEMESANAN
            $item->nama, // NAMA PENDAFTAR
            $item->email, // EMAIL
            $item->affiliasi, // AFFILIASI
            $item->telp, // NOMOR WHATSAPP
            $item->jumlah_pendaftar, // JUMLAH PENDAFTAR

            'Rp. ' . number_format($item->biaya ?? 0, 0, ',', '.'), // BIAYA
            'Rp. ' . number_format($item->ppn ?? 0, 0, ',', '.'), // PPN
            'Rp. ' . number_format($item->kode_unik ?? 0, 0, ',', '.'), // KODE UNIK PEMBAYARAN
            'Rp. ' . number_format($item->nominal_diskon ?? 0, 0, ',', '.'), // NOMINAL DISKON
            'Rp. ' . number_format($item->total_pembayaran ?? 0, 0, ',', '.'), // TOTAL PEMBAYARAN

            $item->tanggal_reschedule
                ? Carbon::parse($item->tanggal_reschedule)->translatedFormat('d F Y')
                : '-', // TANGGAL RESCHEDULE

            $item->kategori_group_wa ?? $item->group_wa, // LINK GRUP WA
            $item->note, // NOTE
            strtoupper($item->status), // STATUS
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

                // Tentukan kolom terakhir sesuai jumlah kolom headings/map
                $lastColumn = 'R'; // Karena ada 18 kolom dari A sampai R

                // Judul
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', 'DATA KATEGORI ANALISIS BIBLIOMETRIK');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Periode
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->setCellValue('A2', $periode);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Garis separator
                $sheet->getStyle("A3:{$lastColumn}3")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Alamat
                $sheet->mergeCells("A4:{$lastColumn}4");
                $sheet->setCellValue('A4', 'Bangunsari, Jl. Bangunsari, Bangunsari, Bangun Kerto, Turi, Sleman, Yogyakarta 55551');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

                // Kontak
                $sheet->mergeCells("A5:{$lastColumn}5");
                $sheet->setCellValue('A5', 'Email : info@rumahscopusfoundation.com | Telp : 0812-2688-3280');
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Garis separator kedua
                $sheet->getStyle("A6:{$lastColumn}6")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // Heading kolom
                $sheet->getStyle("A8:{$lastColumn}8")->getFont()->setBold(true);
                $sheet->getStyle("A8:{$lastColumn}8")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
