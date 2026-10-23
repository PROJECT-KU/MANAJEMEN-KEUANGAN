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
        return PendaftaranScopusCamp::create(array_merge([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-UJI-' . Str::random(8),
            'kategori_id' => $this->angkatan()->getKey(),
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
        $this->assertStringContainsString('Menunggu pembayaran', $isi);
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
