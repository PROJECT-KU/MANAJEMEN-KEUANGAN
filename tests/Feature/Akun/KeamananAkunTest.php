<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\Livewire\Akun\KeamananAkun;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
            ->assertHasNoErrors();

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
            ->get(route('account.profil.show', $pengguna->getKey()))
            ->assertOk()
            ->assertSee('Keamanan')
            ->assertSeeLivewire(KeamananAkun::class);
    }
}
