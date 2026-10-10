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
}
