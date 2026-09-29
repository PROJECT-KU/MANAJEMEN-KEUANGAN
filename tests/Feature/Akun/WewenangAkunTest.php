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

    /**
     * $peran menentukan hak akses; $jabatan hanya penanda posisi dan sengaja
     * TIDAK ikut menentukan apa pun — justru itu yang diuji di berkas ini.
     */
    private function buatPengguna(string $peran = 'user', string $jabatan = 'karyawan'): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Wewenang',
            'username' => 'uji_wewenang_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'peran' => $peran,
            'level' => $jabatan,
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    public function test_pengguna_biasa_tidak_bisa_mengangkat_dirinya_jadi_manager(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.pengguna.update', $pengguna->getKey()), [
                'peran' => 'administrator',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertSame('user', $pengguna->refresh()->peran);
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
        $this->actingAs($this->buatPengguna('administrator', 'manager'))
            ->get(route('account.pengguna.index'))
            ->assertOk();
    }

    public function test_pengelola_tetap_bisa_mengubah_peran(): void
    {
        $manajer = $this->buatPengguna('administrator', 'manager');
        $anggota = $this->buatPengguna('karyawan');

        $this->actingAs($manajer)
            ->post(route('account.pengguna.update', $anggota->getKey()), ['peran' => 'administrator'])
            ->assertRedirect();

        $this->assertSame('administrator', $anggota->refresh()->peran);
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
                'peran' => 'administrator',
                'status' => 'active',
                'norek' => '1234567890',
            ])
            ->assertRedirect();

        $pengguna->refresh();

        // Yang dijaga peranNYA, bukan jabatannya: halaman profil memang boleh
        // menyentuh jabatan, tetapi tidak boleh menaikkan hak akses.
        $this->assertSame('user', $pengguna->peran);
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

        $manajer = $this->buatPengguna('administrator', 'manager');
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

    public function test_profil_hanya_bisa_dibuka_lewat_uuid(): void
    {
        $pengguna = $this->buatPengguna();

        // UUID-nya sendiri: terbuka.
        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk();

        // Id berurutan tidak lagi jadi alamat. Dengan id, siapa pun yang
        // sudah masuk bisa menaikkan angkanya satu per satu dan memetakan
        // seluruh akun beserta waktu pembuatannya.
        $this->actingAs($pengguna)
            ->get('/account/profil/' . $pengguna->getKey() . '/show')
            ->assertNotFound();

        // UUID karangan juga tidak membocorkan apa pun.
        $this->actingAs($pengguna)
            ->get('/account/profil/00000000-0000-0000-0000-000000000000/show')
            ->assertNotFound();
    }

    public function test_setiap_akun_baru_dapat_uuid(): void
    {
        $pengguna = $this->buatPengguna();

        $this->assertNotEmpty($pengguna->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $pengguna->uuid
        );
    }

    public function test_profil_orang_lain_dikembalikan_ke_profil_sendiri(): void
    {
        $manager = $this->buatPengguna('administrator', 'manager');
        $manager->forceFill(['company' => 'PT Uji'])->save();

        $rekan = $this->buatPengguna('karyawan');
        $rekan->forceFill(['company' => 'PT Uji', 'full_name' => 'Nama Rekan'])->save();

        // Dulu halaman ini terbuka untuk manager dan menampilkan dua orang
        // sekaligus: kotak isian terisi data rekan, tombol Simpan menulis
        // ke akun manager sendiri.
        $this->actingAs($manager)
            ->get(route('account.profil.show', $rekan->uuid))
            ->assertRedirect(route('account.profil.show', $manager->uuid));

        // Dan halaman tujuannya tidak boleh memuat nama rekan di mana pun.
        $this->actingAs($manager)
            ->get(route('account.profil.show', $manager->uuid))
            ->assertOk()
            ->assertDontSee('Nama Rekan');
    }

    public function test_bank_penggajian_selalu_bri(): void
    {
        $pengguna = $this->buatPengguna('administrator', 'manager');
        $pengguna->forceFill(['bank' => '008', 'norek' => '123456'])->save();

        // Kode bank lain dikirim langsung ke alamat penyimpanan, seolah lewat
        // alat pengembang. Penguncian di layar saja tidak cukup.
        $this->actingAs($pengguna)->post(route('account.profil.update'), [
            'bank' => '014',      // BCA
            'norek' => '123456',
        ]);

        $pengguna->refresh();

        // 002 = BRI. Akun lama yang memakai bank lain ikut dibetulkan.
        $this->assertSame('002', $pengguna->bank);
    }
}
