<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tombol di kepala layar daftar berperilaku sama di SEMUA layar.
 *
 * Letak tombol yang sama tidak boleh berpindah antar layar. Sebelum ini
 * Angkatan Layanan menaruh tombol utamanya PALING AKHIR sementara Pendaftar
 * Layanan menaruhnya pertama, dan dua layar unduhan tidak memakai kelas
 * ponsel bersamanya sama sekali — di 390px ketiganya bertumpuk dan memakan
 * satu layar penuh sebelum ada satu data pun yang terlihat.
 *
 * Dijaga dari SUMBER tampilannya karena yang menentukan urutan dan kelasnya
 * memang di sana; akibatnya terukur di peramban dan dicatat di komentar
 * masing-masing berkas.
 */
class TombolKepalaSeragamTest extends TestCase
{
    /**
     * Layar daftar yang kepalanya punya tombol unduhan.
     *
     * Layar BARU yang punya unduhan wajib masuk daftar ini — daftar inilah
     * yang membuat "seragam" berarti sesuatu.
     *
     * @var array<int, string>
     */
    private const LAYAR = [
        'pendaftaran_layanan/index.blade.php',
        'kategori_layanan/index.blade.php',
        'customer/index.blade.php',
        'promo_layanan/index.blade.php',
        'clinik_scopus_biaya_persesi/index.blade.php',
    ];

    private function isi(string $layar): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/account/' . $layar
        );
    }

    /** Potongan markah dari pembuka .mis-kepala-aksi sampai penutup kepalanya. */
    private function blokAksi(string $isi, string $layar): string
    {
        $mulai = strpos($isi, 'class="mis-kepala-aksi');

        $this->assertNotFalse($mulai, $layar . ': tidak punya blok .mis-kepala-aksi sama sekali.');

        $blok = substr($isi, $mulai, 3000);

        /*
         * Ekspresi Blade DIBUANG sebelum markahnya dibaca.
         *
         * `{{ route('x', ['layanan' => $l]) }}` memuat tanda ">" dari panah
         * larik, dan itu memutus pola `<a[^>]*>` tepat di tengah atribut —
         * terukur: layar Angkatan memulangkan NOL tombol padahal punya dua,
         * dan penjaganya merah karena polanya, bukan karena markahnya.
         */
        return (string) preg_replace('/\{\{.*?\}\}/s', '', $blok);
    }

    #[Test]
    public function semua_kepala_memakai_kelas_ponsel_bersamanya(): void
    {
        /*
         * Tanpa .mis-kepala-aksi-pasangan, kedua tombol unduhan bertumpuk
         * sendiri-sendiri di ponsel. Terukur di layar lain: tiga tombol
         * bertumpuk membuat kepala halaman 259px dan kartu pertama baru mulai
         * di 537px dari 844px layar.
         */
        foreach (self::LAYAR as $layar) {
            $this->assertStringContainsString(
                'mis-kepala-aksi-pasangan',
                $this->blokAksi($this->isi($layar), $layar),
                $layar . ': kepala aksinya tidak memakai kelas ponsel bersama; tombolnya akan bertumpuk.'
            );
        }
    }

    #[Test]
    public function tombol_utama_selalu_diletakkan_pertama(): void
    {
        /*
         * Tindakan utama mendahului pelengkapnya. Diletakkan terakhir di satu
         * layar dan pertama di layar lain, orang yang berpindah layar harus
         * mencarinya lagi tiap kali — dan admin layar ini bukan orang yang
         * hafal letak tombol.
         */
        foreach (self::LAYAR as $layar) {
            $blok = $this->blokAksi($this->isi($layar), $layar);

            $utama = strpos($blok, 'mis-tombol-ungu');
            $unduh = strpos($blok, 'mis-tombol-halus');

            // Layar tanpa tombol utama tidak punya apa pun untuk diurutkan.
            if ($utama === false) {
                continue;
            }

            $this->assertNotFalse($unduh, $layar . ': tombol unduhannya tidak ketemu.');
            $this->assertLessThan(
                $unduh,
                $utama,
                $layar . ': tombol utamanya ditaruh SESUDAH unduhan; di layar lain ia pertama.'
            );
        }
    }

    #[Test]
    public function tombol_unduhan_menyebut_dirinya_untuk_pembaca_layar(): void
    {
        /*
         * Di lebar ponsel tulisan panjangnya ditukar nama pendek, dan pada
         * layar bertombol banyak tulisannya disembunyikan sama sekali. Tombol
         * ikon tanpa nama tidak menyebut apa pun ke pembaca layar, dan title
         * tidak pernah muncul di ponsel — di sana tidak ada kursor yang bisa
         * berhenti di atas tombol.
         */
        foreach (self::LAYAR as $layar) {
            $blok = $this->blokAksi($this->isi($layar), $layar);

            preg_match_all('/<a[^>]*mis-tombol-halus[^>]*>/s', $blok, $tombol);

            $this->assertNotEmpty($tombol[0], $layar . ': tidak ada tombol unduhan yang ketemu.');

            foreach ($tombol[0] as $satu) {
                $this->assertStringContainsString('aria-label', $satu,
                    $layar . ': ada tombol unduhan tanpa aria-label; di ponsel ia jadi ikon tanpa nama.');
            }
        }
    }

    #[Test]
    public function ikon_unduhannya_berwarna_sama_di_semua_layar(): void
    {
        /*
         * Hijau untuk Excel, merah untuk PDF. Dibiarkan polos di satu layar,
         * tombol yang sama terbaca berbeda — dan warna itulah yang dikenali
         * lebih dulu daripada tulisannya.
         */
        foreach (self::LAYAR as $layar) {
            $blok = $this->blokAksi($this->isi($layar), $layar);

            if (str_contains($blok, 'fa-file-excel')) {
                $this->assertMatchesRegularExpression('/fa-file-excel[^"]*mis-ikon-hijau/', $blok,
                    $layar . ': ikon Excel-nya tidak hijau seperti di layar lain.');
            }

            if (str_contains($blok, 'fa-file-pdf')) {
                $this->assertMatchesRegularExpression('/fa-file-pdf[^"]*mis-ikon-merah/', $blok,
                    $layar . ': ikon PDF-nya tidak merah seperti di layar lain.');
            }
        }
    }
}
