<?php

namespace Tests\Feature\PromoLayanan;

use App\Actions\Pendaftaran\BuatPendaftaran;
use App\KategoriLayanan;
use App\PromoLayanan;
use App\Support\DampakPromo;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PromoAngkatan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar rincian promo, unduhannya, gandakan, dan pemasangan dari sisi angkatan.
 */
class RincianDanUnduhanPromoTest extends TestCase
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

    // -------------------------------------------------------------- rincian

    #[Test]
    public function rincian_menampilkan_hasil_promonya(): void
    {
        [$admin, $angkatan, $promo] = $this->siap(['tipe' => 'rupiah', 'nominal' => 150_000]);

        $this->daftar($admin, $angkatan, 2);
        DampakPromo::lupakan();

        $this->actingAs($admin)->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee('Hasil promo ini')
            // 1 pendaftaran, 2 peserta, potongan 2 x 150.000.
            ->assertSee('Rp 300.000')
            ->assertSee($angkatan->nama);
    }

    #[Test]
    public function rincian_menyebut_promo_yang_belum_menunjuk_angkatan(): void
    {
        /*
         * Keadaan yang paling mudah luput: promo yang sudah dibuat, menyala,
         * tanggalnya pas — dan tidak memotong apa pun karena belum satu
         * angkatan pun dicentang.
         */
        $admin = $this->akun();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Melayang ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee('Belum menunjuk angkatan mana pun');
    }

    #[Test]
    public function rincian_memajang_jejak_perubahannya(): void
    {
        $admin = $this->akun();
        $this->actingAs($admin);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Berjejak ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $promo->update(['persen' => 35]);

        $this->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee('Jejak perubahan')
            ->assertSee('10 → 35')
            ->assertSee($admin->full_name);
    }

    #[Test]
    public function pelanggan_tidak_bisa_membuka_rincian(): void
    {
        $promo = PromoLayanan::create([
            'nama' => 'Promo Tertutup ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($this->akun(User::PERAN_PELANGGAN))
            ->get(route('account.promo-layanan.detail', $promo->id))
            ->assertRedirect(route('account.dashboard.index'));
    }

    // -------------------------------------------------------------- unduhan

    #[Test]
    public function unduhan_excel_dan_pdf_keluar_dengan_jenis_berkas_yang_benar(): void
    {
        $admin = $this->akun();

        $this->actingAs($admin)->get(route('account.promo-layanan.excel'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );

        $this->actingAs($admin)->get(route('account.promo-layanan.cetak'))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function unduhan_membawa_saringan_yang_sedang_dipakai(): void
    {
        /*
         * Berkas berisi 3 dari 60 promo tidak boleh terbaca seperti daftar
         * yang lengkap. Satu-satunya cara menyatakannya adalah menyebutkan
         * saringannya di kepala berkas — dan itu hanya bisa benar kalau
         * saringannya memang sampai ke sana.
         */
        $admin = $this->akun();
        $t = Str::random(6);

        PromoLayanan::create([
            'nama' => 'Ikut ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDay()->toDateString(), 'aktif' => true,
        ]);

        PromoLayanan::create([
            'nama' => 'Tidak Ikut ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->addDays(9)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(), 'aktif' => true,
        ]);

        // getContent(), bukan streamedContent(): PDF-nya dibangun di memori
        // lalu dikirim sekaligus, bukan dialirkan.
        $isi = $this->actingAs($admin)
            ->get(route('account.promo-layanan.cetak', ['cari' => $t, 'keadaan' => 'berjalan']))
            ->assertOk()->getContent();

        // PDF-nya biner; yang diperiksa bahwa permintaannya tidak galat dan
        // berkasnya memang PDF. Isi saringannya diperiksa dari HTML-nya.
        $this->assertStringStartsWith('%PDF', $isi);

        $html = view('account.promo_layanan.cetak-pdf', [
            'daftar' => PromoLayanan::cari($t)->get(),
            'saringan' => 'Disaring: kata kunci "' . $t . '", keadaan sedang berjalan.',
        ])->render();

        /*
         * Tanda kutipnya ikut dilolosi Blade jadi &quot;, jadi yang dicocokkan
         * bagian yang tidak berubah. Dicocokkan apa adanya, penjaga ini merah
         * untuk alasan yang sama sekali bukan yang dimaksud.
         */
        $this->assertStringContainsString('Disaring: kata kunci', $html);
        $this->assertStringContainsString($t, $html);
        $this->assertStringContainsString('Jumlah baris: 2', $html);
    }

    #[Test]
    public function pelanggan_tidak_bisa_mengunduh(): void
    {
        $pelanggan = $this->akun(User::PERAN_PELANGGAN);

        $this->actingAs($pelanggan)->get(route('account.promo-layanan.excel'))
            ->assertRedirect(route('account.dashboard.index'));
        $this->actingAs($pelanggan)->get(route('account.promo-layanan.cetak'))
            ->assertRedirect(route('account.dashboard.index'));
    }

    // ------------------------------------------------------------- gandakan

    #[Test]
    public function gandakan_menyalin_isinya_beserta_angkatannya(): void
    {
        [$admin, $angkatan, $promo] = $this->siap([
            'tipe' => 'persen', 'persen' => 30, 'batas_potongan' => 500_000, 'kuota' => 20,
        ]);

        $this->actingAs($admin)
            ->post(route('account.promo-layanan.gandakan', $promo->id))
            ->assertRedirect();

        $salinan = PromoLayanan::where('nama', 'like', '%(salinan)')->latest('created_at')->firstOrFail();

        $this->assertSame(30, $salinan->persen);
        $this->assertSame(500_000, $salinan->batas_potongan);
        $this->assertSame(20, $salinan->kuota);
        $this->assertSame(1, $salinan->angkatan()->count());
    }

    #[Test]
    public function salinan_lahir_mati_dan_bertanggal_hari_ini(): void
    {
        /*
         * Tanggal lama berarti promo yang sudah lewat, dan salinan yang
         * langsung menyala dengan tanggal tahun lalu adalah dua kekeliruan
         * sekaligus yang tidak akan terlihat sampai ada yang memeriksa
         * harganya.
         */
        [$admin, , $promo] = $this->siap(['tipe' => 'persen', 'persen' => 10]);

        $promo->update([
            'mulai' => now()->subYear()->toDateString(),
            'selesai' => now()->subYear()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.gandakan', $promo->id));

        $salinan = PromoLayanan::where('nama', 'like', '%(salinan)')->latest('created_at')->firstOrFail();

        $this->assertFalse($salinan->aktif);
        $this->assertSame(now()->toDateString(), $salinan->mulai->toDateString());
        $this->assertSame('nonaktif', $salinan->keadaan);
    }

    #[Test]
    public function gandakan_meninggalkan_jejak_yang_menyebut_asalnya(): void
    {
        [$admin, , $promo] = $this->siap(['tipe' => 'persen', 'persen' => 10]);

        $this->actingAs($admin)->post(route('account.promo-layanan.gandakan', $promo->id));

        $salinan = PromoLayanan::where('nama', 'like', '%(salinan)')->latest('created_at')->firstOrFail();

        $this->assertSame(
            1,
            $salinan->jejak()->where('aksi', 'digandakan')->count()
        );
        $this->assertStringContainsString(
            $promo->nama,
            (string) $salinan->jejak()->where('aksi', 'digandakan')->first()->ringkasan
        );
    }

    #[Test]
    public function pelanggan_tidak_bisa_menggandakan(): void
    {
        [, , $promo] = $this->siap(['tipe' => 'persen', 'persen' => 10]);
        $sebelum = PromoLayanan::count();

        $this->actingAs($this->akun(User::PERAN_PELANGGAN))
            ->post(route('account.promo-layanan.gandakan', $promo->id))
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertSame($sebelum, PromoLayanan::count());
    }

    // ------------------------------------------- dipasang dari sisi angkatan

    #[Test]
    public function promo_bisa_dipasang_dari_layar_angkatan(): void
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Ditempel ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(5)->toDateString(), 'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.tempel', $angkatan->id), [
            'promo_id' => $promo->id,
        ])->assertRedirect();

        PromoAngkatan::lupakan();

        $this->assertSame(1, $promo->fresh()->angkatan()->count());
        $this->assertSame(
            (string) $promo->getKey(),
            (string) $angkatan->fresh()->promo_berjalan?->getKey()
        );
    }

    #[Test]
    public function memasang_dua_kali_tidak_menggandakan_barisnya(): void
    {
        /*
         * Tanpa syncWithoutDetaching, kiriman kedua ditolak kunci unik
         * pivotnya dengan galat yang menyebut nama constraint — bukan apa
         * yang salah.
         */
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Dobel ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        foreach ([1, 2] as $ulang) {
            $this->actingAs($admin)->post(route('account.promo-layanan.tempel', $angkatan->id), [
                'promo_id' => $promo->id,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(1, DB::table('promo_layanan_angkatan')
            ->where('promo_id', $promo->id)->where('kategori_id', $angkatan->id)->count());
    }

    #[Test]
    public function promo_bisa_dilepas_dari_layar_angkatan(): void
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Dilepas ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $this->actingAs($admin)->post(route('account.promo-layanan.tempel', $angkatan->id), [
            'promo_id' => $promo->id, 'lepas' => '1',
        ])->assertRedirect();

        PromoAngkatan::lupakan();

        $this->assertSame(0, $promo->fresh()->angkatan()->count());
        $this->assertNull($angkatan->fresh()->promo_berjalan);
    }

    #[Test]
    public function memasang_dari_angkatan_ikut_tercatat_di_jejak_promonya(): void
    {
        /*
         * Dicatat di jejak PROMO, bukan jejak angkatan: yang berubah cakupan
         * promo, dan di situlah orang mencarinya kalau harga sebuah angkatan
         * tiba-tiba berbeda.
         */
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terjejak ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.tempel', $angkatan->id), [
            'promo_id' => $promo->id,
        ]);

        $this->assertStringContainsString(
            $angkatan->nama,
            (string) $promo->jejak()->first()->ringkasan
        );
    }

    #[Test]
    public function pelanggan_tidak_bisa_memasang_promo_dari_angkatan(): void
    {
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terkunci ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($this->akun(User::PERAN_PELANGGAN))
            ->post(route('account.promo-layanan.tempel', $angkatan->id), ['promo_id' => $promo->id])
            ->assertRedirect(route('account.dashboard.index'));

        $this->assertSame(0, $promo->fresh()->angkatan()->count());
    }

    #[Test]
    public function layar_angkatan_menawarkan_promo_yang_masih_punya_masa_depan(): void
    {
        /*
         * Promo yang sudah selesai sengaja tidak ditawarkan: memasangnya
         * tidak mengubah harga apa pun, jadi menu yang memuatnya cuma
         * menawarkan pilihan yang tidak melakukan apa-apa.
         */
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $hidup = PromoLayanan::create([
            'nama' => 'Promo Hidup ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDays(5)->toDateString(),
            'aktif' => true,
        ]);

        $mati = PromoLayanan::create([
            'nama' => 'Promo Kedaluwarsa ' . Str::random(4), 'tipe' => 'persen', 'persen' => 15,
            'mulai' => now()->subDays(9)->toDateString(),
            'selesai' => now()->subDay()->toDateString(), 'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('account.kategori-layanan.detail', $angkatan->id))
            ->assertOk()
            ->assertSee($hidup->nama)
            ->assertDontSee($mati->nama);
    }

    // ------------------------------------------- saringan keadaan yang baru

    #[Test]
    public function saringan_hampir_habis_hanya_memulangkan_yang_tinggal_seminggu(): void
    {
        $admin = $this->akun();
        $t = Str::random(6);

        PromoLayanan::create([
            'nama' => 'Mepet ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDays(5)->toDateString(),
            'selesai' => now()->addDays(3)->toDateString(), 'aktif' => true,
        ]);

        PromoLayanan::create([
            'nama' => 'Lama ' . $t, 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDays(5)->toDateString(),
            'selesai' => now()->addDays(40)->toDateString(), 'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => $t, 'keadaan' => 'hampir-habis']))
            ->assertOk()
            ->assertSee('Mepet ' . $t)
            ->assertDontSee('Lama ' . $t);
    }

    #[Test]
    public function saringan_kuota_habis_memulangkan_yang_jatahnya_sudah_terpakai(): void
    {
        [$admin, $angkatan, $promo] = $this->siap([
            'tipe' => 'rupiah', 'nominal' => 50_000, 'kuota' => 1,
        ]);

        $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan();

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['keadaan' => 'kuota-habis']))
            ->assertOk()
            ->assertSee($promo->nama);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['keadaan' => 'berjalan']))
            ->assertOk()
            ->assertDontSee($promo->nama);
    }

    #[Test]
    public function kuota_bertitik_ribuan_tersimpan_sebagai_angka(): void
    {
        $admin = $this->akun();
        $nama = 'Promo Kuota ' . Str::random(5);

        $this->actingAs($admin)->post(route('account.promo-layanan.store'), [
            'nama' => $nama, 'tipe' => 'persen', 'persen' => 10, 'kuota' => '1.500',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1500, PromoLayanan::where('nama', $nama)->firstOrFail()->kuota);
    }

    #[Test]
    public function kuota_nol_ditolak(): void
    {
        /*
         * Nol berarti promo yang habis sejak detik pertama, dan itu hampir
         * selalu salah ketik, bukan maksud.
         */
        $this->actingAs($this->akun())->post(route('account.promo-layanan.store'), [
            'nama' => 'Promo Kuota Nol', 'tipe' => 'persen', 'persen' => 10, 'kuota' => '0',
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors('kuota');
    }

    // ------------------------------------------------------------- perkakas

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Admin Rincian ' . $t, 'username' => 'rnc_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(), 'peran' => $peran,
        ])->save();

        return $orang;
    }

    private function angkatan(): KategoriLayanan
    {
        KategoriLayanan::lupakanPendaftar();

        return KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => 'Angkatan Rincian ' . Str::random(6),
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
            'biaya' => '2000000',
        ]);
    }

    /**
     * @param  array<string, mixed>  $ubah
     * @return array{0: User, 1: KategoriLayanan, 2: PromoLayanan}
     */
    private function siap(array $ubah): array
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan();

        $promo = PromoLayanan::create(array_merge([
            'nama' => 'Promo Rincian ' . Str::random(6),
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(),
            'aktif' => true,
        ], $ubah));

        $promo->angkatan()->attach($angkatan->getKey());

        PromoAngkatan::lupakan();
        DampakPromo::lupakan();

        return [$admin, $angkatan, $promo];
    }

    private function daftar(User $admin, KategoriLayanan $angkatan, int $jumlah)
    {
        $t = Str::random(6);

        $hasil = (new BuatPendaftaran)->jalankan('scopus_camp', [
            'kategori_id' => $angkatan->getKey(),
            'nama' => 'Pendaftar ' . $t, 'email' => $t . '@contoh.test',
            'telp' => '0811-0000-0005', 'jumlah' => (string) $jumlah, 'cara_bayar' => 'transfer',
        ], $admin->full_name);

        $this->assertTrue($hasil['berhasil'], 'Pendaftarannya gagal: ' . ($hasil['pesan'] ?? '-'));

        return $hasil['model'];
    }
}
