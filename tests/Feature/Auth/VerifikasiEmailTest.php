<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\KirimUlangVerifikasi;
use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class VerifikasiEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(array $ubah = []): User
    {
        $pengguna = User::create(array_merge([
            'full_name' => 'Uji Verifikasi',
            'username' => 'ujiver' . substr(uniqid(), -6),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'staff',
        ], $ubah));

        return $pengguna->refresh();
    }

    public function test_halaman_kirim_ulang_bisa_dibuka(): void
    {
        $this->get('/kirim-ulang-verifikasi')->assertOk()->assertSeeLivewire(KirimUlangVerifikasi::class);
    }

    public function test_tautan_baru_dikirim_untuk_akun_belum_terverifikasi(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        RateLimiter::clear('verifikasi-ulang|' . strtolower($pengguna->email) . '|127.0.0.1');

        Livewire::test(KirimUlangVerifikasi::class)
            ->set('email', $pengguna->email)
            ->call('kirimUlang')
            ->assertSet('terkirim', true)
            ->assertHasNoErrors();

        Mail::assertSent(VerifikasiEmailMail::class, fn ($surat) => $surat->hasTo($pengguna->email));
    }

    public function test_akun_yang_sudah_terverifikasi_tidak_dikirimi_apa_pun(): void
    {
        Mail::fake();
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill(['email_verified_at' => now()])->save();

        Livewire::test(KirimUlangVerifikasi::class)
            ->set('email', $pengguna->email)
            ->call('kirimUlang')
            ->assertSet('terkirim', true);

        Mail::assertNothingSent();
    }

    public function test_akun_baru_yang_belum_verifikasi_ditahan(): void
    {
        $pengguna = $this->buatPengguna();
        // Dibuat sesudah tanggal wajib verifikasi.
        $pengguna->forceFill(['created_at' => now(), 'email_verified_at' => null])->save();

        $this->actingAs($pengguna)
            ->get('/account/dashboard')
            ->assertRedirect(route('verification.resend'));
    }

    public function test_akun_lama_yang_belum_verifikasi_tetap_boleh_masuk(): void
    {
        // Saat aturan dipasang ada 47 akun lama yang belum terverifikasi;
        // mereka sengaja diberi kelonggaran supaya tidak terkunci serentak.
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill([
            'created_at' => Carbon::parse(config('auth.verifikasi_wajib_sejak'))->subMonth(),
            'email_verified_at' => null,
        ])->save();

        $this->actingAs($pengguna)->get('/account/dashboard')->assertOk();
    }

    public function test_akun_terverifikasi_tidak_ditahan(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill(['created_at' => now(), 'email_verified_at' => now()])->save();

        $this->actingAs($pengguna)->get('/account/dashboard')->assertOk();
    }
}
