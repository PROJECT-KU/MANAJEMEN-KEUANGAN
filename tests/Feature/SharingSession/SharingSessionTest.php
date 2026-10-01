<?php

namespace Tests\Feature\SharingSession;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\SharingSessionPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layanan Sharing Session: API untuk halaman landing, borang pendaftaran,
 * penjaga kuota, dan pemberitahuan balik dari gerbang pembayaran.
 */
class SharingSessionTest extends TestCase
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
        KategoriLayanan::where('layanan', 'sharing_session')
            ->update(['status' => 'non active']);
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'sharing_session',
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
        $this->assertArrayHasKey('sharing_session', Layanan::katalog());

        $tarif = ClinikScopusBiayaPersesi::berlaku('sharing_session', null);

        $this->assertNotNull($tarif, 'Tarif Sharing Session harus ada.');
        $this->assertSame(129000, (int) $tarif->biaya_persesi);
    }

    #[Test]
    public function tabel_pendaftarannya_terdaftar_di_model(): void
    {
        /*
         * Tanpa ini, peserta Sharing Session tidak terhitung di layar Angkatan
         * dan angkatannya bisa dihapus walau sudah ada yang mendaftar —
         * persis celah yang sudah ditemukan pada Scopus Cafe dan Clinik
         * Scopus sebelumnya.
         */
        $this->assertSame(
            'sharing_session_pendaftaran',
            KategoriLayanan::TABEL_PENDAFTARAN['sharing_session'] ?? null
        );

        $this->assertFalse($this->sesi()->belumPunyaPendaftaran());
    }

    // ----------------------------------------------------------------- API

    #[Test]
    public function api_mengirim_sesi_yang_sedang_dibuka(): void
    {
        $sesi = $this->sesi(['nama' => 'Sesi API Uji']);

        $jawab = $this->getJson('/api/sharing-session');

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

        $this->getJson('/api/sharing-session')->assertOk()->assertJsonPath('ada', false);
    }

    #[Test]
    public function api_menjawab_200_walau_belum_ada_sesi(): void
    {
        // 200, bukan 404: "belum ada sesi" jawaban yang sah, dan 404 membuat
        // halaman landing menampilkan galat merah padahal tidak ada yang rusak.
        $this->tutupSemuaSesi();

        $this->getJson('/api/sharing-session')
            ->assertOk()
            ->assertJsonPath('ada', false)
            ->assertJsonPath('sesi', null);
    }

    #[Test]
    public function api_memecah_kontak_jadi_tautan_whatsapp(): void
    {
        $this->sesi();

        $kontak = $this->getJson('/api/sharing-session')->json('sesi.kontak');

        $this->assertNotEmpty($kontak);
        // 0889... jadi 6289... supaya tautannya bisa dipakai langsung.
        $this->assertStringStartsWith('62', $kontak[0]['wa']);
    }

    #[Test]
    public function api_tidak_pernah_menulis_apa_pun(): void
    {
        // Endpoint ini tanpa autentikasi dan terbuka ke internet; satu jalur
        // tulis di sana berarti siapa pun bisa mengubah data.
        $this->postJson('/api/sharing-session')->assertStatus(405);
        $this->putJson('/api/sharing-session')->assertStatus(405);
        $this->deleteJson('/api/sharing-session')->assertStatus(405);
    }

    // ---------------------------------------------------------- pendaftaran

    #[Test]
    public function borang_pendaftaran_terbuka_dengan_token_yang_benar(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.sharingsession.daftar', [$sesi->id, $sesi->token]))
            ->assertOk()
            ->assertSee($sesi->nama)
            ->assertSee('Rp 129.000');
    }

    #[Test]
    public function borang_menolak_token_yang_salah(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.sharingsession.daftar', [$sesi->id, 'token-karangan']))
            ->assertRedirect(route('public.sharingsession.index'));
    }

    #[Test]
    public function mendaftar_menyimpan_dan_memotong_kuota(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Budi Santoso',
            'email' => 'Budi@Contoh.Test',
            'telp' => '0812-3456-7890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 3,
        ])->assertRedirect();

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ani', 'email' => 'ani@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->assertMatchesRegularExpression('/^SS-\d{8}-\d{4}$/', $p->id_transaksi);
    }

    #[Test]
    public function tidak_bisa_mendaftar_melebihi_sisa_kuota(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '2']);

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Cici', 'email' => 'cici@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 5,
        ])->assertSessionHas('error');

        $this->assertSame(0, SharingSessionPendaftaran::where('kategori_id', $sesi->id)->count());
        $this->assertSame(2, (int) $sesi->fresh()->sisa_kuota, 'Kuotanya tidak boleh ikut terpotong.');
    }

    #[Test]
    public function tidak_bisa_mendaftar_ke_sesi_yang_sudah_ditutup(): void
    {
        $sesi = $this->sesi(['status' => 'non active']);

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Dedi', 'email' => 'dedi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ])->assertRedirect(route('public.sharingsession.index'));

        $this->assertSame(0, SharingSessionPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function halaman_status_memperlihatkan_cara_bayar(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Eka', 'email' => 'eka@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->get(route('public.sharingsession.status', $p->token))
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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Fani', 'email' => 'fani@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->postJson(route('public.sharingsession.pemberitahuan'), [
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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Gina', 'email' => 'gina@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 2,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Hadi', 'email' => 'hadi@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 1,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ika', 'email' => 'ika@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 4,
        ]);

        $this->assertSame(16, (int) $sesi->fresh()->sisa_kuota);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();

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

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Joko', 'email' => 'joko@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 3,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();
        $p->forceFill(['kedaluwarsa_pada' => now()->subHour()])->save();

        $this->artisan('sharing-session:kedaluwarsakan')->assertSuccessful();

        $this->assertSame('expired', $p->fresh()->status);
        $this->assertSame(20, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function jalan_kering_tidak_melepas_apa_pun(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.sharingsession.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Kiki', 'email' => 'kiki@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 2,
        ]);

        $p = SharingSessionPendaftaran::where('kategori_id', $sesi->id)->first();
        $p->forceFill(['kedaluwarsa_pada' => now()->subHour()])->save();

        $this->artisan('sharing-session:kedaluwarsakan', ['--kering' => true])->assertSuccessful();

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
        $jalur = '/Sharing-Session/pemberitahuan/doku';

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
}
