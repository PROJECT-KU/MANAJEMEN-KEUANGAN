<?php

namespace Tests\Feature;

use App\CategoriesScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Halaman umum Scopus Camp — katalog kelas tatap muka.
 *
 * Dirombak 22 Okt 2026 mengikuti beranda. Yang dijaga di sini bukan
 * seleranya, melainkan empat hal yang kalau rusak tidak mengeluarkan galat.
 */
class ScopusCampPublikTest extends TestCase
{
    use DatabaseTransactions;

    private const TAMPILAN = 'resources/views/public/scopus_camp/index.blade.php';

    private const BERANDA = 'resources/views/public/home.blade.php';

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'nama' => 'UJI Camp ' . Str::random(6),
            'nama_ke' => random_int(9000, 9999),
            'mulai' => now()->addDays(3)->toDateString(),
            'selesai' => now()->addDays(5)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20,
            'sisa_kuota' => 20,
            'biaya' => 5500000,
            'status' => 'active',
        ], $ganti));
    }

    #[Test]
    public function halamannya_terbuka(): void
    {
        $this->get('/Scopus-Camp')->assertOk();
    }

    #[Test]
    public function angkatan_yang_sudah_jalan_tidak_lagi_dipajang(): void
    {
        /*
         * KategoriLayanan::tanggalMulaiSudahTiba() menyebut angkatan begitu
         * sudah tertutup sejak 5 Okt 2026, atas permintaan pemiliknya.
         *
         * Terukur di halaman ini sebelum dirombak: TIGA angkatan yang sudah
         * berjalan masih terpampang lengkap dengan tombol "Daftar Sekarang".
         * Orang membayar acara yang sudah separuh jalan, dan tidak ada galat
         * apa pun yang memberi tahu.
         */
        $sudahJalan = $this->angkatan([
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDay()->toDateString(),
        ]);

        $belumJalan = $this->angkatan();

        $isi = (string) $this->get('/Scopus-Camp')->assertOk()->getContent();

        $this->assertStringNotContainsString($sudahJalan->nama, $isi,
            'Angkatan yang tanggal mulainya sudah lewat masih dipajang.');

        $this->assertStringContainsString($belumJalan->nama, $isi,
            'Angkatan yang belum mulai justru tidak muncul.');
    }

    #[Test]
    public function daftarnya_dipaginasi(): void
    {
        /*
         * Seluruh angkatan dulu dicetak di satu halaman: terukur 294 kartu
         * setinggi 22.711px dengan markah 627 KB. Tanpa batas, halaman ini
         * tumbuh selamanya mengikuti jumlah angkatan yang pernah dibuka.
         */
        $isi = (string) $this->get('/Scopus-Camp')->assertOk()->getContent();

        $kartu = substr_count($isi, 'class="kmp-baris"') + substr_count($isi, 'kmp-baris kmp-penuh');

        $this->assertLessThanOrEqual(12, $kartu,
            'Halaman pertama memajang ' . $kartu . ' angkatan; paginasinya tidak berlaku.');
    }

    #[Test]
    public function tidak_pernah_memajang_rupiah_nol(): void
    {
        /*
         * Ada angkatan yang biayanya memang 0 di basis data. "Rp 0" di
         * samping angkatan lain yang 5,5 juta terbaca sebagai sistem yang
         * rusak, bukan sebagai kelas gratis.
         */
        $this->angkatan(['biaya' => 0, 'total_biaya' => 0, 'mulai' => now()->addDay()->toDateString()]);

        $isi = (string) $this->get('/Scopus-Camp')->assertOk()->getContent();
        $teks = (string) preg_replace('/\s+/', ' ', strip_tags($isi));

        $this->assertDoesNotMatchRegularExpression('/Rp\s*0(?![.,\d])/', $teks,
            'Halaman Scopus Camp memajang "Rp 0".');
    }

    #[Test]
    public function kuota_tak_terbatas_tidak_dianggap_penuh(): void
    {
        /*
         * Syarat lamanya `sisa_kuota === null || sisa_kuota <= 0`, jadi
         * angkatan TANPA batas kuota dipajang "Pendaftaran Ditutup". Belum
         * pernah terasa karena kebetulan semua angkatan yang ada mengisi
         * kuotanya — dan begitu ada satu yang tidak, ia tertutup tanpa sebab
         * yang kelihatan.
         */
        $bebas = $this->angkatan(['total_kuota' => null, 'sisa_kuota' => null]);

        $isi = (string) $this->get('/Scopus-Camp')->assertOk()->getContent();

        $cocok = preg_match(
            '#<div class="kmp-baris kmp-penuh">.{0,900}?' . preg_quote($bebas->nama, '#') . '#s',
            $isi
        );

        $this->assertSame(0, $cocok,
            'Angkatan berkuota tak terbatas dipajang sebagai kursinya habis.');
    }

    #[Test]
    public function memakai_bahasa_rupa_beranda(): void
    {
        $isi = (string) $this->get('/Scopus-Camp')->assertOk()->getContent();

        $this->assertStringContainsString('class="kmp"', $isi,
            'Pembungkus rupa halaman ini hilang.');

        $this->assertStringContainsString('Fraunces', $isi,
            'Huruf judulnya tidak dimuat; halaman ini akan memakai huruf bawaan '
            . 'templat sementara beranda memakai Fraunces.');

        $this->assertStringContainsString('--kertas: #fdf5e8', $isi,
            'Palet krem beranda hilang dari halaman ini.');

        $this->assertStringNotContainsString('#ff3131', $isi,
            'Gradien merah palet lama dipasang lagi di halaman ini.');
    }

    #[Test]
    public function panel_sampingnya_tidak_dibuat_menempel(): void
    {
        /*
         * position: sticky + top: 104px sempat dipasang di panel kiri kedua
         * halaman ini DAN beranda. Terukur di peramban: di gulir 0 panelnya
         * turun TEPAT 104px dari baris pertama, lalu saat digulung ia tidak
         * menempel sama sekali — tepi atasnya terus naik sampai -933px.
         *
         * Jadi aturan itu nol guna dan berbiaya satu celah yang terlihat.
         * Sticky di blok biasa pada halaman yang SAMA bergeser 0px, jadi
         * pemicunya konteks kisi.
         *
         * Dipin di sini karena akibatnya tidak bisa dilihat dari PHPUnit:
         * yang rusak cuma jaraknya, dan halamannya tetap 200.
         */
        $kena = [];

        foreach ([self::TAMPILAN => '.kmp-panel', self::BERANDA => '.bnd-jadwal-kepala'] as $berkas => $kelas) {
            $sumber = (string) preg_replace(
                ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
                (string) file_get_contents(base_path($berkas))
            );

            if (preg_match('/' . preg_quote($kelas, '/') . '[^{]*\{[^}]*position:\s*sticky/s', $sumber)) {
                $kena[] = $berkas . ' (' . $kelas . ')';
            }
        }

        $this->assertSame([], $kena,
            "Aturan sticky dipasang lagi di panel ini; akibatnya cuma celah "
            . "setinggi nilai top-nya:\n- " . implode("\n- ", $kena));
    }
}
