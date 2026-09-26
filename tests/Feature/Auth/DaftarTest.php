<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Daftar;
use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
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
            'kataSandi' => 'RahasiaUji2026',
            'kataSandiKonfirmasi' => 'RahasiaUji2026',
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
        $this->assertTrue(Hash::check('RahasiaUji2026', $pengguna->password));
        // Kata sandi tidak boleh tersimpan apa adanya.
        $this->assertNotSame('RahasiaUji2026', $pengguna->password);
    }

    public function test_username_dan_email_tidak_boleh_kembar(): void
    {
        $adaDulu = User::create([
            'full_name' => 'Sudah Ada',
            'username' => 'ujikembar' . substr(uniqid(), -6),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
        ]);

        Livewire::test(Daftar::class)
            ->set($this->isianSah(['username' => $adaDulu->username, 'email' => $adaDulu->email]))
            ->call('daftar')
            ->assertHasErrors(['username' => 'unique', 'email' => 'unique']);
    }

    public function test_konfirmasi_kata_sandi_harus_cocok(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['kataSandiKonfirmasi' => 'BedaSekali2026']))
            ->call('daftar')
            ->assertHasErrors(['kataSandi' => 'same']);
    }

    public function test_kata_sandi_terlalu_pendek_ditolak(): void
    {
        // Aturannya kini Password::defaults() (minimal 8, ada huruf & angka,
        // dan tidak pernah bocor), jadi yang diperiksa keberadaan galatnya.
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['kataSandi' => 'pendek', 'kataSandiKonfirmasi' => 'pendek']))
            ->call('daftar')
            ->assertHasErrors('kataSandi');
    }

    public function test_kata_sandi_tanpa_angka_ditolak(): void
    {
        Livewire::test(Daftar::class)
            ->set($this->isianSah(['kataSandi' => 'hanyahurufsaja', 'kataSandiKonfirmasi' => 'hanyahurufsaja']))
            ->call('daftar')
            ->assertHasErrors('kataSandi');
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

    public function test_tautan_verifikasi_dikirim_saat_mendaftar(): void
    {
        Mail::fake();
        $isian = $this->isianSah();

        Livewire::test(Daftar::class)->set($isian)->call('daftar')->assertHasNoErrors();

        $pengguna = User::where('username', $isian['username'])->first();
        $this->assertNull($pengguna->email_verified_at, 'Akun baru belum boleh terverifikasi.');

        Mail::assertSent(VerifikasiEmailMail::class, function ($surat) use ($pengguna) {
            return $surat->hasTo($pengguna->email)
                && str_contains($surat->tautan, '/verifikasi-email/' . $pengguna->getKey() . '/')
                && str_contains($surat->tautan, 'signature=');
        });
    }

    public function test_tautan_verifikasi_mengaktifkan_akun(): void
    {
        Mail::fake();
        $isian = $this->isianSah();
        Livewire::test(Daftar::class)->set($isian)->call('daftar');
        $pengguna = User::where('username', $isian['username'])->first();

        $tautan = null;
        Mail::assertSent(VerifikasiEmailMail::class, function ($surat) use (&$tautan) {
            $tautan = $surat->tautan;

            return true;
        });

        $this->get($tautan)->assertRedirect(route('login'));

        $pengguna->refresh();
        $this->assertNotNull($pengguna->email_verified_at);
        $this->assertSame('active', $pengguna->status);
    }

    public function test_tautan_verifikasi_yang_dirusak_ditolak(): void
    {
        Mail::fake();
        $isian = $this->isianSah();
        Livewire::test(Daftar::class)->set($isian)->call('daftar');
        $pengguna = User::where('username', $isian['username'])->first();

        $tautan = null;
        Mail::assertSent(VerifikasiEmailMail::class, function ($surat) use (&$tautan) {
            $tautan = $surat->tautan;

            return true;
        });

        $this->get($tautan . 'x')->assertForbidden();

        $pengguna->refresh();
        $this->assertNull($pengguna->email_verified_at);
    }

    public function test_jebakan_bot_menolak_pendaftaran(): void
    {
        Mail::fake();
        $isian = $this->isianSah(['kodePos2' => 'https://spam.example']);

        Livewire::test(Daftar::class)->set($isian)->call('daftar')->assertHasErrors('kodePos2');

        $this->assertNull(User::where('username', $isian['username'])->first());
        Mail::assertNothingSent();
    }

    public function test_pendaftaran_dibatasi_per_jaringan(): void
    {
        Mail::fake();
        RateLimiter::clear('daftar|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Daftar::class)->set($this->isianSah())->call('daftar')->assertHasNoErrors();
        }

        Livewire::test(Daftar::class)->set($this->isianSah())->call('daftar')->assertHasErrors('email');

        RateLimiter::clear('daftar|127.0.0.1');
    }
}
