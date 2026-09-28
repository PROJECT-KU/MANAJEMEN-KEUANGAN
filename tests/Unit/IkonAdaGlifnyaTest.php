<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Menjaga agar tidak ada nama ikon Font Awesome 6 dipakai di tampilan.
 *
 * Yang terpasang Font Awesome Free 5.5.0. Nama FA6 seperti fa-circle-info
 * atau fa-xmark TIDAK membuat halaman galat — ikonnya sekadar tidak
 * tergambar, lebarnya nol, dan tulisan di sebelahnya jadi menempel ke tepi.
 * Gejalanya tidak menyebut sebabnya, jadi mudah dikira salah jarak.
 */
class IkonAdaGlifnyaTest extends TestCase
{
    /** Kelas Font Awesome yang mengatur ukuran/gerak, bukan nama ikon. */
    private const BUKAN_IKON = [
        'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-6x', 'fa-7x', 'fa-8x', 'fa-9x', 'fa-10x',
        'fa-lg', 'fa-sm', 'fa-xs', 'fa-fw', 'fa-li', 'fa-ul', 'fa-border', 'fa-spin',
        'fa-pulse', 'fa-solid', 'fa-regular', 'fa-brands', 'fa-stack', 'fa-inverse',
        'fa-rotate-90', 'fa-rotate-180', 'fa-rotate-270', 'fa-flip-horizontal',
        'fa-flip-vertical', 'fa-caret-',
    ];

    /**
     * Utang yang sudah ada sebelum penjaga ini dipasang (28 Sep 2026).
     * Daftar ini hanya boleh MENGECIL. Menambah nama baru ke sini berarti
     * sengaja memasang ikon yang tidak akan tergambar.
     */
    private const UTANG_LAMA = [
        'fa-circle-user',        // layouts/version.blade.php
        'fa-clock-rotate-left',  // layouts/version.blade.php
        'fa-grid-2',             // account/clinik_scopus_riwayat_pemesanan/index.blade.php
        'fa-house',              // layouts/version.blade.php
        'fa-image-slash',        // account/clinik_scopus_riwayat_pemesanan/detail.blade.php
        'fa-list-check',         // layouts/version.blade.php
        'fa-rectangle-list',     // layouts/version.blade.php
        'fa-user-gear',          // layouts/version.blade.php
    ];

    public function test_semua_nama_ikon_di_tampilan_ada_glifnya(): void
    {
        $akar = dirname(__DIR__, 2);
        $gaya = file_get_contents($akar . '/public/assets/modules/fontawesome/css/all.css');

        $ketemu = [];
        $berkas = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($akar . '/resources/views')
        );

        foreach ($berkas as $b) {
            if (! $b->isFile() || ! str_ends_with($b->getFilename(), '.blade.php')) {
                continue;
            }

            preg_match_all('/\bfa-[a-z0-9-]{2,}/', (string) file_get_contents($b->getPathname()), $c);

            foreach ($c[0] as $nama) {
                $ketemu[$nama][] = str_replace($akar . '/resources/views/', '', $b->getPathname());
            }
        }

        $hilang = [];

        foreach ($ketemu as $nama => $tempat) {
            if (in_array($nama, self::BUKAN_IKON, true) || in_array($nama, self::UTANG_LAMA, true)) {
                continue;
            }

            // Font Awesome menulis tiap ikon sebagai ".fa-nama:before" atau
            // sebagai salah satu dari daftar selektor yang dipisah koma.
            if (! str_contains($gaya, '.' . $nama . ':') && ! str_contains($gaya, '.' . $nama . ',')) {
                $hilang[] = $nama . ' (' . implode(', ', array_unique($tempat)) . ')';
            }
        }

        $this->assertSame([], $hilang, "Nama ikon berikut tidak ada di Font Awesome 5.5.0, jadi tidak akan tergambar:\n- " . implode("\n- ", $hilang) . "\n");
    }
}
