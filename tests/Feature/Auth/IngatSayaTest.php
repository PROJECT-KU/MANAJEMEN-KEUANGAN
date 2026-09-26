<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Masuk;
use App\Support\IngatanMasuk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

/**
 * "Ingat saya" hanya menyimpan username/email di peramban — tidak pernah
 * kata sandi.
 */
class IngatSayaTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Ingat',
            'username' => 'uji_ingat_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    private function bukaMasuk(array $isiKue = []): Testable
    {
        $kue = $isiKue === [] ? [] : [IngatanMasuk::NAMA => json_encode($isiKue)];

        return Testable::create(Masuk::class, [], [], $kue);
    }

    /** @return array<string,mixed>|null */
    private function isiKueTersimpan(): ?array
    {
        $kue = Cookie::queued(IngatanMasuk::NAMA);

        if ($kue === null || $kue->getValue() === null || $kue->getValue() === '') {
            return null;
        }

        return json_decode($kue->getValue(), true);
    }

    public function test_identitas_disimpan_saat_ingat_saya_dicentang(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk()
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->set('ingatSaya', true)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $isi = $this->isiKueTersimpan();

        $this->assertNotNull($isi);
        $this->assertSame($pengguna->username, $isi['identitas']);
        $this->assertTrue($isi['ingat']);
        $this->assertSame('sandi', $isi['mode']);
    }

    public function test_kata_sandi_tidak_pernah_ikut_disimpan(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk()
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->set('ingatSaya', true)
            ->call('masuk');

        $kue = Cookie::queued(IngatanMasuk::NAMA);

        $this->assertNotNull($kue);
        $this->assertStringNotContainsString(self::SANDI, (string) $kue->getValue());
        $this->assertArrayNotHasKey('kataSandi', (array) $this->isiKueTersimpan());
        $this->assertArrayNotHasKey('pin', (array) $this->isiKueTersimpan());
    }

    public function test_tanpa_centang_ingatan_dihapus(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk()
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $this->assertNull($this->isiKueTersimpan());
    }

    public function test_isian_terisi_otomatis_dari_ingatan(): void
    {
        $pengguna = $this->buatPengguna();

        $this->bukaMasuk([
            'identitas' => $pengguna->username,
            'mode' => 'sandi',
            'ingat' => true,
            'uid' => $pengguna->getKey(),
        ])
            ->assertSet('identitas', $pengguna->username)
            ->assertSet('ingatSaya', true)
            ->assertSet('identitasTersimpan', true)
            ->assertSee('Bukan Anda?');
    }

    public function test_ingatan_untuk_pin_tidak_mengisi_isian(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');

        // ingat = false: ingatan ini hanya untuk PIN, bukan untuk mengisi isian.
        $this->bukaMasuk([
            'identitas' => $pengguna->username,
            'mode' => 'pin',
            'ingat' => false,
            'uid' => $pengguna->getKey(),
        ])
            ->assertSet('identitas', '')
            ->assertSet('identitasTersimpan', false)
            ->assertSet('akunPerangkat', $pengguna->username);
    }

    public function test_melepas_centang_tidak_mematikan_pin_perangkat(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');

        $this->bukaMasuk([
            'identitas' => $pengguna->username,
            'mode' => 'pin',
            'ingat' => true,
            'uid' => $pengguna->getKey(),
        ])
            ->set('ingatSaya', false)
            ->assertSet('identitasTersimpan', false);

        $isi = $this->isiKueTersimpan();

        $this->assertNotNull($isi);
        $this->assertSame('pin', $isi['mode']);
        $this->assertFalse($isi['ingat']);
    }

    public function test_masuk_dengan_sandi_tidak_menghapus_ingatan_pin(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->aturPin('482913');

        $this->bukaMasuk([
            'identitas' => $pengguna->username,
            'mode' => 'pin',
            'ingat' => false,
            'uid' => $pengguna->getKey(),
        ])
            ->call('gantiMode', 'sandi')
            ->set('identitas', $pengguna->username)
            ->set('kataSandi', self::SANDI)
            ->call('masuk')
            ->assertRedirect('/account/dashboard');

        $isi = $this->isiKueTersimpan();

        $this->assertNotNull($isi);
        $this->assertSame('pin', $isi['mode'], 'PIN perangkat seharusnya tetap bisa dipakai.');
    }
}
