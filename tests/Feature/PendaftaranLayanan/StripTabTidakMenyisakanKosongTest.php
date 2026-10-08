<?php

namespace Tests\Feature\PendaftaranLayanan;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Deret tab layar rincian tidak menyisakan ruang kosong.
 *
 * Terukur sebelum diperbaiki, di kolom kanan layar rincian:
 *
 *     3 tab @1440px   sisa kanan 164px
 *     6 tab @1440px   baris ke-2 berisi SATU tab, sisa 576px
 *     6 tab @820px    baris ke-2 berisi SATU tab, sisa 552px
 *
 * Sesudahnya nol di semuanya (6px yang tersisa itu bantalan <ul>-nya).
 *
 * Yang dijaga di sini ATURANNYA, bukan hasil ukurnya: pengukurannya menuntut
 * peramban sungguhan, dan satu-satunya yang menentukan hasil itu dua baris
 * CSS di bawah.
 */
class StripTabTidakMenyisakanKosongTest extends TestCase
{
    private function gaya(): string
    {
        $isi = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );

        // Komentarnya dibuang: catatan di berkas itu MENYEBUTKAN aturan yang
        // sengaja tidak dipakai, beserta alasannya. Pemindai yang menghitung
        // komentar akan membacanya sebagai aturan yang berlaku.
        $isi = (string) preg_replace('#\{\{--.*?--\}\}#s', '', $isi);

        return (string) preg_replace('#/\*.*?\*/#s', '', $isi);
    }

    #[Test]
    public function tab_tumbuh_mengisi_barisnya(): void
    {
        $ada = preg_match('/#rin-tab\s*>\s*li\s*\{(?<isi>[^}]*)\}/', $this->gaya(), $cocok);

        $this->assertSame(1, $ada, 'Aturan lebar tab hilang.');

        $this->assertMatchesRegularExpression('/flex:\s*1\s+1\s+auto/', $cocok['isi'],
            'Tab tidak lagi tumbuh mengisi barisnya; sisa ruang kanan kembali jadi ruang kosong.');

        /*
         * `max-width` adalah justru yang membuat kosongnya.
         *
         * Nilai 200px dulu dipasang untuk menahan gejala lain: saat barisnya
         * membungkus, tab terakhir sendirian melar membagi seluruh lebar
         * baris. Tetapi batas itu juga menahan SEMUA tab di baris yang tidak
         * membungkus — dan di situlah 164px kosong itu lahir.
         */
        $this->assertMatchesRegularExpression('/max-width:\s*none/', $cocok['isi'],
            'Lebar tab dibatasi lagi; sisa ruang baris jadi ruang kosong.');
    }

    #[Test]
    public function bantalan_tab_dirampingkan_dan_timpaannya_berlaku(): void
    {
        /*
         * DIBALIK 8 Okt 2026, SESUDAH DITANYAKAN.
         *
         * Keputusan 4 Okt 2026 di mis-ui.css menuntut jarak tab sama persis
         * dengan halaman Profil dan menutup dengan "JANGAN menimpanya lagi
         * tanpa menanyakan dulu". Ditanyakan, dan pemiliknya memilih deret
         * sebaris: keenam tab kurang 10px untuk muat (752px lebar alami +
         * 30px jeda = 782px melawan strip 772px).
         *
         * `!important` itu bagian dari perbaikannya, bukan hiasan: style.css
         * memasang padding-left/right 15px !important. Timpaan tanpa itu
         * pernah dipasang di sini dan DIUKUR tidak berlaku sama sekali —
         * bantalannya tetap 15px. Aturan mati yang terbaca seolah bekerja
         * lebih buruk daripada tidak ada aturan.
         */
        $ada = preg_match('/#rin-tab\s+\.nav-link\s*\{(?<isi>[^}]*padding[^}]*)\}/', $this->gaya(), $cocok);

        $this->assertSame(1, $ada,
            'Timpaan bantalan tab hilang; keenam tab kembali pecah dua baris di 1440px.');

        $this->assertMatchesRegularExpression('/padding-left:\s*13px\s*!important/', $cocok['isi'],
            'Bantalan kiri tab bukan 13px ber-!important.');

        $this->assertMatchesRegularExpression('/padding-right:\s*13px\s*!important/', $cocok['isi'],
            'Bantalan kanan tab bukan 13px ber-!important.');
    }

    #[Test]
    public function pembalikannya_dicatat_di_mis_ui(): void
    {
        /*
         * Catatan 4 Okt 2026 ada di mis-ui.css, bukan di layar ini — jadi
         * siapa pun yang membacanya nanti akan menyimpulkan jaraknya masih
         * seragam dengan Profil, padahal tidak lagi. Pembalikannya harus
         * tercatat di tempat yang sama dengan keputusan yang dibalik.
         */
        $misUi = file_get_contents(public_path('assets/css/mis-ui.css'));

        $this->assertStringContainsString('DIBALIK 8 Okt 2026', $misUi,
            'Pembalikan keputusan jarak tab tidak tercatat di mis-ui.css, tempat '
            . 'keputusan aslinya tinggal.');

        $this->assertStringContainsString('#rin-tab', $misUi,
            'Catatannya tidak menyebut bahwa yang dikecualikan hanya #rin-tab.');
    }

    #[Test]
    public function aturan_bantalan_15px_di_mis_ui_masih_berlaku(): void
    {
        /*
         * Prasyarat uji di atas. Kalau !important itu suatu hari dibuang,
         * alasan nomor dua gugur — dan keputusan nomor satu harus ditanyakan
         * ulang, bukan diam-diam ditimpa.
         */
        $style = file_get_contents(public_path('assets/css/style.css'));

        $this->assertMatchesRegularExpression(
            '/\.nav-pills\s+\.nav-item\s+\.nav-link\s*\{[^}]*padding-left:\s*15px\s*!important/s',
            $style,
            'style.css tidak lagi memaksa bantalan 15px; tinjau ulang catatan di mis-ui.css.'
        );
    }
}
