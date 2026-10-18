<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\Pendaftaran\UbahStatusPendaftaran;
use App\KategoriLayanan;
use App\Mail\WebinarEksklusifLunasMail;
use App\Support\PendaftaranSemuaLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pelunasan menghasilkan surat berikut KWITANSINYA, dari jalur mana pun.
 *
 * Dua jalur menuju keadaan yang sama, dan dulu keduanya mengirim surat yang
 * BERBEDA: lewat DOKU yang datang surat bukti pendaftaran dalam mode lunas,
 * sementara dari layar panitia yang datang surat perubahan status umum —
 * yang tidak menyebut nominal maupun cara bayarnya sama sekali. Tidak satu
 * pun di antaranya membawa kwitansi, padahal surat pendaftarannya sendiri
 * sudah berjanji "kami kirimkan surat penanda lunas".
 */
class KwitansiLunasTest extends TestCase
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
            'nama' => 'Sesi Kwitansi ' . Str::random(5),
            'nama_ke' => (string) random_int(4000, 4999),
            'mulai' => now()->addDays(9)->toDateString(),
            'selesai' => now()->addDays(9)->toDateString(),
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
            'id_transaksi' => 'WE-KWI-' . Str::random(6),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Kwitansi',
            'email' => 'kwi' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '232931',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function lunas_lewat_doku_membawa_kwitansi(): void
    {
        Mail::fake();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS'],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['name' => 'Bank Mandiri'],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.webinareksklusif.keadaan', $p->getKey()))->assertOk();

        Mail::assertSent(WebinarEksklusifLunasMail::class, function ($surat) {
            $surat->build();

            $this->assertCount(1, $surat->rawAttachments,
                'Suratnya terkirim tanpa kwitansi.');

            return true;
        });
    }

    #[Test]
    public function lunas_dari_layar_mis_mengirim_surat_yang_sama(): void
    {
        /*
         * Yang dijaga PILIHAN KELASNYA, bukan sekadar "ada surat".
         *
         * Daftar suratnya dulu kosong untuk webinar, dan daftar kosong
         * TIDAK berarti diam — ia jatuh ke surat perubahan status umum.
         * Jadi panitia yang menandai lunas dari layarnya mengirimkan surat
         * yang berbeda dari yang diterima orang yang membayar lewat DOKU,
         * untuk kejadian yang sama persis.
         */
        $this->assertSame(
            WebinarEksklusifLunasMail::class,
            PendaftaranSemuaLayanan::suratUntuk('webinar_eksklusif', 'paid'),
            'Pelunasan dari layar panitia tidak lagi mengirim surat lunas '
            . 'berkwitansi; yang datang surat status umum tanpa nominal.'
        );

        Mail::fake();

        $p = $this->pendaftaran();

        (new UbahStatusPendaftaran)->jalankan(
            'webinar_eksklusif',
            (string) $p->getKey(),
            'paid',
            'Panitia Uji'
        );

        Mail::assertSent(WebinarEksklusifLunasMail::class, 1);
    }

    #[Test]
    public function kwitansinya_pdf_sah_dan_menyebut_nomor_serta_nominalnya(): void
    {
        $p = $this->pendaftaran([
            'status' => 'paid',
            'bayar_pada' => now(),
            'bayar_kanal' => 'Virtual Account Mandiri',
        ]);

        $surat = new WebinarEksklusifLunasMail($p, KategoriLayanan::find($p->kategori_id));
        $surat->build();

        $lampiran = $surat->rawAttachments[0];

        $this->assertStringStartsWith('%PDF', $lampiran['data'],
            'Lampirannya bukan PDF yang sah.');

        $this->assertSame(
            str_replace('WE-', 'KW-', (string) $p->id_transaksi) . '.pdf',
            $lampiran['name'],
            'Nama berkasnya tidak menurunkan nomor pendaftarannya; dua kwitansi '
            . 'di folder unduhan yang sama akan bertabrakan.'
        );

        // Isi kwitansinya diperiksa dari TAMPILANNYA, bukan dari PDF-nya:
        // teks di dalam PDF tersimpan tersandi dan tidak bisa dicari apa adanya.
        $html = view('public.webinar_eksklusif.kwitansi_pdf', [
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => \Riskihajar\Terbilang\Facades\Terbilang::make(232931, ' rupiah'),
        ])->render();

        $this->assertStringContainsString($p->id_transaksi, $html);
        $this->assertStringContainsString('232.931', $html);
        $this->assertStringContainsString('LUNAS', $html);
    }

    #[Test]
    public function kwitansinya_berlogo_rumah_scopus_bukan_mis(): void
    {
        /*
         * Kwitansi ini sebagian diserahkan ke bendahara kantor penerimanya
         * untuk penggantian biaya. Merek yang salah di dokumen itu bukan
         * soal selera.
         */
        $p = $this->pendaftaran(['status' => 'paid', 'bayar_pada' => now()]);

        $html = view('public.webinar_eksklusif.kwitansi_pdf', [
            'pendaftaran' => $p,
            'sesi' => KategoriLayanan::find($p->kategori_id),
            'terbilang' => 'dua ratus rupiah',
        ])->render();

        $this->assertStringNotContainsString('logo-email.png', $html,
            'Kwitansinya memakai logo MIS.');
        $this->assertStringContainsString('logo-rsc-email.png', $html);
    }

    #[Test]
    public function terbilangnya_bahasa_indonesia(): void
    {
        /*
         * config/terbilang.php dulu tidak menyebut 'locale' sama sekali,
         * jadi yang terbaca nilai bawaan paketnya — 'en' — dan ia MENANG
         * atas app.locale yang sudah 'id', sebab ?: hanya mundur saat
         * nilainya kosong.
         *
         * Yang tercetak di bawah kata "Terbilang:" adalah "two hundred
         * thirty-two thousand ... rupiah", dan bukan cuma di kwitansi ini:
         * faktur Scopus Camp, faktur Bibliometrik, slip gaji, dan slip camp
         * memakai pembantu yang sama.
         */
        $hasil = (string) \Riskihajar\Terbilang\Facades\Terbilang::make(232931, ' rupiah');

        $this->assertStringContainsString('dua ratus tiga puluh dua ribu', $hasil,
            'Terbilangnya kembali berbahasa Inggris: ' . $hasil);
    }

    #[Test]
    public function kwitansinya_tidak_menyisipkan_font(): void
    {
        /*
         * Dompdf MENYISIPKAN font yang bukan bawaan PDF, utuh, ke tiap
         * berkas. Terukur pada kwitansi yang sama: DejaVu Sans membuatnya
         * 1.289.545 bita, sementara Helvetica 20.965 — enam puluh satu kali
         * lebih berat, pada lampiran yang dikirim ke tiap orang yang
         * membayar.
         *
         * Batasnya 200 KB, longgar terhadap perubahan isi tetapi jauh di
         * bawah berkas yang menyisipkan satu font pun.
         */
        $p = $this->pendaftaran(['status' => 'paid', 'bayar_pada' => now()]);

        $surat = new WebinarEksklusifLunasMail($p, KategoriLayanan::find($p->kategori_id));
        $surat->build();

        $ukuran = strlen($surat->rawAttachments[0]['data']);

        $this->assertLessThan(200_000, $ukuran,
            'Kwitansinya ' . number_format($ukuran) . ' bita — hampir pasti ada '
            . 'font yang disisipkan utuh ke dalamnya.');
    }
}
