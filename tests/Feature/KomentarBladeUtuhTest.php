<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tidak ada komentar Blade yang menyebut penanda blok PHP di dalamnya.
 *
 * Penanda itu diolah Blade LEBIH DULU daripada komentarnya. Penutup
 * komentarnya kehilangan pasangannya, lalu dipasangkan dengan penutup blok
 * lain yang ada di bawahnya — dan semua yang ada di antaranya ikut tertelan.
 *
 * Yang membuatnya mahal adalah GALATNYA: ia menyebut peubah yang sama sekali
 * tidak bersalah. Terjadi 23 Okt 2026 di borang pendaftaran Scopus Camp —
 * halamannya 500 dengan "Undefined variable $fMulai", padahal $fMulai
 * didefinisikan sembilan baris di atas tempatnya dipakai; yang hilang blok
 * yang mendefinisikannya. Sebelumnya terjadi di halaman artikel, dengan
 * gejala yang sama persis.
 *
 * ---------------------------------------------------------------------------
 * Kenapa diperiksa di SUMBERNYA, bukan di hasil kompilasinya
 * ---------------------------------------------------------------------------
 *
 * Versi pertama penjaga ini memeriksa hasil kompilasi: mencari penanda
 * komentar yang tersisa. Sudah dicoba pada berkas yang BENAR-BENAR rusak itu,
 * dan hasilnya HIJAU — sebab di berkas penuh, penutup komentarnya menemukan
 * pasangan lain di bawahnya, jadi tidak ada satu pun penanda yang tersisa.
 * Hasil kompilasinya juga lolos `php -l` dan tidak menyisakan satu pun
 * penanda yang belum diolah. Kerusakannya memang tidak meninggalkan jejak
 * struktural apa pun — hanya markah yang hilang diam-diam.
 *
 * Jadi yang dijaga aturannya, bukan akibatnya: komentar Blade tidak pernah
 * mengeja penanda blok PHP. Dua komentar lama yang mengejanya sudah ditulis
 * ulang 23 Okt 2026; keduanya kebetulan aman, tetapi keduanya menunggu jadi
 * tidak aman begitu barisnya bergeser.
 */
class KomentarBladeUtuhTest extends TestCase
{
    /**
     * Dirakit saat jalan, bukan ditulis utuh.
     *
     * Berkas INI sendiri bukan Blade, jadi mengejanya di sini tidak merusak
     * apa pun — tetapi orang yang membacanya akan menyalinnya ke komentar
     * Blade, dan di sana ia berbahaya.
     *
     * @return array<int, string>
     */
    private function penanda(): array
    {
        $at = '@';

        return [$at . 'php', $at . 'endphp'];
    }

    /** @return array<int, string> nama berkas yang komentarnya mengeja penanda */
    private function pelanggar(string $isi, string $nama): array
    {
        $kena = [];

        preg_match_all('/\{\{--.*?--\}\}/s', $isi, $komentar);

        foreach ($komentar[0] as $satu) {
            foreach ($this->penanda() as $p) {
                if (str_contains($satu, $p)) {
                    $kena[] = $nama;

                    return $kena;
                }
            }
        }

        return $kena;
    }

    #[Test]
    public function tidak_ada_komentar_blade_yang_mengeja_penanda_blok_php(): void
    {
        $rusak = [];

        foreach (File::allFiles(resource_path('views')) as $berkas) {
            if (! str_ends_with($berkas->getFilename(), '.blade.php')) {
                continue;
            }

            $rusak = array_merge($rusak, $this->pelanggar(
                $berkas->getContents(),
                $berkas->getRelativePathname()
            ));
        }

        $this->assertSame([], $rusak,
            "Komentar Blade di berkas berikut mengeja penanda blok PHP. Penanda "
            . "itu diolah lebih dulu daripada komentarnya, dan markah di "
            . "belakangnya akan tertelan tanpa galat apa pun — halamannya 500 "
            . "dengan galat yang menunjuk peubah yang tidak bersalah. Sebut "
            . "'blok PHP' saja, jangan dieja:\n  " . implode("\n  ", $rusak));
    }

    #[Test]
    public function penjaganya_memang_menangkap_komentar_yang_mengeja(): void
    {
        /*
         * Penjaga di atas hijau untuk SELURUH berkas yang ada sekarang, jadi
         * tanpa uji ini tidak ada yang membuktikan ia masih bisa merah.
         */
        $naskah = '{{-- komentar yang menyebut ' . $this->penanda()[0] . ' di dalamnya --}}';

        $this->assertSame(['contoh.blade.php'],
            $this->pelanggar($naskah, 'contoh.blade.php'),
            'Pemeriksaannya tidak lagi menangkap komentar yang mengeja penandanya; '
            . 'penjaga di atas jadi hijau selamanya.');
    }
}
