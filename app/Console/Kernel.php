<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendEmail::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Run the 'send:email' command daily at 9:46 AM
        $schedule->command('send:email')->everyMinute();
        $schedule->command('promo:expire')->everyMinute();

        // Pangkas jejak masuk supaya tabelnya tidak tumbuh tanpa batas.
        $schedule->command('aktivitas:pangkas')->dailyAt('02:30');

        // Ingatkan sekali akun yang mendaftar dua hari lalu tapi emailnya
        // belum diverifikasi.
        $schedule->command('verifikasi:ingatkan')->dailyAt('09:00');

        // Tutup angkatan yang tanggalnya sudah lewat. Dijalankan pagi, bukan
        // tengah malam: angkatan yang selesai hari ini baru boleh ditutup
        // setelah harinya benar-benar habis, dan jam 01:10 memberi jarak dari
        // pekerjaan tengah malam lain di peladen yang sama.
        $schedule->command('angkatan:tutup-lewat')->dailyAt('01:10');

        // Lepas kursi Webinar Eksklusif yang dipesan tetapi tidak jadi dibayar.
        // Tiap sepuluh menit, bukan harian: batas bayarnya satu jam, dan kursi
        // yang tertahan semalaman adalah peserta yang batal mendaftar.
        $schedule->command('webinar-eksklusif:kedaluwarsakan')->everyTenMinutes();

        /*
         * Pengingat H-1, pagi hari. Jam 08:00, bukan tengah malam: surat yang
         * tiba pukul 00:05 tenggelam di antara surat semalam, dan yang
         * membacanya pagi-pagi sudah melewatkan satu kesempatan mengingat.
         */
        $schedule->command('webinar-eksklusif:ingatkan')->dailyAt('08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
