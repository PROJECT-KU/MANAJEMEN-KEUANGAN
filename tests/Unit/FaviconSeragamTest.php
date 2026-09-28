<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Seluruh layar back office memakai favicon yang sama dengan halaman masuk.
 *
 * Sebelum uji ini dipasang ada TUJUH berkas favicon berbeda yang dipakai
 * bergantian — logo-pwa.png, scopus.jpg, logonew1.png, dan seterusnya —
 * sehingga ikon di tab peramban berubah-ubah tergantung layar mana yang
 * sedang dibuka. Tidak ada yang galat, jadi tidak ada yang menyadarinya.
 */
class FaviconSeragamTest extends TestCase
{
    private const IKON = 'assets/img/mis-favicon.png';

    /**
     * Situs publik memakai lambang Rumah Scopus, bukan lambang MIS: itu
     * permukaan merek yang berbeda dan memang disengaja.
     */
    private const DIKECUALIKAN = [
        'public/layout/header.blade.php',
        'public/plagiasi/layout/header.blade.php',
    ];

    public function test_semua_layar_back_office_memakai_favicon_halaman_masuk(): void
    {
        $akar = dirname(__DIR__, 2) . '/resources/views';
        $berkas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar));

        $melenceng = [];

        foreach ($berkas as $b) {
            if (! $b->isFile() || ! str_ends_with($b->getFilename(), '.blade.php')) {
                continue;
            }

            $relatif = str_replace($akar . '/', '', $b->getPathname());

            if (in_array($relatif, self::DIKECUALIKAN, true)) {
                continue;
            }

            $isi = (string) file_get_contents($b->getPathname());

            // Tiap <link ...> yang menyebut rel icon, apa pun urutan atributnya.
            preg_match_all('/<link[^>]*rel=["\'][^"\']*icon[^"\']*["\'][^>]*>/i', $isi, $c);

            foreach ($c[0] as $tag) {
                if (! str_contains($tag, self::IKON)) {
                    $melenceng[] = $relatif . ' → ' . trim(preg_replace('/\s+/', ' ', $tag));
                }
            }
        }

        $this->assertSame([], $melenceng, "Layar berikut memakai favicon selain halaman masuk:\n- " . implode("\n- ", $melenceng) . "\n");
    }

    public function test_halaman_masuk_masih_jadi_acuannya(): void
    {
        $auth = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layouts/auth.blade.php');

        $this->assertStringContainsString(self::IKON, $auth, 'Acuannya berubah; uji di atas ikut harus disesuaikan.');
    }
}
