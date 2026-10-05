<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use Illuminate\Console\Command;

/**
 * Menonaktifkan angkatan yang masih "Aktif" padahal tanggal MULAI-nya tiba.
 *
 * Sebelum ini tidak ada apa pun yang menutup angkatan, jadi satu-satunya jalan
 * adalah seseorang membuka layar Angkatan Layanan, melihat lencana "Lewat",
 * lalu menekannya satu per satu. Selama tidak ada yang membuka layar itu,
 * angkatan yang acaranya sudah selesai tetap terpajang di halaman publik dan
 * tetap menerima pendaftar.
 *
 * Yang disentuh HANYA status 'active'. Draf sengaja dibiarkan: draf tidak
 * terpajang di mana pun, jadi menutupnya tidak menyelamatkan apa-apa sementara
 * menghapusnya dari pandangan admin justru menyembunyikan pekerjaan yang
 * belum selesai.
 */
class TutupAngkatanLewat extends Command
{
    protected $signature = 'angkatan:tutup-lewat {--kering : Hanya melaporkan, tidak mengubah apa pun}';

    protected $description = 'Menonaktifkan angkatan yang tanggal mulainya sudah tiba';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        /*
         * Aturannya DIPINJAM dari modelnya, tidak ditulis ulang di sini.
         * Ditulis ulang, perintah ini dan lencana "Sudah mulai" di layar
         * admin bisa menyimpang tanpa ada yang terlihat rusak — yang satu
         * menutup, yang lain tidak menyalahkan apa-apa.
         */
        $lewat = KategoriLayanan::query()
            ->where('status', 'active')
            ->mulainyaSudahTiba()
            ->get();

        if ($lewat->isEmpty()) {
            $this->info('Tidak ada angkatan aktif yang tanggal mulainya sudah tiba.');

            return self::SUCCESS;
        }

        foreach ($lewat as $a) {
            $this->line(sprintf(
                '  %s%s — mulai %s',
                $a->nama,
                $a->nama_ke ? ' ke-' . $a->nama_ke : '',
                $a->mulai
            ));

            if (! $kering) {
                $a->status = 'non active';
                $a->save();
            }
        }

        $this->info($lewat->count() . ' angkatan ' . ($kering ? 'AKAN ' : '') . 'dinonaktifkan.');

        return self::SUCCESS;
    }
}
