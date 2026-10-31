<?php

namespace Tests\Feature\Promo;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\PromoLayanan;
use App\Support\PromoAngkatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo berkode: harganya tidak turun sampai kodenya diketik dan sah.
 *
 * Dua perkara sekaligus, dan keduanya tentang uang.
 *
 * SATU — lubang yang terukur 31 Okt 2026 di Scopus Camp. Besar potongan
 * diambil mentah dari `nominal_diskon` kiriman borang, tanpa satu baris pun
 * yang memeriksa apakah kodenya sah:
 *
 *     kode_diskon = KODE-KARANGAN   (angkatannya tidak punya kode apa pun)
 *     nominal_diskon = 5.499.000
 *
 * Kursi seharga Rp 5.500.000 tersimpan dengan tagihan Rp 2.039, dan tagihan
 * DOKU-nya benar-benar terbit atas angka itu. Jalur Analisis Bibliometrik
 * memakai pola yang sama persis.
 *
 * DUA — keputusan pemiliknya 31 Okt 2026: promo boleh punya kode, dan promo
 * berkode TIDAK memotong harga dengan sendirinya. Harga lamanya pun tidak
 * boleh tercoret sebelum kodenya masuk; kalau tercoret, orang sudah melihat
 * harga promo tanpa mengetik apa pun dan kodenya kehilangan gunanya.
 */
class KodePromoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-PROMO');
        config()->set('services.doku.secret_key', 'SK-UJI-PROMO');

        Mail::fake();

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/UJI'],
                'order' => ['invoice_number' => 'CAMP-UJI'],
            ],
        ], 200)]);

        PromoAngkatan::lupakan();
    }

    protected function tearDown(): void
    {
        PromoAngkatan::lupakan();

        parent::tearDown();
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Promo Kode ' . Str::random(6),
            'nama_ke' => (string) random_int(4000, 4999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujipromo',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ], $ganti));
    }

    private function promo(array $ganti = []): PromoLayanan
    {
        $p = PromoLayanan::create(array_merge([
            'nama' => 'UJI Promo ' . Str::random(5),
            'tipe' => 'rupiah',
            'nominal' => 1000000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addMonth()->toDateString(),
            'aktif' => true,
        ], $ganti));

        PromoAngkatan::lupakan();

        return $p;
    }

    private function isian(CategoriesScopusCamp $a, array $ganti = []): array
    {
        return array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Promo',
            'email' => 'promo' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ], $ganti);
    }

    private function pendaftaranTerakhir(CategoriesScopusCamp $a): ?PendaftaranScopusCamp
    {
        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->latest('created_at')->first();
    }

    // --------------------------------------------------- lubang nominal

    #[Test]
    public function potongan_karangan_dari_peramban_tidak_dipakai(): void
    {
        $a = $this->angkatan(['kode_diskon' => null, 'nominal_diskon' => null]);

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'kode_diskon' => 'KODE-KARANGAN',
            'nominal_diskon' => '5.499.000',
        ]));

        $p = $this->pendaftaranTerakhir($a);

        $this->assertNotNull($p, 'Pendaftarannya tidak tersimpan sama sekali.');

        $this->assertSame(0, (int) $p->nominal_diskon,
            'Potongan karangan dari peramban tetap dipakai: kursi Rp 5.500.000 '
            . 'bisa dibeli seharga kode uniknya saja.');

        $this->assertGreaterThan(5000000, (int) $p->total_pembayaran,
            'Tagihannya jauh di bawah harga kursinya.');
    }

    #[Test]
    public function kode_milik_angkatannya_sendiri_tetap_berlaku(): void
    {
        /*
         * Jalur lama tidak boleh ikut mati saat lubangnya ditutup: kode
         * angkatan sudah dicetak di bahan promosi yang beredar.
         */
        $a = $this->angkatan(['kode_diskon' => 'HEMAT500', 'nominal_diskon' => '500000']);

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'kode_diskon' => 'HEMAT500',
        ]));

        $p = $this->pendaftaranTerakhir($a);

        $this->assertSame(500000, (int) $p->nominal_diskon,
            'Kode milik angkatannya sendiri berhenti memotong.');
    }

    // ------------------------------------------------- promo berkode

    #[Test]
    public function promo_berkode_tidak_menurunkan_harga_sebelum_kodenya_masuk(): void
    {
        $a = $this->angkatan();
        $this->promo(['kode' => 'SALAMQ1'])->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        $a->refresh();

        $this->assertSame((int) $a->harga_sebelum_promo, (int) $a->harga_setelah_promo,
            'Harganya sudah turun padahal belum ada kode yang diketik.');

        $this->assertNull(PromoAngkatan::untuk($a),
            'Promo berkode ikut terbaca sebagai promo otomatis.');
    }

    #[Test]
    public function halaman_umum_tidak_mencoret_harga_karena_promo_berkode(): void
    {
        /*
         * Coretan di harga lama DIGAMBAR dari selisih harga_sebelum_promo dan
         * harga_setelah_promo. Selama keduanya sama, tidak ada yang digambar —
         * jadi yang diperiksa di sini halaman sungguhannya, bukan dua angka
         * yang sudah diperiksa uji di atas.
         */
        $a = $this->angkatan();
        $this->promo(['kode' => 'SALAMQ1'])->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        $isi = $this->get(route('public.scopuscamp.Selengkapnya', ['id' => $a->getKey()]))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('<s class=', $isi,
            'Harga lamanya tercoret padahal belum ada kode yang diketik.');
    }

    #[Test]
    public function promo_berkode_memotong_sesudah_kodenya_diketik(): void
    {
        $a = $this->angkatan();
        $this->promo(['kode' => 'SALAMQ1'])->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'kode_diskon' => 'SALAMQ1',
        ]));

        $p = $this->pendaftaranTerakhir($a);

        $this->assertSame(1000000, (int) $p->nominal_diskon,
            'Promo berkode tidak memotong walau kodenya sudah diketik dan sah.');
    }

    #[Test]
    public function kodenya_dicocokkan_tanpa_peduli_besar_kecil_huruf(): void
    {
        /*
         * Orang mengetik "salamq1", " SalamQ1 ", dan "SALAMQ1" untuk kode
         * yang sama. Dibandingkan apa adanya, dua dari tiga ditolak dan yang
         * mengetiknya tidak punya cara tahu bedanya di mana.
         *
         * YANG MENJAGA PERILAKU INI BUKAN KODE KITA, dan itu disebut di sini
         * supaya tidak ada yang mengira begitu. Collation sambungannya
         * utf8mb4_unicode_ci; diukur 31 Okt 2026, 'SALAMQ1' = 'salamq1'
         * memulangkan 1 di MySQL. Spasi tepinya dibuang PotonganKode.
         * Mencabut mb_strtoupper() dari rapikanKode() tidak memerahkan uji
         * ini — sudah dicoba.
         *
         * Ujinya tetap ada karena yang dijaga AKIBATNYA, bukan sebabnya: ia
         * memerah kalau collation-nya kelak diganti jadi peka huruf, atau
         * kalau rangkaian ujinya dipindah ke basis data yang '='-nya BINARY.
         */
        foreach (['salamq1', '  SalamQ1  ', 'SALAMQ1'] as $bentuk) {
            $a = $this->angkatan();
            $this->promo(['kode' => 'SALAMQ1'])->angkatan()->attach($a->getKey());
            PromoAngkatan::lupakan();

            $this->post(route('public.scopuscamp.store'), $this->isian($a, [
                'kode_diskon' => $bentuk,
            ]));

            $this->assertSame(1000000, (int) $this->pendaftaranTerakhir($a)->nominal_diskon,
                'Bentuk "' . $bentuk . '" ditolak padahal kodenya sama.');
        }
    }

    #[Test]
    public function promo_berkode_milik_angkatan_lain_tidak_bisa_dipakai(): void
    {
        $a = $this->angkatan();
        $lain = $this->angkatan();

        // Promonya dicentang pada angkatan LAIN, bukan pada $a.
        $this->promo(['kode' => 'SALAMQ1'])->angkatan()->attach($lain->getKey());
        PromoAngkatan::lupakan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'kode_diskon' => 'SALAMQ1',
        ]));

        $this->assertSame(0, (int) $this->pendaftaranTerakhir($a)->nominal_diskon,
            'Kode promo angkatan lain ikut memotong di angkatan ini.');
    }

    #[Test]
    public function promo_berkode_yang_sudah_lewat_tidak_memotong(): void
    {
        $a = $this->angkatan();

        $this->promo([
            'kode' => 'SALAMQ1',
            'mulai' => now()->subMonth()->toDateString(),
            'selesai' => now()->subDay()->toDateString(),
        ])->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        $this->post(route('public.scopuscamp.store'), $this->isian($a, [
            'kode_diskon' => 'SALAMQ1',
        ]));

        $this->assertSame(0, (int) $this->pendaftaranTerakhir($a)->nominal_diskon,
            'Promo yang tanggalnya sudah lewat tetap memotong.');
    }

    #[Test]
    public function promo_berkode_berlaku_juga_di_webinar_eksklusif(): void
    {
        /*
         * "Untuk semua layanan" berarti aturannya tidak boleh berhenti di
         * Scopus Camp. Webinar Eksklusif punya jalur kodenya sendiri sejak
         * awal — satu-satunya yang sudah benar — dan sebelum ini jalur itu
         * HANYA mengenali kode milik sesinya, jadi promo berkode tidak
         * pernah berlaku di sana walau panitia sudah mencentang sesinya.
         */
        $sesi = \App\KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'UJI Webinar Promo ' . Str::random(5),
            'nama_ke' => (string) random_int(3000, 3999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30',
            'platform' => 'Zoom', 'pemateri' => 'Pemateri Uji',
            'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);

        $this->promo(['kode' => 'SALAMQ1', 'tipe' => 'rupiah', 'nominal' => 30000])
            ->angkatan()->attach($sesi->getKey());
        PromoAngkatan::lupakan();

        $email = 'webinarpromo' . Str::random(5) . '@contoh.test';

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Pendaftar Promo',
            'email' => $email, 'telp' => '08123450002',
            'jumlah_pendaftar' => 1, 'setuju' => '1',
            'kode_diskon' => 'SALAMQ1',
        ]);

        $p = \App\WebinarEksklusifPendaftaran::where('email', $email)->first();

        $this->assertNotNull($p, 'Pendaftarannya tidak tersimpan sama sekali.');

        $this->assertSame(30000, (int) $p->nominal_diskon,
            'Promo berkode tidak berlaku di Webinar Eksklusif.');
    }

    #[Test]
    public function promo_tanpa_kode_tetap_berlaku_otomatis_seperti_dulu(): void
    {
        /*
         * Yang sudah ada tidak boleh ikut berubah. Promo tanpa kode memang
         * dimaksudkan memotong tanpa diminta, dan empat promo di basis data
         * sungguhan semuanya begitu.
         */
        $a = $this->angkatan();
        $this->promo(['kode' => null])->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        $a->refresh();

        $this->assertSame(1000000, (int) $a->potongan_promo,
            'Promo tanpa kode berhenti berlaku otomatis.');
    }
}
