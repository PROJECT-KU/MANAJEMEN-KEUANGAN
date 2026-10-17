<?php

namespace App\Console\Commands;

use App\WebinarEksklusifPendaftaran;
use Illuminate\Console\Command;

/**
 * Menirukan pemberitahuan pembayaran dari gerbang, UNTUK PENGUJIAN LOKAL.
 *
 * Pemberitahuan DOKU adalah peladen DOKU menelepon peladen kita. `localhost`
 * tidak bisa dihubungi dari luar, jadi di mesin pengembang statusnya
 * SELAMANYA menunggu — bukan lambat, memang tidak ada yang berangkat.
 * Akibatnya seluruh layar sesudah pembayaran (halaman lunas, perayaannya,
 * surat buktinya) tidak pernah bisa dilihat tanpa memasang terowongan.
 *
 * Perintah ini memotong jalan itu: ia mengubah status persis seperti yang
 * dilakukan penerima pemberitahuan, lalu halaman status yang sedang terbuka
 * berbalik sendiri dalam hitungan detik.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini tidak berbahaya
 * ---------------------------------------------------------------------------
 *
 * Hanya bisa dipanggil dari baris perintah — tidak ada rute, tidak ada
 * alamat, tidak ada cara memanggilnya dari peramban. Dan ia MENOLAK jalan
 * di lingkungan produksi: menandai lunas tanpa uang masuk adalah hal yang
 * paling tidak boleh bisa dilakukan sembarangan di sana.
 */
class TirukanBayarWebinarEksklusif extends Command
{
    protected $signature = 'webinar-eksklusif:tirukan-bayar
                            {nomor : Nomor pendaftaran, mis. WE-20261017-0011}
                            {--gagal : Tirukan pembayaran GAGAL, bukan berhasil}';

    protected $description = 'Menirukan pemberitahuan pembayaran DOKU (hanya untuk pengujian lokal)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Perintah ini tidak boleh jalan di produksi.');
            $this->line('Di sana pemberitahuan datang dari DOKU, dan menandai lunas');
            $this->line('tanpa uang masuk bukan sesuatu yang boleh dilakukan dengan tangan.');

            return self::FAILURE;
        }

        $nomor = trim((string) $this->argument('nomor'));

        $pendaftaran = WebinarEksklusifPendaftaran::where('id_transaksi', $nomor)->first();

        if ($pendaftaran === null) {
            $this->error('Pendaftaran "' . $nomor . '" tidak ditemukan.');

            return self::FAILURE;
        }

        if ($pendaftaran->status === 'paid') {
            $this->warn('Pendaftaran ini sudah lunas sejak ' . $pendaftaran->bayar_pada . '.');

            return self::SUCCESS;
        }

        $gagal = (bool) $this->option('gagal');

        /*
         * Kolom yang disentuh SAMA PERSIS dengan yang disentuh penerima
         * pemberitahuan. Kalau di sana bertambah, di sini harus ikut —
         * kalau tidak, yang diuji bukan lagi keadaan yang sebenarnya.
         */
        $pendaftaran->forceFill($gagal
            ? ['status' => 'expired', 'bayar_status' => 'failed']
            : ['status' => 'paid', 'bayar_status' => 'success', 'bayar_pada' => now()]
        )->save();

        $this->info(($gagal ? 'Ditandai GAGAL' : 'Ditandai LUNAS') . ': ' . $nomor);
        $this->line('Halaman statusnya akan berbalik sendiri dalam beberapa detik:');
        $this->line(route('public.webinareksklusif.status', $pendaftaran->getKey()));

        return self::SUCCESS;
    }
}
