<?php

namespace Tests\Feature\Kategori;

use App\KategoriLayanan;
use App\Layanan;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tambahan Angkatan Layanan dari audit putaran kedua, 30 Sep 2026:
 * sampul, ubah status massal, halaman rincian, nomor angkatan berikutnya,
 * dan pembersihan HTML pada deskripsi.
 *
 * Unggahan sampul BENAR-BENAR menulis ke public/, sebab halaman publik membaca
 * berkasnya dari sana. Jadi tiap uji yang mengunggah menyapu berkasnya sendiri
 * di akhir — kalau tidak, repo kebanjiran berkas uji.
 */
class AngkatanAuditDuaTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> */
    private array $sampah = [];

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
    }

    protected function tearDown(): void
    {
        foreach ($this->sampah as $berkas) {
            if (is_file($berkas)) {
                unlink($berkas);
            }
        }

        parent::tearDown();
    }

    private function akun(string $peran = User::PERAN_ADMINISTRATOR): User
    {
        $u = User::create([
            'full_name' => 'Uji ' . Str::random(4),
            'username' => 'uji_ang3_' . Str::random(8),
            'email' => Str::random(8) . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $u->forceFill(['status' => 'active', 'email_verified_at' => now(), 'peran' => $peran])->save();

        return $u->refresh();
    }

    private function angkatan(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'token' => Str::random(30), 'nama' => 'Angkatan Uji ' . Str::random(4),
            'nama_ke' => '7', 'mulai' => now()->addMonth(),
            'selesai' => now()->addMonth()->addDays(2), 'lokasi' => 'Yogyakarta',
            'total_kuota' => '20', 'sisa_kuota' => '20',
            'biaya' => '5500000', 'total_biaya' => '5500000',
            'desc' => 'Isi pengumuman', 'status' => 'active',
        ], $lain));
    }

    /** @return array<string, mixed> */
    private function isian(array $lain = []): array
    {
        return array_merge([
            'layanan' => 'scopus_camp', 'varian' => 'jawa',
            'nama' => 'Angkatan Sampul ' . Str::random(4), 'nama_ke' => '900',
            'mulai' => now()->addMonths(2)->toDateString(),
            'selesai' => now()->addMonths(2)->addDays(2)->toDateString(),
            'lokasi' => 'Bandung', 'total_kuota' => '20', 'sisa_kuota' => '20',
            'ikuti_tarif' => '1', 'status' => 'draft',
        ], $lain);
    }

    // ------------------------------------------------------------- sampul

    #[Test]
    public function sampul_tersimpan_di_folder_layanannya(): void
    {
        $this->actingAs($this->akun());

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'gambar' => UploadedFile::fake()->image('flyer.jpg', 600, 800),
        ]))->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '900')->firstOrFail();
        $this->sampah[] = public_path($baru->gambar);

        // Bukan sekadar "ada isinya": folder Scopus Camp memang ScopusCamp/,
        // dan halaman publiknya membaca dari sana lewat basename().
        $this->assertStringStartsWith('ScopusCamp/', $baru->gambar);
        $this->assertFileExists(public_path($baru->gambar));
        $this->assertSame(asset($baru->gambar), $baru->alamat_sampul);
    }

    #[Test]
    public function sampul_bibliometrik_masuk_folder_sendiri(): void
    {
        $this->actingAs($this->akun());

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'layanan' => 'bibliometrik', 'varian' => 'online', 'lokasi' => null,
            'nama' => 'Biblio Sampul ' . Str::random(4), 'nama_ke' => '901',
            'gambar' => UploadedFile::fake()->image('flyer.png'),
        ]))->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '901')->firstOrFail();
        $this->sampah[] = public_path($baru->gambar);

        $this->assertStringStartsWith('bibliometrik/', $baru->gambar);
    }

    #[Test]
    public function berkas_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->akun());

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '902',
            'gambar' => UploadedFile::fake()->create('pengumuman.pdf', 40, 'application/pdf'),
        ]))->assertSessionHasErrors('gambar');

        $this->assertNull(KategoriLayanan::where('nama_ke', '902')->first());
    }

    #[Test]
    public function sampul_lama_dibuang_saat_diganti(): void
    {
        $this->actingAs($this->akun());
        $angkatan = $this->angkatan();

        $this->post(route('account.kategori-layanan.update', $angkatan), $this->isian([
            'nama' => $angkatan->nama, 'nama_ke' => $angkatan->nama_ke,
            'gambar' => UploadedFile::fake()->image('satu.jpg'),
        ]))->assertRedirect();

        $lama = $angkatan->refresh()->gambar;
        $this->sampah[] = public_path($lama);

        $this->post(route('account.kategori-layanan.update', $angkatan), $this->isian([
            'nama' => $angkatan->nama, 'nama_ke' => $angkatan->nama_ke,
            'gambar' => UploadedFile::fake()->image('dua.jpg'),
        ]))->assertRedirect();

        $baru = $angkatan->refresh()->gambar;
        $this->sampah[] = public_path($baru);

        $this->assertNotSame($lama, $baru);
        $this->assertFileDoesNotExist(public_path($lama));
        $this->assertFileExists(public_path($baru));
    }

    #[Test]
    public function sampul_yang_masih_dipakai_angkatan_lain_tidak_dibuang(): void
    {
        $this->actingAs($this->akun());
        $angkatan = $this->angkatan();

        $this->post(route('account.kategori-layanan.update', $angkatan), $this->isian([
            'nama' => $angkatan->nama, 'nama_ke' => $angkatan->nama_ke,
            'gambar' => UploadedFile::fake()->image('bersama.jpg'),
        ]))->assertRedirect();

        $bersama = $angkatan->refresh()->gambar;
        $this->sampah[] = public_path($bersama);

        // 41 angkatan Yogyakarta memakai SATU flyer yang sama. Menghapus berkas
        // milik salah satunya mematikan sampul keempat puluh lainnya.
        $penumpang = $this->angkatan(['gambar' => $bersama]);

        $this->post(route('account.kategori-layanan.update', $angkatan), $this->isian([
            'nama' => $angkatan->nama, 'nama_ke' => $angkatan->nama_ke,
            'gambar' => UploadedFile::fake()->image('sendiri.jpg'),
        ]))->assertRedirect();

        $this->sampah[] = public_path($angkatan->refresh()->gambar);

        $this->assertFileExists(public_path($bersama));
        $this->assertSame($bersama, $penumpang->refresh()->gambar);
    }

    #[Test]
    public function sampul_hilang_dari_cakram_tidak_bikin_alamat_palsu(): void
    {
        // Berkasnya bisa terhapus dari luar aplikasi (unggah ulang lewat
        // hPanel, sinkronisasi OneDrive). alamat_sampul harus null, bukan
        // alamat yang menghasilkan gambar rusak di halaman publik.
        $angkatan = $this->angkatan(['gambar' => 'ScopusCamp/tidak-pernah-ada-' . Str::random(8) . '.jpg']);

        $this->assertNull($angkatan->alamat_sampul);
    }

    // ------------------------------------------------- sampul warisan

    #[Test]
    public function sampul_diwarisi_dari_angkatan_lain_di_lokasi_yang_sama(): void
    {
        $this->actingAs($this->akun());

        // 41 angkatan Scopus Camp Yogyakarta memakai satu flyer yang sama;
        // angkatan baru di sana tidak perlu mengunggahnya lagi.
        $lazim = KategoriLayanan::sampulLazim()['scopus_camp|yogyakarta'] ?? null;
        $this->assertNotNull($lazim, 'flyer Yogyakarta belum jadi kebiasaan di data ini');

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '905', 'lokasi' => 'Yogyakarta',
            'sampul_warisan' => $lazim['jalur'],
        ]))->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '905')->firstOrFail();

        $this->assertSame($lazim['jalur'], $baru->gambar);

        // Berkasnya TIDAK disalin: semuanya menunjuk satu berkas yang sama,
        // dan itu yang membuat mengganti flyer cukup sekali untuk semuanya.
        $this->assertNotNull($baru->alamat_sampul);
    }

    #[Test]
    public function ejaan_lokasi_tidak_perlu_sama_persis(): void
    {
        $this->actingAs($this->akun());
        $lazim = KategoriLayanan::sampulLazim()['scopus_camp|yogyakarta'];

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '906', 'lokasi' => '  yOgYaKaRtA  ',
            'sampul_warisan' => $lazim['jalur'],
        ]))->assertRedirect();

        $this->assertSame($lazim['jalur'], KategoriLayanan::where('nama_ke', '906')->firstOrFail()->gambar);
    }

    #[Test]
    public function jalur_sampul_di_luar_daftar_ditolak(): void
    {
        $this->actingAs($this->akun());

        // Jalurnya datang dari peramban. Tanpa daftar tertutup, siapa pun yang
        // bisa mengirim borang ini boleh menunjuk berkas mana saja di public/.
        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '907', 'lokasi' => 'Yogyakarta',
            'sampul_warisan' => '../../.env',
        ]))->assertSessionHasErrors('sampul_warisan');

        $this->assertNull(KategoriLayanan::where('nama_ke', '907')->first());
    }

    #[Test]
    public function jalur_sah_tetapi_lokasinya_tidak_cocok_diabaikan(): void
    {
        $this->actingAs($this->akun());
        $lazim = KategoriLayanan::sampulLazim()['scopus_camp|yogyakarta'];

        // Jalurnya ada di daftar, jadi validator meloloskannya — yang menolak
        // pemeriksaan pasangan layanan+lokasi di pengendali.
        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '908', 'lokasi' => 'Surabaya',
            'sampul_warisan' => $lazim['jalur'],
        ]))->assertRedirect();

        $this->assertNull(KategoriLayanan::where('nama_ke', '908')->firstOrFail()->gambar);
    }

    #[Test]
    public function sampul_yang_sudah_ada_tidak_ditimpa_warisan(): void
    {
        $this->actingAs($this->akun());
        $lazim = KategoriLayanan::sampulLazim()['scopus_camp|yogyakarta'];

        $punyaSendiri = 'ScopusCamp/milik-sendiri-' . Str::random(6) . '.jpg';
        $angkatan = $this->angkatan(['lokasi' => 'Yogyakarta', 'gambar' => $punyaSendiri]);

        $this->post(route('account.kategori-layanan.update', $angkatan), $this->isian([
            'nama' => $angkatan->nama, 'nama_ke' => $angkatan->nama_ke,
            'lokasi' => 'Yogyakarta', 'sampul_warisan' => $lazim['jalur'],
        ]))->assertRedirect();

        $this->assertSame($punyaSendiri, $angkatan->refresh()->gambar);
    }

    #[Test]
    public function berkas_yang_diunggah_mengalahkan_warisan(): void
    {
        $this->actingAs($this->akun());
        $lazim = KategoriLayanan::sampulLazim()['scopus_camp|yogyakarta'];

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '909', 'lokasi' => 'Yogyakarta',
            'sampul_warisan' => $lazim['jalur'],
            'gambar' => UploadedFile::fake()->image('punya-sendiri.jpg'),
        ]))->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '909')->firstOrFail();
        $this->sampah[] = public_path($baru->gambar);

        $this->assertNotSame($lazim['jalur'], $baru->gambar);
        $this->assertFileExists(public_path($baru->gambar));
    }

    #[Test]
    public function usulan_hanya_untuk_yang_sudah_jadi_kebiasaan(): void
    {
        /*
         * Jakarta punya lima berkas berbeda dan tak satu pun dipakai lebih dari
         * sekali. Menebak salah satunya sama saja menebak acak, dan flyer salah
         * yang terlanjur terbit di halaman publik lebih mahal daripada tidak
         * ada usulan sama sekali.
         */
        $lazim = KategoriLayanan::sampulLazim();

        foreach ($lazim as $kunci => $isi) {
            $this->assertGreaterThanOrEqual(2, $isi['jumlah'],
                $kunci . ' diusulkan padahal berkasnya baru dipakai sekali');
            $this->assertFileExists(public_path($isi['jalur']),
                $kunci . ' menunjuk berkas yang tidak ada di cakram');
        }
    }

    // -------------------------------------------------------- ubah massal

    #[Test]
    public function status_banyak_angkatan_diubah_sekali_jalan(): void
    {
        $this->actingAs($this->akun());

        $satu = $this->angkatan(['status' => 'active']);
        $dua = $this->angkatan(['status' => 'draft']);
        $luar = $this->angkatan(['status' => 'active']);

        $this->postJson(route('account.kategori-layanan.massal'), [
            'id' => [$satu->id, $dua->id], 'status' => 'non active',
        ])->assertOk()->assertJson(['success' => true])
            ->assertJsonPath('message', '2 angkatan diubah jadi Nonaktif.');

        $this->assertSame('non active', $satu->refresh()->status);
        $this->assertSame('non active', $dua->refresh()->status);
        $this->assertSame('active', $luar->refresh()->status, 'yang tidak dipilih ikut berubah');
    }

    #[Test]
    public function status_massal_di_luar_daftar_ditolak(): void
    {
        $this->actingAs($this->akun());
        $angkatan = $this->angkatan(['status' => 'active']);

        $this->postJson(route('account.kategori-layanan.massal'), [
            'id' => [$angkatan->id], 'status' => 'dihapus',
        ])->assertStatus(422);

        $this->assertSame('active', $angkatan->refresh()->status);
    }

    #[Test]
    public function massal_tanpa_pilihan_ditolak(): void
    {
        $this->actingAs($this->akun());

        $this->postJson(route('account.kategori-layanan.massal'), ['status' => 'draft'])
            ->assertStatus(422)
            ->assertJsonPath('errors.id.0', 'Pilih dulu angkatan yang mau diubah.');
    }

    #[Test]
    public function massal_dibatasi_dua_ratus_sekali_jalan(): void
    {
        $this->actingAs($this->akun());

        // Batasnya ada supaya satu tekanan tombol tidak bisa membalik seluruh
        // tabel; di produksi isinya 60 angkatan dan terus bertambah.
        $this->postJson(route('account.kategori-layanan.massal'), [
            'id' => array_map(fn () => (string) Str::uuid(), range(1, 201)),
            'status' => 'draft',
        ])->assertStatus(422)->assertJsonPath('errors.id.0', 'Maksimal 200 angkatan sekali ubah.');
    }

    #[Test]
    public function orang_luar_tidak_bisa_ubah_massal(): void
    {
        $angkatan = $this->angkatan(['status' => 'active']);

        $this->actingAs($this->akun(User::PERAN_PELANGGAN));

        $this->postJson(route('account.kategori-layanan.massal'), [
            'id' => [$angkatan->id], 'status' => 'draft',
        ])->assertStatus(403);

        $this->assertSame('active', $angkatan->refresh()->status);
    }

    // ------------------------------------------------------------ rincian

    #[Test]
    public function halaman_rincian_menampilkan_fakta_dan_deskripsi(): void
    {
        $this->actingAs($this->akun());

        $angkatan = $this->angkatan([
            'nama' => 'Angkatan Rincian', 'desc' => "Baris satu\nBaris dua",
        ]);

        $this->get(route('account.kategori-layanan.detail', $angkatan))
            ->assertOk()
            ->assertSee('Angkatan Rincian')
            ->assertSee('Yogyakarta')
            ->assertSee('Baris satu')
            // Deskripsinya ditampilkan apa adanya, bukan dirender: teks yang
            // kebetulan berisi tag tidak boleh jadi tag di layar admin.
            ->assertSee('Baris dua');
    }

    #[Test]
    public function orang_luar_tidak_bisa_lihat_rincian(): void
    {
        $angkatan = $this->angkatan();

        $this->actingAs($this->akun(User::PERAN_PELANGGAN));

        $this->get(route('account.kategori-layanan.detail', $angkatan))
            ->assertRedirect(route('account.dashboard.index'));
    }

    // ------------------------------------------------- nomor & deskripsi

    #[Test]
    public function borang_tambah_menyodorkan_nomor_berikutnya(): void
    {
        $this->actingAs($this->akun());
        $this->angkatan(['nama_ke' => '888']);

        // Nomor tertinggi dibaca sebagai ANGKA, bukan teks: sebagai teks, '9'
        // lebih besar daripada '888' dan angkatan berikutnya jadi nomor 10.
        $this->angkatan(['nama_ke' => '9']);

        $this->get(route('account.kategori-layanan.create', ['layanan' => 'scopus_camp']))
            ->assertOk()
            ->assertSee('value="889"', false);
    }

    #[Test]
    public function deskripsi_berhtml_dibersihkan_saat_disimpan(): void
    {
        $this->actingAs($this->akun());

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '903',
            'desc' => '<h2 data-start="1">Judul</h2><p>Isi <strong>tebal</strong></p>'
                . '<ol><li>Satu</li><li>Dua</li></ol>',
        ]))->assertRedirect();

        $baru = KategoriLayanan::where('nama_ke', '903')->firstOrFail();

        $this->assertSame("Judul\n\nIsi tebal\n\n1. Satu\n2. Dua", $baru->desc);
    }

    #[Test]
    public function deskripsi_teks_datar_tidak_diubah_ubah(): void
    {
        $this->actingAs($this->akun());

        $teks = "Judul\n\nIsi biasa, ada & dan 2 spasi  di sini.";

        $this->post(route('account.kategori-layanan.store'), $this->isian([
            'nama_ke' => '904', 'desc' => $teks,
        ]))->assertRedirect();

        $this->assertSame($teks, KategoriLayanan::where('nama_ke', '904')->firstOrFail()->desc);
    }

    #[Test]
    public function tidak_ada_lagi_deskripsi_berhtml_yang_tersimpan(): void
    {
        // Penjaga hasil migrasi: empat angkatan Bibliometrik dulu menyimpan
        // HTML tempelan, dan halaman publik menampilkannya sebagai tulisan.
        $berhtml = DB::table('kategori_layanan')
            ->whereNotNull('desc')
            ->get(['nama', 'nama_ke', 'desc'])
            ->filter(fn ($b) => $b->desc !== strip_tags($b->desc))
            ->map(fn ($b) => $b->nama . ' #' . $b->nama_ke)
            ->values()
            ->all();

        $this->assertSame([], $berhtml, 'masih ada deskripsi ber-HTML: ' . implode(', ', $berhtml));
    }
}
