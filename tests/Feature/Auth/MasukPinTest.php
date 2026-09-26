<?php

namespace Tests\Feature\Auth;

use App\AktivitasMasuk;
use App\Livewire\Auth\Masuk;
use App\Mail\PemberitahuanPinMail;
use App\Support\IngatanMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

class MasukPinTest extends TestCase
{
    use DatabaseTransactions;

    private const PIN = '482913';

    private function buatPengguna(array $ubah = [], bool $denganPin = true): User
    {
        $status = $ubah['status'] ?? 'active';
        unset($ubah['status']);

        $pengguna = User::create(array_merge([
            'full_name' => 'Uji PIN',
            'username' => 'uji_pin_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'karyawan',
        ], $ubah));

        $pengguna->forceFill(['status' => $status, 'email_verified_at' => now()])->save();
        $pengguna->refresh();

        if ($denganPin) {
            $pengguna->aturPin(self::PIN);
        }

        return $pengguna->refresh();
    }

    /**
     * Buka halaman masuk. Kue ingatan perangkat harus dikirim lewat
     * Testable::create supaya sudah ada saat mount() berjalan; Livewire::test
     * tidak menyediakan jalan untuk itu.
     */
    private function bukaMasuk(?User $pengguna = null, string $mode = 'pin', bool $ingat = false): Testable
    {
        $kue = [];

        if ($pengguna) {
            $kue[IngatanMasuk::NAMA] = json_encode([
                'identitas' => $pengguna->username,
                'mode' => $mode,
                'ingat' => $ingat,
                'uid' => $pengguna->getKey(),
            ]);
        }

        return Testable::create(Masuk::class, [], [], $kue);
    }

    public function test_tab_pin_mati_bila_perangkat_belum_mengaktifkan_pin(): void
    {
        $this->bukaMasuk()
            ->assertSet('mode', 'sandi')
            ->assertSet('siapPin', false)
            ->assertSee('belum aktif');
    }

    public function test_tab_pin_mati_bila_akun_belum_punya_pin(): void
    {
        $pengguna = $this->buatPengguna(denganPin: false);

        $this->bukaMasuk($pengguna)
            ->assertSet('mode', 'sandi')
            ->assertSet('siapPin', false);
    }

    public function test_bisa_masuk_hanya_dengan_pin_tanpa_username_dan_sandi(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk($pengguna)
            ->assertSet('mode', 'pin')
            ->assertSet('siapPin', true)
            ->assertSet('akunPerangkat', $pengguna->username)
            ->set('pin', self::PIN)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_pin_tetap_bisa_walau_ingat_saya_tidak_dicentang(): void
    {
        $pengguna = $this->buatPengguna();

        // ingat = false: username tidak diisikan ke isian, tapi PIN tetap jalan.
        $this->bukaMasuk($pengguna, ingat: false)
            ->assertSet('identitas', '')
            ->assertSet('ingatSaya', false)
            ->assertSet('mode', 'pin')
            ->set('pin', self::PIN)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($pengguna);
    }

    public function test_masuk_dengan_pin_dicatat_di_jejak(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk($pengguna)->set('pin', self::PIN)->call('masuk');

        $jejak = AktivitasMasuk::where('user_id', $pengguna->getKey())->latest('id')->first();

        $this->assertNotNull($jejak);
        $this->assertTrue($jejak->berhasil);
        $this->assertSame('masuk dengan PIN', $jejak->alasan);
    }

    public function test_pin_salah_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk($pengguna)
            ->set('pin', '111333')
            ->call('masuk')
            ->assertHasErrors('pin');

        $this->assertGuest();
        $this->assertDatabaseHas('aktivitas_masuk', [
            'user_id' => $pengguna->getKey(),
            'berhasil' => 0,
            'alasan' => 'PIN salah',
        ]);
    }

    public function test_pin_dinonaktifkan_setelah_batas_percobaan_dan_pemilik_diberi_tahu(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();

        RateLimiter::clear('masuk-pin|' . $pengguna->getKey());

        $batas = (int) config('auth.pin.batas_gagal');
        $uji = $this->bukaMasuk($pengguna);

        for ($i = 0; $i < $batas; $i++) {
            $uji->set('pin', '111333')->call('masuk')->assertHasErrors('pin');
        }

        $this->assertFalse($pengguna->refresh()->pinAktif());
        $this->assertGuest();

        Mail::assertSent(PemberitahuanPinMail::class, function ($surat) use ($pengguna) {
            return $surat->aksi === 'dinonaktifkan-otomatis' && $surat->hasTo($pengguna->email);
        });
    }

    public function test_pin_ditolak_bila_perangkat_tidak_mengingat_akun(): void
    {
        $this->buatPengguna();

        // Tanpa ingatan perangkat, mode PIN tidak punya akun untuk dibuka.
        $this->bukaMasuk()
            ->set('mode', 'pin')
            ->set('pin', self::PIN)
            ->call('masuk')
            ->assertHasErrors('pin')
            ->assertSet('mode', 'sandi');

        $this->assertGuest();
    }

    public function test_pin_tidak_bisa_diarahkan_ke_akun_lain_dari_peramban(): void
    {
        $korban = $this->buatPengguna();
        $penyerang = $this->buatPengguna();

        // Perangkat mengingat akun penyerang, tapi properti identitas ditukar
        // ke akun korban. Akun yang dibuka harus tetap dari kue, bukan isian.
        $this->bukaMasuk($penyerang)
            ->set('identitas', $korban->username)
            ->set('pin', self::PIN)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertAuthenticatedAs($penyerang);
    }

    public function test_akun_nonaktif_tidak_bisa_masuk_dengan_pin(): void
    {
        $pengguna = $this->buatPengguna(['status' => 'nonactive']);

        $this->bukaMasuk($pengguna)
            ->set('pin', self::PIN)
            ->call('masuk')
            ->assertHasErrors('identitas');

        $this->assertGuest();
    }

    public function test_pin_harus_enam_angka(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk($pengguna)
            ->set('pin', '123')
            ->call('masuk')
            ->assertHasErrors(['pin' => 'digits']);
    }

    public function test_ganti_akun_menghapus_ingatan_perangkat(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk($pengguna)
            ->call('lupakanSaya')
            ->assertSet('akunPerangkat', '')
            ->assertSet('mode', 'sandi');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);
        $this->assertNull($kue->getValue());
        $this->assertTrue($kue->getExpiresTime() < time());
    }
}
