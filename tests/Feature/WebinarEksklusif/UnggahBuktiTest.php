<?php

namespace Tests\Feature\WebinarEksklusif;

use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use App\Layanan;
use App\Services\Gambar;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Peserta mengunggah bukti transfernya sendiri dari halaman status.
 *
 * Ada selama gerbang pembayaran DOKU belum terverifikasi. Sebelumnya
 * satu-satunya jalan adalah WhatsApp panitia, dan bukti yang menumpuk di satu
 * nomor pribadi tidak pernah sampai ke baris pendaftarannya.
 *
 * Yang dijaga di sini tiga hal yang mudah sekali rusak diam-diam:
 *
 * 1. Yang TERSIMPAN selalu WebP — bukan JPG yang cuma berganti nama.
 * 2. Berkas ASLINYA tidak tertinggal di mana pun.
 * 3. Penjagaannya ada di PELADEN, bukan cuma borang yang tidak digambar.
 */
class UnggahBuktiTest extends TestCase
{
    use DatabaseTransactions;

    private const FOLDER = 'bukti/webinar_eksklusif';

    /**
     * @var array<int, string> berkas yang SUDAH ADA sebelum uji ini jalan
     *
     * DatabaseTransactions mengembalikan barisnya, tetapi TIDAK menyentuh
     * cakram sama sekali. Tanpa potret ini, tiap jalan uji meninggalkan WebP
     * baru di storage pengembang — dan pembersihan tanpa pagar pernah
     * menghapus berkas sungguhan milik orang.
     */
    private array $milikOrang = [];

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        KategoriLayanan::lupakanPendaftar();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();

        $this->milikOrang = Storage::disk(Gambar::CAKRAM)->files(self::FOLDER);
    }

    protected function tearDown(): void
    {
        foreach (Storage::disk(Gambar::CAKRAM)->files(self::FOLDER) as $berkas) {
            if (! in_array($berkas, $this->milikOrang, true)) {
                Storage::disk(Gambar::CAKRAM)->delete($berkas);
            }
        }

        parent::tearDown();
    }

    private function sesi(array $lain = []): KategoriLayanan
    {
        return KategoriLayanan::create(array_merge([
            'layanan' => 'webinar_eksklusif', 'token' => Str::random(30),
            'nama' => 'Sesi Bukti ' . Str::random(4), 'nama_ke' => (string) random_int(7000, 7999),
            'mulai' => now()->addDays(12)->toDateString(), 'selesai' => now()->addDays(12)->toDateString(),
            'jam_mulai' => '09:30', 'jam_selesai' => '11:30', 'platform' => 'Zoom',
            'pemateri' => 'Pemateri', 'total_kuota' => '20', 'sisa_kuota' => '18',
            'biaya' => '129000', 'status' => 'active',
        ], $lain));
    }

    private function pendaftaran(array $lain = []): WebinarEksklusifPendaftaran
    {
        return WebinarEksklusifPendaftaran::create(array_merge([
            'kategori_id' => $this->sesi()->id,
            'nama' => 'Pendaftar Bukti', 'email' => 'b-' . Str::random(5) . '@contoh.test',
            'telp' => '628123456789', 'jumlah_pendaftar' => 1, 'total_pembayaran' => '130478',
            'cara_bayar' => 'transfer', 'status' => 'pending',
            'kedaluwarsa_pada' => now()->addHours(20),
        ], $lain));
    }

    private function kirim(WebinarEksklusifPendaftaran $p, UploadedFile $berkas)
    {
        return $this->post(
            route('public.webinareksklusif.bukti', $p->getKey()),
            ['bukti' => $berkas]
        );
    }

    // ------------------------------------------------------- jalan bahagia

    #[Test]
    public function jpg_yang_diunggah_tersimpan_sebagai_webp(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti-transfer.jpg', 1200, 900))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $jalur = (string) $p->fresh()->gambar;

        $this->assertStringStartsWith(self::FOLDER . '/', $jalur,
            'Buktinya harus mendarat di folder layanan ini, bukan di akar cakram.');
        $this->assertStringEndsWith('.webp', $jalur,
            'Yang tersimpan harus WebP, bukan berkas asli yang cuma berganti nama.');

        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($jalur));

        /*
         * Diperiksa dari ISI berkasnya, bukan dari akhirannya.
         *
         * Akhiran .webp pada berkas yang isinya masih JPEG akan lolos uji yang
         * hanya membaca namanya, dan rusaknya baru ketahuan saat panitia
         * membuka berkasnya.
         */
        $isi = Storage::disk(Gambar::CAKRAM)->get($jalur);

        $this->assertSame('WEBP', substr($isi, 8, 4),
            'Isi berkasnya ternyata bukan WebP.');
        $this->assertNotFalse(getimagesizefromstring($isi));
    }

    #[Test]
    public function png_juga_diterima(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.png', 800, 600))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertStringEndsWith('.webp', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function berkas_asli_tidak_ikut_tertinggal_di_cakram(): void
    {
        /*
         * Janji yang disampaikan kepada peserta di halamannya: "berkas aslinya
         * tidak kami simpan". Yang dijaga di sini janji itu, bukan sekadar
         * nilai kolomnya.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('rahasia-nama-asli.jpg', 900, 600))
            ->assertRedirect();

        $semua = Storage::disk(Gambar::CAKRAM)->allFiles(self::FOLDER);

        foreach ($semua as $berkas) {
            $this->assertStringEndsWith('.webp', $berkas,
                'Ada berkas bukan-WebP tertinggal di folder bukti: ' . $berkas);
        }

        $this->assertEmpty(
            array_filter($semua, fn ($b) => str_contains($b, 'rahasia-nama-asli')),
            'Nama berkas asli peserta ikut tersimpan; namanya sendiri bisa memuat data pribadi.'
        );
    }

    #[Test]
    public function bukti_yang_diganti_membuang_berkas_lamanya(): void
    {
        /*
         * Tanpa ini tiap unggahan ulang meninggalkan satu WebP yatim yang
         * tidak ditunjuk baris mana pun dan tidak akan pernah terhapus.
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('pertama.jpg', 600, 400))->assertRedirect();
        $lama = (string) $p->fresh()->gambar;

        $this->kirim($p, UploadedFile::fake()->image('kedua.jpg', 600, 400))->assertRedirect();
        $baru = (string) $p->fresh()->gambar;

        $this->assertNotSame($lama, $baru);
        $this->assertFalse(Storage::disk(Gambar::CAKRAM)->exists($lama),
            'Bukti lama masih tertinggal di cakram sesudah diganti.');
        $this->assertTrue(Storage::disk(Gambar::CAKRAM)->exists($baru));
    }

    // ------------------------------------------------------------ penolakan

    #[Test]
    public function berkas_bukan_gambar_ditolak(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->create('tagihan.pdf', 120, 'application/pdf'))
            ->assertSessionHasErrors('bukti');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function berkas_kelewat_besar_ditolak(): void
    {
        $p = $this->pendaftaran();

        // 9 MB; batasnya 8 MB.
        $this->kirim($p, UploadedFile::fake()->create('besar.jpg', 9 * 1024, 'image/jpeg'))
            ->assertSessionHasErrors('bukti');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function heic_lolos_pemeriksaan_borang(): void
    {
        /*
         * HEIC adalah format BAWAAN kamera iPhone, dan peserta yang memotret
         * buktinya di situ tidak tahu berkasnya bukan JPG.
         *
         * Yang dijaga: akhiran .heic TIDAK ditolak borangnya. Pemeriksaan
         * lewat jenis MIME akan menolaknya di sebagian peladen — finfo di
         * sana memulangkan application/octet-stream untuk HEIC.
         *
         * Berhasil-tidaknya konversi tergantung alat yang ada di peladen, dan
         * itu diuji terpisah. Di sini yang penting galatnya BUKAN galat
         * borang.
         */
        $p = $this->pendaftaran();

        $jawab = $this->kirim($p, UploadedFile::fake()->create('IMG_4821.heic', 300, 'image/heic'));

        $jawab->assertSessionDoesntHaveErrors('bukti');
    }

    #[Test]
    public function heic_yang_tidak_terbaca_dijawab_kalimat_yang_bisa_dikerjakan(): void
    {
        /*
         * Peladen tanpa Imagick/libheif tidak bisa membongkar HEIC sama
         * sekali. Yang dilihat peserta harus menyebut jalan keluar yang bisa
         * ia kerjakan sendiri — bukan "terjadi kesalahan".
         */
        $p = $this->pendaftaran();

        // Isinya bukan HEIC sungguhan, jadi tidak ada alat mana pun yang bisa
        // membacanya: itu persis keadaan yang diuji.
        $this->kirim($p, UploadedFile::fake()->create('IMG_4822.heic', 300, 'image/heic'))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'JPG'));

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    // ------------------------------------------------- penjagaan di peladen

    #[Test]
    public function pendaftaran_yang_sudah_lunas_menolak_bukti(): void
    {
        /*
         * Borangnya memang tidak digambar untuk yang sudah lunas — tetapi
         * alamatnya tetap bisa dikirimi permintaan oleh siapa pun yang pernah
         * membuka halamannya.
         */
        $p = $this->pendaftaran(['status' => 'paid', 'bayar_status' => 'lunas']);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function pendaftaran_yang_dibatalkan_menolak_bukti(): void
    {
        $p = $this->pendaftaran(['status' => 'cancel']);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    #[Test]
    public function pendaftaran_kedaluwarsa_menolak_bukti(): void
    {
        $p = $this->pendaftaran([
            'status' => 'expired',
            'kedaluwarsa_pada' => now()->subHour(),
        ]);

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('', (string) $p->fresh()->gambar);
    }

    // ---------------------------------------------------------- tampilannya

    #[Test]
    public function halaman_status_menawarkan_unggahan_dan_menyebut_formatnya(): void
    {
        $p = $this->pendaftaran();

        $isi = $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('public.webinareksklusif.bukti', $p->getKey()), $isi,
            'Borangnya tidak menunjuk ke alamat unggahan.');

        $this->assertStringContainsString('enctype="multipart/form-data"', $isi,
            'Tanpa enctype, berkasnya tidak pernah ikut terkirim dan galatnya tidak menyebut sebabnya.');

        $this->assertStringContainsString('.heic', $isi,
            'Daftar format yang diterima harus menyebut HEIC; itu bawaan kamera iPhone.');

        // Isian berkasnya disembunyikan 1x1 transparan, BUKAN display:none —
        // yang display:none dilewati papan ketik sama sekali.
        $this->assertStringContainsString('class="sta-berkas"', $isi);
    }

    #[Test]
    public function bukti_yang_sudah_ada_bisa_dibuka_dari_halaman_status(): void
    {
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $this->get(route('public.webinareksklusif.status', $p->getKey()))
            ->assertOk()
            ->assertSee('Bukti Anda sudah kami terima', false)
            ->assertSee('storage/' . $p->fresh()->gambar, false);
    }

    #[Test]
    public function panitia_melihat_bukti_yang_diunggah_peserta(): void
    {
        /*
         * Bukti yang tidak bisa dilihat panitia sama saja dengan tidak ada.
         *
         * Dulu buktiBaris() merakit jalurnya sendiri sebagai public/<folder>/
         * dan layanan ini TIDAK punya folder lama — jadi apa pun yang
         * diunggah peserta selalu terbaca "berkasnya tidak ada".
         */
        $p = $this->pendaftaran();

        $this->kirim($p, UploadedFile::fake()->image('bukti.jpg', 600, 400))->assertRedirect();

        $baris = (object) [
            'layanan' => 'webinar_eksklusif',
            'bukti' => $p->fresh()->gambar,
        ];

        $bukti = \App\Support\PendaftaranSemuaLayanan::buktiBaris($baris);

        $this->assertTrue($bukti['nilai']);
        $this->assertTrue($bukti['ada'], 'Panitia akan melihat "berkasnya tidak ada".');
        $this->assertNotNull($bukti['url']);
    }

    #[Test]
    public function heic_sungguhan_diubah_jadi_webp(): void
    {
        /*
         * Jalur HEIC dari ujung ke ujung, dengan berkas HEIC SUNGGUHAN.
         *
         * Dilewati kalau mesin ini tidak bisa membuatnya — pembuatannya
         * memakai `sips` yang hanya ada di macOS. Dilewati pun ada gunanya:
         * ia menandai bahwa jalur ini TIDAK terbukti di mesin itu, bukan
         * berpura-pura hijau.
         */
        $heic = $this->berkasHeic();

        if ($heic === null) {
            $this->markTestSkipped('Mesin ini tidak bisa membuat berkas HEIC contoh (butuh sips).');
        }

        $p = $this->pendaftaran();

        $this->kirim($p, new UploadedFile($heic, 'IMG_5120.heic', 'image/heic', null, true))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        @unlink($heic);

        $jalur = (string) $p->fresh()->gambar;

        $this->assertStringEndsWith('.webp', $jalur);

        $isi = Storage::disk(Gambar::CAKRAM)->get($jalur);

        $this->assertSame('WEBP', substr($isi, 8, 4),
            'HEIC-nya tidak benar-benar berubah jadi WebP.');
        $this->assertNotFalse(getimagesizefromstring($isi));
    }

    /** Berkas HEIC contoh; null kalau mesin ini tidak bisa membuatnya. */
    private function berkasHeic(): ?string
    {
        $sumber = sys_get_temp_dir() . '/heic-sumber-' . Str::random(6) . '.jpg';
        $tujuan = sys_get_temp_dir() . '/heic-bukti-' . Str::random(6) . '.heic';

        $im = imagecreatetruecolor(600, 400);
        imagefill($im, 0, 0, imagecolorallocate($im, 30, 90, 200));
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
}
