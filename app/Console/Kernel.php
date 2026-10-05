<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendEmail::class,
    ];

    /**
     * Tiap tugas dipanggil lewat call(), BUKAN command().
     *
     * command() menjalankan "php artisan ..." sebagai PROSES BARU, dan untuk
     * itu ia butuh proc_open. Di peladen produksi fungsi itu dimatikan —
     * diperiksa langsung 5 Okt 2026: proc_open, exec, shell_exec, passthru,
     * dan popen semuanya DIMATIKAN.
     *
     * Akibatnya setiap tugas terjadwal gagal sebelum sempat mengerjakan
     * apa pun, dan gagalnya tidak terlihat: yang gagal proses anak, sementara
     * schedule:run sendiri melaporkan sukses. Tidak ada yang tahu sampai ada
     * yang menyadari angkatannya tidak pernah tertutup.
     *
     * call() menjalankannya DI DALAM proses yang sama, jadi tidak ada proses
     * anak yang perlu dinyalakan.
     *
     * Masih perlu SATU cron di hPanel yang memanggil `php artisan
     * schedule:run` tiap menit; tanpa itu tidak ada yang membangunkan
     * penjadwalnya sama sekali.
     */
    protected function schedule(Schedule $schedule)
    {
        /*
         * Dinamai, supaya `schedule:list` menyebut perintahnya dan bukan
         * "Closure at Kernel.php:37" tujuh kali — daftar seperti itu tidak
         * bisa dipakai saat mencari tugas mana yang tidak jalan.
         */
        $tugas = fn (string $perintah) => $schedule
            ->call(fn () => Artisan::call($perintah))
            ->name($perintah)
            ->withoutOverlapping();

        $tugas('send:email')->everyMinute();
        $tugas('promo:expire')->everyMinute();

        // Pangkas jejak masuk supaya tabelnya tidak tumbuh tanpa batas.
        $tugas('aktivitas:pangkas')->dailyAt('02:30');

        // Ingatkan sekali akun yang mendaftar dua hari lalu tapi emailnya
        // belum diverifikasi.
        $tugas('verifikasi:ingatkan')->dailyAt('09:00');

        /*
         * Tutup angkatan yang tanggal MULAI-nya sudah tiba.
         *
         * Jam 00:10, bukan 01:10: patokannya kini tanggal mulai, dan
         * pendaftaran yang masih terbuka berjam-jam di hari acaranya sendiri
         * adalah orang yang membayar untuk kursi yang sudah tidak ada.
         */
        $tugas('angkatan:tutup-lewat')->dailyAt('00:10');

        // Lepas kursi Webinar Eksklusif yang dipesan tetapi tidak jadi dibayar.
        // Tiap sepuluh menit, bukan harian: batas bayarnya satu jam, dan kursi
        // yang tertahan semalaman adalah peserta yang batal mendaftar.
        $tugas('webinar-eksklusif:kedaluwarsakan')->everyTenMinutes();

        /*
         * Pengingat H-1, pagi hari. Jam 08:00, bukan tengah malam: surat yang
         * tiba pukul 00:05 tenggelam di antara surat semalam, dan yang
         * membacanya pagi-pagi sudah melewatkan satu kesempatan mengingat.
         */
        $tugas('webinar-eksklusif:ingatkan')->dailyAt('08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
