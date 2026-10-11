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
}
