<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\AturUlangPassword;
use App\Livewire\Auth\LupaPassword;
use App\Mail\KodeResetPasswordMail;
use App\Mail\PasswordResetSuccessMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ResetKataSandiTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(): User
    {
        return User::create([
            'full_name' => 'Uji Reset',
            'username' => 'ujireset' . substr(uniqid(), -6),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('SandiLama123'),
            'level' => 'karyawan',
        ]);
    }

    /** Menyiapkan kode reset seperti yang dilakukan komponen LupaPassword. */
    private function simpanKode(string $email, string $kode, ?Carbon $dibuat = null): void
    {
        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($kode),
            'created_at' => $dibuat ?? Carbon::now(),
        ]);
    }

    public function test_halaman_lupa_kata_sandi_bisa_dibuka_tamu(): void
    {
        // Sebelumnya rute ini berada di grup 'auth' sehingga tamu ditolak.
        $this->get('/lupa-password')->assertOk()->assertSeeLivewire(LupaPassword::class);
        $this->get('/atur-ulang-password')->assertOk()->assertSeeLivewire(AturUlangPassword::class);
    }

    public function test_kode_dikirim_dan_disimpan_dalam_bentuk_hash(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimKode')
            ->assertRedirect(route('password.atur-ulang', ['email' => $pengguna->email]));

        $baris = DB::table('password_resets')->where('email', $pengguna->email)->first();

        $this->assertNotNull($baris);
        $this->assertNotEmpty($baris->token);

        $kode = null;
        Mail::assertSent(KodeResetPasswordMail::class, function ($surat) use ($pengguna, &$kode) {
            $kode = $surat->kode;

            return $surat->hasTo($pengguna->email);
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', $kode);
        // Kode tidak boleh tersimpan apa adanya di basis data.
        $this->assertNotSame($kode, $baris->token);
        $this->assertTrue(Hash::check($kode, $baris->token));
    }

    public function test_email_asing_tidak_membocorkan_keberadaan_akun(): void
    {
        Mail::fake();

        Livewire::test(LupaPassword::class)
            ->set('email', 'tidak-terdaftar-' . uniqid() . '@contoh.test')
            ->call('kirimKode')
            ->assertHasNoErrors()
            ->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_kode_benar_mengganti_kata_sandi(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $this->simpanKode($pengguna->email, '123456');

        Livewire::test(AturUlangPassword::class)
            ->set('email', $pengguna->email)
            ->set('kode', '123456')
            ->set('kataSandi', 'SandiBaru123')
            ->set('kataSandiKonfirmasi', 'SandiBaru123')
            ->call('simpan')
            ->assertRedirect(route('login'));

        $pengguna->refresh();

        $this->assertTrue(Hash::check('SandiBaru123', $pengguna->password));
        // Kode sekali pakai: barisnya harus hilang setelah dipakai.
        $this->assertNull(DB::table('password_resets')->where('email', $pengguna->email)->first());
        Mail::assertSent(PasswordResetSuccessMail::class);
    }

    public function test_kode_salah_ditolak(): void
    {
        $pengguna = $this->buatPengguna();
        $this->simpanKode($pengguna->email, '123456');

        Livewire::test(AturUlangPassword::class)
            ->set('email', $pengguna->email)
            ->set('kode', '999999')
            ->set('kataSandi', 'SandiBaru123')
            ->set('kataSandiKonfirmasi', 'SandiBaru123')
            ->call('simpan')
            ->assertHasErrors('kode');

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $pengguna->password));
    }

    public function test_kode_kedaluwarsa_ditolak(): void
    {
        $pengguna = $this->buatPengguna();
        $menit = (int) config('auth.passwords.users.expire', 60);
        $this->simpanKode($pengguna->email, '123456', Carbon::now()->subMinutes($menit + 5));

        Livewire::test(AturUlangPassword::class)
            ->set('email', $pengguna->email)
            ->set('kode', '123456')
            ->set('kataSandi', 'SandiBaru123')
            ->set('kataSandiKonfirmasi', 'SandiBaru123')
            ->call('simpan')
            ->assertHasErrors('kode');

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $pengguna->password));
    }

    public function test_tanpa_kode_tidak_bisa_mengganti_kata_sandi_orang_lain(): void
    {
        // Ini yang dulu bisa terjadi: cukup tahu email, kata sandi langsung diganti.
        $korban = $this->buatPengguna();

        Livewire::test(AturUlangPassword::class)
            ->set('email', $korban->email)
            ->set('kode', '000000')
            ->set('kataSandi', 'DibajakOrang1')
            ->set('kataSandiKonfirmasi', 'DibajakOrang1')
            ->call('simpan')
            ->assertHasErrors('kode');

        $korban->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $korban->password));
    }

    public function test_percobaan_kode_dibatasi(): void
    {
        $pengguna = $this->buatPengguna();
        $this->simpanKode($pengguna->email, '123456');

        $uji = Livewire::test(AturUlangPassword::class)
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiBaru123')
            ->set('kataSandiKonfirmasi', 'SandiBaru123');

        for ($i = 0; $i < 5; $i++) {
            $uji->set('kode', '000000')->call('simpan');
        }

        // Percobaan berikutnya ditolak pembatas walau kodenya benar.
        $uji->set('kode', '123456')->call('simpan')->assertHasErrors('kode');

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $pengguna->password));
    }

    public function test_permintaan_kode_dibatasi(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();

        for ($i = 0; $i < 3; $i++) {
            Livewire::test(LupaPassword::class)->set('email', $pengguna->email)->call('kirimKode');
        }

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimKode')
            ->assertHasErrors('email');

        Mail::assertSent(KodeResetPasswordMail::class, 3);
    }
}
