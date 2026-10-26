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
            /*
             * DIPERKETAT 26 Okt 2026, dan sebabnya bukan beban peladen.
             *
             * Alamat ini memulangkan NAMA, EMAIL, dan AFILIASI untuk satu
             * nomor telepon, tanpa login dan tanpa bukti apa pun bahwa
             * penanyanya pemilik nomor itu. Dengan batas lama — 10 per menit
             * — satu alamat IP bisa menanyakan 14.400 nomor sehari: siapa pun
             * yang memegang daftar nomor bocoran bisa memperkayanya jadi
             * berkas identitas, dari basis data kita.
             *
             * Fiturnya TIDAK dicabut: ia diminta pemiliknya justru supaya
             * orang tidak perlu mengetik ulang datanya. Yang diubah harganya.
             *
             * Dua batas bersamaan, dan keduanya perlu:
             *
             *   - 4 per menit menahan penyapuan cepat;
             *   - 25 per HARI menahan penyapuan lambat, yang tidak tersentuh
             *     batas per menit sama sekali. Orang sungguhan menanyakan
             *     nomornya sendiri satu sampai tiga kali; 25 jauh di atas itu,
             *     dan memaksa pemanen menyiapkan ratusan alamat IP.
             *
             * Ditambah batas per NOMOR, supaya satu sasaran tertentu tidak
             * bisa diintai berulang-ulang dari banyak jaringan sekaligus.
             *
             * DELAPAN, bukan tiga. Angka pertamanya tiga, dan itu langsung
             * menggigit pemakaian yang sah: satu orang yang nomornya tidak
             * ketemu mencoba ulang dalam bentuk lain — 0812…, +62812…, dengan
             * tanda hubung — dan bentuk keempatnya sudah ditolak. Terukur
             * begitu oleh ujinya sendiri. Delapan memberi ruang untuk
             * percobaan yang wajar dan tetap menutup pengintaian.
             */
            $nomor = \App\Support\NomorTelepon::rapikan((string) $request->input('telp'));

            return [
                Limit::perMinute(4)->by($request->ip()),
                Limit::perDay(25)->by('isi-ip:' . $request->ip()),
                Limit::perDay(8)->by('isi-nomor:' . $nomor),
            ];
        });

        /*
         * Pemberitahuan balik dari gerbang pembayaran.
         *
         * Tanda tangannya diperiksa sebelum satu pun baris disentuh, jadi
         * kiriman palsu tidak berbahaya — tetapi menghitung HMAC untuk
         * banjir kiriman tetap memakan prosesor, dan gerbangnya sendiri tidak
         * pernah mengirim sebanyak itu.
         *
         * 120 per menit: jauh di atas kebutuhan gerbang (satu pembayaran satu
         * pemberitahuan, plus pengulangan saat jawaban kita tidak sampai).
         */
        RateLimiter::for('gerbang-pemberitahuan', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
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

        /*
         * BORANG PENDAFTARAN, dan ini yang paling merugikan kalau hilang.
         *
         * Tiap kiriman: menahan satu KURSI dari kuota yang terbatas,
         * mengirim surat ke alamat yang DITENTUKAN PENGIRIMNYA — jadi
         * alamat siapa pun bisa dibanjiri dari domain kita — dan membuat
         * TAGIHAN SUNGGUHAN di gerbang pembayaran. Terukur 26 Okt 2026:
         * rutenya tidak punya pembatas apa pun, sementara borang Webinar
         * Eksklusif sudah lama punya.
         *
         * Enam per menit: satu orang yang menekan tombolnya berulang karena
         * mengira gagal tidak akan pernah menyentuhnya, sementara skrip yang
         * menyapu kuota berhenti di kiriman ketujuh.
         *
         * Dihitung per IP DAN per alamat email. Per IP saja, satu penyerang
         * dari beberapa jaringan tetap bisa membanjiri satu alamat; per
         * email saja, satu jaringan bisa menyapu banyak alamat sekaligus.
         */
        RateLimiter::for('daftar-umum', function (Request $request) {
            return [
                Limit::perMinute(6)->by($request->ip()),
                Limit::perMinute(3)->by(mb_strtolower(trim((string) $request->input('email')))),
            ];
        });

        /*
         * Pemeriksa kode diskon.
         *
         * Tanpa pembatas, kodenya bisa ditebak satu per satu — dan jawabannya
         * dulu membedakan "angkatan ini tidak punya kode" dari "kode tidak
         * cocok", yang memberi tahu penebaknya angkatan mana yang layak
         * dicoba. Jawabannya kini disamakan; pembatas ini lapis keduanya.
         */
        RateLimiter::for('cek-diskon', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        /*
         * Penanya keadaan pembayaran di halaman status.
         *
         * Tiap permintaan ke sini memicu SATU panggilan keluar ke gerbang
         * pembayaran — terukur 1:1 — dan halamannya menanyainya tiap 5 detik
         * sampai 40 kali. Satu tab terbuka berarti 40 panggilan dalam tiga
         * menit; siapa pun yang memegang satu UUID bisa mengulangnya tanpa
         * henti, menghabiskan kuota kita di gerbang sekaligus kuota proses
         * hosting yang memang sudah pernah berakhir 503 karena poller.
         *
         * 30 per menit: jauh di atas kebutuhan poller sendiri (12 per menit),
         * cukup longgar untuk beberapa tab, dan tetap memotong pengulangan
         * yang tidak wajar.
         */
        /*
         * Halaman status dan unduhan berkasnya.
         *
         * Keduanya mahal dengan cara yang berbeda: halaman statusnya memicu
         * satu panggilan keluar ke gerbang pembayaran tiap kali dimuat, dan
         * unduhan dokumennya merakit PDF — kerja prosesor yang nyata di
         * hosting berbagi.
         *
         * 60 per menit: satu orang yang memuat ulang halamannya berkali-kali
         * tidak akan menyentuhnya, dan pengulangan yang tidak wajar tetap
         * terpotong.
         */
        RateLimiter::for('camp-status', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('camp-keadaan', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
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
