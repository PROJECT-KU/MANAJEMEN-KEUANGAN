<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Masuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class MasukTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(array $ubah = []): User
    {
        $status = $ubah['status'] ?? 'active';
        unset($ubah['status']);

        $pengguna = User::create(array_merge([
            'full_name' => 'Uji Masuk',
            'username' => 'uji_masuk_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji123'),
            'level' => 'karyawan',
        ], $ubah));

        // Kolom status tidak ada di $fillable model User, jadi harus diisi
        // terpisah (bukan lewat mass assignment).
        $pengguna->forceFill(['status' => $status])->save();

        return $pengguna->refresh();
    }

    public function test_halaman_masuk_bisa_dibuka_tamu(): void
    {
        $this->get('/login')->assertOk()->assertSeeLivewire(Masuk::class);
    }

    public function test_bisa_masuk_dengan_username(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'RahasiaUji123')
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_bisa_masuk_dengan_email(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->email)
            ->set('kataSandi', 'RahasiaUji123')
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'salah-sekali')
            ->call('masuk')
            ->assertHasErrors('identitas');

        $this->assertGuest();
    }

    public function test_akun_nonactive_tidak_bisa_masuk(): void
    {
        $pengguna = $this->buatPengguna(['status' => 'nonactive']);

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'RahasiaUji123')
            ->call('masuk')
            ->assertHasErrors('identitas');

        $this->assertGuest();
    }

    public function test_percobaan_berlebihan_dikunci_sementara(): void
    {
        $pengguna = $this->buatPengguna();

        $uji = Livewire::test(Masuk::class)->set('identitas', $pengguna->username);

        for ($i = 0; $i < 5; $i++) {
            $uji->set('kataSandi', 'salah')->call('masuk');
        }

        // Percobaan ke-6 ditolak pembatas meski kata sandinya benar.
        $uji->set('kataSandi', 'RahasiaUji123')->call('masuk')->assertHasErrors('identitas');

        $this->assertGuest();
    }

    public function test_isian_kosong_wajib_diisi(): void
    {
        Livewire::test(Masuk::class)
            ->call('masuk')
            ->assertHasErrors(['identitas' => 'required', 'kataSandi' => 'required']);
    }
}
