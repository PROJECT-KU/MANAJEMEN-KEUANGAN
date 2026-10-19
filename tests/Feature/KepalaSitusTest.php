<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kepala situs publik: menu layar lebar dan menu hamburger.
 *
 * Satu berkas layout, SEPULUH halaman publik. Yang dijaga di sini bukan
 * seleranya — rupa boleh berubah kapan saja — melainkan dua hal yang kalau
 * rusak tidak mengeluarkan galat apa pun.
 */
class KepalaSitusTest extends TestCase
{
    use DatabaseTransactions;

    private const LAYOUT = 'resources/views/public/layout/header.blade.php';

    #[Test]
    public function tombol_masuk_tidak_lagi_memakai_gaya_sebaris(): void
    {
        /*
         * Tombolnya dulu membawa warnanya sendiri di atribut style=, dan
         * itu sebabnya ia tertinggal memakai gradien merah-jingga lama
         * sementara seluruh halaman sudah berpindah ke jingga logo:
         * style= menang atas aturan CSS mana pun, jadi menatanya dari
         * lembar gaya tidak akan pernah berpengaruh.
         *
         * Diperiksa pada HALAMAN YANG TERGAMBAR dan dibatasi ke blok
         * <header>: #ff3131 masih sah dipakai di tempat lain.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        $cocok = preg_match('#<header\b.*?</header>#s', $isi, $tangkap);

        $this->assertSame(1, $cocok, 'Blok <header> tidak ditemukan di halaman.');

        $this->assertStringContainsString('kop-tombol', $tangkap[0],
            'Tombol masuk di kepala situs kehilangan kelas .kop-tombol.');

        $this->assertStringNotContainsString('#ff3131', $tangkap[0],
            'Tombol masuk kembali membawa warnanya di atribut style=. Warna di '
            . 'style= menang atas lembar gaya, jadi tombolnya akan tertinggal '
            . 'setiap kali warna situs berubah — tanpa satu pun galat.');
    }

    #[Test]
    public function timpaan_menu_ponsel_cukup_bobot_melawan_selektor_id(): void
    {
        /*
         * Aturan layar lebar memakai #navbar, yang berbobot ID. Selektor
         * berkelas saja KALAH melawannya.
         *
         * Versi pertama menu ponselnya ditulis `.navbar-mobile a` dan mati
         * diam-diam: terukur di peramban, butir menunya memakai padding
         * 14px/9px dan ukuran 15px milik layar lebar, latar butir aktifnya
         * tetap tembus pandang, dan penandanya jadi campuran — lebar 5px
         * dari aturan ponsel, tinggi 3px dari aturan layar lebar.
         *
         * Gejalanya yang paling menipu: menunya tampak HAMPIR benar.
         */
        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
            (string) file_get_contents(base_path(self::LAYOUT))
        );

        $this->assertStringContainsString('#navbar.navbar-mobile a.active', $sumber,
            'Aturan butir aktif menu ponsel kehilangan awalan #navbar; ia akan '
            . 'kalah bobot melawan #navbar a.active dan tidak pernah berlaku.');

        /*
         * Yang dilarang khusus aturan yang PUNYA tandingan ber-#navbar.
         * `.navbar-mobile { background }` sendirian tidak bertanding dengan
         * apa pun, jadi ia boleh tetap berkelas saja.
         */
        $telanjang = [];

        foreach (['a', 'ul', '.dropdown', '.kop-tombol', '.kop-bungkus-tombol'] as $bagian) {
            if (preg_match('/(?<![.\w#])\.navbar-mobile\s+' . preg_quote($bagian, '/') . '\b/', $sumber)) {
                $telanjang[] = '.navbar-mobile ' . $bagian;
            }
        }

        $this->assertSame([], $telanjang,
            "Aturan menu ponsel ini ditulis tanpa awalan #navbar dan akan kalah "
            . "bobot melawan aturan layar lebar:\n- " . implode("\n- ", $telanjang));
    }

    #[Test]
    public function seluruh_halaman_publik_masih_menggambar_menunya(): void
    {
        /*
         * Satu layout, sepuluh halaman. Perubahan di sini menyentuh layar
         * yang sedang tidak dikerjakan, dan tidak ada yang memberi tahu
         * kalau salah satunya rusak.
         */
        $halaman = [
            '/', '/blog', '/contact', '/Scopus-Camp', '/Scopus-Kafe',
            '/Analisis-Bibliometrik', '/Clinik-Scopus', '/Webinar-Eksklusif',
            '/Refrensi-Paper', '/paperisasi/public/data',
        ];

        foreach ($halaman as $jalur) {
            $isi = (string) $this->get($jalur)->assertOk()->getContent();

            $this->assertStringContainsString('id="navbar"', $isi,
                'Halaman ' . $jalur . ' tidak lagi menggambar menunya.');

            $this->assertStringContainsString('mobile-nav-toggle', $isi,
                'Halaman ' . $jalur . ' kehilangan tombol hamburgernya; di layar '
                . 'sempit tidak ada satu pun jalan ke menu.');
        }
    }
}
