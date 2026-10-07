<?php

namespace Tests\Feature;

use App\ClinikScopusPromo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Perintah terjadwal `promo:expire`.
 *
 * Tiga cacat bertumpuk, dan tidak satu pun pernah menampakkan diri: penjadwal
 * mati karena proc_open, lalu kelas modelnya tidak bisa dimuat di Linux, lalu
 * siaran ke kelas yang tidak pernah ada. Yang ketiga ada DI DALAM
 * perulangan — jadi promo pertama tersimpan dan sisanya tidak.
 */
class PerintahPromoKedaluwarsaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function menonaktifkan_semua_promo_yang_sudah_lewat_bukan_cuma_yang_pertama(): void
    {
        /*
         * TIGA promo, bukan satu. Dengan satu promo, perintah yang mati di
         * perulangan pertama tetap terlihat berhasil: promo itu memang sudah
         * tersimpan sebelum barisnya melempar.
         */
        $lewat = collect(range(1, 3))->map(fn () => $this->promo(now()->subDays(3)));
        $belum = $this->promo(now()->addDays(3));

        Artisan::call('promo:expire');

        foreach ($lewat as $p) {
            $this->assertSame('non active', $p->fresh()->status,
                'Ada promo lewat yang tidak ikut dinonaktifkan; perulangannya mati di tengah.');
        }

        $this->assertSame('active', $belum->fresh()->status,
            'Promo yang belum lewat ikut dimatikan.');

        $this->assertStringContainsString('3 promo', Artisan::output());
    }

    #[Test]
    public function tidak_menyiarkan_ke_kelas_yang_tidak_ada(): void
    {
        /*
         * app/Events/ tidak ada sama sekali di proyek ini, dan tidak ada satu
         * pun yang mendengarkan siarannya. Barisnya selalu melempar
         * "Class App\Events\PromoStatusUpdated not found".
         */
        /*
         * KOMENTARNYA DIBUANG dulu lewat token_get_all.
         *
         * Percobaan pertama memindai berkasnya apa adanya dan langsung merah
         * pada kode yang justru sudah benar: catatan di perintah itu
         * MENYEBUTKAN nama kelas yang dibuang, beserta alasannya. Pemindai
         * yang menghitung komentar menghukum penjelasan — dan pilihan yang
         * tersisa jadi "buang penjelasannya", yang persis kebalikan dari yang
         * diinginkan.
         */
        $sumber = $this->tanpaKomentar(
            file_get_contents(base_path('app/Console/Commands/ExpirePromoStatus.php'))
        );

        $this->assertStringNotContainsString('PromoStatusUpdated', $sumber,
            'Siaran ke kelas yang tidak pernah ada kembali; perintahnya akan mati di perulangan.');

        $this->assertDirectoryDoesNotExist(base_path('app/Events'),
            'app/Events sekarang ADA — kalau memang dibuat, siarannya boleh dipasang lagi, '
            . 'tetapi di LUAR perulangan supaya satu kegagalan tidak menghentikan sisanya.');
    }

    /** Kode tanpa komentarnya. */
    private function tanpaKomentar(string $isi): string
    {
        $hasil = '';

        foreach (token_get_all($isi) as $t) {
            if (is_array($t) && in_array($t[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            $hasil .= is_array($t) ? $t[1] : $t;
        }

        return $hasil;
    }

    private function promo(\Carbon\Carbon $selesai): ClinikScopusPromo
    {
        return ClinikScopusPromo::create([
            'nama_promo' => 'Promo Uji ' . Str::random(6),
            'status' => 'active',
            'tanggal_mulai_promo' => $selesai->copy()->subDays(10),
            'tanggal_selesai_promo' => $selesai,
            'harga_normal' => '1000000',
            'tipe_diskon' => 'nominal',
            'nominal_diskon' => '100000',
            'total_biaya' => '900000',
        ]);
    }
}
