<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Mail\AkunPesertaWebinarMail;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kapan keterangan akun dikirim, dan kapan TIDAK.
 *
 * Kaitnya dipasang di model, bukan di pengendali, karena ada EMPAT jalur yang
 * bisa menandai lunas. Uji di sini menjaga dua sisi yang sama-sama diam kalau
 * rusak: peserta yang membayar tetapi tidak pernah menerima akun, dan peserta
 * yang menerima surat yang sama berulang-ulang setiap barisnya disentuh.
 */
class AkunDikirimSaatLunasTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function pendaftaran(array $ganti = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'token' => Str::random(30),
            'nama' => 'Pemesan Uji',
            'email' => 'pemesan' . Str::random(8) . '@contoh.test',
            'telp' => '081234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => 100000,
            'status' => 'pending',
        ], $ganti));
    }

    #[Test]
    public function belum_bayar_belum_dapat_akun(): void
    {
        $p = $this->pendaftaran();

        $this->assertNull(User::where('email', $p->email)->first(),
            'Pendaftaran yang belum dibayar tidak boleh menghasilkan akun.');
        Mail::assertNothingSent();
    }

    #[Test]
    public function berpindah_ke_lunas_mengirim_keterangan_akun(): void
    {
        $p = $this->pendaftaran();

        $p->forceFill(['status' => 'paid'])->save();

        $this->assertNotNull(User::where('email', $p->email)->first());
        Mail::assertSent(AkunPesertaWebinarMail::class, 1);
    }

    #[Test]
    public function lunas_sejak_dibuat_juga_dikirimi(): void
    {
        /*
         * Salah satu dari empat jalur membuat barisnya LANGSUNG berstatus
         * paid. Tanpa kait pada `created`, jalur itu diam seluruhnya.
         */
        $p = $this->pendaftaran(['status' => 'paid']);

        $this->assertNotNull(User::where('email', $p->email)->first());
        Mail::assertSent(AkunPesertaWebinarMail::class, 1);
    }

    #[Test]
    public function menyimpan_lagi_tanpa_mengubah_status_tidak_mengirim_ulang(): void
    {
        $p = $this->pendaftaran();
        $p->forceFill(['status' => 'paid'])->save();

        Mail::assertSent(AkunPesertaWebinarMail::class, 1);

        /*
         * Baris yang sudah lunas disimpan lagi untuk hal lain — panitia
         * membetulkan nomor telepon, gerbang menuliskan kanal bayarnya.
         * Tanpa penjaga wasChanged(), tiap penyimpanan itu mengirim ulang
         * keterangan akun ke seluruh rombongan.
         */
        $p->forceFill(['telp' => '081200000000'])->save();
        $p->forceFill(['bayar_kanal' => 'VIRTUAL_ACCOUNT_BCA'])->save();

        Mail::assertSent(AkunPesertaWebinarMail::class, 1);
    }

    #[Test]
    public function batal_atau_kedaluwarsa_tidak_mengirim_apa_pun(): void
    {
        foreach (['cancel', 'expired'] as $status) {
            Mail::fake();
            $p = $this->pendaftaran();
            $p->forceFill(['status' => $status])->save();

            $this->assertNull(User::where('email', $p->email)->first(), $status);
            Mail::assertNotSent(AkunPesertaWebinarMail::class);
        }
    }

    #[Test]
    public function tiap_peserta_menerima_suratnya_sendiri(): void
    {
        $p = $this->pendaftaran(['jumlah_pendaftar' => 3]);
        $e2 = 'rekan2' . Str::random(6) . '@contoh.test';
        $e3 = 'rekan3' . Str::random(6) . '@contoh.test';
        $p->pesertaLain()->create(['urutan' => 1, 'nama' => 'Rekan Kedua', 'email' => $e2]);
        $p->pesertaLain()->create(['urutan' => 2, 'nama' => 'Rekan Ketiga', 'email' => $e3]);

        $p->fresh()->forceFill(['status' => 'paid'])->save();

        Mail::assertSent(AkunPesertaWebinarMail::class, 3);

        /*
         * Dan tiap surat hanya pergi ke SATU alamat, membawa SATU nama
         * pengguna. Surat berisi daftar seluruh rombongan akan membocorkan
         * nama pengguna orang lain — separuh dari kunci masuk mereka.
         */
        // strtolower(): alamatnya dinormalkan saat akun dibuat, supaya
        // "Budi@X.com" dan "budi@x.com" tidak jadi dua akun.
        foreach (array_map('strtolower', [$p->email, $e2, $e3]) as $alamat) {
            Mail::assertSent(AkunPesertaWebinarMail::class, function ($surat) use ($alamat) {
                return $surat->hasTo($alamat) && count($surat->to) === 1;
            });
        }
    }

    #[Test]
    public function surat_akun_baru_menyebut_sandi_surat_akun_lama_tidak(): void
    {
        $email = 'lama' . Str::random(8) . '@contoh.test';
        $lama = User::create([
            'full_name' => 'Peserta Lama', 'username' => 'lama_' . Str::random(8),
            'email' => $email, 'password' => bcrypt('SandiPilihanSendiri2026'), 'level' => 'user',
        ]);
        $lama->forceFill(['status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_PELANGGAN])->save();

        $this->pendaftaran(['email' => $email, 'status' => 'paid']);

        /*
         * Menyebutkan sandi bawaan kepada pemilik akun lama akan membuatnya
         * mencoba sandi itu, gagal, lalu mengira akunnya rusak — padahal
         * sandinya memang yang ia pilih sendiri dan tidak kami sentuh.
         */
        Mail::assertSent(AkunPesertaWebinarMail::class, function ($surat) {
            return $surat->baru === false;
        });
    }
}
