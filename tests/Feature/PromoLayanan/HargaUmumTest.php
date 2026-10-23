<?php

namespace Tests\Feature\PromoLayanan;

use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use App\CategoriesScopusCamp;
use App\KategoriLayanan;
use App\PendaftaranScopusCamp;
use App\PromoLayanan;
use App\Support\DampakPromo;
use App\Support\HargaUmum;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PromoAngkatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Harga yang DIPAJANG dan yang DITAGIH di halaman umum harus sama.
 *
 * Sebelum ini tidak sama, dan selisihnya bukan kecil. Halaman daftarnya
 * MENCORET `biaya` dan menjanjikan `total_biaya`, lalu borangnya menagih angka
 * yang dicoret itu — terukur 18 angkatan terkena, Scopus Camp Padang
 * menjanjikan Rp 5.060.000 lalu menagih Rp 5.500.000.
 *
 * Dan totalnya datang dari peramban: `total_pembayaran` disimpan mentah dari
 * kiriman borang, jadi nominal yang ditagihkan sepenuhnya ditentukan sisi yang
 * tidak bisa dipercaya.
 */
class HargaUmumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Berkas bukti bayar yang sudah ada SEBELUM uji ini jalan.
     *
     * DatabaseTransactions mengembalikan barisnya, TETAPI TIDAK mengembalikan
     * berkasnya: pengendali memindahkan unggahan ke public/ScopusCamp, dan
     * folder itu memuat 2.376 bukti pembayaran sungguhan. Terukur 23 Okt
     * 2026: tiap kali rangkaian ini jalan, tiga berkas 0 byte tertinggal di
     * sana selamanya.
     *
     * Yang dihapus HANYA yang muncul selama uji ini — daftar ini yang jadi
     * batasnya. Menyapu folder menurut pola nama atau menurut umur berkas
     * akan menghapus bukti bayar orang.
     *
     * Dua folder, bukan satu: uji ini mengirim borang Scopus Camp DAN borang
     * Analisis Bibliometrik, dan keduanya memindahkan unggahannya ke folder
     * yang berbeda. Terukur: dengan satu folder saja, tiga berkas 0 byte
     * tertinggal di public/bibliometrik tiap kali rangkaian ini jalan.
     *
     * @var array<string, array<int, string>>
     */
    private array $buktiSebelumnya = [];

    /** @var array<int, string> */
    private const FOLDER_BUKTI = ['ScopusCamp', 'bibliometrik'];

    protected function tearDown(): void
    {
        foreach (self::FOLDER_BUKTI as $folder) {
            $baru = array_diff(
                $this->isiFolderBukti($folder),
                $this->buktiSebelumnya[$folder] ?? []
            );

            foreach ($baru as $berkas) {
                @unlink(public_path($folder . '/' . $berkas));
            }
        }

        parent::tearDown();
    }

    /** @return array<int, string> */
    private function isiFolderBukti(string $folder): array
    {
        $jalur = public_path($folder);

        return is_dir($jalur)
            ? array_values(array_diff(scandir($jalur) ?: [], ['.', '..']))
            : [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::FOLDER_BUKTI as $folder) {
            $this->buktiSebelumnya[$folder] = $this->isiFolderBukti($folder);
        }

        PromoAngkatan::lupakan();
        DampakPromo::lupakan();
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    // ------------------------------------------------------------- rumusnya

    #[Test]
    public function harga_umum_memakai_total_biaya_bukan_biaya_kotornya(): void
    {
        $a = $this->angkatan(['biaya' => '5500000', 'total_biaya' => '5060000']);

        $this->assertSame(5_060_000, HargaUmum::hitung($a, 1)['satuan']);
    }

    #[Test]
    public function tanpa_total_biaya_jatuh_ke_biayanya(): void
    {
        $a = $this->angkatan(['biaya' => '2000000', 'total_biaya' => null]);

        $this->assertSame(2_000_000, HargaUmum::hitung($a, 1)['satuan']);
    }

    #[Test]
    public function promo_ikut_memotong_harga_umum(): void
    {
        $a = $this->angkatan(['biaya' => '4000000', 'total_biaya' => '4000000']);
        $this->promo($a, ['tipe' => 'persen', 'persen' => 25]);

        $h = HargaUmum::hitung($a, 2);

        $this->assertSame(3_000_000, $h['satuan']);
        $this->assertSame(6_000_000, $h['subtotal']);
        $this->assertNotNull($h['promo']);
    }

    #[Test]
    public function kode_unik_yang_di_luar_rentang_dibuat_ulang(): void
    {
        /*
         * Dipercaya mentah, siapa pun bisa mengirim kode unik 9.999.999 dan
         * menaikkan tagihannya sendiri — atau mengirim 0 dan menghapus
         * satu-satunya penanda yang dipakai mencocokkan mutasi rekening.
         */
        $a = $this->angkatan(['biaya' => '1000000']);
        [$min, $maks] = Pendaftaran::KODE_UNIK;

        foreach (['9999999', '0', 'bukan angka', null] as $ngawur) {
            $k = HargaUmum::hitung($a, 1, $ngawur)['kode_unik'];

            $this->assertGreaterThanOrEqual($min, $k);
            $this->assertLessThanOrEqual($maks, $k);
        }

        // Yang masuk akal TETAP dipakai: kode unik yang dibuat ulang diam-diam
        // membuat nominal di layar berbeda dari yang tersimpan.
        $this->assertSame(1234, HargaUmum::hitung($a, 1, '1234')['kode_unik']);
        $this->assertSame(500, HargaUmum::hitung($a, 1, '500')['kode_unik']);
        $this->assertSame(1500, HargaUmum::hitung($a, 1, '1500')['kode_unik']);
    }

    #[Test]
    public function kode_unik_yang_dipakai_sama_dengan_yang_dijumlahkan(): void
    {
        /*
         * Dihitung dua kali, keduanya memberi angka berbeda begitu jatuh ke
         * pembuat acak — dan nominal yang ditagihkan tidak berakhir dengan
         * kode yang tersimpan di sebelahnya, jadi tidak ada transfer yang bisa
         * dicocokkan dengannya.
         */
        $a = $this->angkatan(['biaya' => '1000000']);

        for ($i = 0; $i < 20; $i++) {
            $h = HargaUmum::hitung($a, 1, 'ngawur');

            $this->assertSame($h['subtotal'] + $h['kode_unik'], $h['total']);
        }
    }

    #[Test]
    public function potongan_kode_diskon_tidak_boleh_melebihi_subtotalnya(): void
    {
        $a = $this->angkatan(['biaya' => '500000']);

        // Kode uniknya harus DI DALAM rentang KODE_UNIK (500-1500); di luar
        // itu ia dibuat ulang, dan totalnya tidak bisa diramalkan.
        $h = HargaUmum::hitung($a, 1, '600', '900000');

        $this->assertSame(500_000, $h['potongan']);
        $this->assertSame(600, $h['total']);
    }

    // --------------------------------------------- yang dipajang di halaman

    #[Test]
    public function borang_umum_memajang_harga_yang_dijanjikan_halaman_daftarnya(): void
    {
        $a = $this->angkatan(['biaya' => '5500000', 'total_biaya' => '5060000'], CategoriesScopusCamp::class);

        $isi = $this->get(route('public.scopuscamp.formpendaftaran', $a->id))
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="5.060.000"', $isi,
            'Borang umum kembali menagih angka yang dicoret di halaman daftarnya.');
        $this->assertStringContainsString('name="subtotal" id="subtotalInput" value="5060000"', $isi);
    }

    #[Test]
    public function halaman_daftar_umum_memajang_harga_sesudah_promo(): void
    {
        /*
         * `mulai` SENGAJA besok, bukan sebulan lagi.
         *
         * Halaman daftarnya dipaginasi 12 per halaman dan diurut dari
         * tanggal terdekat sejak 22 Okt 2026. Angkatan yang berjarak sebulan
         * mendarat di halaman keberapa pun — dan ujinya merah bukan karena
         * harga promonya salah, melainkan karena barisnya memang tidak ada di
         * halaman yang dibuka. Besok adalah tanggal paling awal yang masih
         * lolos saringan "belum mulai", jadi ia selalu di kelompok teratas.
         */
        $a = $this->angkatan([
            'biaya' => '4000000', 'total_biaya' => '4000000', 'status' => 'active',
            'mulai' => now()->addDay()->toDateString(),
            'selesai' => now()->addDays(3)->toDateString(),
        ], CategoriesScopusCamp::class);

        $this->promo($a, ['tipe' => 'persen', 'persen' => 50]);

        $this->get(route('public.scopuscamp.index'))
            ->assertOk()
            ->assertSee('Rp 2.000.000')
            // Huruf kecil sejak halamannya dirombak; yang dijaga potongannya
            // tetap disebut, bukan besar-kecil hurufnya.
            ->assertSee('hemat 50%');
    }

    // ------------------------------------------------- yang benar-benar ditagih

    #[Test]
    public function total_yang_dikirim_peramban_tidak_dipercaya(): void
    {
        /*
         * Inti dari seluruh perubahan ini. Sebelumnya kiriman di bawah akan
         * tersimpan apa adanya: pendaftaran Rp 1 untuk angkatan seharga
         * Rp 5.060.000, tanpa satu baris pun yang menolak.
         */
        $a = $this->angkatan([
            'biaya' => '5500000', 'total_biaya' => '5060000', 'status' => 'active',
        ], CategoriesScopusCamp::class);

        $this->post(route('public.scopuscamp.store'), [
            'kategori_id' => $a->id,
            'nama' => 'Penawar Nakal', 'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0007', 'affiliasi' => 'Uji',
            'jumlah_pendaftar' => 1,
            // Centang ketentuan dan bukti transfer sama-sama wajib sejak
            // 23 Okt 2026. Uji ini memeriksa HARGA, bukan penjagaannya,
            // jadi ia memenuhi keduanya seperti pendaftar sungguhan — dan
            // jalur yang dilaluinya tetap transfer manual, sebab rangkaian
            // uji memang mematikan gerbang pembayaran.
            'setuju' => '1',
            'gambar' => \Illuminate\Http\UploadedFile::fake()
                ->create('bukti.jpg', 40, 'image/jpeg'),
            'kode_unik' => '572',
            'ppn' => '0',
            'total_pembayaran' => '1',
        ]);

        $b = PendaftaranScopusCamp::where('nama', 'Penawar Nakal')->firstOrFail();

        $this->assertSame(5_060_572, (int) $b->total_pembayaran);
        $this->assertSame(572, (int) $b->kode_unik);
    }

    #[Test]
    public function pendaftaran_umum_ikut_kena_promo_dan_tertali_padanya(): void
    {
        $a = $this->angkatan([
            'biaya' => '4000000', 'total_biaya' => '4000000', 'status' => 'active',
        ], CategoriesScopusCamp::class);

        $promo = $this->promo($a, ['tipe' => 'rupiah', 'nominal' => 500_000]);

        $this->post(route('public.scopuscamp.store'), [
            'kategori_id' => $a->id,
            'nama' => 'Pendaftar Promo', 'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0008', 'affiliasi' => 'Uji',
            'jumlah_pendaftar' => 2, 'kode_unik' => '550', 'ppn' => '0',
            // Nama peserta lainnya kini WAJIB sebanyak yang dipesan
            // dikurangi satu; aturannya diuji di PenjagaanBorangTest.
            'peserta' => [['nama' => 'Peserta Dua']],
            // Centang ketentuan dan bukti transfer sama-sama wajib sejak
            // 23 Okt 2026. Uji ini memeriksa HARGA, bukan penjagaannya,
            // jadi ia memenuhi keduanya seperti pendaftar sungguhan — dan
            // jalur yang dilaluinya tetap transfer manual, sebab rangkaian
            // uji memang mematikan gerbang pembayaran.
            'setuju' => '1',
            'gambar' => \Illuminate\Http\UploadedFile::fake()
                ->create('bukti.jpg', 40, 'image/jpeg'),
            'total_pembayaran' => '999',
        ]);

        $b = PendaftaranScopusCamp::where('nama', 'Pendaftar Promo')->firstOrFail();

        // (4.000.000 - 500.000) x 2 + 550
        $this->assertSame(7_000_550, (int) $b->total_pembayaran);
        $this->assertSame((string) $promo->getKey(), (string) $b->promo_id);
    }

    #[Test]
    public function bibliometrik_umum_dihitung_dengan_rumus_yang_sama(): void
    {
        /*
         * Dua pengendali umum, satu rumus. Kalau salah satunya menghitung
         * sendiri, selisihnya cuma ketahuan saat ada yang membandingkan dua
         * layanan — dan itu tidak pernah terjadi.
         */
        $a = $this->angkatan([
            'layanan' => 'bibliometrik', 'biaya' => '999000', 'total_biaya' => '750000',
            'status' => 'active',
        ], CategoriesAnalisisBibliometrik::class);

        $this->post(route('public.analisisbibliometrik.store'), [
            'kategori_id' => $a->id,
            'nama' => 'Pendaftar Biblio', 'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0009', 'affiliasi' => 'Uji',
            'jumlah_pendaftar' => 1, 'kode_unik' => '533', 'ppn' => '0',
            // Centang ketentuan dan bukti transfer sama-sama wajib sejak
            // 23 Okt 2026. Uji ini memeriksa HARGA, bukan penjagaannya,
            // jadi ia memenuhi keduanya seperti pendaftar sungguhan — dan
            // jalur yang dilaluinya tetap transfer manual, sebab rangkaian
            // uji memang mematikan gerbang pembayaran.
            'setuju' => '1',
            'gambar' => \Illuminate\Http\UploadedFile::fake()
                ->create('bukti.jpg', 40, 'image/jpeg'),
            'total_pembayaran' => '5',
        ]);

        $b = AnalisisBibliometrik::where('nama', 'Pendaftar Biblio')->firstOrFail();

        $this->assertSame(750_533, (int) $b->total_pembayaran);
    }

    #[Test]
    public function dampak_promo_ikut_menghitung_pendaftaran_dari_halaman_umum(): void
    {
        /*
         * Tanpa promo_id yang ditulis jalur umum, layar dampak promo hanya
         * menghitung pendaftaran yang dicatat panitia — dan angkanya jauh
         * lebih kecil daripada kenyataannya tanpa ada yang curiga.
         */
        $a = $this->angkatan([
            'biaya' => '1000000', 'total_biaya' => '1000000', 'status' => 'active',
        ], CategoriesScopusCamp::class);

        $promo = $this->promo($a, ['tipe' => 'rupiah', 'nominal' => 100_000]);

        $this->post(route('public.scopuscamp.store'), [
            'kategori_id' => $a->id,
            'nama' => 'Pendaftar Terhitung', 'email' => Str::random(6) . '@contoh.test',
            'telp' => '0811-0000-0010', 'affiliasi' => 'Uji',
            'jumlah_pendaftar' => 3, 'kode_unik' => '521', 'ppn' => '0',
            // Nama peserta lainnya kini WAJIB sebanyak yang dipesan
            // dikurangi satu; aturannya diuji di PenjagaanBorangTest.
            'peserta' => [['nama' => 'Peserta Dua'], ['nama' => 'Peserta Tiga']],
            // Centang ketentuan dan bukti transfer sama-sama wajib sejak
            // 23 Okt 2026. Uji ini memeriksa HARGA, bukan penjagaannya,
            // jadi ia memenuhi keduanya seperti pendaftar sungguhan — dan
            // jalur yang dilaluinya tetap transfer manual, sebab rangkaian
            // uji memang mematikan gerbang pembayaran.
            'setuju' => '1',
            'gambar' => \Illuminate\Http\UploadedFile::fake()
                ->create('bukti.jpg', 40, 'image/jpeg'),
            'total_pembayaran' => '0',
        ]);

        DampakPromo::lupakan();
        $angka = DampakPromo::untuk((string) $promo->getKey());

        $this->assertSame(1, $angka['pendaftaran']);
        $this->assertSame(3, $angka['peserta']);
    }

    // ------------------------------------------------------------- perkakas

    /** @param array<string, mixed> $ubah */
    private function angkatan(array $ubah, string $kelas = KategoriLayanan::class)
    {
        KategoriLayanan::lupakanPendaftar();

        return $kelas::create(array_merge([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Umum ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'selesai' => now()->addMonth()->addDays(3)->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
        ], $ubah));
    }

    /** @param array<string, mixed> $ubah */
    private function promo($angkatan, array $ubah): PromoLayanan
    {
        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Umum ' . Str::random(6),
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($angkatan->getKey());

        PromoAngkatan::lupakan();
        DampakPromo::lupakan();

        return $promo;
    }
}
