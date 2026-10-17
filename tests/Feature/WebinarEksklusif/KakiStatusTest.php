<?php

namespace Tests\Feature\WebinarEksklusif;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\MenyiapkanGerbangBayar;
use Tests\TestCase;

/**
 * Tata letak halaman status: yang kecil-kecil tidak boleh tercecer.
 *
 * Halaman ini punya dua lajur di layar lebar, dan tingginya pernah timpang
 * jauh — terukur lajur kanan 611px sementara lajur kiri berhenti di 341px.
 * Separuh bawah sisi kiri menganga kosong, garis pemisahnya berhenti di
 * udara, dan tiga hal kecil (tombol kirim ulang, tautan sesi lain, kalimat
 * "simpan halaman ini") melayang sendiri-sendiri di bawahnya.
 *
 * Yang dijaga di sini BUKAN selera, melainkan letak yang menentukan
 * keseimbangan itu. Aturan rupanya sendiri ada di CSS dan tidak bisa
 * dijalankan dari sini, jadi dua di antaranya dijaga dari sumbernya.
 */
class KakiStatusTest extends TestCase
{
    use DatabaseTransactions;
    use MenyiapkanGerbangBayar;

    private const TAMPILAN = 'resources/views/public/webinar_eksklusif/status.blade.php';

    protected function setUp(): void
    {
        parent::setUp();

        $this->siapkanGerbangBayar();
        Mail::fake();
        Http::fake(['*/orders/v1/status/*' => Http::response([], 404)]);
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Kaki ' . Str::random(5),
            'nama_ke' => (string) random_int(6000, 6999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** @param array<string, mixed> $ubah */
    private function pendaftaran(array $ubah = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'id_transaksi' => 'WE-KAKI-' . Str::random(5),
            'kategori_id' => $this->sesi()->getKey(),
            'nama' => 'Pendaftar Kaki',
            'email' => 'kaki' . Str::random(5) . '@contoh.test',
            'telp' => '6281234567890',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129515',
            'cara_bayar' => 'doku',
            'bayar_url' => 'https://bayar.uji.test/kaki',
            'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHour(),
        ], $ubah));
    }

    private function sumberTampilan(): string
    {
        return (string) file_get_contents(base_path(self::TAMPILAN));
    }

    #[Test]
    public function tombol_kirim_ulang_berdiri_di_kaki_bukan_di_dalam_lajur(): void
    {
        /*
         * Dulu tombol ini menggantung di DASAR LAJUR KANAN, dan tingginya
         * langsung masuk hitungan tinggi lajur itu — ia sendiri yang
         * menyumbang 60px dari selisih tinggi kedua lajurnya.
         *
         * Dijaga lewat URUTAN di markah: kalau tombolnya dikembalikan ke
         * dalam lajur, ia akan tergambar SEBELUM kakinya, bukan sesudah.
         */
        $p = $this->pendaftaran();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Kirim ulang bukti pendaftaran ke email saya')
            ->getContent();

        $kaki = strpos((string) $isi, 'sta-kaki');
        $tombol = strpos((string) $isi, 'sta-kirim-ulang');

        $this->assertNotFalse($kaki, 'Kaki halamannya tidak tergambar sama sekali.');
        $this->assertNotFalse($tombol, 'Tombol kirim ulangnya tidak tergambar.');
        $this->assertGreaterThan(
            $kaki,
            $tombol,
            'Tombol kirim ulang kembali berdiri di dalam lajur, bukan di kaki halaman — '
            . 'lajurnya memanjang lagi dan tombolnya menggantung sendirian di bawahnya.'
        );
    }

    #[Test]
    public function yang_kedaluwarsa_tidak_ditawari_kirim_ulang(): void
    {
        /*
         * Syarat keduanya memang berlawanan, dan dulu ditulis dua kali di
         * dua tempat yang berjauhan: @if (! $batal && ! $habis) di dalam
         * lajur kanan, dan @if ($batal || $habis) jauh di bawahnya. Dua
         * salinan satu aturan adalah dua kesempatan untuk berbeda.
         */
        $p = $this->pendaftaran(['status' => 'expired']);

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Lihat sesi yang dibuka')
            ->assertDontSee('Kirim ulang bukti pendaftaran ke email saya');
    }

    #[Test]
    public function tawaran_akun_duduk_di_lajur_kiri(): void
    {
        /*
         * Di lajur kanan ia menumpuk di bawah kabar pembayaran dan membuat
         * sisi kanan penuh sesak sementara sisi kiri menganga. Di lajur
         * kiri ia mengisi ruang itu.
         *
         * Lajur kiri digambar lebih dulu di markah, jadi letaknya terbaca
         * dari urutan: tawaran akunnya harus muncul SEBELUM kartu menunggu.
         */
        $p = $this->pendaftaran();

        $isi = (string) $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Mau lebih mudah lain kali?')
            ->getContent();

        $tawar = strpos($isi, 'sta-tawar-akun');
        $tunggu = strpos($isi, 'sta-tunggu');

        $this->assertNotFalse($tawar);
        $this->assertNotFalse($tunggu);
        $this->assertLessThan(
            $tunggu,
            $tawar,
            'Tawaran akun kembali ke lajur kanan; lajur kirinya kosong separuh lagi.'
        );
    }

    #[Test]
    public function di_ponsel_tawaran_akun_turun_di_bawah_kartu_menunggu(): void
    {
        /*
         * Konsekuensi dari uji di atas: begitu tawaran akunnya pindah ke
         * lajur kiri, di ponsel — tempat kedua lajurnya menumpuk — ia
         * mendarat DI ANTARA rincian dan kartu menunggu. Iklan yang boleh
         * dilewati berdiri di depan satu-satunya kartu yang berubah sendiri
         * saat pembayarannya masuk.
         *
         * Yang menahannya cuma dua aturan CSS, dan CSS tidak bisa
         * dijalankan dari sini — jadi keberadaannya yang dijaga. Keduanya
         * saling bergantung: display:contents yang menaikkan isi lajur jadi
         * anak langsung kisinya, dan order yang memindahkannya ke bawah.
         */
        $sumber = $this->sumberTampilan();

        $this->assertStringContainsString(
            '.sta-kisi > .sta-lajur { display: contents; }',
            $sumber,
            'Pembungkus lajurnya tidak lagi dilarutkan di ponsel, jadi urutan '
            . 'tumpukannya tidak bisa diatur dan tawaran akun naik ke depan '
            . 'kartu menunggu.'
        );

        $this->assertStringContainsString(
            '.sta-kisi .sta-tawar-akun { order: 2; }',
            $sumber,
            'Tawaran akun tidak lagi didorong ke bawah di ponsel; ia berdiri '
            . 'di depan kartu yang menunggu kabar pembayaran.'
        );
    }

    #[Test]
    public function garis_pemisahnya_menjangkau_dasar_kisi(): void
    {
        /*
         * Garis pemisah dua lajurnya digambar sebagai border kanan lajur
         * kiri. Tanpa align-self, kotak lajur kiri berhenti tepat di dasar
         * isinya — terukur 341px — sementara kisinya 611px, jadi garisnya
         * ikut berhenti 270px di udara dan halamannya terbaca seperti belum
         * selesai digambar.
         */
        $this->assertMatchesRegularExpression(
            '/\.sta-kisi:not\(\.sta-kisi-tunggal\) > \.sta-lajur-kiri \{[^}]*align-self: stretch;/s',
            $this->sumberTampilan(),
            'Lajur kiri tidak lagi diregangkan; garis pemisahnya berhenti di udara.'
        );
    }
}
