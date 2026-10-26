<?php

namespace Tests\Feature\ScopusKafe;

use App\PendaftaranScopusKafe;
use App\Support\HargaKafe;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Scopus Kafe: dari memilih sesi sampai hasil pembayarannya.
 *
 * Dirombak 26 Okt 2026 mengikuti Scopus Camp. Yang dijaga di sini hal-hal
 * yang kalau rusak TIDAK mengeluarkan galat apa pun:
 *
 *   - tujuh medan uang yang dulu datang dari peramban dan disimpan mentah;
 *   - tanggal yang bukan hari penyelenggaraan, yang dulu diterima lalu
 *     ditolak panitia dengan tangan lewat WhatsApp;
 *   - pemesanan tanpa cara membayar sama sekali;
 *   - tautan halaman status yang diserahkan kepada yang menebak nama dan
 *     email orang lain.
 */
class AlurPemesananTest extends TestCase
{
    use DatabaseTransactions;

    private const KLIEN = 'BRN-UJI-KAFE';

    private const RAHASIA = 'SK-UJI-KAFE';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);
    }

    /** Jadwal sesi uji; dibuat sendiri supaya tidak bergantung isi katalog. */
    private function sesi(array $ganti = []): object
    {
        $id = (string) Str::uuid();

        DB::table('meme')->insert(array_merge([
            'id' => $id,
            'token' => Str::random(30),
            'sesi' => 'Sesi Uji ' . Str::upper(Str::random(4)),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '13:00:00',
            'kuota' => '25',

            // Bertitik, seperti isi sungguhannya: dibaca apa adanya sebagai
            // angka, PHP memulangkan 1 dan sesinya berharga satu rupiah.
            'biaya' => '1.000.000',
            'lokasi' => 'Rumah Scopus Pusat',
            'status' => 'publish',
            'created_at' => now(),
            'updated_at' => now(),
        ], $ganti));

        return DB::table('meme')->where('id', $id)->first();
    }

    private function jumatBerikutnya(): Carbon
    {
        $hari = now()->startOfDay();

        while (! HargaKafe::hariBuka($hari)) {
            $hari = $hari->addDay();
        }

        return $hari;
    }

    /** @return array<string, mixed> */
    private function isian(array $sesi, array $ganti = []): array
    {
        return array_merge([
            'nama' => 'Pemesan Uji',
            'email' => 'kafe' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'tanggal_pemesanan' => $this->jumatBerikutnya()->toDateString(),
            'sesi' => collect($sesi)->pluck('id')->all(),
            'setuju' => '1',
        ], $ganti);
    }

    private function gerbangMenerima(string $url = 'https://staging.doku.com/checkout-link-v2/KAFE'): void
    {
        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => $url]],
        ], 200)]);
    }

    // ----------------------------------------------------------- harganya

    #[Test]
    public function harganya_dihitung_peladen_bukan_diterima_dari_borang(): void
    {
        /*
         * PENJAGA TERPENTING di berkas ini.
         *
         * Sebelum ini `biaya`, `kode_unik_pembayaran`, `subtotal_pembayaran`
         * (tiga kali, satu per sesi), dan `total_keseluruhan_pembayaran`
         * seluruhnya datang dari kiriman borang lalu disimpan mentah — tujuh
         * medan uang yang ditentukan sisi yang tidak bisa dipercaya. Siapa
         * pun bisa memesan tiga sesi bertotal nol tanpa satu baris pun yang
         * menolak, dan panitia baru tahu saat mencocokkan mutasi.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi], [
            // Kiriman jahat: nominalnya dikirim nol.
            'biaya' => '0',
            'subtotal_pembayaran' => '0',
            'total_keseluruhan_pembayaran' => '0',
        ]))->assertRedirect('https://staging.doku.com/checkout-link-v2/KAFE');

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        $this->assertSame(1000000, (int) $p->biaya,
            'Harga sesinya diambil dari kiriman borang, bukan dari katalognya.');

        $this->assertGreaterThanOrEqual(1000000 + 1, (int) $p->total_keseluruhan_pembayaran,
            'Totalnya diterima dari peramban; pemesan bisa menentukan sendiri '
            . 'berapa yang ditagihkan kepadanya.');

        $this->assertSame(
            1000000 + (int) $p->kode_unik_pembayaran,
            (int) $p->total_keseluruhan_pembayaran,
            'Totalnya tidak sama dengan harga sesi ditambah kode uniknya.');
    }

    #[Test]
    public function biaya_bertitik_di_katalog_terbaca_utuh(): void
    {
        /*
         * Kolom `biaya` di katalog sesinya varchar dan isinya memang bertitik
         * — terukur: "1.000.000". Dibaca apa adanya sebagai angka, PHP
         * memulangkan 1, dan seluruh sesi berharga satu rupiah tanpa satu
         * galat pun.
         */
        $this->assertSame(1000000, HargaKafe::rupiah('1.000.000'));
        $this->assertSame(1500000, HargaKafe::rupiah('Rp 1.500.000,-'));
    }

    #[Test]
    public function sesi_yang_sama_tidak_dihitung_dua_kali(): void
    {
        /*
         * Borangnya memilih sampai tiga sesi, dan tidak ada yang menghalangi
         * orang mengirim kunci yang sama tiga kali — lalu ditagih tiga juta
         * untuk satu sesi yang sama.
         */
        $sesi = $this->sesi();

        $harga = HargaKafe::hitung([$sesi->id, $sesi->id, $sesi->id], 900);

        $this->assertCount(1, $harga['sesi']);
        $this->assertSame(1000000, $harga['subtotal']);
    }

    #[Test]
    public function kunci_sesi_karangan_tidak_dihargai_nol(): void
    {
        /*
         * Kunci yang tidak ada di katalog DILEWATI, bukan dihargai nol —
         * dihargai nol, pemesanan berisi satu sesi sungguhan dan dua sesi
         * karangan tetap tersimpan dan tagihannya kurang.
         */
        $sesi = $this->sesi();

        $harga = HargaKafe::hitung([$sesi->id, (string) Str::uuid()], 900);

        $this->assertCount(1, $harga['sesi']);
        $this->assertSame(1000000, $harga['subtotal']);
    }

    // ------------------------------------------------------- hari bukanya

    #[Test]
    public function pemesanan_di_luar_akhir_pekan_ditolak(): void
    {
        /*
         * Layanan ini TIDAK BERANGKATAN — terbuka terus — tetapi hanya
         * berjalan Jumat, Sabtu, Minggu. Tanpa aturan ini orang memesan hari
         * Selasa, panitia menolaknya dengan tangan lewat WhatsApp, dan
         * pemesannya mengulang dari nol — kadang sesudah membayar.
         */
        $sesi = $this->sesi();

        $selasa = now()->startOfDay();

        while ($selasa->dayOfWeekIso !== 2) {
            $selasa = $selasa->addDay();
        }

        Http::fake();

        $this->post(route('public.scopuskafe.store'),
            $this->isian([$sesi], ['tanggal_pemesanan' => $selasa->toDateString()]))
            ->assertSessionHasErrors('tanggal_pemesanan');

        $this->assertSame(0, PendaftaranScopusKafe::where('nama', 'Pemesan Uji')->count(),
            'Pemesanan di hari yang tidak dilayani tetap tersimpan.');

        Http::assertNothingSent();
    }

    #[Test]
    public function tanggal_yang_sudah_lewat_ditolak(): void
    {
        /*
         * Sisi yang lain dari aturan tanggal: hari Jumat yang sudah lewat
         * tetap hari Jumat, jadi pemeriksaan hari saja meloloskannya.
         */
        $sesi = $this->sesi();

        $jumatLalu = now()->startOfDay()->subDays(7);

        while (! HargaKafe::hariBuka($jumatLalu)) {
            $jumatLalu = $jumatLalu->subDay();
        }

        Http::fake();

        $this->post(route('public.scopuskafe.store'),
            $this->isian([$sesi], ['tanggal_pemesanan' => $jumatLalu->toDateString()]))
            ->assertSessionHasErrors('tanggal_pemesanan');

        Http::assertNothingSent();
    }

    #[Test]
    public function ketiga_hari_bukanya_diterima(): void
    {
        /*
         * Sisi sebaliknya, dan ia perlu: aturan yang terlalu ketat menolak
         * pemesanan yang sah, dan itu tidak terlihat dari uji yang cuma
         * memeriksa penolakan.
         */
        foreach ([5, 6, 7] as $hari) {
            $tanggal = now()->startOfDay();

            while ($tanggal->dayOfWeekIso !== $hari) {
                $tanggal = $tanggal->addDay();
            }

            $this->assertTrue(HargaKafe::hariBuka($tanggal),
                'Hari ke-' . $hari . ' ditolak, padahal termasuk hari penyelenggaraan.');
        }

        foreach ([1, 2, 3, 4] as $hari) {
            $tanggal = now()->startOfDay();

            while ($tanggal->dayOfWeekIso !== $hari) {
                $tanggal = $tanggal->addDay();
            }

            $this->assertFalse(HargaKafe::hariBuka($tanggal),
                'Hari ke-' . $hari . ' diterima, padahal bukan hari penyelenggaraan.');
        }
    }

    // --------------------------------------------------------- gerbangnya

    #[Test]
    public function pemesanan_diantar_ke_gerbang_dan_tagihannya_tersimpan(): void
    {
        $sesi = $this->sesi();
        $this->gerbangMenerima('https://staging.doku.com/checkout-link-v2/ABC');

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]))
            ->assertRedirect('https://staging.doku.com/checkout-link-v2/ABC');

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        $this->assertSame('doku', $p->cara_bayar);
        $this->assertSame($p->id_pemesanan, $p->bayar_nomor);
        $this->assertNotNull($p->kedaluwarsa_pada);

        /*
         * Rinciannya HARUS berjumlah sama dengan totalnya, kalau tidak
         * gerbang menolak dengan "AMOUNT NOT MATCH" dan pemesannya cuma
         * melihat "pembayaran sedang bermasalah".
         */
        Http::assertSent(function ($permintaan) use ($p) {
            if (! str_contains($permintaan->url(), 'checkout')) {
                return false;
            }

            $this->assertSame(
                (int) $p->total_keseluruhan_pembayaran,
                (int) data_get($permintaan->data(), 'order.amount'));

            return true;
        });
    }

    #[Test]
    public function tanpa_gerbang_pemesanan_ditolak_tanpa_menyimpan_apa_pun(): void
    {
        /*
         * Jalur unggah bukti sudah dicabut, jadi tanpa gerbang tidak ada cara
         * menerima uang sama sekali. Ditolak dengan terang, bukan diam-diam
         * membuat pemesanan yang tidak bisa dibayar.
         */
        config()->set('services.doku.client_id', '');
        config()->set('services.doku.secret_key', '');

        $sesi = $this->sesi();
        $sebelum = PendaftaranScopusKafe::count();

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]))
            ->assertSessionHas('error');

        $this->assertSame($sebelum, PendaftaranScopusKafe::count(),
            'Pemesanan tetap tersimpan walau pembayarannya tidak mungkin diselesaikan.');

        // Borangnya pun tidak digambar: menggambarnya berarti orang mengisi
        // empat isian untuk hal yang sudah diketahui sejak awal.
        $this->followingRedirects()
            ->get(route('public.scopuskafe.formpendaftaran'))
            ->assertOk()
            ->assertSee('Pembayaran daring sedang tidak tersedia', false);
    }

    #[Test]
    public function borang_tidak_lagi_memajang_rekening_maupun_kotak_unggah(): void
    {
        /*
         * Nomor rekening yang tercetak di halaman yang pembayarannya lewat
         * gerbang mengundang transfer yang tidak dicocokkan siapa pun:
         * uangnya masuk, tidak ada tagihan yang tertutup, dan pengirimnya
         * tetap tercatat belum bayar sampai ia mengadu.
         */
        $this->sesi();

        $isi = $this->get(route('public.scopuskafe.formpendaftaran'))
            ->assertOk()->getContent();

        foreach (['name="gambar"', 'type="file"', '216401000467563'] as $jejak) {
            $this->assertStringNotContainsString($jejak, $isi,
                'Bekas jalur transfer manual masih tergambar di borang: ' . $jejak);
        }
    }

    // ------------------------------------------------------ halaman hasil

    #[Test]
    public function halaman_hasilnya_menyebut_nomor_tanggal_dan_sesinya(): void
    {
        /*
         * Sebelum ini TIDAK ADA halaman hasil sama sekali: sesudah mengirim
         * borang orangnya dipantulkan ke halaman daftar dengan satu kotak
         * hijau — tanpa nomor pemesanan, tanpa cara kembali, tanpa cara tahu
         * pembayarannya sudah masuk atau belum.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $isi = $this->get(route('public.scopuskafe.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString(strtoupper($p->id_pemesanan), $isi,
            'Nomor pemesanannya tidak disebut; orangnya tidak punya apa pun untuk '
            . 'disebut saat menghubungi panitia.');

        $this->assertStringContainsString($sesi->sesi, $isi, 'Sesinya tidak disebut.');

        $this->assertStringContainsString(
            number_format((int) $p->total_keseluruhan_pembayaran, 0, ',', '.'), $isi,
            'Nominal tagihannya tidak disebut.');
    }

    #[Test]
    public function nama_sesi_bertulis_besar_tidak_dirusak_di_layar(): void
    {
        /*
         * Tiga halaman publik membesarkan huruf nama sesi karena isinya di
         * basis data berhuruf kecil semua ("sesi 1"). Str::title telanjang
         * membesarkannya TERMASUK nama yang panitia sengaja tulis dengan
         * singkatan: "Sesi SEO" keluar jadi "Sesi Seo" di daftar, di borang,
         * dan di halaman status — salah di tiga tempat sekaligus dan tidak
         * ada galat yang memberi tahu.
         */
        $sesi = $this->sesi(['sesi' => 'Sesi SEO & AI']);
        $this->gerbangMenerima();

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $halaman = [
            route('public.scopuskafe.index'),
            route('public.scopuskafe.formpendaftaran'),
            route('public.scopuskafe.status', $p->getKey()),
        ];

        foreach ($halaman as $alamat) {
            $isi = $this->get($alamat)->assertOk()->getContent();

            $this->assertStringContainsString('Sesi SEO &amp; AI', $isi,
                'Nama sesinya dirusak di ' . $alamat . ': singkatan yang ditulis '
                . 'panitia ikut dibesarkan hurufnya.');
        }

        // Yang berhuruf kecil memang masih dibesarkan; itu gunanya.
        $this->assertSame('Sesi 1', HargaKafe::namaSesi('sesi 1'));
    }

    #[Test]
    public function pembayaran_lewat_gerbang_menandai_lunas(): void
    {
        $sesi = $this->sesi();
        $this->gerbangMenerima();

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        $this->kirimPemberitahuan([
            'order' => ['invoice_number' => $p->bayar_nomor],
            'transaction' => ['status' => 'SUCCESS'],
        ])->assertOk();

        $p->refresh();

        $this->assertSame('pembayaran diterima', $p->status);
        $this->assertNotNull($p->bayar_pada);
    }

    #[Test]
    public function kwitansi_hanya_setelah_uangnya_masuk(): void
    {
        /*
         * Kwitansi menyatakan uangnya SUDAH diterima. Diberikan sebelum itu,
         * ia jadi surat yang kami tanda tangani sendiri untuk sesuatu yang
         * belum terjadi — dan bisa dipakai menagih balik lembaga ini.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();
        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $this->get(route('public.scopuskafe.dokumen', [$p->getKey(), 'kwitansi']))
            ->assertRedirect(route('public.scopuskafe.status', $p->getKey()))
            ->assertSessionHas('error');

        $jawab = $this->get(route('public.scopuskafe.dokumen', [$p->getKey(), 'faktur']))
            ->assertOk();

        $this->assertStringStartsWith('%PDF', $jawab->getContent());
    }

    #[Test]
    public function nama_dan_email_orang_lain_tidak_membuka_halaman_statusnya(): void
    {
        /*
         * Penjaga pemesanan ganda mencocokkan tanggal + nama + email, dan
         * ketiganya bukan rahasia. Mengantar pengirimnya ke halaman status
         * berarti menyerahkan UUID-nya kepada siapa pun yang menebak nama dan
         * email orang lain — beserta nomor telepon, sesi, dan fakturnya.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();

        $isian = $this->isian([$sesi]);

        $this->post(route('public.scopuskafe.store'), $isian);

        $jawab = $this->post(route('public.scopuskafe.store'), $isian);

        $this->assertSame(1, PendaftaranScopusKafe::where('email', $isian['email'])->count(),
            'Kiriman kedua membuat pemesanan kedua.');

        $this->assertStringNotContainsString('/Scopus-Kafe/Status/',
            (string) $jawab->headers->get('location'),
            'Yang tahu nama dan email orang lain diantar ke halaman status milik '
            . 'orang itu.');

        Mail::assertSent(\App\Mail\TautanPendaftaranScopusKafeMail::class, 1);
    }

    #[Test]
    public function tagihan_kedaluwarsa_ditegakkan_tanpa_menunggu_cron(): void
    {
        /*
         * Cron hPanel di peladen ini belum terpasang, jadi tanpa pemanggilan
         * dari jalur web tenggat yang dipajang halaman status tidak pernah
         * ditegakkan siapa pun — tagihannya mati, tetapi halamannya tetap
         * menawarkan tombol bayar yang mengantar ke halaman galat.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima('https://staging.doku.com/checkout-link-v2/MATI');

        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();
        $p->forceFill(['kedaluwarsa_pada' => now()->subHours(2)])->save();

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $isi = $this->get(route('public.scopuskafe.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertSame('expired', (string) $p->refresh()->bayar_status,
            'Tenggatnya tidak ditegakkan; tagihannya mati tetapi barisnya tetap '
            . 'terbaca menunggu pembayaran.');

        $this->assertStringNotContainsString('MATI', $isi,
            'Halamannya masih menawarkan tagihan yang sudah mati.');

        $this->assertStringContainsString('Buat tagihan baru', $isi,
            'Tidak ada jalan keluar; pemesannya harus menghubungi panitia.');
    }

    #[Test]
    public function tagihan_baru_memakai_nomor_yang_belum_pernah_dipakai(): void
    {
        /*
         * Gerbang menolak nomor tagihan yang sudah pernah dipakai dengan
         * "INVOICE ALREADY USED" — terukur di Scopus Camp 24 Okt 2026. Nomor
         * PEMESANANNYA tidak berubah: itu yang sudah ada di surat dan di
         * catatan panitia.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();
        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();
        $nomorAsli = $p->id_pemesanan;

        $this->gerbangMenerima();
        $this->post(route('public.scopuskafe.bayarulang', $p->getKey()));

        $p->refresh();

        $this->assertSame($nomorAsli, $p->id_pemesanan,
            'Nomor pemesanannya berubah; yang ada di surat tidak cocok lagi.');

        $this->assertSame($nomorAsli . '-R2', $p->bayar_nomor,
            'Tagihan barunya memakai nomor yang sudah pernah dipakai.');
    }

    #[Test]
    public function tagihan_baru_ditolak_setelah_tanggal_sesinya_lewat(): void
    {
        /*
         * Tanpa ini, yang tagihannya mati setelah harinya lewat tetap bisa
         * membayar sesi yang sudah selesai.
         */
        $sesi = $this->sesi();
        $this->gerbangMenerima();
        $this->post(route('public.scopuskafe.store'), $this->isian([$sesi]));

        $p = PendaftaranScopusKafe::latest('created_at')->first();
        $p->forceFill(['tanggal_pemesanan' => now()->subWeek()])->save();

        Http::fake();

        $this->post(route('public.scopuskafe.bayarulang', $p->getKey()))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    // -------------------------------------------------------- halamannya

    #[Test]
    public function halaman_daftar_menyebut_kapan_acaranya_berjalan(): void
    {
        /*
         * Layanan ini tidak berangkatan, jadi pertanyaan pertama yang muncul
         * justru "kapan bisanya?" — dan bentuk lamanya tidak menjawabnya sama
         * sekali: orangnya baru menemukannya di borang, sesudah mengisi nama.
         */
        $this->sesi();

        $this->get(route('public.scopuskafe.index'))
            ->assertOk()
            ->assertSee('Jumat, Sabtu, atau Minggu', false)
            ->assertSee(route('public.scopuskafe.tautansaya'), false);
    }

    #[Test]
    public function halaman_daftar_tidak_menawarkan_pesan_saat_gerbang_mati(): void
    {
        /*
         * Tanpa gerbang, borangnya memang dipantulkan pengendalinya.
         * Menawarkan tombolnya berarti mengantar orang ke jalan buntu.
         */
        config()->set('services.doku.client_id', '');
        config()->set('services.doku.secret_key', '');

        $this->sesi();

        $this->get(route('public.scopuskafe.index'))
            ->assertOk()
            ->assertSee('Pemesanan ditutup sementara', false)
            ->assertDontSee('Pesan sekarang', false);
    }

    // ------------------------------------------------------------ pembantu

    private function kirimPemberitahuan(array $isi)
    {
        $jalur = '/Scopus-Kafe/pemberitahuan/doku';
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

        return $this->call('POST', $jalur, [], [], [], [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => 'HMACSHA256=' . base64_encode(
                hash_hmac('sha256', $bahan, self::RAHASIA, true)
            ),
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }
}
