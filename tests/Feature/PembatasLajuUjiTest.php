<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembatas laju tidak boleh menulis ke cache yang sungguhan saat diuji.
 *
 * config/cache.php menyetel `limiter` TERPISAH dari `default`, jadi
 * CACHE_DRIVER=array di phpunit.xml tidak menyentuhnya. Dengan 'database',
 * hitungan throttle ditulis ke tabel `cache` yang sungguhan dan bertahan
 * antar jalankan rangkaian uji.
 *
 * Terukur 31 Okt 2026: batas 'isi-otomatis' 25 per HARI per alamat IP:
 * menjalankan rangkaiannya beberapa kali dalam satu hari menghabiskan jatah
 * 127.0.0.1, lalu dua uji pencarian nomor memerah dengan 429 — tanpa satu
 * baris kode pun berubah, dan hijau lagi keesokan harinya. Kegagalan yang
 * paling mahal justru karena ia menuduh kode yang tidak bersalah.
 */
class PembatasLajuUjiTest extends TestCase
{
    #[Test]
    public function hitungan_throttle_tidak_mendarat_di_tabel_cache(): void
    {
        $sebelum = DB::table('cache')->count();

        /*
         * Dihitung lewat RateLimiter sungguhan, bukan dengan membaca
         * config(): membaca setelannya hanya mengulang apa yang ditulis di
         * berkas, sementara yang ditanyakan di sini KE MANA angkanya jatuh.
         */
        $kunci = 'uji-pembatas-' . bin2hex(random_bytes(8));

        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit($kunci, 60);
        }

        $this->assertSame(3, RateLimiter::attempts($kunci),
            'Pembatas lajunya tidak menghitung sama sekali; ujinya tidak menguji apa pun.');

        $this->assertSame($sebelum, DB::table('cache')->count(),
            'Hitungan throttle mendarat di tabel `cache` yang sungguhan. Jatah '
            . 'per-hari akan habis sesudah rangkaian ujinya dijalankan beberapa '
            . 'kali, lalu uji yang menyentuh alamat berpembatas memerah dengan '
            . '429 tanpa satu baris kode pun berubah.');

        RateLimiter::clear($kunci);
    }
}
