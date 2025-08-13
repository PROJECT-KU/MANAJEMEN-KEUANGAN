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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PendaftaranAnalisisBibliometrikExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell
{
    /** @var \Illuminate\Support\Collection */
    protected Collection $datas;

    /** @var Carbon|null */
    protected ?Carbon $tanggalAwal;

    /** @var Carbon|null */
    protected ?Carbon $tanggalAkhir;

    public function __construct($datas, $tanggalAwal = null, $tanggalAkhir = null)
    {
        // pastikan menjadi Collection
        $this->datas        = collect($datas);
        $this->tanggalAwal  = $tanggalAwal ? Carbon::parse($tanggalAwal) : null;
        $this->tanggalAkhir = $tanggalAkhir ? Carbon::parse($tanggalAkhir) : null;
    }

    public function collection()
    {
        // Filtering tanggal sudah dilakukan di controller.
        // Kalau tetap ingin jaga-jaga, aktifkan blok di bawah:
        /*
        if ($this->tanggalAwal && $this->tanggalAkhir) {
            return $this->datas->filter(function ($row) {
                if (empty($row->created_at)) return false;
                $created = Carbon::parse($row->created_at);
                return $created->betweenIncluded($this->tanggalAwal, $this->tanggalAkhir);
            });
        }
        */
        return $this->datas;
    }

    public function headings(): array
    {
        return [[
            'NO',
            'ID TRANSAKSI',
            'NAMA BATCH',
            'TANGGAL PEMESANAN',
            'NAMA PENDAFTAR',
            'EMAIL',
            'AFFILIASI',
            'NOMOR WHATSAPP',
            'JUMLAH PENDAFTAR',
            'BIAYA',
            'PPN',
            'KODE UNIK PEMBAYARAN',
            'NOMINAL DISKON',
            'TOTAL PEMBAYARAN',
            'TANGGAL RESCHEDULE',
            'LINK GRUP WHATSAPP',
            'NOTE',
            'STATUS',
        ]];
    }

    public function map($item): array
    {
        static $row = 0;
        $row++;

        // Tanggal pemesanan
        $tglPemesanan = '-';
        if (!empty($item->created_at) && strtotime($item->created_at)) {
            // gunakan translatedFormat jika ingin bahasa Indonesia (butuh Carbon locale)
            $tglPemesanan = Carbon::parse($item->created_at)->translatedFormat('d F Y');
        }

        // Tanggal reschedule (opsional)
        $tglReschedule = '-';
        if (!empty($item->tanggal_reschedule) && strtotime($item->tanggal_reschedule)) {
            $tglReschedule = Carbon::parse($item->tanggal_reschedule)->translatedFormat('d F Y');
        }

        // Link group WA
        $groupWa = $item->kategori_group_wa ?? ($item->group_wa ?? '-');

        return [
            $row,
            $item->id_transaksi ?? '-',
            trim(($item->kategori_nama ?? '-') . ' #' . ($item->kategori_nama_ke ?? '-')),
            $tglPemesanan,
            $item->nama       ?? '-',
            $item->email      ?? '-',
            $item->affiliasi  ?? '-',
            $item->telp       ?? '-',
            (int)($item->jumlah_pendaftar ?? 0),

            'Rp. ' . number_format((float)($item->biaya ?? 0), 0, ',', '.'),
            'Rp. ' . number_format((float)($item->ppn ?? 0), 0, ',', '.'),
            'Rp. ' . number_format((float)($item->kode_unik ?? 0), 0, ',', '.'),
            'Rp. ' . number_format((float)($item->nominal_diskon ?? 0), 0, ',', '.'),
            'Rp. ' . number_format((float)($item->total_pembayaran ?? 0), 0, ',', '.'),

            $tglReschedule,
            $groupWa,
            $item->note   ?? '-',
            strtoupper((string)($item->status ?? '-')),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bisa tambahkan style tambahan jika perlu.
        return [];
    }

    public function startCell(): string
    {
        // Header tabel akan mulai di row 8
        return 'A8';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Teks periode berdasarkan tanggal yang DIKIRIM dari controller
                if ($this->tanggalAwal && $this->tanggalAkhir) {
                    $periode = 'Periode: ' .
                        $this->tanggalAwal->translatedFormat('d F Y') .
                        ' s.d. ' .
                        $this->tanggalAkhir->translatedFormat('d F Y');
                } else {
                    $firstDay = Carbon::now()->startOfMonth()->translatedFormat('d F Y');
                    $lastDay  = Carbon::now()->endOfMonth()->translatedFormat('d F Y');
                    $periode  = 'Periode: ' . $firstDay . ' s.d. ' . $lastDay;
                }

                // Hitung kolom terakhir otomatis dari headings
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()[0]));

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
