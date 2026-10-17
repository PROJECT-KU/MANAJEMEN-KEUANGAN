<?php

namespace Tests\Feature\Doku;

use App\Services\Doku;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Rincian tagihan DOKU harus berjumlah sama dengan totalnya.
 *
 * DOKU menolak dengan "AMOUNT NOT MATCH" kalau `order.amount` berbeda dari
 * jumlah `line_items`-nya. Cacat ini tidak pernah kelihatan sampai 17 Okt
 * 2026 sebab kredensialnya belum pernah diisi, jadi jalur DOKU tidak pernah
 * hidup sekali pun.
 *
 * Yang akan menabraknya justru pendaftaran yang PAKAI POTONGAN: totalnya
 * sudah dikurangi, sementara rinciannya masih harga penuh dikali jumlah
 * peserta. Terukur di basis data ini: 62 pendaftaran memakai kode diskon.
 * Semuanya akan gagal membayar daring, dan yang paling mungkin disalahkan
 * orangnya sendiri.
 */
class RincianTagihanDokuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
        config()->set('services.doku.produksi', false);
    }

    /** @param array<string, mixed> $ubah */
    private function tagih(array $ubah = []): array
    {
        return app(Doku::class)->buatTagihan(array_merge([
            'nomor' => 'UJI-0001',
            'jumlah' => 258000,
            'harga_satuan' => 129000,
            'jumlah_peserta' => 2,
            'judul' => 'Express Writing',
            'nama' => 'Pendaftar Uji',
            'email' => 'uji@contoh.test',
            'telp' => '6281234567890',
            'kembali' => 'https://contoh.test/status',
        ], $ubah));
    }

    /** Isi badan permintaan yang BENAR-BENAR dikirim. */
    private function terkirim(): array
    {
        $kiriman = null;

        Http::recorded(function ($permintaan) use (&$kiriman) {
            $kiriman = json_decode($permintaan->body(), true);
        });

        $this->assertNotNull($kiriman, 'Tidak ada permintaan yang dikirim ke DOKU.');

        return $kiriman;
    }

    #[Test]
    public function tanpa_potongan_rinciannya_satu_baris(): void
    {
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200)]);

        $hasil = $this->tagih();

        $this->assertTrue($hasil['berhasil']);

        $baris = $this->terkirim()['order']['line_items'];

        $this->assertCount(1, $baris);
        $this->assertSame(129000, $baris[0]['price']);
        $this->assertSame(2, $baris[0]['quantity']);
    }

    #[Test]
    public function potongan_jadi_baris_tersendiri_bernilai_minus(): void
    {
        /*
         * Bernilai minus, BUKAN dengan memperkecil harga satuannya: yang
         * membayar harus melihat harga asli dan potongannya terpisah, sama
         * seperti yang tertulis di borang pendaftarannya.
         */
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200)]);

        $hasil = $this->tagih([
            'jumlah' => 208000,
            'potongan' => 50000,
            'label_potongan' => 'Potongan ALUMNI',
        ]);

        $this->assertTrue($hasil['berhasil'], 'Tagihan berpotongan ditolak sebelum dikirim.');

        $isi = $this->terkirim();
        $baris = $isi['order']['line_items'];

        $this->assertCount(2, $baris);
        $this->assertSame(129000, $baris[0]['price'], 'Harga satuannya ikut dikecilkan.');
        $this->assertSame(-50000, $baris[1]['price']);
        $this->assertSame('Potongan ALUMNI', $baris[1]['name']);

        $this->assertSame(
            (int) $isi['order']['amount'],
            array_sum(array_map(fn ($b) => $b['price'] * $b['quantity'], $baris)),
            'Rinciannya tidak berjumlah sama dengan amount; DOKU akan menolak '
            . 'dengan "AMOUNT NOT MATCH".'
        );
    }

    #[Test]
    public function kode_unik_ikut_jadi_rincian(): void
    {
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200)]);

        $this->tagih(['jumlah' => 258086, 'kode_unik' => 86]);

        $baris = $this->terkirim()['order']['line_items'];

        $this->assertCount(2, $baris);
        $this->assertSame(86, $baris[1]['price']);
    }

    #[Test]
    public function orangnya_dikembalikan_sendiri_ke_situs_kita(): void
    {
        /*
         * auto_redirect = false adalah BAWAAN DOKU, dan dengan itu halaman
         * hasil DOKU ditampilkan lalu menunggu orang menekan "Back to
         * Merchant".
         *
         * Banyak yang tidak menekannya: mereka sudah membaca "Payment
         * Successful" lalu menutup tabnya — dan tidak pernah melihat nomor
         * pendaftarannya, tautan grup, atau kabar bahwa tautan masuk dikirim
         * ke emailnya. Kegagalan yang tidak terlihat sebagai kegagalan di
         * mana pun: pembayarannya berhasil, orangnya saja yang hilang.
         */
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://bayar.test/x']]], 200)]);

        $this->tagih(['kembali' => 'https://contoh.test/status/abc']);

        $pesanan = $this->terkirim()['order'];

        $this->assertTrue($pesanan['auto_redirect'] ?? false,
            'auto_redirect tidak dikirim; DOKU akan berhenti di halaman '
            . 'hasilnya sendiri dan menunggu orang menekan tombol.');

        $this->assertSame('https://contoh.test/status/abc', $pesanan['callback_url'],
            'Alamat kembalinya tidak ikut, jadi tidak ada tempat untuk dituju.');
    }

    #[Test]
    public function rincian_yang_meleset_tidak_pernah_dikirim(): void
    {
        /*
         * INI penjaga yang paling penting.
         *
         * Kalau rinciannya tidak cocok, yang benar BUKAN mengirim jumlah
         * rinciannya begitu saja — itu menagih orang dengan angka yang
         * berbeda dari yang tercatat di basis data. Gagal membuat tagihan
         * jauh lebih baik: pemanggilnya sudah menyiapkan jalan mundur ke
         * transfer manual.
         */
        Http::fake(['*' => Http::response([], 200)]);

        $hasil = $this->tagih(['jumlah' => 999999]);

        $this->assertFalse($hasil['berhasil']);
        $this->assertNull($hasil['url']);

        Http::assertNothingSent();
    }

    #[Test]
    public function tanpa_kredensial_tidak_ada_permintaan_sama_sekali(): void
    {
        config()->set('services.doku.client_id', '');
        config()->set('services.doku.secret_key', '');

        Http::fake();

        $hasil = $this->tagih();

        $this->assertFalse($hasil['berhasil']);
        $this->assertSame('Pembayaran daring belum aktif.', $hasil['pesan']);

        Http::assertNothingSent();
    }
}
