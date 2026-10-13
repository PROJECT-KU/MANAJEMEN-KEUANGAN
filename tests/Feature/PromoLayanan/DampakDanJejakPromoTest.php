<?php

namespace Tests\Feature\PromoLayanan;

use App\Actions\Pendaftaran\BuatPendaftaran;
use App\KategoriLayanan;
use App\PromoJejak;
use App\PromoLayanan;
use App\Support\DampakPromo;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PromoAngkatan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo bisa dipertanggungjawabkan: terukur hasilnya, terbatas jatahnya,
 * tercatat perubahannya.
 *
 * Sebelum ini ketiganya tidak ada. Promo yang sudah dibuat tidak bisa dinilai
 * berhasil atau tidak, tidak bisa dihentikan sendiri saat kebablasan, dan
 * tidak meninggalkan satu baris pun tentang siapa mengubah persennya.
 */
class DampakDanJejakPromoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        PromoAngkatan::lupakan();
        DampakPromo::lupakan();
        Pendaftaran::lupakan();
        KategoriLayanan::lupakanPendaftar();
    }

    // ------------------------------------------------------- tali ke promonya

    #[Test]
    public function pendaftaran_yang_kena_promo_tertali_ke_promonya(): void
    {
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'persen', 'persen' => 20]);

        $b = $this->daftar($admin, $angkatan, 1);

        $this->assertSame((string) $promo->getKey(), (string) $b->promo_id);
        $this->assertStringContainsString('PROMO', (string) $b->kode_diskon);
    }

    #[Test]
    public function pendaftaran_yang_potongannya_dari_alumni_tidak_tertali_ke_promo(): void
    {
        /*
         * Ditali apa adanya, pendaftaran yang potongannya datang dari alumni
         * ikut terhitung di layar dampak promo — dan promonya terlihat jauh
         * lebih berhasil daripada kenyataannya.
         */
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'persen', 'persen' => 5]);

        \App\ClinikScopusBiayaPersesi::query()
            ->where('layanan', 'scopus_camp')->whereNull('varian')
            ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
            ->update(['status' => \App\ClinikScopusBiayaPersesi::NONAKTIF]);

        \App\ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_camp', 'varian' => null, 'biaya_persesi' => '1000000',
            'status' => \App\ClinikScopusBiayaPersesi::AKTIF, 'diskon_alumni_persen' => '40',
        ]);

        $b = $this->daftar($admin, $angkatan, 1, ['alumni' => '1']);

        $this->assertSame('ALUMNI', (string) $b->kode_diskon);
        $this->assertNull($b->promo_id);
    }

    // -------------------------------------------------------------- dampaknya

    #[Test]
    public function dampak_promo_menghitung_pendaftaran_peserta_dan_potongannya(): void
    {
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'rupiah', 'nominal' => 100_000]);

        $this->daftar($admin, $angkatan, 1);
        $this->daftar($admin, $angkatan, 3);

        DampakPromo::lupakan();
        $angka = DampakPromo::untuk((string) $promo->getKey());

        $this->assertSame(2, $angka['pendaftaran']);
        $this->assertSame(4, $angka['peserta']);
        // 1 x 100.000 + 3 x 100.000
        $this->assertSame(400_000, $angka['potongan']);
    }

    #[Test]
    public function dampak_tetap_terhitung_sesudah_promonya_diganti_nama(): void
    {
        /*
         * Inti dari keputusan menyimpan promo_id di samping namanya.
         *
         * Dihitung dari untaian "PROMO: <nama>" di kode_diskon, mengganti nama
         * promo membuat SELURUH pendaftaran lama berhenti terhitung — tanpa
         * galat, tanpa ada yang curiga, angkanya tinggal turun.
         */
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'rupiah', 'nominal' => 50_000]);

        $this->daftar($admin, $angkatan, 1);

        $promo->update(['nama' => 'Nama Yang Sudah Diganti ' . Str::random(4)]);

        DampakPromo::lupakan();

        $this->assertSame(1, DampakPromo::untuk((string) $promo->getKey())['pendaftaran']);
    }

    #[Test]
    public function rincian_per_angkatan_menjumlah_ke_totalnya(): void
    {
        [$admin, $satu, $promo] = $this->siap(['tipe' => 'rupiah', 'nominal' => 100_000]);

        $dua = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Kedua ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
            'biaya' => '2000000',
        ]);
        $promo->angkatan()->attach($dua->getKey());
        PromoAngkatan::lupakan();

        $this->daftar($admin, $satu, 2);
        $this->daftar($admin, $dua, 1);

        DampakPromo::lupakan();

        $rincian = DampakPromo::perAngkatan($promo);
        $total = DampakPromo::untuk((string) $promo->getKey());

        $this->assertCount(2, $rincian);
        $this->assertSame($total['potongan'], array_sum(array_column($rincian, 'potongan')));
        $this->assertSame($total['peserta'], array_sum(array_column($rincian, 'peserta')));
        // Yang paling besar potongannya di atas.
        $this->assertGreaterThanOrEqual($rincian[1]['potongan'], $rincian[0]['potongan']);
    }

    #[Test]
    public function promo_yang_belum_pernah_dipakai_memulangkan_nol_bukan_galat(): void
    {
        [, , $promo] = $this->siap(['tipe' => 'persen', 'persen' => 10]);

        $this->assertSame(
            ['pendaftaran' => 0, 'peserta' => 0, 'potongan' => 0],
            DampakPromo::untuk((string) $promo->getKey())
        );
        $this->assertSame([], DampakPromo::perAngkatan($promo));
    }

    // ------------------------------------------------------------------ kuota

    #[Test]
    public function promo_berhenti_memotong_saat_kuotanya_habis(): void
    {
        [$admin, $angkatan, $promo] = $this->siap([
            'tipe' => 'rupiah', 'nominal' => 100_000, 'kuota' => 2,
        ]);

        $a = $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan(); PromoAngkatan::lupakan();

        $b = $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan(); PromoAngkatan::lupakan();

        $c = $this->daftar($admin, $angkatan, 1);

        $this->assertSame(100_000, (int) $a->nominal_diskon);
        $this->assertSame(100_000, (int) $b->nominal_diskon);
        $this->assertNull($c->nominal_diskon, 'Pendaftaran ketiga masih kena promo padahal kuotanya 2.');
        $this->assertNull($c->promo_id);
    }

    #[Test]
    public function kuota_habis_punya_keadaannya_sendiri_bukan_berjalan(): void
    {
        /*
         * Terbaca "Sedang berjalan" padahal sudah tidak memotong apa pun,
         * admin akan menunggu pendaftaran yang tidak akan pernah dapat promo —
         * dan menyalahkan kodenya, bukan kuotanya.
         */
        [$admin, $angkatan, $promo] = $this->siap([
            'tipe' => 'rupiah', 'nominal' => 50_000, 'kuota' => 1,
        ]);

        $this->assertSame('berjalan', $promo->fresh()->keadaan);

        $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan();

        $segar = $promo->fresh();

        $this->assertSame('kuota-habis', $segar->keadaan);
        $this->assertSame('Kuota habis', $segar->keadaan_terbaca);
        $this->assertSame(0, $segar->sisa_kuota);
    }

    #[Test]
    public function kuota_kosong_berarti_tanpa_batas(): void
    {
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'rupiah', 'nominal' => 50_000]);

        $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan();

        $this->assertNull($promo->fresh()->sisa_kuota);
        $this->assertSame('berjalan', $promo->fresh()->keadaan);
    }

    // ------------------------------------------------------------------ jejak

    #[Test]
    public function membuat_promo_meninggalkan_jejak_berisi_isinya(): void
    {
        $admin = $this->akun();
        $this->actingAs($admin);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Berjejak ' . Str::random(4), 'tipe' => 'persen', 'persen' => 25,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDays(3)->toDateString(),
            'aktif' => true,
        ]);

        $jejak = $promo->jejak()->get();

        $this->assertCount(1, $jejak);
        $this->assertSame('dibuat', $jejak[0]->aksi);
        $this->assertStringContainsString('25%', (string) $jejak[0]->ringkasan);
        $this->assertSame($admin->full_name, $jejak[0]->oleh_nama);
    }

    #[Test]
    public function mengubah_promo_mencatat_nilai_lama_dan_barunya(): void
    {
        /*
         * "diubah" saja tidak menjawab pertanyaan yang sebenarnya diajukan
         * saat ada harga yang tidak terduga: diubah DARI apa, JADI apa.
         */
        $admin = $this->akun();
        $this->actingAs($admin);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Disunting ' . Str::random(4), 'tipe' => 'persen', 'persen' => 20,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $promo->update(['persen' => 50]);

        $terakhir = $promo->jejak()->first();

        $this->assertSame('diubah', $terakhir->aksi);
        $this->assertStringContainsString('20 → 50', (string) $terakhir->ringkasan);
    }

    #[Test]
    public function menyalakan_dan_mematikan_punya_aksinya_sendiri(): void
    {
        /*
         * Keduanya peristiwa yang paling sering dicari di jejak — "sejak kapan
         * promo ini mati?" — dan terkubur di antara baris "diubah" ia harus
         * dicari dengan membaca ringkasan satu per satu.
         */
        $this->actingAs($this->akun());

        $promo = PromoLayanan::create([
            'nama' => 'Promo Saklar ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $promo->update(['aktif' => false]);
        $this->assertSame('dimatikan', $promo->jejak()->first()->aksi);

        $promo->update(['aktif' => true]);
        $this->assertSame('dinyalakan', $promo->jejak()->first()->aksi);
    }

    #[Test]
    public function mengubah_saklar_bersama_hal_lain_tetap_tercatat_sebagai_diubah(): void
    {
        $this->actingAs($this->akun());

        $promo = PromoLayanan::create([
            'nama' => 'Promo Campur ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $promo->update(['aktif' => false, 'persen' => 15]);

        $this->assertSame('diubah', $promo->jejak()->first()->aksi);
    }

    #[Test]
    public function penyimpanan_yang_tidak_mengubah_apa_pun_tidak_dicatat(): void
    {
        $this->actingAs($this->akun());

        $promo = PromoLayanan::create([
            'nama' => 'Promo Diam ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $promo->update(['persen' => 10]);

        $this->assertSame(1, $promo->jejak()->count());
    }

    #[Test]
    public function menghapus_promo_meninggalkan_jejak_beserta_salinan_barisnya(): void
    {
        /*
         * Jejaknya TIDAK ikut terhapus bersama promonya: justru sesudah
         * promonya hilang, jejaknyalah satu-satunya keterangan yang tersisa.
         */
        $this->actingAs($this->akun());

        $promo = PromoLayanan::create([
            'nama' => 'Promo Dihapus ' . Str::random(4), 'tipe' => 'rupiah', 'nominal' => 70_000,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $id = (string) $promo->getKey();
        $promo->delete();

        $jejak = PromoJejak::where('promo_id', $id)->orderByDesc('id')->get();

        $this->assertSame('dihapus', $jejak[0]->aksi);
        $this->assertSame('rupiah', $jejak[0]->data['tipe'] ?? null);
        $this->assertSame(70000, (int) ($jejak[0]->data['nominal'] ?? 0));
    }

    // ------------------------------------------------------------- perkakas

    private function akun(): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Admin Dampak ' . $t, 'username' => 'dmp_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $orang;
    }

    /**
     * @param  array<string, mixed>  $ubah
     * @return array{0: User, 1: KategoriLayanan, 2: PromoLayanan}
     */
    private function siap(array $ubah): array
    {
        $admin = $this->akun();
        $t = Str::random(8);

        $angkatan = KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Dampak ' . $t,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '99', 'sisa_kuota' => '99', 'status' => 'active',
            'biaya' => '2000000',
        ]);

        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Dampak ' . $t,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($angkatan->getKey());

        PromoAngkatan::lupakan();
        DampakPromo::lupakan();

        return [$admin, $angkatan, $promo];
    }

    /** @param array<string, mixed> $tambahan */
    private function daftar(User $admin, KategoriLayanan $angkatan, int $jumlah, array $tambahan = [])
    {
        $t = Str::random(6);

        $hasil = (new BuatPendaftaran)->jalankan('scopus_camp', array_merge([
            'kategori_id' => $angkatan->getKey(),
            'nama' => 'Pendaftar ' . $t,
            'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0004',
            'jumlah' => (string) $jumlah,
            'cara_bayar' => 'transfer',
        ], $tambahan), $admin->full_name);

        $this->assertTrue($hasil['berhasil'], 'Pendaftarannya gagal: ' . ($hasil['pesan'] ?? '-'));

        return $hasil['model'];
    }
}
