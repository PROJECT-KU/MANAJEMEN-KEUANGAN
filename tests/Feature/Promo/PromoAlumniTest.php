<?php

namespace Tests\Feature\Promo;

use App\CategoriesScopusCamp;
use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\PromoLayanan;
use App\Support\PromoAngkatan;
use App\Support\RiwayatLunas;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo alumni: potongan hanya untuk yang PERNAH IKUT DAN LUNAS.
 *
 * Keputusan pemiliknya 31 Okt 2026, dengan contohnya sendiri: nomor
 * 0895421735441 pernah mendaftar Webinar Eksklusif, lalu mendaftar Class
 * Offline (Scopus Camp) dan dapat potongan. Tetapi hanya kalau pendaftaran
 * Webinar-nya SUDAH LUNAS — yang pernah mendaftar lalu tidak membayar tidak
 * dapat apa-apa.
 *
 * Syarat lunasnya bukan kerewelan. Tanpa itu satu orang bisa membuat
 * pendaftaran di layanan termurah, membiarkannya kedaluwarsa tanpa membayar
 * sepeser pun, lalu memakai potongan alumninya selamanya di layanan
 * termahal — dan tidak ada satu baris pun yang menolaknya.
 *
 * Layanan prasyaratnya DIPILIH ADMIN, tidak dipatok di kode: pasangan
 * layanan yang dianggap "alumni" berubah tiap kampanye.
 */
class PromoAlumniTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Nomor uji DIBUAT BARU tiap jalan, bukan nomor dari laporannya.
     *
     * Versi pertama berkas ini memakai 0895421735441 — nomor yang disebut
     * pemiliknya. Lima uji negatifnya langsung merah, dan bukan karena
     * kodenya salah: nomor itu SUDAH alumni lunas di Webinar Eksklusif DAN
     * Scopus Camp di basis data lokal yang sungguhan. Jadi "yang belum
     * pernah ikut" tidak pernah benar-benar diuji.
     *
     * Diperiksa juga di setUp bahwa nomornya memang belum dikenal, supaya
     * tabrakan yang sama tidak terjadi diam-diam lagi.
     */
    private string $nomor;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.doku.client_id', 'BRN-UJI-ALUMNI');
        config()->set('services.doku.secret_key', 'SK-UJI-ALUMNI');

        Mail::fake();

        Http::fake(['*/checkout/v1/payment' => Http::response([
            'response' => [
                'payment' => ['url' => 'https://staging.doku.com/checkout-link-v2/UJI'],
                'order' => ['invoice_number' => 'CAMP-UJI'],
            ],
        ], 200)]);

        PromoAngkatan::lupakan();

        $this->nomor = '0811' . random_int(10000000, 99999999);

        $this->assertFalse(
            RiwayatLunas::pernahLunas($this->nomor, array_keys(
                \App\Support\PendaftaranSemuaLayanan::semuaSumber()
            )),
            'Nomor uji ' . $this->nomor . ' ternyata sudah punya riwayat lunas di '
            . 'basis data; uji negatif di bawah tidak akan menguji apa pun.'
        );
    }

    protected function tearDown(): void
    {
        PromoAngkatan::lupakan();

        parent::tearDown();
    }

    private function angkatanCamp(): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Alumni Camp ' . Str::random(6),
            'nama_ke' => (string) random_int(2000, 2999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujialumni',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'biaya' => 5500000, 'status' => 'active',
        ]);
    }

    private function sesiWebinar(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'UJI Alumni Webinar ' . Str::random(5),
            'nama_ke' => (string) random_int(1000, 1999),
            'mulai' => now()->subDays(30)->toDateString(),
            'selesai' => now()->subDays(30)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30',
            'platform' => 'Zoom', 'pemateri' => 'Pemateri Uji',
            'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    /** Pendaftaran Webinar milik nomor itu, dengan status apa pun. */
    private function ikutWebinar(string $status, ?string $telp = null): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create([
            'kategori_id' => $this->sesiWebinar()->getKey(),
            'id_transaksi' => 'WE-UJI-' . Str::random(6),
            'nama' => 'Alumni Uji',
            'email' => 'alumni' . Str::random(5) . '@contoh.test',
            'telp' => $telp ?? $this->nomor,
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '129000',
            'status' => $status,
        ]);
    }

    private function promoAlumni(CategoriesScopusCamp $a, array $syarat): PromoLayanan
    {
        $p = PromoLayanan::create([
            'nama' => 'UJI Promo Alumni ' . Str::random(5),
            'kode' => 'ALUMNI',
            'syarat_layanan' => $syarat,
            'tipe' => 'rupiah',
            'nominal' => 500000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addMonth()->toDateString(),
            'aktif' => true,
        ]);

        $p->angkatan()->attach($a->getKey());
        PromoAngkatan::lupakan();

        return $p;
    }

    private function daftarCamp(CategoriesScopusCamp $a, array $ganti = []): ?PendaftaranScopusCamp
    {
        $this->post(route('public.scopuscamp.store'), array_merge([
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Alumni',
            'email' => 'daftar' . Str::random(6) . '@contoh.test',
            'telp' => $this->nomor,
            'affiliasi' => 'Universitas Uji',
            'jumlah_pendaftar' => 1,
            'setuju' => '1',
            'kode_diskon' => 'ALUMNI',
        ], $ganti));

        return PendaftaranScopusCamp::where('kategori_id', $a->getKey())
            ->latest('created_at')->first();
    }

    // -------------------------------------------------- kasus yang dilaporkan

    #[Test]
    public function alumni_webinar_yang_lunas_dapat_potongan_di_class_offline(): void
    {
        $this->ikutWebinar('paid');

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $p = $this->daftarCamp($a);

        $this->assertSame(500000, (int) $p->nominal_diskon,
            'Alumni Webinar yang sudah lunas tidak dapat potongan di Scopus Camp.');
    }

    #[Test]
    public function pernah_mendaftar_tetapi_belum_lunas_tidak_dapat_potongan(): void
    {
        /*
         * Inti aturannya. Tanpa syarat lunas, satu orang membuat pendaftaran
         * di layanan termurah, membiarkannya kedaluwarsa tanpa membayar
         * sepeser pun, lalu memakai potongan alumninya di layanan termahal.
         */
        $this->ikutWebinar('pending');

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $p = $this->daftarCamp($a);

        $this->assertSame(0, (int) $p->nominal_diskon,
            'Pendaftaran yang belum dibayar sudah dihitung sebagai alumni.');
    }

    #[Test]
    public function pendaftaran_yang_batal_tidak_membuat_orang_jadi_alumni(): void
    {
        $this->ikutWebinar('cancel');

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $this->assertSame(0, (int) $this->daftarCamp($a)->nominal_diskon,
            'Pendaftaran yang dibatalkan dihitung sebagai alumni.');
    }

    #[Test]
    public function yang_belum_pernah_ikut_sama_sekali_tidak_dapat_potongan(): void
    {
        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $this->assertSame(0, (int) $this->daftarCamp($a)->nominal_diskon,
            'Yang belum pernah ikut apa pun tetap dapat potongan alumni.');
    }

    #[Test]
    public function nomor_orang_lain_yang_lunas_tidak_menolong(): void
    {
        /*
         * Yang diperiksa nomor PENDAFTARNYA, bukan sekadar ada-tidaknya
         * alumni di basis data.
         */
        $this->ikutWebinar('paid', '081200000099');

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $this->assertSame(0, (int) $this->daftarCamp($a)->nominal_diskon,
            'Kelunasan nomor orang lain ikut membuka potongan.');
    }

    #[Test]
    public function nomornya_cocok_dalam_bentuk_penulisan_apa_pun(): void
    {
        /*
         * Kolom telp diisi bertahun-tahun oleh layar yang berbeda: ada yang
         * "62812…", ada "0812…", ada "+62 812-…". Dicocokkan satu bentuk
         * saja, alumni yang nomornya tersimpan dalam ejaan lain tidak pernah
         * ketemu — dan diamnya terbaca seperti ia memang belum pernah ikut.
         */
        // Nomor yang SAMA, ditulis dengan ejaan lain: '+62 811-…' untuk
        // nomor uji yang tersimpan sebagai '0811…'.
        $lain = '+62 ' . substr($this->nomor, 1, 3) . '-' . substr($this->nomor, 4, 4)
            . '-' . substr($this->nomor, 8);

        $this->ikutWebinar('paid', $lain);

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['webinar_eksklusif']);

        $this->assertSame(500000, (int) $this->daftarCamp($a)->nominal_diskon,
            'Nomor yang tersimpan dalam ejaan lain tidak dikenali sebagai alumni.');
    }

    // ------------------------------------------------------------ dinamis

    #[Test]
    public function layanan_prasyaratnya_mengikuti_pilihan_admin(): void
    {
        /*
         * Promo yang sama, syarat yang berbeda. Kalau daftarnya dipatok di
         * kode, uji ini tidak mungkin lulus dua-duanya.
         */
        $this->ikutWebinar('paid');

        // Disyaratkan Scopus Camp — nomor ini belum pernah lunas di sana.
        $a1 = $this->angkatanCamp();
        $this->promoAlumni($a1, ['scopus_camp']);

        $this->assertSame(0, (int) $this->daftarCamp($a1)->nominal_diskon,
            'Syarat yang dipilih admin tidak dipakai.');

        // Disyaratkan Webinar Eksklusif — dan di sana ia memang lunas.
        $a2 = $this->angkatanCamp();
        $this->promoAlumni($a2, ['webinar_eksklusif']);

        $this->assertSame(500000, (int) $this->daftarCamp($a2)->nominal_diskon,
            'Syarat yang dipilih admin tidak dipakai.');
    }

    #[Test]
    public function beberapa_layanan_berarti_salah_satunya_cukup(): void
    {
        /*
         * Admin yang mencentang tiga layanan bermaksud "alumni mana pun dari
         * ketiganya", bukan "yang pernah ikut ketiga-tiganya" — syarat
         * terakhir itu hampir tidak pernah terpenuhi siapa pun dan akan
         * terbaca sebagai promo yang rusak.
         */
        $this->ikutWebinar('paid');

        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['scopus_camp', 'webinar_eksklusif', 'scopus_kafe']);

        $this->assertSame(500000, (int) $this->daftarCamp($a)->nominal_diskon,
            'Lunas di salah satu layanan tidak cukup; syaratnya jadi "semuanya".');
    }

    #[Test]
    public function promo_tanpa_syarat_riwayat_terbuka_untuk_siapa_saja(): void
    {
        $a = $this->angkatanCamp();
        $this->promoAlumni($a, []);

        $this->assertSame(500000, (int) $this->daftarCamp($a)->nominal_diskon,
            'Promo tanpa syarat riwayat ikut menuntut riwayat.');
    }

    #[Test]
    public function syarat_yang_menyebut_layanan_tak_dikenal_diabaikan(): void
    {
        /*
         * Layanan boleh dihapus dari katalog. Syarat yang menyebut layanan
         * yang sudah tidak ada adalah syarat yang TIDAK AKAN PERNAH
         * terpenuhi siapa pun — promonya diam-diam mati untuk semua orang,
         * dan tidak ada satu galat pun yang menyebut sebabnya.
         */
        $a = $this->angkatanCamp();
        $this->promoAlumni($a, ['layanan_yang_sudah_dihapus']);

        $this->assertSame(500000, (int) $this->daftarCamp($a)->nominal_diskon,
            'Syarat yang menyebut layanan tak dikenal mematikan promonya.');
    }

    #[Test]
    public function tanpa_nomor_sama_sekali_syaratnya_tidak_terpenuhi(): void
    {
        $this->assertFalse(RiwayatLunas::pernahLunas(null, ['webinar_eksklusif']),
            'Tanpa nomor, syarat riwayat tetap dianggap terpenuhi.');

        $this->assertFalse(RiwayatLunas::pernahLunas('', ['webinar_eksklusif']));
    }
}
