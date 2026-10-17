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
 * Yang sudah membayar dikabari, bukan dibiarkan memegang surat "BELUM DIBAYAR".
 *
 * Tampilan suratnya sudah lama bisa berbunyi "SUDAH DIBAYAR" — yang tidak
 * ada adalah yang memanggilnya saat keadaannya berubah. Surat hanya dikirim
 * saat mendaftar dan saat pendaftarnya sendiri menekan "kirim ulang", jadi
 * orang yang sudah membayar tetap hanya memegang surat yang menyuruhnya
 * membayar.
 */
class SuratLunasTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');

        Mail::fake();
    }

    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Surat ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);

        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-SURAT-' . Str::random(6),
            'kategori_id' => $sesi->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129770',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function surat_dikirim_begitu_pembayarannya_masuk(): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS'],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['name' => 'Bank Mandiri'],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        Mail::assertSent(WebinarEksklusifPendaftaranMail::class, 1);
    }

    #[Test]
    public function suratnya_berbunyi_sudah_dibayar(): void
    {
        /*
         * Bukan sekadar "ada surat terkirim": yang dikirim harus surat yang
         * MENGETAHUI pembayarannya sudah masuk. Dikirim sebelum statusnya
         * tersimpan, isinya masih menyuruh orangnya membayar — dan itu
         * persis keluhan yang memulai perbaikan ini.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS'],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        Mail::assertSent(WebinarEksklusifPendaftaranMail::class, function ($surat) {
            $isi = $surat->render();

            $this->assertStringContainsString('SUDAH DIBAYAR', $isi);
            $this->assertStringNotContainsString('BELUM DIBAYAR', $isi);

            return true;
        });
    }

    #[Test]
    public function tidak_dikirim_dua_kali_saat_halamannya_dibuka_berulang(): void
    {
        /*
         * Halaman status bertanya ke gerbang TIAP kali dibuka, dan panelnya
         * bertanya tiap tiga detik. Surat yang dikirim di luar cabang
         * perpindahan keadaan akan terkirim sekali per pertanyaan.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS'],
        ], 200)]);

        $p = $this->pendaftaran();

        for ($i = 0; $i < 4; $i++) {
            $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();
        }

        Mail::assertSent(WebinarEksklusifPendaftaranMail::class, 1);
    }

    #[Test]
    public function yang_belum_lunas_tidak_dikirimi_apa_pun(): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        Mail::assertNothingSent();
    }
}
