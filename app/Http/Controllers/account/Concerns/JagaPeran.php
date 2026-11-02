<?php

namespace App\Http\Controllers\account\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Penjagaan peran untuk pengendali di bawah `account/`.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini ada
 * ---------------------------------------------------------------------------
 *
 * Grup rute `account/` hanya ber-middleware `web, auth, terverifikasi`. TIDAK
 * ADA middleware peran sama sekali, dan tidak ada aliasnya di Kernel — jadi
 * penjagaannya diserahkan ke tiap pengendali, satu per satu, dan yang lupa
 * memasangnya terbuka untuk SIAPA PUN yang punya akun.
 *
 * Peran bawaan kolomnya `'user'`. Artinya tiap pelanggan yang mendaftar akun
 * — ratusan orang — otomatis memenuhi `auth` + `terverifikasi`.
 *
 * Terukur 3 Nov 2026 dengan satu akun berperan `user`, sembilan layar panitia
 * menjawab HTTP 200 untuknya:
 *
 *     Artikel · Kategori Artikel · Referensi Paper · Camp · Meme ·
 *     Paperisasi · Maintenance · Laporan Peserta · Karir
 *
 * Bilah sisi memang menyembunyikan menunya di balik pagar peran, tetapi
 * alamatnya bisa ditebak — dan menyembunyikan tautan bukan penjagaan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa di PEMBANGUN, bukan di tiap method
 * ---------------------------------------------------------------------------
 *
 * Pola lama di aplikasi ini memanggil `boleh()`/`tolak()` di awal tiap aksi.
 * Itu bekerja selama tidak ada yang lupa — dan justru kelupaan itu yang
 * membuat kesembilan layar di atas terbuka. Dipasang sekali di pembangun,
 * method yang ditambahkan besok ikut terjaga tanpa harus diingat siapa pun.
 *
 * `Illuminate\Routing\Controller` masih menyediakan `middleware()` di Laravel
 * 12, dan pengendali aplikasi ini mewarisinya.
 *
 * ---------------------------------------------------------------------------
 * Kenapa 403, bukan dialihkan
 * ---------------------------------------------------------------------------
 *
 * Yang sampai ke sini bukan orang yang salah tautan — menunya memang tidak
 * pernah ditampilkan kepadanya. Dialihkan diam-diam ke dasbor, percobaan
 * menerobos terbaca sama persis dengan penggunaan biasa di log. 403 menyebut
 * apa yang terjadi.
 */
trait JagaPeran
{
    /**
     * Hanya administrator.
     *
     * @param  list<string>  $kecuali  method yang BOLEH dibuka siapa saja
     */
    protected function jagaAdministrator(array $kecuali = []): void
    {
        $this->pasangPenjaga(
            fn ($p) => (bool) $p?->adalahAdministrator(),
            $kecuali
        );
    }

    /**
     * Administrator, ATAU sederet akun tertentu.
     *
     * Dipakai layar yang bilah sisinya memang berbunyi
     * `adalahAdministrator() || Auth::user()->id === 83`. Nomornya DISALIN
     * apa adanya dari sana, bukan ditebak: mengubahnya mengunci orang yang
     * hari ini benar-benar bekerja di layar itu.
     *
     * Nomor akun di dalam kode memang bukan cara yang benar — yang benar
     * sebuah peran, misalnya 'penulis'. Dibiarkan sebagai salinan supaya
     * penjagaan ini tidak sekaligus mengubah siapa yang boleh; penggantinya
     * perkara tersendiri dan perlu diputuskan pemiliknya.
     *
     * @param  list<int>  $idTambahan
     * @param  list<string>  $kecuali
     */
    protected function jagaAdministratorAtau(array $idTambahan, array $kecuali = []): void
    {
        $this->pasangPenjaga(
            fn ($p) => (bool) $p?->adalahAdministrator()
                || ($p !== null && in_array((int) $p->getKey(), $idTambahan, true)),
            $kecuali
        );
    }

    /**
     * Orang dalam yang BUKAN karyawan — persis pagar bilah sisinya.
     *
     * @param  list<string>  $kecuali
     */
    protected function jagaOrangDalamBukanKaryawan(array $kecuali = []): void
    {
        $this->pasangPenjaga(
            fn ($p) => $p !== null && $p->adalahOrangDalam() && ! $p->adalahKaryawan(),
            $kecuali
        );
    }

    /**
     * @param  \Closure(\App\User|null): bool  $boleh
     * @param  list<string>  $kecuali
     */
    private function pasangPenjaga(\Closure $boleh, array $kecuali): void
    {
        $penjaga = $this->middleware(function ($request, $next) use ($boleh) {
            if (! $boleh(Auth::user())) {
                abort(403, 'Layar ini bukan untuk akun Anda.');
            }

            return $next($request);
        });

        if ($kecuali !== []) {
            $penjaga->except($kecuali);
        }
    }
}
