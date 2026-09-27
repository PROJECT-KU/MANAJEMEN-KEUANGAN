<?php

namespace Tests\Feature\Auth;

use App\Livewire\Akun\PengaturanPin;
use App\Livewire\Auth\AturUlangPassword;
use App\Livewire\Auth\Daftar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Livewire mengirim seluruh properti publik kembali ke peramban dan
 * menyimpannya di wire:snapshot. Kata sandi dan PIN tidak boleh tertinggal
 * di sana setelah kiriman gagal.
 */
class RahasiaTidakTertinggalTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Rahasia',
            'username' => 'uji_rahasia_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public function test_pendaftaran_gagal_tidak_menyisakan_kata_sandi(): void
    {
        $uji = Livewire::test(Daftar::class);
        $this->travel(6)->seconds();

        $uji->set('namaLengkap', 'Uji')
            ->set('username', 'uji_sisa_' . uniqid())
            ->set('email', uniqid() . '@contoh.test')
            ->set('kataSandi', 'SandiRahasiaUji2026')
            ->set('kataSandiKonfirmasi', 'tidaksama')
            ->set('setuju', true)
            ->call('daftar')
            ->assertHasErrors()
            ->assertSet('kataSandi', '')
            ->assertSet('kataSandiKonfirmasi', '');
    }

    public function test_atur_ulang_gagal_tidak_menyisakan_kata_sandi(): void
    {
        $pengguna = $this->buatPengguna();
        $token = Password::broker()->createToken($pengguna);

        Testable::create(AturUlangPassword::class, ['token' => $token], ['email' => $pengguna->email])
            ->set('kataSandi', 'SandiRahasiaUji2026')
            ->set('kataSandiKonfirmasi', 'tidaksama')
            ->call('simpan')
            ->assertHasErrors()
            ->assertSet('kataSandi', '')
            ->assertSet('kataSandiKonfirmasi', '');
    }

    public function test_atur_pin_gagal_tidak_menyisakan_kata_sandi_dan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '111111')
            ->set('pinKonfirmasi', '111111')
            ->call('simpan')
            ->assertHasErrors('pin')
            ->assertSet('kataSandi', '')
            ->assertSet('pin', '')
            ->assertSet('pinKonfirmasi', '');
    }

    public function test_kata_sandi_salah_di_pengaturan_pin_ikut_dibersihkan(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        \Illuminate\Support\Facades\RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasErrors('kataSandi')
            ->assertSet('kataSandi', '')
            ->assertSet('pin', '');
    }
}
