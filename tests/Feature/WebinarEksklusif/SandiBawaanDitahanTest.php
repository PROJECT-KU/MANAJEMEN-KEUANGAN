<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\Webinar\BuatAkunPeserta;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penahan akun yang kata sandinya masih bawaan.
 *
 * Akun peserta webinar lahir dengan sandi yang SAMA untuk semua orang, atas
 * permintaan pemiliknya, supaya suratnya bisa menyebutkan sandi yang mudah
 * diketik orang yang tidak terbiasa. Akibatnya nyata: MIS masuk lewat
 * username, username peserta dirakit dari namanya, jadi selama sandinya belum
 * diganti, NAMA ORANG LAIN ADALAH KUNCI AKUN ORANG LAIN.
 *
 * Penahan inilah satu-satunya yang membuat keadaan itu berumur pendek.
 * Kalau ia rusak, tidak ada satu pun galat yang terbit — semua halaman justru
 * terbuka seperti biasa, dan itu persis gejalanya.
 */
class SandiBawaanDitahanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function akunBawaan(): User
    {
        $u = User::create([
            'full_name' => 'Peserta Baru',
            'username' => 'tahan_' . Str::random(10),
            'email' => 'tahan' . Str::random(8) . '@contoh.test',
            'password' => Hash::make(BuatAkunPeserta::SANDI_BAWAAN),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN, 'wajib_ganti_sandi' => true])->save();

        return $u;
    }

    #[Test]
    public function halaman_lain_dialihkan_ke_ganti_sandi(): void
    {
        $this->actingAs($this->akunBawaan())
            ->get(route('account.rekaman.index'))
            ->assertRedirect(route('account.sandi.wajib'));
    }

    #[Test]
    public function halaman_ganti_sandinya_sendiri_tidak_ikut_dialihkan(): void
    {
        /*
         * Tanpa pengecualian ini, pengalihannya BERPUTAR selamanya dan
         * peramban menyerah dengan ERR_TOO_MANY_REDIRECTS — akunnya terkunci
         * total, bukan terarahkan.
         */
        $this->actingAs($this->akunBawaan())
            ->get(route('account.sandi.wajib'))
            ->assertOk();
    }

    #[Test]
    public function permintaan_json_dijawab_409_bukan_dialihkan(): void
    {
        /*
         * Pengalihan 302 pada XHR menghasilkan halaman HTML yang masuk ke
         * pengurai JSON. Layarnya lalu cuma diam — tanpa pesan, tanpa galat,
         * tanpa petunjuk bahwa ada yang harus dikerjakan dulu.
         */
        $r = $this->actingAs($this->akunBawaan())
            ->getJson(route('account.rekaman.index'));

        $r->assertStatus(409);
        $r->assertJson(['wajib_ganti_sandi' => true]);
        $r->assertJsonPath('alihkan', route('account.sandi.wajib'));
    }

    #[Test]
    public function mengganti_sandi_melepas_penahannya(): void
    {
        $akun = $this->akunBawaan();

        /*
         * Jawaban HaveIBeenPwned dipalsukan sebagai "tidak ada di daftar".
         *
         * Tanpa ini ujinya tetap hijau — TETAPI karena alasan yang salah:
         * panggilan jaringannya gagal, Laravel menganggap sandinya aman, dan
         * aturan uncompromised() diam-diam tidak diuji sama sekali. Hijau
         * yang didapat dari kegagalan adalah hijau yang tidak menjaga apa pun.
         */
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response(
                "0018A45C4D1DEF81644B54AB7F969B88D65:1\n", 200
            ),
        ]);

        $this->actingAs($akun)
            ->post(route('account.sandi.wajib.simpan'), [
                'password' => 'Jingga7Semesta2026',
                'password_confirmation' => 'Jingga7Semesta2026',
            ])
            ->assertRedirect(route('account.rekaman.index'));

        $akun->refresh();

        $this->assertFalse((bool) $akun->wajib_ganti_sandi);
        $this->assertTrue(Hash::check('Jingga7Semesta2026', $akun->password));
    }

    #[Test]
    public function sandi_bawaan_itu_sendiri_ditolak_sebagai_sandi_baru(): void
    {
        $akun = $this->akunBawaan();

        /*
         * Aturan sandi aplikasi ini memakai uncompromised(), dan
         * "Password123" ada di daftar kebocoran. Jadi halaman yang memaksa
         * mengganti sandi tidak bisa dilewati dengan mengetik ulang sandi
         * yang sama — yang memang seluruh gunanya.
         *
         * -------------------------------------------------------------------
         * Jawaban HaveIBeenPwned DIPALSUKAN, dan itu bukan sekadar kerapian
         * -------------------------------------------------------------------
         *
         * uncompromised() memanggil api.pwnedpasswords.com SUNGGUHAN lewat
         * jaringan. Uji yang bergantung padanya akan MERAH saat jaringan mati,
         * saat layanan itu lambat, atau — yang paling menyesatkan — HIJAU
         * PALSU: kalau panggilannya gagal, Laravel menganggap sandinya aman
         * dan validasinya lolos, sehingga ujinya diam-diam berhenti menguji
         * apa pun.
         *
         * Dipalsukan dengan jawaban yang bentuknya persis seperti aslinya:
         * daftar "SISA_SHA1:JUMLAH" per baris, dan sisa hash "Password123"
         * ada di dalamnya.
         */
        $sisa = substr(strtoupper(sha1(BuatAkunPeserta::SANDI_BAWAAN)), 5);

        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response(
                "0018A45C4D1DEF81644B54AB7F969B88D65:1\n" . $sisa . ":123456\n",
                200
            ),
        ]);

        $this->actingAs($akun)
            ->post(route('account.sandi.wajib.simpan'), [
                'password' => BuatAkunPeserta::SANDI_BAWAAN,
                'password_confirmation' => BuatAkunPeserta::SANDI_BAWAAN,
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue((bool) $akun->fresh()->wajib_ganti_sandi);
    }

    #[Test]
    public function akun_biasa_tidak_ikut_ditahan(): void
    {
        $u = User::create([
            'full_name' => 'Pelanggan Lama',
            'username' => 'lama_' . Str::random(10),
            'email' => 'biasa' . Str::random(8) . '@contoh.test',
            'password' => Hash::make('SandiPilihanSendiri2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->actingAs($u)
            ->get(route('account.rekaman.index'))
            ->assertOk();
    }

    #[Test]
    public function halaman_ganti_sandi_tidak_dipajang_ke_yang_tidak_perlu(): void
    {
        $u = User::create([
            'full_name' => 'Pelanggan Lama',
            'username' => 'lama2_' . Str::random(10),
            'email' => 'biasa2' . Str::random(8) . '@contoh.test',
            'password' => Hash::make('SandiPilihanSendiri2026'),
            'level' => 'user',
        ]);
        $u->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        // Dibuka langsung lewat alamatnya, ia akan terbaca seperti TUNTUTAN
        // kepada orang yang sandinya memang sudah miliknya sendiri.
        $this->actingAs($u)
            ->get(route('account.sandi.wajib'))
            ->assertRedirect(route('account.more.index'));
    }
}
