<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Semua templat surat memakai logo yang sama, dan memakainya dengan cara
 * yang sama.
 *
 * Sebelum uji ini dipasang ada dua logo berbeda: delapan templat memakai
 * logo-email.png, enam belas lainnya masih LogoRSC.png. Satu di antaranya
 * bahkan memasangnya lewat asset() — tautan ke APP_URL, yang di sebagian
 * besar aplikasi surat diblokir dan di sini menunjuk http://localhost.
 */
class LogoSuratSeragamTest extends TestCase
{
    private const LOGO = 'assets/img/logo-email.png';

    /** Logo yayasan ukuran surat — lihat SURAT_YAYASAN. */
    private const LOGO_YAYASAN = 'assets/img/logo-rsc-email.png';

    /**
     * Logo yayasan ukuran SITUS. Tidak boleh dipakai surat mana pun.
     *
     * Mereknya sama dengan LOGO_YAYASAN — keduanya dibuka berdampingan,
     * tata letaknya identik — tetapi berkasnya 1906 x 470 piksel dan di
     * peladen 121.370 bita, sementara yang ukuran surat 440 x 108 piksel
     * dan 15.735 bita. Dikirim untuk slot selebar 210 piksel, selisihnya
     * hampir delapan kali berat surat tanpa satu piksel pun bedanya.
     */
    private const LOGO_SITUS = 'assets/img/LogoRSC.png';

    /**
     * Surat yang sengaja memakai logo YAYASAN, bukan logo alat.
     *
     * logo-email.png adalah logo MIS — "Management Integration System by
     * Rumah Scopus" — dan itu memang pantas untuk surat akun dan keamanan,
     * yang pembacanya orang dalam.
     *
     * Surat di daftar ini dibaca PENDAFTAR, bukan orang dalam. Mengirimi
     * calon peserta logo alat administrasi internal menyampaikan merek yang
     * salah. Ditetapkan 3 Okt 2026 atas permintaan pemilik produk sesudah
     * melihat suratnya di kotak masuk.
     *
     * DAFTARNYA DULU BERISI SATU, padahal empat surat memakai logo yayasan.
     * Ketiga yang lain lolos karena penjaganya mencari kata "LogoRSC" saja,
     * dan ketiganya memakai berkas ukuran surat yang namanya tidak memuat
     * kata itu — jadi daftar yang mengaku tertutup sebenarnya bocor, dan
     * surat pendaftar baru bisa memakai logo apa pun tanpa tertangkap.
     *
     * Daftar ini sengaja berupa daftar tertutup, bukan pengecualian terbuka:
     * surat baru tetap memakai logo alat kecuali ada yang menuliskannya di
     * sini beserta alasannya.
     */
    private const SURAT_YAYASAN = [
        'emails/pendaftaran-dicatat.blade.php',
        'emails/pendaftaran/kerangka.blade.php',
        'public/webinar_eksklusif/email_pendaftaran.blade.php',
        'public/webinar_eksklusif/email_pengingat.blade.php',

        /*
         * Kabar "batas bayar lewat, kursi Anda dilepas". Pembacanya orang
         * yang baru saja kehilangan kursinya dan sebagian masih mau ikut —
         * jadi suratnya harus terbaca datang dari penyelenggaranya, bukan
         * dari alat administrasi yang namanya tidak pernah ia dengar.
         *
         * Ditambahkan 18 Okt 2026, atas permintaan pemilik produk yang
         * meminta surat ini "dari Rumah Scopus, bukan logo MIS".
         */
        'public/webinar_eksklusif/email_kedaluwarsa.blade.php',

        /*
         * Surat "pembayaran diterima" berikut kwitansinya. Pembacanya
         * pendaftar, dan lampirannya kwitansi yang sebagian orang serahkan
         * ke bendahara kantornya — jadi mereknya harus merek yayasan, baik
         * di suratnya maupun di kwitansinya.
         */
        'public/webinar_eksklusif/email_lunas.blade.php',

        /* Kabar pembayaran yang tidak berhasil. Pembacanya pendaftar yang
           baru saja gagal membayar dan sebagian masih mau ikut — surat
           bermerek asing di saat begitu dihapus tanpa dibuka. */
        'public/webinar_eksklusif/email_gagal_bayar.blade.php',
    ];

    /** Berkas yang isinya surat, bukan halaman atau faktur. */
    private function templatSurat(): array
    {
        $akar = dirname(__DIR__, 2) . '/resources/views';
        $berkas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar));
        $hasil = [];

        foreach ($berkas as $b) {
            if (! $b->isFile() || ! str_ends_with($b->getFilename(), '.blade.php')) {
                continue;
            }

            $relatif = str_replace($akar . '/', '', $b->getPathname());
            $nama = $b->getFilename();

            // Faktur dan tata letak halaman bukan surat, walau namanya mirip.
            if (str_contains($relatif, 'invoice') || str_contains($relatif, '/layout/')) {
                continue;
            }

            if (str_contains($nama, 'mail') || str_contains($nama, 'email') || str_contains($relatif, 'emails/')) {
                $hasil[$relatif] = (string) file_get_contents($b->getPathname());
            }
        }

        return $hasil;
    }

    /**
     * Logo yang DIPASANG, bukan yang disebut.
     *
     * Dicocokkan pada pemanggilan asset()-nya, bukan pada ada-tidaknya nama
     * berkasnya di dalam isi. Nama berkas muncul juga di komentar yang
     * menerangkan mengapa berkas itu TIDAK dipakai — dan penjaga yang
     * membaca kata apa adanya akan menyatakan surat itu memakainya.
     *
     * @return list<string>
     */
    private function logoTerpasang(string $isi): array
    {
        preg_match_all("/aset_surat\\('(assets\\/img\\/[^']+)'\\)/", $isi, $cocok);

        return array_values(array_unique($cocok[1]));
    }

    public function test_surat_pendaftar_memakai_logo_yayasan_ukuran_surat(): void
    {
        foreach (self::SURAT_YAYASAN as $relatif) {
            $isi = $this->templatSurat()[$relatif] ?? null;

            $this->assertNotNull($isi,
                $relatif . ' terdaftar sebagai surat pendaftar tetapi berkasnya tidak ada.');

            $this->assertContains(self::LOGO_YAYASAN, $this->logoTerpasang($isi),
                $relatif . ' terdaftar sebagai surat untuk pendaftar, jadi logonya '
                . 'logo yayasan ukuran surat.');
        }
    }

    public function test_tidak_ada_templat_surat_yang_memakai_logo_lama(): void
    {
        $melenceng = [];
        $berat = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            $terpasang = $this->logoTerpasang($isi);

            // Logo ukuran situs tidak pantas di surat mana pun, termasuk
            // surat pendaftar yang memang berlogo yayasan.
            if (in_array(self::LOGO_SITUS, $terpasang, true)) {
                $berat[] = $relatif;
            }

            if (in_array($relatif, self::SURAT_YAYASAN, true)) {
                continue;
            }

            if (in_array(self::LOGO_YAYASAN, $terpasang, true)) {
                $melenceng[] = $relatif;
            }
        }

        $this->assertSame([], $berat,
            "Templat surat berikut memakai logo ukuran situs (121.370 bita untuk "
            . "slot 210 piksel):\n- " . implode("\n- ", $berat) . "\n");

        $this->assertSame([], $melenceng,
            "Templat surat berikut memakai logo yayasan tanpa terdaftar di "
            . "SURAT_YAYASAN:\n- " . implode("\n- ", $melenceng) . "\n");
    }

    /**
     * DIBALIK 5 Okt 2026. Sebelumnya logonya wajib DISISIPKAN.
     *
     * Alasan lamanya dua, dan satu sudah tidak berlaku:
     *
     *   1. "APP_URL masih http://localhost" — benar saat ditulis, sebab
     *      aplikasinya belum pernah dideploy. Sekarang APP_URL di peladen
     *      https://rumahscopusfoundation.com/ dan asset() diperiksa langsung
     *      di sana menghasilkan alamat https yang benar.
     *
     *   2. "aplikasi surat kebanyakan memblokir gambar jarak jauh" — masih
     *      benar untuk sebagian klien, tetapi Gmail memuatnya sendiri lewat
     *      proxy, dan Gmail yang dipakai penerima suratnya.
     *
     * Yang membuatnya dibalik: disisipkan, Gmail memasang KEPING LAMPIRAN
     * "logo-email.png" pada surat atur ulang kata sandi. Lampiran tak dikenal
     * pada surat keamanan justru hal yang diajarkan untuk dicurigai.
     *
     * Suratnya sendiri sah — diperiksa langsung: multipart/related,
     * Content-Disposition: inline, Content-ID-nya memang dirujuk HTML-nya.
     * Gmail tetap memasang kepingnya karena gambarnya membawa nama berkas.
     *
     * Ongkosnya terukur pada surat atur ulang kata sandi:
     *
     *     disisipkan  81.744 bita
     *     ditautkan    5.982 bita
     *
     * Yang ditukar: klien yang memblokir gambar jarak jauh menampilkan
     * logonya kosong sampai diizinkan. Itu diterima; logo bukan isi surat.
     */
    public function test_logo_surat_ditautkan_bukan_disisipkan(): void
    {
        $melenceng = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            if (str_contains($isi, 'message->embed(')) {
                $melenceng[] = $relatif;
            }
        }

        $this->assertSame([], $melenceng,
            "Templat surat berikut menyisipkan gambarnya sebagai berkas; Gmail akan "
            . "memasang keping lampiran di surat yang memakainya:\n- "
            . implode("\n- ", $melenceng) . "\n");
    }

    public function test_logo_surat_dipasang_lewat_helper_alamat(): void
    {
        $melenceng = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            if (! str_contains($isi, self::LOGO)) {
                continue;
            }

            if (! str_contains($isi, "aset_surat('" . self::LOGO . "')")) {
                $melenceng[] = $relatif;
            }
        }

        $this->assertSame([], $melenceng,
            "Templat surat berikut tidak memasang logonya lewat aset_surat():\n- "
            . implode("\n- ", $melenceng) . "\n");

        // Alamatnya tidak ada gunanya kalau berkasnya sendiri tidak terbit.
        $this->assertFileExists(
            dirname(__DIR__, 2) . '/public/' . self::LOGO,
            'Berkas logonya tidak ada di public/, jadi alamatnya akan 404 di surat.'
        );
    }

    /**
     * TIDAK ADA gambar surat yang alamatnya dirakit asset().
     *
     * asset() merakit alamatnya dari APP_URL. Di lingkungan lokal APP_URL
     * berbunyi http://localhost, dan Gmail mengambil gambar lewat proksinya
     * sendiri di peladen Google — tempat "localhost" berarti mesin Google.
     * Permintaannya gagal, dan yang tergambar di kotak masuk ikon rusak.
     *
     * Terukur dua kali pada surat uji yang dikirim panitia untuk memeriksa
     * rupanya: surat pendaftaran logonya rusak, lalu surat perubahan status
     * logonya DAN ketiga ikon media sosialnya rusak. Peladennya sendiri
     * tidak bermasalah — APP_URL di sana sudah alamat situsnya — tetapi
     * surat uji justru yang dipakai memutuskan rupa suratnya.
     *
     * Dijaga per templat, bukan per logo: ikon media sosial di kaki surat
     * pun gambar, dan ketiganya ikut rusak oleh sebab yang sama.
     */
    public function test_tidak_ada_gambar_surat_yang_memakai_asset(): void
    {
        $melenceng = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            if (preg_match_all("/asset\\('(assets\\/img\\/[^']+)'\\)/", $isi, $cocok) > 0) {
                $melenceng[] = $relatif . ': ' . implode(', ', array_unique($cocok[1]));
            }
        }

        $this->assertSame([], $melenceng,
            "Templat surat berikut merakit alamat gambarnya dari APP_URL; di "
            . "lokal alamatnya http://localhost dan gambarnya rusak di kotak "
            . "masuk:\n- " . implode("\n- ", $melenceng) . "\n");
    }

    /**
     * Alamat gambar suratnya alamat PUBLIK, bukan localhost.
     *
     * Pembantunya bisa saja dipasang tetapi config-nya menunjuk alamat yang
     * tidak dapat dijangkau siapa pun — dan gejalanya persis sama dengan
     * sebelum diperbaiki.
     */
    public function test_alamat_gambar_suratnya_dapat_dijangkau_umum(): void
    {
        /*
         * Dibaca sebagai TEKS. Uji ini kelas PHPUnit polos tanpa wadah
         * aplikasi: config() melempar BindingResolutionException, dan
         * me-require berkasnya pun gagal sebab isinya memanggil
         * resource_path(). Keduanya merah karena alasan yang sama sekali
         * bukan alamatnya.
         *
         * Yang diperiksa NILAI BAWAANNYA — yang berlaku saat .env tidak
         * menyetelnya, dan itulah keadaan di hampir semua mesin.
         */
        $isi = (string) file_get_contents(dirname(__DIR__, 2) . '/config/mail.php');

        $this->assertSame(1,
            preg_match("/'aset_url' => env\\('MAIL_ASSET_URL', '([^']+)'\\)/", $isi, $cocok),
            'config/mail.php tidak punya aset_url berikut nilai bawaannya.');

        $alamat = $cocok[1];

        $this->assertMatchesRegularExpression('#^https://[a-z0-9.-]+\\.[a-z]{2,}#i', $alamat,
            'Alamat gambar surat bukan alamat https publik: ' . $alamat);

        $this->assertStringNotContainsString('localhost', $alamat);
    }

    public function test_berkas_logonya_memang_ada(): void
    {
        $this->assertFileExists(dirname(__DIR__, 2) . '/public/' . self::LOGO);
        $this->assertFileExists(dirname(__DIR__, 2) . '/public/' . self::LOGO_YAYASAN);
    }
}
