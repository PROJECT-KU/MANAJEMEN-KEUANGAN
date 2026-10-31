<?php

namespace Tests\Feature\ScopusKafe;

use App\ClinikScopusBiayaPersesi;
use App\PendaftaranScopusKafe;
use App\Support\HargaKafe;
use App\Support\PendaftaranSemuaLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Scopus Kafe mengambil sesi dan harganya dari layar Tarif Layanan.
 *
 * Sampai 31 Okt 2026 layanan ini tidak mengenal varian sama sekali: sesinya
 * dibaca dari tabel `meme` (layar "Data Meme"), SATU daftar untuk semua
 * orang, dan harganya ikut kolom `biaya` di sana. Padahal layanannya berjalan
 * dalam dua bentuk — tatap muka dan daring — dengan jam dan harga yang
 * berbeda, dan keduanya sudah lama disetel panitia di Tarif Layanan.
 *
 * Akibatnya yang daring membayar harga tatap muka, dan jam yang dipajang
 * cuma jam salah satunya.
 */
class VarianTarifTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('services.doku.client_id', 'BRN-UJI-VARIAN');
        config()->set('services.doku.secret_key', 'SK-UJI-VARIAN');

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/V']],
        ], 200)]);

        /*
         * Tarif Kafe yang sungguhan dinonaktifkan selama uji ini. Panitia
         * boleh mengubahnya kapan saja — dibiarkan hidup, angka yang
         * dibandingkan di bawah ikut berubah dan ujinya merah tanpa ada yang
         * salah di kodenya.
         */
        ClinikScopusBiayaPersesi::where('layanan', 'scopus_kafe')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $this->tarif('offline', '1.000.000', [
            ['nama' => 'sesi pagi', 'mulai' => '08:00', 'selesai' => '13:00'],
            ['nama' => 'sesi sore', 'mulai' => '13:00', 'selesai' => '18:00'],
        ]);

        $this->tarif('online', '600.000', [
            ['nama' => 'sesi malam', 'mulai' => '19:00', 'selesai' => '21:00'],
        ]);
    }

    private function tarif(string $varian, string $biaya, array $sesi): ClinikScopusBiayaPersesi
    {
        $baris = ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_kafe',
            'varian' => $varian,
            'biaya_persesi' => $biaya,
            'status' => ClinikScopusBiayaPersesi::AKTIF,
            'sesi' => $sesi,
        ]);

        // Keduanya disimpan di ingatan selama satu permintaan.
        PendaftaranSemuaLayanan::lupakanSesi();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        return $baris;
    }

    private function jumatBerikutnya(): Carbon
    {
        $hari = now()->startOfDay();

        while (! HargaKafe::hariBuka($hari)) {
            $hari = $hari->addDay();
        }

        return $hari;
    }

    private function isian(string $varian, array $sesi): array
    {
        return [
            'nama' => 'Pemesan Varian',
            'email' => 'varian' . $varian . '@contoh.test',
            'telp' => '081234567890',
            'tanggal_pemesanan' => $this->jumatBerikutnya()->toDateString(),
            'varian' => $varian,
            'sesi' => $sesi,
            'setuju' => '1',
        ];
    }

    #[Test]
    public function tiap_varian_punya_sesi_dan_harganya_sendiri(): void
    {
        $this->assertSame(
            ['sesi pagi', 'sesi sore'],
            array_column(HargaKafe::sesiTersedia('offline'), 'nama'));

        $this->assertSame(
            ['sesi malam'],
            array_column(HargaKafe::sesiTersedia('online'), 'nama'));

        $this->assertSame(1000000, HargaKafe::hitung('offline', ['sesi pagi'])['subtotal']);
        $this->assertSame(600000, HargaKafe::hitung('online', ['sesi malam'])['subtotal']);
    }

    #[Test]
    public function sesi_milik_varian_lain_tidak_bisa_dibeli_dengan_harga_varian_ini(): void
    {
        /*
         * Inilah lubangnya kalau katalognya satu untuk semua: pilih daring
         * yang Rp 600.000, lalu kirimkan juga kunci sesi tatap muka. Dengan
         * katalog bersama, dua sesi tertagih 1,2 juta padahal yang satu
         * berharga sejuta — atau lebih buruk, sesi tatap muka didapat dengan
         * harga daring.
         */
        $harga = HargaKafe::hitung('online', ['sesi malam', 'sesi pagi', 'sesi sore']);

        $this->assertCount(1, $harga['sesi'],
            'Sesi milik varian lain ikut terbeli.');

        $this->assertSame(600000, $harga['subtotal']);
    }

    #[Test]
    public function varian_karangan_tidak_menghasilkan_pemesanan(): void
    {
        /*
         * Varian yang tidak ditawarkan harus memulangkan pemesanan KOSONG,
         * bukan pemesanan berharga nol: berharga nol, kiriman dengan varian
         * karangan tersimpan lengkap dengan tagihan DOKU senilai kode
         * uniknya saja.
         */
        $sebelum = PendaftaranScopusKafe::count();

        $this->post(route('public.scopuskafe.store'),
            $this->isian('luar-angkasa', ['sesi pagi']))
            ->assertSessionHasErrors(['sesi']);

        $this->assertSame($sebelum, PendaftaranScopusKafe::count(),
            'Varian karangan tetap tersimpan sebagai pemesanan.');
    }

    #[Test]
    public function variannya_tersimpan_dan_disebut_di_halaman_status(): void
    {
        $this->post(route('public.scopuskafe.store'), $this->isian('online', ['sesi malam']));

        $p = PendaftaranScopusKafe::latest('created_at')->firstOrFail();

        $this->assertSame('online', $p->varian,
            'Variannya tidak tersimpan; panitia tidak tahu orangnya datang atau daring.');

        $this->assertSame('600000', (string) $p->biaya,
            'Harga yang tersimpan bukan harga varian yang dipilih.');

        Http::fake(['*' => Http::response(['transaction' => ['status' => 'PENDING']], 200)]);

        $isi = $this->get(route('public.scopuskafe.status', $p->getKey()))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Daring', $isi,
            'Halaman statusnya tidak menyebut bentuk yang dipilih.');
    }

    #[Test]
    public function tanpa_tarif_aktif_sesinya_tidak_dijual_seharga_nol(): void
    {
        /*
         * Daftar sesi dan tarifnya datang dari DUA tempat yang berbeda, dan
         * itu yang membuat lubang ini mungkin: sesinya punya daftar BAWAAN
         * di PendaftaranSemuaLayanan::SESI, sementara harganya tidak punya
         * bawaan sama sekali. Jadi begitu panitia menonaktifkan tarif satu
         * varian, sesinya MASIH ada — tinggal harganya yang hilang.
         *
         * Tanpa penjagaan ini $satuan jatuh ke nol dan dua sesi tatap muka
         * terjual Rp 0, lengkap dengan tagihan DOKU senilai kode uniknya
         * saja. Tidak ada galat; angkanya cuma salah.
         */
        ClinikScopusBiayaPersesi::where('layanan', 'scopus_kafe')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        PendaftaranSemuaLayanan::lupakanSesi();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $this->assertNotSame([], HargaKafe::sesiTersedia('offline'),
            'Andaian ujinya tidak berlaku lagi: sesi bawaannya ikut hilang saat '
            . 'tarifnya dimatikan, jadi lubang yang dijaga di bawah tidak ada.');

        $harga = HargaKafe::hitung('offline', ['sesi 1', 'sesi 2']);

        $this->assertSame([], $harga['sesi'],
            'Sesi tetap terjual padahal tidak ada tarif aktif yang memberinya harga.');

        $this->assertSame(0, $harga['total']);
    }

    #[Test]
    public function tarif_yang_dinonaktifkan_tidak_lagi_ditawarkan(): void
    {
        /*
         * Panitia menutup satu bentuk lewat layar Tarif Layanan. Kalau
         * halaman publik tetap menawarkannya, pemesanan masuk untuk layanan
         * yang sudah tidak dijalankan.
         */
        ClinikScopusBiayaPersesi::where('layanan', 'scopus_kafe')
            ->where('varian', 'online')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        PendaftaranSemuaLayanan::lupakanSesi();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $this->assertSame(['offline'], array_keys(HargaKafe::varianTersedia()));

        $isi = $this->get(route('public.scopuskafe.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('sesi malam', $isi,
            'Bentuk yang sudah ditutup panitia masih dipajang di halaman daftar.');
    }
}
