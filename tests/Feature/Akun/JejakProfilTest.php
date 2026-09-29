<?php

namespace Tests\Feature\Akun;

use App\AktivitasMasuk;
use App\Mail\EmailAkunDipindahMail;
use App\Mail\PasswordResetSuccessMail;
use App\Support\FotoProfil;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Perubahan pada profil harus meninggalkan jejak dan, untuk alamat email,
 * mengabari pemilik alamat lamanya. Tanpa keduanya, satu sesi yang terlanjur
 * dibajak bisa memindahkan akun tanpa pemiliknya pernah tahu.
 */
class JejakProfilTest extends TestCase
{
    use DatabaseTransactions;

    private const SANDI = 'RahasiaUji2026';

    private function buatPengguna(array $ubah = []): User
    {
        $pengguna = User::create(array_merge([
            'full_name' => 'Uji Jejak Profil',
            'username' => 'uji_jejak_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make(self::SANDI),
            'level' => 'karyawan',
        ], $ubah));

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    private function jejak(User $pengguna): array
    {
        return AktivitasMasuk::where('user_id', $pengguna->getKey())
            ->pluck('alasan')
            ->all();
    }

    #[Test]
    public function mengganti_alamat_email_mengabari_alamat_lama_dan_tercatat(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $emailLama = $pengguna->email;

        $this->actingAs($pengguna)
            ->post(route('account.profil.update.datadiri'), [
                'email' => 'alamat.baru.' . uniqid() . '@contoh.test',
                'kata_sandi_email' => self::SANDI,
            ])
            ->assertRedirect();

        $this->assertNotSame($emailLama, $pengguna->refresh()->email);
        $this->assertNull($pengguna->email_verified_at, 'Alamat baru harus diverifikasi ulang.');
        $this->assertContains('alamat email diganti', $this->jejak($pengguna));

        Mail::assertSent(
            EmailAkunDipindahMail::class,
            fn ($surat) => $surat->hasTo($emailLama) && $surat->emailBaru === $pengguna->email
        );
    }

    /**
     * Penjaga terhadap kesalahan yang sudah pernah terjadi: jendela ganti
     * email di halaman profil sempat mengirim ke rute pengelolaan pengguna,
     * yang tidak mengirim tautan verifikasi, tidak mengabari alamat lama,
     * dan tidak mencatat apa pun. Ujinya lolos karena menembak rutenya
     * langsung, bukan lewat layar.
     */
    #[Test]
    public function jendela_ganti_email_menembak_rute_profil(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->get(route('account.profil.show', $pengguna->uuid))
            ->assertOk()
            ->assertSee(route('account.profil.update.datadiri'), false)
            ->assertDontSee(route('account.pengguna.update.datadiri', $pengguna), false);
    }

    #[Test]
    public function kata_sandi_salah_tidak_memindahkan_email_dan_tidak_mengabari_siapa_pun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $emailLama = $pengguna->email;

        $this->actingAs($pengguna)
            ->post(route('account.profil.update.datadiri'), [
                'email' => 'jangan.pindah@contoh.test',
                'kata_sandi_email' => 'SandiKeliru2026',
            ])
            ->assertRedirect();

        $this->assertSame($emailLama, $pengguna->refresh()->email);
        $this->assertSame([], $this->jejak($pengguna));

        Mail::assertNothingSent();
    }

    #[Test]
    public function username_dan_nomor_rekening_yang_berubah_ikut_tercatat(): void
    {
        $pengguna = $this->buatPengguna(['level' => 'karyawan']);

        $this->actingAs($pengguna)
            ->post(route('account.profil.update'), [
                'username' => 'uji_jejak_baru_' . uniqid(),
                'norek' => '123401001234567',
            ])
            ->assertRedirect();

        $jejak = $this->jejak($pengguna);

        $this->assertContains('username diganti', $jejak);
        $this->assertContains('nomor rekening diganti', $jejak);
    }

    #[Test]
    public function menyimpan_tanpa_mengubah_apa_pun_tidak_mengotori_riwayat(): void
    {
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill(['norek' => '123401001234567'])->save();

        $this->actingAs($pengguna)
            ->post(route('account.profil.update'), [
                'username' => $pengguna->username,
                'norek' => '123401001234567',
            ])
            ->assertRedirect();

        $this->assertSame([], $this->jejak($pengguna));
    }

    /**
     * Mengganti kata sandi adalah perubahan paling menentukan — siapa pun
     * yang berhasil melakukannya sudah memegang akunnya sepenuhnya — dan
     * sampai sekarang itulah satu-satunya yang tidak mengirim kabar apa pun.
     */
    #[Test]
    public function mengganti_kata_sandi_mengabari_pemilik_akun_dan_tercatat(): void
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

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SandiBaruUji2026', $pengguna->refresh()->password));
        $this->assertContains('kata sandi diganti', $this->jejak($pengguna));

        Mail::assertSent(
            PasswordResetSuccessMail::class,
            fn ($surat) => $surat->hasTo($pengguna->email) && $surat->ip !== ''
        );
    }

    #[Test]
    public function kata_sandi_lama_salah_tidak_mengabari_siapa_pun(): void
    {
        Mail::fake();

        $pengguna = $this->buatPengguna();
        $lama = $pengguna->password;

        $this->actingAs($pengguna)
            ->post(route('account.profil.reset.password'), [
                'old_password' => 'SandiKeliru2026',
                'password' => 'SandiBaruUji2026',
                'password_confirmation' => 'SandiBaruUji2026',
            ])
            ->assertOk();

        $this->assertSame($lama, $pengguna->refresh()->password);
        $this->assertSame([], $this->jejak($pengguna));

        Mail::assertNothingSent();
    }

    #[Test]
    public function foto_profil_bisa_dihapus_dan_tercatat(): void
    {
        $pengguna = $this->buatPengguna();

        $nama = 'uji-hapus-' . uniqid() . '.webp';
        Storage::disk(FotoProfil::DISK)->put($nama, 'bukan-webp-sungguhan');
        $pengguna->forceFill(['gambar' => $nama])->save();

        $this->actingAs($pengguna)
            ->post(route('account.profil.hapusFoto'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($pengguna->refresh()->gambar);
        $this->assertFalse(Storage::disk(FotoProfil::DISK)->exists($nama), 'Berkasnya harus ikut terhapus.');
        $this->assertContains('foto profil dihapus', $this->jejak($pengguna));
    }

    #[Test]
    public function tidak_ada_foto_berarti_tidak_ada_yang_dihapus(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.hapusFoto'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame([], $this->jejak($pengguna));
    }

    #[Test]
    public function kolom_terisi_tetapi_berkasnya_hilang_bukan_berarti_punya_foto(): void
    {
        // Kalau ini dianggap "punya foto", tombol Hapus foto muncul di layar
        // padahal tidak ada yang bisa dihapus.
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill(['gambar' => 'berkas-yang-tidak-ada.webp'])->save();

        $this->assertFalse($pengguna->refresh()->punya_foto);

        $this->actingAs($pengguna)
            ->post(route('account.profil.hapusFoto'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function gambar_bawaan_tidak_ikut_terhapus(): void
    {
        // 'no-image.jpg' dipakai bersama semua akun; menghapusnya akan
        // merusak tampilan akun lain yang juga memakainya.
        $pengguna = $this->buatPengguna();
        $pengguna->forceFill(['gambar' => 'no-image.jpg'])->save();

        $this->actingAs($pengguna)
            ->post(route('account.profil.hapusFoto'))
            ->assertSessionHas('error');

        $this->assertSame('no-image.jpg', $pengguna->refresh()->gambar);
        $this->assertFileExists(public_path('assets/img/profil/no-image.jpg'));
    }
}
