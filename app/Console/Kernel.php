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

        /*
         * send:email TIDAK dijadwalkan lagi.
         *
         * Perintahnya bukan kode yang bisa jalan: isinya kerangka yang tidak
         * pernah diselesaikan — `Presensi::where()` yang argumennya cuma
         * komentar "Your conditions here", dua baris berbunyi
         * `$x = // logic to determine $x;`, dan
         * `$request->input(...)` di dalam perintah konsol yang tidak punya
         * permintaan HTTP. Ia gagal pada baris PERTAMA, selalu.
         *
         * Selama penjadwalnya mati karena proc_open, kegagalan itu tidak
         * terlihat. Begitu penjadwalnya diperbaiki, ia menulis satu galat
         * "Too few arguments to function Builder::where()" ke log TIAP MENIT
         * — terukur di produksi 7 Okt 2026 — dan menenggelamkan galat
         * sungguhan yang perlu dibaca.
         *
         * Yang dibuang JADWALNYA, bukan perintahnya: niat aslinya (mengirim
         * pemberitahuan presensi) mungkin masih diinginkan, dan menghapus
         * berkasnya menghilangkan jejak niat itu. Ia tetap bisa dipanggil
         * dengan tangan oleh siapa pun yang hendak menyelesaikannya.
         */
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

        /*
         * Pengingat pembayaran untuk KELIMA layanan.
         *
         * 09:30, sesudah `verifikasi:ingatkan` (09:00): keduanya menyurati
         * orang yang sama kalau ia baru mendaftar, dan dua surat yang tiba
         * dalam detik yang sama lebih mudah diabaikan daripada dua surat
         * yang berjarak.
         *
         * Harian, tetapi yang menentukan seberapa sering satu orang disurati
         * bukan jadwal ini melainkan pilihan --ulang dan --maks di dalam
         * perintahnya: paling cepat tujuh hari sekali, paling banyak tiga
         * kali. Jadwal harian hanya membuat pendaftar yang baru memenuhi
         * syarat tidak perlu menunggu sampai minggu depan.
         */
        $tugas('pendaftaran:ingatkan-bayar')->dailyAt('09:30');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
