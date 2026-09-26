<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Daftar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DaftarTest extends TestCase
{
    use DatabaseTransactions;

    private function isianSah(array $ubah = []): array
    {
        $unik = uniqid();

        return array_merge([
            'namaLengkap' => 'Pengguna Uji',
            'username' => 'ujidaftar' . substr($unik, -6),
            'email' => $unik . '@contoh.test',
            'telp' => '081234567890',
            'kataSandi' => 'RahasiaUji123',
            'kataSandiKonfirmasi' => 'RahasiaUji123',
            'setuju' => true,
        ], $ubah);
    }

    public function test_halaman_daftar_bisa_dibuka_tamu(): void
    {
        $this->get('/register')->assertOk()->assertSeeLivewire(Daftar::class);
    }

    public function test_akun_baru_tersimpan(): void
    {
        $isian = $this->isianSah();

        Livewire::test(Daftar::class)
            ->set($isian)
            ->call('daftar')
            ->assertRedirect(route('login'));

        $pengguna = User::where('username', $isian['username'])->first();

        $this->assertNotNull($pengguna);
        $this->assertSame($isian['email'], $pengguna->email);
        $this->assertTrue(Hash::check('RahasiaUji123', $pengguna->password));
        // Kata sandi tidak boleh tersimpan apa adanya.
        $this->assertNotSame('RahasiaUji123', $pengguna->password);
    }

    public function test_username_dan_email_tidak_boleh_kembar(): void
    {
        $adaDulu = User::create([
            'full_name' => 'Sudah Ada',
            'username' => 'ujikembar' . substr(uniqid(), -6),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji123'),
        ]);

        Livewire::test(Daftar::class)
            ->set($this->isianSah(['username' => $adaDulu->username, 'email' => $adaDulu->email]))
            ->call('daftar')
            ->assertHasErrors(['username' => 'unique', 'email' => 'unique']);
    }

    public function test_konfirmasi_kata_sandi_harus_cocok(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['kataSandiKonfirmasi' => 'BedaSekali123']))
            ->call('daftar')
            ->assertHasErrors(['kataSandi' => 'same']);
    }

    public function test_kata_sandi_minimal_delapan_karakter(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['kataSandi' => 'pendek', 'kataSandiKonfirmasi' => 'pendek']))
            ->call('daftar')
            ->assertHasErrors(['kataSandi' => 'min']);
    }

    public function test_harus_menyetujui_ketentuan(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['setuju' => false]))
            ->call('daftar')
            ->assertHasErrors(['setuju' => 'accepted']);
    }

    public function test_username_tidak_boleh_berisi_spasi(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['username' => 'ada spasi']))
            ->call('daftar')
            ->assertHasErrors(['username' => 'alpha_dash']);
    }
}
