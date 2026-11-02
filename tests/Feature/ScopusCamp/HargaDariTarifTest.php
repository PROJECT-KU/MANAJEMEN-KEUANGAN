<?php

namespace Tests\Feature\ScopusCamp;

use App\CategoriesScopusCamp;
use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Support\PromoAngkatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Harga Scopus Camp dibaca dari TARIF LAYANAN, bukan dari kolom angkatan.
 *
 * Keputusan pemiliknya 2 Nov 2026, dan ia lahir dari satu angkatan yang
 * salah harga: Scopus Camp Yogyakarta ke-202 memajang Rp 4.950.000 padahal
 * tarifnya Rp 5.500.000. Angkanya datang dari `total_biaya` milik angkatan
 * itu, yang sudah memuat diskon 10% yang diketik panitia saat membuatnya —
 * dan layar Tarif Layanan menyebut angka yang lain tanpa ada yang tahu mana
 * yang dipakai.
 *
 * Angkatan itu juga menyimpan kode diskon bernama sama dengan promonya,
 * jadi mengetik kodenya memotong 550.000 SEKALI LAGI di atas harga yang
 * sudah dipotong: Rp 4.400.000 untuk kelas yang dimaksudkan diskon 10%.
 */
class HargaDariTarifTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        PromoAngkatan::lupakan();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    protected function tearDown(): void
    {
        PromoAngkatan::lupakan();
        parent::tearDown();
    }

    private function tarif(string $layanan, ?string $varian, string $biaya): ClinikScopusBiayaPersesi
    {
        // Yang sudah ada dinonaktifkan: berlaku() memilih yang paling
        // belakangan disetel, dan dua tarif aktif untuk pasangan yang sama
        // membuat ujinya bergantung urutan.
        ClinikScopusBiayaPersesi::query()->untuk($layanan, $varian)
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);

        $t = ClinikScopusBiayaPersesi::create([
            'layanan' => $layanan, 'varian' => $varian,
            'biaya_persesi' => $biaya, 'status' => ClinikScopusBiayaPersesi::AKTIF,
        ]);

        PromoAngkatan::lupakan();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        return $t;
    }

    private function angkatanCamp(array $ganti = []): CategoriesScopusCamp
    {
        return CategoriesScopusCamp::create(array_merge([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Tarif ' . Str::random(6),
            'nama_ke' => (string) random_int(1000, 1999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(12)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'group_wa' => 'https://chat.whatsapp.com/ujitarif',
            'total_kuota' => 20, 'sisa_kuota' => 20,
            'status' => 'active',
        ], $ganti));
    }

    #[Test]
    public function tarif_mengalahkan_kolom_harga_di_angkatannya(): void
    {
        /*
         * Inilah bentuk persis kekeliruan ke-202: angkatannya menyimpan
         * harga yang sudah didiskon, tarifnya menyebut harga penuh.
         */
        $this->tarif('scopus_camp', 'jawa', '5500000');

        $a = $this->angkatanCamp(['biaya' => '5500000', 'total_biaya' => '4950000']);

        $this->assertSame(5500000, (int) $a->harga_sebelum_promo,
            'Harganya masih dibaca dari kolom angkatan, jadi angka yang dipajang '
            . 'bisa berbeda dari yang disetel panitia di layar Tarif Layanan.');
    }

    #[Test]
    public function tiap_varian_memakai_tarifnya_sendiri(): void
    {
        /*
         * Jawa dan luar Jawa berbeda harganya. Satu tarif untuk keduanya
         * akan menagih peserta luar Jawa dengan harga Jawa — selisih sejuta
         * per kursi, dan tidak ada galat yang memberi tahu.
         */
        $this->tarif('scopus_camp', 'jawa', '5500000');
        $this->tarif('scopus_camp', 'luar_jawa', '6500000');

        $jawa = $this->angkatanCamp(['varian' => 'jawa', 'biaya' => '1']);
        $luar = $this->angkatanCamp(['varian' => 'luar_jawa', 'biaya' => '1']);

        $this->assertSame(5500000, (int) $jawa->harga_sebelum_promo);
        $this->assertSame(6500000, (int) $luar->harga_sebelum_promo, 'Varian luar Jawa memakai tarif Jawa.');
    }

    #[Test]
    public function tanpa_tarif_aktif_harganya_jatuh_ke_kolom_angkatan(): void
    {
        /*
         * Dipaksa nol saat tarifnya belum disetel, SELURUH angkatan layanan
         * itu mendadak berharga Rp 0 dan borangnya menerima pendaftaran
         * gratis — kegagalan yang jauh lebih mahal daripada harga yang
         * ketinggalan.
         */
        ClinikScopusBiayaPersesi::query()->untuk('scopus_camp', 'jawa')
            ->update(['status' => ClinikScopusBiayaPersesi::NONAKTIF]);
        PromoAngkatan::lupakan();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $a = $this->angkatanCamp(['biaya' => '4200000', 'total_biaya' => null]);

        $this->assertSame(4200000, (int) $a->harga_sebelum_promo,
            'Tanpa tarif aktif harganya tidak jatuh ke kolom angkatan; '
            . 'angkatan bisa berakhir berharga nol.');
    }

    #[Test]
    public function layanan_lain_tidak_ikut_berubah(): void
    {
        /*
         * Sengaja DIBATASI ke Scopus Camp. Dicoba untuk semua layanan dan
         * rangkaian ujinya memerah di 9 tempat: sesi Webinar Eksklusif punya
         * harga sendiri-sendiri dan tarifnya cuma angka bawaan, sementara
         * angkatan Scopus Camp memang mengikuti satu tarif baku per varian.
         */
        $this->tarif('webinar_eksklusif', null, '129000');

        $sesi = KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'token' => Str::random(30),
            'nama' => 'UJI Webinar Tarif ' . Str::random(5),
            'nama_ke' => (string) random_int(2000, 2999),
            'mulai' => now()->addDays(14)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30',
            'platform' => 'Zoom', 'pemateri' => 'Pemateri Uji', 'pemateri_jabatan' => 'Trainer',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '275000', 'status' => 'active',
        ]);

        $this->assertSame(275000, (int) $sesi->harga_sebelum_promo,
            'Harga sesi Webinar Eksklusif ikut ditimpa tarif; sesi yang sengaja '
            . 'dihargai berbeda jadi ikut harga bawaan.');
    }
}
