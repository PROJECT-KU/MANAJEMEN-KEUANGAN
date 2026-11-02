<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use App\Services\Doku;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Gerbang pembayaran yang SEDANG MATI, bukan yang menolak.
 *
 * ---------------------------------------------------------------------------
 * Kejadiannya
 * ---------------------------------------------------------------------------
 *
 * 3 Nov 2026, pada pendaftaran sungguhan: menekan "Lanjut ke pembayaran"
 * tidak mengantar ke DOKU melainkan memantulkan orangnya ke halaman status
 * dengan "Pembayaran daring sedang bermasalah … hubungi panitia".
 *
 * Sebabnya BUKAN kode ini. DOKU sandbox menjawab
 *
 *     HTTP 503 Service Temporarily Unavailable
 *     <hr><center>alb</center>
 *
 * — halaman galat dari penyeimbang beban (AWS ALB) milik mereka. Diperiksa
 * langsung pada saat yang sama: api-sandbox.doku.com menjawab 503,
 * api.doku.com menjawab 400 (hidup normal).
 *
 * ---------------------------------------------------------------------------
 * Tiga hal yang memang salah di pihak kita
 * ---------------------------------------------------------------------------
 *
 *   1. tidak ada percobaan kedua sama sekali — satu 503 sesaat langsung
 *      berakhir sebagai pendaftaran tanpa tagihan;
 *   2. 5xx dan 4xx memulangkan kalimat yang SAMA, padahal yang harus
 *      dikerjakan orangnya berbeda: menunggu, versus menghubungi panitia;
 *   3. catatan panitianya menumpuk — kalimat yang sama tertulis dua kali
 *      sesudah dua percobaan.
 */
class GerbangMatiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-MATI');
        config()->set('services.doku.secret_key', 'SK-UJI-MATI');

        Mail::fake();
    }

    private function angkatan(): CategoriesOnlineTraining
    {
        return CategoriesOnlineTraining::create([
            'token' => Str::random(30),
            'nama' => 'UJI Gerbang Mati ' . Str::random(6),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(),
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 3500000, 'status' => 'active',
        ]);
    }

    private function daftar(CategoriesOnlineTraining $a): ?PendaftaranOnlineTraining
    {
        $sebelum = PendaftaranOnlineTraining::where('kategori_id', $a->getKey())
            ->pluck('id')->map(fn ($k) => (string) $k)->all();

        $this->post(route('public.onlinetraining.store'), [
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Gerbang',
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ]);

        return PendaftaranOnlineTraining::where('kategori_id', $a->getKey())
            ->whereNotIn('id', $sebelum !== [] ? $sebelum : ['-'])
            ->first();
    }

    // ------------------------------------------------------- percobaan kedua

    #[Test]
    public function lima_ratus_tiga_sesaat_dicoba_lagi_dan_berhasil(): void
    {
        /*
         * 503 datang dari penyeimbang beban DOKU, jadi permintaannya tidak
         * pernah sampai ke aplikasi mereka — tidak ada tagihan yang
         * terlanjur terbit, jadi mengulangnya aman. Satu kedipan sesaat
         * tidak boleh berakhir sebagai pendaftaran tanpa tagihan.
         */
        Http::fakeSequence()
            ->push('<html><body>503</body></html>', 503)
            ->push([
                'response' => [
                    'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/PULIH'],
                    'order' => ['invoice_number' => 'CO-PULIH'],
                ],
            ], 200);

        $p = $this->daftar($this->angkatan());

        $this->assertNotNull($p);
        $this->assertSame('menunggu', (string) $p->bayar_status);
        $this->assertSame('https://staging.doku.com/checkout-link-v2/PULIH', (string) $p->bayar_url);
    }

    #[Test]
    public function empat_ratus_tidak_dicoba_lagi(): void
    {
        /*
         * 4xx penolakan yang DISENGAJA — bentuk datanya salah, kuncinya
         * salah, atau nomornya sudah terpakai. Mengulang hal yang sama
         * menghasilkan penolakan yang sama, dan pada "INVOICE ALREADY USED"
         * ulangan justru berbahaya: ia menandakan tagihannya SUDAH ada.
         */
        Http::fake(['*' => Http::response(['error' => 'bad request'], 400)]);

        $p = $this->daftar($this->angkatan());

        $this->assertNotNull($p);
        $this->assertSame('gagal-dibuat', (string) $p->bayar_status);

        // Satu permintaan saja, bukan dua.
        $jumlah = 0;
        Http::recorded(function () use (&$jumlah) { $jumlah++; });

        $this->assertSame(1, $jumlah, '4xx tidak boleh diulang.');
    }

    // -------------------------------------------------------------- kalimat

    #[Test]
    public function kalimatnya_membedakan_gerbang_mati_dari_gerbang_menolak(): void
    {
        /*
         * Bedanya menentukan apa yang dikerjakan orangnya. "Hubungi panitia"
         * salah untuk 5xx: panitia tidak bisa berbuat apa-apa atas peladen
         * DOKU, dan yang sebenarnya perlu cuma menunggu beberapa menit.
         */
        Http::fake(['*' => Http::response('<html>503</html>', 503)]);

        // TANPA followingRedirects(): mengikuti alihannya memakai pesan
        // kilatnya, jadi session('error') sudah kosong saat diperiksa.
        $this->post(route('public.onlinetraining.store'), [
            'kategori_id' => $this->angkatan()->getKey(),
            'nama' => 'Pendaftar Gerbang',
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ]);

        $pesan = (string) session('error');

        $this->assertStringContainsString('di pihak mereka', $pesan);
        $this->assertStringNotContainsString('hubungi panitia', mb_strtolower($pesan));
    }

    #[Test]
    public function kalimat_gerbang_menolak_tetap_mengarahkan_ke_panitia(): void
    {
        Http::fake(['*' => Http::response(['error' => 'bad request'], 400)]);

        $this->post(route('public.onlinetraining.store'), [
            'kategori_id' => $this->angkatan()->getKey(),
            'nama' => 'Pendaftar Gerbang',
            'email' => Str::lower(Str::random(10)) . '@contoh.test',
            'telp' => '0813' . random_int(10000000, 99999999),
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ]);

        $this->assertStringContainsString('panitia', mb_strtolower((string) session('error')));
    }

    // --------------------------------------------------------------- catatan

    #[Test]
    public function catatan_panitia_tidak_menumpuk_tiap_percobaan(): void
    {
        /*
         * Terukur pada pendaftaran sungguhan sesudah dua percobaan: kolom
         * catatan panitia berisi kalimat yang sama dua kali, kata demi kata.
         */
        Http::fake(['*' => Http::response(['error' => 'bad request'], 400)]);

        $a = $this->angkatan();
        $p = $this->daftar($a);

        $this->assertNotNull($p);

        $sekali = (string) $p->refresh()->note;

        // Percobaan kedua, lewat tombol "Buat tagihan baru".
        $this->post(route('public.onlinetraining.bayarulang', $p->getKey()));

        $duaKali = (string) $p->refresh()->note;

        $this->assertSame($sekali, $duaKali,
            'Catatan panitia menumpuk tiap percobaan tagihan yang gagal.');
        $this->assertSame(1, substr_count($duaKali, 'Tagihan daring gagal dibuat'));
    }

    // ----------------------------------------------------- kursinya selamat

    #[Test]
    public function pendaftarannya_tetap_tersimpan_dan_kursinya_ditahan(): void
    {
        /*
         * Yang paling penting dan yang memang sudah benar sejak awal:
         * gagalnya tagihan TIDAK menghapus pendaftarannya. Orangnya sudah
         * mengisi borang dan kursinya sudah ditahan; membatalkannya berarti
         * ia mengulang dari nol karena kesalahan yang bukan miliknya.
         */
        Http::fake(['*' => Http::response('<html>503</html>', 503)]);

        $a = $this->angkatan(['total_kuota' => 10, 'sisa_kuota' => 10] + []);
        $p = $this->daftar($a);

        $this->assertNotNull($p);
        $this->assertSame(
            PendaftaranSemuaLayanan::statusAwal('online_training'),
            (string) $p->status
        );
        $this->assertSame(19, (int) $a->refresh()->sisa_kuota);

        // Dan tenggatnya tetap tercatat, jadi kursinya dilepas sendiri kalau
        // memang tidak jadi dibayar.
        $this->assertNotNull($p->kedaluwarsa_pada);
        $this->assertLessThanOrEqual(Doku::MENIT_KEDALUWARSA + 1,
            now()->diffInMinutes($p->kedaluwarsa_pada));
    }
}
