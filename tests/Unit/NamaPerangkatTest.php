<?php

namespace Tests\Unit;

use App\Support\NamaPerangkat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Penerjemah user-agent dipakai di halaman keamanan, tempat pemilik akun
 * memutuskan "ini saya atau bukan". Salah menamai perangkat di situ lebih
 * berbahaya daripada tidak menamainya sama sekali.
 */
class NamaPerangkatTest extends TestCase
{
    public static function contohPeramban(): array
    {
        return [
            'Chrome di Mac' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36',
                'Chrome di Mac',
            ],
            'Safari di iPhone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
                'Safari di iPhone',
            ],
            'Chrome di Android' => [
                'Mozilla/5.0 (Linux; Android 13; SM-A536E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                'Chrome di Android',
            ],
            'Firefox di Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0',
                'Firefox di Windows',
            ],
            // Edge dan Opera menyebut "Chrome" di user-agent-nya; yang lebih
            // khusus harus menang, kalau tidak semuanya jadi "Chrome".
            'Edge di Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
                'Edge di Windows',
            ],
            'Opera di Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 OPR/105.0.0.0',
                'Opera di Windows',
            ],
            // Chrome tanpa jendela dulu terbaca "Safari di Mac": "HeadlessChrome"
            // tidak punya batas kata sebelum "Chrome".
            'Chrome otomatis di Mac' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/153.0.0.0 Safari/537.36',
                'Chrome otomatis di Mac',
            ],
            'Samsung Internet di Android' => [
                'Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0.0.0 Mobile Safari/537.36',
                'Samsung Internet di Android',
            ],
        ];
    }

    #[Test]
    #[DataProvider('contohPeramban')]
    public function peramban_umum_dinamai_dengan_benar(string $ua, string $harapan): void
    {
        $this->assertSame($harapan, NamaPerangkat::ringkas($ua));
    }

    #[Test]
    public function user_agent_kosong_dinyatakan_tidak_dikenali(): void
    {
        $this->assertSame('Peramban tidak dikenali', NamaPerangkat::ringkas(null));
        $this->assertSame('Peramban tidak dikenali', NamaPerangkat::ringkas('   '));
    }

    #[Test]
    public function alat_yang_tidak_dikenali_tetap_ditampilkan_apa_adanya(): void
    {
        // Justru baris seperti inilah yang perlu dilihat pemilik akun; kalau
        // diganti "tidak dikenali", petunjuknya hilang.
        $this->assertSame('curl/8.4.0', NamaPerangkat::ringkas('curl/8.4.0'));
    }

    #[Test]
    public function penanda_html_dibuang_supaya_tidak_ikut_tergambar(): void
    {
        $this->assertSame('curl/8.4.0', NamaPerangkat::ringkas('<b>curl/8.4.0</b>'));
    }
}
