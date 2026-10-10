<?php

namespace Tests\Feature\PendaftaranLayanan;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Yang menutupi data di layar ponsel.
 *
 * Terukur di 390x844 sebelum perbaikan ini: kepala halaman 226px, ubin 93px,
 * baris angka 176px, ringkasan penyaring 42px — kartu pendaftar pertama baru
 * mulai di 773px dari 844px layar. Panitia membuka daftarnya dan tidak
 * melihat satu pendaftar pun sebelum menggulir.
 *
 * Yang dijaga di sini aturannya, bukan angkanya: ukuran piksel tidak bisa
 * dibaca dari sumber. Tetapi tiap aturan di bawah ini punya angka
 * pengukurnya sendiri di komentar berkasnya.
 */
class LayarPonselPendaftarTest extends TestCase
{
    private function gaya(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/index.blade.php')
        );
    }

    private function bersama(): string
    {
        return file_get_contents(public_path('assets/css/mis-ui.css'));
    }

    /**
     * Ringkasan dilipat di ponsel — 269px (ubin + angka) jadi 42px.
     *
     * Dilipat, BUKAN dibuang: ubinnya sekaligus pintasan saringan, dan
     * angka pokoknya tetap tertulis di ringkasan lipatannya.
     */
    #[Test]
    public function ringkasan_dilipat_di_ponsel(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/<details[^>]*class="mis-lipat pdl-lipat"[^>]*data-mis-lipat/', $isi,
            'Ringkasan tidak lagi dilipat; ubin dan baris angkanya kembali '
            . 'menutupi layar pertama sebelum ada satu pendaftar pun.'
        );

        $this->assertStringContainsString('pdl-lipat-angka', $isi,
            'Angka pokoknya hilang dari ringkasan lipatan, jadi yang paling '
            . 'sering dicari menuntut satu ketukan lagi.');
    }

    /**
     * mis-ui.js memaksa <details data-mis-lipat> terbuka di >= 768px. Tanpa
     * penanda itu, layar lebar ikut terlipat — dan di sana tidak ada
     * masalah ruang sama sekali.
     */
    #[Test]
    public function layar_lebar_tidak_ikut_terlipat(): void
    {
        $this->assertStringContainsString("matchMedia('(min-width: 768px)')",
            file_get_contents(public_path('assets/js/mis-ui.js')),
            'Pemaksa buka di layar lebar hilang; ringkasan ikut terlipat di desktop.');
    }

    /**
     * Tiga tombol sekunder di kepala halaman: satu baris, ikon saja, hanya
     * di lebar ponsel. Bertumpuk, ketiganya membuat kepala halaman 278px.
     */
    #[Test]
    public function tombol_kepala_tidak_bertumpuk_di_ponsel(): void
    {
        $this->assertStringContainsString('mis-kepala-aksi-trio', $this->gaya(),
            'Kepala halaman kembali menumpuk tombolnya.');

        $this->assertMatchesRegularExpression(
            '/\.mis-kepala-aksi-trio \.mis-tombol-halus \.mis-tombol-teks\s*\{[^}]*position:\s*absolute/s',
            $this->bersama(),
            'Tulisan tombolnya tidak disembunyikan, jadi ketiganya tidak muat sebaris.'
        );
    }

    /**
     * Tombol yang tulisannya disembunyikan WAJIB punya aria-label: tanpa
     * itu ia jadi tombol tanpa nama bagi pembaca layar.
     */
    #[Test]
    public function tombol_ikon_tetap_punya_namanya(): void
    {
        $isi = $this->gaya();

        /*
         * Tiap <a ...> diambil utuh lalu diperiksa isinya, bukan dicocokkan
         * dengan satu pola panjang. Pola yang mengandaikan urutan atribut
         * dan letak ganti baris pecah pada perubahan yang tidak ada
         * hubungannya — dan merahnya menuding aksesibilitas, padahal yang
         * berubah cuma pembungkusan barisnya.
         */
        preg_match_all('/<a\s[^>]*>/s', $isi, $cocok);

        foreach (['excel', 'pdf', 'terhapus'] as $tombol) {
            $tag = array_values(array_filter(
                $cocok[0],
                fn ($t) => str_contains($t, 'pendaftaran-layanan.' . $tombol)
            ));

            $this->assertNotEmpty($tag, "Tombol {$tombol} tidak ada di kepala halaman.");

            $this->assertStringContainsString('aria-label=', $tag[0],
                "Tombol {$tombol} kehilangan aria-label; di ponsel tulisannya "
                . 'disembunyikan, jadi ia jadi tombol ikon tanpa nama.');
        }
    }

    /** Medali kepala sebaris dengan judulnya — 44px yang terbuang. */
    #[Test]
    public function medali_kepala_tidak_makan_satu_baris_sendiri(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.mis-kepala > \.mis-kepala-teks\s*\{[^}]*flex:\s*1 1 0/s', $this->bersama(),
            'Basis 260px-nya kembali, jadi medali kepala duduk sendirian di satu baris penuh.'
        );
    }

    /** Baris angka: dua lajur di ponsel, bukan empat kotak bertumpuk. */
    #[Test]
    public function baris_angka_dua_lajur_di_ponsel(): void
    {
        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 575\.98px\) \{\s*\.pdl-uang-total\s*\{[^}]*grid-template-columns:\s*repeat\(2/s',
            $this->gaya(),
            'Baris angkanya kembali satu lajur; empat kotak bertumpuk jadi 205px.'
        );
    }

    // --------------------------------------------------- layar rincian

    private function rincian(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );
    }

    /**
     * Sasaran ketuk minimal 40px di ponsel. Terukur: tombol salin 24x24,
     * tombol aksi 32x32, pembuka jejak 30px. Yang meleset bukan tidak
     * terjadi apa-apa — tombol WhatsApp ada persis di sebelahnya.
     */
    #[Test]
    public function sasaran_ketuk_cukup_besar_di_ponsel(): void
    {
        $isi = $this->rincian();

        foreach (['.rin-salin', '.rin-aksi'] as $kelas) {
            $this->assertMatchesRegularExpression(
                '/\\' . $kelas . '\s*\{[^}]*height:\s*40px/s', $isi,
                "Sasaran ketuk {$kelas} di ponsel kembali di bawah 40px."
            );
        }

        $this->assertMatchesRegularExpression(
            '/\[data-mis-ringkas\]\s*\{[^}]*min-height:\s*40px/s', $isi,
            'Tombol pembuka jejak dan daftar peserta kembali setinggi 30px.'
        );
    }

    /**
     * Tautan ke pendaftaran ganda juga >= 40px.
     *
     * Blok itu ditambahkan SESUDAH penyisiran sasaran ketuk pertama, jadi ia
     * lolos — terukur 16px tinggi. Penjaga ini menyebut kelasnya, bukan
     * sekadar "yang di layar rincian", supaya blok berikutnya tidak ikut
     * lolos dengan cara yang sama.
     */
    #[Test]
    public function tautan_pendaftaran_ganda_cukup_besar(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-teks > a\s*\{[^}]*min-height:\s*40px/s', $this->rincian(),
            'Tautan pendaftaran ganda kembali setinggi 16px.'
        );
    }

    /**
     * Ubin di layar arsip memakai pembungkus geser + petunjuknya, sama
     * dengan layar daftar. Tanpa itu, di ponsel ubin keduanya meluber ke
     * kanan tanpa satu tanda pun bahwa barisnya berlanjut.
     */
    #[Test]
    public function ubin_arsip_punya_petunjuk_gesernya(): void
    {
        $isi = file_get_contents(
            resource_path('views/account/pendaftaran_layanan/terhapus.blade.php')
        );

        $this->assertStringContainsString('mis-ringkas-geser', $isi);
        $this->assertStringContainsString('mis-ringkas-petunjuk', $isi,
            'Petunjuk gesernya hilang; ubin kedua meluber tanpa tanda apa pun.');
    }

    /**
     * Tab Hapus selalu di ujung kanan. Jumlah tabnya berayun 2 sampai 7
     * tergantung isi pendaftarannya, jadi "tab ke-sekian" bukan tempat yang
     * tetap — dan yang paling mahal kalau meleset justru tab itu.
     */
    #[Test]
    public function tab_hapus_selalu_di_ujung(): void
    {
        $this->assertMatchesRegularExpression(
            '/#rin-tab > li:last-child\s*\{[^}]*margin-left:\s*auto/s', $this->rincian(),
            'Tab Hapus kembali mengikuti jumlah tab di depannya, jadi tempatnya '
            . 'berpindah-pindah antar pendaftaran.'
        );
    }
}
