<?php

namespace Tests\Unit;

use App\Services\Gambar;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Menjaga pengecilan gambar yang tujuannya folder di dalam public/.
 *
 * Sampai 2 Okt 2026 sampul dan foto pemateri angkatan dipindah MENTAH ke
 * sana. Terukur: satu foto pemateri 4000x2252 piksel seberat 2,8 MB ikut
 * terunduh di layar pertama halaman iklan, padahal tampil 230x287.
 *
 * Kegagalan seperti itu tidak pernah menampakkan diri — halamannya tetap
 * benar, hanya lambat — jadi dijaga uji, bukan diingat.
 */
class GambarKeJalurTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        // Folder sendiri di bawah temp, supaya tidak ada berkas nyata yang
        // bisa tersentuh oleh pembersihan di tearDown.
        $this->folder = sys_get_temp_dir() . '/uji-gambar-' . bin2hex(random_bytes(6));
        mkdir($this->folder, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->folder . '/*') ?: [] as $berkas) {
            @unlink($berkas);
        }

        @rmdir($this->folder);

        parent::tearDown();
    }

    private function buatJpeg(int $lebar, int $tinggi): string
    {
        $jalur = $this->folder . '/asal.jpg';
        $im = imagecreatetruecolor($lebar, $tinggi);

        // Diisi derau supaya tidak termampatkan jadi beberapa byte saja —
        // gambar polos tidak menguji apa pun tentang ukuran.
        for ($x = 0; $x < $lebar; $x += 4) {
            for ($y = 0; $y < $tinggi; $y += 4) {
                imagefilledrectangle($im, $x, $y, $x + 3, $y + 3,
                    imagecolorallocate($im, ($x * 7) % 255, ($y * 13) % 255, ($x + $y) % 255));
            }
        }

        imagejpeg($im, $jalur, 95);
        imagedestroy($im);

        return $jalur;
    }

    #[Test]
    public function gambar_besar_dikecilkan_ke_lebar_maksimum_dan_jadi_webp(): void
    {
        $asal = $this->buatJpeg(4000, 2252);
        $tujuan = $this->folder . '/hasil.webp';

        $this->assertTrue(app(Gambar::class)->keJalur($asal, $tujuan));
        $this->assertFileExists($tujuan);

        [$lebar, $tinggi] = getimagesize($tujuan);

        $this->assertSame(Gambar::LEBAR_MAKS, $lebar,
            'lebarnya harus dipangkas ke batas yang sama dengan galeri');

        // Nisbahnya ikut terjaga, jadi tidak ada yang terpotong.
        $this->assertEqualsWithDelta(2252 / 4000, $tinggi / $lebar, 0.01);

        $this->assertSame('image/webp', getimagesize($tujuan)['mime']);

        $this->assertLessThan(filesize($asal), filesize($tujuan),
            'hasilnya harus lebih kecil daripada berkas aslinya');
    }

    #[Test]
    public function gambar_yang_sudah_kecil_tidak_ikut_dibesarkan(): void
    {
        $asal = $this->buatJpeg(600, 400);
        $tujuan = $this->folder . '/kecil.webp';

        $this->assertTrue(app(Gambar::class)->keJalur($asal, $tujuan));

        [$lebar] = getimagesize($tujuan);

        $this->assertSame(600, $lebar, 'yang sudah di bawah batas dibiarkan apa adanya');
    }

    /**
     * JPEG dengan satu penanda warna di SUDUT KIRI ATAS, ditulis mendatar
     * tetapi bertanda EXIF Orientation 6.
     *
     * Peramban memutar JPEG seperti ini saat menampilkannya, jadi di layar
     * tampak tegak. GD mengabaikan tandanya dan WebP tidak membawanya - maka
     * putarannya harus dibakukan ke pikselnya saat dikonversi.
     */
    private function buatJpegBerputar(): string
    {
        $jalur = $this->folder . '/berputar.jpg';

        $im = imagecreatetruecolor(400, 200);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        // Penanda merah di sudut KIRI ATAS.
        imagefilledrectangle($im, 0, 0, 59, 59, imagecolorallocate($im, 255, 0, 0));
        imagejpeg($im, $jalur, 95);
        imagedestroy($im);

        /*
         * Tanda EXIF disisipkan sebagai APP1 tepat sesudah SOI. Ditulis
         * langsung karena PHP tidak punya penulis EXIF bawaan.
         */
        $isi = file_get_contents($jalur);

        $tiff = "\x4D\x4D\x00\x2A\x00\x00\x00\x08"      // big-endian, IFD di offset 8
            . "\x00\x01"                                     // satu entri
            . "\x01\x12\x00\x03\x00\x00\x00\x01\x00\x06\x00\x00"  // Orientation = 6
            . "\x00\x00\x00\x00";                          // tidak ada IFD berikutnya

        $app1 = "Exif\x00\x00" . $tiff;
        $segmen = "\xFF\xE1" . pack('n', strlen($app1) + 2) . $app1;

        file_put_contents($jalur, substr($isi, 0, 2) . $segmen . substr($isi, 2));

        return $jalur;
    }

    #[Test]
    public function putaran_exif_dibakukan_ke_pikselnya(): void
    {
        $asal = $this->buatJpegBerputar();

        $this->assertSame(6, @exif_read_data($asal)['Orientation'] ?? null,
            'prasyarat ujinya: berkasnya memang harus bertanda Orientation 6');

        $tujuan = $this->folder . '/tegak.webp';
        $this->assertTrue(app(\App\Services\Gambar::class)->keJalur($asal, $tujuan));

        [$lebar, $tinggi] = getimagesize($tujuan);

        $this->assertGreaterThan($lebar, $tinggi,
            'yang semula 400x200 mendatar harus jadi tegak sesudah putarannya dibakukan');

        /*
         * Arah putarannya diperiksa dari LETAK PENANDANYA, bukan dari ukuran
         * saja - memutar 90 ke kiri dan ke kanan sama-sama menghasilkan
         * gambar tegak, dan hanya satu di antaranya yang benar.
         *
         * Orientation 6 berarti diputar searah jarum jam saat ditampilkan,
         * jadi penanda yang semula di kiri-atas pindah ke KANAN-ATAS.
         */
        $im = imagecreatefromwebp($tujuan);
        $warna = function ($x, $y) use ($im) {
            $c = imagecolorat($im, $x, $y);

            return [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
        };

        [$r, $g, $b] = $warna($lebar - 10, 10);
        imagedestroy($im);

        $this->assertGreaterThan(180, $r, 'sudut kanan-atas harus merah');
        $this->assertLessThan(90, $g);
        $this->assertLessThan(90, $b);
    }

    #[Test]
    public function putaran_exif_dibakukan_lewat_simpan_kalau_diminta(): void
    {
        /*
         * simpan() tidak meluruskan secara bawaan, dan itu keputusan
         * pemiliknya yang dijaga uji lain (GaleriTest): foto yang sudah
         * melewati WhatsApp kehilangan penandanya sementara pikselnya tetap
         * miring, jadi menebak berarti sebagian foto justru dimiringkan.
         * Galeri foto punya tombol putar manual sebagai gantinya.
         *
         * Yang dijaga di sini jalan MASUKNYA, untuk layar yang tidak punya
         * tombol itu. Bukti transfer dari iPhone tersimpan miring 90 derajat
         * di layar panitia 5 Okt 2026, dan tidak ada seorang pun yang bisa
         * membetulkannya — pengirimnya tidak melihat hasilnya, panitia tidak
         * punya tombolnya.
         */
        $asal = $this->buatJpegBerputar();

        $this->assertSame(6, @exif_read_data($asal)['Orientation'] ?? null,
            'prasyarat ujinya: berkasnya memang harus bertanda Orientation 6');

        $unggahan = new \Illuminate\Http\UploadedFile($asal, 'berputar.jpg', null, null, true);

        $jalur = app(\App\Services\Gambar::class)->simpan($unggahan, 'uji-putaran', true);

        $this->assertNotNull($jalur, 'berkasnya tidak tersimpan sama sekali');

        $cakram = \Illuminate\Support\Facades\Storage::disk(\App\Services\Gambar::CAKRAM);

        [$lebar, $tinggi] = getimagesize($cakram->path($jalur));

        $cakram->delete($jalur);

        $this->assertGreaterThan($lebar, $tinggi,
            'diminta meluruskan tetapi yang tersimpan tetap mendatar');
    }

    #[Test]
    public function simpan_tidak_meluruskan_kalau_tidak_diminta(): void
    {
        /*
         * Sisi satunya, dan ini yang paling mudah hilang: menambah pelurusan
         * di tempat yang salah tidak pernah terasa seperti merusak sesuatu.
         * Percobaan pertama memasangnya untuk SEMUA jalur, dan yang jatuh
         * justru keputusan pemiliknya soal galeri foto.
         */
        $asal = $this->buatJpegBerputar();

        $unggahan = new \Illuminate\Http\UploadedFile($asal, 'berputar.jpg', null, null, true);

        $jalur = app(\App\Services\Gambar::class)->simpan($unggahan, 'uji-putaran');

        $this->assertNotNull($jalur);

        $cakram = \Illuminate\Support\Facades\Storage::disk(\App\Services\Gambar::CAKRAM);

        [$lebar, $tinggi] = getimagesize($cakram->path($jalur));

        $cakram->delete($jalur);

        $this->assertGreaterThan($tinggi, $lebar,
            'sistem memutar sendiri padahal tidak diminta; foto galeri yang penandanya basi akan dimiringkan sistem');
    }

    #[Test]
    public function heic_dibongkar_lewat_format_yang_membawa_penanda_putaran(): void
    {
        /*
         * Penjaga tingkat SUMBER, karena hasilnya bergantung alat yang ada di
         * mesin — dan di peladen sungguhan belum tentu ada satu pun.
         *
         * Yang dijaga PILIHAN FORMATNYA. sips dulu menulis PNG, dan PNG tidak
         * punya tempat untuk penanda putaran. Diukur pada IMG_3675.HEIC
         * 5712x4284 milik iPhone:
         *
         *   sips -s format png   -> 5712x4284, penandanya HILANG
         *   sips -s format jpeg  -> 5712x4284, Orientation: 6 TERBAWA
         *
         * Lewat PNG tidak ada lagi yang bisa tahu fotonya harus diputar, dan
         * yang tersimpan miring 90 derajat tanpa satu pun galat.
         *
         * heif-convert tetap PNG: libheif memutarnya sendiri saat membongkar,
         * jadi hasilnya sudah tegak dan tidak butuh penanda apa pun.
         */
        $sumber = file_get_contents(app_path('Services/Gambar.php'));

        $kode = preg_replace('#/\*.*?\*/#s', '', $sumber);

        $this->assertMatchesRegularExpression("/\['sips',\s*'-s format jpeg/", (string) $kode,
            'sips kembali menulis PNG; penanda putaran foto iPhone akan hilang lagi.');

        $this->assertMatchesRegularExpression("/\['heif-convert',\s*'%s %s'/", (string) $kode,
            'heif-convert tidak lagi dipakai lebih dulu; ia satu-satunya yang memutar sendiri.');

        $this->assertSame(0, preg_match("/\['sips',\s*'-s format png/", (string) $kode),
            'cabang sips yang lama masih ada.');
    }

    #[Test]
    public function berkas_yang_tidak_terbaca_mengembalikan_false(): void
    {
        $rusak = $this->folder . '/rusak.jpg';
        file_put_contents($rusak, 'ini bukan gambar');

        $this->assertFalse(app(Gambar::class)->keJalur($rusak, $this->folder . '/rusak.webp'),
            'pemanggilnya mengandalkan nilai ini untuk menyimpan berkas aslinya sebagai jalan mundur');

        $this->assertFileDoesNotExist($this->folder . '/rusak.webp');
    }

    #[Test]
    public function berkas_asal_yang_tidak_ada_mengembalikan_false(): void
    {
        $this->assertFalse(
            app(Gambar::class)->keJalur($this->folder . '/tidak-ada.jpg', $this->folder . '/x.webp')
        );
    }

    #[Test]
    public function folder_tujuan_dibuat_kalau_belum_ada(): void
    {
        $asal = $this->buatJpeg(800, 600);
        $sarang = $this->folder . '/belum/ada/sama/sekali';

        $this->assertTrue(app(Gambar::class)->keJalur($asal, $sarang . '/hasil.webp'));
        $this->assertFileExists($sarang . '/hasil.webp');

        // Dibersihkan di sini; tearDown hanya menyapu satu lapis.
        @unlink($sarang . '/hasil.webp');

        foreach (['/belum/ada/sama/sekali', '/belum/ada/sama', '/belum/ada', '/belum'] as $sisa) {
            @rmdir($this->folder . $sisa);
        }
    }
}
