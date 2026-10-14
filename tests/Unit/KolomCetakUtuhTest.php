<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tabel di berkas cetak harus utuh: sebanyak kepalanya, selebar kertasnya.
 *
 * Dompdf tidak pernah mengeluh soal tabel. Dua cacat ini lolos diam-diam
 * dan keduanya sudah pernah terjadi di sini:
 *
 * 1. Kepala lebih banyak daripada selnya. Isinya bergeser SATU KOLOM KE
 *    KIRI sepanjang tabel — nilai masih tercetak rapi, hanya saja di bawah
 *    judul yang salah, dan kolom terakhir kosong. Tidak ada yang curiga
 *    karena berkasnya tetap terlihat benar.
 *
 * 2. Lebar persen yang tidak berjumlah 100. Ekspor Angkatan Layanan sempat
 *    berjumlah 84%; Dompdf membagikan sisa 16% itu menurut isinya sendiri,
 *    sehingga angka persen yang susah payah disetel tidak berarti apa-apa.
 *
 * Keduanya hanya kelihatan kalau berkasnya dibuka dan dibandingkan dengan
 * kepalanya satu per satu — pekerjaan yang tidak akan dilakukan orang tiap
 * kali menambah kolom. Karena itu dijaga di sini.
 */
class KolomCetakUtuhTest extends TestCase
{
    /**
     * Berkas cetak LAMA yang kepalanya sudah telanjur timpang.
     *
     * Bukan daftar pengecualian yang boleh ditambah: ini utang yang
     * terukur, dan tiap barisnya menyebut berapa kolom yang hilang.
     * Memperbaikinya menggeser isi tabel yang selama ini salah tempat,
     * jadi menunggu keputusan — bukan diputuskan sambil lalu di sini.
     */
    private const TERTUNDA = [
        'camp/pdf.blade.php' => 'kepala "No." tanpa sel; isinya bergeser, "Tanggal camp" kosong',
        'gaji/pdf.blade.php' => '7 kepala, 6 sel',
        'laporan_credit/pdf.blade.php' => '5 kepala, 4 sel',
        'laporan_debit/pdf.blade.php' => '5 kepala, 4 sel',
        'laporan_semua/pdf.blade.php' => '9 kepala, 8 sel',
        'neraca/pdf.blade.php' => '6 kepala, 5 sel',
        'presensi/pdf.blade.php' => '7 kepala, 8 sel',
    ];

    /** @return array<string, string> */
    private function berkasCetak(): array
    {
        $akar = dirname(__DIR__, 2) . '/resources/views/account/';
        $hasil = [];

        $jelajah = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar));

        foreach ($jelajah as $berkas) {
            if (! preg_match('/pdf.*\.blade\.php$/', $berkas->getFilename())) {
                continue;
            }

            $hasil[str_replace($akar, '', $berkas->getPathname())]
                = (string) file_get_contents($berkas->getPathname());
        }

        ksort($hasil);

        $this->assertNotEmpty($hasil, 'Tidak satu pun berkas cetak ditemukan; '
            . 'penjaga ini jadi hijau tanpa memeriksa apa pun.');

        return $hasil;
    }

    /**
     * Lebar satu baris: jumlah colspan-nya, bukan jumlah selnya.
     */
    private function rentang(string $baris, string $tag): int
    {
        preg_match_all('/<' . $tag . '\b([^>]*)>/', $baris, $sel);

        $lebar = 0;

        foreach ($sel[1] as $atribut) {
            $lebar += preg_match('/colspan=["\']?(\d+)/', $atribut, $n) ? (int) $n[1] : 1;
        }

        return $lebar;
    }

    /** @return list<string> */
    private function baris(string $potong): array
    {
        preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/s', $potong, $baris);

        return $baris[1];
    }

    public function test_sel_isi_sebanyak_kepalanya(): void
    {
        $tertunda = [];

        foreach ($this->berkasCetak() as $relatif => $isi) {
            if (! preg_match('/<thead>(.*?)<\/thead>/s', $isi, $kepala)
                || ! preg_match('/<tbody>(.*?)<\/tbody>/s', $isi, $tubuh)) {
                continue;
            }

            $barisKepala = $this->baris($kepala[1]);

            if ($barisKepala === []) {
                continue;
            }

            // Baris kepala PERTAMA selalu menutupi seluruh lebar tabel, baik
            // lewat colspan (kepala bertingkat) maupun rowspan (kepala yang
            // menjulur ke baris bawahnya). Baris keduanya hanya mengisi
            // sebagian, jadi menghitungnya akan salah.
            $lebar = $this->rentang($barisKepala[0], 'th');

            foreach ($this->baris($tubuh[1]) as $urutan => $baris) {
                $sel = $this->rentang($baris, 'td');

                if ($sel === 0 || $sel === $lebar) {
                    continue;
                }

                if (isset(self::TERTUNDA[$relatif])) {
                    $tertunda[$relatif] = true;

                    continue;
                }

                $this->fail(sprintf(
                    '%s: kepalanya %d kolom, tetapi baris isi ke-%d punya %d sel. '
                    . 'Dompdf tidak akan menyalahkan apa pun — isinya cuma akan '
                    . 'tercetak di bawah judul yang salah.',
                    $relatif, $lebar, $urutan + 1, $sel
                ));
            }
        }

        // Utang yang sudah dibayar harus dicoret dari daftarnya, supaya
        // daftar ini tidak pelan-pelan jadi tempat menyembunyikan cacat baru.
        $lunas = array_diff(array_keys(self::TERTUNDA), array_keys($tertunda));

        $this->assertSame([], array_values($lunas), 'Berkas ini sudah tidak '
            . 'timpang lagi tetapi masih terdaftar di TERTUNDA; hapus barisnya.');
    }

    public function test_lebar_persen_kepala_berjumlah_seratus(): void
    {
        $diperiksa = 0;

        foreach ($this->berkasCetak() as $relatif => $isi) {
            if (! preg_match('/<thead>(.*?)<\/thead>/s', $isi, $kepala)) {
                continue;
            }

            $barisKepala = $this->baris($kepala[1]);

            if ($barisKepala === []) {
                continue;
            }

            preg_match_all('/<th\b([^>]*)>/', $barisKepala[0], $sel);

            $persen = [];

            foreach ($sel[1] as $atribut) {
                if (preg_match('/width="(\d+(?:[.,]\d+)?)%"/', $atribut, $n)) {
                    $persen[] = (float) str_replace(',', '.', $n[1]);
                }
            }

            // Berkas yang memang tidak memakai persen (lebarnya diserahkan
            // ke Dompdf) bukan urusan penjaga ini.
            if ($persen === []) {
                continue;
            }

            $diperiksa++;

            $this->assertCount(count($sel[1]), $persen, $relatif . ': ada kepala '
                . 'kolom tanpa width persen sementara yang lain memakainya. '
                . 'Dompdf akan membagi sisanya menurut isi kolom itu, bukan '
                . 'menurut lebar yang disetel di sini.');

            $this->assertEqualsWithDelta(100.0, array_sum($persen), 0.01, sprintf(
                '%s: lebar kolomnya berjumlah %s%%, bukan 100%%. Sisanya '
                . 'dibagikan Dompdf sekehendaknya dan angka di berkas ini '
                . 'berhenti berarti.',
                $relatif, rtrim(rtrim(number_format(array_sum($persen), 2, ',', ''), '0'), ',')
            ));
        }

        $this->assertGreaterThanOrEqual(6, $diperiksa, 'Hanya ' . $diperiksa
            . ' berkas cetak yang lebarnya dipatok persen; sebelumnya ada 6.');
    }
}
