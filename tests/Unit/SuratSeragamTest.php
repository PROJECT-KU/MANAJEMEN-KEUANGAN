<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Surat bukti pendaftaran SEMUA layanan memakai satu kerangka yang sama.
 *
 * Diminta pemiliknya 24 Okt 2026: "semua email diseragamkan seperti webinar
 * eksklusif untuk semua pemesanan layanan". Sebelum itu tiap layanan punya
 * desainnya sendiri, dan jaraknya jauh:
 *
 *   Webinar Eksklusif  tabel bergaya sebaris, logo yayasan, 336 baris
 *   Scopus Camp        logo MIS biru, kepala biru muda, susunan lain
 *   Bibliometrik       lain lagi
 *   Scopus Kafe        memuat Bootstrap, FontAwesome, style.css, bahkan
 *                      sweetalert2 lewat <link>, lalu menata isinya dengan
 *                      kelas .card di dalam blok <style>
 *
 * Satu pelanggan yang memesan dua layanan menerima dua surat yang tidak
 * terlihat berasal dari tempat yang sama.
 *
 * ---------------------------------------------------------------------------
 * Kenapa dijaga di SUMBERNYA
 * ---------------------------------------------------------------------------
 *
 * Desain yang disalin akan menyimpang perlahan, dan yang pertama menyimpang
 * selalu surat yang jarang dibuka. Penyimpangan itu TIDAK membuat satu uji pun
 * merah: suratnya tetap terkirim, tetap terbaca, cuma tidak lagi seragam. Jadi
 * yang dijaga bukan hasil rendernya melainkan satu hal yang bisa diperiksa
 * dengan pasti — tiap surat menyertakan kerangka yang sama.
 */
class SuratSeragamTest extends TestCase
{
    /*
     * Disebut LENGKAP dengan @include dan tanda kutipnya.
     *
     * Versi pertama cuma mencari 'emails.pendaftaran.bukti', dan itu masih
     * cocok dengan 'emails.pendaftaran.buktiXX' — nama yang salah ketik atau
     * sengaja dialihkan tetap lolos, dan penjaganya hijau untuk surat yang
     * sebenarnya tidak memakai kerangka apa pun. Dibuktikan: mengubah
     * namanya jadi buktiXX tidak membuat uji ini merah.
     */
    private const KERANGKA = "@include('emails.pendaftaran.bukti'";

    /**
     * Surat bukti pendaftaran tiap layanan.
     *
     * Clinik Scopus TIDAK ada di sini: layanan itu memang tidak pernah
     * mengirim surat bukti pemesanan — diperiksa ke seluruh app/Mail.
     *
     * @var array<string, string>
     */
    private const SURAT_BUKTI = [
        'Webinar Eksklusif' => 'public/webinar_eksklusif/email_pendaftaran.blade.php',
        'Scopus Camp' => 'public/scopus_camp/mail.blade.php',
        'Analisis Bibliometrik' => 'public/analisis_bibliometrik/mail.blade.php',
        'Scopus Kafe' => 'public/scopus_kafe/send_email_sukses.blade.php',
    ];

    private const KERANGKA_CETAKAN = "@include('pendaftaran.dokumen_pdf'";

    /**
     * Faktur/kwitansi yang DILAMPIRKAN ke surat tiap layanan.
     *
     * Scopus Kafe dan Clinik Scopus tidak ada di sini: keduanya memang tidak
     * melampirkan cetakan apa pun — diperiksa ke seluruh app/Mail, hanya tiga
     * Mailable yang memanggil Pdf::loadView.
     *
     * @var array<string, string>
     */
    private const CETAKAN = [
        'Webinar Eksklusif' => 'public/webinar_eksklusif/dokumen_pdf.blade.php',
        'Scopus Camp' => 'public/scopus_camp/dokumen_pdf.blade.php',
        'Analisis Bibliometrik' => 'public/analisis_bibliometrik/dokumen_pdf.blade.php',
        'Scopus Kafe' => 'public/scopus_kafe/dokumen_pdf.blade.php',
    ];

    private function isi(string $relatif): string
    {
        $jalur = dirname(__DIR__, 2) . '/resources/views/' . $relatif;

        $this->assertFileExists($jalur, $relatif . ' tidak ada.');

        return (string) file_get_contents($jalur);
    }

    /**
     * Membuang komentar sebelum isinya diperiksa.
     *
     * Blok <style> dan <link> justru DISEBUT di komentar yang menerangkan
     * kenapa keduanya tidak boleh dipakai — dan komentar tidak pernah
     * terkirim, jadi menuduhnya salah. Terjadi pada versi pertama penjaga ini
     * sendiri: ia merah karena kalimat yang justru menjaga hal yang sama.
     *
     * Penjaga yang merah karena alasan yang salah mengajari orang
     * mengabaikannya, dan itu lebih berbahaya daripada tidak ada penjaga.
     * Caranya disalin dari Tests\Unit\IkonAdaGlifnyaTest, yang sudah kena
     * persis begini 18 Okt 2026.
     */
    private function tanpaKomentar(string $isi): string
    {
        // Komentar Blade lebih dulu: isinya bisa memuat /* */ yang bukan
        // komentar PHP, dan membuangnya terbalik akan menyisakan potongan.
        $isi = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $isi);

        return (string) preg_replace('#/\*.*?\*/#s', '', $isi);
    }

    public function test_semua_surat_bukti_memakai_kerangka_yang_sama(): void
    {
        $melenceng = [];

        foreach (self::SURAT_BUKTI as $layanan => $relatif) {
            if (! str_contains($this->tanpaKomentar($this->isi($relatif)), self::KERANGKA)) {
                $melenceng[] = $layanan . ' (' . $relatif . ')';
            }
        }

        $this->assertSame([], $melenceng,
            "Surat bukti pendaftaran berikut TIDAK memakai kerangka bersama "
            . "emails.pendaftaran.bukti, jadi desainnya akan menyimpang sendiri:\n- "
            . implode("\n- ", $melenceng) . "\n");
    }

    public function test_tidak_ada_surat_bukti_yang_merakit_kerangkanya_sendiri(): void
    {
        /*
         * Menyertakan kerangkanya saja tidak cukup: surat yang menyertakannya
         * TETAPI juga membuka <html>-nya sendiri akan mengirim dua dokumen
         * bersarang, dan sebagian klien surat hanya menggambar yang pertama.
         *
         * Diperiksa dari <!DOCTYPE, bukan dari <html>: kata "html" muncul
         * juga di atribut lang dan di komentar.
         */
        $kembar = [];

        foreach (self::SURAT_BUKTI as $layanan => $relatif) {
            if (stripos($this->tanpaKomentar($this->isi($relatif)), '<!DOCTYPE') !== false) {
                $kembar[] = $layanan . ' (' . $relatif . ')';
            }
        }

        $this->assertSame([], $kembar,
            "Surat berikut menyertakan kerangka bersama TETAPI masih membuka "
            . "dokumennya sendiri; yang terkirim dua dokumen bersarang:\n- "
            . implode("\n- ", $kembar) . "\n");
    }

    public function test_tidak_ada_surat_yang_memuat_gaya_dari_berkas_luar(): void
    {
        /*
         * Gmail — yang paling banyak dipakai pembaca surat ini — membuang
         * <link> DAN blok <style> sama sekali. Surat Scopus Kafe memuat
         * Bootstrap, FontAwesome, style.css, components.css, bahkan
         * sweetalert2 lewat <link>, lalu menata isinya dengan kelas; yang
         * benar-benar diterima orangnya teks telanjang tanpa warna, tanpa
         * kartu, tanpa logo.
         *
         * Kerangka bersamanya menulis gaya SEBARIS di tiap unsur, jadi tidak
         * ada satu pun berkas gaya yang perlu dimuat.
         */
        $bergaya = [];

        foreach (self::SURAT_BUKTI as $layanan => $relatif) {
            $isi = $this->tanpaKomentar($this->isi($relatif));

            if (preg_match('/<link[^>]+stylesheet/i', $isi) || preg_match('/<style[\s>]/i', $isi)) {
                $bergaya[] = $layanan . ' (' . $relatif . ')';
            }
        }

        $this->assertSame([], $bergaya,
            "Surat berikut memuat gaya dari berkas luar atau blok <style>; "
            . "Gmail membuang keduanya, dan yang sampai ke pembacanya teks "
            . "telanjang:\n- " . implode("\n- ", $bergaya) . "\n");
    }

    public function test_semua_cetakan_memakai_kerangka_yang_sama(): void
    {
        /*
         * Lampirannya ikut diseragamkan 24 Okt 2026, atas permintaan
         * pemiliknya: "attach file nya di email belum sama seperti yang ada
         * di webinar eksklusif".
         *
         * Sebelumnya Scopus Camp dan Bibliometrik punya cetakannya sendiri —
         * logo di tengah, tabel tanpa kolom Qty maupun Harga satuan, tanpa
         * cap, tanpa pita kaki, dan tanpa baris "Sisa tagihan". Dokumen untuk
         * dua layanan yang sama penerbitnya terbaca seperti berasal dari dua
         * kantor, dan yang memegangnya sering bendahara yang harus
         * mencocokkan keduanya.
         */
        $melenceng = [];

        foreach (self::CETAKAN as $layanan => $relatif) {
            if (! str_contains($this->tanpaKomentar($this->isi($relatif)), self::KERANGKA_CETAKAN)) {
                $melenceng[] = $layanan . ' (' . $relatif . ')';
            }
        }

        $this->assertSame([], $melenceng,
            "Cetakan berikut TIDAK memakai kerangka bersama "
            . "pendaftaran.dokumen_pdf, jadi angkanya akan menyimpang sendiri:\n- "
            . implode("\n- ", $melenceng) . "\n");
    }

    public function test_tidak_ada_cetakan_yang_merakit_halamannya_sendiri(): void
    {
        $kembar = [];

        foreach (self::CETAKAN as $layanan => $relatif) {
            if (stripos($this->tanpaKomentar($this->isi($relatif)), '<!DOCTYPE') !== false) {
                $kembar[] = $layanan . ' (' . $relatif . ')';
            }
        }

        $this->assertSame([], $kembar,
            "Cetakan berikut menyertakan kerangka bersama TETAPI masih membuka "
            . "halamannya sendiri; Dompdf menggambar dua dokumen bersarang:\n- "
            . implode("\n- ", $kembar) . "\n");
    }

    public function test_kerangka_cetakannya_memakai_helvetica(): void
    {
        /*
         * BUKAN DejaVu, dan ini soal ukuran berkas.
         *
         * Dompdf MENYISIPKAN font yang dipakai ke dalam tiap PDF. Terukur:
         * DejaVu 1.289.545 bita melawan Helvetica 20.965 — dan berkas ini
         * dilampirkan ke setiap surat pendaftaran. Helvetica salah satu font
         * bawaan PDF, jadi ia tidak perlu disisipkan sama sekali.
         */
        $isi = $this->tanpaKomentar($this->isi('pendaftaran/dokumen_pdf.blade.php'));

        $this->assertStringContainsString('Helvetica', $isi,
            'Kerangka cetakannya tidak lagi memakai Helvetica.');

        $this->assertStringNotContainsString('DejaVu', $isi,
            'Kerangka cetakannya memakai DejaVu; Dompdf menyisipkan fontnya utuh '
            . 'ke tiap berkas — 1.289.545 bita melawan 20.965.');
    }

    public function test_kerangkanya_menulis_gaya_sebaris(): void
    {
        /*
         * Sisi sebaliknya, dan ia yang membuat ketiga uji di atas berarti:
         * kerangkanya sendiri harus benar-benar bergaya sebaris. Kalau ia
         * suatu saat beralih ke <style>, seluruh surat ikut kehilangan
         * gayanya sekaligus — dan ketiga uji di atas tetap hijau.
         */
        $isi = $this->tanpaKomentar($this->isi('emails/pendaftaran/bukti.blade.php'));

        $this->assertDoesNotMatchRegularExpression('/<link[^>]+stylesheet/i', $isi,
            'Kerangka suratnya memuat berkas gaya dari luar; Gmail membuangnya.');

        $this->assertDoesNotMatchRegularExpression('/<style[\s>]/i', $isi,
            'Kerangka suratnya memakai blok <style>; Gmail membuangnya.');

        $this->assertGreaterThan(20, substr_count($isi, 'style="'),
            'Kerangka suratnya nyaris tidak bergaya sebaris, padahal itu '
            . 'satu-satunya cara gaya surat sampai ke Gmail.');
    }
}
