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
            "/menuAngkatan\.value = tombol\.dataset\.nilai;([\s\S]{0,1200}?)Swal\.close\(\)/",
            $isi, $cocok
        ), 'Blok yang memasang pilihan dari pemilih tidak ditemukan.');

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
            '/var tambah = function \(opsi, labelKelompok\) \{([\s\S]*?)\n            \};/',
            $this->layar(), $cocok
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
            '/var bukaPemilihAngkatan = function \(\) \{([\s\S]*?)\n        \};/',
            $this->layar(), $cocok
        ), 'Fungsi pembuka pemilihnya tidak ditemukan.');

        $this->assertStringContainsString("menuAngkatan.querySelectorAll('optgroup')", $cocok[1],
            'Daftarnya tidak dibaca dari <select> saat dibuka, jadi ia bisa '
            . 'menawarkan angkatan layanan yang sedang tidak disaring.');
    }

}
