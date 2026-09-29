<?php

namespace Tests\Feature\Akun;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar Data Pelanggan.
 *
 * Yang dijaga di sini: siapa boleh melihat, siapa boleh menghapus, dan bahwa
 * alamatnya memakai uuid — bukan id berurut yang bisa ditebak.
 */
class DataPelangganTest extends TestCase
{
    use DatabaseTransactions;

    private function buat(string $peran): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Pelanggan',
            'username' => 'uji_pel_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'peran' => $peran,
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    #[Test]
    public function tautannya_memakai_uuid_bukan_id(): void
    {
        $pelanggan = $this->buat('user');

        $tautan = route('account.customer.edit', $pelanggan);

        $this->assertStringContainsString($pelanggan->uuid, $tautan);
        // id berurut tidak boleh ikut muncul: satu tautan cukup untuk menebak
        // tautan pelanggan lain hanya dengan menambah satu.
        $this->assertStringNotContainsString('/' . $pelanggan->getKey(), $tautan);
    }

    #[Test]
    public function pelanggan_tidak_boleh_melihat_daftar_pelanggan(): void
    {
        $this->actingAs($this->buat('user'))
            ->get(route('account.customer.index'))
            ->assertRedirect(route('account.dashboard.index'));
    }

    #[Test]
    public function karyawan_boleh_melihat_daftarnya(): void
    {
        $this->buat('user');

        $this->actingAs($this->buat('karyawan'))
            ->get(route('account.customer.index'))
            ->assertOk()
            ->assertSee('Data Pelanggan');
    }

    #[Test]
    public function daftarnya_hanya_memuat_yang_berperan_pelanggan(): void
    {
        $pelanggan = $this->buat('user');
        $karyawan = $this->buat('karyawan');

        $jawaban = $this->actingAs($this->buat('administrator'))
            ->get(route('account.customer.index', ['cari' => 'uji_pel_']));

        $jawaban->assertOk()
            ->assertSee($pelanggan->username)
            ->assertDontSee($karyawan->username);
    }

    #[Test]
    public function halaman_satu_orang_menolak_yang_bukan_pelanggan(): void
    {
        $karyawan = $this->buat('karyawan');

        $this->actingAs($this->buat('administrator'))
            ->get(route('account.customer.edit', $karyawan))
            ->assertNotFound();
    }

    #[Test]
    public function karyawan_tidak_boleh_menghapus_pelanggan(): void
    {
        $pelanggan = $this->buat('user');

        $this->actingAs($this->buat('karyawan'))
            ->deleteJson(route('account.customer.destroy', $pelanggan))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $pelanggan->getKey()]);
    }

    /* Dipisah dari uji penolakan di atas: dua actingAs berturut-turut dalam
       satu uji membuat permintaan JSON kedua terbaca belum masuk (401). */
    #[Test]
    public function administrator_boleh_menghapus_pelanggan(): void
    {
        $pelanggan = $this->buat('user');

        $this->actingAs($this->buat('administrator'))
            ->deleteJson(route('account.customer.destroy', $pelanggan))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('users', ['id' => $pelanggan->getKey()]);
    }

    #[Test]
    public function penyaring_status_dan_verifikasi_bekerja(): void
    {
        $aktif = $this->buat('user');
        $mati = $this->buat('user');
        $mati->forceFill(['status' => 'non active', 'email_verified_at' => null])->save();

        $admin = $this->buat('administrator');

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['status' => 'aktif', 'cari' => 'uji_pel_']))
            ->assertSee($aktif->username)
            ->assertDontSee($mati->username);

        $this->actingAs($admin)
            ->get(route('account.customer.index', ['verifikasi' => 'belum', 'cari' => 'uji_pel_']))
            ->assertSee($mati->username)
            ->assertDontSee($aktif->username);
    }
}
