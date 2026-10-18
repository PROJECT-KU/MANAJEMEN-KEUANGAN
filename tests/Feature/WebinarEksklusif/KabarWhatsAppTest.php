<?php

namespace Tests\Feature\WebinarEksklusif;

use App\Actions\WebinarEksklusif\KabariWhatsApp;
use App\KategoriLayanan;
use App\Services\Fonnte;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pemberitahuan WhatsApp, sepasang dengan suratnya.
 *
 * Alasannya satu kalimat dari pemilik produk: tidak semua orang membuka
 * emailnya. Yang tidak membukanya kehilangan nomor pendaftaran, tagihan,
 * dan batas waktunya sekaligus — lalu kursinya dilepas tanpa ia pernah tahu
 * apa yang terjadi.
 */
class KabarWhatsAppTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.fonnte.token', 'TOKEN-UJI');
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi WA ' . Str::random(5),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(16)->toDateString(),
            'selesai' => now()->addDays(16)->toDateString(),
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
            'id_transaksi' => 'WE-WA-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar WA',
            'email' => 'wa' . Str::random(5) . '@contoh.test',
            'telp' => '081234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129000',
            'cara_bayar' => 'doku',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    #[Test]
    public function tanpa_token_tidak_ada_satu_pun_permintaan_keluar(): void
    {
        /*
         * PENGAMAN TERPENTING di berkas ini.
         *
         * Token di .env seseorang berarti rangkaian uji mengirim WhatsApp
         * SUNGGUHAN ke nomor-nomor di data uji — sebagian nomor orang
         * betulan — dan ikut menghabiskan kuota 1.000 pesan/bulan paket
         * gratisnya. phpunit.xml mengosongkannya; baris ini memastikan
         * kosongnya benar-benar berarti diam, bukan permintaan tanpa token.
         */
        config()->set('services.fonnte.token', '');
        Http::fake();

        $hasil = app(Fonnte::class)->kirim('081234567890', 'halo');

        $this->assertFalse($hasil['berhasil']);
        Http::assertNothingSent();
    }

    #[Test]
    public function nomornya_dikirim_dengan_awalan_62(): void
    {
        /*
         * countryCode dimatikan ('0') dan nomornya dinormalkan sendiri
         * lewat PesananPelanggan::nomorWa(). Dibiarkan Fonnte yang
         * menambahkan, perilakunya bergantung pada nilai bawaan mereka yang
         * bisa berubah sewaktu-waktu.
         */
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        app(Fonnte::class)->kirim('081234567890', 'halo');

        Http::assertSent(function ($permintaan) {
            $this->assertSame('6281234567890', $permintaan['target']);
            $this->assertSame('0', $permintaan['countryCode']);

            // TANPA "Bearer" — Fonnte memintanya telanjang.
            $this->assertSame('TOKEN-UJI', $permintaan->header('Authorization')[0]);

            return true;
        });
    }

    #[Test]
    public function keberhasilan_berhuruf_besar_tetap_terbaca_berhasil(): void
    {
        /*
         * Dokumentasi Fonnte sendiri menyebut ejaan kuncinya TIDAK
         * KONSISTEN: sebagian balasan memakai "Status" berhuruf besar,
         * sisanya "status".
         *
         * Arah yang DIUJI di sini sengaja yang berbahaya, dan itu hasil
         * koreksi: baris ini semula menguji balasan GAGAL berhuruf besar —
         * dan ternyata tidak bisa dibuat merah. Dibaca huruf kecil saja,
         * "Status": false jadi null, jatuh ke cabang gagal, dan hasilnya
         * KEBETULAN benar.
         *
         * Yang benar-benar merugikan justru kebalikannya: "Status": true
         * yang terbaca null lalu dicatat sebagai GAGAL. Pesannya sudah
         * terkirim dan kuotanya sudah terpakai, tetapi log kami bilang
         * tidak — lalu orang mengirim ulang pesan yang sudah sampai.
         */
        Http::fake(['api.fonnte.com/*' => Http::response([
            'Status' => true,
            'id' => ['80367170'],
            'detail' => 'success! message in queue',
        ], 200)]);

        $hasil = app(Fonnte::class)->kirim('081234567890', 'halo');

        $this->assertTrue($hasil['berhasil'],
            'Balasan berhasil berhuruf besar dicatat sebagai gagal; pesannya '
            . 'sudah terkirim tetapi log kami bilang tidak.');

        $this->assertSame('80367170', $hasil['id']);
    }

    #[Test]
    public function kegagalan_membawa_alasannya(): void
    {
        /*
         * Alasannya ikut dipulangkan supaya log menyebut APA yang salah —
         * "insufficient quota" dan "token invalid" menuntut tindakan yang
         * sama sekali berbeda, dan log yang cuma bilang "gagal" tidak
         * membedakannya.
         */
        Http::fake(['api.fonnte.com/*' => Http::response([
            'status' => false,
            'reason' => 'insufficient quota',
        ], 200)]);

        $hasil = app(Fonnte::class)->kirim('081234567890', 'halo');

        $this->assertFalse($hasil['berhasil']);
        $this->assertSame('insufficient quota', $hasil['pesan']);
    }

    #[Test]
    public function nomor_yang_tidak_bisa_dihubungi_tidak_dikirimi(): void
    {
        Http::fake();

        $hasil = app(Fonnte::class)->kirim('123', 'halo');

        $this->assertFalse($hasil['berhasil']);
        Http::assertNothingSent();
    }

    #[Test]
    public function mendaftar_mengirim_whatsapp_berisi_nomor_jumlah_dan_tautan(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $p = $this->pendaftaran();
        $sesi = KategoriLayanan::find($p->kategori_id);

        app(KabariWhatsApp::class)->pendaftaranMasuk($p, $sesi);

        Http::assertSent(function ($permintaan) use ($p) {
            $pesan = $permintaan['message'];

            $this->assertStringContainsString($p->id_transaksi, $pesan, 'Nomor pendaftaran tidak disebut.');
            $this->assertStringContainsString('129.000', $pesan, 'Jumlahnya tidak disebut.');
            $this->assertStringContainsString('/Webinar-Eksklusif/Status/', $pesan, 'Tautan statusnya tidak ada.');

            return true;
        });
    }

    #[Test]
    public function gagal_kirim_whatsapp_tidak_menggagalkan_pendaftarannya(): void
    {
        /*
         * Pendaftaran yang batal hanya karena WhatsApp sedang mati adalah
         * kerugian yang jauh lebih besar daripada satu pesan yang tidak
         * sampai — dan pada jalur pemberitahuan DOKU, galat di sini membuat
         * kami menjawab bukan-200 ke gerbangnya, yang lalu mengulang
         * pemberitahuan pembayaran yang sudah tercatat.
         */
        Http::fake(['api.fonnte.com/*' => fn () => throw new \RuntimeException('jaringan mati')]);

        $p = $this->pendaftaran();

        $terkirim = app(KabariWhatsApp::class)
            ->pendaftaranMasuk($p, KategoriLayanan::find($p->kategori_id));

        $this->assertFalse($terkirim, 'Kegagalan jaringan terbaca sebagai terkirim.');
    }
}
