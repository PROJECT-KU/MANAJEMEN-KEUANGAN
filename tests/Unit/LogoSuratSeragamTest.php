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

    /**
     * Surat yang sengaja memakai logo YAYASAN, bukan logo alat.
     *
     * logo-email.png adalah logo MIS — "Management Integration System by
     * Rumah Scopus" — dan itu memang pantas untuk surat akun dan keamanan,
     * yang pembacanya orang dalam.
     *
     * Surat di daftar ini dibaca PENDAFTAR, bukan orang dalam. Mengirimi
     * calon peserta logo alat administrasi internal menyampaikan merek yang
     * salah, jadi yang dipakai LogoRSC.png. Ditetapkan 3 Okt 2026 atas
     * permintaan pemilik produk sesudah melihat suratnya di kotak masuk.
     *
     * Daftar ini sengaja berupa daftar tertutup, bukan pengecualian terbuka:
     * surat baru tetap memakai logo alat kecuali ada yang menuliskannya di
     * sini beserta alasannya.
     */
    private const SURAT_YAYASAN = [
        'emails/pendaftaran-dicatat.blade.php',
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

    public function test_tidak_ada_templat_surat_yang_memakai_logo_lama(): void
    {
        $melenceng = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            if (in_array($relatif, self::SURAT_YAYASAN, true)) {
                // Justru HARUS memakai logo yayasan; dibalik pemeriksaannya.
                $this->assertStringContainsString(
                    'LogoRSC',
                    $isi,
                    $relatif . ' terdaftar sebagai surat untuk pendaftar, jadi logonya logo yayasan.'
                );

                continue;
            }

            if (str_contains($isi, 'LogoRSC')) {
                $melenceng[] = $relatif;
            }
        }

        $this->assertSame([], $melenceng, "Templat surat berikut masih memakai logo lama:\n- " . implode("\n- ", $melenceng) . "\n");
    }

    public function test_logo_surat_disisipkan_bukan_ditautkan(): void
    {
        $melenceng = [];

        foreach ($this->templatSurat() as $relatif => $isi) {
            if (! str_contains($isi, self::LOGO)) {
                continue;
            }

            /*
             * asset() menghasilkan tautan ke APP_URL. Aplikasi surat kebanyakan
             * memblokir gambar jarak jauh, dan APP_URL di sini masih
             * http://localhost — logonya tidak akan pernah tampil.
             */
            if (! str_contains($isi, "\$message->embed(public_path('" . self::LOGO . "'))")) {
                $melenceng[] = $relatif;
            }
        }

        $this->assertSame([], $melenceng, "Templat surat berikut menautkan logonya, bukan menyisipkannya:\n- " . implode("\n- ", $melenceng) . "\n");
    }

    public function test_berkas_logonya_memang_ada(): void
    {
        $this->assertFileExists(dirname(__DIR__, 2) . '/public/' . self::LOGO);
    }
}
