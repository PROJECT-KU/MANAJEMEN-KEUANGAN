<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifPendaftaranMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembayaran Webinar Eksklusif SELALU lewat gerbang pembayaran.
 *
 * Transfer manual dicabut 17 Okt 2026 atas keputusan pemiliknya. Yang
 * dijaga di sini dua hal yang berlawanan arah:
 *
 *   1. pendaftaran BARU tidak pernah lagi bertanda 'transfer', dan suratnya
 *      tidak lagi menyuruh orang mengirim bukti ke WhatsApp;
 *   2. pendaftaran LAMA yang bertanda 'transfer' tetap utuh — datanya masih
 *      ada, suratnya masih bisa dikirim ulang, dan panitia masih harus bisa
 *      menyelesaikannya.
 */
class HanyaGerbangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function gerbangSiap(): void
    {
        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');

        Http::fake(['*' => Http::response(
            ['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200
        )]);
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Gerbang ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    private function daftar(KategoriLayanan $sesi)
    {
        return $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Pendaftar ' . Str::random(6),
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'jumlah_pendaftar' => 1, 'peserta' => [], 'setuju' => '1',
        ]);
    }

    #[Test]
    public function pendaftaran_baru_selalu_lewat_gerbang(): void
    {
        $this->gerbangSiap();

        $sesi = $this->sesi();
        $this->daftar($sesi)->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->assertNotNull($p);
        $this->assertSame('doku', $p->cara_bayar,
            'Masih ada pendaftaran baru bertanda transfer; jalur itu sudah dicabut.');
    }

    #[Test]
    public function suratnya_tidak_lagi_menyuruh_kirim_bukti(): void
    {
        $this->gerbangSiap();

        $this->daftar($this->sesi())->assertRedirect();

        Mail::assertSent(WebinarEksklusifPendaftaranMail::class, function ($surat) {
            $isi = $surat->render();

            $this->assertStringNotContainsString('Kirim bukti transfernya', $isi,
                'Suratnya masih menyuruh mengirim bukti ke WhatsApp; uangnya akan '
                . 'dikirim ke rekening yang tidak lagi dicocokkan siapa pun.');

            $this->assertStringContainsString('Selesaikan pembayarannya di halaman itu', $isi);

            return true;
        });
    }

    #[Test]
    public function tanpa_kunci_gerbang_pendaftarannya_ditolak_terang_terangan(): void
    {
        /*
         * INI penjaga yang paling penting di berkas ini.
         *
         * Tanpa jalur transfer, gerbang yang belum disetel berarti TIDAK ADA
         * cara membayar sama sekali. Menerima pendaftarannya diam-diam akan
         * memotong kuota untuk orang yang tidak akan pernah bisa membayar,
         * dan tidak ada satu pun layar yang menyatakan ada yang salah.
         */
        config()->set('services.doku.client_id', '');
        config()->set('services.doku.secret_key', '');

        Http::fake();

        $sesi = $this->sesi();
        $sisaAwal = (int) $sesi->sisa_kuota;

        $this->daftar($sesi);

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'Pendaftarannya tetap tersimpan padahal tidak ada cara membayarnya.');

        $this->assertSame($sisaAwal, (int) $sesi->refresh()->sisa_kuota,
            'Kuotanya ikut terpotong untuk pendaftaran yang tidak bisa dibayar.');

        Mail::assertNothingSent();
    }

    #[Test]
    public function pendaftaran_lama_bertanda_transfer_tetap_utuh(): void
    {
        /*
         * Mencabut jalurnya BUKAN menghapus yang sudah ada. Barisnya masih
         * di basis data, panitia masih harus bisa menyelesaikannya, dan
         * halaman statusnya masih harus menyebut cara bayar lamanya.
         */
        Http::fake();

        $p = WebinarEksklusifPendaftaran::create([
            'id_transaksi' => 'WE-LAMA-' . Str::random(6),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Lama',
            'email' => 'lama' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129770',
            'kode_unik' => '770',
            'cara_bayar' => 'transfer',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHours(20),
        ]);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Cara membayar');

        // Dan ia TIDAK ikut ditanyakan ke gerbang — tidak punya tagihan di sana.
        Http::assertNothingSent();
    }
}
