<?php

namespace Tests\Feature\ScopusKafe;

use App\Support\HargaKafe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Halaman Scopus Kafe mengikuti bentuk halaman Class Offline.
 *
 * ---------------------------------------------------------------------------
 * Apa yang diminta, dan apa yang diukur
 * ---------------------------------------------------------------------------
 *
 * Diminta pemiliknya 9 Nov 2026: "perbaiki /Scopus-Kafe seperti halaman class
 * offline". Bahasa rupanya sudah sama sejak 26 Okt — yang berbeda
 * KEPADATANNYA. Terukur di layar 1440px:
 *
 *     Scopus Camp  1.305px
 *     Scopus Kafe  2.341px
 *
 * Selisihnya bukan isi, melainkan empat blok yang Camp tidak punya: pita
 * "kapan" tersendiri (90px), judul bagian "Pilih bentuk dan sesinya" beserta
 * kalimatnya (110px), kepala varian bertingkat dua (240px per varian), dan
 * empat ubin berikon untuk "Yang kamu dapat" (422px, plus 80px judulnya).
 * Sesudah dirombak: 1.717px — sisanya memang isi, sebab Kafe punya dua varian
 * dan tiga sesi sementara Camp saat itu satu angkatan.
 *
 * ---------------------------------------------------------------------------
 * Yang dijaga berkas ini
 * ---------------------------------------------------------------------------
 *
 * BUKAN tingginya — angka itu berubah mengikuti jumlah sesi, dan uji yang
 * memakunya akan merah karena panitia menambah sesi. Yang dijaga: keterangan
 * yang kotaknya dibuang TIDAK ikut hilang, dan bentuk penutupnya tetap sama
 * dengan Camp.
 */
class HalamanMengikutiCampTest extends TestCase
{
    use DatabaseTransactions;

    private function isi(): string
    {
        return (string) $this->get('/Scopus-Kafe')->assertOk()->getContent();
    }

    #[Test]
    public function hari_buka_dan_tanggal_terdekat_tetap_terbaca(): void
    {
        /*
         * INTI uji ini. Pita "kapan" dibuang, dan yang dibuang kotaknya —
         * bukan keterangannya. Ini layanan TANPA ANGKATAN, jadi "kapan
         * bisanya?" pertanyaan pertama yang muncul; sebelum pita itu ada,
         * halaman ini tidak menjawabnya sama sekali sampai orangnya membuka
         * borang.
         *
         * Dibuang tanpa penjagaan ini, halamannya kembali ke keadaan itu dan
         * tidak ada yang merah.
         */
        $isi = $this->isi();

        $this->assertStringContainsString(HargaKafe::namaHariBuka(), $isi,
            'Hari buka tidak lagi disebut di halaman depan.');

        // Tanggal terdekatnya ikut, dalam bahasa Indonesia.
        $this->assertMatchesRegularExpression(
            '/terdekat.{0,40}\d{1,2}\s+\p{Lu}\p{L}+\s+\d{4}/su',
            $isi,
            'Tanggal sesi terdekat tidak lagi disebut di halaman depan.'
        );
    }

    #[Test]
    public function yang_didapat_berbentuk_daftar_centang_seperti_camp(): void
    {
        /*
         * Empat ubin berikon diganti satu kartu berdaftar centang — bentuk
         * panel fasilitas Scopus Camp. Keempat butirnya harus tetap ada:
         * yang diubah bungkusnya, bukan janjinya kepada pemesan.
         */
        $isi = $this->isi();

        $this->assertStringContainsString('kfd-panel-kartu', $isi);
        $this->assertStringContainsString('kfd-fasilitas', $isi);
        $this->assertSame(4, substr_count($isi, 'bi-check-circle-fill'),
            'Jumlah butir "Yang kamu dapat" berubah.');

        foreach (['Lima jam penuh', 'Naskahmu sendiri', 'terindeks Scopus', 'Yogyakarta'] as $kata) {
            $this->assertStringContainsString($kata, $isi, $kata);
        }
    }

    #[Test]
    public function dua_blok_penutup_tetap_bersebelahan(): void
    {
        // Susunan yang sama dengan Camp: fasilitas di kiri, kartu lacak di
        // kanan, dalam satu kisi.
        $isi = $this->isi();

        $this->assertStringContainsString('kfd-bawah', $isi);
        $this->assertStringContainsString('kfd-lacak', $isi);

        $this->assertLessThan(
            mb_strpos($isi, 'kfd-lacak'),
            mb_strpos($isi, 'kfd-panel-kartu'),
            'Kartu lacak mendahului panel fasilitas; susunannya tidak lagi seperti Camp.'
        );
    }

    #[Test]
    public function gaya_yang_markahnya_dibuang_ikut_dibuang(): void
    {
        /*
         * Gaya yatim adalah gaya yang kelak disalin orang ke tempat yang
         * salah — dan nama seperti `.kfd-renggang` terdengar berlaku umum
         * padahal ia dibuat untuk satu judul tertentu yang sudah tidak ada.
         *
         * Diperiksa pada BERKAS GAYANYA, bukan pada respons: `<style>`
         * inline ikut terkirim ke peramban, jadi memeriksa respons akan
         * hijau-palsu oleh komentar yang menyebut nama-nama itu.
         */
        $gaya = (string) file_get_contents(
            base_path('resources/views/public/scopus_kafe/partials/gaya-daftar.blade.php')
        );

        // Komentarnya dibuang dulu; yang diperiksa aturan CSS-nya.
        $tanpaKomentar = (string) preg_replace('#/\*.*?\*/#s', '', $gaya);

        foreach (['.kfd-ubin', '.kfd-kapan', '.kfd-renggang', '.kfd-bagian-kata'] as $kelas) {
            $this->assertStringNotContainsString($kelas, $tanpaKomentar,
                "Gaya {$kelas} hidup lagi tanpa markah yang memakainya.");
        }
    }

    #[Test]
    public function markah_lamanya_tidak_kembali(): void
    {
        /*
         * KOMENTAR CSS DIBUANG DULU.
         *
         * Gayanya ditanam sebagai <style> inline, jadi komentar `/* … *\/`
         * di dalamnya IKUT TERKIRIM ke peramban dan terbaca sebagai isi
         * halaman. Uji ini merah pada percobaan pertamanya justru karena
         * komentar yang MENJELASKAN bahwa .kfd-ubin sudah dibuang — jebakan
         * yang sudah tercatat dan tetap saya masuki.
         */
        $isi = (string) preg_replace('#/\*.*?\*/#s', '', $this->isi());

        foreach ([
            'kfd-ubin' => 'ubin berikon menggantikan daftar centang lagi',
            'kfd-kapan' => 'pita "kapan" berdiri sendiri lagi',
            'Pilih bentuk dan sesinya' => 'judul bagian yang mengulang isinya kembali',
        ] as $jejak => $sebab) {
            $this->assertStringNotContainsString($jejak, $isi, $sebab);
        }
    }
}
