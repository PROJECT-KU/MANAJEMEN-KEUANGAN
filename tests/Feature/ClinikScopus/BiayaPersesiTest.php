<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tarif per sesi Clinik Scopus.
 *
 * Tabelnya menyimpan RIWAYAT tarif, bukan daftar pilihan: hanya satu baris
 * yang berlaku pada satu waktu. Sebelum ini tidak ada apa pun yang menjaga
 * aturan itu, dan pemakainya memilih dengan first() — yang berarti sembarang
 * satu di antaranya begitu ada lebih dari satu yang aktif.
 */
class BiayaPersesiTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_tar_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function tarif(int $nilai, ?int $ppn = null, string $status = ClinikScopusBiayaPersesi::NONAKTIF): ClinikScopusBiayaPersesi
    {
        return ClinikScopusBiayaPersesi::create([
            'biaya_persesi' => $nilai,
            'ppn' => $ppn,
            'status' => $status,
        ]);
    }

    // ------------------------------------------------- hanya satu yang berlaku

    #[Test]
    public function memberlakukan_satu_tarif_menonaktifkan_yang_lain(): void
    {
        $lama = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $baru = $this->tarif(150000, 11);

        $baru->jadikanBerlaku();

        $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $baru->refresh()->status);
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $lama->refresh()->status);
        $this->assertSame(1, ClinikScopusBiayaPersesi::aktif()->count());
    }

    #[Test]
    public function berlaku_mengembalikan_yang_paling_belakangan_disetel(): void
    {
        /*
         * Jaring pengaman. Kalau entah bagaimana ada dua yang aktif — data
         * lama, impor, atau tulisan langsung ke basis data — yang dipakai
         * harus yang paling belakangan disetel, bukan sembarang seperti
         * first() tanpa urutan.
         */
        $awal = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $awal->forceFill(['updated_at' => now()->subYear()])->save();

        $akhir = $this->tarif(175000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->assertTrue(ClinikScopusBiayaPersesi::berlaku()->is($akhir));
    }

    // ------------------------------------------------------------- menyetel

    #[Test]
    public function tarif_baru_langsung_berlaku(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $lama = $this->tarif(100000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'biaya_persesi' => '175.000',
                'ppn' => 11,
            ])
            ->assertRedirect(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $berlaku = ClinikScopusBiayaPersesi::berlaku();

        $this->assertSame(175000, (int) $berlaku->biaya_persesi);
        $this->assertFalse($berlaku->is($lama), 'Seharusnya baris BARU, bukan yang lama ditimpa.');
        $this->assertSame(ClinikScopusBiayaPersesi::NONAKTIF, $lama->refresh()->status);
    }

    #[Test]
    public function tarif_diketik_berformat_rupiah_tetap_tersimpan_sebagai_angka(): void
    {
        // Isiannya dipoles jadi "175.000" saat diketik; yang tersimpan harus
        // bilangan, bukan untaian bertitik.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'biaya_persesi' => 'Rp 1.250.000',
            'ppn' => null,
        ]);

        $this->assertSame(1250000, (int) ClinikScopusBiayaPersesi::berlaku()->biaya_persesi);
    }

    #[Test]
    public function memperbaiki_tarif_yang_berlaku_tidak_menambah_baris_riwayat(): void
    {
        /*
         * Membetulkan salah ketik tidak boleh meninggalkan jejak seolah
         * harganya pernah berubah — riwayat tarif dibaca orang untuk tahu
         * kapan harga naik.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $berlaku = $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);
        $sebelum = ClinikScopusBiayaPersesi::count();

        $this->actingAs($admin)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'biaya_persesi' => '125.000',
            'ppn' => 12,
            'perbaiki' => $berlaku->getKey(),
        ]);

        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::count());
        $this->assertSame(12, $berlaku->refresh()->ppn_persen);
    }

    #[Test]
    public function tarif_nol_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), ['biaya_persesi' => '0'])
            ->assertSessionHasErrors('biaya_persesi');
    }

    #[Test]
    public function ppn_di_atas_seratus_persen_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
                'biaya_persesi' => '100.000',
                'ppn' => 150,
            ])
            ->assertSessionHasErrors('ppn');
    }

    // ------------------------------------------------------------------ hak

    #[Test]
    public function karyawan_boleh_melihat_tetapi_tidak_disuguhi_borang(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $halaman = $this->actingAs($karyawan)->get(route('account.Clinik-Scopus-Biaya-Persesi.index'));

        $halaman->assertOk();
        $halaman->assertSee('Hanya administrator yang boleh mengubah tarif.');
        $this->assertStringNotContainsString('type="submit"', $halaman->getContent());
    }

    #[Test]
    public function karyawan_tidak_boleh_menyetel_tarif(): void
    {
        $karyawan = $this->akun(User::PERAN_KARYAWAN);
        $sebelum = ClinikScopusBiayaPersesi::count();

        $this->actingAs($karyawan)->post(route('account.Clinik-Scopus-Biaya-Persesi.simpan'), [
            'biaya_persesi' => '999.000',
        ]);

        $this->assertSame($sebelum, ClinikScopusBiayaPersesi::count());
    }

    // ------------------------------------------------------------ penghapusan

    #[Test]
    public function tarif_yang_sedang_berlaku_tidak_bisa_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $berlaku = $this->tarif(125000, 11, ClinikScopusBiayaPersesi::AKTIF);

        $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $berlaku))
            ->assertStatus(409)
            ->assertJson(['success' => false]);

        $this->assertNotNull(ClinikScopusBiayaPersesi::find($berlaku->getKey()));
    }

    #[Test]
    public function tarif_yang_masih_jadi_acuan_sesi_tidak_bisa_dihapus(): void
    {
        /*
         * clinikscopus.biaya_persesi_id berkunci asing ON DELETE NO ACTION,
         * jadi tanpa pemeriksaan ini MySQL menolak dengan galat 1451 yang
         * hanya menyebut nama constraint-nya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = $this->tarif(100000, 11);

        $sesi = DB::table('clinikscopus')->first();

        if (! $sesi) {
            $this->markTestSkipped('Tidak ada baris clinikscopus untuk diuji.');
        }

        DB::table('clinikscopus')->where('id', $sesi->id)->update(['biaya_persesi_id' => $tarif->getKey()]);

        $jawab = $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif));

        $jawab->assertStatus(409);
        $this->assertStringContainsString('sesi', $jawab->json('message'));
        $this->assertNotNull(ClinikScopusBiayaPersesi::find($tarif->getKey()));
    }

    #[Test]
    public function tarif_lama_yang_belum_dipakai_boleh_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = $this->tarif(90000, 11);

        $this->actingAs($admin)
            ->deleteJson(route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(ClinikScopusBiayaPersesi::find($tarif->getKey()));
    }

    // -------------------------------------------------------- pemakai tarifnya

    #[Test]
    public function ppn_publik_datang_dari_tarif_yang_berlaku_bukan_sembarang(): void
    {
        /*
         * cekPpn() dulu memanggil first() TANPA menyaring status. Begitu
         * tarifnya pernah diganti sekali saja, PPN yang ditagihkan ke
         * pelanggan bisa datang dari harga yang sudah tidak dipakai.
         */
        ClinikScopusBiayaPersesi::query()->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $usang = $this->tarif(100000, 5);
        $usang->forceFill(['created_at' => now()->subYears(2), 'updated_at' => now()->subYears(2)])->save();

        $sekarang = $this->tarif(150000, 11);
        $sekarang->jadikanBerlaku();

        $this->assertSame(11, ClinikScopusBiayaPersesi::berlaku()->ppn_persen);
    }

    #[Test]
    public function ppn_kosong_berarti_tanpa_ppn(): void
    {
        // Kolomnya varchar dan boleh NULL; pada data yang ada nilainya memang
        // NULL, yang berarti "tidak dikenakan" — bukan nol yang disetel.
        $tarif = $this->tarif(125000, null, ClinikScopusBiayaPersesi::AKTIF);

        $this->assertSame(0, $tarif->ppn_persen);
    }

    #[Test]
    public function rute_memakai_uuid_bukan_nomor_berurut(): void
    {
        // Kunci utamanya UUID, jadi alamat satu tarif tidak bisa ditebak
        // dengan menambah satu seperti nomor berurut.
        $tarif = $this->tarif(125000, 11);
        $alamat = route('account.Clinik-Scopus-Biaya-Persesi.destroy', $tarif);

        $this->assertMatchesRegularExpression(
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $alamat
        );
    }
}
