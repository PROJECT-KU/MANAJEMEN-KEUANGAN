<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Penjaga: jangan biarkan tes menghapus isi basis data pengembangan.
     *
     * phpunit.xml tidak menimpa DB_DATABASE, jadi seluruh tes berjalan di
     * basis data yang sama dengan `php artisan serve` — di sini `rsc`. Itu
     * disengaja: banyak tabel warisan (clinikscopus, gaji, todolist) tidak
     * punya migrasi, sehingga basis data kosong tidak cukup untuk menguji.
     *
     * Konsekuensinya semua tes WAJIB memakai DatabaseTransactions, yang
     * hanya membungkus dan mengembalikan perubahannya. RefreshDatabase dan
     * DatabaseMigrations menjalankan `migrate:fresh`: seluruh tabel dibuang
     * lalu dibuat ulang kosong. Pada 27 Sep 2026 satu berkas tes sekali
     * pakai memakai RefreshDatabase dan menghapus seluruh isi `rsc`.
     *
     * Jadi kalau ada yang memakai trait itu sementara sasarannya bukan
     * basis data uji, tesnya dihentikan sebelum `migrate:fresh` sempat
     * jalan — bukan setelah datanya hilang.
     */
    /**
     * setUpTraits() dipanggil sesudah aplikasi dibuat tetapi SEBELUM
     * RefreshDatabase sempat menjalankan migrate:fresh — satu-satunya titik
     * di mana nama basis datanya sudah terbaca dan datanya masih utuh.
     * (Memeriksa di setUp() terlalu dini: .env belum dimuat, env('DB_DATABASE')
     * masih kosong.)
     */
    protected function setUpTraits()
    {
        $this->pastikanTidakMenghapusBasisDataPengembangan();

        return parent::setUpTraits();
    }

    /**
     * Memastikan satu layanan BELUM punya varian, sebatas transaksi uji ini.
     *
     * Daftar varian tiap layanan hidup di tabel `layanan`, yaitu data NYATA
     * yang disunting orang lewat aplikasi — bukan tetapan di kode. Jadi uji
     * yang mengandaikan satu layanan belum bervarian akan merah begitu ada
     * yang menambahkan variannya, dan merahnya menunjuk ke mana-mana kecuali
     * ke sebabnya.
     *
     * Terjadi 3 Okt 2026 pukul 20:01: dua varian ditambahkan ke Scopus Kafe
     * lewat aplikasi, dan 21 uji di tiga berkas langsung merah dengan
     * "Attempt to read property on null" — sebab borang tarifnya menolak
     * kiriman tanpa varian, dan tarif yang ditunggu uji itu tidak pernah
     * tersimpan.
     *
     * Dipanggil di dalam DatabaseTransactions, jadi datanya kembali seperti
     * semula begitu ujinya selesai.
     */
    protected function tanpaVarian(string $kode): void
    {
        \App\Layanan::where('kode', $kode)->update(['varian' => '[]']);
        \App\Layanan::lupakanKatalog();

        /*
         * Tarifnya disisakan TEPAT SATU: aktif, tanpa varian, tanpa jadwal.
         *
         * Mengosongkan daftar varian di layanannya saja tidak cukup — baris
         * yang terlanjur menunjuk satu varian tetap menunjuknya, sehingga
         * pencari tarif bervarian-null tidak menemukan apa pun dan borang
         * layanan menolak membuang variannya ("sudah dipakai tarif atau
         * angkatan").
         *
         * Tetapi menihilkan variannya saja juga tidak cukup. Terukur 3 Okt
         * 2026, data nyatanya berisi EMPAT tarif Scopus Kafe di dua varian,
         * satu di antaranya terjadwal — dinihilkan semua, keempatnya menumpuk
         * di satu keranjang, dan uji yang menghitung "satu tarif terjadwal"
         * menemukan dua.
         *
         * Satu baris yang ada dipertahankan, bukan dibuat baru: uji
         * "menambah varian memindahkan tarif lama" memang menuntut ada tarif
         * lama untuk dipindahkan, dan baris sungguhan membawa seluruh kolom
         * apa adanya.
         */
        $simpan = \DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', $kode)
            ->orderByRaw("status = 'active' DESC")
            ->value('id');

        \DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', $kode)
            ->when($simpan !== null, fn ($q) => $q->where('id', '<>', $simpan))
            ->delete();

        if ($simpan !== null) {
            \DB::table('clinikscopus_biaya_persesi')->where('id', $simpan)->update([
                'varian' => null,
                'status' => 'active',
                'berlaku_mulai' => null,
            ]);
        }

        \DB::table('kategori_layanan')->where('layanan', $kode)->update(['varian' => null]);
    }

    private function pastikanTidakMenghapusBasisDataPengembangan(): void
    {
        $merusak = array_intersect(
            [RefreshDatabase::class, DatabaseMigrations::class],
            class_uses_recursive(static::class)
        );

        if ($merusak === []) {
            return;
        }

        $sambungan = config('database.default');
        $basis = (string) config("database.connections.{$sambungan}.database");

        // Hanya basis data yang namanya menyebut dirinya basis data uji, atau
        // yang memang hidup di memori, yang boleh dibangun ulang. Nama yang
        // tidak dikenali dianggap BERBAHAYA, bukan aman.
        if ($basis === ':memory:' || str_contains($basis, 'test') || str_contains($basis, 'uji')) {
            return;
        }

        throw new RuntimeException(sprintf(
            "%s memakai %s, yang menjalankan migrate:fresh dan MENGHAPUS seluruh isi basis data \"%s\".\n"
                . "Basis data itu dipakai juga oleh aplikasi yang sedang berjalan.\n"
                . "Pakai DatabaseTransactions seperti tes lain, atau arahkan DB_DATABASE ke basis data uji tersendiri.",
            static::class,
            basename(str_replace('\\', '/', reset($merusak))),
            $basis
        ));
    }
}
