<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Toast tidak tertutup bilah navigasi bawah di ponsel.
 *
 * Toast sengaja dipindah ke DASAR layar di ponsel supaya ia tidak menutupi
 * bilah atas maupun judul yang sedang dibaca. Tetapi di sanalah bilah
 * navigasi ponsel berada: `position: fixed; bottom: 20px; height: 75px` —
 * jadi ia memakan 95px terbawah — dan z-index-nya 2000 melawan 1060 milik
 * wadah SweetAlert.
 *
 * Terukur di peramban pada 390px DENGAN User-Agent ponsel: toast 745–830
 * melawan bilah 749–824, dan titik tengah toast-nya ditempati ikon bilah
 * navigasi. Pemberitahuan yang muncul sesudah menekan "salin" karena itu
 * tidak pernah terbaca sampai habis.
 *
 * Cacat ini TIDAK terlihat dengan User-Agent desktop: layout MIS bercabang
 * dari User-Agent, dan bilah bawahnya hanya disajikan ke peramban ponsel.
 */
class ToastDiAtasNavPonselTest extends TestCase
{
    /** Tinggi bilah ditambah jaraknya dari dasar layar. */
    private const RUANG_BILAH = 95;

    private function gayaBersama(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2) . '/public/assets/css/mis-ui.css'
        );
    }

    private function tataLetakPonsel(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/layouts/version.blade.php'
        );
    }

    /**
     * Angka 95px itu bukan tebakan: ia tinggi bilahnya ditambah jaraknya
     * dari dasar layar. Kalau salah satunya berubah, ruang yang disediakan
     * toast ikut salah — dan tidak ada yang menandainya selain tampilannya.
     */
    public function test_ruang_yang_disediakan_sesuai_ukuran_bilahnya(): void
    {
        $tata = $this->tataLetakPonsel();

        $this->assertSame(1, preg_match(
            '/\.mobile-bottom-nav\s*\{([^}]*)\}/s', $tata, $cocok
        ), 'Aturan .mobile-bottom-nav tidak ketemu di tata letak ponsel.');

        $this->assertSame(1, preg_match('/\bbottom:\s*(\d+)px/', $cocok[1], $bawah));
        $this->assertSame(1, preg_match('/\bheight:\s*(\d+)px/', $cocok[1], $tinggi));

        $this->assertSame(self::RUANG_BILAH, (int) $bawah[1] + (int) $tinggi[1],
            'Ukuran bilah navigasi berubah (' . $bawah[1] . 'px + ' . $tinggi[1]
            . 'px), jadi ruang ' . self::RUANG_BILAH . 'px yang disediakan untuk '
            . 'toast tidak lagi pas.');
    }

    public function test_toast_diangkat_di_atas_bilah_navigasi(): void
    {
        $css = $this->gayaBersama();

        $this->assertSame(1, preg_match(
            '/body:has\(\.mobile-bottom-nav\)[^{]*\.swal2-container\.swal2-top-end\s*\{([^}]*)\}/s',
            $css, $cocok
        ), 'Tidak ada aturan yang mengangkat toast di atas bilah navigasi ponsel.');

        $aturan = $cocok[1];

        $this->assertSame(1, preg_match('/padding-bottom:\s*calc\(\s*(\d+)px/', $aturan, $ruang),
            'Toastnya tidak diberi ruang bawah, jadi ia tetap berimpitan dengan '
            . 'bilah navigasi.');

        $this->assertGreaterThanOrEqual(self::RUANG_BILAH, (int) $ruang[1],
            'Ruang bawahnya kurang dari ' . self::RUANG_BILAH . 'px, jadi toast '
            . 'masih bertindihan dengan bilah navigasi.');

        /*
         * z-index ikut dinaikkan DI ATAS 2000 milik bilahnya.
         *
         * Diangkat saja sudah cukup untuk toast seukuran biasa, tetapi pesan
         * yang panjang bisa tumbuh melewati ruang yang tersisa — dan di situ
         * yang menang tumpukan yang terbaca.
         */
        $this->assertSame(1, preg_match('/z-index:\s*(\d+)/', $aturan, $z),
            'z-index toastnya tidak dinaikkan; bilah navigasi berada di 2000.');

        $this->assertGreaterThan(2000, (int) $z[1],
            'z-index toast (' . $z[1] . ') tidak melebihi 2000 milik bilah '
            . 'navigasi, jadi pesan yang panjang tetap tertutup.');
    }

    /**
     * Pengangkatannya TERKUNCI pada layar yang memang punya bilah itu.
     *
     * Toast ini dipakai seluruh back office, dan sebagian layar tidak punya
     * bilah navigasi bawah sama sekali. Diangkat di semua layar, di sana ia
     * mengambang 105px di atas dasar tanpa alasan apa pun.
     */
    public function test_pengangkatannya_hanya_untuk_layar_berbilah(): void
    {
        $css = $this->gayaBersama();

        $this->assertSame(1, preg_match(
            '/padding-bottom:\s*calc\([^;]*\);/', $css, $tak_terpakai),
            'Ruang bawah toast disetel lebih dari sekali; salah satunya pasti '
            . 'berlaku di layar yang tidak punya bilah navigasi.');

        $this->assertStringContainsString('body:has(.mobile-bottom-nav)', $css,
            'Pengangkatannya tidak dikunci pada layar berbilah.');
    }
}
