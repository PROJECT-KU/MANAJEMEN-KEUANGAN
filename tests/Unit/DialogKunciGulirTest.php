<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penguncian guliran latar saat dialog terbuka.
 *
 * Perilakunya sendiri hanya terlihat di peramban, tetapi dua kesalahan yang
 * membuatnya gagal bisa dilihat dari sumbernya — dan keduanya sudah terjadi.
 */
class DialogKunciGulirTest extends TestCase
{
    private function css(): string
    {
        return file_get_contents(public_path('assets/css/mis-ui.css'));
    }

    #[Test]
    public function latar_dikunci_lewat_kelas_bersama(): void
    {
        /*
         * showModal() membuat sisa halaman tidak bisa disentuh, tetapi TIDAK
         * mengunci gulirannya — terukur, roda tetikus di atas latarnya
         * menggulung halaman 400px di belakang dialog yang terbuka.
         */
        $this->assertStringContainsString('body.mis-dialog-terbuka', $this->css());

        $js = file_get_contents(public_path('assets/js/mis-ui.js'));

        $this->assertStringContainsString('mis-dialog-terbuka', $js,
            'Kelasnya dipasang mis-ui.js supaya berlaku untuk dialog mana pun.');
        $this->assertStringContainsString('dialog[open]', $js);
    }

    #[Test]
    public function penahan_guliran_tidak_dipasang_ke_seluruh_keturunan_dialog(): void
    {
        /*
         * Regresi yang sudah pernah terjadi: `dialog *` membuat TIAP textarea
         * yang isinya muat ikut menahan guliran, karena penerusan ke induknya
         * diblokir. Terukur, dialog yang perlu digulung (781px isi dalam 710px
         * ruang) sama sekali tidak bergerak saat roda berada di atas kotak
         * Fasilitas.
         *
         * Wadah gulirnya sendiri yang menyatakan `contain`, bukan semua
         * keturunannya.
         */
        $css = $this->css();

        $this->assertDoesNotMatchRegularExpression(
            '/dialog\s*\*\s*\{[^}]*overscroll-behavior/s',
            $css,
            'overscroll-behavior tidak boleh dipasang ke `dialog *`.'
        );

        $this->assertMatchesRegularExpression(
            '/^dialog\s*\{[^}]*overscroll-behavior:\s*contain/m',
            $css,
            'Dialognya sendiri tetap menahan guliran.'
        );
    }

    #[Test]
    public function wadah_gulir_dialog_tarif_menahan_gulirannya_sendiri(): void
    {
        $blade = file_get_contents(
            resource_path('views/account/clinik_scopus_biaya_persesi/index.blade.php')
        );

        $this->assertMatchesRegularExpression(
            '/\.tar-dialog-isi\s*\{[^}]*overscroll-behavior:\s*contain/s',
            $blade
        );
    }

    #[Test]
    public function penanda_singgahan_dinaikkan_saat_berkas_bersama_berubah(): void
    {
        /*
         * mis-ui.css dan mis-ui.js disinggahi peramban. Tanpa penanda ?v= yang
         * naik, perubahannya tidak sampai ke orang yang sudah pernah membuka
         * halamannya — dan perbaikan yang benar terlihat tidak berpengaruh.
         */
        $layout = file_get_contents(resource_path('views/layouts/account.blade.php'));

        preg_match("/mis-ui\.css'\) \}\}\?v=(\d+)/", $layout, $css);
        preg_match("/mis-ui\.js'\) \}\}\?v=(\d+)/", $layout, $js);

        $this->assertNotEmpty($css, 'Penanda ?v= pada mis-ui.css harus ada.');
        $this->assertNotEmpty($js, 'Penanda ?v= pada mis-ui.js harus ada.');

        // Angka saat penguncian guliran ini ditambahkan; naik lagi berikutnya.
        $this->assertGreaterThanOrEqual(70, (int) $css[1]);
        $this->assertGreaterThanOrEqual(12, (int) $js[1]);
    }
}
