<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penjagaan keamanan alur kelas tatap muka: memilih kelas → bayar → hasilnya.
 *
 * Semua yang dijaga di sini terukur sebagai lubang 26 Okt 2026, dan tidak
 * satu pun mengeluarkan galat saat terbuka:
 *
 *   - borang pendaftaran tanpa pembatas laju: tiap kiriman menahan kursi,
 *     mengirim surat ke alamat yang ditentukan pengirimnya, dan membuat
 *     tagihan sungguhan di gerbang;
 *   - penanya keadaan pembayaran memicu satu panggilan keluar per
 *     permintaan, 1:1, tanpa pembatas;
 *   - penjaga pendaftaran ganda mengantar siapa pun yang tahu nama dan email
 *     orang lain ke halaman status milik orang itu;
 *   - pemberitahuan gerbang yang sah bisa dikirim ulang kapan saja;
 *   - pemeriksa kode diskon membedakan "angkatan ini tidak punya kode" dari
 *     "kode tidak cocok", dan tidak dibatasi lajunya.
 */
class KeamananAlurBayarTest extends TestCase
{
    use DatabaseTransactions;

    private const KLIEN = 'BRN-UJI-AMAN';

    private const RAHASIA = 'SK-UJI-AMAN';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Cache::flush();

        config()->set('services.doku.client_id', self::KLIEN);
        config()->set('services.doku.secret_key', self::RAHASIA);
    }

    private function angkatan(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp', 'token' => Str::random(30),
            'nama' => 'UJI Aman ' . Str::random(5), 'nama_ke' => (string) random_int(5000, 5999),
            'mulai' => now()->addDays(20)->toDateString(),
            'selesai' => now()->addDays(22)->toDateString(),
            'total_kuota' => 50, 'sisa_kuota' => 49,
            'biaya' => 5500000, 'status' => 'active',
        ], $ganti));
    }

    /** @return array<string, mixed> */
    private function isian(CategoriesScopusCamp $a, array $ganti = []): array
    {
        return array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Aman',
            'email' => 'aman' . Str::random(6) . '@contoh.test',
            'telp' => '081234567890',
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
        ], $ganti);
    }

    private function pendaftaran(CategoriesScopusCamp $a): PendaftaranScopusCamp
    {
        return PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-AMAN-' . Str::upper(Str::random(6)),
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Aman', 'email' => 'aman' . Str::random(5) . '@contoh.test',
            'telp' => '081234567890', 'jumlah_pendaftar' => 1,
            'total_pembayaran' => '5500000', 'cara_bayar' => 'doku',
            'status' => 'diproses', 'bayar_status' => 'menunggu',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);
    }

    // ------------------------------------------------------- pembatas laju

    #[Test]
    public function borang_pendaftaran_dibatasi_lajunya(): void
    {
        /*
         * Tiap kiriman menahan satu KURSI dari kuota yang terbatas, mengirim
         * surat ke alamat yang DITENTUKAN PENGIRIMNYA — jadi kotak masuk
         * siapa pun bisa dibanjiri dari domain kita — dan membuat TAGIHAN
         * SUNGGUHAN di gerbang pembayaran.
         *
         * Terukur: rutenya tidak punya pembatas apa pun, sementara borang
         * Webinar Eksklusif sudah lama punya.
         */
        $a = $this->angkatan();

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://staging.doku.com/x']],
        ], 200)]);

        $kena = false;

        for ($i = 0; $i < 9; $i++) {
            $jawab = $this->post(route('public.scopuscamp.store'), $this->isian($a));

            if ($jawab->status() === 429) {
                $kena = true;
                break;
            }
        }

        $this->assertTrue($kena,
            'Borang pendaftaran menerima sembilan kiriman beruntun tanpa menolak; '
            . 'satu skrip bisa menghabiskan kuota kursi dan membanjiri kotak masuk '
            . 'alamat mana pun dari domain kita.');
    }

    #[Test]
    public function penanya_keadaan_dibatasi_lajunya(): void
    {
        /*
         * Tiap permintaan ke sini memicu satu panggilan keluar ke gerbang.
         * Tanpa pembatas, siapa pun yang memegang satu UUID bisa
         * mengulangnya tanpa henti — menghabiskan kuota kita di gerbang
         * sekaligus kuota proses hosting yang memang sudah pernah berakhir
         * 503 karena poller.
         */
        $p = $this->pendaftaran($this->angkatan());

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $kena = false;

        for ($i = 0; $i < 40; $i++) {
            if ($this->get(route('public.scopuscamp.keadaan', $p->getKey()))->status() === 429) {
                $kena = true;
                break;
            }
        }

        $this->assertTrue($kena, 'Penanya keadaan tidak dibatasi lajunya.');
    }

    #[Test]
    public function jawaban_gerbang_disimpan_sebentar_agar_poll_tidak_jadi_panggilan(): void
    {
        /*
         * Halaman status menanyai keadaan tiap 5 detik sampai 40 kali, dan
         * tiap pertanyaan dulu jadi SATU panggilan keluar — terukur 1:1.
         * Satu tab berarti 40 panggilan dalam tiga menit; sepuluh orang yang
         * membayar bersamaan jadi 400.
         */
        $p = $this->pendaftaran($this->angkatan());

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        for ($i = 0; $i < 12; $i++) {
            $this->get(route('public.scopuscamp.keadaan', $p->getKey()))->assertOk();
        }

        $this->assertSame(1, count(Http::recorded()),
            'Tiap poll masih jadi satu panggilan keluar ke gerbang.');
    }

    // ------------------------------------------------- kebocoran tautannya

    #[Test]
    public function nama_dan_email_orang_lain_tidak_membuka_halaman_statusnya(): void
    {
        /*
         * LUBANG YANG PALING MUDAH DIPAKAI dari seluruh alur ini.
         *
         * Penjaga pendaftaran ganda mencocokkan angkatan + nama + email, lalu
         * MENGANTAR pengirimnya ke halaman status pendaftaran yang sudah ada.
         * Ketiganya bukan rahasia — nama dan email justru hal yang paling
         * mudah diketahui rekan sekantor atau siapa pun yang memegang daftar
         * peserta.
         *
         * Yang ia dapatkan: nomor telepon, afiliasi, daftar peserta
         * rombongan, faktur PDF, tombol kirim ulang, dan tombol membuat
         * tagihan. UUID halaman statusnya rahasia pembawa, dan cabang itu
         * menyerahkannya cuma-cuma.
         */
        $a = $this->angkatan();
        $isian = $this->isian($a);

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://staging.doku.com/x']],
        ], 200)]);

        $this->post(route('public.scopuscamp.store'), $isian);

        // Orang lain menebak nama dan emailnya.
        $jawab = $this->post(route('public.scopuscamp.store'), $isian);

        $this->assertStringNotContainsString('/Scopus-Camp/Status/',
            (string) $jawab->headers->get('location'),
            'Yang tahu nama dan email orang lain diantar ke halaman status milik '
            . 'orang itu.');
    }

    // ------------------------------------------------------ ulang-kirim

    #[Test]
    public function pemberitahuan_gerbang_tidak_bisa_dikirim_ulang(): void
    {
        /*
         * Tanda tangannya memang diperiksa, tetapi cap waktunya tidak pernah
         * DIBACA dan Request-Id-nya tidak pernah dicatat. Satu pemberitahuan
         * sah yang tertangkap — dari log proksi, dari peladen yang pernah
         * disusupi — bisa dikirim ulang kapan saja dan tetap diterima.
         *
         * Akibat yang paling jelas: pendaftaran yang dananya sudah
         * dikembalikan panitia bisa ditandai LUNAS lagi.
         */
        $p = $this->pendaftaran($this->angkatan());

        $isi = [
            'order' => ['invoice_number' => $p->id_transaksi],
            'transaction' => ['status' => 'SUCCESS'],
        ];

        $kepala = $this->kepalaSah($isi);

        $this->kirim($isi, $kepala)->assertOk();
        $this->assertSame('Pendaftaran Diterima', $p->refresh()->status);

        // Panitia mengembalikan dananya.
        $p->forceFill(['status' => 'Pendaftaran Refund'])->save();

        // Pemberitahuan yang SAMA PERSIS dikirim ulang.
        $this->kirim($isi, $kepala)->assertUnauthorized();

        $this->assertSame('Pendaftaran Refund', $p->refresh()->status,
            'Pemberitahuan lama yang dikirim ulang menandai lunas pendaftaran yang '
            . 'dananya sudah dikembalikan.');
    }

    #[Test]
    public function pemberitahuan_bercap_waktu_lama_ditolak(): void
    {
        /*
         * Lapis yang berdiri sendiri dari catatan Request-Id: tangkapan lama
         * tidak berguna walau identitasnya belum pernah terlihat di peladen
         * ini — termasuk sesudah singgahannya dibersihkan atau peladennya
         * dipindah.
         */
        $p = $this->pendaftaran($this->angkatan());

        $isi = [
            'order' => ['invoice_number' => $p->id_transaksi],
            'transaction' => ['status' => 'SUCCESS'],
        ];

        $kepala = $this->kepalaSah($isi, gmdate('Y-m-d\TH:i:s\Z', time() - 7200));

        $this->kirim($isi, $kepala)->assertUnauthorized();

        $this->assertSame('diproses', $p->refresh()->status,
            'Pemberitahuan berumur dua jam masih diterima.');
    }

    #[Test]
    public function pemberitahuan_sah_yang_baru_tetap_diterima(): void
    {
        /*
         * Sisi sebaliknya, dan ia perlu: gerbang BOLEH mengirim ulang
         * pemberitahuan yang sama saat jawaban kita tidak sampai, dan
         * pengulangan sah itu membawa Request-Id baru. Penjagaan di atas
         * tidak boleh ikut menolaknya — kalau iya, pembayaran yang sudah
         * masuk tidak pernah tercatat.
         */
        $p = $this->pendaftaran($this->angkatan());

        $isi = [
            'order' => ['invoice_number' => $p->id_transaksi],
            'transaction' => ['status' => 'SUCCESS'],
        ];

        $this->kirim($isi, $this->kepalaSah($isi))->assertOk();
        $this->kirim($isi, $this->kepalaSah($isi))->assertOk();

        $this->assertSame('Pendaftaran Diterima', $p->refresh()->status);
    }

    // ------------------------------------------------------ kode diskon

    #[Test]
    public function pemeriksa_kode_diskon_tidak_membocorkan_angkatan_mana_yang_punya(): void
    {
        /*
         * Jawabannya dulu membedakan "angkatan ini tidak punya kode" dari
         * "kode tidak cocok" — yang menyaring angkatan mana yang layak
         * ditebak sebelum satu kode pun dicoba. Rutenya pun tidak dibatasi
         * lajunya, jadi menebaknya gratis.
         */
        $tanpa = $this->angkatan();
        $dengan = $this->angkatan();
        $dengan->forceFill(['kode_diskon' => 'RAHASIA123', 'nominal_diskon' => '250000'])->save();

        $a = $this->getJson(route('public.scopuscamp.cekkodediskon', $tanpa->getKey())
            . '?kode_diskon=COBA')->json();

        $b = $this->getJson(route('public.scopuscamp.cekkodediskon', $dengan->getKey())
            . '?kode_diskon=COBA')->json();

        $this->assertSame($a, $b,
            'Jawabannya berbeda antara angkatan yang punya kode dan yang tidak; '
            . 'penebaknya tahu mana yang layak dicoba sebelum menebak satu kode pun.');

        // Kode yang benar tetap diterima.
        $this->getJson(route('public.scopuscamp.cekkodediskon', $dengan->getKey())
            . '?kode_diskon=RAHASIA123')
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    #[Test]
    public function kode_diskon_tidak_ditulis_ke_log(): void
    {
        /*
         * Tiap percobaan dulu tertulis apa adanya beserta kunci angkatannya,
         * jadi log aplikasi jadi daftar kode diskon yang berlaku — dan log
         * dibaca lebih banyak orang daripada basis datanya.
         */
        $sumber = (string) file_get_contents(base_path(
            'app/Http/Controllers/Publict/PublicScopusCampController.php'
        ));

        $awal = strpos($sumber, 'public function cekKodeDiskon');
        $this->assertNotFalse($awal);

        $potong = substr($sumber, $awal, 2200);

        $this->assertStringNotContainsString('Log::info', $potong,
            'Percobaan kode diskon ditulis ke log; lognya jadi daftar kode yang berlaku.');
    }

    // ------------------------------------------------------ kepala jawaban

    #[Test]
    public function tiap_jawaban_membawa_kepala_keamanannya(): void
    {
        /*
         * Terukur: aplikasi ini tidak mengirim satu pun kepala keamanan.
         *
         * Referrer-Policy yang paling penting di sini — alamat halaman status
         * memuat UUID yang menjaga keterangan satu orang, dan halaman itu
         * memuat tautan keluar. Peramban masa kini memang sudah ketat secara
         * bawaan, tetapi itu nilai bawaan PERAMBAN: berbeda antar peramban,
         * berubah antar versi. Rahasia tidak dititipkan pada nilai bawaan
         * pihak lain.
         */
        $p = $this->pendaftaran($this->angkatan());

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $jawab = $this->get(route('public.scopuscamp.status', $p->getKey()))->assertOk();

        $this->assertSame('strict-origin-when-cross-origin',
            $jawab->headers->get('Referrer-Policy'));
        $this->assertSame('SAMEORIGIN', $jawab->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $jawab->headers->get('X-Content-Type-Options'));

        /*
         * Halaman ini dan berkasnya dirakit untuk SATU orang: tidak boleh
         * disimpan mesin pencari maupun singgahan bersama. Meta noindex-nya
         * hanya terbaca pada HTML — faktur PDF-nya tidak, dan singgahan
         * bersama tidak membaca meta sama sekali.
         */
        $this->assertStringContainsString('noindex',
            (string) $jawab->headers->get('X-Robots-Tag'));
        $this->assertStringContainsString('no-store',
            (string) $jawab->headers->get('Cache-Control'));
    }

    #[Test]
    public function tautan_keluar_tidak_membawa_alamat_halamannya(): void
    {
        /*
         * Lapis kedua selain Referrer-Policy, dan ia tidak bergantung pada
         * kepala jawaban sama sekali: `noreferrer` pada tautannya sendiri.
         */
        $p = $this->pendaftaran($this->angkatan());
        $p->forceFill(['status' => 'Pendaftaran Diterima'])->save();

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'SUCCESS']], 200)]);

        $isi = $this->get(route('public.scopuscamp.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('rel="noopener"', $isi,
            'Ada tautan keluar yang masih membawa alamat halaman status sebagai '
            . 'rujukan ke situs lain.');
    }

    // ------------------------------------------------------------ pembantu

    /** @return array<string, string> */
    private function kepalaSah(array $isi, ?string $waktu = null): array
    {
        $jalur = '/Scopus-Camp/pemberitahuan/doku';
        $requestId = (string) Str::uuid();
        $waktu ??= gmdate('Y-m-d\TH:i:s\Z');
        $json = json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $bahan = implode("\n", [
            'Client-Id:' . self::KLIEN,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $waktu,
            'Request-Target:' . $jalur,
            'Digest:' . base64_encode(hash('sha256', $json, true)),
        ]);

        return [
            'HTTP_Client-Id' => self::KLIEN,
            'HTTP_Request-Id' => $requestId,
            'HTTP_Request-Timestamp' => $waktu,
            'HTTP_Signature' => 'HMACSHA256=' . base64_encode(
                hash_hmac('sha256', $bahan, self::RAHASIA, true)
            ),
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    private function kirim(array $isi, array $kepala)
    {
        return $this->call('POST', '/Scopus-Camp/pemberitahuan/doku', [], [], [], $kepala,
            json_encode($isi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
