<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Masuk;
use App\Mail\MasukPerangkatBaruMail;
use App\Mail\PemberitahuanPinMail;
use App\Mail\TautanMatikanPinMail;
use App\Support\IngatanMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

/**
 * PIN adalah kredensial kedua, jadi ia harus ikut mati setiap kali kredensial
 * utama diganti — dan pemiliknya harus bisa mematikannya dari jauh.
 */
class PinKeamananTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private const PIN = '482913';

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji PIN Keamanan',
            'username' => 'uji_pinkeamanan_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();
        $pengguna->refresh()->aturPin(self::PIN);

        return $pengguna->refresh();
    }

    public function test_atur_ulang_kata_sandi_ikut_mematikan_pin(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $token = Password::broker()->createToken($pengguna);

        Testable::create(\App\Livewire\Auth\AturUlangPassword::class, ['token' => $token], ['email' => $pengguna->email])
            ->set('kataSandi', 'SandiBaruUji2026')
            ->set('kataSandiKonfirmasi', 'SandiBaruUji2026')
            ->call('simpan')
            ->assertRedirect(route('login'));

        $pengguna->refresh();

        $this->assertFalse($pengguna->pinAktif(), 'PIN harus mati setelah kata sandi diatur ulang.');
        $this->assertNull($pengguna->pin);

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'dinonaktifkan');
    }

    public function test_ganti_kata_sandi_di_profil_ikut_mematikan_pin(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.reset.password'), [
                'old_password' => self::SANDI,
                'password' => 'SandiBaruUji2026',
                'password_confirmation' => 'SandiBaruUji2026',
            ])
            ->assertOk();

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_tautan_dari_email_bisa_mematikan_pin(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();

        $tautan = URL::temporarySignedRoute('pin.matikan', now()->addMinutes(30), [
            'id' => $pengguna->getKey(),
            'hash' => sha1($pengguna->email),
        ]);

        $this->get($tautan)->assertRedirect(route('login'));

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_tautan_matikan_pin_tanpa_tanda_tangan_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        $this->get('/matikan-pin/' . $pengguna->getKey() . '/' . sha1($pengguna->email))
            ->assertForbidden();

        $this->assertTrue($pengguna->refresh()->pinAktif());
    }

    public function test_permintaan_matikan_pin_mengirim_tautan_ke_email_akun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();

        $kue = [IngatanMasuk::NAMA => json_encode([
            'identitas' => $pengguna->username,
            'mode' => 'pin',
            'ingat' => false,
            'uid' => $pengguna->getKey(),
        ])];

        Testable::create(Masuk::class, [], [], $kue)->call('mintaMatikanPin');

        Mail::assertSent(TautanMatikanPinMail::class, fn ($surat) => $surat->hasTo($pengguna->email));

        // PIN belum mati sebelum tautannya diklik.
        $this->assertTrue($pengguna->refresh()->pinAktif());
    }

    public function test_masuk_dari_perangkat_baru_memberi_tahu_pemilik(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();

        // Masuk pertama: belum ada riwayat, jadi belum dianggap perangkat baru.
        Testable::create(Masuk::class)
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->call('masuk');

        Mail::assertNothingSent();

        auth()->logout();
        $this->flushSession();

        // Masuk kedua dari penanda perangkat berbeda.
        Testable::create(Masuk::class, [], [], ['penanda_perangkat' => str_repeat('b', 32)])
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->call('masuk');

        Mail::assertSent(MasukPerangkatBaruMail::class, fn ($surat) => $surat->hasTo($pengguna->email));
    }
}
