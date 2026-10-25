<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * Rute di routes/web.php & routes/api.php masih memakai string
     * 'folder\NamaController@method', jadi namespace ini tetap dipakai.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        /*
         * PEMBATAS BERNAMA, dan ini bukan kerapian.
         *
         * `throttle:10,1` tanpa nama TIDAK memberi rute itu jatahnya sendiri.
         * Kuncinya dirakit ThrottleRequests dari DOMAIN dan ALAMAT IP saja —
         * alamat rutenya tidak ikut — jadi SELURUH rute ber-throttle di
         * aplikasi ini berbagi satu penghitung per pengunjung. Angkanya saja
         * yang berbeda.
         *
         * Akibatnya nyata: sepuluh kali memakai isi otomatis borang langsung
         * menghabiskan jatah rute pendaftaran yang batasnya enam, dan orang
         * berikutnya di jaringan yang sama — satu kantor, satu kampus, satu
         * sambungan bersama — ditolak 429 saat menekan "Daftar". Tertangkap
         * 23 Okt 2026 sebagai uji pembatas laju Webinar Eksklusif yang merah
         * HANYA di rangkaian penuh, dan hijau saat dijalankan sendiri.
         *
         * Dengan nama, kuncinya diawali nama ini, jadi kedua jalur isi
         * otomatis punya penghitungnya sendiri — terpisah dari borang
         * pendaftaran, dan tetap berbagi satu sama lain karena memang satu
         * fitur yang sama.
         *
         * Tujuh rute ber-throttle lainnya MASIH berbagi satu penghitung.
         * Dibiarkan apa adanya di sini: memberi masing-masing nama sendiri
         * mengubah perilaku tujuh jalur sekaligus, dan itu perubahan yang
         * perlu diputuskan tersendiri.
         */
        RateLimiter::for('isi-otomatis', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        /*
         * Tiga jalur baru halaman status Scopus Camp, masing-masing
         * berpenghitung sendiri dengan alasan yang berbeda.
         *
         * 'camp-bayar-ulang' — tiap penekanan membuat TAGIHAN SUNGGUHAN di
         * DOKU. Tagihan yang menumpuk membuat panitia menelusuri pembayaran
         * di antara belasan nomor mati, dan jatahnya di DOKU pun terpakai.
         *
         * 'camp-kirim-ulang' — tiap penekanan mengirim SURAT. Tanpa nama,
         * tombol ini ikut menghabiskan jatah borang pendaftaran, dan orang
         * berikutnya di jaringan yang sama ditolak 429 saat menekan
         * "Daftar".
         *
         * 'camp-tautan-saya' — borangnya menerima alamat email ORANG LAIN.
         * Batasnya yang paling ketat: tanpa itu ia bisa dipakai mengirim
         * surat beruntun ke alamat siapa pun, dan yang menerimanya melihat
         * nama kita sebagai pengirimnya.
         */
        RateLimiter::for('camp-bayar-ulang', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('camp-kirim-ulang', function (Request $request) {
            return Limit::perMinute(2)->by($request->ip());
        });

        RateLimiter::for('camp-tautan-saya', function (Request $request) {
            /*
             * Dihitung per ALAMAT EMAIL yang diminta, bukan hanya per IP.
             *
             * Per IP saja, satu orang bisa membanjiri satu alamat dari
             * beberapa jaringan; per email saja, satu penyerang dari satu
             * jaringan bisa menyapu banyak alamat sekaligus. Keduanya
             * dipakai, jadi yang dibatasi dua-duanya.
             */
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perMinute(2)->by(mb_strtolower(trim((string) $request->input('email')))),
            ];
        });

        $this->routes(function () {
            // Tanpa namespace: isinya komponen Livewire & controller ber-FQCN.
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
}
