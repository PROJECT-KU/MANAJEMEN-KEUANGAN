<?php

namespace Tests\Feature\Doku;

use App\KategoriLayanan;
use App\Services\Doku;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar panitia menyebut kanal pembayarannya, bukan cuma "DOKU".
 *
 * `cara_bayar` hanya membedakan transfer manual dari gerbang. Lewat gerbang
 * ia selalu berbunyi "DOKU", padahal orangnya bisa membayar dengan VA
 * Mandiri, QRIS, OVO, atau kartu — dan "lewat apa" justru pertanyaan pertama
 * panitia saat satu pembayaran perlu ditelusuri.
 */
class KanalBayarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
    }

    /** @param array<string, mixed> $jawaban */
    private function jawabDoku(array $jawaban): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response($jawaban, 200)]);
    }

    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Kanal ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);

        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-KANAL-' . Str::random(6),
            'kategori_id' => $sesi->getKey(),
            'nama' => 'Pendaftar Kanal ' . Str::random(5),
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '130031',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    private function admin(): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Uji Kanal ' . $t, 'username' => 'knl_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $orang;
    }

    #[Test]
    public function nama_kanal_dirakit_dari_jawaban_doku(): void
    {
        $d = app(Doku::class);

        $this->assertSame('VA Bank Mandiri', $d->namaKanal([
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['name' => 'Bank Mandiri'],
            'channel' => ['id' => 'VIRTUAL_ACCOUNT_BANK_MANDIRI'],
        ]));

        // QRIS tetap huruf besar semua: "Qris" terbaca seperti salah ketik.
        $this->assertSame('QRIS', $d->namaKanal([
            'service' => ['id' => 'QRIS'], 'channel' => ['id' => 'QRIS'],
        ]));

        $this->assertSame('OVO', $d->namaKanal([
            'service' => ['id' => 'EMONEY'], 'acquirer' => ['name' => 'OVO'],
        ]));

        // Tidak ada keterangan sama sekali: null, bukan untaian kosong yang
        // nanti tercetak sebagai ruang kosong di layar panitia.
        $this->assertNull($d->namaKanal([]));
    }

    #[Test]
    public function kanalnya_tersimpan_saat_lunas_lewat_gerbang(): void
    {
        $this->jawabDoku([
            'transaction' => ['status' => 'SUCCESS'],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['name' => 'Bank Mandiri'],
        ]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        $this->assertSame('VA Bank Mandiri', $p->refresh()->bayar_kanal);
    }

    #[Test]
    public function layar_panitia_menyebut_kanalnya_bukan_doku(): void
    {
        $this->jawabDoku([
            'transaction' => ['status' => 'SUCCESS'],
            'service' => ['id' => 'QRIS'], 'channel' => ['id' => 'QRIS'],
        ]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        Http::fake();   // layar panitia tidak boleh ikut menanyakan apa pun

        $this->actingAs($this->admin())
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $p->id_transaksi]))
            ->assertOk()
            ->assertSee('QRIS');
    }

    #[Test]
    public function kanal_bayar_terbaca_dengan_nama_yang_sama_di_dua_tempat(): void
    {
        /*
         * Kueri gabungan layar daftar menamainya `kanal_bayar`; kolomnya
         * sendiri `bayar_kanal`, dan layar rincian memakai model mentah.
         * Tanpa nama kedua di model, tampilan yang sama harus ditulis dua
         * kali dengan dua nama berbeda untuk satu hal yang sama.
         */
        $p = $this->pendaftaran(['bayar_kanal' => 'VA Bank Mandiri']);

        $this->assertSame('VA Bank Mandiri', $p->refresh()->kanal_bayar);

        $baris = \App\Support\PendaftaranSemuaLayanan::kueri([])
            ->where('nomor', $p->id_transaksi)->first();

        $this->assertNotNull($baris);
        $this->assertSame('VA Bank Mandiri', $baris->kanal_bayar);
    }

    #[Test]
    public function yang_belum_punya_kanal_jatuh_ke_label_lamanya(): void
    {
        /*
         * Pendaftaran lama tidak punya nilainya, dan transfer manual memang
         * tidak pernah punya. Keduanya harus tetap terbaca, bukan kosong.
         */
        Http::fake();

        $p = $this->pendaftaran(['cara_bayar' => 'transfer', 'status' => 'paid']);

        $this->actingAs($this->admin())
            ->get(route('account.pendaftaran-layanan.index', ['cari' => $p->id_transaksi]))
            ->assertOk()
            ->assertSee('Transfer');
    }
}
