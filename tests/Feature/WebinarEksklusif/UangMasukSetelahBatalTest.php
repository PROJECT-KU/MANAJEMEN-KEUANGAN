<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifLunasMail;
use App\PendaftaranJejak;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\MenyiapkanGerbangBayar;
use Tests\TestCase;

/**
 * Uang yang tiba SESUDAH pendaftarannya dibatalkan.
 *
 * Membatalkan di layar panitia tidak memberi tahu DOKU apa pun: tagihannya
 * di sana tetap hidup sampai tenggatnya habis, dan pendaftar yang tabnya
 * masih terbuka bisa tetap membayar. Cancel Order API DOKU pun tidak
 * menutup semuanya — hanya Bank Transfer dan QRIS yang bisa dibatalkan,
 * kartu dan e-wallet tidak. Jadi keadaan ini TIDAK bisa dihilangkan, hanya
 * bisa ditangani.
 *
 * Sampai 18 Okt 2026 ia tidak ditangani sama sekali. Terukur, dari satu
 * pembayaran: status cancel → paid, sisa kuota TIDAK berkurang (kursinya
 * sudah dikembalikan saat dibatalkan dan tidak pernah diambil lagi), dan
 * surat "Anda sudah lunas" berangkat ke orang yang pendaftarannya sudah
 * dibatalkan panitia. Tiga kerusakan, tidak satu pun bergejala.
 *
 * Keputusan pemiliknya: TERIMA kalau kursinya masih ada, TOLAK dan tandai
 * untuk dikembalikan kalau sudah habis.
 */
class UangMasukSetelahBatalTest extends TestCase
{
    use DatabaseTransactions;
    use MenyiapkanGerbangBayar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanGerbangBayar();
        Mail::fake();
    }

    private function sesi(int $kuota = 20): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Batal ' . Str::random(5),
            'nama_ke' => (string) random_int(3000, 3999),
            'mulai' => now()->addDays(9)->toDateString(),
            'selesai' => now()->addDays(9)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => (string) $kuota, 'sisa_kuota' => (string) $kuota,
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    private function daftar(KategoriLayanan $sesi): WebinarEksklusifPendaftaran
    {
        $this->post(route('public.webinareksklusif.store'), [
            'kategori_id' => $sesi->id,
            'nama' => 'Pendaftar Batal',
            'email' => 'batal' . Str::random(5) . '@contoh.test',
            'telp' => '0812-3456-' . random_int(1000, 9999),
            'jumlah_pendaftar' => 1, 'peserta' => [], 'setuju' => '1',
        ]);

        return WebinarEksklusifPendaftaran::where('kategori_id', $sesi->id)->firstOrFail();
    }

    private function batalkanLewatLayarPanitia(WebinarEksklusifPendaftaran $p): void
    {
        app(\App\Actions\Pendaftaran\UbahStatusPendaftaran::class)
            ->jalankan('webinar_eksklusif', (string) $p->getKey(), 'cancel', 'admin uji');
    }

    /**
     * Pemberitahuan bertanda tangan sah, persis seperti kiriman DOKU.
     *
     * Ditandatangani dengan aturan yang sama seperti di Doku, jadi uji ini
     * ikut menjaga aturan itu.
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

    #[Test]
    public function kursinya_masih_ada_maka_pembayarannya_diterima(): void
    {
        $sesi = $this->sesi(20);
        $p = $this->daftar($sesi);

        $this->assertSame('19', (string) $sesi->fresh()->sisa_kuota);

        $this->batalkanLewatLayarPanitia($p);

        $this->assertSame('cancel', $p->fresh()->status);
        $this->assertSame('20', (string) $sesi->fresh()->sisa_kuota, 'kursinya dikembalikan saat dibatalkan');

        $this->kirimPemberitahuan((string) $p->id_transaksi, 'SUCCESS')->assertOk();

        $this->assertSame('paid', $p->fresh()->status);

        /*
         * INI yang dulu bocor. Statusnya memang berpindah ke 'paid', tetapi
         * kursinya TIDAK pernah diambil lagi — jadi ada pendaftaran lunas
         * yang tidak memegang tempat, dan angkatannya bisa terjual melebihi
         * kapasitasnya.
         */
        $this->assertSame('19', (string) $sesi->fresh()->sisa_kuota,
            'Pembayarannya diterima tetapi kursinya tidak diambil kembali; '
            . 'angkatan ini sekarang punya satu kursi hantu.');

        $this->assertNull($p->fresh()->perlu_dikembalikan_pada,
            'Ditandai perlu dikembalikan padahal kursinya dapat dan statusnya lunas.');

        Mail::assertSent(WebinarEksklusifLunasMail::class);
    }

    #[Test]
    public function kursinya_habis_maka_tetap_batal_dan_ditandai_untuk_dikembalikan(): void
    {
        $sesi = $this->sesi(1);
        $p = $this->daftar($sesi);

        $this->batalkanLewatLayarPanitia($p);

        // Kursinya yang kembali itu langsung diambil orang lain.
        $sesi->forceFill(['sisa_kuota' => '0'])->save();

        Mail::fake();

        $this->kirimPemberitahuan((string) $p->id_transaksi, 'SUCCESS')->assertOk();

        $segar = $p->fresh();

        $this->assertSame('cancel', $segar->status,
            'Pendaftarannya dihidupkan kembali padahal kursinya sudah diberikan '
            . 'kepada orang lain; bentrokannya baru ketahuan di hari acaranya.');

        $this->assertSame('0', (string) $sesi->fresh()->sisa_kuota,
            'Kuotanya ditarik di bawah nol — satu kursi yang tidak pernah ada.');

        $this->assertNotNull($segar->perlu_dikembalikan_pada,
            'Uangnya masuk tetapi tidak ditandai di mana pun. Panitia tidak punya '
            . 'satu pun cara mengetahui ada yang harus dikembalikan.');

        Mail::assertNotSent(WebinarEksklusifLunasMail::class);
    }

    #[Test]
    public function keduanya_meninggalkan_jejak_atas_nama_gerbang(): void
    {
        /*
         * Panitia membaca "Jejak perubahan", bukan log peladen. Tanpa baris
         * di sini, satu-satunya tanda bahwa uang pernah masuk adalah kolom
         * yang tidak pernah dilihat siapa pun.
         *
         * Atas nama gerbangnya, bukan admin yang kebetulan sedang masuk:
         * yang melakukannya memang bukan orang.
         */
        $sesi = $this->sesi(20);
        $p = $this->daftar($sesi);

        $this->batalkanLewatLayarPanitia($p);
        $this->kirimPemberitahuan((string) $p->id_transaksi, 'SUCCESS')->assertOk();

        $jejak = PendaftaranJejak::milik('webinar_eksklusif', (string) $p->getKey())
            ->where('aksi', 'like', 'bayar-batal%')
            ->first();

        $this->assertNotNull($jejak, 'Uang masuk sesudah batal tidak meninggalkan jejak apa pun.');
        $this->assertSame('Gerbang pembayaran (DOKU)', $jejak->oleh_nama);
    }

    #[Test]
    public function layar_panitia_menyuarakan_uang_yang_perlu_dikembalikan(): void
    {
        /*
         * Keadaannya tidak bisa dibaca dari status maupun nominal — barisnya
         * tetap bertanda batal, persis seperti batal yang tidak pernah
         * dibayar. Kalau layarnya tidak mengatakannya, tidak ada yang tahu.
         *
         * Layarnya BENAR-BENAR DIBUKA, tidak dipindai sumbernya.
         *
         * Versi pertama penjaga ini mencari untaian "perlu_dikembalikan_pada"
         * di dalam berkas Blade-nya — dan tetap HIJAU ketika @if-nya saya
         * ganti jadi @if (false), sebab nama kolomnya masih tertulis di badan
         * notanya. Penjaga yang hijau saat cacatnya dipasang tidak menjaga
         * apa pun.
         */
        $panitia = User::create([
            'full_name' => 'Rina Panitia',
            'username' => 'batal_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $panitia->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        $sesi = $this->sesi(1);
        $p = $this->daftar($sesi);

        $this->batalkanLewatLayarPanitia($p);
        $sesi->forceFill(['sisa_kuota' => '0'])->save();

        $this->kirimPemberitahuan((string) $p->id_transaksi, 'SUCCESS')->assertOk();

        $this->assertNotNull($p->fresh()->perlu_dikembalikan_pada, 'prasyarat');

        \App\Support\PendaftaranSemuaLayanan::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $this->actingAs($panitia)
            ->get(route('account.pendaftaran-layanan.rincian', [
                'webinar_eksklusif', $p->getKey(),
            ]))
            ->assertOk()
            ->assertSee('Uangnya perlu dikembalikan', false);
    }

    #[Test]
    public function layar_panitia_tidak_menyuarakannya_pada_yang_biasa(): void
    {
        /*
         * Sisi sebaliknya, dan ia yang membuat penjaga di atas berarti: nota
         * merah yang muncul di SEMUA pendaftaran tidak menandai apa pun.
         */
        $panitia = User::create([
            'full_name' => 'Rina Panitia',
            'username' => 'biasa_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $panitia->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        $p = $this->daftar($this->sesi());

        \App\Support\PendaftaranSemuaLayanan::lupakan();
        KategoriLayanan::lupakanPendaftar();

        $this->actingAs($panitia)
            ->get(route('account.pendaftaran-layanan.rincian', [
                'webinar_eksklusif', $p->getKey(),
            ]))
            ->assertOk()
            ->assertDontSee('Uangnya perlu dikembalikan', false);
    }

    #[Test]
    public function alamat_permintaan_tagihannya_disimpan(): void
    {
        /*
         * Cancel Order API DOKU mewajibkan payment.original_request_id —
         * nomor faktur saja tidak cukup. Selama nilainya dibuang sesudah
         * dipakai menandatangani, tagihan yang pendaftarannya dibatalkan
         * tidak akan pernah bisa ditutup dari sisi kita, dan keadaan yang
         * diuji di berkas ini akan terus terjadi untuk SEMUA kanal, bukan
         * hanya yang tidak didukung.
         */
        $p = $this->daftar($this->sesi());

        $this->assertNotEmpty($p->bayar_permintaan_id,
            'Request-Id tagihannya dibuang lagi; tagihan yang dibatalkan tidak '
            . 'akan pernah bisa ditutup lewat Cancel Order API.');
    }
}
