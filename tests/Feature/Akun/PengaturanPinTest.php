<?php

namespace Tests\Feature\Akun;

use App\Livewire\Akun\PengaturanPin;
use App\Mail\PemberitahuanPinMail;
use App\Support\IngatanMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PengaturanPinTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(array $ubah = []): User
    {
        $pengguna = User::create(array_merge([
            'full_name' => 'Uji Atur PIN',
            'username' => 'uji_atur_pin_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ], $ubah));

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public function test_tab_pin_tampil_di_halaman_profil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk()
            ->assertSee('PIN masuk')
            ->assertSeeLivewire(PengaturanPin::class)
            // Livewire harus ikut memuat asetnya di layout admin, kalau tidak
            // tombol di dalam tab tidak akan berfungsi.
            ->assertSee('livewire.js', false);
    }

    public function test_pin_bisa_diaktifkan_dengan_kata_sandi_yang_benar(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasNoErrors();

        $pengguna->refresh();

        $this->assertTrue($pengguna->pinAktif());
        $this->assertTrue($pengguna->pinCocok('482913'));
        $this->assertNotNull($pengguna->pin_diubah_pada);

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'diaktifkan');
    }

    public function test_pin_disimpan_teracak_bukan_apa_adanya(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan');

        $tersimpan = (string) $pengguna->refresh()->pin;

        $this->assertNotSame('482913', $tersimpan);
        $this->assertStringNotContainsString('482913', $tersimpan);
        $this->assertTrue(Hash::check('482913', $tersimpan));
    }

    public function test_mengaktifkan_pin_membuat_perangkat_ini_siap_pakai_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue, 'Perangkat harus mengingat akun supaya PIN bisa dipakai tanpa username.');

        $isi = json_decode((string) $kue->getValue(), true);

        $this->assertSame('pin', $isi['mode']);
        $this->assertSame($pengguna->username, $isi['identitas']);
        $this->assertSame($pengguna->getKey(), $isi['uid']);
        // Mengaktifkan PIN tidak ikut menyalakan pengisian otomatis isian.
        $this->assertFalse($isi['ingat']);
    }

    public function test_kata_sandi_salah_menolak_perubahan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasErrors('kataSandi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    #[DataProvider('pinLemah')]
    public function test_pin_yang_mudah_ditebak_ditolak(string $pin): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', $pin)
            ->set('pinKonfirmasi', $pin)
            ->call('simpan')
            ->assertHasErrors('pin');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public static function pinLemah(): array
    {
        return [
            'angka sama' => ['111111'],
            'berurutan naik' => ['123456'],
            'berurutan turun' => ['654321'],
            'pola berulang' => ['121212'],
            'tiga angka berulang' => ['123123'],
            'terlalu umum' => ['159753'],
        ];
    }

    public function test_pin_tidak_boleh_sama_dengan_tanggal_lahir(): void
    {
        $pengguna = $this->buatPengguna(['tanggal_lahir' => '1998-08-17']);
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '170898')
            ->set('pinKonfirmasi', '170898')
            ->call('simpan')
            ->assertHasErrors('pin');
    }

    public function test_ulangan_pin_harus_sama(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482914')
            ->call('simpan')
            ->assertHasErrors('pinKonfirmasi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_pin_hanya_boleh_angka_sepanjang_enam(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '48291')
            ->set('pinKonfirmasi', '48291')
            ->call('simpan')
            ->assertHasErrors(['pin' => 'digits']);
    }

    public function test_pin_bisa_diubah(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->set('pin', '739154')
            ->set('pinKonfirmasi', '739154')
            ->call('simpan')
            ->assertHasNoErrors();

        $pengguna->refresh();

        $this->assertTrue($pengguna->pinCocok('739154'));
        $this->assertFalse($pengguna->pinCocok('482913'));

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'diubah');
    }

    public function test_pin_bisa_dinonaktifkan(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', self::SANDI)
            ->call('nonaktifkan')
            ->assertHasNoErrors()
            // Pemberitahuannya lewat toast bersama, bukan kotak .alert.
            ->assertDispatched('toast', jenis: 'berhasil');

        $pengguna->refresh();

        $this->assertFalse($pengguna->pinAktif());
        $this->assertNull($pengguna->pin);

        Mail::assertSent(PemberitahuanPinMail::class, fn ($surat) => $surat->aksi === 'dinonaktifkan');
    }

    public function test_nonaktifkan_butuh_kata_sandi_yang_benar(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('kataSandi', 'SandiKeliru2026')
            ->call('nonaktifkan')
            ->assertHasErrors('kataSandi');

        $this->assertTrue($pengguna->refresh()->pinAktif());
    }

    public function test_kata_sandi_salah_berulang_ditunda(): void
    {
        $pengguna = $this->buatPengguna();
        $this->actingAs($pengguna);

        RateLimiter::clear('atur-pin|' . $pengguna->getKey());

        $uji = Livewire::test(PengaturanPin::class);

        for ($i = 0; $i < 5; $i++) {
            $uji->set('kataSandi', 'SandiKeliru2026')
                ->set('pin', '482913')
                ->set('pinKonfirmasi', '482913')
                ->call('simpan')
                ->assertHasErrors('kataSandi');
        }

        $uji->set('kataSandi', self::SANDI)
            ->set('pin', '482913')
            ->set('pinKonfirmasi', '482913')
            ->call('simpan')
            ->assertHasErrors('kataSandi');

        $this->assertFalse($pengguna->refresh()->pinAktif());
    }

    public function test_pin_dari_komputer_bisa_didaftarkan_di_perangkat_lain(): void
    {
        // Ini jawaban untuk "PIN dibuat di komputer, mau dipakai di HP":
        // masuk sekali dengan kata sandi di perangkat baru, lalu masukkan PIN
        // yang sudah ada di sini.
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        $uji = Livewire::test(PengaturanPin::class);

        $this->assertFalse($uji->instance()->perangkatSiap(), 'Perangkat baru belum boleh langsung memakai PIN.');

        $uji->set('pinPerangkat', '482913')
            ->call('aktifkanDiPerangkat')
            ->assertHasNoErrors();

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);

        $isi = json_decode((string) $kue->getValue(), true);

        $this->assertSame('pin', $isi['mode']);
        $this->assertSame($pengguna->getKey(), $isi['uid']);
    }

    public function test_daftar_perangkat_menolak_pin_yang_salah(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        RateLimiter::clear('pin-perangkat|' . $pengguna->getKey());

        Livewire::test(PengaturanPin::class)
            ->set('pinPerangkat', '111333')
            ->call('aktifkanDiPerangkat')
            ->assertHasErrors('pinPerangkat');

        $this->assertNull(Cookie::queued(IngatanMasuk::NAMA));
    }

    public function test_perangkat_bisa_dilupakan_tanpa_mematikan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');
        $this->actingAs($pengguna);

        // Kue dikirimkan seperti yang dilakukan peramban pada kunjungan
        // berikutnya; harness Livewire tidak mengembalikan kue yang baru
        // diantre ke permintaan setelahnya.
        Testable::create(PengaturanPin::class, [], [], [
            IngatanMasuk::NAMA => json_encode([
                'identitas' => $pengguna->username,
                'mode' => 'pin',
                'ingat' => false,
                'uid' => $pengguna->getKey(),
            ]),
        ])->call('lupakanPerangkat');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);
        $this->assertNull($kue->getValue(), 'Ingatan perangkat harus dihapus.');
        $this->assertTrue($pengguna->refresh()->pinAktif(), 'PIN akun harus tetap aktif.');
    }
}
