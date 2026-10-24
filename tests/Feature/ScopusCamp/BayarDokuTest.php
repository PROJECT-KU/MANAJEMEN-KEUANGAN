<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pembayaran Scopus Camp lewat gerbang DOKU.
 *
 * Sebelum ini satu-satunya cara bayarnya transfer manual: salin nomor
 * rekening, pindah ke aplikasi bank, potret bukti, kembali, unggah, lalu
 * menunggu panitia membuka gambarnya. Terukur di basis data ini, enam bukti
 * menunggu diperiksa selama 177, 127, 74, 28, 17, dan 6 hari — orang-orang
 * itu sudah mengirim uangnya dan masih tercatat belum bayar.
 *
 * Yang dijaga di sini bukan adanya tombol, melainkan hal-hal yang kalau
 * rusak TIDAK mengeluarkan galat apa pun:
 *
 *   - tagihan yang rinciannya tidak berjumlah sama dengan totalnya ditolak
 *     DOKU, dan pendaftarnya cuma melihat "pembayaran sedang bermasalah";
 *   - pemberitahuan palsu yang menandai orang lunas tanpa uang masuk;
 *   - status lunas yang dikirim lewat borangnya sendiri;
 *   - surat penerimaan yang berangkat dua kali karena DOKU mengulang
 *     pemberitahuannya.
 */
class BayarDokuTest extends TestCase
{
    use DatabaseTransactions;

    /** Angkatan yang dipakai pendaftaran() terakhir; lihat alasannya di sana. */
    private ?CategoriesScopusCamp $angkatanTerakhir = null;

    private const KLIEN = 'BRN-UJI-CAMP';
    private const RAHASIA = 'SK-UJI-CAMP';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Doku ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujicamp',
            'total_kuota' => 20,
            'sisa_kuota' => 20,
            'biaya' => 5500000,
            'status' => 'active',
        ], $ganti));
    }

    /** @return array<string, mixed> */
    private function isian(CategoriesScopusCamp $a, array $ganti = []): array
    {
        return array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            // Centang ketentuan wajib sejak 23 Okt 2026; penjagaannya sendiri
            // diuji di PenjagaanBorangTest, bukan di sini.
            'setuju' => '1',
        ], $ganti);
    }

    /** Jawaban DOKU saat tagihan dibuat. */
    private function gerbangMenerima(string $url = 'https://staging.doku.com/checkout-link-v2/UJI'): void
    {
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => $url],
                'order' => ['invoice_number' => 'CAMP-UJI'],
            ],
        ], 200)]);
    }

    private function pendaftaran(array $ubah = []): PendaftaranScopusCamp
    {
        /*
         * Angkatannya diingat di properti KELAS UJI, bukan ditempelkan ke
         * modelnya.
         *
         * Menempelkannya sebagai properti dinamis pada model Eloquent membuat
         * ia diperlakukan sebagai ATRIBUT: simpanan berikutnya mengirimkannya
         * sebagai kolom, dan MySQL menolak dengan "Unknown column ... in
         * field list". Terjadi persis begitu 24 Okt 2026, dan yang merah
         * bukan uji yang menempelkannya melainkan uji lain yang kebetulan
         * menyimpan ulang barisnya.
         */
        $angkatan = $this->angkatan();
        $this->angkatanTerakhir = $angkatan;

        $baris = PendaftaranScopusCamp::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-UJI-' . Str::random(8),
            'kategori_id' => $angkatan->getKey(),
            'nama' => 'Pendaftar Uji',
            'email' => 'uji' . Str::random(6) . '@contoh.test',
            'telp' => '6281234567890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'kode_unik' => '777',
            'total_pembayaran' => '5500777',
            'cara_bayar' => 'doku',
            'status' => 'diproses',
            'bayar_status' => 'menunggu',
            'bayar_url' => 'https://staging.doku.com/checkout-link-v2/UJI',
            'bayar_permintaan_id' => (string) Str::uuid(),
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));

        return $baris;
    }

    // ------------------------------------------------------------ borang

    #[Test]
    public function borang_tidak_meminta_bukti_transfer_selama_gerbangnya_siap(): void
    {
        /*
         * Keduanya harus hilang BERSAMAAN.
         *
         * Nomor rekening yang tertinggal berarti sebagian orang tetap
         * mentransfer manual ke rekening itu — uangnya masuk tanpa tagihan
         * yang bisa dicocokkan, dan tidak ada satu pun layar yang menyatakan
         * ada yang salah.
         */
        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $this->assertStringNotContainsString('216401000467563', $isi,
            'Nomor rekening transfer manual masih dipajang padahal pembayarannya '
            . 'sudah lewat gerbang — uang yang masuk ke sana tidak tercocokkan siapa pun.');

        $this->assertStringNotContainsString('name="gambar"', $isi,
            'Borangnya masih menuntut unggah bukti transfer padahal tidak ada '
            . 'yang perlu diunggah.');

        $this->assertStringContainsString('Lanjut ke pembayaran', $isi);
    }

    #[Test]
    public function borang_tidak_menjanjikan_cara_bayar_yang_tidak_diterima(): void
    {
        /*
         * Daftar yang dipajang borang HARUS sama dengan yang benar-benar
         * ditawarkan halaman DOKU. Yang menjanjikan kartu kredit lalu tidak
         * menemukannya di halaman berikutnya berhenti di situ — dan tidak
         * ada satu pun catatan yang menyebut ia pernah mencoba.
         */
        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $daftar = (string) (preg_match('/<div class="daf-kanal">(.*?)<\/div>/s', $isi, $c)
            ? $c[1] : '');

        $this->assertNotSame('', $daftar, 'Daftar cara bayarnya hilang dari borang.');

        foreach (['Kartu kredit', 'OVO', 'DANA', 'ShopeePay', 'Alfamart', 'Indomaret'] as $dicabut) {
            $this->assertStringNotContainsString($dicabut, $daftar,
                '"' . $dicabut . '" masih dijanjikan borangnya, padahal tagihannya '
                . 'dibatasi ke QRIS dan Virtual Account saja.');
        }

        $this->assertStringContainsString('QRIS', $daftar);
        $this->assertStringContainsString('Virtual Account', $daftar);
    }

    #[Test]
    public function borang_kembali_ke_transfer_manual_saat_gerbangnya_belum_disetel(): void
    {
        /*
         * Peladen yang kredensial DOKU-nya belum dipasang HARUS tetap punya
         * cara menerima uang. Tanpa cabang ini, borangnya memajang tombol
         * "Lanjut ke pembayaran" yang selalu berakhir di halaman galat.
         */
        config()->set('services.doku.client_id', '');
        config()->set('services.doku.secret_key', '');

        $isi = (string) $this->get(route('public.scopuscamp.formpendaftaran',
            ['id' => $this->angkatan()->getKey()]))->assertOk()->getContent();

        $this->assertStringContainsString('216401000467563', $isi,
            'Tanpa gerbang pembayaran, borangnya tidak memajang satu pun cara membayar.');
        $this->assertStringContainsString('name="gambar"', $isi);
    }

    // ------------------------------------------------------ membuat tagihan

    #[Test]
    public function pendaftaran_diantar_ke_halaman_bayar_dan_tagihannya_tersimpan(): void
    {
        Mail::fake();
        $this->gerbangMenerima('https://staging.doku.com/checkout-link-v2/ABC123');

        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a))
            ->assertRedirect('https://staging.doku.com/checkout-link-v2/ABC123');

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();

        $this->assertSame('doku', $p->cara_bayar);
        $this->assertSame('menunggu', $p->bayar_status);
        $this->assertSame('https://staging.doku.com/checkout-link-v2/ABC123', $p->bayar_url,
            'Alamat bayarnya tidak disimpan — yang menutup tabnya tidak punya '
            . 'satu pun tombol untuk menyelesaikan pembayarannya.');

        $this->assertNotEmpty($p->bayar_permintaan_id,
            'Nomor permintaannya tidak disimpan — tagihan ini tidak akan pernah '
            . 'bisa ditutup lewat Cancel Order API DOKU, dan orangnya masih bisa '
            . 'membayar kursi yang sudah diberikan kepada orang lain.');

        $this->assertNull($p->gambar,
            'Lewat gerbang tidak ada bukti transfer untuk disimpan.');
    }

    #[Test]
    public function rincian_tagihan_berjumlah_sama_dengan_total_yang_disimpan(): void
    {
        /*
         * DOKU menolak dengan "AMOUNT NOT MATCH" kalau order.amount berbeda
         * dari jumlah line_items-nya, dan penolakannya sampai ke pendaftar
         * sebagai "pembayaran daring sedang bermasalah" — tanpa petunjuk apa
         * pun bahwa yang salah ada di sisi kita.
         *
         * Dijumlah ULANG di sini dari rincian yang benar-benar dikirim,
         * bukan dicocokkan dengan angka yang kita hitung sendiri lagi.
         */
        Mail::fake();
        $this->gerbangMenerima();

        $a = $this->angkatan(['ppn' => 11]);

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();

        $this->assertGreaterThan(0, (int) $p->ppn,
            'Angkatan uji ini seharusnya kena PPN; tanpa itu bagian yang paling '
            . 'mudah meleset tidak ikut teruji.');

        Http::assertSent(function ($permintaan) use ($p) {
            if (! str_contains($permintaan->url(), '/checkout/v1/payment')) {
                return false;
            }

            $isi = json_decode($permintaan->body(), true);

            $jumlahRincian = array_sum(array_map(
                fn ($b) => $b['price'] * $b['quantity'],
                $isi['order']['line_items']
            ));

            $this->assertSame((int) $p->total_pembayaran, $isi['order']['amount'],
                'Nominal yang ditagihkan berbeda dari yang tersimpan di basis data.');

            $this->assertSame((int) $p->total_pembayaran, $jumlahRincian,
                'Rincian tagihannya tidak berjumlah sama dengan totalnya — '
                . 'DOKU menolaknya dengan AMOUNT NOT MATCH.');

            return true;
        });
    }

    #[Test]
    public function tagihannya_hanya_menawarkan_qris_dan_virtual_account(): void
    {
        /*
         * Daftar kanal di borangnya cuma JANJI. Yang menentukan pilihan
         * sebenarnya halaman DOKU, dan tanpa pembatasan ini ia memajang
         * seluruh kanal yang aktif di akunnya.
         *
         * Terukur langsung di halaman checkout sandbox 23 Okt 2026:
         *
         *   tanpa pembatasan  Bank Transfer, e-Wallet, Convenience Store,
         *                     Cards, Digital Banking, PayLater, Internet
         *                     Banking, Direct Debit
         *   dengan pembatasan Bank Transfer saja
         *
         * Jadi tanpa baris ini, orang yang membaca "QRIS dan Virtual
         * Account" di borang kita bisa membayar dengan kartu kredit di
         * halaman berikutnya — dan tidak ada satu pun layar yang menyatakan
         * ada yang salah.
         */
        Mail::fake();
        $this->gerbangMenerima();

        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a));

        Http::assertSent(function ($permintaan) {
            if (! str_contains($permintaan->url(), '/checkout/v1/payment')) {
                return false;
            }

            $kanal = json_decode($permintaan->body(), true)['payment']['payment_method_types'] ?? null;

            $this->assertIsArray($kanal,
                'Tagihannya dikirim TANPA pembatasan kanal — halaman DOKU akan '
                . 'memajang kartu, e-wallet, dan gerai, padahal borangnya '
                . 'menjanjikan QRIS dan Virtual Account saja.');

            $this->assertContains('QRIS', $kanal);

            $this->assertNotEmpty(
                array_filter($kanal, fn ($k) => str_starts_with($k, 'VIRTUAL_ACCOUNT_')),
                'Tidak ada satu pun Virtual Account di daftarnya.'
            );

            foreach ($kanal as $satu) {
                $this->assertTrue(
                    $satu === 'QRIS' || str_starts_with($satu, 'VIRTUAL_ACCOUNT_'),
                    'Kanal "' . $satu . '" bukan QRIS maupun Virtual Account.'
                );
            }

            return true;
        });
    }

    #[Test]
    public function nama_virtual_accountnya_disebut_per_bank(): void
    {
        /*
         * DOKU TIDAK mengenal nama payung "VIRTUAL_ACCOUNT" di
         * payment_method_types. Yang ia terima nama per bank, dan nama
         * payungnya ditolak 400 dengan "PAYMENT CHANNEL IS INACTIVE" —
         * pesan yang terbaca seperti kanalnya dimatikan, bukan seperti
         * namanya salah. Sudah diukur langsung ke sandbox 23 Okt 2026.
         */
        $this->assertNotContains('VIRTUAL_ACCOUNT', \App\Services\Doku::KANAL_QRIS_DAN_VA,
            'Nama payung "VIRTUAL_ACCOUNT" dipakai; DOKU menolak seluruh '
            . 'tagihannya dengan pesan yang menunjuk ke arah yang salah.');

        $this->assertGreaterThanOrEqual(5,
            count(array_filter(\App\Services\Doku::KANAL_QRIS_DAN_VA,
                fn ($k) => str_starts_with($k, 'VIRTUAL_ACCOUNT_'))),
            'Banknya terlalu sedikit; kalau yang tersisa kebetulan mati di '
            . 'akunnya, SETIAP pendaftaran berakhir di halaman "pembayaran '
            . 'daring sedang bermasalah".');
    }

    #[Test]
    public function layanan_lain_tidak_ikut_dibatasi_kanalnya(): void
    {
        /*
         * Yang meminta pembatasan cuma Scopus Camp. Webinar Eksklusif
         * memakai kelas Doku yang SAMA, dan membatasi dari dalam kelas itu
         * berarti mencabut cara bayar dari layanan yang tidak pernah
         * memintanya — tanpa satu baris pun yang menyebutkannya.
         */
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://staging.doku.com/x'],
                           'order' => ['invoice_number' => 'WE-UJI']],
        ], 200)]);

        app(\App\Services\Doku::class)->buatTagihan([
            'nomor' => 'WE-UJI-0001', 'jumlah' => 129000,
            'harga_satuan' => 129000, 'jumlah_peserta' => 1,
            'potongan' => 0, 'kode_unik' => 0,
            'judul' => 'Sesi Uji', 'nama' => 'Uji',
            'email' => 'uji@contoh.test', 'telp' => '08123456789',
            'kembali' => 'http://127.0.0.1:8000/',
        ]);

        Http::assertSent(function ($permintaan) {
            $isi = json_decode($permintaan->body(), true);

            $this->assertArrayNotHasKey('payment_method_types', $isi['payment'],
                'Tagihan yang TIDAK meminta pembatasan ikut dibatasi kanalnya.');

            return true;
        });
    }

    #[Test]
    public function tagihan_gagal_tidak_menghapus_pendaftarannya(): void
    {
        /*
         * Orangnya sudah mengisi borang dan kursinya sudah ditahan.
         * Menghapusnya berarti ia mengulang dari nol karena kesalahan yang
         * bukan miliknya — dan sebagian tidak mengulang sama sekali.
         */
        Mail::fake();
        Http::fake(['*/checkout/v1/payment' => Http::response(['error' => 'tolak'], 400)]);

        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a))
            ->assertRedirectContains('/Scopus-Camp/Status/');

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->first();

        $this->assertNotNull($p, 'Pendaftarannya ikut hilang saat tagihannya gagal dibuat.');
        $this->assertSame('gagal-dibuat', $p->bayar_status);
    }

    #[Test]
    public function status_lunas_tidak_bisa_dikirim_lewat_borangnya(): void
    {
        /*
         * Sampai 23 Okt 2026 barisnya memakai $request->input('status').
         * Siapa pun bisa menyertakan status='Pendaftaran Diterima' bersama
         * borangnya dan terdaftar sebagai lunas tanpa membayar sepeser pun —
         * dan di layar panitia ia terbaca persis sama dengan yang sudah
         * diperiksa buktinya.
         */
        Mail::fake();
        $this->gerbangMenerima();

        $a = $this->angkatan();

        $this->post(route('public.scopuscamp.store'),
            $this->isian($a, ['status' => 'Pendaftaran Diterima']));

        $p = PendaftaranScopusCamp::where('kategori_id', $a->getKey())->firstOrFail();

        $this->assertSame('diproses', $p->status,
            'Status pendaftaran bisa ditentukan dari borangnya — orang bisa '
            . 'mendaftar sebagai lunas tanpa membayar.');
    }

    // ------------------------------------------------------ pemberitahuan

    /** @param array<string, mixed> $isi */
    private function kirimPemberitahuan(array $isi, bool $tandaTanganiBenar = true)
    {
        $jalur = '/Scopus-Camp/pemberitahuan/doku';
        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');
        $json = json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $bahan = implode("\n", [
            'Client-Id:' . self::KLIEN,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . base64_encode(hash('sha256', $json, true)),
        ]);

        $tanda = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $bahan, $tandaTanganiBenar ? self::RAHASIA : 'SALAH', true)
        );

        return $this->call('POST', $jalur, [], [], [], [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => $tanda,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }

    /** @return array<string, mixed> */
    private function kiriman(PendaftaranScopusCamp $p, string $status = 'SUCCESS'): array
    {
        return [
            'order' => ['invoice_number' => $p->id_transaksi],
            'transaction' => ['status' => $status, 'date' => now()->toIso8601String()],
            'service' => ['id' => 'VIRTUAL_ACCOUNT'],
            'acquirer' => ['name' => 'Bank Mandiri'],
        ];
    }

    #[Test]
    public function pemberitahuan_bertanda_tangan_salah_ditolak(): void
    {
        /*
         * Alamat ini dikecualikan dari CSRF, jadi tanda tangannya adalah
         * SATU-SATUNYA penjagaan. Tanpa pemeriksaan itu, siapa pun yang tahu
         * alamatnya bisa menandai pendaftaran mana pun sebagai lunas hanya
         * dengan satu permintaan POST.
         */
        $p = $this->pendaftaran();

        $this->kirimPemberitahuan($this->kiriman($p), false)->assertStatus(401);

        $this->assertSame('diproses', $p->refresh()->status,
            'Pemberitahuan palsu berhasil menandai pendaftaran ini lunas.');
    }

    #[Test]
    public function pemberitahuan_sah_menandai_lunas_beserta_kanalnya(): void
    {
        Mail::fake();

        $p = $this->pendaftaran();

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        $p->refresh();

        $this->assertSame('Pendaftaran Diterima', $p->status,
            'Pembayaran yang sudah masuk tidak memindahkan statusnya — panitia '
            . 'akan menagih orang yang sudah membayar.');

        $this->assertSame('VA Bank Mandiri', $p->bayar_kanal,
            'Kanalnya tidak tercatat; layar panitia cuma bisa berkata "DOKU".');

        $this->assertNotNull($p->bayar_pada);
    }

    #[Test]
    public function pemberitahuan_yang_sama_dua_kali_hanya_mengirim_satu_surat(): void
    {
        /*
         * DOKU boleh mengulang pemberitahuan yang sama. Tanpa penjagaan,
         * pendaftarnya menerima surat penerimaan berkali-kali untuk satu
         * pembayaran.
         */
        Mail::fake();

        $p = $this->pendaftaran();

        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();
        $this->kirimPemberitahuan($this->kiriman($p))->assertOk();

        Mail::assertSent(\App\Mail\ScopusCampUpdateDiterimaMail::class, 1);
    }

    #[Test]
    public function pemberitahuan_gagal_tidak_membatalkan_pendaftarannya(): void
    {
        /*
         * Di layanan ini 'Pendaftaran Dibatalkan' adalah KEPUTUSAN PANITIA,
         * bukan keputusan gerbang — ia menyeret surat pembatalan ke
         * pendaftarnya. Tagihan yang kedaluwarsa cuma berarti tagihannya mati,
         * dan orangnya masih bisa diminta membayar ulang.
         */
        Mail::fake();

        $p = $this->pendaftaran();

        $this->kirimPemberitahuan($this->kiriman($p, 'EXPIRED'))->assertOk();

        $p->refresh();

        $this->assertSame('diproses', $p->status,
            'Tagihan yang kedaluwarsa ikut membatalkan pendaftarannya.');
        $this->assertSame('expired', $p->bayar_status);
    }

    // --------------------------------------------------------- halaman status

    #[Test]
    public function halaman_status_memajang_tombol_bayar_selama_tagihannya_hidup(): void
    {
        // Gerbang belum mengenal nomornya; halaman ini tidak boleh menyimpulkan
        // apa pun dari jawaban itu.
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString($p->bayar_url, $isi,
            'Tombol bayarnya hilang padahal tagihannya masih hidup — orangnya '
            . 'tidak punya jalan menyelesaikan pembayarannya.');
        $this->assertStringContainsString('Tinggal satu langkah lagi', $isi);
    }

    #[Test]
    public function tombol_bayar_hilang_begitu_tenggatnya_lewat(): void
    {
        /*
         * Tombol yang tetap digambar mengantar orangnya ke halaman DOKU
         * bertuliskan "your order has expired" — layar yang digambar di
         * domain mereka dan tidak bisa kita ganti kalimatnya.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran(['kedaluwarsa_pada' => now()->subMinute()]);

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString($p->bayar_url, $isi,
            'Tombol bayarnya masih digambar padahal tagihannya sudah mati.');
    }

    #[Test]
    public function halaman_status_menyusulkan_lunas_dari_gerbang(): void
    {
        /*
         * Pemberitahuan DOKU berjalan dari peladen ke peladen dan hampir
         * selalu kalah cepat dari peramban yang cuma berpindah halaman; di
         * mesin pengembang ia tidak pernah sampai sama sekali. Tanpa jalur
         * bertanya ini, orang membaca "belum dibayar" tepat sesudah membayar —
         * lalu sebagian membayar lagi.
         */
        Mail::fake();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS', 'date' => now()->toIso8601String()],
            'channel' => ['id' => 'QRIS'],
        ], 200)]);

        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('sudah lunas', $isi);
        $this->assertSame('Pendaftaran Diterima', $p->refresh()->status);
        $this->assertSame('QRIS', $p->bayar_kanal);
    }

    #[Test]
    public function pembayaran_lebih_tua_daripada_pendaftarannya_tidak_diwariskan(): void
    {
        /*
         * Nomor tagihan bisa terpakai ulang, dan gerbang menjawab SUCCESS
         * untuk nomor itu sebab memang pernah dibayar — yang membayar
         * pendahulunya. Tanpa penjagaan ini pendaftaran baru tertandai lunas
         * tanpa ada uang masuk, dan pendaftarnya menerima surat penerimaan.
         */
        Mail::fake();

        Http::fake(['*/orders/v1/status/*' => Http::response([
            'transaction' => ['status' => 'SUCCESS', 'date' => now()->subDay()->toIso8601String()],
        ], 200)]);

        $p = $this->pendaftaran();

        $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();

        $this->assertSame('diproses', $p->refresh()->status,
            'Pembayaran milik pendaftaran lain diwariskan ke pendaftaran ini.');
    }

    #[Test]
    public function keadaan_memulangkan_selesai_hanya_setelah_lunas(): void
    {
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $this->getJson(route('public.scopuscamp.keadaan', $p->getKey()))
            ->assertOk()
            ->assertJson(['ada' => true, 'lunas' => false, 'selesai' => false]);

        $p->forceFill(['status' => 'Pendaftaran Diterima'])->save();

        $this->getJson(route('public.scopuscamp.keadaan', $p->getKey()))
            ->assertOk()
            ->assertJson(['ada' => true, 'lunas' => true, 'selesai' => true]);
    }

    // --------------------------------------------- isi halaman statusnya

    #[Test]
    public function tautan_grup_peserta_hanya_untuk_yang_sudah_lunas(): void
    {
        /*
         * INI penjagaan, bukan tata letak.
         *
         * Grup peserta isinya orang yang sudah membayar. Memajang tautannya
         * pada pendaftaran yang BELUM lunas berarti siapa pun yang memegang
         * alamat halaman ini — dan alamatnya ikut terkirim di surat bukti
         * pendaftaran — bisa masuk tanpa membayar sepeser pun.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();
        $grup = $this->angkatanTerakhir->group_wa;

        $this->assertNotEmpty($grup, 'Angkatan ujinya tidak punya tautan grup.');

        $belum = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString($grup, $belum,
            'Tautan grup peserta dipajang pada pendaftaran yang belum lunas.');

        $p->forceFill(['status' => 'Pendaftaran Diterima'])->save();

        $sudah = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString($grup, $sudah,
            'Yang sudah lunas tidak diberi tautan grup pesertanya.');
    }

    #[Test]
    public function halaman_status_menyebut_jadwal_dan_lokasi_acaranya(): void
    {
        /*
         * Orangnya baru membayar belasan juta untuk sebuah acara, dan sampai
         * 24 Okt 2026 halaman yang memberitahunya "sudah lunas" tidak
         * menyebutkan kapan maupun di mana acaranya. Keterangan itu memang
         * ada di suratnya — tetapi halaman ini yang ia buka lagi saat
         * mencarinya.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        /*
         * Diperiksa DI DALAM kwitansinya, bukan di seluruh halaman.
         *
         * Versi pertama penjaga ini mencari "Yogyakarta" di mana pun — dan
         * kata itu ADA di alamat kaki halaman ("… Sleman, Daerah Istimewa
         * Yogyakarta 55551"). Jadi ia tetap hijau walau kwitansinya sama
         * sekali tidak menyebut lokasi acaranya. Dibuktikan: mematikan baris
         * lokasinya di Blade tidak membuat penjaga ini merah.
         */
        $karcis = (string) (preg_match('#<div class="sts-karcis">(.*?)</div>\s*</div>#s', $isi, $c)
            ? $c[1]
            : '');

        $this->assertNotSame('', $karcis, 'Kwitansinya tidak tergambar.');

        $this->assertStringContainsString($this->angkatanTerakhir->lokasi, $karcis,
            'Lokasi acaranya tidak disebut di kwitansi halaman status.');

        $this->assertStringContainsString(
            \Carbon\Carbon::parse($this->angkatanTerakhir->mulai)->locale('id')->translatedFormat('F'),
            $karcis,
            'Tanggal acaranya tidak disebut di kwitansi halaman status.'
        );
    }

    #[Test]
    public function halaman_status_memajang_nama_peserta_rombongan(): void
    {
        /*
         * Satu-satunya tempat pendaftar bisa MEMERIKSA siapa saja yang ia
         * daftarkan. Tanpa itu, salah ketik nama baru ketahuan di sertifikat —
         * saat sudah terlambat diperbaiki.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran(['jumlah_pendaftar' => 3]);
        $p->pesertaLain()->create(['urutan' => 1, 'nama' => 'Siti Peserta Dua', 'telp' => '6285712345678']);
        $p->pesertaLain()->create(['urutan' => 2, 'nama' => 'Ahmad Peserta Tiga', 'telp' => null]);

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Siti Peserta Dua', $isi);
        $this->assertStringContainsString('Ahmad Peserta Tiga', $isi);

        // Nomornya ditampilkan dalam bentuk lokal, bukan 62… mentah seperti
        // tersimpan — yang membacanya pendaftar, bukan panitia.
        $this->assertStringContainsString('0857-1234-5678', $isi);

        // Yang nomornya memang belum diisi disebut apa adanya, bukan
        // dibiarkan kosong — baris kosong terbaca seperti data yang hilang.
        $this->assertStringContainsString('Nomornya belum diisi', $isi);
    }

    #[Test]
    public function daftar_peserta_tidak_digambar_untuk_pendaftaran_satu_orang(): void
    {
        // Judul "Peserta yang didaftarkan" di atas satu nama adalah bagian
        // yang tidak menjelaskan apa pun, dan hampir semua pendaftaran
        // memang satu orang.
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $isi = (string) $this->get(route('public.scopuscamp.status',
            $this->pendaftaran()->getKey()))->assertOk()->getContent();

        $this->assertStringNotContainsString('Peserta yang didaftarkan', $isi);
    }

    #[Test]
    public function yang_batal_tidak_diberi_langkah_berikutnya(): void
    {
        // Tidak ada langkah berikutnya bagi pendaftaran yang sudah
        // dibatalkan; menuliskannya tetap berarti menyuruh orang mengerjakan
        // sesuatu yang tidak akan mengubah apa pun.
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran(['status' => 'Pendaftaran Dibatalkan']);

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        /*
         * Yang diperiksa MARKAHNYA, bukan kata "sts-langkah" di mana pun.
         *
         * Aturan gayanya selalu ada di blok <style> halaman ini, lunas
         * maupun batal — jadi mencari namanya begitu saja membuat penjaga
         * ini merah selamanya, untuk alasan yang tidak ada hubungannya
         * dengan yang dijaga.
         */
        $this->assertStringNotContainsString('<ol class="sts-langkah">', $isi,
            'Pendaftaran yang sudah batal tetap diberi daftar langkah berikutnya.');

        $this->assertStringContainsString('sudah tidak berjalan', $isi);
    }

    #[Test]
    public function centang_lunas_tetap_tergambar_saat_gerak_dikurangi(): void
    {
        /*
         * Centangnya digambar dengan stroke-dashoffset: ia BERANGKAT dari
         * keadaan tidak terlihat dan baru lengkap saat animasinya selesai.
         *
         * Jadi mematikan animasinya untuk yang memasang "kurangi gerak" —
         * dengan `animation: none` saja — membuat centangnya TIDAK PERNAH
         * TERGAMBAR: yang tampil lingkaran kosong, persis pada orang yang
         * paling tidak akan menyangka halamannya sedang beranimasi, dan
         * tidak ada galat apa pun yang memberi tahu.
         *
         * Dijaga di sumbernya: penjaga berbasis permintaan HTTP tidak
         * menghitung CSS, dan media query-nya tidak pernah aktif di sana.
         */
        $sumber = file_get_contents(base_path(
            'resources/views/public/scopus_camp/status.blade.php'
        ));

        $cocok = preg_match(
            '/@media \(prefers-reduced-motion: reduce\) \{(.*?)\n    \}/s',
            $sumber,
            $c
        );

        $this->assertSame(1, $cocok, 'Blok "kurangi gerak" tidak ada di halaman status.');

        $this->assertStringContainsString('stroke-dashoffset: 0', $c[1],
            'Blok "kurangi gerak" tidak memasang keadaan akhir centangnya, jadi '
            . 'centang itu tidak akan pernah tergambar bagi yang memintanya.');
    }

    #[Test]
    public function animasi_centang_lunas_berulang_terus(): void
    {
        /*
         * Diminta pemiliknya 24 Okt 2026: versi pertama memakai `forwards`,
         * jadi animasinya jalan SEKALI saat halaman dibuka lalu berhenti di
         * keadaan akhir — dan sesudah itu halaman diam.
         *
         * Dijaga di sumbernya karena yang menentukan CSS, dan `forwards`
         * kembali ke sini tidak akan membuat satu pun uji lain merah:
         * halamannya tetap 200, centangnya tetap tergambar, yang hilang cuma
         * geraknya.
         */
        $sumber = file_get_contents(base_path(
            'resources/views/public/scopus_camp/status.blade.php'
        ));

        foreach (['sts-centang-lingkar', 'sts-centang-garis'] as $bagian) {
            $cocok = preg_match(
                '/\.sts-meterai\.lunas \.' . preg_quote($bagian, '/') . ' \{(.*?)\}/s',
                $sumber,
                $c
            );

            $this->assertSame(1, $cocok, 'Aturan animasi ' . $bagian . ' tidak ada.');

            $this->assertStringContainsString('infinite', $c[1],
                'Animasi ' . $bagian . ' tidak berulang — ia berhenti sesudah '
                . 'satu putaran, dan halamannya diam sesudah itu.');
        }
    }

    #[Test]
    public function centang_lunas_digambar_sebagai_svg(): void
    {
        /*
         * Ikon Font Awesome adalah glif — ia hanya bisa dibesarkan dan
         * diputar, tidak bisa digambar garis demi garis. Kembali ke
         * <i class="fas fa-check"> akan mencabut animasinya tanpa satu pun
         * galat; yang tersisa cuma lingkaran dengan centang yang langsung
         * jadi.
         */
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);

        $p = $this->pendaftaran(['status' => 'Pendaftaran Diterima']);

        $isi = (string) $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        /*
         * Dicari MARKAHNYA, bukan namanya di mana pun: aturan gayanya selalu
         * ada di blok <style> halaman ini, jadi mencari nama kelasnya begitu
         * saja membuat penjaga ini hijau selamanya. Sudah dibuktikan —
         * mencabut kelas itu dari <path> tidak membuatnya merah.
         */
        $this->assertStringContainsString('<path class="sts-centang-garis"', $isi,
            'Centang lunasnya tidak lagi digambar sebagai SVG beranimasi.');
    }

    // -------------------------------------------------- menutup tagihannya

    #[Test]
    public function membatalkan_pendaftaran_menutup_tagihannya_di_gerbang(): void
    {
        /*
         * Membatalkan di layar panitia TIDAK dengan sendirinya sampai ke
         * DOKU: tagihannya di sana hidup sampai tenggatnya habis, dan
         * pendaftar yang tabnya masih terbuka bisa tetap membayar kursi yang
         * sudah diberikan kepada orang lain.
         */
        Mail::fake();
        Http::fake(['*/checkout/v3/cancellations' => Http::response(['ok' => true], 200)]);

        $p = $this->pendaftaran();

        (new \App\Actions\Pendaftaran\UbahStatusPendaftaran)->jalankan(
            'scopus_camp', (string) $p->getKey(), 'Pendaftaran Dibatalkan', 'Panitia Uji'
        );

        Http::assertSent(function ($permintaan) use ($p) {
            if (! str_contains($permintaan->url(), '/checkout/v3/cancellations')) {
                return false;
            }

            $isi = json_decode($permintaan->body(), true);

            $this->assertSame($p->id_transaksi, $isi['order']['invoice_number']);
            $this->assertSame($p->bayar_permintaan_id, $isi['payment']['original_request_id'],
                'original_request_id-nya tidak ikut dikirim — DOKU menolak '
                . 'pembatalannya, dan tagihannya tetap bisa dibayar.');

            return true;
        });
    }
}
