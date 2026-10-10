<?php

namespace App\Support;

/**
 * Menormalkan nomor telepon jadi satu bentuk: angka saja, berawalan 62.
 *
 * Dipakai BERSAMA oleh penyimpan pendaftaran dan pencari data pendaftar.
 * Kalau keduanya punya salinan sendiri, keduanya akan berbeda perlahan —
 * dan yang terjadi: nomor tersimpan "6281234567890" sementara pencariannya
 * mencari "081234567890", lalu pencariannya selalu bilang tidak ketemu
 * tanpa ada yang tahu kenapa.
 *
 * Bentuk yang beredar di basis data ini memang bermacam-macam: kolom
 * users.telp menyimpan "+6285155240654" lengkap dengan plusnya, sedangkan
 * pendaftaran webinar menyimpan "62895421735441" tanpa plus.
 */
class NomorTelepon
{
    /** Panjang angka terpendek yang masuk akal (08xx + 7 angka). */
    public const MINIMAL = 9;

    /** Batas E.164. */
    public const MAKSIMAL = 15;

    /**
     * Angka saja, awalan 0 diganti 62.
     *
     * String kosong kalau tidak ada angka sama sekali — pemanggilnya yang
     * memutuskan apa artinya, sebab "tidak ada angka" di borang pendaftaran
     * berarti ditolak, sedangkan di pencarian berarti tidak usah mencari.
     */
    public static function rapikan(?string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor);

        if ($angka === '') {
            return '';
        }

        // 0812... -> 62812...
        if (str_starts_with($angka, '0')) {
            return '62' . substr($angka, 1);
        }

        return $angka;
    }

    /**
     * Semua bentuk yang mungkin tersimpan di basis data untuk satu nomor.
     *
     * Kolomnya diisi bertahun-tahun oleh layar yang berbeda, jadi satu orang
     * bisa tersimpan sebagai "6281234567890", "081234567890", atau
     * "+62 812-3456-7890". Mencari satu bentuk saja membuat sebagian orang
     * tidak pernah ketemu — dan diamnya pencarian terbaca sebagai "memang
     * belum pernah mendaftar", bukan sebagai pencarian yang meleset.
     *
     * @return list<string>
     */
    public static function semuaBentuk(?string $nomor): array
    {
        $rapi = self::rapikan($nomor);

        if ($rapi === '') {
            return [];
        }

        $bentuk = [$rapi];

        // 62812... -> 0812...
        if (str_starts_with($rapi, '62')) {
            $bentuk[] = '0' . substr($rapi, 2);
        }

        return array_values(array_unique($bentuk));
    }

    /**
     * Ungkapan SQL yang membuang tanda baca dari satu kolom nomor.
     *
     * Dibutuhkan karena kolomnya menyimpan nomor APA ADANYA seperti diketik:
     * terukur di basis data ini, 424 nomor memuat tanda hubung. Membandingkan
     * kolomnya langsung dengan bentuk berangka-saja TIDAK PERNAH cocok, dan
     * ketidakcocokan itu diam — pencariannya cuma memulangkan nol.
     *
     * REPLACE bersarang, bukan REGEXP_REPLACE: yang terakhir baru ada di
     * MySQL 8 dan MariaDB 10.0, dan kegagalannya berupa galat SQL di
     * produksi, bukan di sini.
     *
     * Yang dibuang enam tanda yang lazim diketik orang. Hanya '-' yang
     * benar-benar ada di data sekarang; lima lainnya untuk yang masuk
     * besok — borangnya tidak membatasi apa pun.
     */
    public static function tanpaTandaSql(string $kolom): string
    {
        $bersih = $kolom;

        foreach ([' ', '-', '.', '(', ')', '+'] as $tanda) {
            $bersih = "REPLACE({$bersih}, '{$tanda}', '')";
        }

        return $bersih;
    }

    /** Sudah cukup panjang untuk dicari atau disimpan. */
    public static function masukAkal(?string $nomor): bool
    {
        $panjang = strlen(self::rapikan($nomor));

        return $panjang >= self::MINIMAL && $panjang <= self::MAKSIMAL;
    }
}
