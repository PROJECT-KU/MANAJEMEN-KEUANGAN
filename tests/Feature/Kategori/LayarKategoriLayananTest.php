<?php

namespace Tests\Feature\Kategori;

use App\KategoriLayanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Layar angkatan gabungan.
 *
 * Yang dijaga di sini bukan cuma "halamannya terbuka", melainkan janji
 * fiturnya: admin mengetik nama, tanggal, dan kuota — harga serta deskripsinya
 * datang sendiri dari tarif induk.
 */
class LayarKategoriLayananTest extends TestCase
{
    use DatabaseTransactions;

    private function akun(string $peran): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . $peran,
            'username' => 'uji_ang_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function isian(array $lain = []): array
    {
        return array_merge([
            'layanan' => 'scopus_camp',
            // Scopus Camp bervarian: fasilitas di Jawa dan luar Jawa berbeda.
            'varian' => 'jawa',
            'nama' => 'SCOPUS CAMP YOGYAKARTA',
            'nama_ke' => '203',
            'mulai' => '2026-11-06',
            'selesai' => '2026-11-08',
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 20,
            'status' => 'draft',
        ], $lain);
    }

    // ------------------------------------------------------------- daftarnya

    #[Test]
    public function daftar_memuat_seluruh_layanan_dan_bisa_disaring(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $semua = $this->actingAs($admin)->get(route('account.kategori-layanan.index'));
        $semua->assertOk();
        $semua->assertSee('Scopus Camp', false);
        $semua->assertSee('Analisis Bibliometrik', false);

        $disaring = $this->actingAs($admin)
            ->get(route('account.kategori-layanan.index', ['layanan' => 'bibliometrik']));

        $disaring->assertOk();

        foreach ($disaring->viewData('angkatan') as $a) {
            $this->assertSame('bibliometrik', $a->layanan,
                'Saringan layanan kebobolan — ini yang paling mudah terjadi sesudah tabelnya disatukan.');
        }
    }

    #[Test]
    public function pencarian_menemukan_lewat_lokasi_dan_nomor_angkatan(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $this->kategoriUji(['lokasi' => 'Balikpapan', 'nama_ke' => '9911']);

        foreach (['Balikpapan', '9911'] as $kata) {
            $jawab = $this->actingAs($admin)
                ->get(route('account.kategori-layanan.index', ['cari' => $kata]));

            $this->assertGreaterThan(0, $jawab->viewData('angkatan')->total(), "Gagal mencari '$kata'.");
        }
    }

    private function kategoriUji(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'scopus_camp',
            'varian' => 'jawa',
            'token' => Str::random(30),
            'nama' => 'Angkatan Uji',
            'mulai' => '2026-11-06 00:00:00',
            'status' => 'draft',
        ], $lain));
    }

    // -------------------------------------------------------------- menyimpan

    #[Test]
    public function angkatan_baru_tersimpan_dengan_token_dan_sisa_kuota_terisi(): void
    {
        /*
         * Sisa kuota mengikuti total HANYA saat angkatannya baru. Token diisi
         * sistem — alamat publik angkatan memakainya, jadi kalau kosong
         * halamannya tidak bisa dibuka siapa pun.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.store'), $this->isian())
            ->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '203')->first();

        $this->assertNotNull($baru);
        $this->assertSame('20', (string) $baru->sisa_kuota);
        $this->assertNotEmpty($baru->token);
    }

    #[Test]
    public function menyunting_tidak_menimpa_sisa_kuota_yang_sudah_berkurang(): void
    {
        /*
         * Angkatan yang sudah berjalan sisanya berkurang karena ada yang
         * mendaftar. Menimpanya dengan total akan membuka kuota yang
         * sebenarnya sudah habis.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $ada = $this->kategoriUji(['total_kuota' => '20', 'sisa_kuota' => '3']);

        $this->actingAs($admin)->post(
            route('account.kategori-layanan.update', $ada),
            $this->isian(['total_kuota' => 20, 'sisa_kuota' => 3])
        );

        $this->assertSame('3', (string) $ada->refresh()->sisa_kuota);
    }

    #[Test]
    public function angkatan_baru_memotret_harga_dari_tarif_induk(): void
    {
        /*
         * Tidak ada isian harga sama sekali di borangnya. Angka yang dikirim
         * peramban pun diabaikan — yang dipakai selalu tarif yang berlaku,
         * supaya tidak ada dua sumber harga yang bisa berselisih.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = (int) \App\ClinikScopusBiayaPersesi::berlaku('scopus_camp', 'jawa')->biaya_persesi;

        $this->actingAs($admin)->post(route('account.kategori-layanan.store'),
            $this->isian(['nama_ke' => '204', 'biaya' => '99.999']));

        $baru = KategoriLayanan::where('nama_ke', '204')->first();

        $this->assertSame((string) $tarif, (string) $baru->biaya);
        // Tanpa promo, total sama dengan biayanya — itulah angka yang dipakai
        // halaman publik dan laporan.
        $this->assertSame((string) $tarif, (string) $baru->total_biaya);
    }

    #[Test]
    public function menyunting_angkatan_lama_tidak_menyentuh_harga_dan_promonya(): void
    {
        /*
         * Yang paling berbahaya dari "harga ikut tarif induk". Dari 48 angkatan
         * Scopus Camp, 25 di antaranya berharga 4,5jt sementara tarif sekarang
         * 5,5jt. Peserta yang sudah mendaftar membayar harga saat itu, jadi
         * menyunting tanggal atau kuota tidak boleh diam-diam menaikkannya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $lama = $this->kategoriUji([
            'biaya' => '4500000',
            'total_biaya' => '4050000',
            'kode_diskon' => 'RSCHBD',
            'total_kuota' => '20',
            'sisa_kuota' => '17',
        ]);

        $this->actingAs($admin)->post(
            route('account.kategori-layanan.update', $lama),
            $this->isian(['nama' => 'Nama diubah', 'total_kuota' => 25, 'sisa_kuota' => 17])
        );

        $lama->refresh();

        $this->assertSame('Nama diubah', $lama->nama, 'Penyuntingannya memang harus berlaku.');
        $this->assertSame('4500000', (string) $lama->biaya);
        $this->assertSame('4050000', (string) $lama->total_biaya);
        $this->assertSame('RSCHBD', $lama->kode_diskon);
    }

    #[Test]
    public function mencentang_ikuti_tarif_menyetarakan_harga_dan_membersihkan_promonya(): void
    {
        /*
         * Satu-satunya jalan mengubah harga angkatan lama. Promonya ikut
         * disetarakan karena dihitung dari harga lama — dibiarkan, "promo"
         * 4.050.000 jadi lebih murah dari yang seharusnya, atau malah lebih
         * mahal dari harganya sendiri.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $tarif = (int) \App\ClinikScopusBiayaPersesi::berlaku('scopus_camp', 'jawa')->biaya_persesi;

        $lama = $this->kategoriUji([
            'biaya' => '4500000', 'total_biaya' => '4050000', 'kode_diskon' => 'RSCHBD',
        ]);

        $this->actingAs($admin)->post(
            route('account.kategori-layanan.update', $lama),
            $this->isian(['ikuti_tarif' => 1])
        );

        $lama->refresh();

        $this->assertSame((string) $tarif, (string) $lama->biaya);
        $this->assertSame((string) $tarif, (string) $lama->total_biaya);
        $this->assertNull($lama->kode_diskon);
    }

    #[Test]
    public function borang_tidak_lagi_punya_isian_harga_atau_promo(): void
    {
        // Penjaga permintaan 30 Sep 2026: harga ikut tarif, promo jadi fitur
        // sendiri. Kalau isiannya kembali, dua sumber harga hidup berdampingan.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $isi = $this->actingAs($admin)->get(route('account.kategori-layanan.create'))->getContent();

        foreach (['name="biaya"', 'name="total_biaya"', 'name="kode_diskon"'] as $medan) {
            if ($medan === 'name="biaya"') {
                // Yang tersisa hanya isian tersembunyi untuk pratinjau deskripsi.
                $this->assertStringNotContainsString('type="text" class="form-control-modern" id="brg-biaya"', $isi);

                continue;
            }

            $this->assertStringNotContainsString($medan, $isi, "Isian $medan seharusnya sudah tidak ada.");
        }
    }

    #[Test]
    public function tanggal_selesai_sebelum_mulai_ditolak(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.store'),
                $this->isian(['mulai' => '2026-11-08', 'selesai' => '2026-11-06']))
            ->assertSessionHasErrors('selesai');
    }

    #[Test]
    public function layanan_bervarian_wajib_menyebut_variannya(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $this->actingAs($admin)
            ->post(route('account.kategori-layanan.store'),
                $this->isian(['layanan' => 'bibliometrik', 'varian' => null]))
            ->assertSessionHasErrors('varian');
    }

    // --------------------------------------------------------------- perakit

    #[Test]
    public function perakit_mengembalikan_deskripsi_dari_isian_yang_belum_disimpan(): void
    {
        /*
         * Inti fiturnya: admin menekan "Rakit dari cetakan" SEBELUM menyimpan,
         * dan teksnya sudah lengkap dengan tanggal serta harga angkatan ini.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $jawab = $this->actingAs($admin)->postJson(route('account.kategori-layanan.rakit'),
            $this->isian(['mulai' => '2026-10-30', 'selesai' => '2026-11-01', 'biaya' => '5.500.000']));

        $jawab->assertOk()->assertJson(['success' => true]);

        $teks = $jawab->json('deskripsi');

        $this->assertStringContainsString('30 Oktober – 1 November 2026', $teks);
        $this->assertStringContainsString('Rp 5.500.000', $teks);
        $this->assertStringContainsString('Yogyakarta', $teks);
        $this->assertStringNotContainsString('{', $teks, 'Masih ada penanda yang tidak terisi.');
    }

    #[Test]
    public function perakit_bilang_terus_terang_kalau_cetakannya_belum_ada(): void
    {
        // Diam-diam mengembalikan teks kosong membuat admin mengira tombolnya
        // rusak, bukan mengira cetakannya belum diisi.
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        \App\ClinikScopusBiayaPersesi::untuk('online_training')
            ->update(['status' => \App\ClinikScopusBiayaPersesi::NONAKTIF]);

        $jawab = $this->actingAs($admin)->postJson(route('account.kategori-layanan.rakit'),
            $this->isian(['layanan' => 'online_training']));

        $jawab->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString('Tarif layanan', $jawab->json('message'));
    }

    // ------------------------------------------------------------ penghapusan

    #[Test]
    public function angkatan_yang_sudah_punya_pendaftar_tidak_bisa_dihapus(): void
    {
        /*
         * Tabel pendaftaran berkunci asing ke sini; tanpa pemeriksaan ini MySQL
         * menolak dengan galat 1451 yang hanya menyebut nama constraint-nya.
         */
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);

        $terpakai = \Illuminate\Support\Facades\DB::table('scopus_camp_pendaftaran')
            ->whereNotNull('kategori_id')->value('kategori_id');

        if (! $terpakai) {
            $this->markTestSkipped('Tidak ada pendaftaran untuk diuji.');
        }

        $jawab = $this->actingAs($admin)->deleteJson(
            route('account.kategori-layanan.destroy', $terpakai));

        $jawab->assertStatus(409);
        // "peserta", bukan "pendaftar": yang disebut jumlah ORANG, dan satu
        // pendaftaran boleh membawa rombongan.
        $this->assertStringContainsString('peserta', $jawab->json('message'));
        $this->assertNotNull(KategoriLayanan::find($terpakai));
    }

    #[Test]
    public function angkatan_tanpa_pendaftar_boleh_dihapus(): void
    {
        $admin = $this->akun(User::PERAN_ADMINISTRATOR);
        $baru = $this->kategoriUji();

        $this->actingAs($admin)
            ->deleteJson(route('account.kategori-layanan.destroy', $baru))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(KategoriLayanan::find($baru->getKey()));
    }
}
