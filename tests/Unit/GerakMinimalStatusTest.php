<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * TIAP animasi di halaman status punya jalan keluar untuk gerak minimal.
 *
 * Bukan uji selera. Orang yang menyalakan "kurangi gerak" di sistemnya
 * sebagian menyalakannya karena gerak di layar membuatnya pusing atau mual —
 * dan halaman ini dibuka justru saat ia sedang menunggu uangnya dikonfirmasi,
 * yaitu saat ia paling tidak bisa pergi begitu saja.
 *
 * Aturannya pernah bocor sekali, dan bocornya di tempat terburuk: terukur
 * delapan dari sembilan animasi sudah dimatikan, dan yang tertinggal adalah
 * konfeti — delapan belas kepingan melesat serentak dari satu titik, persis
 * gerak yang dihindari orangnya. Lolos karena yang memeriksanya cuma mata,
 * dan mata memeriksa yang diingatnya saja.
 *
 * Dijaga dari SUMBERNYA: CSS tidak bisa dijalankan dari PHPUnit. Jadi ini
 * pin kontrak, bukan jebakan yang membuktikan akibatnya — yang dibuktikan
 * cuma bahwa tiap selektor beranimasi disebut lagi di dalam blok gerak
 * minimal.
 */
class GerakMinimalStatusTest extends TestCase
{
    private const TAMPILAN = 'resources/views/public/webinar_eksklusif/status.blade.php';

    private function sumber(): string
    {
        $isi = (string) file_get_contents(dirname(__DIR__, 2) . '/' . self::TAMPILAN);

        // Komentar dibuang lebih dulu: nama animasi disebut juga di dalam
        // komentar yang menerangkan kenapa ia dipakai, dan penjaga yang
        // membaca kata apa adanya akan menghitungnya sebagai deklarasi.
        $isi = (string) preg_replace('#/\*.*?\*/#s', '', $isi);

        return (string) preg_replace('#\{\{--.*?--\}\}#s', '', $isi);
    }

    /**
     * Isi seluruh blok @media (prefers-reduced-motion: reduce), disatukan.
     *
     * Kurungnya dihitung manual, bukan dicocokkan regex: blok ini memuat
     * aturan bersarang, dan regex yang berhenti di '}' pertama memotongnya
     * di tengah — lalu menyatakan selektor yang sebenarnya ada di dalamnya
     * sebagai tidak terlindungi.
     *
     * @return array{0: string, 1: list<array{int, int}>}
     */
    private function blokHematGerak(string $isi): array
    {
        $blok = [];
        $kumpul = '';
        $awalan = '@media (prefers-reduced-motion: reduce) {';
        $dari = 0;

        while (($mulai = strpos($isi, $awalan, $dari)) !== false) {
            $i = $mulai + strlen($awalan);
            $dalam = 1;

            while ($i < strlen($isi) && $dalam > 0) {
                if ($isi[$i] === '{') {
                    $dalam++;
                } elseif ($isi[$i] === '}') {
                    $dalam--;
                }
                $i++;
            }

            $blok[] = [$mulai, $i];
            $kumpul .= substr($isi, $mulai, $i - $mulai) . "\n";
            $dari = $i;
        }

        return [$kumpul, $blok];
    }

    public function test_tiap_animasi_dimatikan_saat_gerak_minimal(): void
    {
        $isi = $this->sumber();
        [$hemat, $blok] = $this->blokHematGerak($isi);

        $this->assertNotSame('', $hemat,
            'Halaman ini tidak punya satu pun blok prefers-reduced-motion.');

        preg_match_all('/animation:\s*([a-z][a-z0-9-]*)\s/i', $isi, $cocok, PREG_OFFSET_CAPTURE);

        $telanjang = [];
        $diperiksa = 0;

        foreach ($cocok[1] as $k => [$nama, $_]) {
            $posisi = $cocok[0][$k][1];

            if ($nama === 'none') {
                continue;
            }

            // Yang sudah berada DI DALAM blok hemat bukan yang diperiksa.
            foreach ($blok as [$a, $b]) {
                if ($posisi >= $a && $posisi < $b) {
                    continue 2;
                }
            }

            $diperiksa++;

            // Selektornya: teks antara penutup aturan sebelumnya dan '{'
            // yang membuka aturan ini.
            $sebelum = substr($isi, 0, $posisi);
            $buka = strrpos($sebelum, '{');
            $potong = $buka === false ? $sebelum : substr($sebelum, 0, $buka);
            $batas = max((int) strrpos($potong, '}'), (int) strrpos($potong, '{'));
            $selektor = trim(substr($potong, $batas + 1));
            $selektor = trim((string) preg_replace('/\s+/', ' ', $selektor));

            if ($selektor === '' || ! str_contains($hemat, $selektor)) {
                $telanjang[] = $selektor . '  (animasi: ' . $nama . ')';
            }
        }

        $this->assertGreaterThan(5, $diperiksa,
            'Hampir tidak ada animasi yang terbaca — pembacanya hampir pasti '
            . 'rusak, dan uji yang tidak membaca apa pun selalu hijau.');

        $this->assertSame([], $telanjang,
            "Animasi berikut tetap berjalan untuk yang meminta gerak minimal:\n- "
            . implode("\n- ", $telanjang) . "\n");
    }
}
