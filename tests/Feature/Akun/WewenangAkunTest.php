<?php

namespace Tests\Feature\Akun;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Penjagaan wewenang pada halaman akun.
 *
 * Sebelumnya siapa pun yang sudah masuk bisa mengirim nomor id milik orang
 * lain ke halaman pengelolaan pengguna — termasuk mengangkat dirinya sendiri
 * menjadi manager. Tes ini mengunci perilaku yang benar.
 */
class WewenangAkunTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(string $level = 'user'): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Wewenang',
            'username' => 'uji_wewenang_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => $level,
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public function test_pengguna_biasa_tidak_bisa_mengangkat_dirinya_jadi_manager(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update', $pengguna->getKey()), [
                'level' => 'manager',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertSame('user', $pengguna->refresh()->level);
    }

    public function test_pengguna_biasa_tidak_bisa_mengubah_akun_orang_lain(): void
    {
        $pengguna = $this->buatPengguna();
        $korban = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update.datadiri', $korban->getKey()), [
                'email' => 'pindah' . uniqid() . '@contoh.test',
            ])
            ->assertForbidden();

        $this->assertSame($korban->email, $korban->refresh()->email);
    }

    public function test_pengguna_biasa_tidak_bisa_menghapus_akun_lain(): void
    {
        $pengguna = $this->buatPengguna();
        $korban = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->delete(route('account.pengguna.destroy', $korban->getKey()))
            ->assertForbidden();

        $this->assertNotNull(User::find($korban->getKey()));
    }

    public function test_pengguna_biasa_tidak_bisa_menandai_email_terverifikasi(): void
    {
        $pengguna = $this->buatPengguna();

        $belum = $this->buatPengguna();
        $belum->forceFill(['email_verified_at' => null])->save();

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update.vertifikasiemail', $belum->getKey()))
            ->assertForbidden();

        $this->assertNull($belum->refresh()->email_verified_at);
    }

    public function test_daftar_pengguna_tertutup_untuk_pengguna_biasa(): void
    {
        $this->actingAs($this->buatPengguna())
            ->get(route('account.pengguna.index'))
            ->assertForbidden();
    }

    public function test_daftar_pengguna_terbuka_untuk_pengelola(): void
    {
        // Sengaja satu permintaan per tes: sesi dan penjaga auth dipakai ulang
        // dalam satu proses PHP, sehingga dua actingAs berturut-turut saling
        // menimpa dan hasilnya menyesatkan.
        $this->actingAs($this->buatPengguna('manager'))
            ->get(route('account.pengguna.index'))
            ->assertOk();
    }

    public function test_pengelola_tetap_bisa_mengubah_peran(): void
    {
        $manajer = $this->buatPengguna('manager');
        $anggota = $this->buatPengguna('karyawan');

        $this->actingAs($manajer)
            ->post(route('account.pengguna.update', $anggota->getKey()), ['level' => 'staff'])
            ->assertRedirect();

        $this->assertSame('staff', $anggota->refresh()->level);
    }

    public function test_ganti_email_sendiri_butuh_kata_sandi(): void
    {
        $pengguna = $this->buatPengguna();
        $emailBaru = 'baru' . uniqid() . '@contoh.test';

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update.datadiri', $pengguna->getKey()), [
                'email' => $emailBaru,
            ])
            ->assertSessionHas('errorsandiemail');

        $this->assertNotSame($emailBaru, $pengguna->refresh()->email);

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update.datadiri', $pengguna->getKey()), [
                'email' => $emailBaru,
                'kata_sandi_email' => self::SANDI,
            ]);

        $this->assertSame($emailBaru, $pengguna->refresh()->email);
        $this->assertNull($pengguna->refresh()->email_verified_at);
    }

    public function test_profil_tidak_menerima_perubahan_peran(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.update'), [
                'level' => 'admin',
                'status' => 'active',
                'norek' => '1234567890',
            ])
            ->assertRedirect();

        $pengguna->refresh();

        $this->assertSame('user', $pengguna->level);
        $this->assertSame('1234567890', $pengguna->norek);
    }

    public function test_username_tidak_boleh_bentrok(): void
    {
        $pengguna = $this->buatPengguna();
        $lain = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.update'), ['username' => $lain->username])
            ->assertSessionHasErrors('username');

        $this->assertNotSame($lain->username, $pengguna->refresh()->username);
    }

    public function test_buka_kunci_hanya_untuk_pengawas(): void
    {
        $this->actingAs($this->buatPengguna())
            ->post(route('account.aktivitas-masuk.buka-kunci'), ['identitas' => 'siapa_saja'])
            ->assertForbidden();
    }

    public function test_pengelola_bisa_mematikan_pin_karyawan(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $manajer = $this->buatPengguna('manager');
        $anggota = $this->buatPengguna('karyawan');
        $anggota->aturPin('482913');

        $this->actingAs($manajer)
            ->post(route('account.pengguna.matikan-pin', $anggota->getKey()))
            ->assertRedirect();

        $this->assertFalse($anggota->refresh()->pinAktif());

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PemberitahuanPinMail::class);
    }

    public function test_pengguna_biasa_tidak_bisa_mematikan_pin_orang_lain(): void
    {
        $pengguna = $this->buatPengguna();
        $korban = $this->buatPengguna();
        $korban->aturPin('482913');

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.matikan-pin', $korban->getKey()))
            ->assertForbidden();

        $this->assertTrue($korban->refresh()->pinAktif());
    }

    public function test_api_lama_sudah_tidak_ada(): void
    {
        // API dihapus: jalur masuk itu melewati pembatas per akun, pencatatan
        // jejak, dan pemberitahuan perangkat baru yang berlaku di halaman masuk.
        $this->postJson('/api/v1/login', ['username' => 'a', 'password' => 'b'])->assertNotFound();
        $this->postJson('/api/v1/register', [])->assertNotFound();
        $this->get('/oauth/authorize')->assertNotFound();
    }
}
