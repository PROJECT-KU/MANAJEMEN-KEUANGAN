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
     * Ringkasan yang DIBUKA tidak menuntut geser menyamping lagi.
     *
     * Barisan geser masuk akal untuk ringkasan yang selalu terpampang — ia
     * menahan tinggi layar. Di dalam lipatan aturannya terbalik: orang baru
     * saja menekan "Ringkasan" untuk melihat angkanya, lalu yang terbuka
     * cuma satu ubin penuh dengan empat lainnya di luar layar. Terukur di
     * 390px sebelum ini dipasang: lebar tampak 362px, lebar isi 1145px —
     * empat dari lima ubin terpotong.
     */
    #[Test]
    public function ringkasan_terbuka_tidak_perlu_digeser_di_ponsel(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.pdl-lipat \.mis-ringkas\s*\{[^}]*display:\s*grid/s', $isi,
            'Ubin di dalam lipatan kembali jadi barisan geser; empat dari lima '
            . 'angkanya di luar layar padahal panelnya sengaja dibuka.'
        );

        /*
         * Penjaga bobotnya, bukan cuma ada-tidaknya aturannya.
         *
         * Penanda .bisa-geser dipasang mis-ui.js, dan aturan yang
         * memunculkan petunjuknya berbobot (0,3,0). Ditulis lebih rendah,
         * baris "Geser untuk lihat semua" tetap terpampang di bawah kisi
         * yang tidak bisa digeser ke mana pun — dan tidak ada galat apa pun
         * yang menandainya.
         */
        $this->assertMatchesRegularExpression(
            '/\.pdl-lipat \.mis-ringkas-geser\.bisa-geser \.mis-ringkas-petunjuk\s*\{[^}]*display:\s*none/s',
            $isi,
            'Petunjuk geser dipadamkan dengan bobot yang lebih rendah daripada '
            . 'aturan yang memunculkannya, jadi ia tetap terbaca.'
        );
    }

    /**
     * Kartu Ringkasan dan kartu Cari & saring tidak bersentuhan.
     *
     * Terukur di 390px: 0px saat tertutup — kedua bilahnya benar-benar
     * menempel dan terbaca seperti satu kotak yang terbelah — dan 12px saat
     * terbuka, yang itu pun datang dari margin baris angkanya, bukan dari
     * kartunya sendiri.
     */
    #[Test]
    public function kartu_ringkasan_punya_jarak_ke_kartu_berikutnya(): void
    {
        /*
         * Diambil dari blok ponselnya saja. Di lebar lain kedua kartu ini
         * tidak berdempetan sama sekali, dan aturan yang dicari memang
         * hanya berlaku di sana.
         */
        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s',
            $this->gaya(), $blok), 'Blok gaya ponsel halaman ini tidak ada.');

        $this->assertSame(1,
            preg_match('/\.pdl-lipat\s*\{[^}]*margin-bottom:\s*(\d+)px/s', $blok[1], $cocok),
            'Kartu Ringkasan tidak punya jarak ke kartu di bawahnya; keduanya '
            . 'bersentuhan di ponsel — terukur 0px saat tertutup.');

        $this->assertGreaterThanOrEqual(10, (int) $cocok[1],
            'Jaraknya terlalu rapat untuk membedakan kedua kartu.');
    }

    /**
     * Kotak angka yang ganjil terakhir MEMBENTANG dua lajur.
     *
     * Jumlahnya dua sampai empat tergantung isi pembukuannya. Ganjil, yang
     * terakhir berdiri sendiri di lajur kiri dan separuh barisnya menganga —
     * terukur: kotak "menunggu lebih dari 7 hari" selebar 177px dengan 177px
     * kosong di sebelahnya.
     */
    #[Test]
    public function kotak_angka_ganjil_terakhir_membentang_penuh(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.pdl-uang-total > \*:last-child:nth-child\(odd\)\s*\{[^}]*grid-column:\s*1 \/ -1/s',
            $this->gaya(),
            'Kotak angka terakhir kembali menyisakan petak kosong di sebelahnya '
            . 'saat jumlahnya ganjil.'
        );
    }

    /**
     * Keempat tombol sekunder MENYEBUT dirinya di ponsel.
     *
     * Nama panjangnya memang disembunyikan supaya keempatnya muat sebaris,
     * tetapi sempat disembunyikan tanpa pengganti — dan yang tersisa empat
     * kotak berisi ikon saja. Admin layar ini bukan orang teknis: berkas
     * hijau, papan jepit, berkas merah, dan tong sampah tidak menyebut
     * Excel, presensi, PDF, atau arsip kepada siapa pun yang belum pernah
     * menekannya.
     *
     * aria-label tidak menutup lubang ini — itu hanya terbaca pembaca
     * layar — dan title tidak pernah muncul di ponsel, sebab di sana tidak
     * ada kursor yang bisa berhenti di atas tombol.
     */
    #[Test]
    public function tombol_kepala_menyebut_namanya_di_ponsel(): void
    {
        $isi = $this->gaya();

        /*
         * Dicocokkan pada BLOK AKSINYA, bukan seluruh halaman: kelas yang
         * sama muncul juga di gaya dan skrip halaman ini, dan penjaga yang
         * membaca seluruh berkas tetap hijau walau tombolnya sendiri sudah
         * kehilangan namanya.
         */
        $this->assertSame(1, preg_match('/<div class="mis-kepala-aksi[^"]*" id="pdl-aksi">(.*?)<\/div>\s*<\/div>/s',
            $isi, $cocok), 'Blok aksi kepala halaman tidak ditemukan.');

        $blok = $cocok[1];

        preg_match_all('/class="mis-tombol mis-tombol-halus[^"]*"/', $blok, $tombol);
        preg_match_all('/class="mis-tombol-ringkas"[^>]*>([^<]+)</', $blok, $nama);

        /*
         * Dipadankan satu lawan satu, BUKAN dihitung ke angka tetap.
         *
         * Tombol presensi ditulis dua kali — bentuk hidup dan bentuk mati
         * — jadi markahnya memuat lima tombol untuk empat yang terender.
         * Angka tetap di sini berarti penjaganya merah setiap kali ada
         * tombol bersyarat baru, dan itu bukan cacat yang mau dijaga.
         */
        $this->assertGreaterThanOrEqual(4, count($tombol[0]),
            'Tombol sekunder di kepala halaman hilang.');

        $this->assertCount(count($tombol[0]), $nama[1],
            'Ada tombol sekunder tanpa nama pendek; di ponsel ia jadi kotak '
            . 'berisi ikon saja.');

        foreach ($nama[1] as $teks) {
            $this->assertLessThanOrEqual(9, mb_strlen(trim($teks)),
                'Nama pendek "' . trim($teks) . '" terlalu panjang; di 390px '
                . 'tiap tombol cuma selebar 75px.');
        }
    }

    /**
     * Nama pendeknya HANYA di ponsel, dan nama panjangnya tetap di lebar lain.
     *
     * Keduanya tampil bersamaan berarti tombolnya berbunyi "Unduh Excel
     * Excel" — persis yang terlihat saat gayanya belum dipasang.
     */
    #[Test]
    public function nama_pendek_tombol_hanya_muncul_di_ponsel(): void
    {
        $css = $this->bersama();

        $this->assertMatchesRegularExpression(
            '/\.mis-tombol-ringkas\s*\{[^}]*display:\s*none/s', $css,
            'Nama pendeknya tidak disembunyikan di lebar biasa, jadi tombolnya '
            . 'berbunyi "Unduh Excel Excel" di desktop.'
        );

        $this->assertMatchesRegularExpression(
            '/@media \(max-width: 575\.98px\)[^@]*\.mis-kepala-aksi-trio \.mis-tombol-halus \.mis-tombol-ringkas\s*\{[^}]*display:\s*block/s',
            $css,
            'Nama pendeknya tidak dimunculkan di lebar ponsel, jadi tombolnya '
            . 'tetap ikon telanjang di sana.'
        );

        // Ikon di atas, nama di bawahnya. Berdampingan, nama 75px tidak muat
        // di sebelah ikonnya dan tombolnya meluber.
        $this->assertMatchesRegularExpression(
            '/\.mis-kepala-aksi-trio \.mis-tombol-halus\s*\{[^}]*flex-direction:\s*column/s',
            $css,
            'Ikon dan namanya kembali berdampingan; di 75px keduanya tidak muat.'
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
