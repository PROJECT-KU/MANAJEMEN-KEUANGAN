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
     *
     * Daftar ini hanya boleh MENGECIL. Menambah nama baru ke sini berarti
     * sengaja memasang ikon yang tidak akan tergambar.
     *
     * ---------------------------------------------------------------------
     * BERPASANGAN DENGAN BERKASNYA, bukan daftar nama saja
     * ---------------------------------------------------------------------
     *
     * Sampai 24 Okt 2026 ini cuma daftar nama, dan berkasnya ditulis sebagai
     * komentar di sebelahnya. Akibatnya nama yang sudah terlanjur ada di sini
     * bebas dipakai di berkas MANA PUN yang baru — penjaganya diam, dan
     * ikonnya tetap tidak tergambar.
     *
     * Terjadi persis begitu hari itu: fa-list-check dipakai di halaman status
     * Scopus Camp yang baru, lolos karena namanya sudah ada di sini untuk
     * layouts/version.blade.php, dan judul "Setelah ini" tergambar tanpa
     * ikon. Komentarnya menyebut berkas yang benar sejak awal; yang tidak ada
     * cuma yang memeriksanya.
     *
     * @var array<string, array<int, string>>
     */
    private const UTANG_LAMA = [
        'fa-circle-user' => ['layouts/version.blade.php'],
        'fa-clock-rotate-left' => ['layouts/version.blade.php'],
        'fa-grid-2' => ['account/clinik_scopus_riwayat_pemesanan/index.blade.php'],
        'fa-house' => ['layouts/version.blade.php'],
        'fa-image-slash' => ['account/clinik_scopus_riwayat_pemesanan/detail.blade.php'],
        'fa-list-check' => ['layouts/version.blade.php'],
        'fa-rectangle-list' => ['layouts/version.blade.php'],
        'fa-user-gear' => ['layouts/version.blade.php'],
    ];

    /**
     * Membuang komentar sebelum nama ikonnya dicari.
     *
     * Nama FA6 disebut justru di komentar yang menerangkan KENAPA ia tidak
     * boleh dipakai — dan komentar tidak pernah tergambar, jadi menuduhnya
     * salah. Terjadi 18 Okt 2026: penjaga ini merah karena satu komentar
     * yang berbunyi "namanya fa-check-circle, bukan fa-circle-check", yaitu
     * kalimat yang justru menjaga hal yang sama.
     *
     * Penjaga yang merah karena alasan yang salah mengajari orang
     * mengabaikannya — dan itu lebih berbahaya daripada tidak ada penjaga.
     */
    private function tanpaKomentar(string $isi): string
    {
        // Komentar Blade lebih dulu: isinya bisa memuat /* */ yang bukan
        // komentar CSS, dan membuangnya terbalik akan menyisakan potongan.
        $isi = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $isi);

        return (string) preg_replace('#/\*.*?\*/#s', '', $isi);
    }

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

            preg_match_all('/\bfa-[a-z0-9-]{2,}/', $this->tanpaKomentar((string) file_get_contents($b->getPathname())), $c);

            foreach ($c[0] as $nama) {
                $ketemu[$nama][] = str_replace($akar . '/resources/views/', '', $b->getPathname());
            }
        }

        // Ikut dipindai: toast merakit ikonnya dari sini, bukan dari Blade.
        preg_match_all(
            '/\bfa-[a-z0-9-]{2,}/',
            $this->tanpaKomentar((string) file_get_contents($akar . '/public/assets/js/mis-ui.js')),
            $cJs
        );

        foreach ($cJs[0] as $nama) {
            $ketemu[$nama][] = 'public/assets/js/mis-ui.js';
        }

        $hilang = [];

        foreach ($ketemu as $nama => $tempat) {
            if (in_array($nama, self::BUKAN_IKON, true)) {
                continue;
            }

            // Font Awesome menulis tiap ikon sebagai ".fa-nama:before" atau
            // sebagai salah satu dari daftar selektor yang dipisah koma.
            if (str_contains($gaya, '.' . $nama . ':') || str_contains($gaya, '.' . $nama . ',')) {
                continue;
            }

            /*
             * Yang diampuni HANYA berkas yang memang sudah memakainya. Berkas
             * di luar itu tetap dilaporkan, walau namanya ada di daftar utang.
             */
            $baru = array_values(array_diff(
                array_unique($tempat),
                self::UTANG_LAMA[$nama] ?? []
            ));

            if ($baru === []) {
                continue;
            }

            $hilang[] = $nama . ' (' . implode(', ', $baru) . ')';
        }

        $this->assertSame([], $hilang, "Nama ikon berikut tidak ada di Font Awesome 5.5.0, jadi tidak akan tergambar:\n- " . implode("\n- ", $hilang) . "\n");
    }
}
