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
             *
             * Dibatasi dari judul kartu ini sampai judul kartu BERIKUTNYA,
             * bukan dari nama kelas pembungkusnya. Versi pertama penjaga ini
             * mencocokkan `<div class="bnd-layanan"` dan langsung merah saat
             * rupanya dirombak dan kelasnya berganti nama — merah karena
             * perubahan yang tidak ada hubungannya dengan tautan, yaitu
             * bentuk kegagalan yang membuat orang berhenti mempercayai uji.
             */
            $mulai = strpos($isi, '<h3>' . $nama . '</h3>');

            $this->assertNotFalse($mulai,
                'Kartu "' . $nama . '" tidak tergambar di beranda sama sekali.');

            $sesudah = substr($isi, $mulai + strlen($nama) + 9);
            $judulBerikut = strpos($sesudah, '<h3>');
            $petak = $judulBerikut === false ? $sesudah : substr($sesudah, 0, $judulBerikut);

            $this->assertStringContainsString($jalur . '"', $petak,
                'Kartu "' . $nama . '" tidak menunjuk ' . $jalur . '. Tombolnya tetap '
                . 'membuka halaman yang sah, jadi tidak ada galat yang muncul — yang '
                . 'menanggung orang yang memesan layanan yang salah.');
        }
    }

    #[Test]
    public function tiap_foto_slider_hero_benar_benar_ada(): void
    {
        /*
         * Slider hero memuat fotonya lewat data-src, dan gambar yang gagal
         * dimuat lewat jalur itu TIDAK meninggalkan ikon rusak — slidenya
         * cuma kosong, dan slidernya lanjut ke berikutnya seolah tidak
         * terjadi apa-apa. Satu-satunya yang tahu adalah orang yang
         * kebetulan menatap layar pada detik itu.
         *
         * Ketujuh belas berkasnya hasil ubahan dari HEIC dan JPG yang
         * ASLINYA SUDAH DIBUANG. Kalau satu hilang dari repositori, ia tidak
         * bisa dibuat ulang.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        preg_match_all('#assets/img/public/header/(header-\d+\.webp)#', $isi, $cocok);

        $berkas = array_unique($cocok[1]);

        $this->assertGreaterThan(1, count($berkas),
            'Slider hero tidak lagi memuat lebih dari satu foto.');

        foreach ($berkas as $satu) {
            $this->assertFileExists(public_path('assets/img/public/header/' . $satu),
                'Foto hero "' . $satu . '" disebut di halaman tetapi berkasnya tidak ada. '
                . 'Slide-nya akan kosong tanpa satu pun galat, dan berkas aslinya sudah '
                . 'dibuang jadi tidak bisa dibuat ulang.');
        }
    }

    #[Test]
    public function hanya_foto_pertama_yang_dimuat_seketika(): void
    {
        /*
         * Ketujuh belas fotonya berjumlah 3,3 MB. Slide yang tidak aktif
         * tetap berada DI DALAM layar — cuma tembus pandang — jadi
         * loading="lazy" tidak menahannya sama sekali; peramban akan
         * mengunduh semuanya di layar pertama.
         *
         * Karena itu hanya yang pertama punya src; sisanya menunggu giliran
         * lewat data-src. Terukur di peramban: 2 berkas diunduh saat halaman
         * dibuka, bukan 17.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        $langsung = preg_match_all('#<img[^>]*\ssrc="[^"]*header-\d+\.webp#', $isi);
        $tunda = preg_match_all('#data-src="[^"]*header-\d+\.webp#', $isi);

        $this->assertSame(1, $langsung,
            'Lebih dari satu foto hero dimuat seketika; layar pertama menanggung '
            . 'megabita gambar yang belum tentu dilihat.');

        $this->assertGreaterThan(1, $tunda,
            'Foto hero sisanya tidak lagi ditunda lewat data-src.');
    }

    #[Test]
    public function ketiga_rekaman_testimoni_tidak_diunduh_sebelum_ditonton(): void
    {
        /*
         * Ketiganya berjumlah 94 MB, dan ini halaman DEPAN.
         *
         * preload="metadata" saja sudah membuat peramban membuka sambungan
         * ke tiap berkas untuk membaca kepalanya — tiga permintaan ke berkas
         * puluhan megabita, untuk rekaman yang belum tentu ditonton. Satu
         * kata yang berubah di sini tidak merusak apa pun yang kelihatan;
         * yang menanggung kuota pengunjungnya.
         *
         * Sampulnya wajib ada karena preload="none" berarti peramban TIDAK
         * punya bingkai pertama untuk digambar: tanpa poster, ketiganya
         * kotak hitam.
         *
         * Berkasnya sendiri tidak diperiksa di sini — storage/app diabaikan
         * git, jadi uji yang menuntutnya ada akan merah di tiap kloning
         * baru. Yang dijaga bentuk markahnya.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        preg_match_all('#<video[^>]*>#', $isi, $cocok);

        $rekaman = array_values(array_filter($cocok[0],
            fn ($satu) => str_contains($satu, 'poster')));

        $this->assertCount(3, $rekaman,
            'Jumlah rekaman testimoni berubah; beranda seharusnya memakai tiga '
            . 'rekaman yang sama dengan halaman iklan Webinar Eksklusif.');

        foreach ($rekaman as $satu) {
            $this->assertStringContainsString('preload="none"', $satu,
                'Rekaman testimoni tidak lagi preload="none". Ketiganya 94 MB, dan '
                . 'ini halaman depan — yang menanggung kuota pengunjungnya.');

            $this->assertMatchesRegularExpression('#poster="[^"]+\.webp"#', $satu,
                'Rekaman testimoni kehilangan sampulnya. Dengan preload="none" '
                . 'peramban tidak punya bingkai pertama untuk digambar, jadi yang '
                . 'tampil kotak hitam.');
        }

        foreach (['testimoni', 'testimoni2-web', 'testimoni3-web'] as $berkas) {
            $this->assertStringContainsString('storage/video/' . $berkas . '.mp4', $isi,
                'Rekaman "' . $berkas . '" tidak lagi dipakai. Ketiganya sengaja '
                . 'diambil dari tempat yang sama dengan halaman iklan — satu berkas, '
                . 'dua halaman, jadi menggantinya cukup sekali.');
        }
    }

    #[Test]
    public function yang_gratis_tidak_pernah_disebut_berdampingan_dengan_sesi_berbayar(): void
    {
        /*
         * Sesi lima puluh menit adalah Clinik Scopus — layanan BERBAYAR.
         * Yang gratis cuma bertanya ke admin.
         *
         * Ajakan di kaki beranda pernah berbunyi "Ngobrol dulu lima puluh
         * menit juga boleh" tepat di kalimat yang mengajak orang masuk tanpa
         * komitmen. Tidak ada galat, tidak ada yang rusak di layar — yang
         * rusak harapan orangnya, dan yang menanggung admin yang harus
         * menjelaskan bahwa itu produk, bukan sambutan.
         *
         * Dijaga dari JARAK antar-kalimatnya, bukan dari keberadaan
         * katanya: kedua kata itu memang harus tetap ada di halaman ini,
         * hanya tidak boleh bertetangga.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        // Markahnya dibuang dulu: kalimat yang terpotong dua baris di berkas
        // Blade menyatu lagi di sini. Pencarian pada sumbernya memberi
        // negatif palsu justru untuk kalimat yang sedang dicari.
        $teks = (string) preg_replace('/\s+/', ' ', strip_tags($isi));

        preg_match_all('/gratis/i', $teks, $cocok, PREG_OFFSET_CAPTURE);

        $this->assertNotEmpty($cocok[0],
            'Kata "gratis" hilang dari beranda; tawaran bertanya tanpa biaya '
            . 'itu satu-satunya pintu masuk yang tidak menuntut apa pun.');

        foreach ($cocok[0] as [$_, $posisi]) {
            $sekitar = mb_substr($teks, max(0, $posisi - 130), 260);

            $this->assertDoesNotMatchRegularExpression(
                '/lima puluh menit|50 menit/i',
                $sekitar,
                'Sesi lima puluh menit (Clinik Scopus, BERBAYAR) disebut berdampingan '
                . 'dengan kata "gratis". Pembacanya akan mengira konsultasinya tidak '
                . 'berbayar: "' . trim($sekitar) . '"'
            );
        }
    }

    #[Test]
    public function tombol_cerita_alumni_menuju_bagian_testimoni(): void
    {
        /*
         * Tombolnya dulu membuka kanal YouTube di tab lain — mengirim orang
         * KELUAR dari halaman sebelum ia sempat melihat satu pun layanan,
         * padahal rekamannya sudah ada di halaman ini beberapa gulir di
         * bawah.
         *
         * Jangkarnya ikut dijaga: href="#testimoni" yang menunjuk id yang
         * tidak ada TIDAK memberi galat apa pun — tombolnya sekadar tidak
         * melakukan apa-apa, dan tidak ada yang tahu selain orang yang
         * menekannya.
         */
        $isi = (string) $this->get('/')->assertOk()->getContent();

        $cocok = preg_match('#<a\b[^>]*href="\#testimoni"[^>]*>(.*?)</a>#s', $isi, $tangkap);

        $this->assertSame(1, $cocok,
            'Tidak ada tautan menuju #testimoni di beranda.');

        $this->assertStringContainsString('Cerita alumni', $tangkap[1],
            'Tautan #testimoni-nya bukan tombol "Cerita alumni".');

        $this->assertMatchesRegularExpression('/id="testimoni"/', $isi,
            'Tombol "Cerita alumni" menunjuk #testimoni tetapi bagiannya tidak '
            . 'punya id itu. Tombolnya tidak akan melakukan apa-apa, tanpa galat.');

        $this->assertStringNotContainsString('youtube.com/@rumahscopus', $tangkap[0],
            'Tombol "Cerita alumni" kembali membuka YouTube; orangnya dikirim '
            . 'keluar halaman sebelum melihat satu pun layanan.');
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
