<?php

namespace App\Exports;

use App\KategoriLayanan;
use App\Support\RentangTanggal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Angkatan layanan untuk Excel.
 *
 * Menerima daftar yang SUDAH disaring dan diurutkan oleh layarnya — yang
 * diunduh orang hampir selalu yang sedang dilihatnya, bukan seluruh tabel.
 */
class AngkatanLayananExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $angkatan)
    {
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
            'Biaya', 'Harga promo', 'Kode promo', 'Status',
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
        ];
    }

    public function styles(Worksheet $lembar): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
