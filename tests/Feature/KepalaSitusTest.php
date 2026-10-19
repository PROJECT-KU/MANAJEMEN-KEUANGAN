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
    public function logonya_tidak_diregangkan(): void
    {
        /*
         * Logonya pernah digambar 180x40 padahal berkasnya 1906x470 —
         * terukur nisbah 4,5 lawan 4,055, meregang sekitar sebelas persen.
         *
         * Sebabnya DUA aturan yang sama-sama berlaku: width: 180px di
         * atribut style=, dan max-height: 40px dari templat. Keduanya
         * dituruti, jadi peramban mengisi kotak 180x40 dan nisbahnya
         * dibuang.
         *
         * Tidak ada galat, dan tidak ada satu pun yang bisa ditunjuk
         * sebagai "rusak" — logo teregang cuma terbaca "dipaksa", dan
         * itulah kata yang dipakai pemiliknya.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        $cocok = preg_match('#<a[^>]*class="logo[^"]*"[^>]*>\s*<img[^>]*>#s', $isi, $tangkap);

        $this->assertSame(1, $cocok, 'Gambar logo di kepala situs tidak ditemukan.');

        $this->assertDoesNotMatchRegularExpression('/style\s*=\s*"[^"]*width/i', $tangkap[0],
            'Logo kepala situs kembali mematok lebarnya di atribut style=. Bersama '
            . 'max-height dari templat, keduanya memaksa gambarnya ke kotak yang '
            . 'nisbahnya berbeda dari berkasnya.');

        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
            (string) file_get_contents(base_path(self::LAYOUT))
        );

        $this->assertMatchesRegularExpression(
            '/#header \.logo img \{[^}]*width:\s*auto[^}]*\}/s', $sumber,
            'Lebar logo bukan lagi auto; nisbahnya tidak lagi dijamin utuh.');

        $this->assertMatchesRegularExpression(
            '/#header \.logo img \{[^}]*max-height:\s*none[^}]*\}/s', $sumber,
            'max-height: none dicabut. Templat memasang max-height: 40px, dan tanpa '
            . 'pembatalan itu tinggi 46px yang kita minta kalah oleh batas atasnya — '
            . 'gambarnya mengerut lagi tanpa satu pun galat.');
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

    // ------------------------------------------------------------ kaki situs

    /** Potongan HTML di antara <footer> dan </footer> pada satu halaman. */
    private function kaki(string $jalur = '/'): string
    {
        $isi = (string) $this->get($jalur)->assertOk()->getContent();

        $cocok = preg_match('#<footer\b.*?</footer>#s', $isi, $tangkap);

        $this->assertSame(1, $cocok, 'Halaman ' . $jalur . ' tidak punya <footer>.');

        return $tangkap[0];
    }

    #[Test]
    public function tiap_tautan_di_kaki_halaman_benar_benar_sampai(): void
    {
        /*
         * Butir "Contact" di kaki halaman menunjuk /blog/contact — dan itu
         * 404. Rutenya /contact.
         *
         * Kaki halaman ini ikut di SETIAP halaman umum, jadi semua orang yang
         * mencari cara menghubungi dari bawah halaman mendarat di halaman
         * kosong. Tidak ada galat di log, tidak ada yang mengadu: orang yang
         * gagal menghubungi tidak menghubungi untuk memberi tahu bahwa ia
         * gagal menghubungi. Ia pergi.
         *
         * Dijaga dengan BENAR-BENAR membuka tiap tautannya, bukan dengan
         * mencocokkan daftar alamat: daftar yang dicocokkan ikut salah
         * begitu rutenya berganti nama.
         */
        preg_match_all('/href="([^"#][^"]*)"/', $this->kaki(), $cocok);

        $dalam = array_values(array_unique(array_filter(
            $cocok[1],
            fn ($t) => str_starts_with($t, url('/')) || str_starts_with($t, '/')
        )));

        $this->assertNotEmpty($dalam, 'Kaki halaman tidak punya satu pun tautan ke dalam situs.');

        $gagal = [];

        foreach ($dalam as $tautan) {
            $jalur = '/' . ltrim((string) parse_url($tautan, PHP_URL_PATH), '/');
            $kode = $this->get($jalur)->getStatusCode();

            if ($kode >= 400) {
                $gagal[] = $jalur . ' -> ' . $kode;
            }
        }

        $this->assertSame([], $gagal,
            "Tautan di kaki halaman ini tidak sampai ke mana pun:\n- "
            . implode("\n- ", $gagal));
    }

    #[Test]
    public function kaki_halaman_tidak_punya_tautan_buntu(): void
    {
        /*
         * Kolom "Layanan Kami" dulu berisi LIMA butir yang semuanya
         * href="#": Class Online, Class Offline, Scopus Kafe, Online
         * Training, Meeting Room. Diklik, tidak terjadi apa-apa.
         *
         * Lebih buruk daripada 404: halaman tidak berganti sama sekali, jadi
         * orangnya mengira kliknya yang meleset dan mencoba lagi.
         *
         * Di kepala situs href="#" masih sah — pembuka submenu "Class"
         * memang begitu — tetapi di kaki halaman tidak ada submenu apa pun.
         */
        preg_match_all('/<a\b[^>]*href="#"[^>]*>(.*?)<\/a>/s', $this->kaki(), $cocok);

        $buntu = array_map(fn ($t) => trim((string) preg_replace('/\s+/', ' ', strip_tags($t))), $cocok[1]);

        $this->assertSame([], $buntu,
            "Butir kaki halaman ini tidak mengarah ke mana pun:\n- "
            . implode("\n- ", $buntu));
    }

    #[Test]
    public function kaki_halaman_memakai_bahasa_rupa_yang_sama(): void
    {
        /*
         * Kakinya sempat TIDAK ikut dirombak, dan selisihnya terukur:
         * latarnya rgb(246, 249, 255) — biru-putih dingin — sementara
         * beranda krem hangat #fdf5e8; hurufnya Open Sans + Nunito sementara
         * beranda Fraunces + Figtree. Halamannya berakhir dengan rupa situs
         * yang berbeda, tepat di bawah ajakan terakhirnya.
         *
         * Yang dijaga bukan warnanya — warna boleh berganti — melainkan
         * BOBOT pemilihnya. Aturan bawaan templat sedalam
         * `.footer .footer-top .footer-links ul a` berbobot (0,4,1); timpaan
         * berkelas saja kalah dan tidak berlaku sama sekali, tanpa galat,
         * dan kakinya diam-diam kembali ke rupa lamanya.
         */
        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
            (string) file_get_contents(base_path(self::LAYOUT))
        );

        $this->assertMatchesRegularExpression('/#footer \{[^}]*background:/s', $sumber,
            'Kaki halaman tidak lagi punya aturan latar ber-#footer; ia akan memakai '
            . 'latar bawaan templat yang warnanya dari palet lama.');

        $this->assertMatchesRegularExpression("/#footer \{[^}]*font-family:[^;]*Figtree/s", $sumber,
            'Kaki halaman tidak lagi memaksa hurufnya; ia akan kembali ke Open Sans '
            . 'bawaan templat sementara seluruh halaman memakai Figtree.');

        /*
         * Timpaan yang MENANDINGI aturan templat harus ber-#footer. Yang
         * tidak punya tandingan — kelas milik kita sendiri seperti
         * .kaki-wa — boleh berkelas saja.
         */
        $telanjang = [];

        foreach (['.footer-top', '.footer-links', '.footer-info', '.copyright', '.social-links'] as $bagian) {
            if (preg_match('/(?<![\w#-])\.footer\s+' . preg_quote($bagian, '/') . '\b/', $sumber)) {
                $telanjang[] = '.footer ' . $bagian;
            }
        }

        $this->assertSame([], $telanjang,
            "Timpaan kaki halaman ini ditulis tanpa awalan #footer dan akan kalah "
            . "bobot melawan aturan bawaan templat:\n- " . implode("\n- ", $telanjang));
    }

    #[Test]
    public function tiap_halaman_publik_punya_jalan_ke_whatsapp(): void
    {
        /*
         * Seluruh halaman umum dulu tidak punya SATU PUN tautan WhatsApp —
         * terukur nol — padahal itu jalur yang benar-benar dipakai
         * pelanggan, dan nomornya sudah lama ada di config/panitia.php.
         *
         * Alamatnya WAJIB api.whatsapp.com, BUKAN wa.me: lewat wa.me emoji
         * dan sebagian tanda baca sampai dalam keadaan rusak, dan pengirimnya
         * tidak pernah tahu karena di layarnya sendiri tampak baik-baik saja.
         * Alasan lengkapnya di App\Support\TautanWa.
         */
        foreach (['/', '/blog', '/contact', '/Scopus-Camp'] as $jalur) {
            $isi = (string) $this->get($jalur)->assertOk()->getContent();

            $this->assertStringContainsString('api.whatsapp.com/send?phone=', $isi,
                'Halaman ' . $jalur . ' kehilangan tautan WhatsApp-nya.');

            $this->assertStringNotContainsString('wa.me/', $isi,
                'Halaman ' . $jalur . ' memakai wa.me. Lewat alamat itu emoji dan '
                . 'tanda baca pesannya sampai dalam keadaan rusak tanpa tanda apa pun.');
        }
    }
}
