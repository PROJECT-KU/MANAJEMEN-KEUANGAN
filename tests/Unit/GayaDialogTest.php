<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Rupa kotak dialog SweetAlert2 di halaman umum.
 *
 * Diminta pemiliknya 24 Okt 2026: dialognya masih berupa bawaan SweetAlert2 —
 * huruf sistem, sudut kecil, tombol batal abu-abu, ikon biru keabuan — di
 * tengah halaman yang sudah memakai kertas krem, Fraunces, dan jingga.
 *
 * Yang dijaga di sini bukan seleranya melainkan dua hal yang kalau hilang
 * TIDAK membuat satu uji pun merah: gayanya tidak lagi disertakan sama
 * sekali, atau ia disertakan tetapi kalah oleh gaya sebaris SweetAlert2.
 */
class GayaDialogTest extends TestCase
{
    private const BERKAS = 'resources/views/public/partials/gaya-dialog.blade.php';

    private const TATA_LETAK = 'resources/views/public/layout/header.blade.php';

    private function isi(string $relatif): string
    {
        $jalur = dirname(__DIR__, 2) . '/' . $relatif;

        $this->assertFileExists($jalur, $relatif . ' tidak ada.');

        return (string) file_get_contents($jalur);
    }

    public function test_gaya_dialognya_disertakan_tata_letak_umum(): void
    {
        /*
         * Berkasnya boleh sempurna, tetapi kalau tidak disertakan ia tidak
         * mengubah satu piksel pun — dan tidak ada galat apa pun yang
         * memberi tahu. Yang terlihat cuma dialog yang kembali berupa
         * bawaan, dan itu mudah dikira memang begitu adanya.
         */
        $this->assertStringContainsString(
            "@include('public.partials.gaya-dialog')",
            $this->isi(self::TATA_LETAK),
            'Tata letak umum tidak lagi menyertakan gaya dialognya, jadi seluruh '
            . 'halaman kembali memakai rupa bawaan SweetAlert2.'
        );
    }

    public function test_warna_tombolnya_menang_atas_gaya_sebaris(): void
    {
        /*
         * INI yang paling mudah "dirapikan" orang berikutnya.
         *
         * SweetAlert2 menyetel warna latar tombolnya lewat atribut style
         * SEBARIS — dari confirmButtonColor yang diberikan pemanggil — dan
         * gaya sebaris selalu menang atas berkas gaya mana pun. Tanpa
         * !important, tombolnya tetap berwarna apa pun yang kebetulan
         * ditulis di sepuluh pemanggil yang berbeda, dan seragamnya tidak
         * pernah tercapai.
         *
         * Dicari di dalam ATURANNYA masing-masing, bukan di seluruh berkas:
         * !important yang kebetulan ada di aturan lain tidak menolong tombol
         * ini sama sekali.
         */
        $isi = $this->isi(self::BERKAS);

        foreach (['swal2-confirm', 'swal2-cancel'] as $tombol) {
            $cocok = preg_match(
                '/\.swal2-styled\.' . preg_quote($tombol, '/') . '\s*\{([^}]*)\}/',
                $isi,
                $c
            );

            $this->assertSame(1, $cocok, 'Aturan untuk .' . $tombol . ' tidak ada.');

            /*
             * Diperiksa pada DEKLARASI background-nya, bukan "ada !important
             * di mana pun di dalam aturan ini".
             *
             * Versi pertama memeriksa seluruh isi aturannya — dan tetap hijau
             * saat !important dicabut dari background, sebab box-shadow di
             * baris berikutnya masih memilikinya. Dibuktikan: mencabutnya dari
             * background saja tidak membuat uji ini merah.
             *
             * Yang ditimpa SweetAlert2 lewat gaya sebaris memang latarnya,
             * jadi di situlah !important harus ada.
             */
            $cocokLatar = preg_match('/background(-color)?\s*:[^;]*!important/', $c[1]);

            $this->assertSame(1, $cocokLatar,
                'Latar .' . $tombol . ' ditulis tanpa !important, jadi ia kalah oleh '
                . 'gaya sebaris yang dipasang SweetAlert2 dari confirmButtonColor.');
        }
    }

    public function test_gayanya_tidak_menyentuh_letak_dialognya(): void
    {
        /*
         * SweetAlert2 menghitung posisi dan ukuran dialognya sendiri lewat
         * display, position, dan width. Menimpanya berarti memperbaiki rupa
         * satu dialog sambil merusak letak sembilan lainnya — dan yang rusak
         * baru ketahuan di halaman yang tidak sempat dibuka.
         *
         * `width` di dalam media query layar sempit DIKECUALIKAN: yang
         * diubah di sana lebar TOMBOLNYA supaya bertumpuk penuh, bukan lebar
         * dialognya.
         */
        $isi = $this->isi(self::BERKAS);

        // Komentar dibuang dulu; ketiga kata itu justru disebut di komentar
        // yang menerangkan kenapa keduanya tidak boleh disentuh.
        $isi = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $isi);
        $isi = (string) preg_replace('#/\*.*?\*/#s', '', $isi);

        foreach (['.swal2-popup', '.swal2-container'] as $pemilih) {
            $cocok = preg_match_all(
                '/' . preg_quote($pemilih, '/') . '\s*\{([^}]*)\}/',
                $isi,
                $semua
            );

            if ($cocok === 0) {
                continue;
            }

            foreach ($semua[1] as $isiAturan) {
                foreach (['display:', 'position:', 'width:'] as $medan) {
                    $this->assertStringNotContainsString($medan, $isiAturan,
                        $pemilih . ' menyetel ' . $medan . ' — SweetAlert2 menghitungnya '
                        . 'sendiri, dan menimpanya merusak letak dialog di halaman lain.');
                }
            }
        }
    }
}
