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
