<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Uji yang memakai DatabaseTransactions tidak boleh merakit ulang aplikasinya.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini penting di proyek INI
 * ---------------------------------------------------------------------------
 *
 * Rangkaian uji di sini berjalan di basis data yang SAMA dengan aplikasi yang
 * sedang dipakai — lihat penjelasannya di Tests\TestCase. Satu-satunya yang
 * menahan uji agar tidak meninggalkan jejak adalah transaksi yang dibuka
 * DatabaseTransactions dan dibatalkan di akhir.
 *
 * `$this->refreshApplication()` merakit ULANG wadah aplikasinya di tengah uji,
 * dan bersamanya sambungan basis data yang sedang memegang transaksi itu.
 * Transaksinya hilang tanpa dibatalkan; semua yang dibuat sesudahnya
 * TERSIMPAN PERMANEN.
 *
 * ---------------------------------------------------------------------------
 * Akibatnya, terukur
 * ---------------------------------------------------------------------------
 *
 * Satu baris refreshApplication() di TabRincianDileburTest membocorkan SATU
 * angkatan dan SATU pendaftaran tiap kali rangkaian ujinya dijalankan. Per
 * 8 Nov 2026 tertumpuk 418 angkatan "Angkatan Penuh …" dan 188 pendaftaran
 * yatim, sejak 23 Okt.
 *
 * Yang membuatnya berbahaya bukan jumlahnya melainkan SIFATNYA: angkatan itu
 * `active` dan tanggal mulainya di masa depan, jadi ia ikut terpajang di
 * halaman daftar Scopus Camp dan di beranda — ratusan baris memenuhi halaman
 * pertama. Uji lain yang membuat angkatannya sendiri lalu mencarinya di
 * halaman itu berubah merah, dan merahnya menunjuk halaman daftar yang tidak
 * bersalah. Merah yang berpindah-pindah tergantung urutan jalan.
 *
 * ---------------------------------------------------------------------------
 * Kenapa dijaga dari SUMBER, bukan dari jejaknya
 * ---------------------------------------------------------------------------
 *
 * Menghitung baris sampah di basis data akan memerahkan uji ini selama
 * sampah lamanya belum dibersihkan — merah yang tidak bisa diperbaiki dengan
 * mengubah kode. Yang dipin sumbernya: pemanggilan itu sendiri.
 */
class UjiTidakMerakitUlangAplikasiTest extends TestCase
{
    #[Test]
    public function tidak_ada_uji_yang_memanggil_refresh_application(): void
    {
        $pelanggar = [];

        $berkas = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path('tests'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($berkas as $b) {
            if ($b->getExtension() !== 'php') {
                continue;
            }

            // Berkas ini sendiri menyebut namanya; kalau tidak, ia memerahkan
            // dirinya sendiri dan penjagaannya jadi tidak bisa dipasang.
            if ($b->getPathname() === __FILE__) {
                continue;
            }

            $isi = (string) file_get_contents($b->getPathname());

            // Hanya yang BENAR-BENAR memanggilnya. Penyebutan di dalam
            // komentar — seperti penjelasan di TabRincianDileburTest — tidak
            // dihitung, sebab ia justru yang menjaga baris itu tidak kembali.
            if (preg_match('/^\s*\$this->refreshApplication\(\)/m', $isi) !== 1) {
                continue;
            }

            $pelanggar[] = str_replace(base_path() . '/', '', $b->getPathname());
        }

        $this->assertSame([], $pelanggar,
            "refreshApplication() membuang transaksi DatabaseTransactions, jadi semua "
            . "yang dibuat sesudahnya tersimpan PERMANEN di basis data pengembangan. "
            . "Berkas berikut memanggilnya:\n  - " . implode("\n  - ", $pelanggar));
    }
}
