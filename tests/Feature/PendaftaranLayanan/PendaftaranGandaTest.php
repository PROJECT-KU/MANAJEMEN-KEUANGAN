<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\AnalisisBibliometrik;
use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\Support\PendaftaranGanda;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pendaftaran ganda disebutkan, bukan dibiarkan ketahuan sendiri.
 *
 * Orang yang mengira borangnya gagal lalu mengisi ulang meninggalkan dua
 * baris yang tidak saling tahu — dan dua kursi terpakai untuk satu orang.
 * Sebelum ini yang ketahuan hanya kalau panitia kebetulan ingat namanya.
 */
class PendaftaranGandaTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function email_yang_sama_dikenali(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $b = $this->camp($t, ['email' => $a->email, 'telp' => '0899-9999-0001']);

        $this->assertTrue($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $b->getKey()),
            'Pendaftaran dengan email yang sama tidak dikenali.');
    }

    /**
     * Nomornya dicari dalam SEMUA bentuk yang mungkin tersimpan. Kolomnya
     * diisi bertahun-tahun oleh layar yang berbeda, jadi satu orang bisa
     * tersimpan sebagai "6281…", "0811…", atau "+62 811-…".
     */
    #[Test]
    public function nomor_yang_sama_dalam_bentuk_berbeda_tetap_dikenali(): void
    {
        [$orang, $a, $t] = $this->dengan(['telp' => '081122330001']);

        $b = $this->camp($t, [
            'email' => 'lain-' . $t . '@contoh.test',
            'telp' => '+62 811-2233-0001',
        ]);

        $this->assertTrue($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $b->getKey()),
            'Nomor yang sama dengan penulisan berbeda tidak dikenali — '
            . 'dan diamnya terbaca sebagai "tidak ada yang ganda".');
    }

    #[Test]
    public function lintas_layanan_ikut_disebut(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $bib = $this->bibliometrik($t, ['email' => $a->email]);

        $this->assertTrue($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $bib->getKey()),
            'Pendaftaran di layanan lain tidak disebut; padahal satu transfer '
            . 'bisa saja dimaksudkan untuk keduanya.');
    }

    /**
     * Dirinya sendiri dibuang DI DALAM kueri, bukan disaring sesudahnya:
     * dengan batas enam baris, barisnya sendiri memakan satu slot dan yang
     * keenam tidak pernah terlihat.
     */
    #[Test]
    public function dirinya_sendiri_tidak_ikut(): void
    {
        [$orang, $a] = $this->dengan();

        $this->assertFalse($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $a->getKey()));
    }

    #[Test]
    public function orang_lain_tidak_ikut_terjaring(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $lain = $this->camp($t, [
            'email' => 'beda-' . $t . '@contoh.test',
            'telp' => '0877-1111-2222',
        ]);

        $this->assertFalse($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $lain->getKey()),
            'Pendaftaran orang lain ikut disebut ganda.');
    }

    /**
     * Nama yang sama TIDAK cukup: "Muhammad Rizki" ada belasan, dan daftar
     * ganda yang isinya orang berbeda berhenti dibaca.
     */
    #[Test]
    public function nama_yang_sama_saja_bukan_ganda(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $kembar = $this->camp($t, [
            'nama' => $a->nama,
            'email' => 'kembar-' . $t . '@contoh.test',
            'telp' => '0866-5555-4444',
        ]);

        $this->assertFalse($this->ganda($a)->contains(fn ($x) => (string) $x->id === (string) $kembar->getKey()));
    }

    #[Test]
    public function tanpa_email_dan_nomor_tidak_menjaring_apa_pun(): void
    {
        $this->assertTrue(
            PendaftaranGanda::lain('scopus_camp', 'apa-saja', '', null)->isEmpty(),
            'Baris tanpa pengenal menjaring SEMUA baris yang emailnya juga kosong.'
        );
    }

    #[Test]
    public function layarnya_memperingatkan_panitia(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $b = $this->camp($t, ['email' => $a->email]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $a->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('pendaftaran lain', $isi,
            'Layarnya tidak menyebut ada pendaftaran ganda.');
        $this->assertStringContainsString(strtoupper($b->id_transaksi), $isi,
            'Nomor pendaftaran gandanya tidak disebut, jadi tidak bisa ditelusuri.');
    }

    #[Test]
    public function yang_tidak_ganda_tidak_diberi_peringatan(): void
    {
        [$orang, $a] = $this->dengan();

        $this->assertStringNotContainsString('pendaftaran lain', $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $a->getKey()]))
            ->assertOk()->getContent());
    }

    // ------------------------------------------- rupa bloknya di layar

    private function gaya(): string
    {
        return file_get_contents(
            resource_path('views/account/pendaftaran_layanan/rincian.blade.php')
        );
    }

    /**
     * Keping berkisi, bukan enam baris penuh.
     *
     * Terukur sebelum diperbaiki: bloknya 488px di 1440px — 41% dari
     * SELURUH tab Ringkasan — dan 785px di ponsel. Peringatan yang memakan
     * empat persepuluh layar mendorong isi utama tab ini, ringkasan
     * pembayaran, ke bawah lipatan.
     */
    #[Test]
    public function kepingnya_berkisi_bukan_enam_baris(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar\s*\{[^}]*grid-template-columns:\s*repeat\(auto-fit/s',
            $this->gaya(),
            'Daftar pendaftaran ganda kembali satu lajur; enam baris penuh untuk '
            . 'sesuatu yang cuma perlu disadari.'
        );
    }

    /**
     * Keterangannya MEMBUNGKUS, tidak dipotong ellipsis.
     *
     * Versi pertama memotongnya dan menaruh teks penuhnya di `title` — dan
     * title tidak bisa dibuka di ponsel sama sekali. Yang terbaca di sana
     * tinggal "Menunggu baya…", jadi statusnya hilang permanen justru di
     * layar yang paling sempit.
     */
    #[Test]
    public function keterangannya_tidak_dipotong(): void
    {
        $aturan = preg_match('/\.rin-ganda-ket\s*\{([^}]*)\}/s', $this->gaya(), $cocok)
            ? $cocok[1] : '';

        $this->assertNotSame('', $aturan, 'Aturan .rin-ganda-ket tidak ketemu.');

        $this->assertStringNotContainsString('text-overflow', $aturan,
            'Keterangannya dipotong lagi; di ponsel title tidak bisa dibuka, '
            . 'jadi yang terpotong hilang permanen.');
    }

    /**
     * Aturan penyembunyinya WAJIB menyebut `.rin-ganda-daftar > li` DAN
     * kelasnya: `> li` menyetel display: flex dan berbobot (0,1,1),
     * sedangkan kelas tunggal (0,1,0) kalah terhadapnya — kepingnya tetap
     * tergambar tanpa galat apa pun. Jebakan yang sama pernah terjadi pada
     * daftar jejak.
     */
    #[Test]
    public function aturan_sembunyinya_cukup_berbobot(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar > li\.rin-ganda-lagi\s*\{[^}]*display:\s*none/s', $isi,
            'Bobot pemilih penyembunyinya kalah; kepingnya tetap tergambar di ponsel.'
        );

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar\.rin-ganda-penuh > li\.rin-ganda-lagi\s*\{[^}]*display:\s*flex/s', $isi,
            'Aturan pembukanya hilang; tombolnya tidak akan menampilkan apa pun.'
        );
    }

    /**
     * Nama layanan tidak diulang kalau sama dengan yang sedang dibuka.
     *
     * Yang ganda hampir selalu di layanan yang sama, jadi "Scopus Camp"
     * terulang enam kali sambil mendesak status dan tanggalnya keluar dari
     * keping — dan medali di sebelah kirinya sudah menyebutkan layanannya.
     */
    #[Test]
    public function nama_layanan_yang_sama_tidak_diulang(): void
    {
        [$orang, $a, $t] = $this->dengan();

        $this->camp($t, ['email' => $a->email]);

        $isi = $this->actingAs($orang)
            ->get(route('account.pendaftaran-layanan.rincian', ['scopus_camp', $a->getKey()]))
            ->assertOk()->getContent();

        $blok = preg_match('/<ul class="rin-ganda-daftar">(.*?)<\/ul>/s', $isi, $cocok)
            ? $cocok[1] : '';

        $this->assertNotSame('', $blok, 'Daftar gandanya tidak tergambar.');

        $this->assertStringNotContainsString('Scopus Camp', $blok,
            'Nama layanan yang sama diulang di tiap keping, dan itu yang '
            . 'mendesak status serta tanggalnya keluar.');

        $this->assertStringContainsString('Menunggu bayar', $blok,
            'Statusnya justru hilang; yang dibuang seharusnya nama layanannya.');
    }

    // ------------------------------------------------------------- pembantu

    private function ganda(PendaftaranScopusCamp $a)
    {
        Pendaftaran::lupakan();

        return PendaftaranGanda::lain('scopus_camp', (string) $a->getKey(), $a->email, $a->telp);
    }

    /** @return array{0: User, 1: PendaftaranScopusCamp, 2: string} */
    private function dengan(array $ubah = []): array
    {
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $t = Str::random(8);

        $u = User::create([
            'full_name' => 'Panitia Uji', 'username' => 'gnd_' . $t,
            'email' => 'p' . $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR])->save();

        return [$u, $this->camp($t, $ubah), $t];
    }

    private function camp(string $t, array $ubah = []): PendaftaranScopusCamp
    {
        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Uji ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        return PendaftaranScopusCamp::create(array_merge([
            'id_transaksi' => 'T-' . Str::random(8), 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-2233-0001', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '5500000', 'status' => 'diproses',
        ], $ubah));
    }

    private function bibliometrik(string $t, array $ubah = []): AnalisisBibliometrik
    {
        $angkatan = KategoriLayanan::create([
            'layanan' => 'bibliometrik', 'nama' => 'Angkatan Bib ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ]);

        return AnalisisBibliometrik::create(array_merge([
            'id_transaksi' => 'B-' . Str::random(8), 'kategori_id' => $angkatan->id,
            'nama' => 'Peserta ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0899-0000-1111', 'jumlah_pendaftar' => '1',
            'total_pembayaran' => '3500000', 'status' => 'diproses',
        ], $ubah));
    }
    /**
     * Keadaan tiap pendaftaran lain jadi LENCANA berwarna.
     *
     * Yang dicari panitia di daftar ini cuma satu: mana di antara
     * pendaftaran lainnya yang juga belum lunas. Sebagai teks sewarna
     * tanggalnya — "Menunggu bayar · 12 Okt 2026" — keenamnya harus dibaca
     * satu per satu; sebagai lencana, keenamnya terbaca sekali lihat.
     */
    #[Test]
    public function keadaan_pendaftaran_lain_jadi_lencana(): void
    {
        $blok = preg_match('/<ul class="rin-ganda-daftar">(.*?)<\/ul>/s', $this->gaya(), $cocok)
            ? $cocok[1] : '';

        $this->assertNotSame('', $blok, 'Daftar pendaftaran lain tidak ketemu.');

        $this->assertMatchesRegularExpression(
            '/<span class="mis-pil mis-pil-\{\{ \$rupaLain\[.warna.\] \}\}">/', $blok,
            'Keadaannya kembali jadi teks biasa; mana yang belum lunas harus '
            . 'dibaca satu per satu.'
        );

        /*
         * Kalimat utuhnya TETAP di title.
         *
         * Lencananya memisah status dari tanggal dan nama layanannya; yang
         * membaca lewat pembaca layar, atau yang menahan kursor di atasnya,
         * tetap mendapat kalimat lengkapnya.
         */
        $this->assertStringContainsString('title="{{ $ketLain }}"', $blok,
            'Kalimat utuhnya hilang dari title saat keadaannya dipecah jadi '
            . 'lencana.');
    }

    /**
     * Medali layanannya SEJAJAR dengan nomor, dan offsetnya hanya di ponsel.
     *
     * Keping ini dua tingkat — nomor lalu keterangannya — jadi
     * align-items: center menaruh medalinya di antara keduanya: terukur di
     * 390px medali mulai 25px dari atas keping sementara nomornya 8px, dan
     * yang terlihat ikon yang mengambang sendirian di kiri.
     *
     * Offsetnya 9px karena baris nomornya dibuat 40px sebagai sasaran ketuk
     * — dan itu HANYA berlaku di ponsel. Dipasang juga di layar lebar,
     * barisnya tinggal 17px dan medalinya justru turun 11,5px di bawah titik
     * tengah nomornya; terukur di 1440px pada versi pertama perbaikan ini.
     */
    #[Test]
    public function medali_sejajar_nomor_dan_offsetnya_hanya_di_ponsel(): void
    {
        $isi = $this->gaya();

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar > li\s*\{[^}]*align-items:\s*flex-start/s', $isi,
            'Kepingnya kembali memusatkan isinya, jadi medalinya mengambang di '
            . 'antara nomor dan keterangannya.'
        );

        /*
         * Offsetnya dicari DI DALAM blok ponselnya, bukan di seluruh berkas:
         * yang hendak dijaga justru bahwa ia tidak bocor ke layar lebar.
         */
        $this->assertSame(1, preg_match('/@media \(max-width: 575\.98px\) \{(.*)/s', $isi, $blok),
            'Blok gaya ponsel layar rincian tidak ada.');

        $this->assertMatchesRegularExpression(
            '/\.rin-ganda-daftar \.mis-medali\s*\{[^}]*margin-top:\s*9px/s', $blok[1],
            'Offset medalinya tidak ada di blok ponsel; di sana baris nomornya '
            . '40px dan medalinya duduk 9px terlalu tinggi.'
        );

        /*
         * Blok ponselnya DIPOTONG dengan menghitung kurung, bukan dengan
         * memotong berkas di kemunculan pertamanya.
         *
         * Versi pertama penjaga ini memakai substr() sampai "@media
         * (max-width: 575.98px)" yang pertama — dan berkas ini punya
         * beberapa blok seperti itu, yang pertama jauh di atas. Aturan yang
         * bocor ke layar lebar di bawahnya tidak pernah ikut terbaca, jadi
         * penjaganya tetap hijau justru untuk cacat yang hendak dijaganya.
         * Dibuktikan: offsetnya dipasang kembali di luar media query dan
         * penjaganya diam saja.
         */
        $diLuarPonsel = $isi;

        while (($mulai = strpos($diLuarPonsel, '@media (max-width: 575.98px)')) !== false) {
            $kurung = strpos($diLuarPonsel, '{', $mulai);
            $dalam = 0;
            $akhir = $kurung;

            for ($i = $kurung; $i < strlen($diLuarPonsel); $i++) {
                if ($diLuarPonsel[$i] === '{') {
                    $dalam++;
                } elseif ($diLuarPonsel[$i] === '}') {
                    $dalam--;

                    if ($dalam === 0) {
                        $akhir = $i;
                        break;
                    }
                }
            }

            $diLuarPonsel = substr($diLuarPonsel, 0, $mulai) . substr($diLuarPonsel, $akhir + 1);
        }

        $this->assertDoesNotMatchRegularExpression(
            '/\.rin-ganda-daftar \.mis-medali\s*\{[^}]*margin-top:/s', $diLuarPonsel,
            'Offset medalinya bocor ke layar lebar, tempat baris nomornya cuma '
            . '17px — medalinya turun 11,5px di bawah titik tengah nomornya.'
        );
    }

}
