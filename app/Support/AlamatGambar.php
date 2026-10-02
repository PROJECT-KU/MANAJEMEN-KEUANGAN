<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Mengubah nilai kolom gambar jadi alamat yang bisa dibuka peramban.
 *
 * Ada DUA bentuk nilai yang beredar di basis data, dan keduanya harus tetap
 * bisa dibuka:
 *
 * 1. Bentuk BARU — jalur di cakram `public`, misalnya
 *    "angkatan/webinar_eksklusif/3f2a....webp". Dilayani lewat
 *    `public/storage` yang ditautkan `php artisan storage:link`.
 *
 * 2. Bentuk LAMA — nama berkas di dalam `public/<folder>/`, misalnya
 *    "ScopusCamp/camp-jogja.jpeg" atau sekadar "camp-jogja.jpeg". Dibaca
 *    lewat basename(), persis seperti yang sudah dilakukan halaman publik
 *    Scopus Camp dan Bibliometrik sejak dulu.
 *
 * Keduanya dilayani bersamaan DENGAN SENGAJA. Konversi 60 angkatan ke WebP
 * berjalan sebagai perintah tersendiri, dan di antara deploy kode dan
 * perintah itu dijalankan, nilai lama masih ada di basis data. Tanpa jalur
 * mundur, halaman publik akan kehilangan seluruh sampulnya selama jeda itu.
 */
class AlamatGambar
{
    /** Cakram unggahan; sama dengan App\Services\Gambar::CAKRAM. */
    public const CAKRAM = 'unggahan';

    /**
     * Alamat gambar, atau null kalau berkasnya memang tidak ada.
     *
     * null, bukan alamat yang menunjuk berkas tidak ada: kolom terisi yang
     * berkasnya hilang membuat halaman menampilkan gambar rusak, dan tidak ada
     * yang tahu sampai ada yang melapor. Pemanggilnya yang memilih cadangan.
     *
     * @param  string|null  $nilai     isi kolom gambar
     * @param  string|null  $folderLama  folder di public/ untuk bentuk lama
     */
    public static function url(?string $nilai, ?string $folderLama = null): ?string
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        // Alamat lengkap yang sudah terlanjur tersimpan dibiarkan apa adanya.
        if (str_starts_with($nilai, 'http://') || str_starts_with($nilai, 'https://')) {
            return $nilai;
        }

        if (Storage::disk(self::CAKRAM)->exists($nilai)) {
            return asset('storage/' . ltrim($nilai, '/'));
        }

        if ($folderLama) {
            $berkas = public_path(trim($folderLama, '/') . '/' . basename($nilai));

            if (is_file($berkas)) {
                return asset(trim($folderLama, '/') . '/' . basename($nilai));
            }
        }

        /*
         * Percobaan terakhir: nilainya mungkin sudah memuat foldernya sendiri
         * ("ScopusCamp/abc.jpeg") sementara pemanggilnya tidak menyebutkan
         * folder apa pun.
         */
        if (str_contains($nilai, '/') && is_file(public_path($nilai))) {
            return asset($nilai);
        }

        return null;
    }

    /**
     * Ukuran asli gambarnya: ['lebar' => int, 'tinggi' => int], atau null.
     *
     * Dipakai tampilan untuk MEMESAN RUANG lewat atribut width/height.
     * Tanpa itu gambarnya setinggi nol sampai berkasnya tiba, lalu seluruh
     * isi di bawahnya terdorong sekaligus — terukur di borang pendaftaran:
     * halaman digulir ke pesan galat, flyernya menyusul termuat, dan
     * pesannya terdorong keluar layar lagi.
     *
     * Hasilnya diingat selama satu permintaan: satu halaman bisa memanggil
     * ini beberapa kali untuk gambar yang sama, dan getimagesize() membaca
     * cakram tiap kali dipanggil.
     *
     * @return array{lebar:int,tinggi:int}|null
     */
    public static function ukuran(?string $nilai, ?string $folderLama = null): ?array
    {
        $jalur = self::jalurLokal($nilai, $folderLama);

        if ($jalur === null) {
            return null;
        }

        static $diingat = [];

        if (! array_key_exists($jalur, $diingat)) {
            $tentang = @getimagesize($jalur);

            $diingat[$jalur] = ($tentang && $tentang[0] > 0 && $tentang[1] > 0)
                ? ['lebar' => (int) $tentang[0], 'tinggi' => (int) $tentang[1]]
                : null;
        }

        return $diingat[$jalur];
    }

    /**
     * Jalur berkasnya di cakram, mengikuti urutan pencarian yang sama dengan
     * url(). Null untuk alamat lengkap — ukurannya tidak bisa dibaca dari
     * sini tanpa mengunduhnya, dan itu bukan pekerjaan saat merender halaman.
     */
    private static function jalurLokal(?string $nilai, ?string $folderLama = null): ?string
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '' || str_starts_with($nilai, 'http://') || str_starts_with($nilai, 'https://')) {
            return null;
        }

        if (Storage::disk(self::CAKRAM)->exists($nilai)) {
            return Storage::disk(self::CAKRAM)->path($nilai);
        }

        if ($folderLama) {
            $berkas = public_path(trim($folderLama, '/') . '/' . basename($nilai));

            if (is_file($berkas)) {
                return $berkas;
            }
        }

        if (str_contains($nilai, '/') && is_file(public_path($nilai))) {
            return public_path($nilai);
        }

        return null;
    }

    /** Berkasnya sudah dalam bentuk baru (WebP di storage). */
    public static function diStorage(?string $nilai): bool
    {
        $nilai = trim((string) $nilai);

        return $nilai !== '' && Storage::disk(self::CAKRAM)->exists($nilai);
    }
}
