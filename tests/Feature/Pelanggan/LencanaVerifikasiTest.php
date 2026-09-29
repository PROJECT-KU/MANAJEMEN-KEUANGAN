<?php

namespace Tests\Feature\Pelanggan;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Lencana centang terverifikasi pada avatar.
 *
 * Bentuknya satu path SVG bergerigi yang dipakai bersama halaman rincian dan
 * daftar. Yang dijaga di sini dua hal: lencananya hanya muncul untuk yang
 * memang sudah terverifikasi, dan ukurannya mengikuti avatarnya — termasuk
 * tetap 30px pada avatar 104px di halaman rincian, supaya layar yang sudah
 * benar tidak ikut berubah saat lencana yang sama dipasang di tempat lain.
 */
class LencanaVerifikasiTest extends TestCase
{
    use DatabaseTransactions;

    private function orang(bool $terverifikasi): User
    {
        $u = User::create([
            'full_name' => 'Uji Lencana',
            'username' => 'uji_len_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active',
            'peran' => User::PERAN_PELANGGAN,
            'email_verified_at' => $terverifikasi ? now() : null,
        ])->save();

        return $u->refresh();
    }

    private function gambar(User $orang, int $ukuran, bool $lencana): string
    {
        return view('partials.avatar', [
            'orang' => $orang,
            'ukuran' => $ukuran,
            'lencana' => $lencana,
        ])->render();
    }

    #[Test]
    public function tanpa_diminta_lencananya_tidak_muncul(): void
    {
        // Bawaannya mati; di dalam tabel ia hanya menambah keramaian kecuali
        // memang diminta.
        $html = view('partials.avatar', ['orang' => $this->orang(true), 'ukuran' => 38])->render();

        $this->assertStringNotContainsString('mis-foto-lencana', $html);
    }

    #[Test]
    public function yang_sudah_terverifikasi_dapat_centang(): void
    {
        $html = $this->gambar($this->orang(true), 38, true);

        $this->assertStringContainsString('mis-foto-lencana', $html);
        $this->assertStringContainsString('Email sudah terverifikasi', $html);

        // Kelas 'belum' yang mengubah warnanya jadi kuning tidak boleh ikut.
        $this->assertStringNotContainsString('mis-foto-lencana belum', $html);
    }

    #[Test]
    public function yang_belum_terverifikasi_tidak_memakai_centang(): void
    {
        $html = $this->gambar($this->orang(false), 104, true);

        $this->assertStringContainsString('mis-foto-lencana belum', $html);
        $this->assertStringContainsString('Email belum terverifikasi', $html);
    }

    public static function ukuranAvatar(): array
    {
        return [
            // Halaman rincian. 30px adalah ukuran yang dipakai sebelum
            // lencananya dibuat menyesuaikan diri; ia HARUS tetap 30px.
            'rincian 104px' => [104, 30],
            'daftar 38px' => [38, 13],
            'bawaan 38px' => [38, 13],
            // Di bawah ini nisbahnya menghasilkan angka lebih kecil dari batas
            // bawah, jadi yang berlaku batasnya.
            'mungil 24px' => [24, 13],
        ];
    }

    #[Test]
    #[DataProvider('ukuranAvatar')]
    public function ukuran_lencana_mengikuti_avatarnya(int $ukuran, int $lencana): void
    {
        $html = $this->gambar($this->orang(true), $ukuran, true);

        $this->assertStringContainsString(
            '--lencana: ' . $lencana . 'px',
            $html,
            "Avatar {$ukuran}px seharusnya berlencana {$lencana}px."
        );
    }

    #[Test]
    public function daftar_pelanggan_memasang_lencana_hanya_pada_yang_terverifikasi(): void
    {
        /*
         * Diperiksa dari berkas tampilannya, bukan dari halaman yang sudah
         * dirender: yang gampang hilang saat layar ini diutak-atik lagi adalah
         * syaratnya, bukan lencananya. Tanpa syarat itu, setiap baris dapat
         * lencana — termasuk yang emailnya belum terverifikasi.
         */
        $blade = file_get_contents(resource_path('views/account/customer/index.blade.php'));

        $this->assertStringContainsString("'lencana' => (bool) \$orang->email_verified_at", $blade);
    }
}
