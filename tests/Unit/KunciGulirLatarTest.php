<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pengunci gulir latar dipasang di dua berkas: mis-ui.js menempelkan
 * kelasnya, mis-ui.css yang benar-benar mengunci. Kalau salah satu namanya
 * diubah tanpa yang lain, tidak ada yang galat — latar cuma kembali bisa
 * digulir di belakang jendela, dan itu baru ketahuan kalau ada yang
 * membukanya di ponsel.
 */
class KunciGulirLatarTest extends TestCase
{
    private const KELAS = 'mis-latar-terkunci';

    private const PEUBAH = '--mis-bilah-gulir';

    public function test_kelas_pengunci_dipakai_js_dan_didefinisikan_css(): void
    {
        $akar = dirname(__DIR__, 2);
        $js = (string) file_get_contents($akar . '/public/assets/js/mis-ui.js');
        $css = (string) file_get_contents($akar . '/public/assets/css/mis-ui.css');

        $this->assertStringContainsString(self::KELAS, $js, 'mis-ui.js tidak lagi menempelkan kelas penguncinya.');
        $this->assertStringContainsString('body.' . self::KELAS, $css, 'mis-ui.css tidak punya aturan untuk kelas itu.');
        $this->assertMatchesRegularExpression(
            '/body\.' . preg_quote(self::KELAS, '/') . '\s*\{[^}]*overflow:\s*hidden/',
            $css,
            'Aturannya ada tapi tidak lagi mengunci overflow.'
        );

        // Ganjalan lebar bilah gulir: tanpa pasangan ini halaman melompat ke
        // kanan sesaat jendela dibuka di peramban yang bilahnya memakan ruang.
        $this->assertStringContainsString(self::PEUBAH, $js);
        $this->assertStringContainsString(self::PEUBAH, $css);
    }

    public function test_pengunci_mengintai_jendela_yang_dipakai_seluruh_layar(): void
    {
        $akar = dirname(__DIR__, 2);
        $js = (string) file_get_contents($akar . '/public/assets/js/mis-ui.js');

        // Semua jendela di sistem ini memakai kelas .custom-popup; itulah yang
        // diintai. Kalau layar baru memakai nama lain, latarnya tidak terkunci.
        $this->assertStringContainsString("'.custom-popup'", $js);
    }
}
