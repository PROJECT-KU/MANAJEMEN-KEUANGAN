<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Menonaktifkan angkatan yang masih "Aktif" padahal tanggalnya sudah lewat.
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

    protected $description = 'Menonaktifkan angkatan yang tanggalnya sudah lewat';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        $lewat = KategoriLayanan::query()
            ->where('status', 'active')
            ->whereRaw('coalesce(selesai, mulai) < ?', [Carbon::today()->toDateString()])
            ->get();

        if ($lewat->isEmpty()) {
            $this->info('Tidak ada angkatan aktif yang tanggalnya sudah lewat.');

            return self::SUCCESS;
        }

        foreach ($lewat as $a) {
            $this->line(sprintf(
                '  %s%s — selesai %s',
                $a->nama,
                $a->nama_ke ? ' ke-' . $a->nama_ke : '',
                ($a->selesai ?: $a->mulai)
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
