<?php

namespace Tests\Feature\Doku;

use App\KategoriLayanan;
use App\PendaftaranJejak;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Membatalkan di layar panitia IKUT MENUTUP tagihannya di DOKU.
 *
 * Sebelum ini pembatalan berhenti di basis data kita sendiri: kursinya
 * dikembalikan ke kuota, tetapi tagihannya di DOKU hidup terus sampai
 * tenggatnya habis. Pendaftar yang tabnya masih terbuka bisa tetap membayar
 * kursi yang sudah diberikan kepada orang lain.
 *
 * Terbukti di sandbox 18 Okt 2026: sesudah panggilan ini, alamat halaman
 * bayarnya dialihkan ke /checkout-link-v2/cancelled/page berbunyi "Order
 * Cancelled — This order is no longer available", tanpa satu pun metode
 * pembayaran. Tagihan pembanding yang tidak ditutup masih menampilkan
 * daftar metode lengkap dengan hitung mundurnya.
 *
 * DUA BATAS yang tidak bisa dihilangkan, dan keduanya diuji di sini:
 *
 *   1. hanya Virtual Account dan QRIS yang bisa dibatalkan DOKU. Kartu dan
 *      e-wallet tidak — jadi penjagaan di penerima pemberitahuan tetap
 *      wajib ada, lihat UangMasukSetelahBatalTest;
 *   2. tagihan yang dibuat SEBELUM kolom bayar_permintaan_id ada tidak
 *      punya original_request_id, dan tidak akan pernah bisa ditutup.
 */
class TutupTagihanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
        config()->set('services.doku.produksi', false);

        Mail::fake();
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Tutup ' . Str::random(5),
            'nama_ke' => (string) random_int(2000, 2999),
            'mulai' => now()->addDays(8)->toDateString(),
            'selesai' => now()->addDays(8)->toDateString(),
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
            'id_transaksi' => 'WE-TUTUP-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Tutup',
            'email' => 'tutup' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129444',
            'cara_bayar' => 'doku',
            'bayar_rujukan' => 'WE-TUTUP-RUJUKAN',
            'bayar_permintaan_id' => 'a1b2c3d4-0000-4444-8888-aaaabbbbcccc',
            'bayar_url' => 'https://bayar.uji.test/tutup',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    private function batalkan(WebinarEksklusifPendaftaran $p, string $status = 'cancel'): array
    {
        return app(\App\Actions\Pendaftaran\UbahStatusPendaftaran::class)
            ->jalankan('webinar_eksklusif', (string) $p->getKey(), $status, 'Rina Panitia');
    }

    #[Test]
    public function membatalkan_mengirim_permintaan_penutupan_ke_doku(): void
    {
        Http::fake(['*' => Http::response(['message' => ['SUCCESS']], 200)]);

        $p = $this->pendaftaran();

        $this->batalkan($p);

        Http::assertSent(function ($permintaan) use ($p) {
            if (! str_contains($permintaan->url(), '/checkout/v3/cancellations')) {
                return false;
            }

            $isi = json_decode($permintaan->body(), true);

            /*
             * KEDUANYA wajib. Nomor faktur saja tidak cukup — DOKU menolak
             * tanpa original_request_id, dan penolakannya tidak menyebut
             * medan mana yang kurang.
             */
            $this->assertSame($p->id_transaksi, data_get($isi, 'order.invoice_number'));
            $this->assertSame(
                'a1b2c3d4-0000-4444-8888-aaaabbbbcccc',
                data_get($isi, 'payment.original_request_id'),
                'Yang dikirim bukan Request-Id asli tagihannya; DOKU tidak akan '
                . 'menemukan pesanan yang dimaksud.'
            );

            $this->assertNotEmpty(data_get($isi, 'note'));

            return true;
        });
    }

    #[Test]
    public function tanpa_nomor_permintaan_tidak_ada_permintaan_yang_dikirim(): void
    {
        /*
         * Tagihan peninggalan: dibuat sebelum kolomnya ada. Menembakkan
         * permintaan tanpa original_request_id hanya menghasilkan penolakan
         * DOKU yang terbaca seperti gangguan — dan menutupi sebab yang
         * sebenarnya, yaitu bahwa tagihan itu memang tidak bisa ditutup.
         */
        Http::fake();

        $p = $this->pendaftaran(['bayar_permintaan_id' => null]);

        $this->batalkan($p);

        Http::assertNothingSent();

        $jejak = PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())
            ->where('aksi', 'tagihan-gagal')->first();

        $this->assertNotNull($jejak,
            'Tagihannya tidak tertutup dan tidak ada satu pun catatan tentangnya; '
            . 'panitia mengira pembatalannya sudah menutup segalanya.');

        $this->assertStringContainsString('sebelum nomor permintaannya disimpan',
            (string) $jejak->ringkasan);
    }

    #[Test]
    public function penolakan_doku_tidak_membatalkan_perpindahan_statusnya(): void
    {
        /*
         * Kanal kartu dan e-wallet MEMANG tidak bisa dibatalkan, jadi
         * penolakan di sini keadaan yang normal — bukan alasan menolak
         * keputusan panitia. Yang menanggung kalau statusnya ikut gagal
         * adalah orang yang paling tidak bisa memperbaikinya.
         */
        Http::fake(['*' => Http::response(['error' => 'not eligible'], 400)]);

        $p = $this->pendaftaran();
        $sisaAwal = (int) $p->angkatan->sisa_kuota;

        $hasil = $this->batalkan($p);

        $this->assertTrue($hasil['berhasil'],
            'Perpindahan statusnya ikut gagal hanya karena gerbang pembayaran menolak.');

        $this->assertSame('cancel', $p->fresh()->status);

        $this->assertSame($sisaAwal + 1, (int) $p->angkatan->fresh()->sisa_kuota,
            'Kursinya tidak dikembalikan; pembatalannya setengah jalan.');

        $jejak = PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())
            ->where('aksi', 'tagihan-gagal')->first();

        $this->assertNotNull($jejak);
        $this->assertStringContainsString('TIDAK tertutup', (string) $jejak->ringkasan,
            'Kegagalannya tidak dinyatakan; tagihannya masih hidup dan tidak ada '
            . 'yang tahu.');
    }

    #[Test]
    public function keberhasilannya_ikut_tercatat(): void
    {
        Http::fake(['*' => Http::response(['message' => ['SUCCESS']], 200)]);

        $p = $this->pendaftaran();

        $this->batalkan($p);

        $jejak = PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())
            ->where('aksi', 'tagihan-tutup')->first();

        $this->assertNotNull($jejak, 'Penutupan tagihannya tidak meninggalkan jejak.');
        $this->assertStringContainsString('tidak bisa dibayar lagi', (string) $jejak->ringkasan);
    }

    #[Test]
    public function kedaluwarsa_juga_menutup_tagihannya(): void
    {
        /*
         * Panitia bisa menandai kedaluwarsa SEBELUM tenggatnya tiba. Kalau
         * hanya 'cancel' yang menutup tagihan, jalur itu meninggalkan
         * tagihan hidup dengan cara yang sama persis.
         */
        Http::fake(['*' => Http::response(['message' => ['SUCCESS']], 200)]);

        $this->batalkan($this->pendaftaran(), 'expired');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/checkout/v3/cancellations'));
    }

    #[Test]
    public function yang_transfer_manual_tidak_ditanyakan_ke_doku(): void
    {
        /*
         * Peninggalan jalur transfer; ia tidak punya tagihan di sana untuk
         * ditutup, dan permintaan untuknya hanya menghasilkan penolakan yang
         * membuat log penuh keluhan palsu.
         *
         * Nomor permintaannya sengaja DIISI, walaupun baris transfer
         * sungguhan tidak akan pernah memilikinya.
         *
         * Versi pertama penjaga ini mengosongkannya, dan tetap HIJAU ketika
         * pemeriksaan cara_bayar saya cabut — yang menahan permintaannya
         * ternyata ketiadaan nomor permintaan, bukan pemeriksaan yang
         * sedang diuji. Dua sebab yang berdiri sendiri, dan salah satunya
         * bisa berubah tanpa yang lain ikut berubah.
         */
        Http::fake();

        $this->batalkan($this->pendaftaran([
            'cara_bayar' => 'transfer',
        ]));

        Http::assertNothingSent();
    }

    #[Test]
    public function yang_sudah_batal_tidak_ditutup_dua_kali(): void
    {
        /*
         * Batal lalu ditandai kedaluwarsa: tagihannya sudah tertutup pada
         * langkah pertama, dan permintaan kedua hanya menambah satu
         * penolakan DOKU yang tidak berarti apa-apa.
         */
        Http::fake(['*' => Http::response(['message' => ['SUCCESS']], 200)]);

        $p = $this->pendaftaran(['status' => 'cancel']);

        $this->batalkan($p, 'expired');

        Http::assertNothingSent();
    }
}
