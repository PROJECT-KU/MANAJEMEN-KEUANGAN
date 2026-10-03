<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layanan Webinar Eksklusif: API untuk halaman landing, borang pendaftaran,
 * penjaga kuota, dan pemberitahuan balik dari gerbang pembayaran.
 */
class WebinarEksklusifTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    /**
     * Menutup semua sesi yang ada supaya ujinya mulai dari keadaan kosong.
     *
     * DITUTUP, bukan dihapus. Menghapusnya gagal begitu ada satu pendaftaran
     * yang menunjuk ke sana — tabel pendaftarannya berkunci asing ke sini —
     * dan ujinya merah dengan galat 1451 yang tidak menyebut apa pun tentang
     * yang sedang diuji.
     */
    private function tutupSemuaSesi(): void
    {
        KategoriLayanan::where('layanan', 'webinar_eksklusif')
            ->update(['status' => 'non active']);
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Uji ' . Str::random(5),
            'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30',
            'jam_selesai' => '11:30',
            'platform' => 'Zoom',
            'pemateri' => 'Pemateri Uji',
            'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20',
            'sisa_kuota' => '20',
            'biaya' => '129000',
            'status' => 'active',
        ], $lain));
    }

    // ------------------------------------------------------------- katalog

    #[Test]
    public function layanan_dan_tarifnya_sudah_ada(): void
    {
        $this->assertArrayHasKey('webinar_eksklusif', Layanan::katalog());

        $tarif = ClinikScopusBiayaPersesi::berlaku('webinar_eksklusif', null);

        $this->assertNotNull($tarif, 'Tarif Webinar Eksklusif harus ada.');
        $this->assertSame(129000, (int) $tarif->biaya_persesi);
    }

    #[Test]
    public function tabel_pendaftarannya_terdaftar_di_model(): void
    {
        /*
         * Tanpa ini, peserta Webinar Eksklusif tidak terhitung di layar Angkatan
         * dan angkatannya bisa dihapus walau sudah ada yang mendaftar —
         * persis celah yang sudah ditemukan pada Scopus Cafe dan Clinik
         * Scopus sebelumnya.
         */
        $this->assertSame(
            'webinar_eksklusif_pendaftaran',
            KategoriLayanan::TABEL_PENDAFTARAN['webinar_eksklusif'] ?? null
        );

        $this->assertFalse($this->sesi()->belumPunyaPendaftaran());
    }

    // ----------------------------------------------------------------- API

    #[Test]
    public function api_mengirim_sesi_yang_sedang_dibuka(): void
    {
        $sesi = $this->sesi(['nama' => 'Sesi API Uji']);

        $jawab = $this->getJson('/api/webinar-eksklusif');

        $jawab->assertOk()
            ->assertJsonPath('ada', true)
            ->assertJsonPath('sesi.harga', 129000)
            ->assertJsonPath('sesi.harga_tulis', 'Rp 129.000')
            ->assertJsonPath('sesi.jam', '09.30 - 11.30 WIB')
            ->assertJsonPath('sesi.platform', 'Zoom')
            ->assertJsonPath('sesi.pemateri.nama', 'Pemateri Uji');

        // Alamat borangnya ikut dikirim supaya halaman landing cukup
        // memasangnya ke tombol tanpa tahu bagaimana ia dirakit.
        $this->assertStringContainsString($sesi->token, $jawab->json('sesi.daftar_url'));
    }

    #[Test]
    public function api_tidak_mengirim_draf_maupun_yang_sudah_lewat(): void
    {
        /*
         * Yang membacanya halaman iklan. Draf yang bocor ke sana berarti
         * harga dan tanggal yang belum final terpampang di depan umum, dan
         * sesi yang sudah lewat berarti orang mendaftar ke acara kemarin.
         */
        $this->tutupSemuaSesi();

        $this->sesi(['status' => 'draft']);
        $this->sesi([
            'status' => 'active',
            'mulai' => now()->subWeek()->toDateString(),
            'selesai' => now()->subWeek()->toDateString(),
        ]);

        $this->getJson('/api/webinar-eksklusif')->assertOk()->assertJsonPath('ada', false);
    }

    #[Test]
    public function api_menjawab_200_walau_belum_ada_sesi(): void
    {
        // 200, bukan 404: "belum ada sesi" jawaban yang sah, dan 404 membuat
        // halaman landing menampilkan galat merah padahal tidak ada yang rusak.
        $this->tutupSemuaSesi();

        $this->getJson('/api/webinar-eksklusif')
            ->assertOk()
            ->assertJsonPath('ada', false)
            ->assertJsonPath('sesi', null);
    }

    #[Test]
    public function api_memecah_kontak_jadi_tautan_whatsapp(): void
    {
        $this->sesi();

        $kontak = $this->getJson('/api/webinar-eksklusif')->json('sesi.kontak');

        $this->assertNotEmpty($kontak);
        // 0889... jadi 6289... supaya tautannya bisa dipakai langsung.
        $this->assertStringStartsWith('62', $kontak[0]['wa']);
    }

    #[Test]
    public function api_tidak_pernah_menulis_apa_pun(): void
    {
        // Endpoint ini tanpa autentikasi dan terbuka ke internet; satu jalur
        // tulis di sana berarti siapa pun bisa mengubah data.
        $this->postJson('/api/webinar-eksklusif')->assertStatus(405);
        $this->putJson('/api/webinar-eksklusif')->assertStatus(405);
        $this->deleteJson('/api/webinar-eksklusif')->assertStatus(405);
    }

    // ---------------------------------------------------------- pendaftaran

    #[Test]
    public function borang_pendaftaran_terbuka_dengan_token_yang_benar(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', [$sesi->id, $sesi->token]))
            ->assertOk()
            ->assertSee($sesi->nama)
            ->assertSee('Rp 129.000');
    }

    #[Test]
    public function borang_menolak_token_yang_salah(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', [$sesi->id, 'token-karangan']))
            ->assertRedirect(route('public.webinareksklusif.index'));
    }

    #[Test]
    public function mendaftar_menyimpan_dan_memotong_kuota(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Budi Santoso',
            'email' => 'Budi@Contoh.Test',
            'telp' => '0812-3456-7890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 3, 'setuju' => '1',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->assertNotNull($p);
        $this->assertSame('budi@contoh.test', $p->email, 'Emailnya dikecilkan.');
        $this->assertSame('6281234567890', $p->telp, 'Nomornya dirapikan jadi 62.');
        $this->assertSame(3, $p->jumlah_pendaftar);
        $this->assertSame(387000, (int) $p->total_pembayaran, '129.000 x 3.');
        $this->assertSame('pending', $p->status);
        $this->assertNotNull($p->kedaluwarsa_pada);

        // Kursi dipotong SEKARANG, bukan nanti saat lunas.
        $this->assertSame(17, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function nomor_pendaftaran_bisa_dibacakan_lewat_telepon(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ani', 'email' => 'ani@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->assertMatchesRegularExpression('/^WE-\d{8}-\d{4}$/', $p->id_transaksi);
    }

    #[Test]
    public function tidak_bisa_mendaftar_melebihi_sisa_kuota(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '2']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Cici', 'email' => 'cici@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 5, 'setuju' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
        $this->assertSame(2, (int) $sesi->fresh()->sisa_kuota, 'Kuotanya tidak boleh ikut terpotong.');
    }

    #[Test]
    public function tidak_bisa_mendaftar_ke_sesi_yang_sudah_ditutup(): void
    {
        $sesi = $this->sesi(['status' => 'non active']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Dedi', 'email' => 'dedi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect(route('public.webinareksklusif.index'));

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function halaman_status_memperlihatkan_cara_bayar(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Eka', 'email' => 'eka@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->get(route('public.webinareksklusif.status', $p->token))
            ->assertOk()
            ->assertSee($p->id_transaksi)
            ->assertSee('Rp 129.000');
    }

    // ------------------------------------------------------ pembayaran DOKU

    #[Test]
    public function pemberitahuan_tanpa_tanda_tangan_ditolak(): void
    {
        /*
         * Penjagaan yang paling penting di seluruh layanan ini. Alamat
         * pemberitahuannya dikecualikan dari CSRF, jadi tanpa pemeriksaan
         * tanda tangan siapa pun yang tahu alamatnya bisa menandai
         * pendaftaran mana pun sebagai lunas dengan satu permintaan POST.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Fani', 'email' => 'fani@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->postJson(route('public.webinareksklusif.pemberitahuan'), [
            'order' => ['invoice_number' => $p->id_transaksi],
            'transaction' => ['status' => 'SUCCESS'],
        ])->assertStatus(401);

        $this->assertSame('pending', $p->fresh()->status, 'Statusnya tidak boleh berubah.');
    }

    #[Test]
    public function pemberitahuan_bertanda_tangan_benar_melunasi(): void
    {
        config(['services.doku.client_id' => 'UJI-CLIENT', 'services.doku.secret_key' => 'UJI-RAHASIA']);

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Gina', 'email' => 'gina@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->kirimPemberitahuan($p->id_transaksi, 'SUCCESS')->assertOk();

        $p->refresh();

        $this->assertSame('paid', $p->status);
        $this->assertNotNull($p->bayar_pada);
        // Kursinya sudah dipotong saat mendaftar; melunasi tidak memotong lagi.
        $this->assertSame(18, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function pemberitahuan_yang_sama_dua_kali_tidak_diproses_ulang(): void
    {
        // DOKU boleh mengirim pemberitahuan yang sama lebih dari sekali.
        config(['services.doku.client_id' => 'UJI-CLIENT', 'services.doku.secret_key' => 'UJI-RAHASIA']);

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Hadi', 'email' => 'hadi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->kirimPemberitahuan($p->id_transaksi, 'SUCCESS')->assertOk();
        $waktuPertama = $p->fresh()->bayar_pada;

        $this->kirimPemberitahuan($p->id_transaksi, 'SUCCESS')->assertOk();

        $this->assertEquals($waktuPertama, $p->fresh()->bayar_pada);
        $this->assertSame(19, (int) $sesi->fresh()->sisa_kuota, 'Kuotanya tidak boleh terpotong dua kali.');
    }

    #[Test]
    public function pembayaran_gagal_mengembalikan_kursinya(): void
    {
        config(['services.doku.client_id' => 'UJI-CLIENT', 'services.doku.secret_key' => 'UJI-RAHASIA']);

        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ika', 'email' => 'ika@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 4, 'setuju' => '1',
        ]);

        $this->assertSame(16, (int) $sesi->fresh()->sisa_kuota);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->kirimPemberitahuan($p->id_transaksi, 'EXPIRED')->assertOk();

        $this->assertSame('expired', $p->fresh()->status);
        $this->assertSame(20, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function perintah_terjadwal_melepas_kursi_yang_tertahan(): void
    {
        /*
         * Jaring pengaman kalau pemberitahuan DOKU tidak pernah sampai —
         * peladen mati, jaringan putus. Tanpa ini kursinya tertahan selamanya.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Joko', 'email' => 'joko@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 3, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();
        $p->forceFill(['kedaluwarsa_pada' => now()->subHour()])->save();

        $this->artisan('webinar-eksklusif:kedaluwarsakan')->assertSuccessful();

        $this->assertSame('expired', $p->fresh()->status);
        $this->assertSame(20, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function jalan_kering_tidak_melepas_apa_pun(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Kiki', 'email' => 'kiki@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();
        $p->forceFill(['kedaluwarsa_pada' => now()->subHour()])->save();

        $this->artisan('webinar-eksklusif:kedaluwarsakan', ['--kering' => true])->assertSuccessful();

        $this->assertSame('pending', $p->fresh()->status);
        $this->assertSame(18, (int) $sesi->fresh()->sisa_kuota);
    }

    /**
     * Mengirim pemberitahuan bertanda tangan sah, persis seperti DOKU.
     *
     * Tanda tangannya dihitung dengan aturan yang sama seperti di Doku, jadi
     * uji ini ikut menjaga aturan itu: kalau urutan barisnya diubah, uji ini
     * merah bersama produksinya.
     */
    private function kirimPemberitahuan(string $nomor, string $status)
    {
        $jalur = '/Webinar-Eksklusif/pemberitahuan/doku';

        $isi = json_encode([
            'order' => ['invoice_number' => $nomor],
            'transaction' => ['status' => $status],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $requestId = (string) Str::uuid();
        $waktu = gmdate('Y-m-d\TH:i:s\Z');

        $bahan = implode("\n", [
            'Client-Id:' . config('services.doku.client_id'),
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . base64_encode(hash('sha256', $isi, true)),
        ]);

        $tanda = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $bahan, config('services.doku.secret_key'), true)
        );

        return $this->call('POST', $jalur, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CLIENT_ID' => config('services.doku.client_id'),
            'HTTP_REQUEST_ID' => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $waktu,
            'HTTP_SIGNATURE' => $tanda,
        ], $isi);
    }

    // ----------------------------------------------- penjaga isian & ganda

    /**
     * Sampai 2 Okt 2026 nomor WhatsApp divalidasi sebagai teks biasa
     * ('required|string|max:30'), lalu semua non-angka dibuang saat disimpan.
     *
     * Akibatnya "tidak punya wa" LOLOS dan yang tersimpan string kosong —
     * pendaftarannya diterima, kursinya terpotong, dan panitia baru tahu saat
     * hendak memasukkan orangnya ke grup. Tidak ada galat, tidak ada gejala.
     */
    #[Test]
    public function nomor_whatsapp_yang_bukan_angka_ditolak(): void
    {
        $sesi = $this->sesi();

        foreach (['tidak punya wa', 'abc', '-', '08'] as $buruk) {
            $this->post(route('public.webinareksklusif.store'), [
                'kategori_id' => $sesi->id, 'nama' => 'Uji', 'email' => 'uji+' . md5($buruk) . '@contoh.test',
                'telp' => $buruk, 'jumlah_pendaftar' => 1, 'setuju' => '1',
            ])->assertSessionHasErrors('telp');
        }

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'tidak satu pun boleh tersimpan');

        $this->assertSame('20', (string) $sesi->fresh()->sisa_kuota,
            'kuotanya tidak boleh terpotong oleh kiriman yang ditolak');
    }

    #[Test]
    public function nomor_whatsapp_yang_benar_tetap_diterima_dan_dirapikan(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Uji', 'email' => 'rapi@contoh.test',
            'telp' => '+62 812-3456-7890', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $this->assertSame('6281234567890',
            WebinarEksklusifPendaftaran::where('email', 'rapi@contoh.test')->value('telp'));
    }

    #[Test]
    public function persetujuan_wajib_dicentang(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Uji', 'email' => 'tanpa@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ])->assertSessionHasErrors('setuju');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function waktu_persetujuan_ikut_disimpan(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Uji', 'email' => 'setuju@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        // Waktunya, bukan sekadar true: yang bisa dijawab saat ditanya
        // belakangan adalah KAPAN orangnya menyetujui.
        $this->assertNotNull(
            WebinarEksklusifPendaftaran::where('email', 'setuju@contoh.test')->value('disetujui_pada')
        );
    }

    /**
     * Tiap pendaftaran langsung memotong kuota dan baru dikembalikan setengah
     * jam kemudian saat kedaluwarsa. Tanpa penjaga ini, satu orang yang
     * menekan "Daftar" tiga kali memakan tiga kursi.
     */
    #[Test]
    public function email_yang_sama_tidak_memotong_kursi_dua_kali(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '10']);

        $kirim = fn () => $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Budi', 'email' => 'budi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'setuju' => '1',
        ]);

        $kirim()->assertRedirect();
        $kirim()->assertRedirect();
        $kirim()->assertRedirect();

        $this->assertSame(1, WebinarEksklusifPendaftaran::where('email', 'budi@contoh.test')->count(),
            'kiriman berikutnya diantar ke pendaftaran yang sudah ada, bukan membuat yang baru');

        $this->assertSame('8', (string) $sesi->fresh()->sisa_kuota,
            'terpotong sekali saja, bukan tiga kali');
    }

    #[Test]
    public function pendaftaran_yang_sudah_kedaluwarsa_boleh_mendaftar_lagi(): void
    {
        $sesi = $this->sesi();

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Lama', 'email' => 'lama@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Lama', 'email' => 'lama@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('email', 'lama@contoh.test')->count(),
            'yang lama sudah lewat batas waktunya, jadi tidak menghalangi');
    }

    // ------------------------------------------------------ pemberitahuan

    /**
     * Sampai 2 Okt 2026 fitur ini tidak mengirim apa pun — berbeda dengan
     * Scopus Kafe yang sudah punya. Orang hanya memegang tautan status di
     * layar, dan begitu tabnya ditutup, tautannya hilang.
     */
    #[Test]
    public function bukti_pendaftaran_dikirim_ke_email_pendaftar(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Citra', 'email' => 'Citra@Contoh.Test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\WebinarEksklusifPendaftaranMail::class,
            fn ($surat) => $surat->hasTo('citra@contoh.test')
        );
    }

    /**
     * Pendaftarannya sudah tersimpan dan kursinya sudah terpotong saat email
     * dikirim. Kalau peladen suratnya bermasalah, yang pantas terjadi adalah
     * galatnya dicatat — BUKAN orangnya melihat layar error padahal
     * pendaftarannya berhasil.
     */
    #[Test]
    public function peladen_surat_bermasalah_tidak_menggagalkan_pendaftaran(): void
    {
        \Illuminate\Support\Facades\Mail::shouldReceive('to')
            ->andThrow(new \RuntimeException('peladen surat mati'));

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Dewi', 'email' => 'dewi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $this->assertSame(1, WebinarEksklusifPendaftaran::where('email', 'dewi@contoh.test')->count(),
            'pendaftarannya tetap tersimpan');
    }

    /**
     * Borangnya publik, diiklankan, dan tiap kiriman yang berhasil langsung
     * memotong kuota — satu skrip bisa menghabiskan seluruh kursi.
     */
    #[Test]
    public function kiriman_borang_dibatasi_per_menit(): void
    {
        $sesi = $this->sesi(['total_kuota' => '200', 'sisa_kuota' => '200']);

        $jawaban = null;

        for ($i = 0; $i < 9; $i++) {
            $jawaban = $this->post(route('public.webinareksklusif.store'), [
                'kategori_id' => $sesi->id, 'nama' => 'Beruntun ' . $i,
                'email' => 'beruntun' . $i . '@contoh.test',
                'telp' => '08123456789', 'jumlah_pendaftar' => 1, 'setuju' => '1',
            ]);
        }

        $jawaban->assertStatus(429);
    }

    // -------------------------------------------- pelepasan kursi mandiri

    /**
     * Kursi yang ditinggalkan semula HANYA dilepas oleh perintah terjadwal.
     *
     * Itu satu titik kegagalan yang diam: kalau penjadwalnya tidak jalan,
     * kursinya tertahan selamanya — terukur di basis data lokal, 2
     * pendaftaran lewat batas menahan 50 kursi — sementara halaman status
     * sudah memberitahu orangnya "kursinya sudah dilepas kembali".
     */
    #[Test]
    public function membuka_halaman_borang_melepas_kursi_yang_ditinggalkan(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '4']);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Ditinggalkan', 'email' => 'tinggal@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'total_pembayaran' => '774000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->get(route('public.webinareksklusif.daftar', [$sesi->id, $sesi->token]))
            ->assertOk();

        $this->assertSame('10', (string) $sesi->fresh()->sisa_kuota,
            'kursinya kembali tanpa menunggu perintah terjadwal');

        $this->assertSame('expired',
            WebinarEksklusifPendaftaran::where('email', 'tinggal@contoh.test')->value('status'));
    }

    #[Test]
    public function kursi_yang_masih_dalam_batas_waktu_tidak_ikut_dilepas(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '4']);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Masih Bayar', 'email' => 'masih@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'total_pembayaran' => '774000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(20),
        ]);

        $this->get(route('public.webinareksklusif.daftar', [$sesi->id, $sesi->token]))->assertOk();

        $this->assertSame('4', (string) $sesi->fresh()->sisa_kuota,
            'yang masih dalam batas waktu kursinya TIDAK boleh dilepas');

        $this->assertSame('pending',
            WebinarEksklusifPendaftaran::where('email', 'masih@contoh.test')->value('status'));
    }

    /**
     * Dua penyapu bisa jalan bersamaan — perintah terjadwal dan orang yang
     * sedang membuka halaman. Tanpa penguncian, keduanya melihat 'pending'
     * dan sama-sama mengembalikan kursinya, jadi kursinya bertambah dua kali
     * dan angkatan membuka tempat yang sebenarnya tidak ada.
     */
    #[Test]
    public function menyapu_dua_kali_tidak_mengembalikan_kursi_dua_kali(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '4']);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Ganda', 'email' => 'ganda@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'total_pembayaran' => '774000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa($sesi->id);
        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa($sesi->id);
        $this->artisan('webinar-eksklusif:kedaluwarsakan');

        $this->assertSame('10', (string) $sesi->fresh()->sisa_kuota,
            'dilepas sekali saja, berapa kali pun disapu');
    }

    // ------------------------------------------------ pengingat & kirim ulang

    /**
     * Jarak antara mendaftar dan hari-H bisa berminggu-minggu, dan tanpa
     * pengingat yang membayar jauh hari lupa — kursinya terpakai tanpa ada
     * orangnya.
     */
    #[Test]
    public function pengingat_dikirim_ke_peserta_sesi_besok(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi(['mulai' => now()->addDay()->toDateString()]);

        $p = WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Eka', 'email' => 'eka@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        $this->artisan('webinar-eksklusif:ingatkan')->assertSuccessful();

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\WebinarEksklusifPengingatMail::class,
            fn ($surat) => $surat->hasTo('eka@contoh.test')
        );

        $this->assertNotNull($p->fresh()->pengingat_pada);
    }

    /**
     * Perintahnya jalan tiap hari dan bisa dipanggil ulang tangan. Tanpa
     * penanda, peserta yang sama menerima surat tiap kali ia jalan — dan yang
     * menerima lima pengingat untuk satu sesi berhenti membaca surat dari
     * kami sama sekali.
     */
    #[Test]
    public function pengingat_tidak_dikirim_dua_kali(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi(['mulai' => now()->addDay()->toDateString()]);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Dua', 'email' => 'dua@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        $this->artisan('webinar-eksklusif:ingatkan');
        $this->artisan('webinar-eksklusif:ingatkan');
        $this->artisan('webinar-eksklusif:ingatkan');

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\WebinarEksklusifPengingatMail::class, 1);
    }

    /**
     * Yang kursinya sudah dilepas tidak boleh diingatkan — mengirimi mereka
     * pengingat berarti menjanjikan tempat yang sudah tidak ada.
     */
    #[Test]
    public function yang_batal_dan_kedaluwarsa_tidak_diingatkan(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi(['mulai' => now()->addDay()->toDateString()]);

        foreach (['cancel', 'expired'] as $status) {
            WebinarEksklusifPendaftaran::create([
                'kategori_id' => $sesi->id, 'nama' => 'Tidak ' . $status,
                'email' => $status . '@contoh.test',
                'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
                'cara_bayar' => 'transfer', 'status' => $status,
            ]);
        }

        $this->artisan('webinar-eksklusif:ingatkan');

        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    #[Test]
    public function jalan_kering_pengingat_tidak_mengirim_apa_pun(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi(['mulai' => now()->addDay()->toDateString()]);

        $p = WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Kering', 'email' => 'kering@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        $this->artisan('webinar-eksklusif:ingatkan', ['--kering' => true]);

        \Illuminate\Support\Facades\Mail::assertNothingSent();
        $this->assertNull($p->fresh()->pengingat_pada, 'jalan kering tidak boleh menandai apa pun');
    }

    /**
     * Satu-satunya jalan kembali ke halaman status adalah tautan bertoken di
     * email. Kalau emailnya terhapus, orangnya kehilangan nomor pendaftaran
     * dan cara bayarnya sekaligus.
     */
    #[Test]
    public function bukti_pendaftaran_bisa_dikirim_ulang(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi();

        $p = WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Fani', 'email' => 'fani@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(20),
        ]);

        $this->post(route('public.webinareksklusif.kirimulang', $p->token))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\WebinarEksklusifPendaftaranMail::class,
            fn ($surat) => $surat->hasTo('fani@contoh.test')
        );
    }

    /**
     * Penerimanya diambil dari BASIS DATA, bukan dari yang dikirim peramban.
     * Kalau bisa ditentukan dari luar, siapa pun yang memegang tautan ini
     * bisa memakainya untuk mengirimi orang lain.
     */
    #[Test]
    public function kirim_ulang_tidak_bisa_diarahkan_ke_email_lain(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi();

        $p = WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Gita', 'email' => 'gita@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(20),
        ]);

        $this->post(route('public.webinareksklusif.kirimulang', $p->token), [
            'email' => 'penyerang@contoh.test',
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\WebinarEksklusifPendaftaranMail::class,
            fn ($surat) => $surat->hasTo('gita@contoh.test') && ! $surat->hasTo('penyerang@contoh.test')
        );
    }

    // ----------------------------------------------------- rupa surat keluar

    /**
     * Surat yang sampai ke peserta sempat tertulis pengirimnya "Laravel".
     *
     * Sebabnya Mailable-nya memakai config('app.name'), sedangkan APP_NAME di
     * .env memang masih bawaan. Yang benar MAIL_FROM_NAME, dan itu sudah
     * terisi sejak dulu. Surat dari "Laravel" terbaca seperti nyasar atau
     * penipuan — di situlah orang berhenti membacanya, dan tidak ada galat
     * apa pun yang menandainya.
     */
    #[Test]
    public function pengirim_suratnya_bukan_nama_bawaan_laravel(): void
    {
        config(['app.name' => 'Laravel']);

        $sesi = $this->sesi();
        $p = $this->pendaftaranUji($sesi);

        foreach ([
            new \App\Mail\WebinarEksklusifPendaftaranMail($p, $sesi),
            new \App\Mail\WebinarEksklusifPengingatMail($p, $sesi),
        ] as $surat) {
            $pesan = $surat->build()->toMail ?? null;

            $dibangun = $surat->build();
            $dari = collect($dibangun->from)->first();

            $this->assertSame(config('mail.from.name'), $dari['name']);
            $this->assertNotSame('Laravel', $dari['name']);
        }
    }

    /**
     * Logonya DISISIPKAN sebagai lampiran, bukan ditautkan ke peladen: Gmail
     * dan Outlook memblokir gambar jauh secara bawaan, jadi logo bertautan
     * muncul sebagai kotak kosong sampai orangnya menekan "tampilkan gambar".
     */
    #[Test]
    public function logo_rumah_scopus_disisipkan_di_surat(): void
    {
        $sesi = $this->sesi();
        $p = $this->pendaftaranUji($sesi);

        $html = (new \App\Mail\WebinarEksklusifPendaftaranMail($p, $sesi))->render();

        $this->assertStringContainsString('alt="Rumah Scopus Foundation"', $html);

        // Berkasnya memang ada; tanpa ini ujinya tetap hijau walau logonya
        // terhapus dan yang tersisa lampiran kosong.
        $this->assertFileExists(public_path('assets/img/logo-rsc-email.png'));
    }

    /**
     * Judul sesinya 68 huruf. "Pendaftaran <judul> tersimpan" terpotong di
     * tengah jalan pada daftar surat, dan yang terbaca tinggal judul sesi
     * tanpa petunjuk bahwa itu bukti pendaftaran.
     */
    #[Test]
    public function subjek_suratnya_pendek_dan_memuat_nomor_pendaftaran(): void
    {
        $sesi = $this->sesi(['nama' => str_repeat('Judul Sesi Yang Sangat Panjang ', 3)]);
        $p = $this->pendaftaranUji($sesi);

        $subjek = (new \App\Mail\WebinarEksklusifPendaftaranMail($p, $sesi))->build()->subject;

        $this->assertStringContainsString($p->id_transaksi, $subjek);
        $this->assertLessThan(60, mb_strlen($subjek),
            'subjek panjang terpotong di daftar surat sebelum sampai ke bagian yang penting');
    }

    private function pendaftaranUji(KategoriLayanan $sesi): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Peserta Uji', 'email' => 'surat@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(30),
        ]);
    }
}
