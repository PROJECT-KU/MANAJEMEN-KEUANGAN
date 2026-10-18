<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\WebinarEksklusif\KabariKursiDilepas;
use App\KategoriLayanan;
use App\Mail\WebinarEksklusifGagalBayarMail;
use App\Mail\WebinarEksklusifKedaluwarsaMail;
use App\Support\PendaftaranSemuaLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembayaran yang GAGAL dikabari, dan dengan kalimatnya sendiri.
 *
 * Dua lubang yang keduanya merugikan orangnya:
 *
 *   - gerbang menjawab "failed" — kartu ditolak, saldo kurang, transaksi
 *     dihentikan bank — dan statusnya dipetakan ke 'expired', jadi yang
 *     terkirim surat "batas waktu pembayarannya sudah lewat". Orang yang
 *     kartunya baru ditolak membaca bahwa ia TERLAMBAT, lalu berhenti
 *     mencoba atas sesuatu yang sebenarnya bisa diulang sekarang juga;
 *   - gerbang menjawab "cancel" dan tidak ada surat sama sekali.
 */
class GagalBayarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Gagal ' . Str::random(5),
            'nama_ke' => (string) random_int(2000, 2999),
            'mulai' => now()->addDays(11)->toDateString(),
            'selesai' => now()->addDays(11)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '19',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-GAGAL-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Gagal',
            'email' => 'gagal' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129000',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function gerbang_yang_menolak_pembayaran_mengirim_surat_gagal(): void
    {
        /*
         * Lewat rutenya, bukan dengan memanggil aksinya: yang diuji justru
         * bahwa jalur gerbangnya SAMPAI ke aksi itu.
         */
        Mail::fake();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'FAILED'],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))->assertOk();

        Mail::assertSent(WebinarEksklusifGagalBayarMail::class,
            fn ($surat) => $surat->hasTo($p->email));

        Mail::assertNotSent(WebinarEksklusifKedaluwarsaMail::class);
    }

    #[Test]
    public function yang_dibatalkan_gerbang_tidak_lagi_didiamkan(): void
    {
        Mail::fake();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'CANCEL'],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))->assertOk();

        $this->assertSame('cancel', $p->refresh()->status);

        Mail::assertSent(WebinarEksklusifGagalBayarMail::class, 1);
    }

    #[Test]
    public function batas_waktu_yang_lewat_tetap_memakai_kalimatnya_sendiri(): void
    {
        /*
         * Pemisahnya harus bekerja DUA ARAH. Dibuat terlalu longgar, semua
         * akhir pendaftaran jadi "pembayaran gagal" — dan yang memang
         * kehabisan waktu tanpa pernah mencoba membayar akan membaca
         * kalimat tentang dana tertahan yang tidak pernah ada.
         */
        Mail::fake();

        $p = $this->pendaftaran([
            'kedaluwarsa_pada' => now()->subMinutes(5),
        ]);

        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa();

        Mail::assertSent(WebinarEksklusifKedaluwarsaMail::class, 1);
        Mail::assertNotSent(WebinarEksklusifGagalBayarMail::class);
    }

    #[Test]
    public function layar_panitia_memakai_surat_yang_sama(): void
    {
        $this->assertSame(
            WebinarEksklusifGagalBayarMail::class,
            PendaftaranSemuaLayanan::suratUntuk('webinar_eksklusif', 'cancel'),
            'Pembatalan dari layar panitia kembali memakai surat status umum, '
            . 'yang tidak menyebut nominal, tidak menyebut dana yang mungkin '
            . 'tertahan, dan tidak memberi jalan mendaftar lagi.'
        );
    }

    #[Test]
    public function suratnya_menyebut_dana_yang_mungkin_tertahan(): void
    {
        /*
         * Kalimat terpenting di surat ini, dan sebabnya tidak kelihatan dari
         * kodenya: sebagian gerbang menahan dana sesaat lalu melepasnya
         * sendiri. Yang melihat saldonya berkurang padahal pendaftarannya
         * gagal akan mengira uangnya hilang — lalu tidak menghubungi siapa
         * pun, dan tidak mendaftar lagi.
         */
        $p = $this->pendaftaran(['status' => 'cancel', 'bayar_status' => 'cancel']);

        $html = (new WebinarEksklusifGagalBayarMail($p, KategoriLayanan::find($p->kategori_id)))->render();

        $this->assertStringContainsString('Saldo Anda terpotong?', $html);
        $this->assertStringContainsString('dikembalikan sendiri oleh bank', $html);
    }

    #[Test]
    public function suratnya_tidak_menyalahkan_orangnya(): void
    {
        $p = $this->pendaftaran(['status' => 'cancel', 'bayar_status' => 'failed']);

        $html = (new WebinarEksklusifGagalBayarMail($p, KategoriLayanan::find($p->kategori_id)))->render();

        $this->assertStringContainsString('bukan kesalahan Anda', $html);
        $this->assertStringNotContainsString('terlambat', mb_strtolower($html),
            'Surat gagal bayar menyebut keterlambatan; itu kalimat untuk yang '
            . 'kehabisan waktu, bukan untuk yang kartunya ditolak.');
    }
}
