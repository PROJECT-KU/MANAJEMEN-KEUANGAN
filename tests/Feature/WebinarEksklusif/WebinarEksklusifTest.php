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

    /*
     * Memotong isi satu lajur dari markah. Dicari dari pembuka berkelasnya
     * sampai pembuka lajur berikutnya (atau penutup kisinya) — bukan dengan
     * mencocokkan penutup div, yang di dalamnya ada puluhan dan pencocokan
     * sederhana akan berhenti di yang pertama.
     */
    private function isiLajur(string $isi, string $kelas): string
    {
        $awal = strpos($isi, '<div class="sta-lajur ' . $kelas . '">');

        if ($awal === false) {
            return '';
        }

        $berikut = strpos($isi, '<div class="sta-lajur ', $awal + 10);
        $tutup = strpos($isi, '/kisi', $awal);
        $akhir = $berikut !== false ? $berikut : ($tutup !== false ? $tutup : strlen($isi));

        return substr($isi, $awal, $akhir - $awal);
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
        $this->assertStringContainsString($sesi->id, $jawab->json('sesi.daftar_url'));
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
    public function borang_pendaftaran_terbuka_dari_uuid_angkatannya(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->assertSee($sesi->nama)
            ->assertSee('Rp 129.000');
    }

    /**
     * Tokennya dibuang 3 Okt 2026, jadi yang menutup pintu tinggal satu:
     * UUID yang tidak dikenali. Kalau pencariannya dilonggarkan — misalnya
     * jatuh ke sesi pertama saat id-nya tidak ketemu — pendaftar bisa
     * mendarat di angkatan yang salah tanpa gejala apa pun.
     */
    #[Test]
    public function borang_menolak_uuid_yang_tidak_dikenal(): void
    {
        $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', (string) \Illuminate\Support\Str::uuid()))
            ->assertRedirect(route('public.webinareksklusif.index'));
    }

    /**
     * Alamatnya TIDAK boleh memuat token lagi. Tanpa uji ini, token yang
     * dipasang kembali di suatu tempat tidak akan menampakkan diri — kedua
     * bentuk alamat sama-sama terbuka selama rutenya masih menerimanya.
     */
    #[Test]
    public function alamat_borang_tidak_memuat_token(): void
    {
        $sesi = $this->sesi();

        $alamat = route('public.webinareksklusif.daftar', $sesi->id);

        $this->assertStringContainsString($sesi->id, $alamat);
        $this->assertStringNotContainsString($sesi->token, $alamat);
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
            'jumlah_pendaftar' => 3, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3']], 'setuju' => '1',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->first();

        $this->assertNotNull($p);
        $this->assertSame('budi@contoh.test', $p->email, 'Emailnya dikecilkan.');
        $this->assertSame('6281234567890', $p->telp, 'Nomornya dirapikan jadi 62.');
        $this->assertSame(3, $p->jumlah_pendaftar);
        /*
         * Totalnya kini DASAR + KODE UNIK. Kode uniknya sengaja tidak
         * ditebak nilainya — yang dijaga hubungannya: totalnya persis
         * dasar ditambah kode, dan kodenya di dalam rentang yang dijanjikan.
         */
        $kode = (int) $p->kode_unik;

        $this->assertGreaterThanOrEqual(
            \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MIN, $kode);
        $this->assertLessThanOrEqual(
            \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MAKS, $kode);

        $this->assertSame(387000 + $kode, (int) $p->total_pembayaran, '129.000 x 3 + kode unik');
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 5, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3'], ['nama' => 'Peserta 4'], ['nama' => 'Peserta 5']], 'setuju' => '1',
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

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee($p->id_transaksi)
            // Nominal yang ditagihkan sudah termasuk kode unik.
            ->assertSee(number_format((int) $p->fresh()->total_pembayaran, 0, ',', '.'));
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'peserta' => [['nama' => 'Peserta 2']], 'setuju' => '1',
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 4, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3'], ['nama' => 'Peserta 4']], 'setuju' => '1',
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 3, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3']], 'setuju' => '1',
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'peserta' => [['nama' => 'Peserta 2']], 'setuju' => '1',
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
            'telp' => '08123456789', 'jumlah_pendaftar' => 2, 'peserta' => [['nama' => 'Peserta 2']], 'setuju' => '1',
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
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3'], ['nama' => 'Peserta 4'], ['nama' => 'Peserta 5'], ['nama' => 'Peserta 6']], 'total_pembayaran' => '774000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
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
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3'], ['nama' => 'Peserta 4'], ['nama' => 'Peserta 5'], ['nama' => 'Peserta 6']], 'total_pembayaran' => '774000',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addMinutes(20),
        ]);

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))->assertOk();

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
            'telp' => '628123456789', 'jumlah_pendaftar' => 6, 'peserta' => [['nama' => 'Peserta 2'], ['nama' => 'Peserta 3'], ['nama' => 'Peserta 4'], ['nama' => 'Peserta 5'], ['nama' => 'Peserta 6']], 'total_pembayaran' => '774000',
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

        $this->post(route('public.webinareksklusif.kirimulang', $p->getKey()))
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

        $this->post(route('public.webinareksklusif.kirimulang', $p->getKey()), [
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

    // ------------------------------------------------------ isi otomatis

    /**
     * Nomor yang pernah dipakai mendaftar mengisi borangnya sendiri.
     *
     * Dicocokkan ke SEMUA bentuk simpanan, bukan satu: kolom telp diisi
     * bertahun-tahun oleh layar yang berbeda, jadi satu orang bisa tersimpan
     * "6281...", "0812...", atau "+62 812-...".
     */
    #[Test]
    public function nomor_yang_pernah_mendaftar_mengisi_borang_otomatis(): void
    {
        $sesi = $this->sesi();

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Hesti Lama', 'email' => 'hesti@contoh.test',
            'telp' => '6281234509876', 'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        foreach (['6281234509876', '081234509876', '+62 812-3450-9876'] as $bentuk) {
            $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $bentuk])
                ->assertOk()
                ->assertJson([
                    'ditemukan' => true,
                    'nama' => 'Hesti Lama',
                    'email' => 'hesti@contoh.test',
                    'affiliasi' => 'Universitas Uji',
                ]);
        }
    }

    /**
     * Arah SEBALIKNYA, dan ini yang sebenarnya terjadi di basis data: kolom
     * users.telp menyimpan "0895421735441" berawalan nol, sedangkan yang
     * diketik orang di borang biasanya diawali 62 atau +62.
     *
     * Tanpa pencocokan ke semua bentuk, orang-orang ini TIDAK PERNAH ketemu
     * — dan diamnya pencarian terbaca seperti mereka memang belum pernah
     * mendaftar, bukan seperti pencarian yang meleset.
     */
    #[Test]
    public function nomor_tersimpan_berawalan_nol_tetap_ketemu_dari_bentuk_62(): void
    {
        $sesi = $this->sesi();

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Gita Nol', 'email' => 'gita.nol@contoh.test',
            // Disimpan berawalan NOL, seperti sebagian baris nyata.
            'telp' => '081299887766',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        foreach (['6281299887766', '+62 812-9988-7766', '081299887766'] as $bentuk) {
            $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $bentuk])
                ->assertOk()
                ->assertJson(['ditemukan' => true, 'nama' => 'Gita Nol']);
        }
    }

    /**
     * Satu nomor yang dipakai BEBERAPA akun tidak boleh dijawab.
     *
     * Di basis data ini ada nomor yang dipakai lima akun sekaligus. Menjawab
     * salah satunya berarti menyerahkan identitas orang yang salah kepada
     * siapa pun yang mengetik nomor itu.
     */
    #[Test]
    public function nomor_yang_dipakai_beberapa_akun_tidak_dijawab(): void
    {
        $nomor = '6288811112222';

        foreach (['satu', 'dua'] as $i => $nama) {
            \App\User::create([
                'full_name' => 'Akun ' . $nama,
                'username' => 'uji-' . $nama . '-' . \Illuminate\Support\Str::random(5),
                'email' => 'uji-' . $nama . '-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
                'password' => bcrypt('rahasia-uji'),
                'telp' => $nomor,
            ]);
        }

        $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $nomor])
            ->assertOk()
            ->assertJson(['ditemukan' => false]);
    }

    /**
     * Punya akun tetapi BELUM masuk — jalur yang paling sering terjadi:
     * orang membuka tautan iklan langsung dari WhatsApp, tanpa pernah masuk
     * ke akunnya.
     *
     * Jalur ini sempat tidak terjaga uji apa pun walau justru yang paling
     * ditanyakan.
     */
    #[Test]
    public function punya_akun_tetapi_belum_masuk_borangnya_tetap_terisi(): void
    {
        \App\User::create([
            'full_name' => 'Rina Punya Akun',
            'username' => 'rina-' . \Illuminate\Support\Str::random(6),
            'email' => 'rina-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'telp' => '628177766551',
            'company' => 'Universitas Rina',
        ]);

        foreach (['628177766551', '08177766551', '+62 817-7766-551'] as $bentuk) {
            $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $bentuk])
                ->assertOk()
                ->assertJson([
                    'ditemukan' => true,
                    'nama' => 'Rina Punya Akun',
                    'affiliasi' => 'Universitas Rina',
                ]);
        }
    }

    /**
     * Pendaftaran sebelumnya DIDAHULUKAN daripada akun: isinya sebentuk
     * dengan borang ini (nama beserta gelar, asal instansi), sedangkan nama
     * di akun sering sekadar "admin" atau "staff".
     */
    #[Test]
    public function pendaftaran_sebelumnya_didahulukan_daripada_akun(): void
    {
        $sesi = $this->sesi();
        $nomor = '628166655544';

        \App\User::create([
            'full_name' => 'staff',
            'username' => 'staff-' . \Illuminate\Support\Str::random(6),
            'email' => 'staff-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'telp' => $nomor,
        ]);

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Dr. Sari Lengkap, M.Si',
            'email' => 'sari@contoh.test', 'telp' => $nomor, 'affiliasi' => 'Kampus Sari',
            'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $nomor])
            ->assertOk()
            ->assertJson(['ditemukan' => true, 'nama' => 'Dr. Sari Lengkap, M.Si']);
    }

    #[Test]
    public function nomor_yang_belum_pernah_dipakai_tidak_ditemukan(): void
    {
        $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => '6289900001111'])
            ->assertOk()
            ->assertJson(['ditemukan' => false]);
    }

    /**
     * Potongan nomor TIDAK dicari. Kalau awalan saja dijawab, nomor bisa
     * disisir dari depan dan jalur ini berubah jadi alat panen data.
     */
    #[Test]
    public function potongan_nomor_tidak_dicari(): void
    {
        $sesi = $this->sesi();

        WebinarEksklusifPendaftaran::create([
            'kategori_id' => $sesi->id, 'nama' => 'Indra', 'email' => 'indra@contoh.test',
            'telp' => '6281234509876', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '129000',
            'cara_bayar' => 'transfer', 'status' => 'paid',
        ]);

        foreach (['0812', '62812', 'abc', '08'] as $potongan) {
            $this->postJson(route('public.webinareksklusif.caripendaftar'), ['telp' => $potongan])
                ->assertOk()
                ->assertJson(['ditemukan' => false]);
        }
    }

    /**
     * Pencariannya dibatasi per menit. Tanpa itu, satu skrip bisa menyisir
     * seluruh rentang nomor dan memanen nama beserta emailnya.
     */
    #[Test]
    public function pencarian_dibatasi_per_menit(): void
    {
        $jawaban = null;

        for ($i = 0; $i < 13; $i++) {
            $jawaban = $this->postJson(route('public.webinareksklusif.caripendaftar'),
                ['telp' => '62888' . str_pad((string) $i, 8, '0', STR_PAD_LEFT)]);
        }

        $jawaban->assertStatus(429);
    }

    /**
     * Yang sudah masuk akun tidak perlu mengetik nomornya lebih dulu —
     * borangnya sudah terisi saat halaman terbuka, dan datanya miliknya
     * sendiri jadi tidak ada yang dibuka ke siapa pun.
     */
    #[Test]
    public function pengunjung_yang_sudah_masuk_akun_borangnya_sudah_terisi(): void
    {
        $sesi = $this->sesi();

        $akun = \App\User::create([
            'full_name' => 'Joko Pengguna',
            'username' => 'joko-' . \Illuminate\Support\Str::random(5),
            'email' => 'joko-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'telp' => '628129998887',
            'company' => 'Kampus Joko',
        ]);

        $this->actingAs($akun)
            ->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->assertSee('Joko Pengguna', false)
            ->assertSee($akun->email, false)
            ->assertSee('Kampus Joko', false);
    }

    /**
     * Nomor WhatsApp HARUS jadi isian pertama.
     *
     * Semula ia isian ketiga, sesudah nama dan email. Akibatnya orang yang
     * pernah mendaftar sudah terlanjur mengetik keduanya sebelum pencarian
     * sempat jalan — pengisian otomatisnya menghemat satu isian, bukan tiga,
     * dan tujuan "sesedikit mungkin mengetik" tidak tercapai.
     *
     * Kalau urutannya dikembalikan, tidak ada yang rusak dan tidak ada galat
     * apa pun: fiturnya hanya diam-diam berhenti berguna. Itu sebabnya
     * dijaga uji.
     */
    #[Test]
    public function nomor_whatsapp_adalah_isian_pertama(): void
    {
        $sesi = $this->sesi();

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->getContent();

        $urutan = [];

        foreach (['ses-telp', 'ses-nama', 'ses-email'] as $id) {
            $pos = strpos($isi, 'id="' . $id . '"');
            $this->assertNotFalse($pos, "isian $id tidak ada di borang");
            $urutan[$id] = $pos;
        }

        $this->assertLessThan($urutan['ses-nama'], $urutan['ses-telp'],
            'nomor WhatsApp harus muncul sebelum nama — kalau tidak, borangnya '
            . 'sudah terlanjur diketik sebelum pengisian otomatis sempat jalan');

        $this->assertLessThan($urutan['ses-email'], $urutan['ses-telp'],
            'nomor WhatsApp harus muncul sebelum email');
    }

    /**
     * autofocus TIDAK boleh dipasang di isian nomor.
     *
     * Terukur saat sempat dipasang: halaman terbuka sudah tergulir di
     * 1340 px, jadi pengunjung mendarat langsung di borang dan melewati judul
     * sesi, tanggal, pemateri, dan flyer — dan di ponsel papan ketiknya
     * langsung menutup separuh layar.
     */
    #[Test]
    public function isian_nomor_tidak_memakai_autofocus(): void
    {
        $sesi = $this->sesi();

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))->getContent();

        $potongan = substr($isi, (int) strpos($isi, 'id="ses-telp"'), 400);

        $this->assertStringNotContainsString('autofocus', $potongan);
    }

    // ------------------------------------------------- peserta rombongan

    /**
     * Borangnya dulu menerima sampai 50 peserta tetapi hanya meminta SATU
     * nama. Di basis data sudah ada pendaftaran berisi 13 dan 37 orang
     * dengan satu nama masing-masing — padahal yang dijanjikan "E-sertifikat
     * resmi atas nama peserta".
     */
    #[Test]
    public function nama_tiap_peserta_rombongan_ikut_tersimpan(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua Rombongan',
            'email' => 'ketua@contoh.test', 'telp' => '08123456789',
            'jumlah_pendaftar' => 3, 'setuju' => '1',
            'peserta' => [
                ['nama' => 'Peserta Dua', 'email' => 'Dua@Contoh.Test'],
                ['nama' => 'Peserta Tiga'],
            ],
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('email', 'ketua@contoh.test')->first();

        $this->assertNotNull($p);
        $this->assertSame(2, $p->pesertaLain()->count(),
            'peserta pertama tidak disalin — ia di kolom nama pendaftarannya');

        $this->assertSame(['Peserta Dua', 'Peserta Tiga'],
            $p->pesertaLain()->pluck('nama')->all(),
            'urutannya harus sama dengan yang diketik, bukan urutan stempel waktu');

        $this->assertSame('dua@contoh.test', $p->pesertaLain()->whereNotNull('email')->value('email'),
            'email peserta ikut dikecilkan hurufnya seperti email pendaftar utama');

        // semuaPeserta() menaruh pendaftar utama lebih dulu.
        $semua = $p->semuaPeserta();
        $this->assertCount(3, $semua);
        $this->assertSame('Ketua Rombongan', $semua[0]['nama']);
        $this->assertTrue($semua[0]['utama']);
    }

    /**
     * Diperiksa DI PELADEN, bukan hanya di peramban. Borang yang dikirim
     * tanpa JavaScript akan lolos begitu saja, dan yang tersimpan
     * pendaftaran 10 orang dengan satu nama.
     */
    #[Test]
    public function rombongan_tanpa_nama_peserta_ditolak(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Sendirian',
            'email' => 'sendiri@contoh.test', 'telp' => '08123456789',
            'jumlah_pendaftar' => 4, 'setuju' => '1',
        ])->assertSessionHasErrors('peserta');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
        $this->assertSame('20', (string) $sesi->fresh()->sisa_kuota, 'kuotanya tidak boleh terpotong');
    }

    #[Test]
    public function jumlah_nama_yang_tidak_cocok_ditolak(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        // 4 peserta, tetapi hanya 1 nama tambahan yang dikirim.
        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua', 'email' => 'k@contoh.test',
            'telp' => '08123456789', 'jumlah_pendaftar' => 4, 'setuju' => '1',
            'peserta' => [['nama' => 'Satu Saja']],
        ])->assertSessionHasErrors('peserta');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    // ------------------------------------------------------ penghubung akun

    #[Test]
    public function pendaftaran_tertaut_ke_akun_saat_pendaftarnya_sedang_masuk(): void
    {
        $sesi = $this->sesi();

        $akun = \App\User::create([
            'full_name' => 'Pendaftar Masuk',
            'username' => 'masuk-' . \Illuminate\Support\Str::random(6),
            'email' => 'masuk-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'telp' => '628123450000',
        ]);

        $this->actingAs($akun)->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Pendaftar Masuk',
            'email' => 'masuk@contoh.test', 'telp' => '08123450000',
            'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $this->assertSame($akun->id,
            WebinarEksklusifPendaftaran::where('email', 'masuk@contoh.test')->value('user_id'));
    }

    #[Test]
    public function pendaftaran_tamu_tidak_tertaut_akun_siapa_pun(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Tamu', 'email' => 'tamu@contoh.test',
            'telp' => '08123450001', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $this->assertNull(
            WebinarEksklusifPendaftaran::where('email', 'tamu@contoh.test')->value('user_id'));
    }

    // -------------------------------------------------------- kode diskon

    /**
     * Potongannya dihitung ULANG dari angkatannya, bukan dipercaya dari
     * borang: nominal yang dikirim peramban bisa disunting siapa saja.
     */
    #[Test]
    public function kode_diskon_yang_benar_memotong_total(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Hemat', 'email' => 'hemat@contoh.test',
            'telp' => '08123450002', 'jumlah_pendaftar' => 1, 'setuju' => '1',
            'kode_diskon' => 'HEMAT30',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('email', 'hemat@contoh.test')->first();

        $this->assertSame(99000 + (int) $p->kode_unik, (int) $p->total_pembayaran,
            '129.000 - 30.000 + kode unik');
        $this->assertSame('HEMAT30', $p->kode_diskon);
        $this->assertSame('30000', (string) $p->nominal_diskon);
    }

    #[Test]
    public function kode_diskon_yang_salah_tidak_memotong_apa_pun(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        foreach (['SALAH', 'hemat30', ''] as $i => $kode) {
            $this->post(route('public.webinareksklusif.store'), [
                'kategori_id' => $sesi->id, 'nama' => 'Coba', 'email' => 'coba' . $i . '@contoh.test',
                'telp' => '0812345' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'jumlah_pendaftar' => 1, 'setuju' => '1', 'kode_diskon' => $kode,
            ]);

            $baris = WebinarEksklusifPendaftaran::where('email', 'coba' . $i . '@contoh.test')->first();

            $this->assertSame(129000 + (int) $baris->kode_unik, (int) $baris->total_pembayaran,
                'kode "' . $kode . '" tidak boleh memotong');
        }
    }

    /**
     * Potongan tidak boleh melebihi tagihannya: total negatif ditolak
     * gerbang pembayaran dengan galat yang tidak menyebut sebabnya.
     */
    #[Test]
    public function potongan_tidak_pernah_melebihi_total(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'BORONG', 'nominal_diskon' => '999999999']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Borong', 'email' => 'borong@contoh.test',
            'telp' => '08123450003', 'jumlah_pendaftar' => 1, 'setuju' => '1',
            'kode_diskon' => 'BORONG',
        ]);

        $baris = WebinarEksklusifPendaftaran::where('email', 'borong@contoh.test')->first();

        // Potongannya menghabiskan tagihan; yang tersisa tinggal kode uniknya.
        $this->assertSame((int) $baris->kode_unik, (int) $baris->total_pembayaran);
    }

    // ----------------------------------------------------- rupa saat penuh

    /**
     * Saat kuota habis, isiannya DINONAKTIFKAN — bukan hanya tombolnya yang
     * dihilangkan.
     *
     * Sebelumnya orang masih bisa mengetik nama, email, nomor, dan
     * mencentang persetujuan, lalu baru sadar tidak ada tombol sama sekali.
     * Usaha yang terbuang, dan tidak ada apa pun yang memberitahunya lebih
     * awal.
     */
    #[Test]
    public function saat_kuota_habis_isiannya_dinonaktifkan(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '0']);

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<fieldset[^>]*\bdisabled\b/', $isi,
            'isian harus dinonaktifkan serentak lewat fieldset');

        $this->assertStringNotContainsString('id="ses-kirim"', $isi,
            'tombol daftar tidak boleh ada saat kuotanya habis');
    }

    /**
     * Pesan "kuota penuh" harus punya JALAN KELUAR. Sebelumnya tertulis
     * "hubungi panitia" tanpa tautan apa pun — jalan buntu, padahal nomor
     * panitianya sudah ada di config.
     */
    #[Test]
    public function saat_kuota_habis_ada_jalan_menghubungi_panitia(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '0']);

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertSee('wa.me/' . config('panitia.whatsapp'), false)
            ->assertSee('Kabari saya kalau ada sesi berikutnya', false);
    }

    #[Test]
    public function saat_kuota_masih_ada_isiannya_tidak_dinonaktifkan(): void
    {
        $sesi = $this->sesi(['total_kuota' => '10', 'sisa_kuota' => '5']);

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))->getContent();

        $this->assertDoesNotMatchRegularExpression('/<fieldset[^>]*\bdisabled\b/', $isi);
        $this->assertStringContainsString('id="ses-kirim"', $isi);
    }

    /**
     * Isian kode diskon hanya muncul kalau angkatannya memang punya kode.
     * Kotak kode yang selalu ada membuat orang mengira ia kehilangan
     * sesuatu, lalu mencari-cari kode yang tidak pernah diterbitkan.
     */
    #[Test]
    public function isian_kode_diskon_hanya_muncul_kalau_angkatannya_punya(): void
    {
        $punya = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);
        $tanpa = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', $punya->id))
            ->assertSee('id="ses-kode"', false);

        $this->get(route('public.webinareksklusif.daftar', $tanpa->id))
            ->assertDontSee('id="ses-kode"', false);
    }

    /**
     * Daftar peserta ditampilkan di halaman status supaya pendaftar bisa
     * memeriksa ejaan namanya SEBELUM sertifikat diterbitkan. Salah eja yang
     * baru ketahuan saat sertifikatnya jadi adalah pekerjaan ulang.
     */
    #[Test]
    public function halaman_status_menampilkan_semua_nama_peserta(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua Tim', 'email' => 'ketua.st@contoh.test',
            'telp' => '08123459999', 'jumlah_pendaftar' => 3, 'setuju' => '1',
            'peserta' => [['nama' => 'Anggota Dua'], ['nama' => 'Anggota Tiga']],
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'ketua.st@contoh.test')->first();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Ketua Tim', false)
            ->assertSee('Anggota Dua', false)
            ->assertSee('Anggota Tiga', false);
    }

    /**
     * Akun TIDAK dibuat otomatis saat mendaftar — ditawarkan, bukan
     * dibuatkan. Akun hasil buatan sistem tidak punya sandi yang dipilih
     * orangnya, dan email salah ketik menciptakan akun yang tidak bisa
     * dibuka siapa pun sekaligus menghalangi pendaftaran akun sungguhannya
     * nanti, sebab emailnya unik.
     */
    #[Test]
    public function mendaftar_tidak_membuat_akun_diam_diam(): void
    {
        $sesi = $this->sesi();
        $sebelum = \App\User::count();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Tanpa Akun',
            'email' => 'tanpa.akun@contoh.test', 'telp' => '08123458888',
            'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $this->assertSame($sebelum, \App\User::count(), 'tidak boleh ada akun yang terbentuk');
        $this->assertSame(0, \App\User::where('email', 'tanpa.akun@contoh.test')->count());
    }

    #[Test]
    public function halaman_status_menawarkan_buat_akun_kepada_tamu(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Tamu Tawar', 'email' => 'tawar@contoh.test',
            'telp' => '08123458887', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'tawar@contoh.test')->first();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Buat akun pakai email ini', false)
            // Emailnya ikut dibawa supaya tidak perlu diketik ulang.
            ->assertSee('tawar%40contoh.test', false);
    }

    #[Test]
    public function yang_sudah_masuk_akun_tidak_ditawari_lagi(): void
    {
        $sesi = $this->sesi();

        $akun = \App\User::create([
            'full_name' => 'Sudah Punya',
            'username' => 'punya-' . \Illuminate\Support\Str::random(6),
            'email' => 'punya-' . \Illuminate\Support\Str::random(5) . '@contoh.test',
            'password' => bcrypt('rahasia-uji'),
            'telp' => '628123458886',
        ]);

        $this->actingAs($akun)->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Sudah Punya', 'email' => 'sudah@contoh.test',
            'telp' => '08123458886', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'sudah@contoh.test')->first();

        $this->actingAs($akun)
            ->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertDontSee('Buat akun pakai email ini', false);
    }

    // ------------------------------------------------------- batas waktu

    /**
     * Transfer manual diberi 24 jam, bukan 60 menit milik gerbang.
     *
     * Enam puluh menit masuk akal untuk virtual account — ia kedaluwarsa
     * sendiri di sisi gerbang. Untuk transfer manual tidak: orangnya harus
     * membuka m-banking atau ke ATM, mengirim bukti ke WhatsApp, lalu
     * MENUNGGU panitia mengonfirmasi dengan tangan. Dengan 60 menit, tiap
     * pendaftaran transfer pasti kedaluwarsa walau uangnya sudah dikirim.
     */
    #[Test]
    public function transfer_manual_diberi_waktu_satu_kali_24_jam(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Transfer', 'email' => 'transfer@contoh.test',
            'telp' => '08123457777', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'transfer@contoh.test')->first();

        $this->assertSame('transfer', $p->cara_bayar, 'prasyarat: DOKU belum disetel');

        /*
         * Dipatok TEPAT 1x24 jam, bukan sekadar "lebih panjang dari gerbang".
         *
         * Bentuk longgarnya (> 12 jam) lolos juga untuk 13 jam maupun 72 jam,
         * padahal angkanya dijanjikan kepada peserta di halaman status dan
         * dipakai hitung mundur yang berjalan di layarnya. Yang dijanjikan
         * harus yang dijaga.
         *
         * Dibandingkan dalam MENIT dengan kelonggaran satu menit: beberapa
         * detik berlalu antara permintaannya diproses dan baris ini dijalankan.
         */
        $menit = now()->diffInMinutes($p->kedaluwarsa_pada, false);

        $this->assertEqualsWithDelta(24 * 60, $menit, 1,
            'Batas transfer manual harus 1x24 jam; itu yang tertulis di halaman status.');
    }

    // --------------------------------------------- batas rombongan & email

    #[Test]
    public function rombongan_lebih_dari_batas_ditolak(): void
    {
        $sesi = $this->sesi(['total_kuota' => '100', 'sisa_kuota' => '100']);

        $batas = \App\Http\Controllers\Publict\PublicWebinarEksklusifController::MAKS_ROMBONGAN;

        $orang = [];
        for ($i = 0; $i < $batas; $i++) {
            $orang[] = ['nama' => 'Peserta ' . ($i + 2)];
        }

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Terlalu Banyak', 'email' => 'banyak@contoh.test',
            'telp' => '08123457778', 'jumlah_pendaftar' => $batas + 1, 'setuju' => '1',
            'peserta' => $orang,
        ])->assertSessionHasErrors('jumlah_pendaftar');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    /**
     * Dua orang beremail sama berarti satu di antaranya tidak akan pernah
     * menerima apa pun, dan yang ketahuan belakangan hanyalah "sertifikat
     * saya tidak sampai".
     */
    #[Test]
    public function email_peserta_yang_kembar_ditolak(): void
    {
        $sesi = $this->sesi();

        // Kembar sesama peserta.
        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua', 'email' => 'ketua@contoh.test',
            'telp' => '08123457779', 'jumlah_pendaftar' => 3, 'setuju' => '1',
            'peserta' => [
                ['nama' => 'Dua', 'email' => 'sama@contoh.test'],
                ['nama' => 'Tiga', 'email' => 'Sama@Contoh.Test'],
            ],
        ])->assertSessionHasErrors('peserta');

        // Kembar dengan pendaftar utamanya.
        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua', 'email' => 'ketua@contoh.test',
            'telp' => '08123457779', 'jumlah_pendaftar' => 2, 'setuju' => '1',
            'peserta' => [['nama' => 'Dua', 'email' => 'ketua@contoh.test']],
        ])->assertSessionHasErrors('peserta');

        $this->assertSame(0, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function peserta_tambahan_yang_beremail_ikut_dikirimi_bukti(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Ketua Kirim', 'email' => 'ketua.kirim@contoh.test',
            'telp' => '08123457780', 'jumlah_pendaftar' => 3, 'setuju' => '1',
            'peserta' => [
                ['nama' => 'Dua', 'email' => 'dua.kirim@contoh.test'],
                ['nama' => 'Tiga'],
            ],
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\WebinarEksklusifPendaftaranMail::class,
            fn ($surat) => $surat->hasTo('ketua.kirim@contoh.test')
                && $surat->hasTo('dua.kirim@contoh.test')
        );
    }

    /**
     * Isian peserta memakai kartu bernomor, bukan sederet kotak datar.
     *
     * Rupa lamanya: dua kotak beruntun per orang dipisahkan label kecil
     * abu-abu. Untuk 10 peserta itu 18 kotak nyaris identik dalam satu kolom —
     * tidak ada tanda di mana satu orang berakhir, jadi mudah salah mengisi
     * email orang ke baris orang lain.
     *
     * Yang dijaga di sini bagian yang dirender PELADEN; perilakunya
     * (penghitung, penanda terisi) dirakit skrip dan diperiksa di peramban.
     */
    #[Test]
    public function wadah_peserta_punya_penghitung_dan_petunjuk_gelar(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->assertSee('id="ses-peserta-hitung"', false)
            // Petunjuk gelarnya dipindah ke kalimat ini sebab placeholder
            // sepanjang "Nama lengkap + gelar" terpotong di layar 320 px.
            ->assertSee('beserta gelarnya', false);
    }

    // --------------------------------------------- pemeriksa kode diskon

    /**
     * Kode diperiksa SEBELUM borangnya dikirim.
     *
     * Tanpa jalur ini, orang yang mengetik kode tidak mendapat tanda apa pun:
     * totalnya di layar tetap harga penuh, tidak ada kabar benar atau salah,
     * dan baru ketahuan sesudah mengirim. Yang salah ketik membayar penuh
     * tanpa tahu kenapa.
     */
    #[Test]
    public function kode_diskon_bisa_diperiksa_sebelum_mengirim(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        $this->postJson(route('public.webinareksklusif.cekdiskon', $sesi->id), ['kode' => 'HEMAT30'])
            ->assertOk()
            ->assertJson(['cocok' => true, 'potongan' => 30000, 'total' => 99000]);

        // Jumlah peserta ikut diperhitungkan.
        $this->postJson(route('public.webinareksklusif.cekdiskon', $sesi->id),
            ['kode' => 'HEMAT30', 'jumlah' => 3])
            ->assertOk()
            ->assertJson(['cocok' => true, 'total' => 357000]);
    }

    #[Test]
    public function kode_yang_salah_dijawab_tidak_cocok(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        foreach (['SALAH', 'hemat30'] as $kode) {
            $this->postJson(route('public.webinareksklusif.cekdiskon', $sesi->id), ['kode' => $kode])
                ->assertOk()
                ->assertJson(['cocok' => false]);
        }
    }

    /**
     * Jawaban pemeriksa TIDAK pernah dipercaya sebagai dasar menagih:
     * potongannya dihitung ulang dari angkatannya saat menyimpan.
     */
    #[Test]
    public function potongan_tetap_dihitung_ulang_saat_menyimpan(): void
    {
        $sesi = $this->sesi(['kode_diskon' => 'HEMAT30', 'nominal_diskon' => '30000']);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Curang', 'email' => 'curang@contoh.test',
            'telp' => '08123456700', 'jumlah_pendaftar' => 1, 'setuju' => '1',
            'kode_diskon' => 'HEMAT30',
            // Nilai-nilai ini dikirim peramban dan HARUS diabaikan.
            'nominal_diskon' => '129000',
            'total_pembayaran' => '0',
        ]);

        $baris = WebinarEksklusifPendaftaran::where('email', 'curang@contoh.test')->first();

        $this->assertSame(99000 + (int) $baris->kode_unik, (int) $baris->total_pembayaran,
            'yang berlaku nominal dari angkatannya, bukan yang dikirim peramban');
    }

    // ------------------------------------------------- tinggi kartu isian

    /**
     * Tombol daftar harus ada DI ATAS LIPATAN saat halaman dibuka.
     *
     * Terukur 3 Okt 2026: kartunya tumbuh jadi 992 px sementara layar 900 px,
     * jadi tombol "Daftar sekarang" jatuh di bawah lipatan di semua lebar —
     * di halaman yang seluruh tujuannya mendaftar. Penyebabnya penumpukan
     * perbaikan kecil: kotak persetujuan, petunjuk rombongan, keterangan
     * kuota, isian kode diskon. Masing-masing benar sendiri; akumulasinya
     * tidak.
     *
     * Yang dijaga di sini bagian yang bisa diperiksa dari markup: baris-baris
     * yang dulu selalu tampil kini hanya muncul saat memang berguna.
     */
    #[Test]
    public function baris_yang_hanya_berguna_untuk_rombongan_tidak_tampil_di_muka(): void
    {
        $sesi = $this->sesi();

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->getContent();

        foreach (['ses-bantu-rombongan', 'ses-total-rincian', 'ses-peserta'] as $id) {
            $this->assertMatchesRegularExpression(
                '/id="' . $id . '"[^>]*\bhidden\b/',
                $isi,
                $id . ' harus tersembunyi saat pesertanya baru satu'
            );
        }
    }

    // ------------------------------------------------------- kode unik

    /**
     * Kode unik 500–1.500 ditambahkan ke tagihan transfer manual.
     *
     * Gunanya mencocokkan pembayaran: dua orang yang mendaftar paket sama
     * mengirim nominal yang persis sama, dan panitia tidak punya cara tahu
     * uang masuk itu dari siapa. Dengan tiga digit terakhir yang berbeda,
     * satu mutasi rekening langsung menunjuk satu pendaftaran.
     */
    #[Test]
    public function kode_unik_ditambahkan_ke_tagihan(): void
    {
        $sesi = $this->sesi();
        $min = \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MIN;
        $maks = \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MAKS;

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Unik', 'email' => 'unik@contoh.test',
            'telp' => '08123450900', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('email', 'unik@contoh.test')->first();
        $kode = (int) $p->kode_unik;

        $this->assertGreaterThanOrEqual($min, $kode);
        $this->assertLessThanOrEqual($maks, $kode);

        $this->assertSame(129000 + $kode, (int) $p->total_pembayaran,
            'kode unik DITAMBAHKAN, bukan dikurangkan');
    }

    /**
     * Tidak boleh ada dua pendaftaran hidup bertotal sama di satu angkatan.
     *
     * Acak saja tidak cukup: seluruh gunanya membuat nominal berbeda
     * antar-orang, dan kalau dua total kebetulan sama persis, satu mutasi
     * rekening menunjuk dua pendaftaran — panitia kembali menebak, dan tidak
     * ada gejala apa pun yang menandainya.
     */
    /**
     * Skenarionya dibuat MENENTUKAN: semua nilai kode unik sudah terpakai
     * kecuali satu.
     *
     * Versi pertama uji ini hanya mendaftarkan dua belas orang, dan itu TIDAK
     * membuktikan apa-apa — dua belas nilai di antara 1.001 kemungkinan jarang
     * bertabrakan, jadi ia tetap hijau walau penjaganya dicabut dan kodenya
     * acak murni. Sudah dicoba tiga kali, hijau ketiganya.
     *
     * Dengan hanya satu nilai tersisa, acak murni hampir pasti meleset dan
     * penjaganyalah satu-satunya yang bisa menemukannya.
     */
    #[Test]
    public function kode_unik_menghindari_total_yang_sudah_terpakai(): void
    {
        $sesi = $this->sesi(['total_kuota' => '2000', 'sisa_kuota' => '2000']);

        $min = \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MIN;
        $maks = \App\Http\Controllers\Publict\PublicWebinarEksklusifController::KODE_UNIK_MAKS;
        $bebas = $maks;  // satu-satunya yang disisakan

        $baris = [];

        for ($kode = $min; $kode < $bebas; $kode++) {
            $baris[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'token' => \Illuminate\Support\Str::random(40),
                'id_transaksi' => 'WE-UJI-' . $kode,
                'kategori_id' => $sesi->id,
                'nama' => 'Penghuni ' . $kode,
                'email' => 'huni' . $kode . '@contoh.test',
                'telp' => '628123400000',
                'jumlah_pendaftar' => 1,
                'total_pembayaran' => (string) (129000 + $kode),
                'cara_bayar' => 'transfer',
                'status' => 'pending',
                'kedaluwarsa_pada' => now()->addHours(5),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        \Illuminate\Support\Facades\DB::table('webinar_eksklusif_pendaftaran')->insert($baris);

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Penyelip', 'email' => 'selip@contoh.test',
            'telp' => '08123450999', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ])->assertRedirect();

        $p = WebinarEksklusifPendaftaran::where('email', 'selip@contoh.test')->first();

        $this->assertNotNull($p);
        $this->assertSame($bebas, (int) $p->kode_unik,
            'satu-satunya kode yang totalnya belum terpakai harus yang dipilih');
        $this->assertSame(129000 + $bebas, (int) $p->total_pembayaran);
    }

    #[Test]
    public function total_tidak_pernah_kembar_di_satu_angkatan(): void
    {
        $sesi = $this->sesi(['total_kuota' => '60', 'sisa_kuota' => '60']);

        /*
         * Pembatas 6 kiriman per menit dilewati DI SINI SAJA. Yang diuji
         * keunikan totalnya, dan dua belas kiriman beruntun hanya mungkin
         * tanpa pembatasnya — pembatasnya sendiri sudah punya ujinya sendiri.
         */
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        for ($i = 0; $i < 12; $i++) {
            $this->post(route('public.webinareksklusif.store'), [
                'kategori_id' => $sesi->id, 'nama' => 'Orang ' . $i,
                'email' => 'orang' . $i . '@contoh.test',
                'telp' => '0812345' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'jumlah_pendaftar' => 1, 'setuju' => '1',
            ]);
        }

        $total = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)
            ->pluck('total_pembayaran')->map(fn ($t) => (int) $t)->all();

        $this->assertCount(12, $total, 'prasyarat: dua belas pendaftaran tersimpan');
        $this->assertSame(count($total), count(array_unique($total)),
            'tiap pendaftaran harus bertotal berbeda supaya transfernya bisa dicocokkan');
    }

    /**
     * Lewat gerbang pembayaran kode unik TIDAK dipakai: pencocokannya memakai
     * nomor rujukan, dan menambah angka receh di sana justru membingungkan.
     */
    #[Test]
    public function tanpa_kode_unik_kalau_lewat_gerbang_pembayaran(): void
    {
        $palsu = new class extends \App\Services\Doku
        {
            public function __construct() {}

            public function siap(): bool
            {
                return true;
            }

            public function buatTagihan(array $data): array
            {
                return ['berhasil' => true, 'url' => 'https://contoh.test/bayar', 'rujukan' => 'UJI-1'];
            }
        };

        $this->app->instance(\App\Services\Doku::class, $palsu);

        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Gerbang', 'email' => 'gerbang@contoh.test',
            'telp' => '08123450901', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'gerbang@contoh.test')->first();

        $this->assertNull($p->kode_unik);
        $this->assertSame(129000, (int) $p->total_pembayaran, 'nominalnya bulat, tanpa kode unik');
    }

    #[Test]
    public function halaman_status_menyebut_kode_unik_dan_alasannya(): void
    {
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Unik Status', 'email' => 'unik.status@contoh.test',
            'telp' => '08123450902', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'unik.status@contoh.test')->first();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Kode unik', false)
            // Alasannya disebut: tanpa itu orang mengira angka ganjilnya salah
            // hitung, lalu membulatkannya.
            ->assertSee('tidak bisa kami cocokkan', false);
    }

    /**
     * Kotak centangnya digambar sendiri, tetapi HARUS tetap <input> asli.
     *
     * Godaannya menyembunyikan input lalu menggambar <span> sebagai
     * penggantinya. Begitu itu dilakukan, semua yang gratis dari unsur borang
     * sungguhan hilang sekaligus: jangkauan papan ketik, pembacaan oleh
     * pembaca layar, keikutsertaan saat borang dikirim, dan sorotan fokus —
     * dan tidak satu pun dari itu menampakkan diri saat diklik tetikus.
     */
    #[Test]
    public function kotak_centang_tetap_unsur_borang_sungguhan(): void
    {
        $sesi = $this->sesi();

        $isi = $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]*type="checkbox"[^>]*id="ses-setuju"|<input[^>]*id="ses-setuju"[^>]*type="checkbox"/',
            $isi,
            'persetujuannya harus tetap <input type="checkbox">, bukan unsur palsu');

        $this->assertStringContainsString('name="setuju"', $isi,
            'tanpa name, centangnya tidak ikut terkirim dan validasinya selalu gagal');

        $this->assertStringContainsString('<label for="ses-setuju">', $isi,
            'label ber-for yang membuat kalimat panjangnya ikut bisa diketuk');
    }

    /**
     * Kalimat "Totalnya nanti ditambah kode unik ..." dibuang atas permintaan.
     * Kode uniknya tetap jalan — yang dibuang hanya penyebutannya di borang;
     * halaman status dan surat buktinya tetap menjelaskannya.
     */
    #[Test]
    public function borang_tidak_lagi_menyebut_kode_unik(): void
    {
        $sesi = $this->sesi();

        $this->get(route('public.webinareksklusif.daftar', $sesi->id))
            ->assertOk()
            ->assertDontSee('ditambah kode unik', false);
    }

    #[Test]
    public function hitung_mundur_di_halaman_status_berjalan_sendiri(): void
    {
        /*
         * Angkanya dulu dirender peladen SEKALI lewat diffForHumans, jadi
         * "23 jam 59 menit lagi" membeku di layar sampai halamannya dimuat
         * ulang. Orang yang membuka tautan ini besok paginya tetap membaca
         * 23 jam — padahal kursinya sudah dilepas semalam.
         *
         * Yang dijaga PENANDANYA berikut bentuk tanggalnya; di situlah
         * skripnya berpegang.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Mundur', 'email' => 'mundur@contoh.test',
            'telp' => '08123458888', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'mundur@contoh.test')->firstOrFail();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        /*
         * Penandanya kini di <div class="sta-mundur">, bukan lagi di <strong>
         * di tengah kalimat: hitung mundurnya berdiri sendiri sebagai kotak
         * angka per satuan.
         *
         * Dicari dengan tanda "=" dan kutip — tanpa itu, pemilih
         * [data-mis-mundur] di dalam skripnya sendiri ikut terhitung.
         */
        $ada = preg_match('/data-mis-mundur="(?<batas>[^"]+)"/', $isi, $cocok);

        $this->assertSame(1, $ada,
            'Penanda hitung mundurnya hilang; angkanya akan membeku tanpa satu pun galat terbit.');

        /*
         * Selisih zonanya WAJIB ikut tertulis.
         *
         * Tanpa "+07:00", peramban menafsirkan angkanya memakai zona waktu
         * PEMBACANYA — peserta yang membuka dari luar Jakarta melihat sisa
         * waktu meleset berjam-jam, dan tidak ada yang tahu sampai ada yang
         * kehilangan kursinya.
         */
        $this->assertMatchesRegularExpression('/[+-]\d{2}:\d{2}$/', $cocok['batas'],
            'Batas waktunya ditulis tanpa selisih zona: ' . $cocok['batas']);

        $this->assertSame(
            $p->kedaluwarsa_pada->toIso8601String(), $cocok['batas'],
            'Yang ditulis di halaman bukan batas waktu yang tersimpan.');

        /*
         * Kalimat dari peladen TETAP ada di dalamnya. Tanpa skrip — peramban
         * lama, skrip gagal dimuat — yang terbaca harus tetap kalimat yang
         * masuk akal, bukan kotak kosong.
         */
        /*
         * Angka awalnya dirender PELADEN. Tanpa itu, yang tergambar sebelum
         * skripnya jalan — atau di peramban yang skripnya gagal dimuat — tiga
         * kotak kosong.
         */
        $this->assertMatchesRegularExpression('/<strong data-mundur-jam>\d{2}<\/strong>/', $isi,
            'Angka jamnya tidak dirender peladen; kotaknya akan kosong tanpa skrip.');
        $this->assertMatchesRegularExpression('/<strong data-mundur-menit>\d{2}<\/strong>/', $isi);
        $this->assertMatchesRegularExpression('/<strong data-mundur-detik>\d{2}<\/strong>/', $isi);

        // Kalimatnya turun ke bawah angkanya, tidak lagi menyelip di tengah.
        $this->assertStringContainsString('Setelah itu kursinya dilepas untuk orang lain.', $isi);
    }

    #[Test]
    public function halaman_status_dibagi_berlajur_selama_masih_harus_dibayar(): void
    {
        /*
         * Halamannya dulu satu pita 640px di tengah layar. Di layar 1.470px
         * itu menyisakan 830px kosong di kiri-kanan, sementara isinya sendiri
         * menggulung panjang.
         *
         * Dijaga PENANDA KELASNYA, bukan lebarnya: lebar hanya ada di
         * peramban, dan uji yang mengukurnya tidak bisa dijalankan di sini.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Tata', 'email' => 'tata@contoh.test',
            'telp' => '08123459999', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'tata@contoh.test')->firstOrFail();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        /*
         * Dicocokkan sebagai ATRIBUT class unsurnya, bukan kata mentah di
         * seluruh halaman: nama kelas yang sama juga tertulis sebagai pemilih
         * di dalam blok <style> halaman ini, dan percobaan pertama merah pada
         * markah yang justru benar karena menemukannya di situ.
         */
        $kisi = preg_match('/<div class="(sta-kisi[^"]*)"/', $isi, $cocokKisi);
        $wadah = preg_match('/<div class="container (sta-wadah[^"]*)"/', $isi, $cocokWadah);

        $this->assertSame(1, $kisi, 'Pembungkus dua lajurnya hilang.');
        $this->assertStringNotContainsString('sta-kisi-tunggal', $cocokKisi[1],
            'Yang masih harus membayar justru dipaksa satu lajur.');

        $this->assertSame(1, $wadah, 'Pembungkus halamannya hilang.');
        $this->assertStringNotContainsString('sta-wadah-ramping', $cocokWadah[1],
            'Halamannya disempitkan padahal isinya dua lajur.');

        /*
         * DUA pembungkus lajur, dan keduanya BERNAMA.
         *
         * Tiga lajur memaksa ketiganya berbagi baris yang sama: rinciannya
         * 341px sementara dua lajur di kanannya 606px, jadi 265px menggantung
         * kosong di bawah lajur kiri. Dua lajur yang masing-masing menumpuk
         * isinya sendiri terukur 621 lawan 671 — sisa kosongnya 50px.
         *
         * Namanya ikut dijaga, bukan cuma jumlahnya: letaknya dipatok per
         * kelas, jadi lajur tanpa nama akan jatuh ke sel yang salah tanpa
         * satu pun galat terbit.
         */
        $this->assertSame(2, substr_count($isi, '<div class="sta-lajur '),
            'Jumlah lajurnya bukan dua.');

        $this->assertStringContainsString('<div class="sta-lajur sta-lajur-kiri">', $isi,
            'Lajur kirinya kehilangan nama; letaknya tidak lagi bisa dipatok.');

        $this->assertStringContainsString('<div class="sta-lajur sta-lajur-kanan">', $isi,
            'Lajur kanannya kehilangan nama; letaknya tidak lagi bisa dipatok.');

        /*
         * Isinya ikut dijaga. Pembagiannya bukan selera: yang kiri bahan
         * BACAAN (rincian lalu cara bayar), yang kanan TINDAKAN (sisa waktu
         * lalu borang unggah). Tertukar, hitung mundurnya kembali berdiri di
         * samping rincian dan ketimpangan 265px itu kembali.
         */
        $kiri = $this->isiLajur($isi, 'sta-lajur-kiri');
        $kanan = $this->isiLajur($isi, 'sta-lajur-kanan');

        $this->assertStringContainsString('class="sta-rincian"', $kiri, 'Rinciannya tidak di lajur kiri.');
        $this->assertStringContainsString('class="sta-bayar"', $kiri, 'Cara bayarnya tidak di lajur kiri.');
        $this->assertStringNotContainsString('class="sta-bayar"', $kanan, 'Cara bayarnya bocor ke lajur kanan.');

        $this->assertStringContainsString('class="sta-unggah"', $kanan, 'Borang unggahnya tidak di lajur kanan.');

        /*
         * Hitung mundurnya bukan anak lajur mana pun, melainkan anak LANGSUNG
         * kisinya — demi ponsel.
         *
         * Di dalam lajur kanan ia baru tergambar sesudah seluruh isi lajur
         * kiri habis: terukur 1.395px dari puncak halaman di layar 390px, dua
         * layar penuh ke bawah. Yang paling mendesak jadi yang paling akhir
         * terlihat. Letaknya di layar lebar dipatok CSS, jadi menaikkannya di
         * markah tidak memindahkannya di layar lebar.
         */
        $this->assertStringNotContainsString('data-mis-mundur="', $kiri,
            'Hitung mundurnya masuk ke lajur kiri; di ponsel ia akan tenggelam di bawah cara bayar.');

        $this->assertStringNotContainsString('data-mis-mundur="', $kanan,
            'Hitung mundurnya masuk ke lajur kanan; di ponsel ia akan tenggelam di bawah cara bayar.');

        /*
         * Dibandingkan LETAKNYA, bukan dicocokkan dengan pola jarak. Pola
         * "paling jauh sekian ratus huruf" tetap hijau walau hitung mundurnya
         * sudah pindah, asal pindahnya tidak terlalu jauh.
         */
        $letakMundur = strpos($isi, 'data-mis-mundur="');
        $letakLajur = strpos($isi, '<div class="sta-lajur ');

        $this->assertNotFalse($letakMundur, 'Penanda hitung mundurnya hilang.');
        $this->assertNotFalse($letakLajur, 'Pembungkus lajurnya hilang.');

        $this->assertLessThan($letakLajur, $letakMundur,
            'Hitung mundurnya turun ke bawah lajur; di ponsel ia akan tenggelam di bawah cara bayar.');

        /*
         * Kepala halaman dibungkus supaya ikon, judul, dan kalimatnya bisa
         * disejajarkan jadi satu baris di layar lebar. Bertumpuk di tengah
         * ketiganya memakan 184px sebelum isi pertamanya terlihat; sebaris
         * 62px.
         */
        /*
         * Dicocokkan dengan pola, bukan teks persis: atribut kelasnya kini
         * dirakit @if, jadi isinya "sta-kepala " berspasi ekor saat pengubah
         * terpusatnya tidak dipakai.
         */
        $this->assertMatchesRegularExpression('/<div class="sta-kepala[ "]/', $isi,
            'Pembungkus kepala hilang; ikon dan judulnya akan bertumpuk lagi.');
    }

    #[Test]
    public function halaman_status_menyempit_saat_tidak_ada_yang_harus_dikerjakan(): void
    {
        /*
         * Yang sudah lunas tidak punya lajur kanan sama sekali. Dibiarkan
         * selebar layar, yang tergambar kartu putih 1.382px dengan kolom
         * 620px melayang di tengahnya — dan garis pemisah vertikal berdiri
         * sendiri di sebelah ruang kosong.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Tata Lunas', 'email' => 'tatalunas@contoh.test',
            'telp' => '08123459998', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'tatalunas@contoh.test')->firstOrFail();
        $p->forceFill(['status' => 'paid', 'bayar_status' => 'lunas', 'bayar_pada' => now()])->save();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        preg_match('/<div class="(sta-kisi[^"]*)"/', $isi, $cocokKisi);
        preg_match('/<div class="container (sta-wadah[^"]*)"/', $isi, $cocokWadah);

        $this->assertStringContainsString('sta-kisi-tunggal', $cocokKisi[1] ?? '',
            'Masih dua lajur padahal lajur kanannya kosong.');
        $this->assertStringContainsString('sta-wadah-ramping', $cocokWadah[1] ?? '',
            'Halamannya tetap selebar layar untuk isi satu lajur.');
    }

    #[Test]
    public function lebarnya_dilepas_dan_dua_lajurnya_dipasang_di_layar_lebar(): void
    {
        /*
         * Penjaga tingkat SUMBER untuk dua aturan yang tidak bisa dilihat dari
         * markah: wadahnya tidak lagi dipatok 640px, dan kisinya baru jadi dua
         * lajur mulai 992px.
         *
         * Komentar dibuang lebih dulu. Percobaan sebelumnya di proyek ini
         * merah/hijau palsu karena yang cocok justru kalimat di komentarnya
         * sendiri, bukan aturannya.
         */
        $sumber = file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        );

        $aturan = preg_replace('#/\*.*?\*/#s', '', $sumber);
        $aturan = preg_replace('#\{\{--.*?--\}\}#s', '', (string) $aturan);

        $this->assertMatchesRegularExpression('/\.sta-wadah\s*\{[^}]*max-width:\s*none/', (string) $aturan,
            'Wadahnya masih dipatok lebar tetap; halamannya tidak akan penuh.');

        $this->assertMatchesRegularExpression('/@media\s*\(min-width:\s*992px\)\s*\{.*?\.sta-kisi\s*\{[^}]*display:\s*grid/s', (string) $aturan,
            'Kisi dua lajurnya tidak dipasang di layar lebar.');

        /*
         * minmax(0, ...) WAJIB. Tanpa itu lajurnya memakai min-width auto dan
         * isi terlebar di dalamnya — nomor rekening yang berspasi — melebarkan
         * lajurnya melewati jatahnya, lalu kartunya meluber keluar layar.
         */
        $this->assertMatchesRegularExpression(
            '/grid-template-columns:\s*minmax\(0,[^;]*minmax\(0,/',
            (string) $aturan,
            'Lajurnya tidak dijaga minmax(0,...); kartunya bisa meluber.'
        );
    }

    #[Test]
    public function dua_lajurnya_ditempatkan_per_kelas_tanpa_lajur_ketiga(): void
    {
        /*
         * Penjaga tingkat SUMBER untuk tata letak yang tidak terlihat dari
         * markah.
         *
         * Dulu ada ambang KEDUA di 1200px yang memasang lajur ketiga, dan di
         * bawah itu lajur ketiganya turun jadi pita selebar kisi. Tiga lajur
         * sudah tidak ada: ketiganya dipaksa berbagi baris, dan rincian yang
         * 341px bersanding dengan lajur 606px meninggalkan 265px kosong.
         *
         * Yang dijaga sekarang: letaknya dipatok per KELAS, bukan per urutan.
         * Isi tiap lajur berbeda-beda per keadaan — borang unggah hilang saat
         * buktinya sudah masuk, cara bayar hilang di layar ringkas — dan
         * penempatan per urutan akan menggeser sisanya ke sel yang salah
         * begitu salah satunya tidak digambar.
         */
        $sumber = file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        );

        // Komentar dibuang dulu; dua percobaan sebelumnya di berkas ini
        // merah/hijau palsu karena yang cocok kalimat di komentarnya sendiri.
        $aturan = preg_replace('#/\*.*?\*/#s', '', $sumber);
        $aturan = preg_replace('#\{\{--.*?--\}\}#s', '', (string) $aturan);

        $this->assertMatchesRegularExpression(
            '/\.sta-lajur-kiri\s*\{[^}]*grid-column:\s*1/',
            (string) $aturan,
            'Lajur kirinya tidak lagi dipatok ke kolom 1.'
        );

        $this->assertMatchesRegularExpression(
            '/\.sta-lajur-kanan\s*\{[^}]*grid-column:\s*2/',
            (string) $aturan,
            'Lajur kanannya tidak lagi dipatok ke kolom 2.'
        );

        $this->assertSame(0, preg_match('/grid-template-columns:\s*repeat\(3,/', (string) $aturan),
            'Lajur ketiganya dipasang lagi; ketiganya akan kembali berbagi baris.');

        $this->assertSame(0, preg_match('/\.sta-lajur:nth-child\(/', (string) $aturan),
            'Letaknya kembali dipatok per urutan; satu lajur yang tidak digambar akan menggeser sisanya.');
    }

    #[Test]
    public function keterangan_format_unggahan_cukup_pendek_untuk_lajur_sempit(): void
    {
        /*
         * Kalimatnya dikunci agar tidak patah — dipatahkan, "MB" turun
         * sendirian dan kotaknya jadi 100px di ponsel. Karena tidak boleh
         * patah, ia MELUBERKAN seluruh kartu begitu lajurnya menyempit:
         * terukur 242px di lajur selebar 219px pada 1200px.
         *
         * Jadi panjangnya ikut dijaga. Dihitung huruf, bukan piksel — piksel
         * hanya ada di peramban.
         */
        $sumber = file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        );

        $ada = preg_match_all('/<small>(?<teks>[^<]*8 MB)<\/small>/', $sumber, $cocok, PREG_SET_ORDER);

        $this->assertGreaterThanOrEqual(1, $ada, 'Keterangan formatnya hilang.');

        foreach ($cocok as $c) {
            $teks = html_entity_decode(str_replace('&middot;', '.', $c['teks']));

            $this->assertLessThanOrEqual(30, mb_strlen($teks),
                'Keterangan format terlalu panjang untuk lajur sempit: "' . $teks . '"');
        }
    }




    #[Test]
    public function pendaftaran_yang_sudah_kedaluwarsa_tidak_menghalangi_pendaftaran_baru(): void
    {
        /*
         * Penjaganya hanya berlaku selama pendaftaran lamanya MASIH BERLAKU.
         * Berlaku juga untuk yang sudah kedaluwarsa, orang yang kelewat batas
         * waktu tidak akan pernah bisa mendaftar ulang dengan nomornya
         * sendiri.
         *
         * Yang dijaga PERILAKUNYA, bukan satu baris tertentu: ada DUA
         * mekanisme yang sama-sama mencukupi — kursi kedaluwarsa dilepas lebih
         * dulu (statusnya jadi 'expired', lalu tersaring whereIn status), dan
         * klausa kedaluwarsa_pada > now(). Dicopot salah satu, uji ini tetap
         * hijau; dicopot keduanya, barulah merah. Itu memang disengaja — dan
         * dicatat di sini supaya tidak ada yang mengira salah satunya mubazir
         * lalu membuangnya tanpa menyadari yang satunya ikut menanggung.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $dasar = [
            'kategori_id' => $sesi->id, 'nama' => 'Lewat', 'email' => 'lewat@contoh.test',
            'telp' => '08123450003', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ];

        $this->post(route('public.webinareksklusif.store'), $dasar);

        $lama = WebinarEksklusifPendaftaran::where('email', 'lewat@contoh.test')->firstOrFail();
        $lama->forceFill(['kedaluwarsa_pada' => now()->subHour()])->save();

        $this->post(route('public.webinareksklusif.store'),
            $dasar + ['email' => 'lewat2@contoh.test'])->assertRedirect();

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'Yang batas waktunya sudah lewat ikut menghalangi pendaftaran baru.');
    }
    // ------------------------------------------- penjaga pendaftaran ganda

    /** Satu borang pendaftaran, dengan bagian yang ditimpa seperlunya. */
    private function borang(KategoriLayanan $sesi, array $lain = []): array
    {
        return array_merge([
            'kategori_id' => $sesi->id,
            'nama' => 'Berto Juni Krisnanto',
            'email' => 'berto@contoh.test',
            'telp' => '0895421735441',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ], $lain);
    }

    #[Test]
    public function data_yang_sama_persis_hanya_jadi_satu_pendaftaran(): void
    {
        /*
         * Yang dicegat hanya pengulangan data yang BENAR-BENAR sama — orang
         * yang menekan "Daftar" dua kali, atau menyegarkan halaman
         * pembayarannya. Tiap pengulangan yang lolos memotong satu kursi yang
         * baru kembali 24 jam kemudian.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $pertama = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        // Nomornya ditulis dalam bentuk lain, namanya berspasi ganda dan
        // berhuruf besar — ketiganya tetap orang yang sama.
        $jawab = $this->post(route('public.webinareksklusif.store'), $this->borang($sesi, [
            'telp' => '62895421735441',
            'nama' => 'BERTO  JUNI   KRISNANTO',
        ]));

        $this->assertSame(1, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'Pendaftaran keduanya tetap dibuat; kursinya terpotong dua kali.');

        $this->assertSame(19, (int) $sesi->fresh()->sisa_kuota);

        $jawab->assertRedirect(route('public.webinareksklusif.status', $pertama->getKey()));
    }

    #[Test]
    public function nomor_sama_tetapi_nama_dan_email_beda_tetap_dapat_pendaftaran_sendiri(): void
    {
        /*
         * Satu nomor WhatsApp dipakai bersama lebih sering daripada yang
         * terlihat: panitia kampus mendaftarkan beberapa orang dari nomornya
         * sendiri, dan suami-istri berbagi satu nomor.
         *
         * Dicegat dari nomornya saja, orang kedua tidak akan pernah bisa
         * didaftarkan — yang terjadi bukan kuota terjaga, melainkan
         * pendaftaran yang hilang.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi, [
            'nama' => 'Siti Aminah',
            'email' => 'siti@contoh.test',
        ]));

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'Orang kedua dari nomor yang sama ikut dicegat; ia tidak bisa mendaftar sama sekali.');

        $this->assertSame(18, (int) $sesi->fresh()->sisa_kuota);
    }

    #[Test]
    public function nomor_sama_nama_sama_tetapi_email_beda_tetap_pendaftaran_sendiri(): void
    {
        // Satu dari tiga berbeda sudah cukup; yang dicegat hanya yang ketiganya sama.
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'),
            $this->borang($sesi, ['email' => 'berto.lain@contoh.test']));

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function nomor_dan_email_sama_tetapi_nama_beda_tetap_pendaftaran_sendiri(): void
    {
        /*
         * Satu-satunya yang berbeda NAMANYA. Ini keadaan panitia yang
         * mendaftarkan orang kedua memakai nomor dan email kantornya sendiri —
         * dan tanpa pembandingan nama, orang kedua itu tidak akan pernah bisa
         * didaftarkan.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'),
            $this->borang($sesi, ['nama' => 'Siti Aminah']));

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count(),
            'Orang kedua ikut dicegat padahal namanya berbeda.');
    }

    #[Test]
    public function email_dan_nama_sama_tetapi_nomor_beda_tetap_pendaftaran_sendiri(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'),
            $this->borang($sesi, ['telp' => '08123450009']));

        $this->assertSame(2, WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->count());
    }

    #[Test]
    public function yang_dicegat_tidak_disambut_pita_merah(): void
    {
        /*
         * Ia tidak berbuat salah — pendaftarannya memang ada dan masih
         * berlaku. Pita merah di layar orang yang tidak bersalah membuatnya
         * mengira pendaftarannya gagal, lalu ia mencoba lagi; justru itu yang
         * hendak dihentikan.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi))
            ->assertSessionHas('kabar')
            ->assertSessionHas('we_ringkas_' . $p->getKey(), true)
            ->assertSessionMissing('error');
    }

    #[Test]
    public function layar_yang_dicegat_tidak_menawarkan_bayar_maupun_unggah(): void
    {
        /*
         * Orang yang sudah mengirim bukti lalu mendaftar lagi — karena ragu,
         * atau karena kehilangan tautannya — akan melihat borang unggah
         * terbuka lagi dan mengunggah lagi. Dan lagi. Yang tertinggal di
         * panitia sepuluh salinan bukti yang sama.
         *
         * Maka yang digambar hanya rincian transaksinya, hitung mundur sisa
         * waktunya, dan statusnya.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $isi = $this->followingRedirects()
            ->post(route('public.webinareksklusif.store'), $this->borang($sesi))
            ->assertOk()
            ->getContent();

        // Yang HARUS tetap ada: rincian, hitung mundur, dan statusnya.
        $this->assertStringContainsString('Nomor pendaftaran', $isi);
        $this->assertStringContainsString('data-mis-mundur="', $isi,
            'Hitung mundurnya hilang; justru itu yang dicari orang saat kembali.');
        $this->assertStringContainsString('Pendaftaran Anda sudah masuk', $isi);

        // Yang HARUS hilang.
        $this->assertStringNotContainsString('Cara membayar', $isi,
            'Langkah membayar masih tergambar di layar orang yang cuma memeriksa.');
        $this->assertStringNotContainsString('2164 0100 0467 563', $isi,
            'Nomor rekening masih tergambar.');
        $this->assertSame(0, preg_match_all('/<input[^>]*type="file"/', $isi),
            'Borang unggahnya masih ada; ia akan mengunggah lagi.');
    }

    #[Test]
    public function layar_ringkas_bertahan_saat_halamannya_dimuat_ulang(): void
    {
        /*
         * Versi pertama memakai pesan sesi sekali-pakai, dan layar ringkasnya
         * hilang begitu halamannya dimuat ulang — layar penuh beserta borang
         * unggahnya muncul lagi tepat pada orang yang baru saja diberi tahu
         * tidak perlu berbuat apa-apa. Ditekan F5 sekali, seluruh penjagaannya
         * batal.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        // Dimuat ulang — persis seperti menekan F5.
        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Cara membayar', $isi,
            'Layar penuh muncul lagi sesudah dimuat ulang.');
        $this->assertSame(0, preg_match_all('/<input[^>]*type="file"/', $isi),
            'Borang unggahnya muncul lagi sesudah dimuat ulang; ia akan mengunggah lagi.');

        // Dan dua kali muat ulang pun tetap ringkas.
        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('Cara membayar');
    }

    #[Test]
    public function layar_ringkas_tidak_punya_jalan_keluar_ke_layar_penuh(): void
    {
        /*
         * Versi sebelumnya menyediakan tautan ?penuh=1 sebagai pintu darurat.
         * Sekali ditekan penanda ringkasnya terhapus — dan sejak itu TIAP muat
         * ulang memulangkan layar penuh beserta borang unggahnya, persis
         * keadaan yang hendak dihindari. Pintu daruratnya sendiri yang jadi
         * lubangnya.
         *
         * Yang dicegat memang sudah pernah mendaftar; cara membayarnya ada di
         * email bukti pendaftaran yang ia terima saat itu, dan tombol
         * pengirim ulangnya ada di layar ini.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertDontSee('penuh=1', false);

        // Dan alamat itu pun tidak lagi membuka layar penuhnya.
        $this->get(route('public.webinareksklusif.status', $p->getKey()) . '?penuh=1')
            ->assertOk()
            ->assertDontSee('Cara membayar');

        // Tombol pengirim ulang email TETAP ada; itu jalan satu-satunya.
        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Kirim ulang bukti pendaftaran');
    }

    #[Test]
    public function membuka_alamatnya_langsung_tetap_menampilkan_halaman_penuh(): void
    {
        /*
         * Mode ringkas hanya berlaku untuk kedatangan ITU. Dibuat menetap,
         * orang yang memang belum membayar kehilangan satu-satunya jalan
         * membayar — dan tautan di emailnya menuju halaman yang sama.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Cara membayar', $isi);
        $this->assertStringContainsString('2164 0100 0467 563', $isi);
        $this->assertMatchesRegularExpression('/<input[^>]*type="file"/', $isi);
    }

    #[Test]
    public function kepala_layar_ringkas_terpusat_dengan_sub_judul_di_bawahnya(): void
    {
        /*
         * Di layar penuh kalimatnya menemani judul di baris yang sama supaya
         * hemat tinggi — di sana ia pengantar menuju langkah berikutnya. Di
         * layar ringkas tidak ada langkah berikutnya, yang ada cuma kabar,
         * jadi kalimatnya berdiri sebagai sub judul di bawah judulnya.
         */
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));
        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        /*
         * Dicocokkan ATRIBUT class unsurnya, bukan kata mentah di seluruh
         * halaman: nama kelas yang sama juga tertulis sebagai pemilih di
         * dalam blok <style> halaman ini.
         */
        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        preg_match('/<div class="(sta-kepala[^"]*)"/', $isi, $cocok);

        $this->assertStringContainsString('sta-kepala-tengah', $cocok[1] ?? '',
            'Judulnya tidak dipusatkan di layar ringkas.');
    }

    #[Test]
    public function kepala_layar_penuh_tidak_ikut_terpusat(): void
    {
        $sesi = $this->sesi(['total_kuota' => '20', 'sisa_kuota' => '20']);

        $this->post(route('public.webinareksklusif.store'), $this->borang($sesi));

        $p = WebinarEksklusifPendaftaran::where('email', 'berto@contoh.test')->firstOrFail();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        preg_match('/<div class="(sta-kepala[^"]*)"/', $isi, $cocok);

        $this->assertStringNotContainsString('sta-kepala-tengah', $cocok[1] ?? '',
            'Layar penuh ikut dipusatkan; kalimatnya jadi turun dan menambah tinggi.');
    }

    #[Test]
    public function sub_judul_layar_ringkas_tidak_dipotong_lebar(): void
    {
        /*
         * Yang memaksa kalimatnya turun ke baris sendiri flex-basis 100% —
         * dan basis itu hanya berlaku kalau tidak ada yang memotongnya.
         *
         * Percobaan pertama memberinya max-width: 68ch untuk menjaga panjang
         * barisnya. Terukur: lebarnya jadi 601px, dan 52 (ikon) + 343 (judul)
         * + 601 masih muat di baris selebar 1.294px — jadi ia TIDAK PERNAH
         * membungkus, dan kalimatnya tetap duduk di samping judul. Tidak ada
         * galat, tidak ada yang terlihat rusak; cuma tata letaknya diam-diam
         * tidak berubah.
         */
        $sumber = file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        );

        $aturan = preg_replace('#/\*.*?\*/#s', '', $sumber);
        $aturan = preg_replace('#\{\{--.*?--\}\}#s', '', (string) $aturan);

        $ada = preg_match('/\.sta-kepala-tengah\s*>\s*\.sta-sub\s*\{(?<isi>[^}]*)\}/', (string) $aturan, $cocok);

        $this->assertSame(1, $ada, 'Aturan sub judul layar ringkas hilang.');

        $this->assertStringContainsString('flex: 1 0 100%', $cocok['isi'],
            'Sub judulnya tidak lagi dipaksa turun ke baris sendiri.');

        $this->assertStringNotContainsString('max-width', $cocok['isi'],
            'Lebarnya dipotong lagi; sub judulnya akan kembali duduk di samping judul.');
    }

    #[Test]
    public function jam_pasirnya_berputar_hanya_selama_masih_menunggu(): void
    {
        /*
         * Ikon kepala halaman ini dipakai ulang untuk EMPAT keadaan: jam pasir
         * (belum dibayar), centang (lunas / bukti masuk), dan silang (batal,
         * kedaluwarsa). Animasinya menempel di kelas penanda, bukan di
         * .sta-ubin — kalau dipasang di .sta-ubin, centang "sudah lunas" ikut
         * berputar-putar, dan keadaan yang sudah SELESAI jadi terbaca seperti
         * masih dikerjakan.
         *
         * Yang dijaga: penandanya ADA saat menunggu dan TIDAK ADA saat lunas.
         *
         * Dicocokkan ke atribut class elemennya, bukan ke nama kelas di mana
         * pun di halaman — aturan CSS-nya sendiri menyebut nama yang sama di
         * dalam <style>, jadi pencarian polos selalu hijau walau markahnya
         * sudah kehilangan kelas itu.
         */
        $sesi = $this->sesi();

        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id, 'nama' => 'Jam Pasir', 'email' => 'jampasir@contoh.test',
            'telp' => '08123457777', 'jumlah_pendaftar' => 1, 'setuju' => '1',
        ]);

        $p = WebinarEksklusifPendaftaran::where('email', 'jampasir@contoh.test')->firstOrFail();

        $menunggu = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $ada = preg_match('/<span class="(?<kelas>sta-ubin[^"]*)"/', $menunggu, $cocok);

        $this->assertSame(1, $ada, 'Ubin ikon kepala halamannya hilang.');

        $this->assertStringContainsString('sta-ubin-menanti', $cocok['kelas'],
            'Jam pasirnya tidak lagi ditandai; ikonnya akan diam padahal hitung mundurnya berdetak.');

        $this->assertStringContainsString('fa-hourglass-half', $menunggu,
            'Ikon jam pasirnya hilang dari keadaan menunggu.');

        $p->update(['status' => 'paid']);

        $lunas = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $ada = preg_match('/<span class="(?<kelas>sta-ubin[^"]*)"/', $lunas, $cocok);

        $this->assertSame(1, $ada, 'Ubin ikon kepala halamannya hilang di keadaan lunas.');

        $this->assertStringNotContainsString('sta-ubin-menanti', $cocok['kelas'],
            'Centang "sudah lunas" ikut berputar; keadaan yang sudah selesai terbaca masih berjalan.');

        /*
         * Penanda di markah saja TIDAK CUKUP, dan lubang ini terbukti nyata:
         * memindahkan animasinya dari ".sta-ubin-menanti > .fas" ke
         * ".sta-ubin > .fas" membuat centang lunas ikut berputar sementara
         * markahnya tetap benar — dua pernyataan di atas hijau semua.
         *
         * Jadi PEMILIHNYA ikut dijaga: animasinya wajib menggantung di
         * penanda, dan .sta-ubin polos wajib bersih dari animasi.
         */
        $aturan = preg_replace('#/\*.*?\*/#s', '', file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        ));
        $aturan = (string) preg_replace('#\{\{--.*?--\}\}#s', '', (string) $aturan);

        $ada = preg_match('/\.sta-ubin-menanti\s*>\s*\.fas\s*\{(?<isi>[^}]*)\}/', $aturan, $pemilih);

        $this->assertSame(1, $ada, 'Aturan animasi jam pasirnya hilang.');

        $this->assertStringContainsString('animation: sta-jam-balik', $pemilih['isi'],
            'Gerak baliknya tidak lagi dipasang di penanda jam pasir.');

        $this->assertSame(0, preg_match('/\.sta-ubin\s*>\s*\.fas\s*\{[^}]*animation/', $aturan),
            'Animasinya dipasang di .sta-ubin polos; centang lunas dan silang batal ikut berputar.');
    }

    #[Test]
    public function putaran_jam_pasirnya_searah_dan_bisa_dimatikan(): void
    {
        /*
         * Dua hal yang tidak terlihat dari markah dan tidak pernah menerbitkan
         * galat kalau rusak.
         *
         * SEARAH. Jam pasir dibalik SATU kali tiap giliran pasir. Kalau
         * sudutnya pulang 180 -> 0, mata membacanya sebagai dibalik dua kali
         * untuk satu giliran — salah secara benda, walau gambarnya sama.
         * Karena itu putarannya ditutup di 360, bukan dikembalikan ke 0.
         * Terukur di peramban: 0 0 0 0 0 0 177 180 180 180 180 180 333 0.
         *
         * BISA DIMATIKAN. Gerakan berulang tanpa henti memicu rasa mual bagi
         * sebagian orang, dan setelan peramban mereka sudah menyatakannya.
         * Tanpa blok ini, pernyataan itu diabaikan tanpa tanda apa pun.
         */
        $sumber = file_get_contents(
            resource_path('views/public/webinar_eksklusif/status.blade.php')
        );

        $aturan = preg_replace('#/\*.*?\*/#s', '', $sumber);
        $aturan = preg_replace('#\{\{--.*?--\}\}#s', '', (string) $aturan);

        $ada = preg_match('/@keyframes\s+sta-jam-balik\s*\{(?<isi>.*?)\}\s*\n\s*\n/s', (string) $aturan, $cocok);

        $this->assertSame(1, $ada, 'Gerak baliknya hilang; jam pasirnya akan diam.');

        $this->assertStringContainsString('rotate(360deg)', $cocok['isi'],
            'Putarannya tidak lagi ditutup di 360; ia akan pulang ke 0 dan terbaca dibalik dua kali.');

        $mati = preg_match(
            '/@media \(prefers-reduced-motion: reduce\) \{(?<isi>[^}]*sta-ubin-menanti[^}]*)\}/s',
            (string) $aturan,
            $cocokMati
        );

        $this->assertSame(1, $mati,
            'Animasi jam pasirnya tidak lagi dimatikan untuk yang minta gerakan dikurangi.');

        $this->assertStringContainsString('animation: none', $cocokMati['isi'],
            'Blok gerakan-dikurangi ada tetapi tidak benar-benar menghentikan animasinya.');
    }
}
