<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penjaga perangkat rupa milik bersama.
 *
 * Ubin ringkasan dan kepala kolom pengurut lahir di layar Data Pelanggan
 * dengan nama .pel-*. Begitu Angkatan Layanan butuh keduanya, godaan paling
 * mudah adalah menyalinnya jadi .ang-* — dan itu persis keluhan yang sudah
 * tertulis di kepala berkas layar Data Pelanggan sendiri: dua layar yang
 * sama-sama daftar data tampil berbeda, dan tiap perbaikan rupa dikerjakan
 * dua kali.
 *
 * Uji ini membaca sumbernya, bukan halamannya: salinan yang belum dipakai pun
 * sudah ketahuan sebelum sempat melenceng.
 */
class RupaBersamaTest extends TestCase
{
    /**
     * Berkas Blade LAYAR ADMIN saja.
     *
     * Sistem rupa .mis- hanya berlaku di sana. Halaman publik memakai kerangka
     * dan palet yang sama sekali lain — tidak satu pun memuat kelas .mis-, dan
     * memindainya membuat uji ini menuduh nama kelas yang kebetulan berakhiran
     * "-ubin" padahal artinya memang berbeda (ubin ikon status pada halaman
     * Webinar Eksklusif, misalnya).
     */
    private function berkasBlade(): array
    {
        $keluar = [];

        $jalan = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views/account'))
        );

        foreach ($jalan as $berkas) {
            if ($berkas->isFile() && str_ends_with($berkas->getFilename(), '.blade.php')) {
                $keluar[$berkas->getPathname()] = file_get_contents($berkas->getPathname());
            }
        }

        return $keluar;
    }

    #[Test]
    public function ubin_dan_pengurut_tidak_punya_salinan_berawalan_layar(): void
    {
        /*
         * Yang dicari nama kelas berawalan apa pun selain mis-, bukan cuma
         * pel-: layar berikutnya akan memakai awalannya sendiri.
         *
         * Hanya di dalam atribut class. Versi pertama uji ini mencari di
         * seluruh berkas dan menuduh `id="lyn-ubin"` di layar Tarif — itu
         * pengenal ubin ikon di dalam dialog, sama sekali bukan ubin
         * ringkasan.
         */
        $pola = '/class="[^"]*\b(?!mis-)[a-z]{2,4}-(?:ubin|ringkas)\b[^"]*"/';

        $salinan = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            if (preg_match_all($pola, $isi, $cocok)) {
                $salinan[basename(dirname($jalur)) . '/' . basename($jalur)] = array_unique($cocok[0]);
            }
        }

        $this->assertSame([], $salinan, 'ada salinan ubin ringkasan: ' . json_encode($salinan));
    }

    #[Test]
    public function kepala_kolom_pengurut_lewat_partial_bersama(): void
    {
        $sendiri = [];

        foreach ($this->berkasBlade() as $jalur => $isi) {
            // Partial bersamanya sendiri jelas boleh memakai kelas itu.
            if (str_ends_with($jalur, 'partials/urut-kolom.blade.php')) {
                continue;
            }

            if (preg_match('/class="(?!mis-urut)[a-z]{2,4}-urut[ "]/', $isi)) {
                $sendiri[] = basename(dirname($jalur)) . '/' . basename($jalur);
            }
        }

        $this->assertSame([], $sendiri, 'masih ada kepala kolom pengurut sendiri: ' . implode(', ', $sendiri));
    }

    #[Test]
    public function kelas_bersamanya_memang_ada_di_mis_ui(): void
    {
        $css = file_get_contents(public_path('assets/css/mis-ui.css'));

        foreach (['.mis-ringkas', '.mis-ubin', '.mis-ubin-angka', '.mis-ubin-label',
            '.mis-ringkas-petunjuk',
            '.mis-urut', '.mis-urut-ponsel', '.mis-saring-kartu', '.mis-saring',
            '.mis-saring-cari', '.mis-saring-pilih', '.mis-saring-hapus',
            '.mis-saring-sibuk', '.mis-hasil',
            // Ukuran sentuh kotak centang: dua layar beraksi massal pernah
            // menulis aturannya sendiri-sendiri dan keduanya berakhir 16x16.
            '.mis-centang', '.mis-centang-bungkus'] as $kelas) {
            $this->assertStringContainsString($kelas . ' {', $css,
                $kelas . ' dipakai layar tetapi tidak didefinisikan di mis-ui.css');
        }
    }

    #[Test]
    public function partial_pengurut_bersama_ada_dan_yang_lama_sudah_tiada(): void
    {
        $this->assertFileExists(resource_path('views/partials/urut-kolom.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/account/customer/partials/urut.blade.php'));
    }

    #[Test]
    public function dua_layar_yang_sudah_dipindah_tidak_merakit_sendiri_lagi(): void
    {
        /*
         * Penandanya DOMParser: menukar sepotong halaman hasil permintaan latar
         * hanya bisa dikerjakan dengan mengurai jawabannya, jadi kemunculannya
         * di layar ini berarti perilakunya ditulis ulang padahal sudah ada di
         * mis-ui.js.
         *
         * Dibatasi dua layar yang memang sudah dipindah. SEMBILAN layar lama —
         * clinik_scopus, pengguna, gaji, presensi, promo, analisis_bibliometrik,
         * pendaftaran_scopus_camp, dan dua layar kategori yang sudah
         * digantikan — masih punya salinannya sendiri. Memasukkan mereka ke
         * sini berarti uji yang merah sejak lahir, dan uji merah yang dibiarkan
         * berhenti dibaca orang.
         */
        foreach ([
            'account/customer/index.blade.php',
            'account/kategori_layanan/index.blade.php',
        ] as $relatif) {
            $this->assertStringNotContainsString('new DOMParser(',
                file_get_contents(resource_path('views/' . $relatif)), $relatif);
        }
    }

    #[Test]
    public function layar_daftar_memakai_perjanjian_saringan_bersama(): void
    {
        // Dua layar daftar besar MIS; keduanya harus memakai atribut yang sama.
        foreach ([
            'account/customer/index.blade.php',
            'account/kategori_layanan/index.blade.php',
        ] as $relatif) {
            $isi = file_get_contents(resource_path('views/' . $relatif));

            $this->assertStringContainsString('data-mis-saring=', $isi, $relatif);
            $this->assertStringContainsString('data-mis-cari', $isi, $relatif);
            $this->assertStringContainsString('data-mis-terapkan', $isi, $relatif);
            $this->assertStringContainsString('class="mis-saring-kartu"', $isi, $relatif);
        }
    }

    #[Test]
    public function barisan_ubin_yang_digeser_memakai_perjanjian_yang_sama(): void
    {
        /*
         * Di bawah 1100px ubinnya jadi barisan yang digeser. Pembungkus
         * data-mis-geser itulah yang dicari mis-ui.js untuk menyalakan
         * petunjuknya — tanpa pembungkusnya, barisannya tetap bisa digeser
         * tetapi tidak ada satu pun tanda bahwa ia bisa.
         */
        foreach ([
            'account/customer/index.blade.php',
            'account/kategori_layanan/index.blade.php',
        ] as $relatif) {
            $isi = file_get_contents(resource_path('views/' . $relatif));

            $this->assertStringContainsString('data-mis-geser', $isi, $relatif);
            $this->assertStringContainsString('mis-ringkas-petunjuk', $isi, $relatif);
        }
    }

    #[Test]
    public function petunjuk_geser_tersembunyi_sebagai_bawaan(): void
    {
        /*
         * Aturannya harus di LUAR media query. Ditaruh di dalamnya, di layar
         * lebar <p>-nya tidak punya display sama sekali dan tampil sebagai
         * paragraf biasa — mengajak menggeser barisan yang tidak bergeser ke
         * mana-mana. Terukur begitu pada percobaan pertama.
         */
        $css = file_get_contents(public_path('assets/css/mis-ui.css'));

        $sebelumMedia = substr($css, 0, strpos($css, '@media (max-width: 1100px)'));

        $this->assertStringContainsString('.mis-ringkas-petunjuk { display: none; }', $sebelumMedia,
            'petunjuk geser harus tersembunyi sebagai bawaan, di luar media query');
    }

    #[Test]
    public function kotak_centang_daftar_memakai_ukuran_bersama(): void
    {
        /*
         * Angkatan Layanan dan Data Pelanggan sama-sama punya aksi massal, dan
         * sebelum ini keduanya menulis sendiri `width: 16px` untuk kotak
         * centangnya — di bawah batas sasaran sentuh 24x24, untuk kendali yang
         * memilih baris yang akan DIHAPUS.
         */
        foreach ([
            'account/kategori_layanan/index.blade.php',
            'account/customer/index.blade.php',
        ] as $layar) {
            $isi = file_get_contents(resource_path('views/' . $layar));

            $this->assertStringContainsString('mis-centang-bungkus', $isi,
                $layar . ' belum memakai pembungkus sasaran sentuh bersama');

            $this->assertStringNotContainsString("-sel input {", $isi,
                $layar . ' masih menentukan ukuran kotak centangnya sendiri');
        }
    }



    /**
     * Isi mis-ui.css TANPA komentar, dan dengan spasi dirapatkan.
     *
     * Komentarnya WAJIB dibuang lebih dulu. Penjaga bantalan tab di bawah
     * sempat ditulis tanpa ini dan ia LULUS walau aturannya sudah dirusak:
     * yang dicocokkannya ternyata kalimat di komentar yang menyebut pemilih
     * itu, bukan pemilihnya sendiri. Lebih jauh lagi, karena komentar tidak
     * memuat tanda `{`, pola `[^{]*` bisa merentang dari sebutan di komentar
     * sampai ke kurung aturan yang SALAH beberapa baris di bawahnya.
     */
    private function cssTanpaKomentar(): string
    {
        $css = file_get_contents(public_path('assets/css/mis-ui.css'));

        return trim(preg_replace('/\s+/', ' ', preg_replace('#/\*.*?\*/#s', ' ', $css) ?? '') ?? '');
    }

    #[Test]
    public function panel_tab_yang_tidak_aktif_benar_benar_disembunyikan(): void
    {
        /*
         * Bootstrap menyembunyikan panel lewat `.tab-content > .tab-pane`.
         * Pembungkus tab di MIS bernama `.mis-tab-isi`, jadi aturan itu TIDAK
         * cocok kecuali penulisnya ingat menyelipkan `<div class="tab-content">`
         * di dalamnya. Layar Ubah Pelanggan ingat; layar Rincian Pendaftaran
         * tidak.
         *
         * Akibatnya ketujuh panelnya ada di tata letak sekaligus, tak terlihat
         * karena `.fade` membuatnya `opacity: 0`, tetapi utuh menempati
         * ruangnya. Terukur di peramban sebelum perbaikan: halaman 3171px
         * padahal panel yang terlihat 478px, kartu kanan 2883px, dan di
         * sebelah kiri menganga 2176px kosong — dan mengganti tab tidak
         * mengubah tingginya satu piksel pun.
         *
         * Yang lebih berbahaya daripada ruang terbuang: 38 kendali di panel
         * tak terlihat tetap dirender, jadi tetap bisa difokus dengan Tab dan
         * diklik — termasuk tombol "Hapus pendaftaran ini".
         *
         * Dijaga dari sumbernya karena tidak ada peramban di dalam uji; yang
         * dituntut adalah ADANYA aturan yang membuat .mis-tab-isi berdiri
         * sendiri, bukan ingatan penulis layar berikutnya.
         */
        $bersih = $this->cssTanpaKomentar();

        $this->assertStringContainsString('.mis-tab-isi > .tab-pane { display: none; }', $bersih,
            'Panel tab yang tidak aktif tidak disembunyikan: ketujuhnya akan menumpuk di satu halaman.');

        $this->assertStringContainsString('.mis-tab-isi > .tab-pane.active { display: block; }', $bersih,
            'Panel yang AKTIF harus dikembalikan jadi terlihat; tanpa ini seluruh tab jadi kosong.');
    }

    #[Test]
    public function tab_yang_menghapus_diberi_rupa_bahaya(): void
    {
        /*
         * Di antara enam tab yang sekadar berpindah tampilan, satu tab yang
         * menghapus pendaftaran tampil persis sama — sewarna dengan "Peserta"
         * tepat di sebelahnya. Yang membedakan hanya kata "Hapus".
         *
         * Penggunanya awam, dan tab bukan tempat yang orang baca dengan
         * cermat. Merahnya harus ada TANPA hover.
         *
         * Ini penanda, bukan pengaman: membuka tabnya tidak menghapus apa pun,
         * panel di baliknya masih meminta penegasan.
         */
        $this->assertStringContainsString('.mis-tab .nav-link.mis-tab-bahaya {', $this->cssTanpaKomentar(),
            'Rupa bahaya untuk tab penghapus tidak didefinisikan di mis-ui.css');

        $rincian = file_get_contents(resource_path('views/account/pendaftaran_layanan/rincian.blade.php'));

        $this->assertStringContainsString("'mis-tab-bahaya'", $rincian,
            'Tab Hapus di layar Rincian tidak memakai rupa bahaya.');
    }

    #[Test]
    public function bantalan_tab_tidak_dikalahkan_aturan_global(): void
    {
        /*
         * style.css memasang `.nav-pills .nav-item .nav-link { padding-left:
         * 15px !important; padding-right: 15px !important }`. Nilai 10px yang
         * tertulis di `.mis-tab .nav-link` karena itu TIDAK PERNAH berlaku.
         *
         * Terukur akibatnya: ketujuh tab layar Rincian menuntut 839px
         * sementara stripnya 812px, jadi "Hapus" terlempar sendirian ke baris
         * kedua. Dengan 10px tuntutannya 769px dan ketujuhnya muat satu baris.
         *
         * Timpaannya butuh bobot (0,4,0): percobaan dengan `.mis-tab
         * .nav-link` (0,2,0) sama-sama `!important` dan tetap kalah — terukur
         * bantalannya masih 15px sesudah aturannya terpasang.
         */
        $bersih = $this->cssTanpaKomentar();

        $this->assertStringContainsString('.mis-tab.nav-pills .nav-item .nav-link', $bersih,
            'Timpaan bantalan tab harus cukup berbobot untuk mengalahkan .nav-pills .nav-item .nav-link');

        $this->assertMatchesRegularExpression(
            '/\.mis-tab\.nav-pills [^{]*\{ padding-left: 10px !important; padding-right: 10px !important; \}/',
            $bersih,
            'Bantalan mendatar tab tidak dipaksa kembali ke 10px.'
        );
    }

    #[Test]
    public function penanda_versi_css_ikut_naik(): void
    {
        /*
         * Berkas CSS-nya ditembolok peramban selamanya tanpa penanda ?v=.
         * Aturan baru yang tidak dibarengi penanda naik berarti layarnya
         * berubah untuk yang belum pernah membuka, dan tidak berubah untuk
         * yang sudah — bug yang mustahil ditiru di mesin sendiri.
         */
        $layout = file_get_contents(resource_path('views/layouts/account.blade.php'));

        preg_match("/mis-ui\.css'\) \}\}\?v=(\d+)/", $layout, $cocok);

        $this->assertNotEmpty($cocok, 'penanda ?v= pada mis-ui.css tidak ketemu');
        $this->assertGreaterThanOrEqual(89, (int) $cocok[1],
            'mis-ui.css berubah tetapi penanda ?v=-nya belum dinaikkan');

        preg_match("/mis-ui\.js'\) \}\}\?v=(\d+)/", $layout, $cocokJs);

        $this->assertNotEmpty($cocokJs, 'penanda ?v= pada mis-ui.js tidak ketemu');
        $this->assertGreaterThanOrEqual(15, (int) $cocokJs[1],
            'mis-ui.js berubah tetapi penanda ?v=-nya belum dinaikkan');
    }
}
