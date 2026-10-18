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
class GerakMinimalPublikTest extends TestCase
{
    /**
     * Halaman publik Webinar Eksklusif yang punya animasi sendiri.
     *
     * Daftarnya tumbuh: daftar sesi menyusul pada 18 Okt 2026 ketika tombol
     * daftarnya diberi denyut, panah, dan kilau. Penjaga yang hanya menyebut
     * satu berkas diam-diam berhenti menjaga begitu animasi berikutnya
     * ditulis di berkas lain.
     */
    private const TAMPILAN = [
        'resources/views/public/webinar_eksklusif/status.blade.php',
        'resources/views/public/webinar_eksklusif/index.blade.php',

        // Beranda menyusul 18 Okt 2026, saat ditulis ulang dan diberi
        // bulatan beranimasi di belakang foto hero. Ia halaman PERTAMA yang
        // dibuka orang — sebelum ia tahu situs ini apa, dan sebelum ia punya
        // alasan untuk bertahan kalau layarnya membuatnya pusing.
        'resources/views/public/home.blade.php',
    ];

    private function sumber(string $berkas): string
    {
        $isi = (string) file_get_contents(dirname(__DIR__, 2) . '/' . $berkas);

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
        $terbaca = 0;

        foreach (self::TAMPILAN as $berkas) {
            $terbaca += $this->periksaSatuBerkas($berkas);
        }

        /*
         * Pemeriksa-diri: uji yang tidak membaca apa pun selalu hijau, dan
         * pembaca animasi di bawah bekerja dengan memotong teks di sekitar
         * tanda kurung — cara yang bisa diam-diam berhenti bekerja ketika
         * bentuk berkasnya berubah.
         *
         * Dihitung untuk SELURUH daftar, bukan per berkas. Sebelumnya tiap
         * berkas wajib memuat lebih dari dua animasi — dan itu bukan sifat
         * yang hendak dijaga, melainkan kebetulan dua berkas pertamanya.
         * Beranda masuk daftar 18 Okt 2026 dengan SATU animasi, dan
         * penjaganya merah bukan karena ada animasi yang telanjang,
         * melainkan karena animasinya terlalu sedikit.
         */
        $this->assertGreaterThan(2, $terbaca,
            'Hampir tidak ada animasi yang terbaca di seluruh daftar — pembacanya '
            . 'hampir pasti rusak, dan uji yang tidak membaca apa pun selalu hijau.');
    }

    /** @return int berapa animasi di luar blok hemat gerak yang diperiksa */
    private function periksaSatuBerkas(string $berkas): int
    {
        $isi = $this->sumber($berkas);
        [$hemat, $blok] = $this->blokHematGerak($isi);

        $this->assertNotSame('', $hemat,
            $berkas . ' tidak punya satu pun blok prefers-reduced-motion.');

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

        /* Berkas yang masuk daftar ini masuk KARENA punya animasi. Nol
           berarti animasinya dicabut tanpa mengeluarkan berkasnya dari
           daftar, atau pembacanya berhenti mengenali bentuk tulisannya. */
        $this->assertGreaterThan(0, $diperiksa,
            $berkas . ': tidak ada satu pun animasi terbaca, padahal berkas ini '
            . 'ada di daftar justru karena punya animasi.');

        $this->assertSame([], $telanjang,
            $berkas . " — animasi berikut tetap berjalan untuk yang meminta "
            . "gerak minimal:\n- " . implode("\n- ", $telanjang) . "\n");

        return $diperiksa;
    }

    /**
     * Tombol yang MATI tidak boleh bergerak.
     *
     * "Kuota penuh" yang berdenyut dan panahnya menyenggol maju adalah
     * ajakan menekan sesuatu yang tidak bisa ditekan — dan yang menekannya
     * berulang kali akan mengira halamannya yang rusak, bukan kuotanya yang
     * habis.
     */
    public function test_tombol_mati_tidak_ikut_bergerak(): void
    {
        $isi = $this->sumber('resources/views/public/webinar_eksklusif/index.blade.php');

        preg_match_all('/\.sel-tombol[^{]*\{[^}]*animation:[^;}]+;/s', $isi, $cocok);

        $ikut = array_values(array_filter(
            $cocok[0],
            fn ($aturan) => ! str_contains($aturan, ':not(.sel-tombol-mati)')
        ));

        $this->assertSame([], $ikut,
            "Aturan gerak berikut tidak mengecualikan tombol mati:\n- "
            . implode("\n- ", array_map(fn ($a) => trim(explode('{', $a)[0]), $ikut)) . "\n");
    }
}
