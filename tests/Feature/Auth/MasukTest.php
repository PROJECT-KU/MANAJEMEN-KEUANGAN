<?php

namespace Tests\Feature\Auth;

use App\AktivitasMasuk;
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
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'karyawan',
        ], $ubah));

        // Kolom status tidak ada di $fillable model User, jadi harus diisi
        // terpisah (bukan lewat mass assignment). Emailnya ditandai
        // terverifikasi supaya middleware verifikasi tidak ikut diuji di sini.
        $pengguna->forceFill(['status' => $status, 'email_verified_at' => now()])->save();

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
            ->set('kataSandi', 'RahasiaUji2026')
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_bisa_masuk_dengan_email(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->email)
            ->set('kataSandi', 'RahasiaUji2026')
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
            ->set('kataSandi', 'RahasiaUji2026')
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
        $uji->set('kataSandi', 'RahasiaUji2026')->call('masuk')->assertHasErrors('identitas');

        $this->assertGuest();
    }

    public function test_isian_kosong_wajib_diisi(): void
    {
        Livewire::test(Masuk::class)
            ->call('masuk')
            ->assertHasErrors(['identitas' => 'required', 'kataSandi' => 'required']);
    }

    public function test_percobaan_masuk_tercatat(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'salah-sekali')
            ->call('masuk');

        $gagal = AktivitasMasuk::where('user_id', $pengguna->id)->latest('id')->first();
        $this->assertNotNull($gagal);
        $this->assertFalse($gagal->berhasil);
        $this->assertSame('kata sandi salah', $gagal->alasan);

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'RahasiaUji2026')
            ->call('masuk');

        $berhasil = AktivitasMasuk::where('user_id', $pengguna->id)->latest('id')->first();
        $this->assertTrue($berhasil->berhasil);
        $this->assertNotNull($berhasil->ip);
    }

    public function test_sesi_perangkat_lain_berakhir_saat_kata_sandi_diubah(): void
    {
        $pengguna = $this->buatPengguna();

        Livewire::test(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', 'RahasiaUji2026')
            ->call('masuk');

        // Sesi ini masih sah selama kata sandinya belum berubah.
        $this->get('/account/dashboard')->assertOk();

        // Kata sandi diganti dari tempat lain (mis. lewat tautan atur ulang).
        $pengguna->forceFill(['password' => Hash::make('SandiLain2026')])->save();

        // Dalam satu proses uji, penjaga auth menyimpan objek user di memori;
        // dilupakan dulu supaya permintaan berikutnya mengambil data terbaru
        // seperti permintaan sungguhan dari perangkat lain.
        $this->app['auth']->forgetGuards();

        // Middleware AuthenticateSession mengikat sesi ke hash kata sandi,
        // jadi sesi lama harus ikut berakhir.
        $this->get('/account/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
