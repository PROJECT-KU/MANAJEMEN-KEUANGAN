<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Beranda: halaman pertama yang dibuka orang sebelum ia tahu situs ini apa.
 *
 * Ditulis ulang 18 Okt 2026. Yang dijaga di sini BUKAN seleranya — rupa
 * boleh berubah kapan saja — melainkan tiga hal yang kalau rusak tidak
 * mengeluarkan satu pun galat, jadi tidak ada yang akan tahu:
 *
 *   1. tautan kartu layanan yang menunjuk layanan LAIN;
 *   2. paragraf rata kiri-kanan, yang merusak keterbacaan tanpa merusak apa pun;
 *   3. aturan CSS yang kalah bobot dan diam-diam tidak pernah berlaku.
 */
class BerandaTest extends TestCase
{
    use DatabaseTransactions;

    private const TAMPILAN = 'resources/views/public/home.blade.php';

    private function sumber(): string
    {
        return (string) file_get_contents(base_path(self::TAMPILAN));
    }

    #[Test]
    public function berandanya_terbuka(): void
    {
        $this->get('/')->assertOk()->assertSee('Rumah Scopus', false);
    }

    #[Test]
    public function tiap_kartu_layanan_menunjuk_halamannya_sendiri(): void
    {
        /*
         * INI penjaga terpenting di berkas ini.
         *
         * Kartu Clinik Scopus DULU menunjuk /Analisis-Bibliometrik. Bukan
         * salah ketik yang kelihatan: tombolnya tetap berbunyi normal dan
         * tetap membuka halaman yang sah, jadi satu-satunya yang tahu ia
         * salah adalah orang yang menekannya lalu mendapati dirinya memesan
         * layanan yang berbeda — dan orang itu tidak akan mengadu, ia akan
         * pergi.
         *
         * Diperiksa dari HALAMAN YANG TERGAMBAR, bukan dari sumbernya:
         * yang menyesatkan orang adalah alamat yang benar-benar keluar.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        $wajib = [
            'Scopus Camp' => '/Scopus-Camp',
            'Scopus Kafe' => '/Scopus-Kafe',
            'Analisis Bibliometrik' => '/Analisis-Bibliometrik',
            'Clinik Scopus' => '/Clinik-Scopus',
            'Webinar Eksklusif' => '/Webinar-Eksklusif',
        ];

        foreach ($wajib as $nama => $jalur) {
            /*
             * Judul kartunya dan alamatnya harus berada di SATU kartu yang
             * sama. Memeriksa keduanya ada di halaman tidak membuktikan apa
             * pun — alamat /Clinik-Scopus bisa saja menempel di kartu lain,
             * dan itu persis bentuk cacatnya.
             */
            $cocok = preg_match(
                '#<div class="bnd-layanan".*?<h3>' . preg_quote($nama, '#') . '</h3>.*?href="[^"]*'
                    . preg_quote($jalur, '#') . '"#s',
                $isi
            );

            $this->assertSame(1, $cocok,
                'Kartu "' . $nama . '" tidak menunjuk ' . $jalur . '. Tombolnya tetap '
                . 'membuka halaman yang sah, jadi tidak ada galat yang muncul — yang '
                . 'menanggung orang yang memesan layanan yang salah.');
        }
    }

    #[Test]
    public function tidak_ada_paragraf_rata_kiri_kanan(): void
    {
        /*
         * Seluruh paragraf halaman lama memakai text-align: justify. Pada
         * kolom kartu selebar 260px itu melebarkan jarak antar kata sampai
         * hampir dua kali normal, dan membuat sungai putih menurun di tengah
         * paragraf. Tidak ada galat, tidak ada yang rusak — hanya lebih
         * sukar dibaca, setiap kali.
         */
        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '', $this->sumber()
        );

        /*
         * `text-align: justify`, bukan kata "justify" apa adanya.
         *
         * Versi pertama penjaga ini mencari kata itu saja dan langsung
         * merah oleh `justify-content` — properti flexbox yang dipakai
         * belasan kali di halaman yang sama dan tidak ada hubungannya
         * dengan perataan teks.
         */
        $this->assertDoesNotMatchRegularExpression('/text-align\s*:\s*justify/i', $sumber,
            'Paragraf rata kiri-kanan kembali ke beranda. Pada kolom kartu selebar '
            . '260px, itu melebarkan jarak antar kata sampai hampir dua kali normal.');
    }

    #[Test]
    public function jarak_judul_ke_sub_judulnya_tidak_kalah_bobot(): void
    {
        /*
         * `.bnd p { margin: 0 }` berbobot (0,1,1); `.bnd-sub` hanya (0,1,0).
         * Selektor kelas tunggal KALAH, dan margin-top-nya tidak pernah
         * berlaku — terukur 0px di keempat kepala bagian, pada 390px maupun
         * 1440px. Gejalanya hanya terlihat di ponsel, tempat judul dua baris
         * dan kalimat penjelasnya menempel sampai terbaca sebagai satu blok.
         *
         * Yang dijaga bentuk selektornya, sebab akibatnya tidak bisa dibaca
         * dari PHP: CSS tidak dijalankan di sini. Siapa pun yang
         * "merapikan" selektor ini kembali jadi kelas tunggal akan
         * menghidupkan lagi cacat yang sama, diam-diam.
         */
        $sumber = $this->sumber();

        $this->assertStringContainsString('.bnd p.bnd-sub {', $sumber,
            'Selektor jarak sub-judul kehilangan penunjuk elemennya. Tanpa itu ia '
            . 'kalah bobot dari `.bnd p { margin: 0 }` dan jaraknya kembali nol.');

        $this->assertDoesNotMatchRegularExpression('/^\s*\.bnd-sub\s*\{/m', $sumber,
            'Ada lagi aturan `.bnd-sub {` berdiri sendiri; aturan itu kalah bobot '
            . 'dan tidak akan pernah berlaku.');
    }
}
