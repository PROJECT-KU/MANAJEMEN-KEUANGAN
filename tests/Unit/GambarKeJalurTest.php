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
