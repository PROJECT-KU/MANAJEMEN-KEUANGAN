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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Promo bisa ditemukan, dipakai beramai-ramai, dan tidak bisa luput dari mata.
 *
 * Tiga hal yang sebelumnya menuntut orang mengingat sendiri: mencari promo
 * lewat apa yang dipromokan, mematikan beberapa sekaligus, dan tahu bahwa ada
 * promo yang hampir habis tanpa membuka layarnya.
 */
class PromoTerlihatDanMassalTest extends TestCase
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

    // ----------------------------------------------------------- pencarian

    #[Test]
    public function promo_bisa_dicari_lewat_nama_angkatannya(): void
    {
        /*
         * Promo hampir selalu dicari lewat apa yang dipromokan — "mana promo
         * Yogyakarta?" — bukan lewat nama promonya sendiri, yang justru
         * paling sulit diingat. Sebelum ini kata itu memulangkan nol hasil
         * walau ada promo yang menyasar Scopus Camp Yogyakarta.
         */
        $admin = $this->akun();
        $t = Str::random(6);

        $angkatan = $this->angkatan('Scopus Camp Yogyakarta ' . $t);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Tanpa Petunjuk ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => 'Yogyakarta ' . $t]))
            ->assertOk()
            ->assertSee($promo->nama);
    }

    #[Test]
    public function pencarian_tidak_memulangkan_promo_yang_tidak_berkaitan(): void
    {
        /*
         * Arah sebaliknya ikut dijaga: pencarian yang menjangkau relasi mudah
         * jadi terlalu longgar, dan saringan yang memulangkan semuanya sama
         * tidak bergunanya dengan yang memulangkan nol.
         */
        $admin = $this->akun();
        $t = Str::random(6);

        $this->angkatan('Scopus Camp Padang ' . $t);

        $lain = PromoLayanan::create([
            'nama' => 'Promo Lain ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => 'Padang ' . $t]))
            ->assertOk()
            ->assertDontSee($lain->nama);
    }

    // -------------------------------------------------------- aksi massal

    #[Test]
    public function beberapa_promo_bisa_dimatikan_sekaligus(): void
    {
        $admin = $this->akun();
        $promo = collect(range(1, 3))->map(fn () => PromoLayanan::create([
            'nama' => 'Promo Massal ' . Str::random(5), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]));

        $this->actingAs($admin)->post(route('account.promo-layanan.massal'), [
            'id' => $promo->pluck('id')->all(),
            'tindakan' => 'matikan',
        ])->assertRedirect();

        foreach ($promo as $p) {
            $this->assertFalse($p->fresh()->aktif);
        }
    }

    #[Test]
    public function aksi_massal_meninggalkan_jejak_untuk_tiap_promo(): void
    {
        /*
         * Disimpan lewat satu `update()` massal, kait modelnya dilewati dan
         * TIDAK ADA satu baris jejak pun — padahal mematikan dua belas promo
         * sekaligus justru perubahan yang paling perlu dicatat.
         */
        $admin = $this->akun();

        $a = PromoLayanan::create([
            'nama' => 'Promo Jejak A ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);
        $b = PromoLayanan::create([
            'nama' => 'Promo Jejak B ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.massal'), [
            'id' => [$a->id, $b->id], 'tindakan' => 'matikan',
        ]);

        foreach ([$a, $b] as $p) {
            $this->assertSame('dimatikan', $p->jejak()->first()->aksi);
        }
    }

    #[Test]
    public function aksi_massal_tidak_bisa_menghapus(): void
    {
        /*
         * Penghapusan massal berarti satu salah klik menghilangkan belasan
         * promo beserta cakupannya, dan belum ada tong sampah untuk promo.
         */
        $admin = $this->akun();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Aman ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('account.promo-layanan.massal'), [
            'id' => [$promo->id], 'tindakan' => 'hapus',
        ])->assertSessionHasErrors('tindakan');

        $this->assertNotNull(PromoLayanan::find($promo->id));
    }

    #[Test]
    public function pelanggan_tidak_bisa_memakai_aksi_massal(): void
    {
        $promo = PromoLayanan::create([
            'nama' => 'Promo Terkunci ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($this->akun(User::PERAN_PELANGGAN))
            ->post(route('account.promo-layanan.massal'), [
                'id' => [$promo->id], 'tindakan' => 'matikan',
            ])->assertRedirect(route('account.dashboard.index'));

        $this->assertTrue($promo->fresh()->aktif);
    }

    // ----------------------------------------- daftar pendaftar per promo

    #[Test]
    public function daftar_pendaftar_bisa_disaring_ke_satu_promo(): void
    {
        [$admin, $angkatan, $promo] = $this->siapPakai();

        $kena = $this->daftar($admin, $angkatan, 1);

        // Pendaftaran kedua tanpa promo: promonya dimatikan dulu.
        $promo->update(['aktif' => false]);
        PromoAngkatan::lupakan();
        $luput = $this->daftar($admin, $angkatan, 1);

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.index', ['promo' => $promo->id]))
            ->assertOk()
            ->assertSee($kena->nama)
            ->assertDontSee($luput->nama);
    }

    #[Test]
    public function layar_pendaftar_menyebut_promo_yang_sedang_menyaring(): void
    {
        /*
         * Tanpa pita itu, satu-satunya tanda bahwa daftarnya dipersempit
         * adalah jumlah barisnya — dan orang yang menerima tautannya tidak
         * punya cara mengetahui promo mana yang dipakai.
         */
        [$admin, , $promo] = $this->siapPakai();

        $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.index', ['promo' => $promo->id]))
            ->assertOk()
            ->assertSee($promo->nama)
            ->assertSee('Lepas saringan promo');
    }

    #[Test]
    public function saringan_promo_ikut_terbawa_saat_menyaring_hal_lain(): void
    {
        /*
         * Tanpa medan tersembunyi di borang penyaring, saringan promo LEPAS
         * begitu ada yang mengetik di kotak cari, dan daftarnya diam-diam
         * kembali memuat seluruh pendaftar.
         */
        [$admin, , $promo] = $this->siapPakai();

        $isi = $this->actingAs($admin)
            ->get(route('account.pendaftaran-layanan.index', ['promo' => $promo->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString(
            '<input type="hidden" name="promo" value="' . $promo->id . '">',
            $isi,
            'Saringan promo tidak ikut di borang penyaring; ia akan lepas saat saringan lain dipakai.'
        );
    }

    #[Test]
    public function rincian_promo_menautkan_ke_daftar_pendaftarnya(): void
    {
        [$admin, $angkatan, $promo] = $this->siapPakai();

        $this->daftar($admin, $angkatan, 1);
        DampakPromo::lupakan();

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee(route('account.pendaftaran-layanan.index', ['promo' => $promo->id]), false);
    }

    // --------------------------------------------------- angkatan terlewat

    #[Test]
    public function rincian_memperingatkan_angkatan_baru_yang_belum_dicentang(): void
    {
        /*
         * Akibat wajar dari "centang satu per satu": angkatan yang dibuat
         * sesudah promonya diam-diam tetap harga normal, dan yang
         * menyadarinya biasanya pendaftar yang membandingkan harga.
         */
        $admin = $this->akun();
        $lama = $this->angkatan('Angkatan Lama ' . Str::random(5));

        $promo = PromoLayanan::create([
            'nama' => 'Promo Terlewat ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($lama->getKey());

        // Dibuat SESUDAH promonya, layanan sama, tanggalnya masih di depan.
        $baru = $this->angkatan('Angkatan Baru ' . Str::random(5));
        $baru->forceFill(['created_at' => now()->addMinute()])->save();

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee('belum ikut')
            ->assertSee($baru->nama);
    }

    #[Test]
    public function angkatan_yang_sudah_dicentang_tidak_ikut_diperingatkan(): void
    {
        $admin = $this->akun();
        $lama = $this->angkatan('Angkatan Pertama ' . Str::random(5));

        $promo = PromoLayanan::create([
            'nama' => 'Promo Lengkap ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(), 'aktif' => true,
        ]);

        $baru = $this->angkatan('Angkatan Kedua ' . Str::random(5));
        $baru->forceFill(['created_at' => now()->addMinute()])->save();

        $promo->angkatan()->attach([$lama->getKey(), $baru->getKey()]);

        $this->actingAs($admin)
            ->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertDontSee('belum ikut');
    }

    // -------------------------------------------------------------- dasbor

    #[Test]
    public function dasbor_memperingatkan_promo_yang_belum_menunjuk_angkatan(): void
    {
        /*
         * Pengingatnya tinggal di DASBOR, bukan di penjadwal: cron di hosting
         * ini belum pernah terpasang, jadi pengingat terjadwal akan mati
         * tanpa suara dan promo yang kebablasan baru ketahuan dari laporan
         * keuangan.
         */
        $admin = $this->akun();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Melayang ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDays(30)->toDateString(),
            'aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('account.dashboard.index'))
            ->assertOk()
            ->assertSee('belum menunjuk angkatan');
    }

    #[Test]
    public function dasbor_memperingatkan_promo_yang_hampir_habis(): void
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan('Angkatan Dasbor ' . Str::random(5));

        $promo = PromoLayanan::create([
            'nama' => 'Promo Mepet ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDays(5)->toDateString(),
            'selesai' => now()->addDays(2)->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $this->actingAs($admin)->get(route('account.dashboard.index'))
            ->assertOk()
            ->assertSee('tinggal 2 hari');
    }

    #[Test]
    public function dasbor_tidak_memperingatkan_promo_yang_sudah_selesai(): void
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan('Angkatan Sunyi ' . Str::random(5));

        $promo = PromoLayanan::create([
            'nama' => 'Promo Kedaluwarsa ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDays(20)->toDateString(),
            'selesai' => now()->subDay()->toDateString(), 'aktif' => true,
        ]);
        $promo->angkatan()->attach($angkatan->getKey());

        $this->actingAs($admin)->get(route('account.dashboard.index'))
            ->assertOk()
            ->assertDontSee($promo->nama);
    }

    // ------------------------------------------------------- jejak bernomor

    #[Test]
    public function jejak_panjang_dinomori_bukan_dipotong(): void
    {
        /*
         * Dibatasi limit, yang ke-51 hilang tanpa satu pun tanda — dan justru
         * jejak lama yang dicari saat ada yang bertanya "sejak kapan harganya
         * segini?".
         */
        $admin = $this->akun();
        $this->actingAs($admin);

        $promo = PromoLayanan::create([
            'nama' => 'Promo Banyak Jejak ' . Str::random(4), 'tipe' => 'persen', 'persen' => 1,
            'mulai' => now()->toDateString(), 'selesai' => now()->addDay()->toDateString(),
            'aktif' => true,
        ]);

        for ($i = 2; $i <= 20; $i++) {
            $promo->update(['persen' => $i]);
        }

        $this->assertSame(20, $promo->jejak()->count());

        $this->get(route('account.promo-layanan.detail', $promo->id))
            ->assertOk()
            ->assertSee('dari 20 peristiwa');

        // Halaman kedua benar-benar ada isinya.
        $this->get(route('account.promo-layanan.detail', $promo->id) . '?jejak=2')
            ->assertOk()
            ->assertSee('Jejak perubahan');
    }

    // ------------------------------------------------------- ubin ringkasan

    #[Test]
    public function ubin_ringkasan_berjumlah_enam_dan_lajurnya_dipatok(): void
    {
        /*
         * Tujuh ubin tidak pernah terbagi rapi, berapa pun lebarnya. Terukur
         * di 1440px: wadahnya 1130px dan auto-fit hanya memuat ENAM lajur,
         * jadi ubin ketujuh jatuh sendirian di baris kedua dengan petak
         * kosong menganga di sebelahnya.
         *
         * Enam selalu terbagi: satu baris di layar lebar, 3+3 di layar
         * sedang. Dijaga DUA-DUANYA — jumlah ubinnya dan cara lajurnya
         * dipatok — sebab mengembalikan salah satu saja menghidupkan lagi
         * ubin yang menggantung.
         */
        $isi = $this->actingAs($this->akun())
            ->get(route('account.promo-layanan.index'))->assertOk()->getContent();

        /*
         * Ditambatkan ke aria-label, bukan ke kelas "mis-ringkas".
         *
         * Pembungkus gesernya bernama `mis-ringkas-geser`, jadi pola yang
         * mencari `class="mis-ringkas...` menangkap pembungkusnya lebih dulu
         * dan penjaganya merah untuk alasan yang sama sekali bukan yang
         * dimaksud.
         */
        /*
         * Batas akhirnya petunjuk geser, BUKAN `</div>` terdekat: `.*?` yang
         * malas berhenti di penutup div ubin PERTAMA, jadi yang terhitung
         * cuma satu ubin.
         */
        preg_match('/<div class="([^"]*)" aria-label="Ringkasan promo">(.*?)<p class="mis-ringkas-petunjuk"/s', $isi, $kisi);

        $this->assertNotEmpty($kisi, 'Kisi ubin ringkasannya tidak ketemu.');
        $this->assertStringContainsString('mis-ringkas-6', $kisi[1],
            'Kisi ubinnya tidak lagi memakai .mis-ringkas-6; lajurnya kembali ditentukan lebar yang kebetulan tersedia.');
        // Dihitung dari mis-ubin-angka, yang hanya ada SEKALI per ubin.
        // Mencocokkan 'class="mis-ubin' saja ikut menghitung -angka dan
        // -label miliknya, jadi angkanya selalu tiga kali lipat.
        $this->assertSame(6, substr_count($kisi[2], 'mis-ubin-angka'),
            'Jumlah ubinnya bukan enam lagi — selain enam, ada baris yang menyisakan petak kosong.');

        $css = file_get_contents(public_path('assets/css/mis-ui.css'));

        preg_match('/\.mis-ringkas-6\s*\{([^}]*)\}/', $css, $aturan);

        $this->assertNotEmpty($aturan, 'Aturan .mis-ringkas-6 tidak ketemu.');
        $this->assertStringNotContainsString('auto-fit', $aturan[1],
            'Lajurnya kembali auto-fit; jumlah lajurnya akan ditentukan lebar layar, bukan oleh jumlah ubinnya.');
        $this->assertStringContainsString('repeat(3', $aturan[1]);
        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 1400px\)\s*\{\s*\.mis-ringkas-6\s*\{[^}]*repeat\(6/',
            $css,
            'Di layar lebar ubinnya tidak lagi dipatok enam lajur.'
        );
    }

    #[Test]
    public function ubin_bernilai_nol_ditandai_kosong(): void
    {
        /*
         * Lima ubin yang berteriak sama kerasnya memaksa mata membaca
         * semuanya untuk menemukan yang ada isinya. Yang nol diredupkan,
         * BUKAN disembunyikan — ubin yang hilang saat nol membuat letak ubin
         * yang sama berubah-ubah dari satu kunjungan ke kunjungan berikutnya.
         */
        $admin = $this->akun();

        $promo = PromoLayanan::create([
            'nama' => 'Promo Satu-satunya ' . Str::random(4), 'tipe' => 'persen', 'persen' => 10,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(30)->toDateString(), 'aktif' => true,
        ]);

        $isi = $this->actingAs($admin)
            ->get(route('account.promo-layanan.index', ['cari' => $promo->nama]))
            ->assertOk()->getContent();

        preg_match('/aria-label="Ringkasan promo">(.*?)<p class="mis-ringkas-petunjuk"/s', $isi, $kisi);

        $this->assertNotEmpty($kisi, 'Kisi ubin ringkasannya tidak ketemu.');

        // Berjalan = 1, sisanya nol: empat dari enam ubin harus bertanda kosong.
        $this->assertSame(4, substr_count($kisi[1], 'kosong'),
            'Jumlah ubin bertanda kosong tidak cocok dengan jumlah yang angkanya nol.');
    }

    // ------------------------------------------------- pengamat tidak boleh berputar

    #[Test]
    public function pengamat_perubahan_daftar_tidak_mengubah_dom(): void
    {
        /*
         * Bilah aksi massal berada DI DALAM #pro-hasil, wadah yang diamati
         * MutationObserver supaya centangnya bisa dipasang ulang sesudah
         * penyaring hidup menukar isinya.
         *
         * Kalau pemanggilan balik observer itu merakit ulang medan
         * tersembunyinya, ia melahirkan mutasi baru yang memanggil dirinya
         * lagi — tanpa henti. Terukur sebelum dibetulkan: tab peramban
         * menggantung dan skrip pengukur tidak pernah memulangkan apa pun.
         * Tidak ada galat apa pun di konsol.
         *
         * Yang dijaga AKIBATNYA: pemanggilan baliknya tidak boleh menyentuh
         * struktur DOM sama sekali.
         */
        $berkas = file_get_contents(
            base_path('resources/views/account/promo_layanan/index.blade.php')
        );

        preg_match('/new MutationObserver\(([^)]*)\)/', $berkas, $pasang);

        $this->assertNotEmpty($pasang, 'Pengamat perubahan daftarnya tidak ketemu.');
        $this->assertSame('pasangUlang', trim($pasang[1]),
            'Pengamatnya memanggil sesuatu selain pasangUlang; kalau itu mengubah DOM, ia akan memanggil dirinya sendiri tanpa henti.');

        preg_match('/function pasangUlang\(\) \{(.*?)\n        \}/s', $berkas, $isi);

        $this->assertNotEmpty($isi, 'Fungsi pasangUlang tidak ketemu.');

        foreach (['appendChild', 'removeChild', '.remove(', 'innerHTML', 'createElement'] as $ubah) {
            $this->assertStringNotContainsString($ubah, $isi[1],
                'pasangUlang mengubah struktur DOM lewat "' . $ubah . '" — dipanggil dari MutationObserver, itu putaran tanpa henti.');
        }
    }

    // ------------------------------------------------------------ perkakas

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $t = Str::random(8);

        $orang = User::create([
            'full_name' => 'Admin Massal ' . $t, 'username' => 'mss_' . $t,
            'email' => $t . '@contoh.test', 'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $orang->forceFill([
            'status' => 'active', 'email_verified_at' => now(), 'peran' => $peran,
        ])->save();

        return $orang;
    }

    private function angkatan(string $nama): KategoriLayanan
    {
        KategoriLayanan::lupakanPendaftar();

        return KategoriLayanan::create([
            'layanan' => 'scopus_camp', 'nama' => $nama,
            'mulai' => now()->addMonth()->toDateString(),
            'total_kuota' => '50', 'sisa_kuota' => '50', 'status' => 'active',
            'biaya' => '2000000',
        ]);
    }

    /** @return array{0: User, 1: KategoriLayanan, 2: PromoLayanan} */
    private function siapPakai(): array
    {
        $admin = $this->akun();
        $angkatan = $this->angkatan('Angkatan Pakai ' . Str::random(6));

        $promo = PromoLayanan::create([
            'nama' => 'Promo Pakai ' . Str::random(6), 'tipe' => 'rupiah', 'nominal' => 100_000,
            'mulai' => now()->subDay()->toDateString(),
            'selesai' => now()->addDays(7)->toDateString(), 'aktif' => true,
        ]);
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
            'telp' => '0811-0000-0006', 'jumlah' => (string) $jumlah, 'cara_bayar' => 'transfer',
        ], $admin->full_name);

        $this->assertTrue($hasil['berhasil'], 'Pendaftarannya gagal: ' . ($hasil['pesan'] ?? '-'));

        return $hasil['model'];
    }
}
