<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\GaleriLayanan;
use App\KategoriLayanan;
use App\Layanan;
use App\Services\Gambar;
use App\Support\AlamatGambar;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Galeri layanan: unggahan jadi WebP di storage, berkas aslinya dihapus.
 */
class GaleriLayananTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> berkas yang dibuat uji ini, dibersihkan di akhir */
    private array $sampah = [];

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    protected function tearDown(): void
    {
        /*
         * Berkasnya dibersihkan sendiri. DatabaseTransactions mengembalikan
         * barisnya, TETAPI tidak menyentuh cakram sama sekali — tanpa ini,
         * tiap jalan uji meninggalkan WebP baru di storage pengembang.
         */
        foreach ($this->sampah as $jalur) {
            Storage::disk(Gambar::CAKRAM)->delete($jalur);
        }

        parent::tearDown();
    }

    private function akun(): User
    {
        $u = User::create([
            'full_name' => 'Uji Galeri',
            'username' => 'uji_galeri_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill([
            'status' => 'active', 'email_verified_at' => now(),
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $u->refresh();
    }

    /** Berkas PNG sungguhan, bukan UploadedFile::fake()->image(). */
    private function berkasPng(int $l = 1800, int $t = 1200): UploadedFile
    {
        /*
         * Gambar SUNGGUHAN, bukan ::fake()->image(): yang palsu isinya bukan
         * PNG yang bisa dibaca GD, jadi konversinya akan selalu gagal dan uji
         * ini tidak membuktikan apa pun.
         */
        $im = imagecreatetruecolor($l, $t);
        imagefill($im, 0, 0, imagecolorallocate($im, 30, 60, 114));
        imagefilledellipse($im, (int) ($l / 2), (int) ($t / 2), 200, 200,
            imagecolorallocate($im, 255, 140, 0));

        $jalur = sys_get_temp_dir() . '/uji-galeri-' . Str::random(8) . '.png';
        imagepng($im, $jalur);
        imagedestroy($im);

        return new UploadedFile($jalur, basename($jalur), 'image/png', null, true);
    }

    private function sesi(): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => 'webinar_eksklusif',
            'token' => Str::random(30),
            'nama' => 'Sesi Galeri ' . Str::random(5),
            'nama_ke' => (string) random_int(6000, 6999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'total_kuota' => '50', 'sisa_kuota' => '50',
            'biaya' => '129000', 'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------ konversi

    #[Test]
    public function unggahan_jadi_webp_di_storage_dan_berkas_aslinya_dihapus(): void
    {
        $berkas = $this->berkasPng();
        $jalurAsli = $berkas->getRealPath();

        $this->assertFileExists($jalurAsli);

        $jalur = app(Gambar::class)->simpan($berkas, 'uji/galeri');
        $this->sampah[] = $jalur;

        $this->assertNotNull($jalur);
        $this->assertStringEndsWith('.webp', $jalur, 'Yang keluar harus WebP.');
        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalur));

        // Berkas aslinya DIHAPUS — itu yang diminta, dan tanpa ini tiap
        // unggahan meninggalkan dua salinan.
        $this->assertFileDoesNotExist($jalurAsli);

        // Isinya memang WebP, bukan sekadar bernama .webp.
        $isi = Storage::disk(Gambar::CAKRAM)->get($jalur);
        $this->assertSame('WEBP', substr($isi, 8, 4));
    }

    #[Test]
    public function gambar_yang_kelewat_lebar_diperkecil(): void
    {
        // 1800px diunggah, padahal yang terpakai di layar paling lebar ~900px.
        $jalur = app(Gambar::class)->simpan($this->berkasPng(1800, 1200), 'uji/galeri');
        $this->sampah[] = $jalur;

        $tentang = getimagesizefromstring(Storage::disk(Gambar::CAKRAM)->get($jalur));

        $this->assertSame(Gambar::LEBAR_MAKS, $tentang[0]);
        // Sebandingnya dijaga: 1800x1200 jadi LEBAR_MAKS x (2/3 LEBAR_MAKS).
        $this->assertSame((int) round(Gambar::LEBAR_MAKS * 2 / 3), $tentang[1]);
    }

    #[Test]
    public function gambar_yang_sudah_kecil_tidak_dibesarkan(): void
    {
        $jalur = app(Gambar::class)->simpan($this->berkasPng(400, 300), 'uji/galeri');
        $this->sampah[] = $jalur;

        $tentang = getimagesizefromstring(Storage::disk(Gambar::CAKRAM)->get($jalur));

        $this->assertSame(400, $tentang[0]);
    }

    #[Test]
    public function berkas_yang_bukan_gambar_mengembalikan_null_bukan_galat(): void
    {
        /*
         * null, bukan pengecualian: satu berkas rusak di tengah dua belas
         * unggahan tidak boleh menggagalkan borang yang sudah susah payah
         * diisi. Pemanggilnya yang memutuskan apa artinya.
         */
        $jalur = sys_get_temp_dir() . '/bukan-gambar-' . Str::random(6) . '.png';
        file_put_contents($jalur, 'ini bukan gambar sama sekali');

        $hasil = app(Gambar::class)->simpan(
            new UploadedFile($jalur, basename($jalur), 'image/png', null, true),
            'uji/galeri'
        );

        $this->assertNull($hasil);

        @unlink($jalur);
    }

    #[Test]
    public function hanya_berkas_di_dalam_folder_yang_diizinkan_yang_dihapus(): void
    {
        /*
         * Penjagaan yang paling penting di layanan ini: tanpa batas folder,
         * satu jalur yang salah dari pemanggil bisa menghapus berkas mana pun
         * yang bisa dijangkau proses PHP.
         */
        $luar = base_path('composer.json');

        $this->assertFileExists($luar);
        $this->assertNull(app(Gambar::class)->dariJalur($luar, 'uji/galeri'));
        $this->assertFileExists($luar, 'Berkas di luar folder unggahan tidak boleh tersentuh.');
    }

    // -------------------------------------------------------------- galeri

    #[Test]
    public function mengunggah_beberapa_foto_sekaligus(): void
    {
        $jawab = $this->actingAs($this->akun())
            ->post(route('account.galeri-layanan.store'), [
                'layanan' => 'webinar_eksklusif',
                'keterangan' => 'Suasana sesi uji',
                'berkas' => [$this->berkasPng(600, 400), $this->berkasPng(600, 400)],
            ]);

        $jawab->assertRedirect();

        $foto = GaleriLayanan::where('layanan', 'webinar_eksklusif')->get();
        $this->sampah = array_merge($this->sampah, $foto->pluck('berkas')->all());

        $this->assertCount(2, $foto);

        foreach ($foto as $g) {
            $this->assertStringEndsWith('.webp', $g->berkas);
            $this->assertNotNull($g->alamat, 'Alamatnya harus bisa dirakit.');
            $this->assertSame('Suasana sesi uji', $g->keterangan);
        }

        // Urutannya berbeda, supaya tidak berebut tempat pertama.
        $this->assertNotSame($foto[0]->urutan, $foto[1]->urutan);
    }

    #[Test]
    public function foto_umum_dan_foto_sesi_sama_sama_tampil(): void
    {
        $sesi = $this->sesi();
        $lain = $this->sesi();

        $umum = GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'berkas' => 'uji/umum.webp', 'urutan' => 5,
        ]);
        $milikSesi = GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'kategori_id' => $sesi->getKey(),
            'berkas' => 'uji/sesi.webp', 'urutan' => 9,
        ]);
        $milikLain = GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'kategori_id' => $lain->getKey(),
            'berkas' => 'uji/lain.webp', 'urutan' => 1,
        ]);

        $tampil = GaleriLayanan::untukAngkatan($sesi)->pluck('id')->all();

        $this->assertContains($milikSesi->id, $tampil);
        $this->assertContains($umum->id, $tampil, 'Foto umum ikut supaya sesi baru tidak kosong.');
        $this->assertNotContains($milikLain->id, $tampil, 'Foto sesi lain tidak boleh ikut.');

        // Dokumentasi sesi ITU didahulukan walau urutannya lebih besar.
        $this->assertSame($milikSesi->id, $tampil[0]);
    }

    #[Test]
    public function foto_nonaktif_tidak_tampil(): void
    {
        $sesi = $this->sesi();

        GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'berkas' => 'uji/mati.webp', 'aktif' => false,
        ]);

        $this->assertCount(0, GaleriLayanan::untukAngkatan($sesi)->get());
    }

    #[Test]
    public function menghapus_foto_ikut_membuang_berkasnya(): void
    {
        $jalur = app(Gambar::class)->simpan($this->berkasPng(400, 300), 'uji/galeri');

        $g = GaleriLayanan::create(['layanan' => 'webinar_eksklusif', 'berkas' => $jalur]);

        $this->actingAs($this->akun())
            ->deleteJson(route('account.galeri-layanan.destroy', $g))
            ->assertOk();

        $this->assertNull(GaleriLayanan::find($g->id));
        $this->assertFalse(Storage::disk(Gambar::CAKRAM)->exists($jalur));
    }

    #[Test]
    public function berkas_yang_dipakai_bersama_tidak_ikut_terhapus(): void
    {
        // Satu berkas boleh ditunjuk dua baris; menghapus salah satunya tidak
        // boleh mengosongkan yang lain.
        $jalur = app(Gambar::class)->simpan($this->berkasPng(400, 300), 'uji/galeri');
        $this->sampah[] = $jalur;

        $satu = GaleriLayanan::create(['layanan' => 'webinar_eksklusif', 'berkas' => $jalur]);
        GaleriLayanan::create(['layanan' => 'scopus_camp', 'berkas' => $jalur]);

        $this->actingAs($this->akun())
            ->deleteJson(route('account.galeri-layanan.destroy', $satu))
            ->assertOk();

        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalur));
    }

    // ----------------------------------------------------------------- API

    #[Test]
    public function galeri_ikut_dikirim_api(): void
    {
        $sesi = $this->sesi();
        $jalur = app(Gambar::class)->simpan($this->berkasPng(600, 400), 'uji/galeri');
        $this->sampah[] = $jalur;

        GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'kategori_id' => $sesi->getKey(),
            'berkas' => $jalur, 'keterangan' => 'Foto uji API',
        ]);

        $galeri = $this->getJson('/api/webinar-eksklusif')->assertOk()->json('sesi.galeri');

        $this->assertNotEmpty($galeri);
        $this->assertSame('Foto uji API', $galeri[0]['keterangan']);
        $this->assertStringContainsString('.webp', $galeri[0]['gambar']);
    }

    #[Test]
    public function foto_yang_berkasnya_hilang_tidak_dikirim_api(): void
    {
        // Dibuang di peladen, bukan dikirim sebagai null: halaman landing tidak
        // perlu tahu bahwa ada yang hilang.
        $sesi = $this->sesi();

        GaleriLayanan::create([
            'layanan' => 'webinar_eksklusif', 'kategori_id' => $sesi->getKey(),
            'berkas' => 'uji/tidak-ada-sama-sekali.webp',
        ]);

        $this->assertSame([], $this->getJson('/api/webinar-eksklusif')->json('sesi.galeri'));
    }

    // ------------------------------------------------- alamat bentuk lama

    #[Test]
    public function nilai_bentuk_lama_masih_bisa_dibuka(): void
    {
        /*
         * Jalur mundur yang menjaga halaman publik selama jeda antara deploy
         * kode dan perintah konversi dijalankan di peladen. Tanpa ini, seluruh
         * sampul hilang selama jeda itu.
         */
        $berkas = public_path('ScopusCamp/uji-bentuk-lama.jpg');
        @mkdir(dirname($berkas), 0755, true);
        copy(public_path('assets/img/avatar/avatar-1.png'), $berkas);

        $this->assertNotNull(AlamatGambar::url('ScopusCamp/uji-bentuk-lama.jpg', 'ScopusCamp'));
        $this->assertNotNull(AlamatGambar::url('uji-bentuk-lama.jpg', 'ScopusCamp'));
        $this->assertFalse(AlamatGambar::diStorage('ScopusCamp/uji-bentuk-lama.jpg'));

        @unlink($berkas);
    }

    #[Test]
    public function nilai_yang_berkasnya_tidak_ada_mengembalikan_null(): void
    {
        // null, bukan alamat yang menunjuk berkas tidak ada: kolom terisi yang
        // berkasnya hilang membuat halaman menampilkan gambar rusak.
        $this->assertNull(AlamatGambar::url('ScopusCamp/tidak-ada-sama-sekali.jpg', 'ScopusCamp'));
        $this->assertNull(AlamatGambar::url(null));
        $this->assertNull(AlamatGambar::url(''));
    }
}
