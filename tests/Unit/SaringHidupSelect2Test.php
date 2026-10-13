<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Menu ber-Select2 di dalam penyaring hidup harus ikut menyaring.
 *
 * Select2 mengabarkan pilihan barunya lewat trigger() milik jQuery, dan
 * trigger() TIDAK membangunkan addEventListener bawaan. Diukur langsung di
 * peramban, jQuery 3.7.1:
 *
 *     jQuery .trigger('change')  -> addEventListener jalan 0 kali
 *     dispatchEvent bawaan       -> addEventListener jalan 1 kali
 *
 * Penyaring hidup di mis-ui.js menyahut lewat addEventListener, jadi menu
 * angkatan di layar Pendaftar Layanan terlihat berubah sementara daftarnya
 * tidak tersaring sama sekali. Direproduksi di peramban dengan klik
 * sungguhan pada menunya: tanpa jembatan ini nilai <select> memang menjadi
 * "199" tetapi nol permintaan terkirim dan isi tabelnya tidak berganti.
 *
 * Tidak ada galat apa pun yang menandainya — itu sebabnya ia dijaga dari
 * sumbernya, bukan diserahkan pada pemeriksaan tangan.
 */
class SaringHidupSelect2Test extends TestCase
{
    private function skrip(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2) . '/public/assets/js/mis-ui.js'
        );
    }

    public function test_penyaring_hidup_menyahut_peristiwa_select2(): void
    {
        $isi = $this->skrip();

        $this->assertStringContainsString('select2:select', $isi,
            'Penyaring hidup tidak menyahut peristiwa Select2, jadi menu '
            . 'ber-Select2 di dalamnya diam saja saat dipilih.');

        $this->assertMatchesRegularExpression(
            '/select2:select[^\']*\'\s*,\s*\'select\'/', $isi,
            'Penyahutnya tidak didelegasikan ke <select>, jadi menu yang '
            . 'baru dipasang Select2 sesudah skrip ini jalan tidak tertangkap.'
        );
    }

    /**
     * Yang disahut peristiwa MILIK Select2, bukan 'change' lewat jQuery.
     *
     * Pendengar jQuery juga menerima peristiwa bawaan. Menyahut 'change'
     * lalu menyebarkan 'change' lagi akan berputar tanpa henti — dan
     * halaman yang berputar begitu tidak menampilkan galat, ia cuma
     * membanjiri peladen dengan permintaan yang sama.
     */
    public function test_tidak_menyahut_change_lewat_jquery(): void
    {
        $isi = $this->skrip();

        $this->assertSame(0,
            preg_match('/jQuery\([^)]*\)\.on\(\s*\'change\'/', $isi),
            'Penyaring hidup menyahut "change" lewat jQuery; peristiwa bawaan '
            . 'yang disebarkannya akan disahut lagi oleh pendengar yang sama.');
    }

    /**
     * Menu angkatan memang berada DI DALAM borang penyaringnya.
     *
     * Di luar borangnya, delegasi di atas tidak pernah menerima apa pun —
     * dan gejalanya sama persis dengan sebelum diperbaiki.
     */
    public function test_menu_angkatan_di_dalam_borang_penyaring(): void
    {
        $markah = (string) file_get_contents(
            dirname(__DIR__, 2)
            . '/resources/views/account/pendaftaran_layanan/index.blade.php'
        );

        $awal = strpos($markah, 'data-mis-saring="pdl-hasil"');
        $this->assertNotFalse($awal, 'Borang penyaring hidupnya tidak ketemu.');

        $akhir = strpos($markah, '</form>', $awal);
        $this->assertNotFalse($akhir, 'Borang penyaringnya tidak ditutup.');

        $this->assertStringContainsString('id="pdl-angkatan"',
            substr($markah, $awal, $akhir - $awal),
            'Menu angkatan berada di luar borang penyaringnya, jadi '
            . 'pilihannya tidak akan pernah ikut tersaring.');
    }
    /**
     * mis-ui.js, tempat pemilih layar penuhnya tinggal.
     *
     * Perilakunya dipakai DUA layar — saringan angkatan di Pendaftar
     * Layanan dan pemindahan angkatan di layar rincian — jadi ia dipindah
     * ke berkas bersama. Salinan kedua yang menyimpang dari yang pertama
     * adalah persoalan yang baru ketahuan sesudah salah satunya lama salah.
     */
    private function pemilih(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2) . '/public/assets/js/mis-ui.js'
        );
    }

    /** Layar Pendaftar Layanan, tempat menu angkatannya berada. */
    private function layar(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2)
            . '/resources/views/account/pendaftaran_layanan/index.blade.php'
        );
    }

    /**
     * Menu angkatan dibuka sebagai pemilih sendiri, lewat kaitan RESMI
     * Select2.
     *
     * Menu bawaan Select2 terbuka sebagai daftar sempit menempel di bawah
     * kotaknya; di ponsel tiap barisnya terpatah dua sementara daftarnya
     * menutupi isian di bawahnya. 'select2:opening' memang peristiwa yang
     * boleh dibatalkan — lewat sini papan ketik ikut tertangkap, sebab
     * membuka menunya dengan Enter atau panah menerbitkan peristiwa yang
     * sama. Disadap lewat klik, yang memakai papan ketik tetap mendapat
     * menu lama.
     */
    public function test_pemilih_angkatan_mencegat_pembukaan_select2(): void
    {
        $isi = $this->layar();

        /*
         * Dipatok pada URUTANNYA, bukan pada jarak aksara.
         *
         * Versi pertama penjaga ini mencari preventDefault() dalam 400
         * aksara sesudah penyahutnya, dan jadi merah begitu satu komentar
         * ditambahkan di antaranya — merah oleh sebab yang sama sekali
         * bukan cacat yang dijaga.
         */
        $this->assertSame(1, preg_match(
            "/on\\('select2:opening',([\\s\\S]*?)bukaPemilihAngkatan\\(\\);/", $isi, $cocok
        ), 'Pembukaan menu Select2 tidak dicegat untuk membuka pemilihnya.');

        $this->assertStringContainsString('preventDefault()', $cocok[1],
            'Pembukaannya tidak dibatalkan, jadi menu Select2 lamanya tetap '
            . 'terbuka — berbarengan dengan pemilihnya.');
    }

    /**
     * Sesudah dipilih, DUA peristiwa diterbitkan — dan keduanya perlu.
     *
     * 'change.select2' menyegarkan kotak Select2-nya saja: tanpa itu
     * kotaknya masih menuliskan pilihan lama padahal <select>-nya sudah
     * berganti. 'select2:select' yang membangunkan penyaring hidup: tanpa
     * itu nilainya berganti tetapi daftarnya tidak tersaring sama sekali,
     * tanpa satu galat pun yang menandainya.
     *
     * Peristiwa 'change' bawaan sengaja tidak dipakai — lihat uji di atas.
     */
    public function test_pilihan_dari_pemilih_menerbitkan_kedua_peristiwa(): void
    {
        $isi = $this->layar();

        $this->assertSame(1, preg_match(
            '/sesudahPilih: function \\(menu\\) \\{([\\s\\S]*?)\\n                \\},/',
            $isi, $cocok
        ), 'Penyahut sesudah memilih tidak ditemukan di layar daftar.');

        $this->assertStringContainsString("trigger('change.select2')", $cocok[1],
            'Kotak Select2-nya tidak disegarkan; ia masih menuliskan pilihan lama.');

        $this->assertStringContainsString("trigger('select2:select')", $cocok[1],
            'Penyaring hidup tidak dibangunkan; angkatannya terpilih tetapi '
            . 'daftarnya tidak ikut tersaring.');

        $this->assertStringNotContainsString("trigger('change')", $cocok[1],
            "Memakai 'change' lewat jQuery di sini membangunkan penyahut yang "
            . 'menyebarkan change lagi — berputar tanpa henti.');
    }

    /**
     * Nama angkatan dipasang lewat textContent, BUKAN dirangkai jadi HTML.
     *
     * Namanya datang dari isian panitia dan boleh memuat apa saja. Dirangkai
     * jadi untaian HTML lalu disisipkan, ia jadi jalan masuk skrip asing ke
     * layar admin — layar yang justru memuat email dan nominal pembayaran
     * orang.
     */
    public function test_nama_angkatan_tidak_dirangkai_jadi_html(): void
    {
        $this->assertSame(1, preg_match(
            '/function rakitBaris\\(opsi, labelKelompok, nilaiTerpilih\\) \\{([\\s\\S]*?)\\n    \\}/',
            $this->pemilih(), $cocok
        ), 'Perakit baris pemilihnya tidak ditemukan.');

        $this->assertStringContainsString('judul.textContent = opsi.textContent', $cocok[1]);

        $this->assertStringNotContainsString('innerHTML', $cocok[1],
            'Baris pemilihnya dirakit dari untaian HTML; nama angkatan yang '
            . 'memuat markah akan ikut dieksekusi.');
    }

    /**
     * Daftarnya dirakit ULANG tiap kali dibuka, bukan sekali lalu disimpan.
     *
     * Kelompok <optgroup> dicopot dan dipasang kembali mengikuti layanan
     * yang sedang disaring. Dirakit sekali, pemilihnya akan menawarkan
     * angkatan layanan lain — pilihan yang pasti mengembalikan nol baris,
     * dan tidak ada yang tampak keliru di layar.
     */
    public function test_daftar_pemilih_dibaca_ulang_tiap_dibuka(): void
    {
        $this->assertSame(1, preg_match(
            '/function misPemilih\\(menu, pilihan\\) \\{([\\s\\S]*?)\\n        return true;/',
            $this->pemilih(), $cocok
        ), 'Fungsi pembuka pemilihnya tidak ditemukan.');

        $this->assertStringContainsString("menu.querySelectorAll('optgroup')", $cocok[1],
            'Daftarnya tidak dibaca dari <select> saat dibuka, jadi ia bisa '
            . 'menawarkan angkatan layanan yang sedang tidak disaring.');
    }

    /**
     * Tombol Reset BERWARNA BAHAYA, dan merahnya terlihat tanpa disentuh.
     *
     * Reset membuang seluruh saringan yang sedang dipasang sekaligus — di
     * ponsel itu bisa berarti tujuh kendali yang baru saja diisi satu per
     * satu di dalam panel yang terlipat. Berupa tombol halus seperti
     * "Saringan lain" di sebelahnya, tidak ada apa pun yang membedakan
     * keduanya sampai salah satunya tertekan.
     *
     * Merahnya pada keadaan DIAM, bukan cuma saat disentuh: di ponsel tidak
     * ada kursor yang bisa berhenti di atas tombol, jadi merah yang baru
     * muncul saat disentuh tidak memperingatkan siapa pun di sana.
     *
     * Terukur di peramban pada 390px dan 1440px — latar rgb(255,241,242),
     * tulisan rgb(225,29,72), tepi rgb(254,205,211) — dan sama di keduanya.
     */
    public function test_tombol_reset_berwarna_bahaya(): void
    {
        $this->assertSame(1, preg_match(
            '/<span id="pdl-reset".*?<\/span>/s', $this->layar(), $cocok
        ), 'Pembungkus tombol Reset tidak ditemukan.');

        $this->assertStringContainsString('mis-tombol-bahaya-halus', $cocok[0],
            'Tombol Reset tidak berwarna bahaya; ia terbaca sama dengan tombol '
            . 'biasa di sebelahnya padahal ia membuang seluruh saringan.');

        /*
         * BUKAN .mis-tombol-hapus. Yang itu berlatar merah penuh dan sengaja
         * dibuat sebagai tombol terberat di layar, untuk yang menghapus data
         * selamanya. Reset cuma membersihkan saringan — dipakaikan merah
         * terberat, isyarat itu kehilangan artinya di tempat yang benar-benar
         * membutuhkannya.
         */
        $this->assertStringNotContainsString('mis-tombol-hapus', $cocok[0],
            'Reset memakai merah terberat yang disediakan untuk penghapusan '
            . 'permanen; isyaratnya jadi tidak berarti apa-apa lagi.');
    }

    /**
     * Merah halusnya memang punya aturan, dan terlihat saat DIAM.
     *
     * Kelas yang namanya benar tetapi tidak punya aturan apa pun akan
     * tergambar seperti tombol biasa — tanpa galat, dan markahnya terbaca
     * seolah sudah benar.
     */
    public function test_merah_halus_terlihat_tanpa_disentuh(): void
    {
        $css = (string) file_get_contents(
            dirname(__DIR__, 2) . '/public/assets/css/mis-ui.css'
        );

        $this->assertSame(1, preg_match(
            '/\n\.mis-tombol-bahaya-halus\s*\{([^}]*)\}/s', $css, $cocok
        ), 'Kelas .mis-tombol-bahaya-halus tidak punya aturan apa pun.');

        foreach (['border', 'background', 'color'] as $sifat) {
            $this->assertMatchesRegularExpression('/\b' . $sifat . ':/', $cocok[1],
                'Keadaan diamnya tidak menyetel ' . $sifat . ', jadi merahnya '
                . 'belum tentu terlihat sebelum disentuh.');
        }

        $this->assertMatchesRegularExpression(
            '/\.mis-tombol-bahaya-halus:hover,\s*\.mis-tombol-bahaya-halus:focus-visible\s*\{[^}]*background:/s',
            $css,
            'Tidak ada keadaan tersentuhnya, jadi tombolnya diam saja saat '
            . 'kursor di atasnya.'
        );

        /*
         * Penanda versi CSS-nya wajib ikut naik. Tanpa itu peramban yang
         * sudah pernah membuka layar ini memakai mis-ui.css lamanya, dan
         * kelas barunya tidak punya aturan apa pun di sana — tombolnya
         * tergambar polos tanpa satu tanda pun bahwa ada yang salah.
         */
        $tataLetak = (string) file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/layouts/account.blade.php'
        );

        $this->assertSame(1, preg_match("/mis-ui\.css'\) \}\}\?v=(\d+)/", $tataLetak, $v),
            'Penanda versi mis-ui.css tidak ditemukan.');

        $this->assertGreaterThanOrEqual(94, (int) $v[1],
            'Penanda versi mis-ui.css belum dinaikkan sesudah kelas barunya '
            . 'ditambahkan.');
    }

    /** Markah satu baris pendaftaran. */
    private function barisPendaftaran(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2)
            . '/resources/views/account/pendaftaran_layanan/baris.blade.php'
        );
    }

    /**
     * "lihat bukti" membuka pratinjau di tempat, bukan tab baru.
     *
     * Untuk memeriksa selembar tangkapan layar transfer, tab baru terlalu
     * mahal: panitia kehilangan tempatnya di daftar, dan sesudah memeriksa
     * harus menutup tab lalu mencari kembali baris yang tadi dilihatnya —
     * pada daftar 350 baris yang baru saja disaring.
     *
     * Tautannya TETAP <a> ber-href: tanpa JavaScript, atau saat diklik
     * tengah, ia bekerja seperti semula.
     */
    public function test_lihat_bukti_membuka_pratinjau_di_tempat(): void
    {
        $markah = $this->barisPendaftaran();

        $this->assertMatchesRegularExpression(
            '/<a class="pdl-bukti pdl-bukti-ada" href="[^"]*"[\s\S]{0,200}?data-pdl-bukti/',
            $markah,
            'Tautan bukti tidak lagi ditandai untuk dicegat, jadi ia kembali '
            . 'membuka tab baru.'
        );

        $this->assertStringContainsString('target="_blank"', $markah,
            'Tautannya kehilangan jalan cadangannya; tanpa JavaScript bukti '
            . 'bayar tidak bisa dibuka sama sekali.');

        $isi = $this->layar();

        /*
         * Didelegasikan dari #pdl-hasil, BUKAN dipasang pada tiap tautannya.
         *
         * Isi bagian itu ditukar seluruhnya oleh penyaring hidup setiap kali
         * saringannya berubah. Pemasangan langsung hilang begitu isinya
         * diganti pertama kali, dan yang tersisa tautan yang kembali membuka
         * tab baru — tanpa ada yang keliru kelihatan. Diperiksa di peramban:
         * sesudah saringannya diubah dua kali, kliknya tetap sampai ke
         * pratinjaunya.
         */
        $this->assertSame(1, preg_match(
            "/getElementById\('pdl-hasil'\)([\s\S]*?)\}\)\(\);/", $isi, $cocok
        ), 'Pencegat kliknya tidak didelegasikan dari #pdl-hasil.');

        $this->assertStringContainsString("closest('a[data-pdl-bukti]')", $cocok[1],
            'Pencegatnya tidak mencari tautan bukti lewat closest(), jadi klik '
            . 'pada ikon di dalamnya tidak tertangkap.');

        // Klik tengah, Ctrl, Cmd, dan Shift dibiarkan lewat: ketiganya cara
        // orang membuka sesuatu di tab sendiri.
        foreach (['metaKey', 'ctrlKey', 'shiftKey'] as $tombol) {
            $this->assertStringContainsString($tombol, $cocok[1],
                'Pencegatnya merampas ' . $tombol . ', padahal itu cara orang '
                . 'membuka sesuatu di tab sendiri.');
        }
    }

    /**
     * Pratinjaunya dirakit lewat DOM, dan tahu kalau berkasnya hilang.
     *
     * Nama pendaftar ikut tercetak di dialog ini dan isinya datang dari
     * isian orang luar, jadi ia tidak boleh dirangkai jadi untaian HTML.
     *
     * Dan berkasnya memang bisa tidak ada: terukur 72 dari 183 nilai bukti
     * menunjuk berkas yang sudah tidak ada di cakram. Tanpa penangan galat,
     * yang tergambar ikon rusak bawaan peramban — yang tidak menyebutkan
     * apa pun tentang apa yang terjadi.
     */
    public function test_pratinjau_bukti_dirakit_aman_dan_punya_jalan_gagal(): void
    {
        $isi = $this->layar();

        $this->assertSame(1, preg_match(
            "/closest\('a\[data-pdl-bukti\]'\)([\s\S]*?)\}\)\(\);/", $isi, $cocok
        ), 'Blok pratinjau bukti tidak ditemukan.');

        $blok = $cocok[1];

        $this->assertStringNotContainsString('innerHTML', $blok,
            'Pratinjaunya dirangkai dari untaian HTML; nama pendaftar yang '
            . 'memuat markah akan ikut dieksekusi.');

        $this->assertStringContainsString("addEventListener('error'", $blok,
            'Tidak ada penangan saat berkasnya gagal dimuat; yang tergambar '
            . 'ikon rusak bawaan peramban.');

        /*
         * Opsi `text` tidak boleh dipakai bersama `html`.
         *
         * SweetAlert mengabaikannya begitu `html` ada — terukur: nomor
         * pendaftarannya tidak pernah tergambar sama sekali, tanpa satu
         * galat pun. Satu orang bisa punya lebih dari satu pendaftaran, dan
         * yang membedakan keduanya cuma nomor itu.
         */
        $this->assertStringNotContainsString('text: nomor', $blok,
            "Nomornya diserahkan lewat opsi `text`, yang diabaikan SweetAlert "
            . 'begitu `html` dipakai.');

        $this->assertStringContainsString('pdl-bukti-nomor', $blok,
            'Nomor pendaftarannya tidak ditulis di dialognya, jadi dua '
            . 'pendaftaran milik orang yang sama tidak bisa dibedakan.');
    }

    /** Layar rincian, tempat angkatan pendaftaran dipindahkan. */
    private function rincian(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2)
            . '/resources/views/account/pendaftaran_layanan/rincian.blade.php'
        );
    }

    /**
     * Isian angkatan di layar RINCIAN memakai pemilih yang sama.
     *
     * Di sana menunya <select> biasa tanpa Select2, dan angkatan Scopus Camp
     * bernama SAMA PERSIS berpuluh-puluh — yang membedakan cuma kode acak,
     * nomor, dan tanggalnya. Menu bawaan peramban tidak bisa dicari sama
     * sekali, jadi memindahkan satu pendaftaran berarti menggulung sambil
     * membaca satu per satu. Terukur: 174 pilihan di menu itu.
     */
    public function test_isian_angkatan_rincian_memakai_pemilih_yang_sama(): void
    {
        $isian = (string) file_get_contents(
            dirname(__DIR__, 2)
            . '/resources/views/account/pendaftaran_layanan/partials/isian.blade.php'
        );

        $this->assertMatchesRegularExpression(
            '/<select[^>]*name="\{\{ \$kolom \}\}"[\s\S]{0,200}?data-mis-pemilih=/', $isian,
            'Isian angkatan tidak ditandai untuk dicegat, jadi yang terbuka '
            . 'menu bawaan peramban yang tidak bisa dicari.'
        );

        $rincian = $this->rincian();

        $this->assertStringContainsString('window.misPemilih(menu, {', $rincian,
            'Layar rincian tidak memanggil pemilih bersama; perilakunya akan '
            . 'menyimpang dari layar daftar.');
    }

    /**
     * Menu bawaannya dicegat pada MOUSEDOWN, bukan click.
     *
     * Menu bawaan peramban terbuka pada mousedown; dicegat di click,
     * keduanya terbuka berbarengan — menu asli di belakang dialognya.
     *
     * Papan ketik ikut ditangkap: Enter, spasi, dan panah bawah adalah cara
     * membuka menu tanpa tetikus, dan dibiarkan, yang memakai papan ketik
     * tetap mendapat menu lama.
     */
    public function test_menu_bawaan_rincian_dicegat_sebelum_terbuka(): void
    {
        $isi = $this->rincian();

        $this->assertSame(1, preg_match(
            "/addEventListener\('mousedown', function \(acara\) \{([\s\S]*?)\}\);/",
            $isi, $cocok
        ), 'Pencegatnya tidak dipasang pada mousedown, jadi menu bawaannya '
            . 'sempat terbuka di belakang dialognya.');

        $this->assertStringContainsString("closest('select[data-mis-pemilih]')", $cocok[1]);
        $this->assertStringContainsString('preventDefault()', $cocok[1],
            'Menu bawaannya tidak dibatalkan, jadi keduanya terbuka bersamaan.');

        $this->assertSame(1, preg_match(
            "/addEventListener\('keydown', function \(acara\) \{([\s\S]*?)\}\);/",
            $isi, $papan
        ), 'Papan ketik tidak ditangkap; yang memakainya tetap mendapat menu lama.');

        foreach (["'Enter'", "' '", "'ArrowDown'"] as $tombol) {
            $this->assertStringContainsString($tombol, $papan[1],
                'Tombol ' . $tombol . ' tidak membuka pemilihnya.');
        }
    }

    /**
     * Pemilihnya TINGGAL DI SATU TEMPAT, bukan disalin ke tiap layar.
     *
     * Dua salinan yang menyimpang satu sama lain adalah persoalan yang baru
     * ketahuan sesudah salah satunya lama salah — dan yang satu dipakai
     * menyaring daftar, yang satu lagi memindahkan peserta beserta kursinya.
     */
    public function test_pemilihnya_tidak_disalin_ke_tiap_layar(): void
    {
        foreach (['layar', 'rincian'] as $berkas) {
            $this->assertStringNotContainsString('mis-pilih-daftar', $this->{$berkas}(),
                'Perakit pemilihnya disalin ke ' . $berkas . '; satu-satunya '
                . 'tempatnya mis-ui.js.');
        }

        $this->assertStringContainsString('window.misPemilih = misPemilih;', $this->pemilih(),
            'misPemilih() tidak dipasang ke window, jadi layar lain tidak bisa '
            . 'memanggilnya.');
    }

}
