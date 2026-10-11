<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pemilih pengubah di berkas cetak harus SEKUAT pemilih dasarnya.
 *
 * Dompdf tidak mengeluh soal apa pun. Aturan yang kalah bobot diam saja,
 * dan yang keluar berkas PDF yang tercetak rapi tetapi kehilangan artinya.
 *
 * Terjadi persis begitu saat merancang ulang ekspor ini: ".kartu-pokok"
 * (0,1,0) kalah dari ".keterangan-berkas td.kartu" (0,2,1), sehingga
 * kelima kartu ringkasan tercetak abu-abu seragam — kartu "Belum bayar"
 * kehilangan kuningnya dan "Uang masuk" kehilangan hijaunya. Sempat
 * disangka batasan Dompdf pada border-collapse, dan diuji: dengan collapse
 * maupun separate hasilnya sama, sebabnya memang bobot pemilih.
 */
class RupaEksporPdfTest extends TestCase
{
    private const BERKAS = [
        'account/pendaftaran_layanan/ekspor-pdf.blade.php',
        'account/pendaftaran_layanan/presensi-pdf.blade.php',
    ];

    /** @return array<string, string> */
    private function templat(): array
    {
        $hasil = [];

        foreach (self::BERKAS as $relatif) {
            $penuh = dirname(__DIR__, 2) . '/resources/views/' . $relatif;

            $this->assertFileExists($penuh);

            $hasil[$relatif] = (string) file_get_contents($penuh);
        }

        return $hasil;
    }

    /**
     * Pengubah warna kartu ringkasan tidak boleh ditulis sebagai kelas
     * telanjang.
     */
    public function test_pengubah_kartu_sekuat_pemilih_dasarnya(): void
    {
        $isi = $this->templat()['account/pendaftaran_layanan/ekspor-pdf.blade.php'];

        foreach (['kartu-pokok', 'kartu-awas', 'kartu-uang'] as $kelas) {
            $this->assertSame(0,
                preg_match('/^\s*\.' . preg_quote($kelas, '/') . '\s*\{/m', $isi),
                "Pengubah .{$kelas} ditulis sebagai kelas telanjang; bobotnya "
                . 'kalah dari ".keterangan-berkas td.kartu" dan warnanya tidak '
                . 'akan tercetak — tanpa galat apa pun.');

            $this->assertSame(1,
                preg_match('/\.keterangan-berkas\s+td\.' . preg_quote($kelas, '/') . '\s*\{/', $isi),
                "Pengubah .{$kelas} tidak ditemukan dengan pemilih selengkap "
                . 'pemilih dasarnya.');
        }
    }

    /**
     * Gambar di berkas cetak DITANAM, tidak ditautkan.
     *
     * Dompdf dijalankan dengan isRemoteEnabled = false pada kedua ekspor,
     * jadi alamat http apa pun tidak akan diambil dan gambarnya hilang
     * tanpa pesan.
     */
    public function test_gambar_berkas_cetak_ditanam(): void
    {
        foreach ($this->templat() as $relatif => $isi) {
            $this->assertStringContainsString('base64_encode', $isi,
                $relatif . ' menautkan gambarnya; Dompdf tanpa isRemoteEnabled '
                . 'tidak akan mengambilnya.');

            $this->assertSame(0, preg_match("/asset\('assets\/img/", $isi),
                $relatif . ' merakit alamat gambarnya dengan asset().');
        }
    }

    /**
     * Judul di kepala berkas cetak terpusat pada LEBAR KERTAS.
     *
     * Kedua lajur tepinya harus sama lebar. Timpang, judulnya terpusat pada
     * SISA lebar — dan itu bukan tengah. Terukur pada hasil cetaknya saat
     * lajur logo 190px sementara lajur kanan tanpa lebar tetap: titik tengah
     * judulnya jatuh sekitar sepertiga lebar kertas.
     *
     * Dompdf tidak mengeluh; yang keluar berkas yang terbaca miring sebelah
     * tanpa ada yang bisa menunjuk sebabnya.
     */
    public function test_judul_kepala_terpusat_pada_lebar_kertas(): void
    {
        foreach ($this->templat() as $relatif => $isi) {
            $mulai = strpos($isi, '<div class="kepala">');

            $this->assertNotFalse($mulai, $relatif . ' tidak punya kepala.');

            // Hanya tabel PERTAMA di dalam kepalanya: berkas presensi punya
            // tabel kedua berisi garisnya, dan lebarnya bukan lajur tepi.
            $akhir = strpos($isi, '</table>', $mulai);
            $kepala = substr($isi, $mulai, $akhir - $mulai);

            preg_match_all('/width="(\d+)"/', $kepala, $lebar);

            $this->assertGreaterThanOrEqual(2, count($lebar[1]),
                $relatif . ': kepalanya tidak punya dua lajur tepi berlebar tetap.');

            $this->assertSame($lebar[1][0], $lebar[1][count($lebar[1]) - 1],
                $relatif . ': lajur tepi kepalanya tidak sama lebar ('
                . $lebar[1][0] . ' vs ' . $lebar[1][count($lebar[1]) - 1]
                . '), jadi judulnya terpusat pada sisa lebar — bukan pada '
                . 'lebar kertasnya.');

            /*
             * Dua ejaan diterima: atribut align="center" dan kelas .tengah
             * milik berkas presensi. Keduanya sungguh memusatkan, dan
             * memaksa satu ejaan berarti menolak berkas yang sudah benar.
             * Yang dijaga tetap sama: tanpa KEDUANYA, penjaga ini merah.
             */
            $this->assertTrue(
                str_contains($kepala, 'align="center"')
                    || str_contains($kepala, 'class="tengah"'),
                $relatif . ': lajur judulnya tidak terpusat.'
            );
        }
    }

    /**
     * Kepala kolom angka DISEJAJARKAN dengan isinya.
     *
     * Semuanya rata kiri, kepala "Total bayar" berdiri jauh dari angka yang
     * diwakilinya dan mata harus menyeberangi lajur untuk mencocokkannya.
     */
    public function test_kepala_kolom_angka_sejajar_dengan_isinya(): void
    {
        $isi = $this->templat()['account/pendaftaran_layanan/ekspor-pdf.blade.php'];

        $this->assertStringContainsString('<th width="11%" class="kanan">Total bayar</th>', $isi,
            'Kepala kolom nominal tidak rata kanan seperti angkanya.');

        $this->assertStringContainsString('<th width="5%" class="tengah">Orang</th>', $isi,
            'Kepala kolom jumlah orang tidak terpusat seperti angkanya.');

        // "Org" terlalu ringkas untuk kepala kolom cetak: berkas ini dibaca
        // orang yang tidak membuka layarnya.
        $this->assertSame(0, preg_match('/>Org</', $isi));
    }
}
