<?php

namespace Tests\Feature\PendaftaranLayanan;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Yang menutupi data di layar ponsel.
 *
 * Terukur di 390x844 sebelum perbaikan ini: kepala halaman 226px, ubin 93px,
 * baris angka 176px, ringkasan penyaring 42px — kartu pendaftar pertama baru
 * mulai di 773px dari 844px layar. Panitia membuka daftarnya dan tidak
 * melihat satu pendaftar pun sebelum menggulir.
 *
 * Yang dijaga di sini aturannya, bukan angkanya: ukuran piksel tidak bisa
 * dibaca dari sumber. Tetapi tiap aturan di bawah ini punya angka
 * pengukurnya sendiri di komentar berkasnya.
 */
class LayarPonselPendaftarTest extends TestCase
{
    private function gaya(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/index.blade.php')
        );
    }

    private function bersama(): string
    {
        return file_get_contents(public_path('assets/css/mis-ui.css'));
    }

    /**
     * Ringkasan dilipat di ponsel — 269px (ubin + angka) jadi 42px.
     *
     * Dilipat, BUKAN dibuang: ubinnya sekaligus pintasan saringan, dan
     * angka pokoknya tetap tertulis di ringkasan lipatannya.
     */
    #[Test]
    public function ringkasan_dilipat_di_ponsel(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/<details[^>]*class="mis-lipat pdl-lipat"[^>]*data-mis-lipat/', $isi,
            'Ringkasan tidak lagi dilipat; ubin dan baris angkanya kembali '
            . 'menutupi layar pertama sebelum ada satu pendaftar pun.'
        );

        $this->assertStringContainsString('pdl-lipat-angka', $isi,
            'Angka pokoknya hilang dari ringkasan lipatan, jadi yang paling '
            . 'sering dicari menuntut satu ketukan lagi.');
    }

    /**
     * mis-ui.js memaksa <details data-mis-lipat> terbuka di >= 768px. Tanpa
     * penanda itu, layar lebar ikut terlipat — dan di sana tidak ada
     * masalah ruang sama sekali.
     */
    #[Test]
    public function layar_lebar_tidak_ikut_terlipat(): void
    {
        $this->assertStringContainsString("matchMedia('(min-width: 768px)')",
            file_get_contents(public_path('assets/js/mis-ui.js')),
            'Pemaksa buka di layar lebar hilang; ringkasan ikut terlipat di desktop.');
    }

    /**
     * Tiga tombol sekunder di kepala halaman: satu baris, ikon saja, hanya
     * di lebar ponsel. Bertumpuk, ketiganya membuat kepala halaman 278px.
     */
    #[Test]
    public function tombol_kepala_tidak_bertumpuk_di_ponsel(): void
    {
        $this->assertStringContainsString('mis-kepala-aksi-trio', $this->gaya(),
            'Kepala halaman kembali menumpuk tombolnya.');

        $this->assertMatchesRegularExpression(
            '/\.mis-kepala-aksi-trio \.mis-tombol-halus \.mis-tombol-teks\s*\{[^}]*position:\s*absolute/s',
            $this->bersama(),
            'Tulisan tombolnya tidak disembunyikan, jadi ketiganya tidak muat sebaris.'
        );
    }

    /**
     * Ringkasan yang DIBUKA tidak menuntut geser menyamping lagi.
     *
     * Barisan geser masuk akal untuk ringkasan yang selalu terpampang — ia
     * menahan tinggi layar. Di dalam lipatan aturannya terbalik: orang baru
     * saja menekan "Ringkasan" untuk melihat angkanya, lalu yang terbuka
     * cuma satu ubin penuh dengan empat lainnya di luar layar. Terukur di
     * 390px sebelum ini dipasang: lebar tampak 362px, lebar isi 1145px —
     * empat dari lima ubin terpotong.
     */
    #[Test]
    public function ringkasan_terbuka_tidak_perlu_digeser_di_ponsel(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.pdl-lipat \.mis-ringkas\s*\{[^}]*display:\s*grid/s', $isi,
            'Ubin di dalam lipatan kembali jadi barisan geser; empat dari lima '
            . 'angkanya di luar layar padahal panelnya sengaja dibuka.'
        );

        /*
         * Penjaga bobotnya, bukan cuma ada-tidaknya aturannya.
         *
         * Penanda .bisa-geser dipasang mis-ui.js, dan aturan yang
         * memunculkan petunjuknya berbobot (0,3,0). Ditulis lebih rendah,
         * baris "Geser untuk lihat semua" tetap terpampang di bawah kisi
         * yang tidak bisa digeser ke mana pun — dan tidak ada galat apa pun
         * yang menandainya.
         */
        $this->assertMatchesRegularExpression(
            '/\.pdl-lipat \.mis-ringkas-geser\.bisa-geser \.mis-ringkas-petunjuk\s*\{[^}]*display:\s*none/s',
            $isi,
            'Petunjuk geser dipadamkan dengan bobot yang lebih rendah daripada '
            . 'aturan yang memunculkannya, jadi ia tetap terbaca.'
        );
    }

    /**
     * Kartu Ringkasan dan kartu Cari & saring tidak bersentuhan.
     *
     * Terukur di 390px: 0px saat tertutup — kedua bilahnya benar-benar
     * menempel dan terbaca seperti satu kotak yang terbelah — dan 12px saat
     * terbuka, yang itu pun datang dari margin baris angkanya, bukan dari
     * kartunya sendiri.
     */
    #[Test]
    public function kartu_ringkasan_punya_jarak_ke_kartu_berikutnya(): void
    {
        /*
         * Diambil dari blok ponselnya saja. Di lebar lain kedua kartu ini
         * tidak berdempetan sama sekali, dan aturan yang dicari memang
         * hanya berlaku di sana.
         */
        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s',
            $this->gaya(), $blok), 'Blok gaya ponsel halaman ini tidak ada.');

        $this->assertSame(1,
            preg_match('/\.pdl-lipat\s*\{[^}]*margin-bottom:\s*(\d+)px/s', $blok[1], $cocok),
            'Kartu Ringkasan tidak punya jarak ke kartu di bawahnya; keduanya '
            . 'bersentuhan di ponsel — terukur 0px saat tertutup.');

        $this->assertGreaterThanOrEqual(10, (int) $cocok[1],
            'Jaraknya terlalu rapat untuk membedakan kedua kartu.');
    }

    /**
     * Kotak angka yang bisa ditekan TIDAK bergaris bawah saat disentuh.
     *
     * Dua di antaranya <a> — "menunggu lebih dari 7 hari" dan "status belum
     * dikenali" — dan Bootstrap, yang ikut dimuat halaman ini, menyetel
     * "a:hover { color: #0056b3; text-decoration: underline }". Bobotnya
     * (0,1,1) melawan ".pdl-uang-total > *" yang (0,1,0), jadi disentuh
     * kursor kotaknya berubah jadi tulisan biru bergaris bawah di tengah
     * deretan kotak yang tidak.
     *
     * Terukur lewat DevTools Protocol dengan :hover dipaksa, sebelum dan
     * sesudah: garis "underline" warna rgb(23,86,156) jadi garis "none"
     * dengan warna tetap rgb(71,85,105).
     *
     * Berlaku di SEMUA lebar: aturannya sengaja di luar media query, sebab
     * kursor justru ada di layar lebar.
     */
    #[Test]
    public function kotak_angka_tidak_bergaris_bawah_saat_disentuh(): void
    {
        $isi = $this->gaya();

        $this->assertSame(1, preg_match(
            '/((?:\.pdl-uang-total a(?::[a-z-]+)?,\s*)+\.pdl-uang-total a:[a-z-]+)\s*\{([^}]*)\}/s',
            $isi, $cocok
        ), 'Tidak ada aturan yang mematikan garis bawah kotak angka.');

        $this->assertStringContainsString('text-decoration: none', $cocok[2]);

        /*
         * Keadaan :hover WAJIB ikut disebut namanya.
         *
         * Mematikannya pada ".pdl-uang-total a" saja berbobot (0,1,1) —
         * SERI dengan "a:hover" milik Bootstrap, dan yang seri dimenangkan
         * yang terbaca belakangan. Bootstrap dimuat lebih dulu di halaman
         * ini, tetapi urutan itu bukan sesuatu yang dijaga siapa pun.
         */
        $this->assertStringContainsString('.pdl-uang-total a:hover', $cocok[1],
            'Garis bawahnya tidak dimatikan pada keadaan :hover, jadi bobotnya '
            . 'cuma seri dengan aturan Bootstrap dan bergantung pada urutan muat.');

        // Warnanya ikut dijaga: Bootstrap mengubahnya jadi biru di aturan
        // yang sama, dan kotak yang berubah warna tetap terbaca seperti
        // tautan walau garisnya sudah hilang.
        $this->assertStringContainsString('color: inherit', $cocok[2]);

        // Rupa tekannya tetap ada, kalau tidak kotaknya jadi tidak memberi
        // tanda apa pun bahwa ia bisa ditekan.
        $this->assertMatchesRegularExpression(
            '/\.pdl-uang-total a:hover,\s*\.pdl-uang-total a:focus-visible\s*\{[^}]*(box-shadow|transform)/s',
            $isi,
            'Kotaknya tidak lagi memberi tanda apa pun saat disentuh.'
        );
    }

    /**
     * Kotak angka yang ganjil terakhir MEMBENTANG dua lajur.
     *
     * Jumlahnya dua sampai empat tergantung isi pembukuannya. Ganjil, yang
     * terakhir berdiri sendiri di lajur kiri dan separuh barisnya menganga —
     * terukur: kotak "menunggu lebih dari 7 hari" selebar 177px dengan 177px
     * kosong di sebelahnya.
     */
    #[Test]
    public function kotak_angka_ganjil_terakhir_membentang_penuh(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.pdl-uang-total > \*:last-child:nth-child\(odd\)\s*\{[^}]*grid-column:\s*1 \/ -1/s',
            $this->gaya(),
            'Kotak angka terakhir kembali menyisakan petak kosong di sebelahnya '
            . 'saat jumlahnya ganjil.'
        );
    }

    /**
     * Keempat tombol sekunder MENYEBUT dirinya di ponsel.
     *
     * Nama panjangnya memang disembunyikan supaya keempatnya muat sebaris,
     * tetapi sempat disembunyikan tanpa pengganti — dan yang tersisa empat
     * kotak berisi ikon saja. Admin layar ini bukan orang teknis: berkas
     * hijau, papan jepit, berkas merah, dan tong sampah tidak menyebut
     * Excel, presensi, PDF, atau arsip kepada siapa pun yang belum pernah
     * menekannya.
     *
     * aria-label tidak menutup lubang ini — itu hanya terbaca pembaca
     * layar — dan title tidak pernah muncul di ponsel, sebab di sana tidak
     * ada kursor yang bisa berhenti di atas tombol.
     */
    #[Test]
    public function tombol_kepala_menyebut_namanya_di_ponsel(): void
    {
        $isi = $this->gaya();

        /*
         * Dicocokkan pada BLOK AKSINYA, bukan seluruh halaman: kelas yang
         * sama muncul juga di gaya dan skrip halaman ini, dan penjaga yang
         * membaca seluruh berkas tetap hijau walau tombolnya sendiri sudah
         * kehilangan namanya.
         */
        $this->assertSame(1, preg_match('/<div class="mis-kepala-aksi[^"]*" id="pdl-aksi">(.*?)<\/div>\s*<\/div>/s',
            $isi, $cocok), 'Blok aksi kepala halaman tidak ditemukan.');

        $blok = $cocok[1];

        preg_match_all('/class="mis-tombol mis-tombol-halus[^"]*"/', $blok, $tombol);
        preg_match_all('/class="mis-tombol-ringkas"[^>]*>([^<]+)</', $blok, $nama);

        /*
         * Dipadankan satu lawan satu, BUKAN dihitung ke angka tetap.
         *
         * Tombol presensi ditulis dua kali — bentuk hidup dan bentuk mati
         * — jadi markahnya memuat lima tombol untuk empat yang terender.
         * Angka tetap di sini berarti penjaganya merah setiap kali ada
         * tombol bersyarat baru, dan itu bukan cacat yang mau dijaga.
         */
        $this->assertGreaterThanOrEqual(4, count($tombol[0]),
            'Tombol sekunder di kepala halaman hilang.');

        $this->assertCount(count($tombol[0]), $nama[1],
            'Ada tombol sekunder tanpa nama pendek; di ponsel ia jadi kotak '
            . 'berisi ikon saja.');

        foreach ($nama[1] as $teks) {
            $this->assertLessThanOrEqual(9, mb_strlen(trim($teks)),
                'Nama pendek "' . trim($teks) . '" terlalu panjang; di 390px '
                . 'tiap tombol cuma selebar 75px.');
        }
    }

    /**
     * Nama pendeknya HANYA di ponsel, dan nama panjangnya tetap di lebar lain.
     *
     * Keduanya tampil bersamaan berarti tombolnya berbunyi "Unduh Excel
     * Excel" — persis yang terlihat saat gayanya belum dipasang.
     */
    #[Test]
    public function nama_pendek_tombol_hanya_muncul_di_ponsel(): void
    {
        $css = $this->bersama();

        $this->assertMatchesRegularExpression(
            '/\.mis-tombol-ringkas\s*\{[^}]*display:\s*none/s', $css,
            'Nama pendeknya tidak disembunyikan di lebar biasa, jadi tombolnya '
            . 'berbunyi "Unduh Excel Excel" di desktop.'
        );

        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 575\.98px\)[^@]*\.mis-kepala-aksi-trio \.mis-tombol-halus \.mis-tombol-ringkas\s*\{[^}]*display:\s*block/s',
            $css,
            'Nama pendeknya tidak dimunculkan di lebar ponsel, jadi tombolnya '
            . 'tetap ikon telanjang di sana.'
        );

        // Ikon di atas, nama di bawahnya. Berdampingan, nama 75px tidak muat
        // di sebelah ikonnya dan tombolnya meluber.
        $this->assertMatchesRegularExpression(
            '/\.mis-kepala-aksi-trio \.mis-tombol-halus\s*\{[^}]*flex-direction:\s*column/s',
            $css,
            'Ikon dan namanya kembali berdampingan; di 75px keduanya tidak muat.'
        );
    }

    /**
     * Tombol yang tulisannya disembunyikan WAJIB punya aria-label: tanpa
     * itu ia jadi tombol tanpa nama bagi pembaca layar.
     */
    #[Test]
    public function tombol_ikon_tetap_punya_namanya(): void
    {
        $isi = $this->gaya();

        /*
         * Tiap <a ...> diambil utuh lalu diperiksa isinya, bukan dicocokkan
         * dengan satu pola panjang. Pola yang mengandaikan urutan atribut
         * dan letak ganti baris pecah pada perubahan yang tidak ada
         * hubungannya — dan merahnya menuding aksesibilitas, padahal yang
         * berubah cuma pembungkusan barisnya.
         */
        preg_match_all('/<a\s[^>]*>/s', $isi, $cocok);

        foreach (['excel', 'pdf', 'terhapus'] as $tombol) {
            $tag = array_values(array_filter(
                $cocok[0],
                fn ($t) => str_contains($t, 'pendaftaran-layanan.' . $tombol)
            ));

            $this->assertNotEmpty($tag, "Tombol {$tombol} tidak ada di kepala halaman.");

            $this->assertStringContainsString('aria-label=', $tag[0],
                "Tombol {$tombol} kehilangan aria-label; di ponsel tulisannya "
                . 'disembunyikan, jadi ia jadi tombol ikon tanpa nama.');
        }
    }

    /** Medali kepala sebaris dengan judulnya — 44px yang terbuang. */
    #[Test]
    public function medali_kepala_tidak_makan_satu_baris_sendiri(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.mis-kepala > \.mis-kepala-teks\s*\{[^}]*flex:\s*1 1 0/s', $this->bersama(),
            'Basis 260px-nya kembali, jadi medali kepala duduk sendirian di satu baris penuh.'
        );
    }

    /** Baris angka: dua lajur di ponsel, bukan empat kotak bertumpuk. */
    #[Test]
    public function baris_angka_dua_lajur_di_ponsel(): void
    {
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 575\.98px\) \{\s*\.pdl-uang-total\s*\{[^}]*grid-template-columns:\s*repeat\(2/s',
            $this->gaya(),
            'Baris angkanya kembali satu lajur; empat kotak bertumpuk jadi 205px.'
        );
    }

    // --------------------------------------------------- layar rincian

    private function rincian(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );
    }

    /**
     * Sasaran ketuk minimal 40px di ponsel. Terukur: tombol salin 24x24,
     * tombol aksi 32x32, pembuka jejak 30px. Yang meleset bukan tidak
     * terjadi apa-apa — tombol WhatsApp ada persis di sebelahnya.
     */
    #[Test]
    public function sasaran_ketuk_cukup_besar_di_ponsel(): void
    {
        $isi = $this->rincian();

        foreach (['.rin-salin', '.rin-aksi'] as $kelas) {
            $this->assertMatchesRegularExpression(
                '/\\' . $kelas . '\s*\{[^}]*height:\s*40px/s', $isi,
                "Sasaran ketuk {$kelas} di ponsel kembali di bawah 40px."
            );
        }

        $this->assertMatchesRegularExpression(
            '/\[data-mis-ringkas\]\s*\{[^}]*min-height:\s*40px/s', $isi,
            'Tombol pembuka jejak dan daftar peserta kembali setinggi 30px.'
        );
    }

    /**
    /**
     * SELURUH keping pendaftaran ganda bisa dikenai jempol.
     *
     * Dulu dijaga lewat tinggi baris nomornya: dipatok 40px supaya tautan
     * yang cuma 16px tidak lagi meleset saat ditekan. Itu bekerja, tetapi
     * ongkosnya terbaca — teks nomornya sendiri 15px, jadi 25px ruang mati
     * menganga antara nomor dan lencana keadaannya, dan kepingnya terasa
     * renggang.
     *
     * Diganti tautan yang MEMBENTANG menutupi kepingnya. Keduanya didapat
     * sekaligus: jaraknya kembali 2px, dan sasaran ketuknya justru membesar
     * dari 186x40 jadi 270x49. Diperiksa di peramban dengan
     * elementFromPoint pada sembilan titik di dalam keping — keempat sudut,
     * keempat sisi, dan tengahnya — kesembilannya sampai ke tautannya, di
     * 390px maupun 1440px.
     *
     * Jangkarnya ikut dijaga: tanpa `position: relative` pada kepingnya,
     * `inset: 0` mengukur diri terhadap leluhur berposisi yang entah di
     * mana, dan lapisannya membentang ke tempat yang salah — tanpa satu
     * galat pun.
     */
    #[Test]
    public function keping_pendaftaran_ganda_bisa_ditekan_seluruhnya(): void
    {
        $isi = $this->rincian();

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-teks > a::after\s*\{[^}]*position:\s*absolute[^}]*inset:\s*0/s', $isi,
            'Tautan kepingnya tidak lagi membentang; yang bisa ditekan tinggal '
            . 'baris teks setinggi 17px.'
        );

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar > li\s*\{[^}]*position:\s*relative/s', $isi,
            'Kepingnya bukan jangkar posisi, jadi lapisan tautannya membentang '
            . 'terhadap leluhur yang entah di mana.'
        );
    }

    /**
     * Ubin di layar arsip memakai pembungkus geser + petunjuknya, sama
     * dengan layar daftar. Tanpa itu, di ponsel ubin keduanya meluber ke
     * kanan tanpa satu tanda pun bahwa barisnya berlanjut.
     */
    #[Test]
    public function ubin_arsip_punya_petunjuk_gesernya(): void
    {
        $isi = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/terhapus.blade.php')
        );

        $this->assertStringContainsString('mis-ringkas-geser', $isi);
        $this->assertStringContainsString('mis-ringkas-petunjuk', $isi,
            'Petunjuk gesernya hilang; ubin kedua meluber tanpa tanda apa pun.');
    }

    /**
     * Tab Hapus selalu di ujung kanan. Jumlah tabnya berayun 2 sampai 7
     * tergantung isi pendaftarannya, jadi "tab ke-sekian" bukan tempat yang
     * tetap — dan yang paling mahal kalau meleset justru tab itu.
     */
    #[Test]
    public function tab_hapus_selalu_di_ujung(): void
    {
        $this->assertMatchesRegularExpression(
            '/#rin-tab > li:last-child\s*\{[^}]*margin-left:\s*auto/s', $this->rincian(),
            'Tab Hapus kembali mengikuti jumlah tab di depannya, jadi tempatnya '
            . 'berpindah-pindah antar pendaftaran.'
        );
    }
    /** Markah satu baris pendaftaran. */
    private function baris(): string
    {
        return (string) file_get_contents(
            resource_path('views/account/pendaftaran_layanan/baris.blade.php')
        );
    }

    /**
     * Tiap pendaftaran berdiri sebagai KARTU sendiri di ponsel.
     *
     * Mode kartu bersama memisahkan barisnya dengan satu garis 1px #f1f5f9
     * dan tidak lebih. Terukur di 390px sebelum ini dipasang: jarak antar
     * kartu 0px, latarnya tembus pandang, sudutnya 0 — dua puluh
     * pendaftaran terbaca sebagai satu blok panjang, dan batas antara satu
     * pesanan dengan berikutnya harus dicari sendiri.
     */
    #[Test]
    public function tiap_pendaftaran_jadi_kartu_terpisah_di_ponsel(): void
    {
        $this->assertSame(1, preg_match(
            '/\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody tr\s*\{([^}]*)\}/s',
            $this->gaya(), $cocok
        ), 'Baris pendaftaran tidak dijadikan kartu sendiri di ponsel.');

        foreach (['margin-bottom', 'border-radius', 'background'] as $sifat) {
            $this->assertMatchesRegularExpression('/\b' . $sifat . ':/', $cocok[1],
                'Kartunya tidak menyetel ' . $sifat . '; tanpa itu batas antar '
                . 'pesanan tidak terlihat.');
        }
    }

    /**
     * Pita warna layanan SELAMAT dari bayangan kartunya.
     *
     * Pitanya berupa box-shadow inset, dan bayangan kartu memakai sifat yang
     * sama — ditulis terpisah, yang belakangan menang dan pitanya hilang
     * sama sekali. Terjadi betulan dan terlihat di potret: kartunya rapi,
     * tetapi warna yang membedakan Scopus Camp dari Webinar Eksklusif lenyap.
     */
    #[Test]
    public function pita_warna_layanan_tidak_tertimpa_bayangan_kartu(): void
    {
        $this->assertSame(1, preg_match(
            '/\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody tr\.pdl-baris\s*\{([^}]*)\}/s',
            $this->gaya(), $cocok
        ), 'Aturan pita warna kartu tidak ditemukan.');

        $this->assertSame(1, preg_match('/box-shadow:([^;]*);/s', $cocok[1], $bayang),
            'Kartunya tidak menyetel box-shadow sama sekali.');

        $this->assertStringContainsString('inset', $bayang[1],
            'Pita warna layanannya hilang; satu-satunya tanda yang membedakan '
            . 'kelima layanan sekali lihat.');

        $this->assertStringContainsString('--warna-aksen', $bayang[1],
            'Pitanya tidak lagi mengikuti warna layanannya.');
    }

    /**
     * Label dan nilai berdiri di DUA LAJUR yang lurus.
     *
     * Lebar label menentukan di mana nilainya mulai, dan labelnya tidak sama
     * panjang: terukur di 390px nilai "Sesi" mulai di 96px, "Total bayar" di
     * 172px, "Daftar" di 164px. Mata tidak punya satu pun garis tegak untuk
     * diikuti — dan itu yang terbaca sebagai "belum rapi", bukan rata
     * kanannya. Sesudah dipatok, ketiganya mulai di 159px.
     */
    #[Test]
    public function label_kartu_selebar_sama_supaya_nilainya_lurus(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody td\[data-judul\]::before\s*\{[^}]*flex:\s*0 0 \d+px/s',
            $this->gaya(),
            'Lebar label tidak dipatok, jadi nilainya mulai di tempat yang '
            . 'berbeda-beda pada tiap baris.'
        );
    }

    /**
     * Keterangan tambahan BERTUMPUK rata kanan, bukan berjejer.
     *
     * Di mode kartu selnya flex dengan label ::before sebagai anak pertama,
     * jadi tiap <span> jadi anak flex tersendiri dan berebut satu baris:
     * terukur di 390px nama angkatan, tanggal, dan lencana "penuh" menuntut
     * 250px dalam ruang nilai selebar 234px.
     */
    #[Test]
    public function keterangan_tambahan_bertumpuk_rata_kanan(): void
    {
        $markah = $this->baris();

        foreach (['pdl-sesi-blok', 'pdl-daftar-blok'] as $pembungkus) {
            $this->assertStringContainsString('class="' . $pembungkus . '"', $markah,
                'Isi selnya tidak dibungkus jadi satu anak flex, jadi tiap '
                . 'keterangan berebut baris yang sama.');
        }

        $this->assertSame(1, preg_match(
            '/\.pdl-sesi-blok,\s*\.pdl-uang-blok,\s*\.pdl-daftar-blok\s*\{([^}]*)\}/s',
            $this->gaya(), $cocok
        ), 'Ketiga pembungkusnya tidak diberi aturan bersama.');

        $this->assertStringContainsString('flex-direction: column', $cocok[1]);
        $this->assertStringContainsString('align-items: flex-end', $cocok[1],
            'Keterangannya tidak dirapatkan ke kanan, jadi tepi kanannya tidak '
            . 'membentuk garis lurus dengan nilai di atas dan di bawahnya.');
    }

    /**
     * Baris data dipisah garis, dan kaki kartunya lebih tegas lagi.
     *
     * Tiap baris kini dua tingkat — nilai pokok lalu keterangannya di bawah.
     * Tanpa garis, enam tingkat itu mengalir jadi satu tumpukan: terukur
     * jarak antar baris 6px, persis sama dengan jarak antara satu nilai dan
     * keterangannya sendiri, jadi mata tidak punya cara tahu mana yang
     * sepasang.
     */
    #[Test]
    public function baris_data_kartu_dipisah_garis(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/td\[data-judul="Total bayar"\],\s*\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody '
            . 'td\[data-judul="Daftar"\]\s*\{[^}]*border-top:/s',
            $isi,
            'Baris datanya tidak dipisah garis; keenam tingkatnya mengalir jadi '
            . 'satu tumpukan.'
        );

        $this->assertMatchesRegularExpression(
            '/\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody td:has\(\.mis-pil\)\s*\{[^}]*border-top:/s',
            $isi,
            'Kaki kartu tidak dipisah dari datanya; keadaan dan tindakan '
            . 'terbaca sebagai baris data berikutnya.'
        );
    }

    /**
     * Tombol aksi selebar kartu dan sama besar.
     *
     * Sel keadaan mengambil baris penuh, jadi sel aksi turun ke barisnya
     * sendiri — tetapi lebarnya mengikuti isinya dan didorong kanan oleh
     * margin otomatis. Terukur: kartunya mulai di 47px sementara barisan
     * tombolnya mulai di 75px, bertakik 28px tepat di baris terakhir.
     *
     * Anak BORANGNYA ikut disebut dalam aturannya. Tombol Follow up
     * dibungkus <form> ber-`display: contents`, jadi yang jadi item lentur
     * barisnya tombolnya, bukan borangnya; aturan yang cuma menyebut anak
     * langsung mendarat di borang yang tidak punya kotak sama sekali, dan
     * terukur Rincian 145px melawan Follow up 133px padahal aturannya
     * mengaku membagi rata. Sesudah diperbaiki keduanya 133px.
     */
    #[Test]
    public function tombol_aksi_kartu_selebar_kartu_dan_sama_besar(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.mis-tabel\.mis-tabel-kartu\.pdl-tabel tbody td\[data-judul="Aksi"\]\s*\{[^}]*flex:\s*1 1 100%/s',
            $isi,
            'Sel aksinya tidak selebar kartu, jadi barisan tombolnya bertakik '
            . 'dari tepi kiri kartu.'
        );

        $this->assertSame(1, preg_match(
            '/(\.pdl-aksi-baris > \*,\s*[^{]*\.pdl-aksi-baris > form > \*)\s*\{([^}]*)\}/s',
            $isi, $cocok
        ), 'Pembagi lebar tombolnya tidak menyebut anak borangnya, jadi ia '
            . 'mendarat di <form display:contents> yang tidak punya kotak.');

        $this->assertStringContainsString('flex: 1 1 0', $cocok[2]);
    }

    /**
     * Nomor WA pindah ke kaki kartu di ponsel, dan HANYA di ponsel.
     *
     * Di kolom Pendaftar ia jadi baris ketiga di bawah nama dan nomor
     * pendaftaran, sementara kaki kartunya menyisakan separuh baris
     * menganggur di kanan lencana keadaan. Dipindah, sel Pendaftar menyusut
     * dari 61px jadi 40px dan nomornya berdiri tepat di atas tombol Follow
     * up yang memakainya.
     */
    #[Test]
    public function nomor_wa_pindah_ke_kaki_kartu_di_ponsel(): void
    {
        $markah = $this->baris();

        foreach (['tabel', 'kartu'] as $tempat) {
            $this->assertStringContainsString("'tempat' => '" . $tempat . "'", $markah,
                'Salinan nomor untuk ' . $tempat . ' tidak dipasang.');
        }

        /*
         * Satu SUMBER, dua tempat — bukan dua salinan markah.
         *
         * Dua salinan yang menyimpang satu sama lain adalah persoalan yang
         * baru ketahuan sesudah salah satunya lama salah.
         */
        $this->assertSame(2, substr_count($markah, 'partials.kontak'),
            'Nomornya tidak dirakit dari satu partial yang sama untuk kedua '
            . 'tempatnya.');

        $this->assertStringNotContainsString('fab fa-whatsapp mis-ikon-hijau', $markah,
            'Markah nomornya disalin langsung di baris, bukan di-include; dua '
            . 'salinan akan menyimpang tanpa ada yang tahu.');
    }

    /**
     * Keduanya dipilih dengan DUA kelas, supaya bobotnya menang.
     *
     * `.pdl-kontak` sendiri menyetel display: flex dan bobotnya (0,1,0) —
     * sama persis dengan `.pdl-kontak-kartu`. Yang seri dimenangkan yang
     * terbaca belakangan, dan `.pdl-kontak` ditulis lebih bawah: terukur di
     * 1440px salinan kartunya ikut tampil dan menimpa kolom keadaan, jadi
     * nomor yang sama tergambar dua kali dalam satu baris tabel. Tidak ada
     * galat apa pun yang menandainya.
     */
    #[Test]
    public function salinan_nomor_dipilih_dengan_bobot_yang_menang(): void
    {
        $isi = $this->gaya();

        /*
         * Diperiksa per ATURAN, bukan "ada di suatu tempat".
         *
         * Versi pertama penjaga ini cuma mencari bentuk dua-kelas di seluruh
         * berkas, dan TETAP HIJAU saat aturan di dalam media ponsel
         * diturunkan jadi satu kelas — sebab aturan bawaannya masih memakai
         * bentuk dua-kelas. Persis cacat yang hendak dijaga, lolos.
         */
        /*
         * Tiap PEMILIH diperiksa sendiri, dan daftar pemilih berkoma pun
         * dipecah dulu.
         *
         * Dua versi penjaga ini sudah keliru sebelumnya. Yang pertama cuma
         * mencari bentuk dua-kelas di seluruh berkas dan tetap hijau saat
         * aturan di dalam media ponsel diturunkan jadi satu kelas. Yang
         * kedua membaca pemilihnya sebagai satu baris, lalu merah sendiri
         * begitu aturannya digabung dengan .pdl-nomor jadi daftar dua baris
         * — merah oleh sebab yang bukan cacatnya.
         */
        /*
         * Komentar DIBUANG dulu.
         *
         * Berkas ini menerangkan tiap aturannya panjang-panjang, dan
         * komentar yang menyebut "pdl-kontak-kartu" terbaca oleh pemindai
         * sebagai pemilih — penjaganya lalu merah sambil mengutip kalimat
         * penjelasan, bukan kode.
         */
        $bersih = preg_replace('#/\*.*?\*/#s', '', $isi);

        preg_match_all('/([^{}]*?)\{([^}]*)\}/s', $bersih, $semua, PREG_SET_ORDER);

        $diperiksa = 0;

        foreach ($semua as $aturan) {
            foreach (explode(',', $aturan[1]) as $pemilih) {
                $pemilih = trim($pemilih);

                /*
                 * Kedua varian disebut PERSIS. Awalan "pdl-kontak-" saja
                 * ikut menangkap .pdl-kontak-kosong — kelas untuk "nomor
                 * belum diisi", yang tidak ada urusannya dengan bobot ini.
                 */
                if (! preg_match('/\.pdl-kontak-(kartu|tabel)\b/', $pemilih)) {
                    continue;
                }

                $diperiksa++;

                $this->assertStringContainsString('.pdl-kontak.', $pemilih,
                    'Pemilih "' . $pemilih . '" ditulis dengan satu kelas saja; '
                    . 'bobotnya cuma seri dengan .pdl-kontak dan hasilnya '
                    . 'bergantung pada urutan tulis.');
            }
        }

        $this->assertGreaterThanOrEqual(3, $diperiksa,
            'Ketiga pemilih salinan nomor tidak lengkap: satu memadamkan '
            . 'salinan kartu di lebar tabel, dua lagi menukar keduanya di '
            . 'lebar ponsel.');

        /*
         * Di lebar tabel salinan kartunya harus benar-benar padam.
         *
         * Dicari lewat aturan yang sudah dipecah di atas, bukan dengan pola
         * yang mengandaikan pemilihnya berdiri sendiri: aturannya kini satu
         * daftar bersama .pdl-nomor, jadi sesudah ".pdl-kontak-kartu" yang
         * menyusul koma, bukan kurung.
         */
        $padam = false;

        foreach ($semua as $aturan) {
            if (! str_contains($aturan[1], '.pdl-kontak.pdl-kontak-kartu')) {
                continue;
            }

            if (preg_match('/display:\s*none/', $aturan[2])) {
                $padam = true;
            }
        }

        $this->assertTrue($padam,
            'Salinan kartunya tidak dipadamkan di lebar tabel; nomornya '
            . 'tergambar dua kali dalam satu baris.');
    }

    /**
     * Nomor pendaftaran naik ke KEPALA kartu, rapat ke kanan.
     *
     * Di kolom Pendaftar ia baris tersendiri di atas nama. Di kartu ia lebih
     * berguna sebagai penanda kartu — di seberang nama layanannya, tempat
     * mata mencari "ini pendaftaran yang mana" — dan blok nama kehilangan
     * satu baris lagi. Terukur: tinggi kartu 383px jadi 365px.
     */
    #[Test]
    public function nomor_pendaftaran_naik_ke_kepala_kartu(): void
    {
        $markah = $this->baris();

        $this->assertSame(2, substr_count($markah, 'partials.nomor'),
            'Nomornya tidak dirakit dari satu partial untuk kedua tempatnya.');

        foreach (['tabel', 'kartu'] as $tempat) {
            $this->assertStringContainsString("partials.nomor', ['tempat' => '" . $tempat . "'",
                $markah, 'Salinan nomor untuk ' . $tempat . ' tidak dipasang.');
        }

        $isi = $this->gaya();

        /*
         * Pembungkus kepalanya WAJIB selebar kartu.
         *
         * .mis-td-utama flex, dan .pdl-layanan di dalamnya cuma selebar
         * isinya — margin otomatis lalu mendorong nomornya ke tepi BLOK
         * itu, bukan ke tepi kartu. Terukur: berhenti di 262px sementara
         * tepi isi kartunya 333px, jadi ia menggantung di tengah.
         */
        $this->assertMatchesRegularExpression(
            '/td\.mis-td-utama \.pdl-layanan\s*\{[^}]*width:\s*100%/s', $isi,
            'Pembungkus kepala kartu tidak selebar kartu, jadi nomornya '
            . 'berhenti di tengah, bukan di tepi kanan.'
        );

        $this->assertMatchesRegularExpression(
            '/\.pdl-nomor\.pdl-nomor-kartu\s*\{[^}]*margin-left:\s*auto/s', $isi,
            'Nomornya tidak didorong ke kanan di kepala kartu.'
        );
    }

    /**
     * Keping rombongan dirapatkan ke kanan di kartu.
     *
     * .pdl-nama flex dan membungkus; kepingnya duduk tepat di samping nama,
     * jadi pada nama pendek ia berhenti di tengah baris dan tidak sejajar
     * dengan apa pun. Didorong ke tepi, ia segaris dengan nomor di atasnya
     * dan dengan nilai kolom kanan di bawahnya — terukur ketiganya berakhir
     * di 333px pada lebar 390px.
     */
    #[Test]
    public function keping_rombongan_rapat_ke_kanan_di_kartu(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.pdl-nama \.pdl-pil-orang\s*\{[^}]*margin-left:\s*auto/s', $this->gaya(),
            'Keping rombongan tidak dirapatkan ke kanan; pada nama pendek ia '
            . 'berhenti di tengah baris.'
        );
    }

    /**
     * Nomor transaksi jadi KEPING penanda, sewarna layanannya.
     *
     * Di kepala kartu ia berdiri sendiri di seberang nama layanan; sebagai
     * teks abu-abu polos ia terbaca seperti keterangan yang kelupaan
     * dirapikan. Warnanya mengambil --warna-aksen — variabel yang sama
     * dengan pita di tepi kartu — jadi kepala kartu menyebut layanannya
     * tiga kali dengan cara yang sama: medali, pita, dan keping ini.
     * Terukur: Scopus Camp #10b981, Webinar #f59e0b, Clinik #0ea5e9.
     */
    #[Test]
    public function nomor_transaksi_jadi_keping_sewarna_layanan(): void
    {
        /*
         * Diambil aturan yang MENYALAKANNYA, bukan yang pertama ketemu.
         *
         * Kelas ini punya dua aturan: satu memadamkannya di lebar tabel,
         * satu lagi merupakannya di lebar kartu. Pola yang cuma mencari
         * kemunculan pertama mendarat di yang memadamkan, lalu merah sambil
         * mengeluh "display: none tidak ber-border-radius" — keluhan yang
         * benar tentang aturan yang salah.
         */
        preg_match_all('/\.pdl-nomor\.pdl-nomor-kartu[^{]*\{([^}]*)\}/s',
            $this->gaya(), $semua, PREG_SET_ORDER);

        $aturan = '';

        foreach ($semua as $satu) {
            if (str_contains($satu[1], 'display: block')) {
                $aturan = $satu[1];
            }
        }

        $this->assertNotSame('', $aturan,
            'Aturan yang merupakan keping nomor di modus kartu tidak ditemukan.');

        $this->assertMatchesRegularExpression('/border-radius:\s*999px/', $aturan,
            'Kepingnya tidak bersudut penuh, jadi ia tidak terbaca sebagai '
            . 'penanda melainkan sebagai kotak teks.');

        $this->assertStringContainsString('--warna-aksen', $aturan,
            'Kepingnya tidak mengambil warna layanannya.');

        /*
         * Latar dan tulisannya WAJIB punya nilai cadangan yang ditulis
         * lebih dulu.
         *
         * color-mix() belum dikenal setiap peramban; yang belum mengenalnya
         * mengabaikan deklarasinya. Tanpa baris cadangan di atasnya, yang
         * tersisa latar transparan dan tulisan warna bawaan — kepingnya
         * hilang sama sekali tanpa satu galat pun.
         */
        foreach (['background', 'color'] as $sifat) {
            $this->assertSame(2, preg_match_all('/\b' . $sifat . ':/', $aturan),
                'Sifat ' . $sifat . ' pada kepingnya tidak punya nilai cadangan '
                . 'sebelum color-mix(); di peramban yang belum mengenalnya '
                . 'kepingnya hilang tanpa tanda apa pun.');
        }
    }

    /**
     * Keping nomor MENGALAH pada nama layanannya.
     *
     * Dibiarkan menyusut sebebasnya ia tetap mengambil seluruh ruang yang
     * dimintanya lebih dulu. Terukur di 320px pada Clinik Scopus, yang
     * nomornya "BOOK-05072026111623-ZRWIJ": nama layanannya terpotong jadi
     * "Clinik Scopu" sementara kepingnya memanjang 111px. Nomor penuhnya
     * tetap ada di title dan di halaman rincian; nama layanan yang terpotong
     * tidak punya cadangan apa pun.
     *
     * Dan sel kepalanya sendiri harus boleh menyusut: anak flex tidak pernah
     * mengecil di bawah min-content-nya, dan nomor itu satu kata panjang
     * ber-nowrap — terukur SELNYA 361px di dalam kartu selebar 290px, jadi
     * elipsisnya tidak pernah terpakai sebab tidak ada yang memaksanya sempit.
     */
    #[Test]
    public function keping_nomor_mengalah_pada_nama_layanan(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.pdl-nomor\.pdl-nomor-kartu\s*\{[^}]*max-width:\s*\d+%/s', $isi,
            'Lebar kepingnya tidak dibatasi; pada nomor panjang nama '
            . 'layanannya yang terpotong.'
        );

        $this->assertMatchesRegularExpression(
            '/td\.mis-td-utama\s*\{[^}]*min-width:\s*0/s', $isi,
            'Sel kepala kartu tidak boleh menyusut, jadi elipsis pada '
            . 'kepingnya tidak pernah terpakai dan isinya meluber keluar kartu.'
        );
    }

    /**
     * Tombol lanjutan BERDAMPINGAN di ponsel, dan namanya ikut dipendekkan.
     *
     * Terukur di 390px: "Kirim ulang suratnya" 197px dan "Follow up:
     * Menunggu bayar" 242px dalam wadah selebar 270px, jadi keduanya turun
     * ke barisnya masing-masing dan bloknya memakan 92px. Berdampingan
     * dengan nama pendek, 42px — dan keduanya tetap satu baris.
     *
     * Barisnya ikut dilebarkan penuh: .rin-lanjutan memakai justify-items:
     * start, jadi begitu tombolnya dibuat menyusut, barisnya menyusut
     * bersamanya — terukur 232px di dalam kartu selebar 270px.
     */
    #[Test]
    public function tombol_lanjutan_berdampingan_di_ponsel(): void
    {
        $isi = $this->rincian();

        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok),
            'Blok gaya ponsel layar rincian tidak ada.');

        $this->assertMatchesRegularExpression(
            '/\.rin-lanjutan-tombol > form > \.mis-tombol\s*\{[^}]*flex:\s*1 1 0/s', $blok[1],
            'Tombol lanjutan tidak berbagi barisnya; keduanya bertumpuk dan '
            . 'bloknya memakan 92px.'
        );

        /*
         * Anak BORANGNYA ikut disebut. Keduanya dibungkus <form>
         * ber-`display: contents`, jadi yang jadi item lentur barisnya
         * tombolnya, bukan borangnya — aturan yang cuma menyebut anak
         * langsung mendarat di borang yang tidak punya kotak sama sekali.
         * Cacat yang sama sudah pernah terjadi di barisan tombol kartu
         * daftar.
         */
        $this->assertStringContainsString('.rin-lanjutan-tombol > form > .mis-tombol', $blok[1],
            'Pembagi lebarnya tidak menyebut anak borangnya, jadi ia mendarat '
            . 'di <form display:contents> yang tidak punya kotak.');

        $this->assertMatchesRegularExpression(
            '/\.rin-lanjutan-tombol\s*\{\s*width:\s*100%/s', $blok[1],
            'Baris tombolnya tidak dilebarkan penuh, jadi ia ikut menyusut '
            . 'bersama tombolnya dan menyisakan ruang menganggur di kanan.'
        );
    }

    /**
     * Nama pendek tombol lanjutan HANYA di ponsel.
     *
     * Keduanya tampil bersamaan berarti tombolnya berbunyi "Kirim ulang
     * suratnya Kirim ulang".
     *
     * Keadaan pada tombol follow-up dibuang dari nama pendeknya, bukan
     * dipotong: "Menunggu bayar" sudah tertulis sebagai lencana di kartu
     * profil layar yang sama, jadi di ponsel ia pengulangan yang memakan
     * satu baris penuh.
     */
    #[Test]
    public function nama_pendek_tombol_lanjutan_hanya_di_ponsel(): void
    {
        $isi = $this->rincian();

        $this->assertSame(2, substr_count($isi, 'class="rin-tombol-pendek"'),
            'Kedua tombol lanjutan tidak punya nama pendeknya.');

        $this->assertMatchesRegularExpression(
            '/\.rin-tombol-pendek\s*\{\s*display:\s*none/s', $isi,
            'Nama pendeknya tidak dipadamkan di lebar biasa, jadi tombolnya '
            . 'menyebutkan dirinya dua kali di desktop.'
        );

        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok),
            'Blok gaya ponsel layar rincian tidak ada.');

        $this->assertMatchesRegularExpression(
            '/\.rin-tombol-panjang\s*\{\s*display:\s*none/s', $blok[1],
            'Nama panjangnya tidak dipadamkan di ponsel, jadi tombolnya tetap '
            . 'menuntut 242px dan keduanya kembali bertumpuk.'
        );
    }

    /**
     * Kisi isian jadi SATU LAJUR di ponsel.
     *
     * auto-fit di aturan dasarnya seharusnya menumpuk isiannya satu per
     * baris saat ruangnya tidak cukup. Terukur, ia tidak:
     *
     *     320px  ->  200px 0px      lajur kedua NOL
     *     390px  ->  215px 41px     lajur kedua 41px
     *
     * Jadi "Kode unik" dan "Nominal potongan" mendapat kotak selebar 41px
     * dengan labelnya terpatah dua baris — dan di 320px kotaknya hilang
     * sama sekali. Isian antar lajurnya pun tidak sejajar, terukur selisih
     * 7px.
     */
    #[Test]
    public function kisi_isian_satu_lajur_di_ponsel(): void
    {
        $isi = $this->rincian();

        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok),
            'Blok gaya ponsel layar rincian tidak ada.');

        $this->assertMatchesRegularExpression(
            '/\.rin-isian-kisi\s*\{[^}]*grid-template-columns:\s*minmax\(0, 1fr\)/s', $blok[1],
            'Kisi isiannya tidak dipatok satu lajur di ponsel; auto-fit '
            . 'menyisakan lajur kedua yang tidak pernah muat.'
        );
    }

    /**
     * Rentang antar-lajur DIBATALKAN di ponsel, dan dengan !important.
     *
     * Memaksa satu lajur saja TIDAK cukup: isian "Total bayar" membawa
     * `style="grid-column: span 2"` yang dirakit peladen dari jumlah isian
     * per bagian, dan satu rentang itu melahirkan lajur TERSIRAT kedua
     * untuk seluruh kisinya. Terukur sesudah lajurnya dipatok satu: masih
     * "38px 218px" di 390px dan "0px 186px" di 320px.
     *
     * !important dipakai justru karena lawannya gaya sebaris, yang tidak
     * bisa dikalahkan bobot pemilih berapa pun — dan itu sebabnya
     * memaksa satu lajur saja terlihat berhasil di sumber tetapi tidak
     * mengubah apa pun di layar.
     */
    #[Test]
    public function rentang_antar_lajur_dibatalkan_di_ponsel(): void
    {
        $isi = $this->rincian();

        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok);

        $this->assertSame(1, preg_match(
            '/\.rin-isian-kisi > \.mis-isian:not\(\.rin-isian-penuh\)\s*\{([^}]*)\}/s',
            $blok[1], $cocok
        ), 'Rentang antar-lajurnya tidak dibatalkan di ponsel; satu isian '
            . 'ber-span melahirkan lajur tersirat untuk seluruh kisinya.');

        $this->assertMatchesRegularExpression('/grid-column:\s*auto\s*!important/', $cocok[1],
            'Pembatalannya tanpa !important, jadi ia kalah oleh gaya sebaris '
            . 'dan tidak mengubah apa pun di layar.');

        /*
         * Isian selebar kisi dikecualikan: ia memang 1 / -1, dan dengan satu
         * lajur itu tetap berarti selebar kisinya.
         */
        $this->assertStringContainsString(':not(.rin-isian-penuh)', $blok[1],
            'Isian yang memang selebar kisi ikut dibatalkan rentangnya.');
    }

    /** Markah layar catatan penghapusan. */
    private function arsip(): string
    {
        return (string) file_get_contents(
            resource_path('views/account/pendaftaran_layanan/terhapus.blade.php')
        );
    }

    /**
     * Saringan arsip DILIPAT dalam kartu, sama dengan layar Pendaftar
     * Layanan.
     *
     * Sebelumnya borangnya berlatar tembus pandang, tanpa tepi, sudut 0,
     * bantalan 0 — sementara tiap catatan di bawahnya kartu putih bersudut
     * 14px. Terukur di 390px isiannya bertumpuk setinggi 185px, jadi yang
     * terlihat deretan kotak yang tercecer di latar halaman tanpa ada yang
     * menyatukannya, dan satu layar penuh habis sebelum catatan pertama
     * kelihatan.
     *
     * Dipakai struktur yang SAMA dengan layar daftar, bukan kartu buatan
     * sendiri: <details data-mis-lipat> + .mis-saring-kartu. Terukur di
     * kedua layar — tertutup di 390px dengan ringkasan terlihat, dipaksa
     * terbuka di 1440px dengan ringkasan display:none, kartunya putih
     * bersudut 16px di keduanya.
     */
    #[Test]
    public function saringan_arsip_dilipat_dalam_kartu(): void
    {
        $isi = $this->arsip();

        $this->assertSame(1, preg_match(
            '/<details class="mis-lipat" id="thp-penyaring" data-mis-lipat>([\s\S]*?)<form /',
            $isi, $cocok
        ), 'Saringan arsip tidak dibungkus lipatan seperti layar daftar; '
            . 'kendalinya memakan satu layar penuh sebelum catatan pertama.');

        $this->assertStringContainsString('Cari &amp; saring', $cocok[1],
            'Ringkasan lipatannya tidak menyebut "Cari & saring" seperti layar '
            . 'sebelah.');

        $this->assertStringContainsString('mis-saring-kartu', $cocok[1],
            'Borangnya tidak di dalam kartu saringan bersama, jadi ia tetap '
            . 'tercecer di latar halaman.');

        /*
         * data-mis-lipat WAJIB: tanpanya mis-ui.js tidak memaksanya terbuka
         * di layar lebar, dan di sana saringannya jadi tertutup — padahal di
         * layar sebelah ia selalu terbuka.
         */
        $this->assertStringContainsString('data-mis-lipat', $isi,
            'Lipatannya tidak ditandai, jadi di layar lebar ia tetap tertutup '
            . 'sementara layar sebelah terbuka.');
    }

    /**
     * Lencana "aktif" ikut diperbarui penyaring hidup.
     *
     * Saringannya terlipat, jadi saat tertutup satu-satunya tanda bahwa
     * daftarnya sedang disaring adalah lencana ini. Tidak ikut diperbarui,
     * ia tertinggal pada keadaan saat halaman dimuat — dan yang membaca
     * mengira daftarnya utuh padahal sedang disaring.
     */
    #[Test]
    public function lencana_aktif_arsip_ikut_diperbarui(): void
    {
        $isi = $this->arsip();

        $this->assertStringContainsString('id="thp-aktif"', $isi,
            'Pembungkus lencana aktifnya tidak ada.');

        $this->assertMatchesRegularExpression(
            '/data-mis-saring-juga="[^"]*thp-aktif/', $isi,
            'Lencana aktifnya tidak ikut diperbarui penyaring hidup; saat '
            . 'lipatannya tertutup, tidak ada tanda bahwa daftarnya disaring.'
        );
    }

    /**
     * Keping panjang BOLEH membungkus — semua kepingnya, bukan <span> saja.
     *
     * Aturannya sudah ada, dan bekerja untuk dua belas keping. Satu tidak:
     * "dikembalikan 12 Okt 2026, 08:07 oleh tesss" ditulis sebagai <a> agar
     * bisa ditekan, dan pemilih lamanya menyebut `.thp-ket > span` saja —
     * jadi ia keluar dari aturan itu tanpa satu tanda pun.
     *
     * Terukur di 390px: kepingnya 315px di dalam induk 247px dan seluruh
     * halaman jadi bisa digeser menyamping, lebar gulir 411px melawan layar
     * 390px. Sesudah diperbaiki, 390 lawan 390.
     */
    #[Test]
    public function semua_keping_arsip_boleh_membungkus(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        $this->assertMatchesRegularExpression(
            '/\.thp-ket > \*\s*\{[^}]*white-space:\s*normal/s', $blok[1],
            'Aturan membungkusnya kembali menyebut satu jenis unsur saja; '
            . 'keping yang ditulis sebagai <a> akan meluber lagi.'
        );
    }

    /**
     * Barisan tombol arsip MENGAMBIL barisnya sendiri di ponsel.
     *
     * Ia item flex seukuran isinya — terukur 257px — di dalam kaki kartu
     * yang pada 320px cuma 177px. min-width: auto menahannya menyusut, jadi
     * ia MELUBER alih-alih membungkus: lebar gulir halaman 353px melawan
     * layar 320px.
     */
    #[Test]
    public function barisan_tombol_arsip_boleh_membungkus(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        $this->assertSame(1, preg_match(
            '/\.thp-kaki > \.thp-aksi\s*\{([^}]*)\}/s', $blok[1], $cocok
        ), 'Barisan tombolnya tidak diberi barisnya sendiri di ponsel.');

        $this->assertMatchesRegularExpression('/flex:\s*1 1 100%/', $cocok[1],
            'Barisan tombolnya tidak mengambil barisnya sendiri.');

        $this->assertMatchesRegularExpression('/min-width:\s*0/', $cocok[1],
            'Tanpa min-width: 0 ia tetap tidak boleh menyusut, jadi ia meluber '
            . 'alih-alih membungkus.');
    }

    /**
     * Saringan arsip BERDIRI SENDIRI, di luar kartu hasilnya.
     *
     * Keduanya sempat dibungkus satu .mis-kartu, jadi bilah "Cari & saring"
     * dan tumpukan catatannya terbaca sebagai satu benda — dan kartu
     * saringannya jadi kotak di dalam kotak. Layar Pendaftar Layanan tidak
     * punya kartu luar sama sekali: saringan dan hasilnya kartu bersaudara.
     *
     * Dijaga dari URUTAN markahnya: pembuka .mis-kartu harus datang SESUDAH
     * lipatan saringannya ditutup.
     */
    #[Test]
    public function saringan_arsip_di_luar_kartu_hasil(): void
    {
        $isi = $this->arsip();

        $tutupLipat = strpos($isi, '</details>', strpos($isi, 'id="thp-penyaring"'));
        $bukaKartu = strpos($isi, '<div class="mis-kartu">');
        $hasil = strpos($isi, 'id="thp-hasil"');

        $this->assertNotFalse($tutupLipat, 'Lipatan saringan arsip tidak ada.');
        $this->assertNotFalse($bukaKartu, 'Kartu hasil arsip tidak ada.');

        $this->assertGreaterThan($tutupLipat, $bukaKartu,
            'Kartu hasil dibuka sebelum lipatan saringannya ditutup, jadi '
            . 'saringan dan datanya kembali terbungkus satu kartu.');

        $this->assertGreaterThan($bukaKartu, $hasil,
            'Wadah hasilnya tidak di dalam kartu itu.');
    }

    /**
     * Kartu saringan arsip tidak bersentuhan dengan kartu hasilnya.
     *
     * Saat terlipat, yang terlihat cuma bilah ringkasannya — dan ia tidak
     * punya margin bawah, sementara .mis-saring-kartu yang punya sedang
     * tidak bertinggi. Terukur: jarak 0px di 390px, jadi keduanya
     * bersentuhan dan terbaca seperti satu kotak terbelah. Sesudah
     * diperbaiki, 14px di 320, 390, dan 1440px.
     */
    #[Test]
    public function kartu_saringan_arsip_punya_jarak_ke_hasilnya(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        $this->assertSame(1, preg_match('/#thp-penyaring\s*\{[^}]*margin-bottom:\s*(\d+)px/s',
            $blok[1], $cocok),
            'Lipatan saringan arsip tidak punya jarak ke kartu hasil di '
            . 'bawahnya; keduanya bersentuhan saat terlipat.');

        $this->assertGreaterThanOrEqual(10, (int) $cocok[1],
            'Jaraknya terlalu rapat untuk membedakan kedua kartu.');
    }

    /**
     * Kartu catatan arsip punya irama, tidak semuanya rata kiri.
     *
     * Terukur di 390px sebelum ini: kartunya 348px tinggi berisi satu
     * tumpukan rata kiri — nomor, nama, enam keping dalam tiga baris,
     * nominal, tautan rincian, dua tombol — tanpa satu pun garis tegak atau
     * pemisah yang membedakan mana kepala, mana isi, mana kaki.
     *
     * Nomornya naik ke KANAN kepala kartu, di seberang medalinya; nominalnya
     * rata kanan dengan garis pemisah di atasnya.
     */
    #[Test]
    public function kartu_catatan_arsip_punya_irama(): void
    {
        $isi = $this->arsip();

        $this->assertStringContainsString('class="thp-isi"', $isi,
            'Badan kartunya tidak berkelas, jadi tata ulangnya di ponsel tidak '
            . 'bisa dipilih.');

        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok);

        $this->assertSame(1, preg_match(
            '/\.thp-isi > \.thp-nomor\s*\{([^}]*)\}/s', $blok[1], $nomor
        ), 'Nomornya tidak ditata ulang di ponsel; ia kembali jadi baris '
            . 'pertama yang mendesak namanya turun.');

        $this->assertMatchesRegularExpression('/justify-self:\s*end/', $nomor[1],
            'Nomornya tidak dirapatkan ke kanan kepala kartu.');

        /*
         * TANPA margin negatif.
         *
         * Versi pertama mengangkatnya 26px supaya sejajar titik tengah
         * medalinya — melebihi bantalan atas kartunya yang cuma 14px, jadi
         * nomornya tergambar DI LUAR kartu, menggantung di atas tepinya.
         * Terlihat langsung di layar, tanpa satu galat pun, dan suite tetap
         * hijau 1362 sementara cacatnya ada.
         *
         * Tidak perlu diangkat: .thp-isi sudah menempati lajur kedua baris
         * pertama, jadi anak pertamanya memang sebaris dengan medalinya —
         * terukur keduanya mulai di 15px dari tepi atas kartu, di 320px
         * maupun 390px.
         */
        $this->assertDoesNotMatchRegularExpression('/margin-top:\s*-/', $nomor[1],
            'Nomornya diangkat dengan margin negatif; kalau melebihi bantalan '
            . 'atas kartunya, ia tergambar di luar kartu.');

        $this->assertSame(1, preg_match('/\n            \.thp-uang\s*\{([^}]*)\}/s', $blok[1], $uang),
            'Nominalnya tidak ditata ulang di ponsel.');

        $this->assertMatchesRegularExpression('/text-align:\s*right/', $uang[1],
            'Nominalnya masih rata kiri di tengah tumpukan, jadi ia terbaca '
            . 'seperti keterangan lain yang kebetulan lewat.');

        $this->assertMatchesRegularExpression('/border-top:/', $uang[1],
            'Nominalnya tidak dipisah garis dari keterangan di atasnya.');
    }

    /**
     * Tiap tombol arsip selebar barisnya.
     *
     * Berdampingan keduanya menuntut 258px — "Buang catatan" 133px,
     * "Kembalikan" 118px, sela 7px — di dalam kolom isi yang 247px, jadi
     * mereka membungkus. Dibiarkan selebar isinya, yang tersisa dua tombol
     * bertumpuk dengan lebar berbeda dan tepi kanan yang ragged. Terukur
     * sesudah diperbaiki: keduanya 247px.
     *
     * BORANGNYA yang disebut, bukan tombolnya: keduanya <form>
     * ber-display:block, jadi borang yang jadi item lentur barisnya.
     */
    #[Test]
    public function tombol_arsip_selebar_barisnya(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        $this->assertMatchesRegularExpression(
            '/\.thp-aksi > form\s*\{[^}]*flex:\s*1 1 100%/s', $blok[1],
            'Borang tombolnya tidak mengambil barisnya sendiri; aturan yang '
            . 'cuma menyebut tombolnya mendarat di dalam kotak yang lebarnya '
            . 'sudah terlanjur mengikuti isinya.'
        );

        $this->assertMatchesRegularExpression(
            '/\.thp-aksi > form > \.mis-tombol,\s*\.thp-aksi > \.mis-tombol\s*\{[^}]*width:\s*100%/s',
            $blok[1],
            'Tombolnya tidak selebar barisnya, jadi tepi kanannya ragged.'
        );
    }

    /**
     * Nama dan nomor BERBAGI baris pertama kartu arsip.
     *
     * Nomornya anak pertama di markah, jadi dibiarkan mengalir ia mengambil
     * barisnya sendiri dan mendorong namanya turun. Keduanya ditempatkan
     * tegas: nama di lajur 1 baris 1, nomor di lajur 2 baris 1.
     *
     * Terukur di 390px: nama butuh 168px, nomor 62px, sela 10px — 240px
     * dalam lebar isi 247px, jadi keduanya memang muat sebaris.
     */
    #[Test]
    public function nama_dan_nomor_arsip_sebaris(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        $this->assertSame(1, preg_match('/\.thp-isi\s*\{([^}]*)\}/s', $blok[1], $isi),
            'Badan kartunya tidak ditata ulang di ponsel.');

        /*
         * fit-content(40%), BUKAN minmax.
         *
         * minmax membiarkan lajur nomornya mengambil 40% penuh walau isinya
         * tidak membutuhkannya — terukur 99px untuk nomor yang cuma 62px,
         * dan 37px yang dirampasnya membuat NAMA membungkus jadi dua baris.
         * fit-content berarti "seukuran isinya, tetapi tidak lebih dari 40%".
         */
        $this->assertMatchesRegularExpression(
            '/grid-template-columns:\s*minmax\(0, 1fr\) fit-content\(\d+%\)/', $isi[1],
            'Lajur nomornya tidak memakai fit-content, jadi ia mengambil lebih '
            . 'dari yang dibutuhkannya dan namanya membungkus.'
        );

        $this->assertMatchesRegularExpression(
            '/\.thp-isi > \.thp-nama\s*\{[^}]*grid-row:\s*1/s', $blok[1],
            'Namanya tidak ditempatkan di baris pertama, jadi ia turun ke bawah '
            . 'nomornya.'
        );

        $this->assertMatchesRegularExpression(
            '/\.thp-isi > \.thp-nomor\s*\{[^}]*grid-row:\s*1/s', $blok[1],
            'Nomornya tidak ditempatkan di baris pertama.'
        );
    }

    /**
     * Nomor panjang DIPOTONG di lajurnya, tidak menimpa namanya.
     *
     * `justify-self: end` membuat kepingnya seukuran isinya dan menembus
     * lajurnya — terukur 188px menimpa namanya, dan elipsisnya tidak pernah
     * terpakai. Pembatasnya 100%, yang pada anak kisi berarti selebar
     * LAJURNYA; 40% akan berarti 40% dari lajur itu, dan terukur nomornya
     * menciut jadi 28px, tinggal elipsis.
     *
     * Arsip boleh memuat nomor layanan mana pun — Clinik Scopus berbentuk
     * "BOOK-05072026111623-ZRWIJ", 25 huruf — walau yang tersimpan sekarang
     * cuma 8 huruf.
     */
    #[Test]
    public function nomor_panjang_arsip_dipotong_bukan_menimpa(): void
    {
        preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $this->arsip(), $blok);

        preg_match('/\.thp-isi > \.thp-nomor\s*\{([^}]*)\}/s', $blok[1], $nomor);

        $this->assertMatchesRegularExpression('/max-width:\s*100%/', $nomor[1],
            'Nomornya tidak dibatasi selebar lajurnya, jadi ia menembus dan '
            . 'menimpa namanya.');

        $this->assertMatchesRegularExpression('/text-overflow:\s*ellipsis/', $nomor[1],
            'Nomor panjang tidak dipotong elipsis.');

        // Nilai penuhnya WAJIB tetap terbaca lewat title.
        $this->assertMatchesRegularExpression(
            '/<span class="thp-nomor"\s*\n?\s*title=/', $this->arsip(),
            'Nomor penuhnya tidak disimpan di title; yang terpotong hilang '
            . 'permanen.'
        );
    }

}
