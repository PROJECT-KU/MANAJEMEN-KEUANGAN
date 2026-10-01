<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\Galeri;
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
class GaleriTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> berkas yang dibuat uji ini, dibersihkan di akhir */
    private array $sampah = [];

    /**
     * @var array<int, string> berkas yang SUDAH ADA sebelum uji ini jalan
     *
     * Dipotret di setUp dan dipakai tearDown sebagai pagar: berkas yang ada di
     * daftar ini TIDAK PERNAH dihapus, apa pun yang masuk $sampah.
     */
    private array $milikOrang = [];

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $this->milikOrang = Galeri::pluck('berkas')->filter()->all();
    }

    protected function tearDown(): void
    {
        /*
         * Berkasnya dibersihkan sendiri. DatabaseTransactions mengembalikan
         * barisnya, TETAPI tidak menyentuh cakram sama sekali — tanpa ini,
         * tiap jalan uji meninggalkan WebP baru di storage pengembang.
         *
         * PAGARNYA: berkas yang sudah ada sebelum uji ini jalan tidak pernah
         * dihapus. Versi pertama uji ini menyapu seluruh baris layanan itu ke
         * dalam $sampah, dan yang ikut terhapus adalah FOTO SUNGGUHAN yang
         * baru saja diunggah orang lewat layar admin — berkasnya hilang, dan
         * barisnya tertinggal menunjuk ke berkas yang tidak ada.
         *
         * Menyaring menurut keterangan sudah memperbaikinya, tetapi itu
         * konvensi yang bisa lupa dipakai uji berikutnya. Pagar ini bekerja
         * walau konvensinya dilanggar.
         */
        foreach ($this->sampah as $jalur) {
            if ($jalur === null || in_array($jalur, $this->milikOrang, true)) {
                continue;
            }

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

    /**
     * Membuat satu foto galeri beserta daftar layanannya.
     *
     * @param  array<string, mixed>  $isi
     * @param  array<int, string>  $layanan
     */
    private function foto(array $isi, array $layanan = []): Galeri
    {
        $g = Galeri::create($isi);
        $g->setLayanan($layanan);

        return $g->refresh();
    }

    private function sesi(string $layanan = 'webinar_eksklusif'): KategoriLayanan
    {
        return KategoriLayanan::create([
            'layanan' => $layanan,
            'token' => Str::random(30),
            'nama' => 'Sesi Galeri ' . Str::random(5),
            'nama_ke' => (string) random_int(6000, 6999),
            'mulai' => now()->addDays(10)->toDateString(),
            'selesai' => now()->addDays(10)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'total_kuota' => '50', 'sisa_kuota' => '50',
            'biaya' => '129000', 'status' => 'active',
            // Varian diisi hanya untuk layanan yang memang punya; create()
            // di sini melewati validasi borang, jadi aman dibiarkan null.
            'varian' => $layanan === 'scopus_camp' ? 'jawa' : null,
        ]);
    }

    // --------------------------------------------------------- layar admin

    #[Test]
    public function layar_galeri_terbuka(): void
    {
        /*
         * Uji yang paling sederhana dan paling sering menyelamatkan: halamannya
         * BISA DIBUKA. Dua kesalahan sekaligus lolos tanpa ini — komponen Blade
         * <x-wajib /> yang tidak ada di proyek ini, dan @extends ke layout
         * milik proyek lain. Keduanya galat 500, dan keduanya hanya ketahuan
         * saat halamannya benar-benar dibuka orang.
         */
        $this->actingAs($this->akun())
            ->get(route('account.galeri.index'))
            ->assertOk()
            ->assertSee('Galeri foto');
    }

    #[Test]
    public function orang_luar_tidak_bisa_membuka_galeri(): void
    {
        $pelanggan = $this->akun();
        $pelanggan->forceFill(['peran' => User::PERAN_PELANGGAN])->save();

        $this->actingAs($pelanggan->refresh())
            ->get(route('account.galeri.index'))
            ->assertRedirect();
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

    // ------------------------------------------------- putaran EXIF & HEIC

    /**
     * Membuat JPEG yang BERTANDA miring, seperti foto dari ponsel.
     *
     * Penanda EXIF-nya ditulis dengan merakit blok APP1 sendiri — tanpa
     * perpustakaan tambahan, dan isinya cuma satu medan Orientation.
     */
    private function jpegMiring(int $arah, int $l = 800, int $t = 400): string
    {
        $im = imagecreatetruecolor($l, $t);
        imagefill($im, 0, 0, imagecolorallocate($im, 20, 40, 90));
        // Penanda di sudut KIRI ATAS, supaya putarannya bisa dibuktikan.
        imagefilledrectangle($im, 0, 0, (int) ($l / 8), (int) ($t / 8),
            imagecolorallocate($im, 255, 200, 0));

        $jalur = sys_get_temp_dir() . '/exif-' . Str::random(8) . '.jpg';
        imagejpeg($im, $jalur, 92);
        imagedestroy($im);

        // APP1/Exif: TIFF little-endian, satu IFD berisi Orientation (0x0112).
        $tiff = "II\x2a\x00\x08\x00\x00\x00"
            . "\x01\x00"
            . "\x12\x01\x03\x00\x01\x00\x00\x00" . pack('v', $arah) . "\x00\x00"
            . "\x00\x00\x00\x00";

        $app1 = "Exif\x00\x00" . $tiff;
        $blok = "\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1;

        $isi = file_get_contents($jalur);
        file_put_contents($jalur, substr($isi, 0, 2) . $blok . substr($isi, 2));

        return $jalur;
    }

    /**
     * Di sudut mana penanda kuningnya berada.
     *
     * Memeriksa POSISI, bukan cuma ukuran. Versi pertama uji ini hanya
     * membandingkan 800x400 jadi 400x800 — dan itu lulus baik diputar searah
     * maupun berlawanan jarum jam, jadi ia tidak membuktikan apa pun tentang
     * arahnya.
     */
    private function sudutPenanda(string $isi): array
    {
        $im = imagecreatefromstring($isi);
        $l = imagesx($im);
        $t = imagesy($im);
        $di = [];

        foreach ([
            ['kiri-atas', 6, 6], ['kanan-atas', $l - 7, 6],
            ['kiri-bawah', 6, $t - 7], ['kanan-bawah', $l - 7, $t - 7],
        ] as [$nama, $x, $y]) {
            $c = imagecolorsforindex($im, imagecolorat($im, $x, $y));

            if ($c['red'] > 200 && $c['green'] > 150 && $c['blue'] < 120) {
                $di[] = $nama;
            }
        }

        imagedestroy($im);

        return $di;
    }

    #[Test]
    public function foto_bertanda_miring_TIDAK_diputar_sistem(): void
    {
        /*
         * Sistem sengaja tidak memutar sendiri, atas permintaan pemiliknya.
         *
         * Alasannya terbukti di lapangan: foto yang sudah melewati WhatsApp
         * atau alat ekspor lain kehilangan penanda EXIF-nya sementara pikselnya
         * tetap miring. Dari berkas seperti itu tidak ada yang bisa ditebak,
         * dan menebak berarti sebagian foto justru dimiringkan oleh sistem.
         *
         * Gantinya tombol putar manual — diuji di bawah.
         */
        $jalur = $this->jpegMiring(6, 800, 400);

        $hasil = app(Gambar::class)->simpan(
            new UploadedFile($jalur, basename($jalur), 'image/jpeg', null, true),
            'uji/galeri'
        );
        $this->sampah[] = $hasil;

        $tentang = getimagesizefromstring(Storage::disk(Gambar::CAKRAM)->get($hasil));

        $this->assertSame(800, $tentang[0], 'Ukurannya harus tetap seperti aslinya.');
        $this->assertSame(400, $tentang[1]);
    }

    #[Test]
    public function tombol_putar_memutar_isinya_searah_jarum_jam(): void
    {
        // Penanda di KIRI-ATAS; sesudah diputar 90 searah jarum jam ia harus
        // pindah ke KANAN-ATAS.
        $jalur = $this->jpegMiring(1, 400, 200);

        $berkas = app(Gambar::class)->simpan(
            new UploadedFile($jalur, basename($jalur), 'image/jpeg', null, true),
            'uji/galeri'
        );
        $this->sampah[] = $berkas;

        $this->assertSame(
            ['kiri-atas'],
            $this->sudutPenanda(Storage::disk(Gambar::CAKRAM)->get($berkas)),
            'Sebelum diputar, penandanya di kiri-atas.'
        );

        $g = $this->foto(['berkas' => $berkas], ['webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.putar', $g), ['derajat' => 90])
            ->assertOk();

        $isi = Storage::disk(Gambar::CAKRAM)->get($berkas);
        $tentang = getimagesizefromstring($isi);

        $this->assertSame(200, $tentang[0], 'Lebarnya jadi 200 sesudah diputar.');
        $this->assertSame(400, $tentang[1]);
        $this->assertSame(['kanan-atas'], $this->sudutPenanda($isi));
    }

    #[Test]
    public function tombol_putar_ke_kiri_arahnya_berlawanan(): void
    {
        $jalur = $this->jpegMiring(1, 400, 200);

        $berkas = app(Gambar::class)->simpan(
            new UploadedFile($jalur, basename($jalur), 'image/jpeg', null, true),
            'uji/galeri'
        );
        $this->sampah[] = $berkas;

        $g = $this->foto(['berkas' => $berkas], ['webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.putar', $g), ['derajat' => -90])
            ->assertOk();

        // Kiri-atas diputar BERLAWANAN jarum jam mendarat di kiri-bawah.
        $this->assertSame(
            ['kiri-bawah'],
            $this->sudutPenanda(Storage::disk(Gambar::CAKRAM)->get($berkas))
        );
    }

    #[Test]
    public function putaran_selain_sembilan_puluh_ditolak(): void
    {
        $g = $this->foto(['berkas' => 'uji/apa-saja.webp'], ['webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.putar', $g), ['derajat' => 37])
            ->assertStatus(422);
    }

    #[Test]
    public function memutar_berkas_yang_hilang_dijawab_terus_terang(): void
    {
        // 409 beserta kalimatnya, bukan 500: berkas yang hilang itu keadaan
        // yang memang mungkin, bukan kerusakan program.
        $g = $this->foto(['berkas' => 'uji/tidak-ada-sama-sekali.webp'], ['webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.putar', $g), ['derajat' => 90])
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function heic_ikut_dikonversi_jadi_webp(): void
    {
        $gambar = app(Gambar::class);

        if (! $gambar->bisaHeic()) {
            $this->markTestSkipped('Peladen ini belum bisa membaca HEIC.');
        }

        $jalur = $this->berkasHeic();

        if ($jalur === null) {
            $this->markTestSkipped('Tidak bisa membuat berkas HEIC contoh di mesin ini.');
        }

        $hasil = $gambar->simpan(
            new UploadedFile($jalur, basename($jalur), 'image/heic', null, true),
            'uji/galeri'
        );
        $this->sampah[] = $hasil;

        $this->assertNotNull($hasil, 'HEIC harus bisa dibaca dan dikonversi.');
        $this->assertStringEndsWith('.webp', $hasil);

        $isi = Storage::disk(Gambar::CAKRAM)->get($hasil);
        $this->assertSame('WEBP', substr($isi, 8, 4), 'Hasilnya harus benar-benar WebP.');
        $this->assertNotNull(getimagesizefromstring($isi));
    }

    /** Berkas HEIC contoh; null kalau mesin ini tidak bisa membuatnya. */
    private function berkasHeic(): ?string
    {
        $sumber = sys_get_temp_dir() . '/heic-sumber-' . Str::random(6) . '.jpg';
        $tujuan = sys_get_temp_dir() . '/heic-uji-' . Str::random(6) . '.heic';

        $im = imagecreatetruecolor(600, 400);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 80, 20));
        imagejpeg($im, $sumber);
        imagedestroy($im);

        @exec('command -v sips 2>/dev/null', $ada, $kode);

        if ($kode === 0) {
            @exec(sprintf('sips -s format heic %s --out %s 2>/dev/null',
                escapeshellarg($sumber), escapeshellarg($tujuan)));
        }

        @unlink($sumber);

        return is_file($tujuan) ? $tujuan : null;
    }

    // -------------------------------------------------------------- galeri

    #[Test]
    public function mengunggah_beberapa_foto_sekaligus(): void
    {
        $jawab = $this->actingAs($this->akun())
            ->post(route('account.galeri.store'), [
                'layanan' => ['webinar_eksklusif', 'scopus_camp'],
                'keterangan' => 'Suasana sesi uji',
                'berkas' => [$this->berkasPng(600, 400), $this->berkasPng(600, 400)],
            ]);

        $jawab->assertRedirect();

        /*
         * Disaring menurut keterangannya, BUKAN seluruh baris: galeri sungguhan
         * di basis data pengembang ikut terhitung, dan ujinya merah karena data
         * yang sama sekali tidak ada hubungannya.
         */
        $foto = Galeri::where('keterangan', 'Suasana sesi uji')->get();

        $this->sampah = array_merge($this->sampah, $foto->pluck('berkas')->all());

        $this->assertCount(2, $foto);

        foreach ($foto as $g) {
            $this->assertStringEndsWith('.webp', $g->berkas);
            $this->assertNotNull($g->alamat, 'Alamatnya harus bisa dirakit.');
            $this->assertSame('Suasana sesi uji', $g->keterangan);

            // SATU foto, DUA layanan — bukan dua baris yang disalin.
            $this->assertSame(['scopus_camp', 'webinar_eksklusif'], $g->daftar_layanan);
            $this->assertFalse($g->semua_layanan);
        }

        // Urutannya berbeda, supaya tidak berebut tempat pertama.
        $this->assertNotSame($foto[0]->urutan, $foto[1]->urutan);
    }

    #[Test]
    public function foto_umum_dan_foto_sesi_sama_sama_tampil(): void
    {
        $sesi = $this->sesi();
        $lain = $this->sesi();

        $umum = $this->foto(['berkas' => 'uji/umum.webp', 'urutan' => 5], ['webinar_eksklusif']);
        $milikSesi = $this->foto(
            ['berkas' => 'uji/sesi.webp', 'urutan' => 9, 'kategori_id' => $sesi->getKey()],
            ['webinar_eksklusif']
        );
        $milikLain = $this->foto(
            ['berkas' => 'uji/lain.webp', 'urutan' => 1, 'kategori_id' => $lain->getKey()],
            ['webinar_eksklusif']
        );

        $tampil = Galeri::untukAngkatan($sesi)->pluck('id')->all();

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

        $mati = $this->foto(['berkas' => 'uji/mati.webp', 'aktif' => false], ['webinar_eksklusif']);

        // Diperiksa FOTONYA SENDIRI, bukan "galerinya kosong": galeri sungguhan
        // di basis data pengembang ikut terhitung, dan ujinya merah karena data
        // yang sama sekali tidak ada hubungannya.
        $this->assertNotContains($mati->id, Galeri::untukAngkatan($sesi)->pluck('id')->all());
    }

    #[Test]
    public function menghapus_foto_ikut_membuang_berkasnya(): void
    {
        $jalur = app(Gambar::class)->simpan($this->berkasPng(400, 300), 'uji/galeri');

        $g = $this->foto(['berkas' => $jalur], ['webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->deleteJson(route('account.galeri.destroy', $g))
            ->assertOk();

        $this->assertNull(Galeri::find($g->id));
        $this->assertFalse(Storage::disk(Gambar::CAKRAM)->exists($jalur));
    }

    #[Test]
    public function berkas_yang_dipakai_bersama_tidak_ikut_terhapus(): void
    {
        // Satu berkas boleh ditunjuk dua baris; menghapus salah satunya tidak
        // boleh mengosongkan yang lain.
        $jalur = app(Gambar::class)->simpan($this->berkasPng(400, 300), 'uji/galeri');
        $this->sampah[] = $jalur;

        $satu = $this->foto(['berkas' => $jalur], ['webinar_eksklusif']);
        $this->foto(['berkas' => $jalur], ['scopus_camp']);

        $this->actingAs($this->akun())
            ->deleteJson(route('account.galeri.destroy', $satu))
            ->assertOk();

        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalur));
    }

    // ------------------------------------------------- dipakai lintas layanan

    #[Test]
    public function satu_foto_bisa_dipakai_beberapa_layanan(): void
    {
        /*
         * SATU baris, dua layanan — bukan dua baris yang isinya disalin.
         * Disalin, mengganti keterangannya harus dua kali, dan salah satunya
         * pasti akan terlupa.
         */
        $camp = $this->sesi('scopus_camp');
        $webinar = $this->sesi('webinar_eksklusif');

        $g = $this->foto(['berkas' => 'uji/berdua.webp'], ['scopus_camp', 'webinar_eksklusif']);

        $this->assertContains($g->id, Galeri::untukAngkatan($camp)->pluck('id')->all());
        $this->assertContains($g->id, Galeri::untukAngkatan($webinar)->pluck('id')->all());
    }

    #[Test]
    public function foto_semua_layanan_tampil_di_layanan_mana_pun(): void
    {
        $g = $this->foto(['berkas' => 'uji/semua.webp', 'semua_layanan' => true], []);

        foreach (['scopus_camp', 'webinar_eksklusif', 'bibliometrik'] as $kode) {
            $this->assertContains(
                $g->id,
                Galeri::untukAngkatan($this->sesi($kode))->pluck('id')->all(),
                'Foto "semua layanan" harus tampil di ' . $kode
            );
        }

        $this->assertSame('Semua layanan', $g->sebut_layanan);
    }

    #[Test]
    public function foto_yang_layanannya_tidak_cocok_tidak_tampil(): void
    {
        $g = $this->foto(['berkas' => 'uji/camp-saja.webp'], ['scopus_camp']);

        $this->assertNotContains(
            $g->id,
            Galeri::untukAngkatan($this->sesi('webinar_eksklusif'))->pluck('id')->all()
        );
    }

    #[Test]
    public function unggahan_tanpa_layanan_ditolak(): void
    {
        /*
         * Tanpa penjagaan ini, foto tersimpan rapi tetapi tidak pernah muncul
         * di halaman mana pun — dan yang mengunggahnya tidak punya petunjuk
         * kenapa.
         */
        $sebelum = Galeri::count();

        $this->actingAs($this->akun())
            ->post(route('account.galeri.store'), [
                'keterangan' => 'Tanpa layanan',
                'berkas' => [$this->berkasPng(400, 300)],
            ])
            ->assertSessionHas('error');

        $this->assertSame($sebelum, Galeri::count());
    }

    #[Test]
    public function daftar_layanan_bisa_diubah_belakangan(): void
    {
        $g = $this->foto(['berkas' => 'uji/ubah.webp'], ['scopus_camp']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.update', $g), [
                'layanan' => ['webinar_eksklusif', 'bibliometrik'],
            ])
            ->assertOk();

        $this->assertSame(['bibliometrik', 'webinar_eksklusif'], $g->fresh()->daftar_layanan);
    }

    #[Test]
    public function mengubah_keterangan_tidak_mencabut_layanannya(): void
    {
        /*
         * Isian di layar dikirim sendiri-sendiri saat ditinggalkan. Kalau
         * daftar layanan ikut ditimpa tiap kali, mengetik satu huruf di
         * keterangan akan mencabut seluruh layanan fotonya.
         */
        $g = $this->foto(['berkas' => 'uji/jaga.webp'], ['scopus_camp', 'webinar_eksklusif']);

        $this->actingAs($this->akun())
            ->postJson(route('account.galeri.update', $g), ['keterangan' => 'Keterangan baru'])
            ->assertOk();

        $segar = $g->fresh();

        $this->assertSame('Keterangan baru', $segar->keterangan);
        $this->assertSame(['scopus_camp', 'webinar_eksklusif'], $segar->daftar_layanan);
    }

    #[Test]
    public function layanan_yang_tidak_dikenal_diabaikan(): void
    {
        // Nilainya datang dari borang; kode karangan tidak boleh tersimpan.
        $g = $this->foto(['berkas' => 'uji/karangan.webp'], ['scopus_camp', 'layanan_karangan']);

        $this->assertSame(['scopus_camp'], $g->daftar_layanan);
    }

    #[Test]
    public function menghapus_foto_ikut_membuang_baris_sambungannya(): void
    {
        $g = $this->foto(['berkas' => 'uji/sambung.webp'], ['scopus_camp', 'webinar_eksklusif']);
        $id = $g->id;

        $g->delete();

        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('galeri_layanan')
            ->where('galeri_id', $id)->count());
    }

    // ----------------------------------------------------------------- API

    #[Test]
    public function galeri_ikut_dikirim_api(): void
    {
        $sesi = $this->sesi();
        $jalur = app(Gambar::class)->simpan($this->berkasPng(600, 400), 'uji/galeri');
        $this->sampah[] = $jalur;

        $this->foto(
            ['berkas' => $jalur, 'kategori_id' => $sesi->getKey(), 'keterangan' => 'Foto uji API'],
            ['webinar_eksklusif']
        );

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

        $hilang = $this->foto(
            ['berkas' => 'uji/tidak-ada-sama-sekali.webp', 'kategori_id' => $sesi->getKey()],
            ['webinar_eksklusif']
        );

        // Yang diperiksa: berkas INI tidak ikut terkirim — bukan bahwa
        // galerinya kosong, sebab foto lain yang sah boleh saja ada.
        $alamat = collect($this->getJson('/api/webinar-eksklusif')->json('sesi.galeri'))
            ->pluck('gambar')->all();

        foreach ($alamat as $a) {
            $this->assertStringNotContainsString('tidak-ada-sama-sekali', (string) $a);
        }

        $this->assertNotNull($hilang->id);
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
