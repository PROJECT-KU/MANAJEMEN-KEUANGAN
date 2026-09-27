<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\AturUlangPassword;
use App\Livewire\Auth\LupaPassword;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\TautanResetPasswordMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
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

    /** Minta tautan lalu ambil tokennya dari email yang terkirim. */
    private function mintaTautan(User $pengguna): string
    {
        $token = null;

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimTautan');

        Mail::assertSent(TautanResetPasswordMail::class, function ($surat) use (&$token) {
            preg_match('#/atur-ulang-password/([^?]+)#', $surat->tautan, $cocok);
            $token = $cocok[1] ?? null;

            return true;
        });

        $this->assertNotNull($token, 'Token tidak ditemukan pada tautan email.');

        return $token;
    }

    public function test_halaman_reset_bisa_dibuka_tamu(): void
    {
        // Sebelumnya rute ini berada di grup 'auth' sehingga tamu ditolak.
        $this->get('/lupa-password')->assertOk()->assertSeeLivewire(LupaPassword::class);
        $this->get('/atur-ulang-password')->assertOk()->assertSeeLivewire(AturUlangPassword::class);
    }

    public function test_tautan_dikirim_dan_token_disimpan_sebagai_hash(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimTautan')
            ->assertSet('terkirim', true)
            ->assertHasNoErrors();

        $baris = DB::table('password_resets')->where('email', $pengguna->email)->first();
        $this->assertNotNull($baris);

        $token = null;
        Mail::assertSent(TautanResetPasswordMail::class, function ($surat) use ($pengguna, &$token) {
            preg_match('#/atur-ulang-password/([^?]+)#', $surat->tautan, $cocok);
            $token = $cocok[1] ?? null;

            return $surat->hasTo($pengguna->email)
                && str_contains($surat->tautan, urlencode($pengguna->email));
        });

        $this->assertNotEmpty($token);
        // Token pada tautan tidak boleh tersimpan apa adanya di basis data.
        $this->assertNotSame($token, $baris->token);
        $this->assertTrue(Hash::check($token, $baris->token));
    }

    public function test_email_asing_tidak_membocorkan_keberadaan_akun(): void
    {
        Mail::fake();

        Livewire::test(LupaPassword::class)
            ->set('email', 'tidak-terdaftar-' . uniqid() . '@contoh.test')
            ->call('kirimTautan')
            ->assertSet('terkirim', true)
            ->assertHasNoErrors();

        Mail::assertNothingSent();
    }

    public function test_halaman_tanpa_token_menawarkan_minta_tautan_baru(): void
    {
        Livewire::test(AturUlangPassword::class)
            ->assertSet('token', '')
            ->assertSee('Tautan tidak lengkap');
    }

    public function test_email_terbaca_dari_tautan(): void
    {
        $pengguna = $this->buatPengguna();

        $this->withoutExceptionHandling()
            ->get('/atur-ulang-password/token-palsu?email=' . urlencode($pengguna->email))
            ->assertOk()
            ->assertSee($pengguna->email);
    }

    public function test_tautan_sah_mengganti_kata_sandi(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'SandiUji2026')
            ->call('simpan')
            ->assertRedirect(route('login'));

        $pengguna->refresh();

        $this->assertTrue(Hash::check('SandiUji2026', $pengguna->password));
        // Token sekali pakai: barisnya harus hilang setelah dipakai.
        $this->assertNull(DB::table('password_resets')->where('email', $pengguna->email)->first());
        Mail::assertSent(PasswordResetSuccessMail::class);
    }

    public function test_token_tidak_bisa_dipakai_dua_kali(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'SandiUji2026')
            ->call('simpan');

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiKetiga2026')
            ->set('kataSandiKonfirmasi', 'SandiKetiga2026')
            ->call('simpan')
            ->assertHasErrors('token');

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiUji2026', $pengguna->password));
    }

    public function test_token_palsu_ditolak(): void
    {
        // Inilah yang dulu bisa terjadi: cukup tahu email, kata sandi langsung
        // diganti tanpa token apa pun.
        $korban = $this->buatPengguna();

        Livewire::test(AturUlangPassword::class, ['token' => 'token-karangan-sendiri'])
            ->set('email', $korban->email)
            ->set('kataSandi', 'DibajakOrang2026')
            ->set('kataSandiKonfirmasi', 'DibajakOrang2026')
            ->call('simpan')
            ->assertHasErrors('token');

        $korban->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $korban->password));
    }

    public function test_tautan_kedaluwarsa_ditolak(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        // Majukan waktu melewati masa berlaku tautan.
        $menit = (int) config('auth.passwords.users.expire', 60);
        Carbon::setTestNow(Carbon::now()->addMinutes($menit + 5));

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'SandiUji2026')
            ->call('simpan')
            ->assertHasErrors('token');

        Carbon::setTestNow();

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiLama123', $pengguna->password));
    }

    public function test_konfirmasi_kata_sandi_harus_cocok(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'BedaSekali2026')
            ->call('simpan')
            ->assertHasErrors(['kataSandi' => 'same']);
    }

    public function test_permintaan_tautan_dibatasi(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();

        for ($i = 0; $i < 3; $i++) {
            // Password broker punya jeda sendiri, jadi waktu dimajukan
            // agar yang diuji di sini benar-benar pembatas milik komponen.
            Carbon::setTestNow(Carbon::now()->addMinutes(2));
            Livewire::test(LupaPassword::class)->set('email', $pengguna->email)->call('kirimTautan');
        }

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimTautan')
            ->assertHasErrors('email');

        Carbon::setTestNow();
        Mail::assertSent(TautanResetPasswordMail::class, 3);
    }

    public function test_broker_memberi_jeda_permintaan_beruntun(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();

        Livewire::test(LupaPassword::class)->set('email', $pengguna->email)->call('kirimTautan');
        // Permintaan kedua dalam jeda throttle broker tidak mengirim email lagi.
        Livewire::test(LupaPassword::class)->set('email', $pengguna->email)->call('kirimTautan');

        Mail::assertSent(TautanResetPasswordMail::class, 1);
        $this->assertSame(
            Password::RESET_THROTTLED,
            Password::sendResetLink(['email' => $pengguna->email])
        );
    }

    public function test_kegagalan_kirim_email_tidak_membuat_halaman_galat(): void
    {
        // Di jaringan yang DNS-nya memblokir host SMTP, pengiriman gagal.
        // Halaman harus memberi pesan, bukan menampilkan galat 500.
        $pengguna = $this->buatPengguna();

        Mail::shouldReceive('to')->andThrow(new TransportException('DNS tidak terjangkau'));

        Livewire::test(LupaPassword::class)
            ->set('email', $pengguna->email)
            ->call('kirimTautan')
            ->assertHasErrors('email')
            ->assertSet('terkirim', false);
    }

    public function test_kata_sandi_tetap_tersimpan_walau_surat_pemberitahuan_gagal(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        // Setelah token didapat, pengiriman surat dibuat gagal.
        Mail::shouldReceive('to')->andThrow(new TransportException('DNS tidak terjangkau'));

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'SandiUji2026')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $pengguna->refresh();
        $this->assertTrue(Hash::check('SandiUji2026', $pengguna->password));
    }

    public function test_token_api_dicabut_saat_kata_sandi_diatur_ulang(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $token = $this->mintaTautan($pengguna);

        // Token API yang masih hidup sebelum kata sandi diganti.
        DB::table('oauth_access_tokens')->insert([
            'id' => 'uji-' . uniqid(),
            'user_id' => $pengguna->getKey(),
            'client_id' => 1,
            'name' => 'uji',
            'scopes' => '[]',
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        Livewire::test(AturUlangPassword::class, ['token' => $token])
            ->set('email', $pengguna->email)
            ->set('kataSandi', 'SandiUji2026')
            ->set('kataSandiKonfirmasi', 'SandiUji2026')
            ->call('simpan');

        $masihHidup = DB::table('oauth_access_tokens')
            ->where('user_id', $pengguna->getKey())
            ->where('revoked', false)
            ->count();

        $this->assertSame(0, $masihHidup, 'Token API harus ikut dicabut saat kata sandi diganti.');
    }
}
