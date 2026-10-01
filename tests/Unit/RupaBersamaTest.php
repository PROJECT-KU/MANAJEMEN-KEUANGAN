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
    private function berkasBlade(): array
    {
        $keluar = [];

        $jalan = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
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
            '.mis-urut', '.mis-urut-ponsel', '.mis-saring-kartu', '.mis-saring',
            '.mis-saring-cari', '.mis-saring-pilih', '.mis-saring-hapus',
            '.mis-saring-sibuk', '.mis-hasil'] as $kelas) {
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
        $this->assertGreaterThanOrEqual(75, (int) $cocok[1],
            'mis-ui.css berubah tetapi penanda ?v=-nya belum dinaikkan');

        preg_match("/mis-ui\.js'\) \}\}\?v=(\d+)/", $layout, $cocokJs);

        $this->assertNotEmpty($cocokJs, 'penanda ?v= pada mis-ui.js tidak ketemu');
        $this->assertGreaterThanOrEqual(13, (int) $cocokJs[1],
            'mis-ui.js berubah tetapi penanda ?v=-nya belum dinaikkan');
    }
}
