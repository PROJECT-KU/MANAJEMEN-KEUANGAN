<?php

namespace App\Exports;

use App\PromoLayanan;
use App\Support\DampakPromo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Promo layanan untuk Excel.
 *
 * Menerima daftar yang SUDAH disaring dan diurutkan oleh layarnya — yang
 * diunduh orang hampir selalu yang sedang dilihatnya, bukan seluruh tabel.
 *
 * Kolom dampaknya ikut: berkas promo yang tidak menyebut berapa kali dipakai
 * dan berapa yang dipotong cuma menyalin apa yang sudah terlihat di layar.
 */
class PromoLayananExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $promo)
    {
    }

    public function collection(): Collection
    {
        return $this->promo;
    }

    public function headings(): array
    {
        return [
            'Promo', 'Jenis', 'Potongan', 'Maksimal potongan',
            'Mulai', 'Selesai', 'Keadaan',
            'Angkatan', 'Kuota', 'Terpakai', 'Peserta', 'Total potongan',
            'Dibuat oleh', 'Catatan',
        ];
    }

    /** @param PromoLayanan $p */
    public function map($p): array
    {
        $angka = DampakPromo::untuk((string) $p->getKey());

        return [
            $p->nama,
            PromoLayanan::TIPE[$p->tipe] ?? $p->tipe,

            /*
             * Potongannya diisi di kolom yang SESUAI tipenya, bukan satu kolom
             * campuran. Angka 40 dan 40000 di satu kolom tidak bisa dijumlah
             * maupun diurutkan oleh siapa pun yang membuka berkasnya.
             */
            $p->tipe === PromoLayanan::PERSEN ? $p->persen . '%' : (int) $p->nominal,
            $p->tipe === PromoLayanan::PERSEN && $p->batas_potongan ? (int) $p->batas_potongan : null,

            $p->mulai?->format('d/m/Y'),
            $p->selesai?->format('d/m/Y'),
            $p->keadaan_terbaca,

            (int) ($p->angkatan_count ?? $p->angkatan()->count()),

            // Tanpa batas ditulis tanda pisah, bukan 0: nol berarti habis
            // sejak detik pertama, dan itu hal yang sama sekali berbeda.
            $p->kuota !== null ? (int) $p->kuota : '—',

            $angka['pendaftaran'],
            $angka['peserta'],
            $angka['potongan'] ?: null,

            $p->penginput?->full_name ?: '—',
            $p->catatan ?: '—',
        ];
    }

    public function styles(Worksheet $lembar): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
