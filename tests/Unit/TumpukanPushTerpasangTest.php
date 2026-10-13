<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tiap @push ke sebuah tumpukan harus ada @stack yang merendernya.
 *
 * Blade MENELAN @push ke nama tumpukan yang tidak pernah dirender. Tidak ada
 * galat, tidak ada peringatan, halamannya tetap 200 — isi bloknya sekadar
 * tidak pernah terbit.
 *
 * Terukur 13 Okt 2026: layar Catatan Penghapusan memakai @push('skrip')
 * sementara layouts/account.blade.php merender @stack('scripts'). Akibatnya
 * SELURUH blok skripnya (107 baris) hilang dari halaman, dan tiga dialog
 * penegasan ikut hilang bersamanya — termasuk penegasan "buang semua yang
 * cocok" yang tidak bisa diurungkan. Tombolnya tetap ada dan tetap bisa
 * ditekan; yang hilang cuma pertanyaannya. Di halaman: markah tombolnya
 * terbit 1x, kata misKonfirmasi 0x.
 *
 * Yang dijaga AKIBATNYA, bukan ejaannya: penjaga yang cuma melarang kata
 * "skrip" akan hijau untuk @push('script'), @push('js'), dan tiap salah
 * ketik berikutnya.
 */
class TumpukanPushTerpasangTest extends TestCase
{
    /**
     * Nama tumpukan yang memang tidak dirender layout mana pun, dan itu benar.
     *
     * Kosong hari ini. Kalau nanti ada komponen yang mengumpulkan tumpukannya
     * sendiri lewat @stack di dalam berkasnya, ia akan tertangkap pemindai
     * @stack di bawah — jadi daftar ini hanya untuk hal yang benar-benar
     * sengaja dibiarkan menggantung.
     *
     * @var array<int, string>
     */
    private const DIBIARKAN = [];

    public function test_tiap_push_punya_stack_yang_merendernya(): void
    {
        $terpasang = $this->namaStack();

        $this->assertNotEmpty(
            $terpasang,
            'Tidak satu pun @stack ketemu di resources/views — pemindainya yang salah, bukan kodenya.'
        );

        $menggantung = [];

        foreach ($this->berkasBlade() as $berkas) {
            $isi = (string) file_get_contents($berkas);

            // @push, @pushOnce, dan @prepend sama-sama mengisi tumpukan.
            preg_match_all(
                '/@(?:push|pushOnce|prepend|prependOnce)\s*\(\s*[\'"]([^\'"]+)[\'"]/',
                $isi, $cocok
            );

            foreach ($cocok[1] as $nama) {
                if (in_array($nama, $terpasang, true) || in_array($nama, self::DIBIARKAN, true)) {
                    continue;
                }

                $menggantung[] = $this->relatif($berkas) . " → @push('" . $nama . "')";
            }
        }

        $this->assertSame([], array_values(array_unique($menggantung)), implode("\n", [
            'Ada @push ke tumpukan yang TIDAK dirender layout mana pun.',
            'Isi bloknya tidak akan pernah terbit, dan halamannya tetap 200 tanpa galat.',
            'Nama tumpukan yang benar-benar dirender: ' . implode(', ', $terpasang) . '.',
            '',
            implode("\n", array_unique($menggantung)),
        ]));
    }

    /**
     * Nama tumpukan yang benar-benar dirender, dibaca dari @stack mana pun.
     *
     * Dibaca dari berkasnya, bukan dihardcode: menuliskan ['gaya','scripts']
     * di sini berarti penjaga ini ikut merah saat layout menambah tumpukan
     * baru yang sah.
     *
     * @return array<int, string>
     */
    private function namaStack(): array
    {
        $nama = [];

        foreach ($this->berkasBlade() as $berkas) {
            preg_match_all(
                '/@stack\s*\(\s*[\'"]([^\'"]+)[\'"]/',
                (string) file_get_contents($berkas), $cocok
            );

            foreach ($cocok[1] as $satu) {
                $nama[$satu] = true;
            }
        }

        return array_keys($nama);
    }

    /** @return array<int, string> */
    private function berkasBlade(): array
    {
        $akar = $this->akar() . '/resources/views';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($akar, \FilesystemIterator::SKIP_DOTS)
        );

        $berkas = [];

        foreach ($iterator as $satu) {
            if ($satu->isFile() && str_ends_with($satu->getFilename(), '.blade.php')) {
                $berkas[] = $satu->getPathname();
            }
        }

        sort($berkas);

        return $berkas;
    }

    private function akar(): string
    {
        return dirname(__DIR__, 2);
    }

    private function relatif(string $jalur): string
    {
        return str_replace($this->akar() . '/', '', $jalur);
    }
}
