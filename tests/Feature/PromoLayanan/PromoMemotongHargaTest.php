<?php

namespace Tests\Feature\PromoLayanan;

use App\Actions\Pendaftaran\BuatPendaftaran;
use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PromoAngkatan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo benar-benar memotong harga yang DITAGIH, bukan cuma tampil di layar.
 *
 * Yang diuji di sini angka akhirnya. Sebelum promo bersama ada, satu-satunya
 * cara memberi potongan untuk beberapa angkatan sekaligus adalah menyunting
 * `total_biaya` tiap angkatan satu per satu — jadi promo yang "sudah dibuat"
 * tetap tidak memotong apa pun sampai ada yang ingat mengubah angkanya.
 */
class PromoMemotongHargaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Promo disimpan di ingatan selama satu permintaan. Tanpa dibuang,
        // uji kedua masih memakai promo milik uji pertama.
        PromoAngkatan::lupakan();
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    #[Test]
    public function promo_persen_memotong_tagihannya(): void
    {
        [$admin, $angkatan] = $this->siap(5_000_000);

        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 20]);

        $b = $this->daftar($admin, $angkatan, 1);

        // 5.000.000 - 20% = 4.000.000, ditambah kode unik dua angka.
        $this->assertSame(1_000_000, (int) $b->nominal_diskon);
        $this->assertSame(4_000_000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    #[Test]
    public function promo_rupiah_dihitung_per_peserta_bukan_sekali_per_pesanan(): void
    {
        /*
         * Yang paling mudah keliru di seluruh fitur ini.
         *
         * Harga yang dipajang di halaman umum harga SATU orang. Promo "potong
         * Rp 100.000" yang dihitung sekali untuk pesanan tujuh kursi akan
         * menagih Rp 600.000 lebih banyak daripada yang terbaca di halamannya
         * — tanpa galat, dan selisihnya baru ketahuan saat orangnya
         * membandingkan sendiri.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->promo($angkatan, ['tipe' => 'rupiah', 'nominal' => 100_000]);

        $b = $this->daftar($admin, $angkatan, 7);

        $this->assertSame(700_000, (int) $b->nominal_diskon);
        $this->assertSame(6_300_000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    #[Test]
    public function batas_potongan_menahan_promo_persen_pada_harga_besar(): void
    {
        [$admin, $angkatan] = $this->siap(6_500_000);

        // 40% dari 6.500.000 = 2.600.000; dibatasi 1.000.000.
        $this->promo($angkatan, [
            'tipe' => 'persen', 'persen' => 40, 'batas_potongan' => 1_000_000,
        ]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(1_000_000, (int) $b->nominal_diskon);
    }

    #[Test]
    public function batas_tidak_berlaku_untuk_promo_rupiah(): void
    {
        /*
         * Batasnya hanya berarti untuk promo persen. Dipakai juga pada promo
         * rupiah, admin yang mengisi keduanya mendapat potongan yang lebih
         * kecil daripada angka yang ia ketik sendiri — dan tidak ada apa pun
         * di layar yang menjelaskan kenapa.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $promo = $this->promo($angkatan, ['tipe' => 'rupiah', 'nominal' => 300_000]);
        $promo->forceFill(['batas_potongan' => 50_000])->save();

        PromoAngkatan::lupakan();

        $this->assertSame(300_000, $promo->potonganUntuk(1_000_000));
    }

    #[Test]
    public function promo_yang_belum_mulai_tidak_memotong(): void
    {
        [$admin, $angkatan] = $this->siap(2_000_000);

        $this->promo($angkatan, [
            'tipe' => 'persen', 'persen' => 50,
            'mulai' => now()->addDays(3)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(),
        ]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertNull($b->nominal_diskon);
        $this->assertSame(2_000_000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    #[Test]
    public function promo_yang_sudah_selesai_tidak_memotong(): void
    {
        /*
         * Keadaannya dihitung dari tanggalnya saat DIBACA, bukan dinaikkan
         * penjadwal: cron di hosting ini belum pernah terpasang, jadi promo
         * yang bergantung penjadwal akan tetap memotong berhari-hari sesudah
         * periodenya habis.
         */
        [$admin, $angkatan] = $this->siap(2_000_000);

        $this->promo($angkatan, [
            'tipe' => 'persen', 'persen' => 50,
            'mulai' => now()->subDays(20)->toDateString(),
            'selesai' => now()->subDay()->toDateString(),
        ]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertNull($b->nominal_diskon);
    }

    #[Test]
    public function promo_hari_terakhir_masih_memotong_sampai_habis_harinya(): void
    {
        [$admin, $angkatan] = $this->siap(2_000_000);

        $this->promo($angkatan, [
            'tipe' => 'persen', 'persen' => 50,
            'mulai' => now()->subDays(5)->toDateString(),
            'selesai' => now()->toDateString(),
        ]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(1_000_000, (int) $b->nominal_diskon);
    }

    #[Test]
    public function promo_yang_dimatikan_tidak_memotong_walau_tanggalnya_pas(): void
    {
        [$admin, $angkatan] = $this->siap(2_000_000);

        $promo = $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 50]);
        $promo->update(['aktif' => false]);

        PromoAngkatan::lupakan();

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertNull($b->nominal_diskon);
        $this->assertSame('nonaktif', $promo->fresh()->keadaan);
    }

    #[Test]
    public function angkatan_yang_tidak_dicentang_tetap_harga_normal(): void
    {
        /*
         * Inti dari keputusan "sasarannya angkatan, dicentang satu-satu":
         * tidak semua angkatan dapat promo. Promo yang diam-diam menyebar ke
         * seluruh layanan berarti angkatan yang harganya sudah disepakati
         * dengan lembaga ikut terpotong.
         */
        [$admin, $kena] = $this->siap(2_000_000);

        $luput = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Tanpa Promo ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '20', 'sisa_kuota' => '20', 'status' => 'active',
            'biaya' => '2000000',
        ]);

        $this->promo($kena, ['tipe' => 'persen', 'persen' => 50]);

        $a = $this->daftar($admin, $kena, 1);
        $b = $this->daftar($admin, $luput, 1);

        $this->assertSame(1_000_000, (int) $a->nominal_diskon);
        $this->assertNull($b->nominal_diskon);
    }

    #[Test]
    public function dua_promo_pada_satu_angkatan_dimenangkan_yang_paling_untung(): void
    {
        /*
         * Satu angkatan boleh kena beberapa promo tanpa ada yang salah —
         * "Promo Akhir Tahun" dan "Promo Alumni Reuni" bisa dicentang pada
         * angkatan yang sama. Yang dipakai yang paling menguntungkan
         * pendaftarnya, sama seperti aturan alumni lawan rombongan yang sudah
         * berlaku sebelum promo ada.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->promo($angkatan, ['tipe' => 'rupiah', 'nominal' => 100_000], 'Promo Kecil');
        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 30], 'Promo Besar');

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(300_000, (int) $b->nominal_diskon);
        $this->assertStringContainsString('Promo Besar', (string) $b->kode_diskon);
    }

    #[Test]
    public function kode_potongan_menyebut_nama_promonya(): void
    {
        /*
         * Kolom kode_diskon satu-satunya keterangan yang tersimpan bersama
         * barisnya. Promo bisa sudah dihapus atau diganti isinya saat
         * pendaftaran itu dibuka setengah tahun kemudian; tanpa namanya,
         * potongan Rp 500.000 tidak bisa dijelaskan siapa pun.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 10], 'Diskon Hari Guru');

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame('PROMO: Diskon Hari Guru', (string) $b->kode_diskon);
    }

    #[Test]
    public function promo_menang_atas_alumni_kalau_potongannya_lebih_besar(): void
    {
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->tarif(['diskon_alumni_persen' => '10']);
        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 30], 'Promo Menang');

        $b = $this->daftar($admin, $angkatan, 1, ['alumni' => '1']);

        $this->assertSame(300_000, (int) $b->nominal_diskon);
        $this->assertStringContainsString('PROMO', (string) $b->kode_diskon);
    }

    #[Test]
    public function alumni_menang_kalau_potongannya_lebih_besar_dari_promo(): void
    {
        /*
         * Arah sebaliknya ikut dijaga. Diuji hanya satu arah, aturan "yang
         * paling untung" bisa diganti jadi "promo selalu menang" tanpa satu
         * uji pun merah — dan pendaftar yang semestinya dapat potongan lebih
         * besar diam-diam dirugikan.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->tarif(['diskon_alumni_persen' => '40']);
        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 10]);

        $b = $this->daftar($admin, $angkatan, 1, ['alumni' => '1']);

        $this->assertSame(400_000, (int) $b->nominal_diskon);
        $this->assertSame('ALUMNI', (string) $b->kode_diskon);
    }

    #[Test]
    public function promo_mengalahkan_potongan_khusus_yang_diketik_panitia(): void
    {
        /*
         * Potongan khusus tetap paling akhir: ia yang diketik tangan, dan
         * sesuatu yang diketik tangan tidak boleh ditumpuk di atas potongan
         * yang dihitung sistem.
         */
        [$admin, $angkatan] = $this->siap(1_000_000);

        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 25]);

        $b = $this->daftar($admin, $angkatan, 1, [
            'potongan' => '50000', 'kode_potongan' => 'SPONSOR',
        ]);

        $this->assertSame(250_000, (int) $b->nominal_diskon);
        $this->assertStringNotContainsString('SPONSOR', (string) $b->kode_diskon);
    }

    #[Test]
    public function potongan_promo_tidak_pernah_melebihi_tagihannya(): void
    {
        /*
         * Potongan yang melebihi tagihan menghasilkan total negatif, dan
         * nominal transfer negatif tidak berarti apa-apa.
         */
        [$admin, $angkatan] = $this->siap(500_000);

        $this->promo($angkatan, ['tipe' => 'rupiah', 'nominal' => 900_000]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(500_000, (int) $b->nominal_diskon);
        $this->assertSame(0 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    #[Test]
    public function angkatan_tanpa_harga_tidak_memotong_apa_pun(): void
    {
        /*
         * Terukur di basis data ini: 182 angkatan tidak punya biaya maupun
         * total_biaya. Promo persen pada harga nol harus menghasilkan nol,
         * bukan pembagian yang menghasilkan nilai aneh.
         */
        [$admin, $angkatan] = $this->siap(0);

        $promo = $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 50]);

        $this->assertSame(0, $promo->potonganUntuk(0));
        $this->assertSame(0, $angkatan->fresh()->potongan_promo);
    }

    #[Test]
    public function harga_setelah_promo_terbaca_dari_angkatannya(): void
    {
        /*
         * Dipakai halaman umum dan layar angkatan. Rumusnya HARUS sama dengan
         * yang dipakai saat menagih — kalau berbeda, harga yang dipajang dan
         * harga yang ditagih tidak pernah cocok dan tidak ada galat yang
         * memberi tahu.
         */
        [, $angkatan] = $this->siap(4_000_000);

        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 25]);

        PromoAngkatan::lupakan();
        $segar = $angkatan->fresh();

        $this->assertSame(4_000_000, $segar->harga_sebelum_promo);
        $this->assertSame(1_000_000, $segar->potongan_promo);
        $this->assertSame(3_000_000, $segar->harga_setelah_promo);
    }

    #[Test]
    public function total_biaya_angkatan_jadi_dasar_promo_bukan_biaya_kotornya(): void
    {
        /*
         * `total_biaya` sudah memuat diskon angkatannya sendiri dan itulah
         * yang ditagihkan. Promo dihitung DI ATAS angka itu; dihitung dari
         * `biaya`, promo 10% pada angkatan yang sudah berdiskon akan memotong
         * lebih banyak daripada 10% dari harga yang sebenarnya ditagih.
         */
        [$admin, $angkatan] = $this->siap(5_000_000);
        $angkatan->forceFill(['total_biaya' => '4000000'])->save();

        $this->promo($angkatan, ['tipe' => 'persen', 'persen' => 10]);

        PromoAngkatan::lupakan();

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(400_000, (int) $b->nominal_diskon);
        $this->assertSame(3_600_000 + (int) $b->kode_unik, (int) $b->total_pembayaran);
    }

    // ------------------------------------------------------------- perkakas

    /** @return array{0: User, 1: KategoriLayanan} */
    private function siap(int $harga): array
    {
        $t = Str::random(8);

        $admin = User::create([
            'full_name' => 'Admin Promo ' . $t, 'username' => 'prm_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $admin->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Promo ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
            'biaya' => $harga > 0 ? (string) $harga : null,
        ]);

        return [$admin, $angkatan];
    }

    /** @param array<string, mixed> $ubah */
    private function promo(KategoriLayanan $angkatan, array $ubah, string $nama = 'Promo Uji'): PromoLayanan
    {
        $promo = PromoLayanan::create(array_merge([
            'nama' => $nama . ' ' . Str::random(4),
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(),
            'aktif' => true,
        ], $ubah));

        // Nama persis dipertahankan kalau pemanggilnya memeriksanya.
        if ($nama !== 'Promo Uji') {
            $promo->forceFill(['nama' => $nama])->save();
        }

        $promo->angkatan()->attach($angkatan->getKey());

        PromoAngkatan::lupakan();

        return $promo->fresh();
    }

    /**
     * Tarif aktif untuk scopus_camp tanpa varian, dipakai uji alumni.
     *
     * @param  array<string, mixed>  $ubah
     */
    private function tarif(array $ubah): ClinikScopusBiayaPersesi
    {
        // Yang sudah aktif diturunkan dulu: berlaku() memakai yang terakhir
        // disetel, dan dua baris aktif untuk pasangan yang sama membuat
        // hasilnya bergantung urutan.
        ClinikScopusBiayaPersesi::query()
            ->where('layanan', 'scopus_camp')->whereNull('varian')
            ->where('status', ClinikScopusBiayaPersesi::AKTIF)
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        return ClinikScopusBiayaPersesi::create(array_merge([
            'layanan' => 'scopus_camp', 'varian' => null,
            'biaya_persesi' => '1000000', 'status' => ClinikScopusBiayaPersesi::AKTIF,
        ], $ubah));
    }

    /** @param array<string, mixed> $tambahan */
    private function daftar(User $admin, KategoriLayanan $angkatan, int $jumlah, array $tambahan = [])
    {
        $t = Str::random(6);

        $hasil = (new BuatPendaftaran)->jalankan('scopus_camp', array_merge([
            'kategori_id' => $angkatan->getKey(),
            'nama' => 'Pendaftar ' . $t,
            'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0002',
            'jumlah' => (string) $jumlah,
            'cara_bayar' => 'transfer',
        ], $tambahan), $admin->full_name);

        $this->assertTrue($hasil['berhasil'], 'Pendaftarannya gagal: ' . ($hasil['pesan'] ?? '-'));

        return $hasil['model'];
    }
}
