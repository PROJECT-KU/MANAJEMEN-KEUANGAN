<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\Livewire\Akun\KeamananAkun;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class KeamananAkunTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Keamanan',
            'username' => 'uji_keamanan_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    /**
     * Sesi tiruan milik pengguna, dengan keaktifan yang bisa diatur.
     *
     * daftarSesi() hanya membaca tabel sessions kalau driver sesinya memang
     * 'database'; di lingkungan uji driver-nya array, jadi tanpa baris ini
     * daftarnya selalu kosong dan ujinya lulus tanpa menguji apa pun.
     */
    private function buatSesi(User $pengguna, string $peramban, int $menitLalu): string
    {
        config(['session.driver' => 'database']);

        $id = 'uji' . uniqid();

        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $pengguna->getKey(),
            'ip_address' => '114.10.22.9',
            'user_agent' => $peramban,
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subMinutes($menitLalu)->getTimestamp(),
        ]);

        return $id;
    }

    /**
     * Daftar perangkat dilipat setelah tiga baris. Tanpa itu, akun yang
     * dipakai di banyak perangkat menampilkan sampai 20 baris berjajar dan
     * mengubur riwayat keamanan di bawahnya.
     */
    public function test_daftar_perangkat_dilipat_sesudah_tiga(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        for ($i = 0; $i < 6; $i++) {
            $this->buatSesi($pengguna, 'Peramban uji ke-' . $i, $i + 1);
        }

        $uji = Livewire::test(KeamananAkun::class);

        $this->assertCount(3, $uji->viewData('sesiTampil'));
        $this->assertSame(3, $uji->viewData('sisaPerangkat'));

        // $toggle tidak bisa dipanggil lewat ->call() di harness Livewire;
        // yang diuji tetap cabang tampilan yang sama.
        $uji->set('semuaPerangkat', true);

        $this->assertCount(6, $uji->viewData('sesiTampil'));
    }

    /**
     * Perangkat yang sedang dipakai selalu di urutan pertama — justru baris
     * itulah yang TIDAK boleh diakhiri, jadi ia tidak boleh tersembunyi di
     * balik lipatan. Urutan menurut keaktifan saja tidak menjaminnya.
     */
    public function test_perangkat_yang_sedang_dipakai_selalu_di_atas(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        // Sesi ini paling lama tidak aktif, tetapi inilah yang sedang dipakai.
        $this->buatSesi($pengguna, 'Peramban saya', 90);
        DB::table('sessions')->where('user_agent', 'Peramban saya')
            ->update(['id' => session()->getId()]);

        for ($i = 0; $i < 4; $i++) {
            $this->buatSesi($pengguna, 'Peramban lain ke-' . $i, $i);
        }

        $tampil = Livewire::test(KeamananAkun::class)->viewData('sesiTampil');

        $this->assertTrue($tampil->first()->ini, 'Perangkat ini harus jadi baris pertama.');
    }

    public function test_pengguna_melihat_riwayat_masuknya_sendiri(): void
    {
        $pengguna = $this->buatPengguna();
        $lain = $this->buatPengguna();

        AktivitasMasuk::create([
            'user_id' => $pengguna->getKey(),
            'identitas' => $pengguna->username,
            'berhasil' => false,
            'alasan' => 'kata sandi salah',
            'ip' => '203.0.113.7',
        ]);

        AktivitasMasuk::create([
            'user_id' => $lain->getKey(),
            'identitas' => $lain->username,
            'berhasil' => false,
            'alasan' => 'kata sandi salah',
            'ip' => '198.51.100.9',
        ]);

        $this->actingAs($pengguna);

        Livewire::test(KeamananAkun::class)
            ->assertSee('203.0.113.7')
            ->assertDontSee('198.51.100.9');
    }

    public function test_bisa_keluar_dari_perangkat_lain_dengan_kata_sandi_benar(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', self::SANDI)
            ->call('keluarkanPerangkatLain')
            ->assertHasNoErrors()
            // Pemberitahuannya lewat toast bersama, bukan kotak .alert yang
            // ikut tergambar ulang setiap komponen menyegarkan dirinya.
            ->assertDispatched('toast', jenis: 'berhasil');

        $this->assertDatabaseHas('aktivitas_masuk', [
            'user_id' => $pengguna->getKey(),
            'alasan' => 'keluar dari perangkat lain',
        ]);

        // Sesi ini harus tetap hidup.
        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('keluarkan-perangkat|' . $pengguna->getKey());

        Livewire::test(KeamananAkun::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->call('keluarkanPerangkatLain')
            ->assertHasErrors('kataSandi');

        $this->assertDatabaseMissing('aktivitas_masuk', [
            'user_id' => $pengguna->getKey(),
            'alasan' => 'keluar dari perangkat lain',
        ]);
    }

    public function test_tab_keamanan_tampil_di_halaman_profil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk()
            ->assertSee('Keamanan')
            ->assertSeeLivewire(KeamananAkun::class);
    }
}
