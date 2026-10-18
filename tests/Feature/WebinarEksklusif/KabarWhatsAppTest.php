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

    /**
     * Keempat pemberitahuan, dirakit lalu diambil isi pesannya.
     *
     * @return array<string, string>
     */
    private function semuaPesan(): array
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

        $p = $this->pendaftaran();
        $sesi = KategoriLayanan::find($p->kategori_id);
        $wa = app(KabariWhatsApp::class);

        $pesan = [];

        foreach (['pendaftaranMasuk', 'lunas', 'gagalBayar', 'kursiDilepas'] as $cara) {
            Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);

            $wa->{$cara}($p, $sesi);

            $terkirim = null;
            Http::assertSent(function ($permintaan) use (&$terkirim) {
                $terkirim = $permintaan['message'];

                return true;
            });

            $pesan[$cara] = (string) $terkirim;
        }

        return $pesan;
    }

    #[Test]
    public function semua_pemberitahuan_melarang_membalas_dan_menyebut_nomor_panitia(): void
    {
        /*
         * Nomor pengirimnya nomor SISTEM, bukan nomor panitia. Balasan ke
         * sana tidak dibaca siapa pun — dan yang membalas lalu menunggu
         * jawaban yang tidak akan pernah datang, sementara masalahnya
         * (uangnya sudah terpotong, kursinya hilang) tetap tidak tertangani.
         *
         * Keempatnya, bukan sebagiannya: pemberitahuan yang satu melarang
         * membalas sementara yang lain diam membuat orang menebak-nebak
         * mana yang boleh.
         */
        config()->set('panitia.whatsapp', '6288983567819');

        foreach ($this->semuaPesan() as $cara => $pesan) {
            $this->assertStringContainsString('jangan dibalas', $pesan,
                $cara . ' tidak melarang membalas.');

            $this->assertStringContainsString('0889-8356-7819', $pesan,
                $cara . ' tidak menyebut nomor admin resmi yang bisa dihubungi.');

            /*
             * "admin resmi", bukan "panitia": panitia bisa berarti siapa
             * saja yang terlibat acara, dan penipu pun menyebut dirinya
             * panitia. Yang membedakan orang asli dari penipu adalah kata
             * "resmi" yang menunjuk SATU nomor.
             */
            $this->assertStringContainsString('admin resmi Rumah Scopus Foundation', $pesan,
                $cara . ' tidak menyebut siapa yang resmi.');
        }
    }

    #[Test]
    public function semua_pemberitahuan_memperingatkan_penipuan(): void
    {
        /*
         * Orang yang baru saja mendaftar adalah sasaran empuk: ia memang
         * sedang menunggu dihubungi soal pembayaran, jadi siapa pun yang
         * menyapa mengaku Rumah Scopus akan dipercaya.
         *
         * Peringatannya di SEMUA pemberitahuan, bukan sebagiannya — yang
         * ditipu belum tentu membaca pesan yang kebetulan memuatnya.
         */
        foreach ($this->semuaPesan() as $cara => $pesan) {
            $this->assertStringContainsString('Hati-hati penipuan', $pesan,
                $cara . ' tidak memperingatkan penipuan.');

            $this->assertStringContainsString('rekening pribadi', $pesan,
                $cara . ' tidak menyebut modus yang dipakai penipunya.');
        }
    }

    #[Test]
    public function janji_tanpa_transfer_manual_masih_benar(): void
    {
        /*
         * Kakinya berjanji "kami tidak pernah meminta transfer ke rekening
         * pribadi, pembayaran hanya lewat tautan resmi".
         *
         * Janji itu BOLEH ditulis karena transfer manual sudah dicabut dari
         * Webinar Eksklusif. Kalau suatu saat dibuka lagi, kalimatnya harus
         * ikut berubah — kalau tidak, ia jadi janji yang dilanggar
         * sistemnya sendiri, dan justru mengajari orang mengabaikan
         * peringatan penipuan kita.
         *
         * Baris ini yang akan merah lebih dulu kalau itu terjadi.
         */
        $sumber = (string) file_get_contents(
            base_path('app/Http/Controllers/Publict/PublicWebinarEksklusifController.php')
        );

        $this->assertStringNotContainsString("'cara_bayar' => 'transfer'", $sumber,
            'Transfer manual hidup lagi, sementara pemberitahuan WhatsApp '
            . 'masih berjanji pembayaran hanya lewat tautan resmi.');
    }

    #[Test]
    public function tidak_ada_pemberitahuan_yang_menyuruh_membalas(): void
    {
        /*
         * Ditambahkan karena pesan "kursi dilepas" SEMPAT berbunyi "Balas
         * pesan ini dengan menyebut nomor di atas" — bertentangan langsung
         * dengan kakinya sendiri, di pesan yang sama.
         *
         * Satu pesan yang menyuruh membalas sementara kakinya melarang
         * membuat pembacanya berhenti mempercayai keduanya.
         */
        foreach ($this->semuaPesan() as $cara => $pesan) {
            $this->assertStringNotContainsString('Balas pesan ini', $pesan,
                $cara . ' menyuruh membalas, padahal kakinya melarang.');
        }
    }

    #[Test]
    public function nomor_panitia_di_whatsapp_ikut_config(): void
    {
        /*
         * Nomornya berubah saat orangnya berganti. Diketik di perakit
         * pesan, ia akan tertinggal — dan yang menerima pemberitahuan
         * dengan nomor basi menghubungi orang yang sudah tidak menangani.
         */
        config()->set('panitia.whatsapp', '6289911223344');

        foreach ($this->semuaPesan() as $cara => $pesan) {
            $this->assertStringContainsString('0899-1122-3344', $pesan,
                $cara . ' memakai nomor panitia yang diketik, bukan dari config.');
        }
    }

    #[Test]
    public function jalur_web_tidak_pernah_menidurkan_permintaannya(): void
    {
        /*
         * Sapuan kedaluwarsa menjeda antar pesan supaya nomornya tidak
         * terbaca sebagai penyiar. Jeda itu AMAN di perintah terjadwal —
         * tidak ada orang yang menunggu layarnya — tetapi mematikan di
         * jalur web: lepaskanYangKedaluwarsa() dipanggil saat halaman
         * daftar sesi dan halaman status dibuka, dan satu sleep di situ
         * membuat halamannya menggantung sampai habis waktu.
         *
         * Pin kontrak atas sumbernya: tidurnya tidak bisa diukur dari
         * PHPUnit tanpa benar-benar menunggu.
         */
        $sumber = (string) file_get_contents(base_path('app/WebinarEksklusifPendaftaran.php'));

        $awal = strpos($sumber, 'public static function lepaskanYangKedaluwarsa');
        $this->assertNotFalse($awal);

        $potong = substr($sumber, $awal, 6000);

        $this->assertStringNotContainsString('sleep(', $potong,
            'lepaskanYangKedaluwarsa() menidurkan prosesnya; ia dipanggil dari '
            . 'halaman web, dan halamannya akan menggantung.');
    }

    #[Test]
    public function sapuan_dibatasi_supaya_tidak_meledak(): void
    {
        /*
         * Jaring kedua selain jeda: tumpukan yang sangat besar tidak
         * dihabiskan sekali jalan. Puluhan pesan beruntun berbunyi hampir
         * sama ke nomor berbeda adalah pola yang dibaca sebagai penyiar,
         * dan itu yang membuat nomor diblokir.
         */
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'id' => ['1']], 200)]);
        config()->set('services.fonnte.batas_sapuan', 2);

        $sesi = $this->sesi();

        for ($i = 0; $i < 5; $i++) {
            $this->pendaftaran([
                'kategori_id' => $sesi->getKey(),
                'id_transaksi' => 'WE-SAPU-' . Str::random(6),
                'email' => 'sapu' . Str::random(5) . '@contoh.test',
                'status' => 'expired',
                'bayar_status' => 'kedaluwarsa',
            ]);
        }

        $terkirim = app(\App\Actions\WebinarEksklusif\KabariKursiDilepas::class)->sapu();

        $this->assertSame(2, $terkirim,
            'Sapuan tidak dibatasi; satu kali jalan mengirim seluruh tumpukan.');
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
