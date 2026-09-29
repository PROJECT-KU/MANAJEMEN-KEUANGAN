<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel 8+ memakai Tailwind untuk pagination bawaan; aplikasi ini memakai Bootstrap 4.
        Paginator::useBootstrapFour();

        // Aturan kata sandi tunggal untuk pendaftaran & atur ulang: minimal 8
        // karakter, mengandung huruf dan angka, serta tidak pernah muncul pada
        // kebocoran data publik. Pemeriksaan kebocoran gagal-aman: kalau layanan
        // pemeriksanya tidak terjangkau, kata sandi tetap diterima.
        AturanKataSandi::defaults(fn () => AturanKataSandi::min(8)->letters()->numbers()->uncompromised());

        // "Ingat saya" bawaan Laravel berlaku lima tahun; untuk sistem yang
        // memegang data keuangan itu terlalu lama, apalagi bila dipakai di
        // komputer bersama.
        // Tautan verifikasi & atur ulang dibuat dari APP_URL. Bila salah, semua
        // tautan di email mengarah ke tempat yang keliru -- dan tanda tangannya
        // ikut tidak cocok. Cukup sering terlewat saat deploy.
        if ($this->app->environment('production')
            && preg_match('/localhost|127\.0\.0\.1|^$/', (string) config('app.url'))) {
            \Illuminate\Support\Facades\Log::warning(
                'APP_URL masih ' . config('app.url') . ' di lingkungan produksi; tautan pada email akan salah.'
            );
        }

        // Kue sesi tanpa penanda Secure ikut terkirim lewat HTTP biasa, jadi
        // bisa dibaca di jaringan yang tidak tepercaya.
        if ($this->app->environment('production') && ! config('session.secure')) {
            \Illuminate\Support\Facades\Log::warning(
                'SESSION_SECURE_COOKIE belum dinyalakan di produksi; kue sesi bisa terkirim tanpa HTTPS.'
            );
        }

        Auth::guard('web')->setRememberDuration(
            (int) config('auth.ingat_saya_menit', 60 * 24 * 30)
        );

        /*
         * Angka lencana di bilah samping.
         *
         * Dulu tiga composer '*' terpisah, masing-masing menjalankan count()
         * ke basis data. Karena '*' berlaku untuk SETIAP view, satu halaman
         * yang merender belasan view menjalankan hitungan itu belasan kali
         * juga: pada dasbor manajer terukur 19x untuk pemesanan Clinik
         * Scopus, 9x perjalanan dinas, dan 9x todolist. Sekarang ketiganya
         * dihitung sekali per permintaan lalu dipakai ulang.
         */
        View::composer('*', function ($view) {
            static $angka = null;

            if ($angka === null) {
                $angka = $this->angkaLencana(Auth::user());
            }

            $view->with($angka);
        });
    }

    /**
     * Hitung seluruh angka lencana sekali jalan.
     *
     * @return array<string,int>
     */
    private function angkaLencana(?\App\User $user): array
    {
        $kosong = [
            'countAjukan' => 0,
            'totalAssignTask' => 0,
            'countScopusPending' => 0,
            'countPaid' => 0,
        ];

        if (! $user) {
            return $kosong;
        }

        $pengelola = $user->adalahAdministrator();

        // --- perjalanan dinas yang menunggu
        $countAjukan = DB::table('perjalanan_dinas')
            ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
            ->where('perjalanan_dinas.status', 'ajukan')
            ->when($pengelola, function ($query) use ($user) {
                return $query->where('users.company', $user->company);
            }, function ($query) use ($user) {
                return $query->where('perjalanan_dinas.user_id', $user->id);
            })
            ->count();

        // --- tugas yang ditugaskan
        $tugas = DB::table('todolist')->where('status', 'Assign Task');

        if (! $user->adalahAdministrator()) {
            $tugas->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('user_id_kedua', $user->id);
            });
        }

        // --- pemesanan Clinik Scopus: dua status sekaligus dalam satu kueri
        $pesanan = DB::table('clinikscopus_pemesanan')
            ->selectRaw("SUM(status = 'pending') as menunggu, SUM(status = 'paid') as terbayar");

        if ($user->adalahPelanggan() && $user->jenis === 'perorangan') {
            $pesanan->where('customer_id', $user->id);
        } elseif ($user->adalahKaryawan()) {
            $pesanan->where('trainer_id', $user->id)
                ->whereExists(function ($q) use ($user) {
                    $q->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'clinikscopus_pemesanan.trainer_id')
                        ->where('users.company', $user->company);
                });
        }

        $hitungPesanan = $pesanan->first();

        return [
            'countAjukan' => $countAjukan,
            'totalAssignTask' => $tugas->count(),
            'countScopusPending' => (int) ($hitungPesanan->menunggu ?? 0),
            'countPaid' => (int) ($hitungPesanan->terbayar ?? 0),
        ];
    }
}
