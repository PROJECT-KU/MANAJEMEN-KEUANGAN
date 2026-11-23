<?php

namespace Tests\Feature\Doku;

use App\Services\Doku;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Sambungan yang putus sekejap tidak boleh mematikan pembayaran.
 *
 * Terukur 23 Nov 2026 di jaringan pengembangan: SATU DARI DUA PULUH panggilan
 * keluar putus di tengah jabat tangan TLS (cURL 35, "Connection reset by
 * peer") — 0% paket hilang, tetapi latensinya berayun 39–156 ms. Pendaftaran
 * memanggil keluar tiga kali berurutan, jadi peluang satu di antaranya putus
 * menumpuk.
 *
 * Sebelum perbaikan ini, tiap putus sekejap LANGSUNG mematikan pembayaran:
 * pendaftarannya tersimpan, kursinya tertahan, dan layarnya berbunyi
 * "Pembayaran daring sedang bermasalah" — padahal DOKU tidak pernah ditanya.
 *
 * Yang membuat ulangan ini aman adalah aturan yang SUDAH ada sejak semula:
 * nomor tagihan percobaan kedua sama persis dengan yang pertama, jadi kalau
 * yang pertama ternyata sempat terbit, DOKU menolaknya 4xx dan ulangannya
 * berhenti. Uji terakhir di berkas ini menjaga aturan itu.
 */
class SambunganPutusDiulangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI');
        config()->set('services.doku.secret_key', 'SK-UJI');
        config()->set('services.doku.produksi', false);
    }

    /** @return array<string, mixed> */
    private function pesanan(): array
    {
        return [
            'nomor' => 'UJI-PUTUS-0001',
            'jumlah' => 151354,
            'judul' => 'Webinar Uji',
            'harga_satuan' => 150000,
            'jumlah_peserta' => 1,
            'kode_unik' => 1354,
            'nama' => 'Peserta Uji',
            'email' => 'uji@contoh.test',
            'telp' => '081234567890',
            'kembali' => 'http://127.0.0.1:8002/status',
        ];
    }

    /**
     * Jawaban berhasil dari DOKU.
     *
     * TANPA tipe kembalian: Http::response() memulangkan PromiseInterface,
     * bukan Response. Menuliskannya salah membuat PHP melempar TypeError di
     * dalam penangan fake — dan TypeError itu DITELAN catch (\Throwable) di
     * dalam Doku::buatTagihan(), lalu menyamar jadi "Tidak bisa menghubungi
     * penyedia pembayaran". Ujinya merah seolah-olah ulangannya tidak
     * bekerja, padahal yang rusak umpannya sendiri.
     */
    private function sukses()
    {
        return Http::response([
            'message' => ['SUCCESS'],
            'response' => ['payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/abc']],
        ], 200);
    }

    #[Test]
    public function putus_sekali_lalu_berhasil(): void
    {
        $urutan = 0;

        Http::fake(function () use (&$urutan) {
            $urutan++;

            if ($urutan === 1) {
                throw new ConnectionException(
                    'cURL error 35: Recv failure: Connection reset by peer'
                );
            }

            return $this->sukses();
        });

        $hasil = app(Doku::class)->buatTagihan($this->pesanan());

        $this->assertTrue($hasil['berhasil'],
            'Sambungan yang putus sekejap harus dicoba sekali lagi, bukan '
            . 'langsung mematikan pembayaran.');
        $this->assertSame('https://staging.doku.com/checkout-link-v2/abc', $hasil['url']);
        $this->assertSame(2, $urutan, 'Harus tepat dua percobaan, bukan lebih.');
    }

    #[Test]
    public function putus_dua_kali_baru_menyerah(): void
    {
        $urutan = 0;

        Http::fake(function () use (&$urutan) {
            $urutan++;

            throw new ConnectionException('cURL error 35: Recv failure: Connection reset by peer');
        });

        $hasil = app(Doku::class)->buatTagihan($this->pesanan());

        $this->assertFalse($hasil['berhasil']);
        $this->assertTrue($hasil['sementara'] ?? false,
            'Ditandai sementara supaya layarnya menawarkan mencoba lagi, '
            . 'bukan menyuruh orangnya menghubungi panitia untuk selamanya.');

        /*
         * DUA, bukan tiga. Yang menunggu orang di layarnya; tiap percobaan
         * tambahan menambah satu detik jeda plus satu batas waktu jaringan.
         */
        $this->assertSame(2, $urutan);
    }

    #[Test]
    public function penolakan_4xx_tidak_diulang(): void
    {
        $urutan = 0;

        Http::fake(function () use (&$urutan) {
            $urutan++;

            return Http::response(['error' => 'INVOICE ALREADY USED'], 400);
        });

        $hasil = app(Doku::class)->buatTagihan($this->pesanan());

        /*
         * Inilah yang membuat ulangan di atas aman. Kalau tagihan percobaan
         * pertama ternyata sempat terbit, percobaan kedua memakai nomor yang
         * SAMA dan DOKU menolaknya 4xx — sehingga tidak pernah ada dua
         * tagihan atas satu kursi. Mengulang 4xx hanya menghasilkan
         * penolakan yang sama.
         */
        $this->assertFalse($hasil['berhasil']);
        $this->assertSame(1, $urutan, '4xx adalah jawaban, bukan gangguan.');
    }

    #[Test]
    public function nomor_tagihan_percobaan_kedua_sama_persis(): void
    {
        $nomor = [];

        Http::fake(function ($permintaan) use (&$nomor) {
            $isi = json_decode($permintaan->body(), true);
            $nomor[] = data_get($isi, 'order.invoice_number');

            if (count($nomor) === 1) {
                throw new ConnectionException('cURL error 35: Connection reset by peer');
            }

            return $this->sukses();
        });

        app(Doku::class)->buatTagihan($this->pesanan());

        $this->assertCount(2, $nomor);
        $this->assertSame($nomor[0], $nomor[1],
            'Nomor tagihan percobaan kedua HARUS sama. Kalau berbeda, '
            . 'tagihan pertama yang sempat terbit tidak akan ketahuan, dan '
            . 'satu kursi bisa punya dua tagihan yang dua-duanya dibayar.');
    }

    #[Test]
    public function gangguan_sementara_dari_gerbang_tetap_diulang(): void
    {
        $urutan = 0;

        Http::fake(function () use (&$urutan) {
            $urutan++;

            return $urutan === 1
                ? Http::response('<hr><center>alb</center>', 503)
                : $this->sukses();
        });

        $hasil = app(Doku::class)->buatTagihan($this->pesanan());

        // Perilaku lama yang tidak boleh ikut hilang saat ulangan sambungan
        // ditambahkan.
        $this->assertTrue($hasil['berhasil']);
        $this->assertSame(2, $urutan);
    }
}
