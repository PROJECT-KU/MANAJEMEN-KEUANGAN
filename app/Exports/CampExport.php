<?php

namespace App\Exports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CampExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate, $endDate, $status)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function collection()
    {
        $user = Auth::user();

        $query = DB::table('camp')
            ->select('camp.id_transaksi', 'camp.title', 'camp.camp_ke', 'camp.total_uang_masuk', 'camp.total', 'camp.persentase_keuntungan', 'camp.tanggal', 'camp.tanggal_akhir')
            ->leftJoin('users', 'camp.user_id', '=', 'users.id')
            ->where('users.company', $user->company);

        // Jika tidak ada filter tanggal, otomatis hanya ambil yang "terbayar"
        if (!$this->startDate || !$this->endDate) {
            $query->where('camp.status', 'terbayar');
        } else {
            $query->whereBetween('camp.tanggal', [$this->startDate, $this->endDate]);

            // Jika status 'terbayar' dipilih, filter hanya yang terbayar
            if ($this->status === 'terbayar') {
                $query->where('camp.status', 'terbayar');
            }
        }

        return $query->orderBy('camp.created_at', 'DESC')->get();
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Camp',
            'Nama Camp-Ke',
            'Total Uang Masuk',
            'Total',
            'Persentase Keuntungan',
            'Tanggal Mulai Camp',
            'Tanggal Akhir Camp'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id_transaksi,
            $row->title,
            '# ' . $row->camp_ke,
            $this->formatRupiah($row->total_uang_masuk),
            $this->formatRupiah($row->total),
            $this->formatPersen($row->persentase_keuntungan),
            Carbon::parse($row->tanggal)->translatedFormat('d F Y'),
            Carbon::parse($row->tanggal_akhir)->translatedFormat('d F Y'),
        ];
    }

    private function formatRupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    private function formatPersen($angka)
    {
        return rtrim(rtrim(number_format($angka, 1, ',', '.'), '0'), ',') . '%';
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
            'G' => ['alignment' => ['horizontal' => 'center']],
            'H' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
