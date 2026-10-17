<?php

namespace Tests\Feature\Doku;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Halaman status BERTANYA ke gerbang pembayaran, tidak sekadar menunggu.
 *
 * Pemberitahuan dari DOKU berjalan dari peladen ke peladen. Ia tetap jadi
 * pegangan utama — ia yang datang walau halamannya sudah ditutup — tetapi
 * ia tidak selalu cepat, dan di mesin pengembang tidak pernah sampai sama
 * sekali: `localhost` tidak bisa dihubungi dari luar. Terukur 17 Okt 2026,
 * NOL pemberitahuan masuk sepanjang hari padahal sandbox berkali-kali
 * sukses.
 *
 * Jadi selama pembayarnya masih menatap layar, kita yang bertanya. Terukur
 * sesudah perubahan ini: halaman status memajang "sudah lunas" 0,29 detik
 * sesudah dibuka, tanpa satu putaran tanya pun.
 */
class TanyaGerbangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
    }

    private function jawabDoku(string $status): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response(
            ['transaction' => ['status' => $status]], 200
        )]);
    }

    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Tanya ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);

        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-TANYA-' . Str::random(6),
            'kategori_id' => $sesi->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '130031',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function tanda_tangan_get_tidak_memakai_digest(): void
    {
        /*
         * INI bagian yang paling mudah salah dan paling sulit didiagnosis.
         *
         * Aturan DOKU: permintaan GET tidak menyertakan Digest. Menyertakannya
         * dengan badan kosong membuat tanda tangannya tidak cocok, dan
         * jawabannya 401 tanpa menyebut bagian mana yang salah — lalu mudah
         * disalahartikan sebagai "kredensialnya keliru".
         */
        $this->jawabDoku('success');

        app(\App\Services\Doku::class)->periksaStatus('WE-UJI-0001');

        Http::assertSent(function ($permintaan) {
            $this->assertSame('', $permintaan->body(), 'Permintaan GET membawa badan.');

            /*
             * Tanda tangannya DIHITUNG ULANG di sini, tanpa Digest, lalu
             * dibandingkan.
             *
             * Versi pertama penjaga ini cuma memeriksa badan kosong dan
             * awalan "HMACSHA256=" — dan keduanya tetap benar walau Digest
             * ikut masuk ke bahan tanda tangannya, sebab Digest tidak
             * dikirim sebagai kepala melainkan dilebur ke dalam HMAC.
             * Terbukti: menyalakan Digest tetap membuat penjaganya hijau.
             */
            $jalur = '/orders/v1/status/WE-UJI-0001';

            $bahan = implode("\n", [
                'Client-Id:BRN-UJI-0000',
                'Request-Id:' . $permintaan->header('Request-Id')[0],
                'Request-Timestamp:' . $permintaan->header('Request-Timestamp')[0],
                'Request-Target:' . $jalur,
            ]);

            $harapan = 'HMACSHA256=' . base64_encode(
                hash_hmac('sha256', $bahan, 'SK-UJI-0000', true)
            );

            $this->assertSame($harapan, $permintaan->header('Signature')[0],
                'Tanda tangan GET-nya tidak cocok dengan bahan tanpa Digest — '
                . 'DOKU akan menjawab 401 tanpa menyebut bagian mana yang salah.');

            return str_contains($permintaan->url(), $jalur);
        });
    }

    #[Test]
    public function halaman_status_langsung_lunas_tanpa_menunggu(): void
    {
        $this->jawabDoku('success');

        $p = $this->pendaftaran();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('sudah lunas')
            ->assertDontSee('Menunggu konfirmasi pembayaran');

        $this->assertSame('paid', $p->refresh()->status);
    }

    #[Test]
    public function kolomnya_sama_dengan_yang_diisi_pemberitahuan(): void
    {
        /*
         * Dua jalur menuju satu keadaan — pemberitahuan dan pertanyaan ini.
         * Mana pun yang tiba lebih dulu, hasilnya tidak boleh bisa dibedakan;
         * kalau berbeda, layar admin menampilkan dua jenis "lunas".
         */
        $this->jawabDoku('settlement');

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->assertJson(['lunas' => true, 'selesai' => true]);

        $p->refresh();

        $this->assertSame('paid', $p->status);
        $this->assertSame('settlement', $p->bayar_status);
        $this->assertNotNull($p->bayar_pada);
    }

    #[Test]
    public function gagal_di_gerbang_mengembalikan_kuotanya(): void
    {
        $this->jawabDoku('expired');

        $p = $this->pendaftaran();
        // (int) pada UUID selalu 0 — versi pertama baris ini memakainya
        // sebagai syarat, jadi $sisaAwal selalu 0 dan yang dibandingkan
        // bukan lagi perubahan kuotanya.
        $sisaAwal = (int) KategoriLayanan::find($p->kategori_id)->sisa_kuota;

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        $this->assertSame('expired', $p->refresh()->status);
        $this->assertSame(
            $sisaAwal + 1,
            (int) KategoriLayanan::find($p->kategori_id)->sisa_kuota,
            'Kursinya tidak dikembalikan; kuota ikut hilang bersama pembayaran yang gagal.'
        );
    }

    #[Test]
    public function doku_yang_tidak_menjawab_tidak_pernah_dianggap_lunas(): void
    {
        /*
         * Nomor yang belum pernah dibayar memang belum dikenal DOKU, dan
         * jawabannya 404. Membaca itu sebagai apa pun selain "belum tahu"
         * berarti menandai lunas sesuatu yang belum dibayar.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))
            ->assertOk()->assertJson(['lunas' => false, 'selesai' => false]);

        $this->assertSame('pending', $p->refresh()->status);
    }

    #[Test]
    public function transfer_manual_tidak_pernah_ditanyakan_ke_gerbang(): void
    {
        /*
         * Yang transfer manual tidak punya tagihan di gerbang untuk
         * ditanyakan. Menanyakannya berarti satu panggilan keluar tiap kali
         * halamannya dibuka, untuk jawaban yang sudah pasti kosong.
         */
        Http::fake();

        $p = $this->pendaftaran(['cara_bayar' => 'transfer']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))->assertOk();

        Http::assertNothingSent();
    }

    #[Test]
    public function yang_sudah_lunas_tidak_ditanyakan_lagi(): void
    {
        Http::fake();

        $p = $this->pendaftaran(['status' => 'paid']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))->assertOk();

        Http::assertNothingSent();
    }
}
