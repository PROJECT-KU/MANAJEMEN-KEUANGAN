<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Catatan penghapusan sebagai lembar kerja.
 *
 * Arsipnya hanya bisa dibaca di layar, satu halaman dua puluh baris. Yang
 * dibutuhkan saat menutup buku bukan membacanya satu per satu melainkan
 * MENJUMLAHKAN: berapa uang yang keluar dari pembukuan bulan ini, dan oleh
 * siapa. Itu pekerjaan lembar kerja, bukan halaman web.
 *
 * Potret JSON-nya sengaja TIDAK ikut. Isinya seluruh kolom mentah tiap
 * pendaftaran — termasuk yang tidak pernah dipakai merekap — dan satu sel
 * berisi ribuan huruf membuat berkasnya tidak bisa dibuka dengan nyaman di
 * mana pun. Yang butuh potretnya membukanya di layar arsipnya.
 */
class PendaftaranDihapusExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(private Collection $baris) {}

    public function title(): string
    {
        return 'Catatan Penghapusan';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Dihapus pada',
            'Oleh',
            'Layanan',
            'Nomor pendaftaran',
            'Nama',
            'Email',
            'Status terakhir',
            'Tagihan',
            'Uang yang ikut terhapus',
            'Catatan pembayaran',
            'Baris jejak',
        ];
    }

    public function array(): array
    {
        $hasil = [];

        foreach ($this->baris->values() as $i => $b) {
            $hasil[] = [
                $i + 1,
                optional($b->created_at)->format('d/m/Y H:i'),
                $b->oleh_nama ?: 'tidak tercatat',
                $b->layanan_nama,
                $b->nomor,
                $b->nama,
                $b->email,
                $b->status,
                // Angka, bukan untaian berformat: kolom inilah yang dijumlahkan
                // penerimanya.
                (int) $b->total,
                (int) $b->uang_terhapus,
                (int) $b->jumlah_pembayaran,
                (int) $b->jumlah_jejak,
            ];
        }

        return $hasil;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $acara) {
                $lembar = $acara->sheet->getDelegate();

                $lembar->insertNewRowBefore(1, 3);

                $lembar->setCellValue('A1', 'Catatan Penghapusan — MIS Rumah Scopus');
                $lembar->setCellValue('A2', 'Diunduh ' . now()->format('d/m/Y H:i') . ' WIB'
                    . ' · ' . number_format($this->baris->count(), 0, ',', '.') . ' penghapusan'
                    . ' · uang terhapus Rp '
                    . number_format((int) $this->baris->sum('uang_terhapus'), 0, ',', '.'));
                /*
                 * Disebut di berkasnya sendiri, bukan cuma di layarnya.
                 * Lembar kerja justru berkas yang paling sering diteruskan,
                 * dan penerimanya tidak pernah melihat kalimat di layar asal.
                 */
                $lembar->setCellValue('A3', 'Ini catatan, bukan tong sampah — '
                    . 'pendaftarannya tidak bisa dikembalikan. Potret lengkap tiap baris '
                    . 'ada di layar Catatan Penghapusan.');

                $lembar->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $lembar->getStyle('A2:A3')->getFont()->setSize(10);
                $lembar->getStyle('A4:L4')->getFont()->setBold(true);

                $lembar->freezePane('A5');
            },
        ];
    }
}
