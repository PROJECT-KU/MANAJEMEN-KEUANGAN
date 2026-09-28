<?php

namespace Tests\Feature\Akun;

use App\Support\FotoProfil;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Foto profil paling besar tampil pada 96px. Menyimpan berkas 1200x900 apa
 * adanya berarti tiap kunjungan mengunduh gambar berkali lipat lebih besar
 * daripada yang pernah terlihat.
 */
class FotoProfilUkuranTest extends TestCase
{
    use DatabaseTransactions;

    private array $sampah = [];

    protected function tearDown(): void
    {
        foreach ($this->sampah as $nama) {
            FotoProfil::hapus($nama);
        }

        parent::tearDown();
    }

    private function buatPengguna(): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Ukuran Foto',
            'username' => 'uji_ukuran_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    private function unggah(User $pengguna, int $lebar, int $tinggi): array
    {
        $gd = imagecreatetruecolor($lebar, $tinggi);
        imagefill($gd, 0, 0, imagecolorallocate($gd, 30, 120, 200));
        $jalur = sys_get_temp_dir() . '/uji_' . uniqid() . '.jpg';
        imagejpeg($gd, $jalur, 92);
        imagedestroy($gd);

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), [
                'gambar' => new UploadedFile($jalur, 'foto.jpg', 'image/jpeg', null, true),
            ])
            ->assertRedirect();

        $nama = $pengguna->refresh()->gambar;
        $this->sampah[] = $nama;

        $isi = Storage::disk(FotoProfil::DISK)->get($nama);
        $gambar = imagecreatefromstring($isi);

        return [imagesx($gambar), imagesy($gambar), strlen($isi)];
    }

    #[Test]
    public function foto_besar_disusutkan_sisi_terpanjangnya_jadi_512(): void
    {
        [$lebar, $tinggi] = $this->unggah($this->buatPengguna(), 1600, 1200);

        $this->assertSame(512, $lebar, 'Sisi terpanjang harus jadi 512.');
        $this->assertSame(384, $tinggi, 'Perbandingan sisinya harus tetap 4:3.');
    }

    #[Test]
    public function foto_memanjang_tidak_kehilangan_perbandingan_sisinya(): void
    {
        [$lebar, $tinggi] = $this->unggah($this->buatPengguna(), 900, 2400);

        $this->assertSame(512, $tinggi, 'Yang disusutkan sisi terpanjang, bukan selalu lebarnya.');
        $this->assertSame(192, $lebar);
    }

    #[Test]
    public function foto_yang_sudah_kecil_tidak_ikut_diperbesar(): void
    {
        // Memperbesar foto kecil hanya menambah berkas tanpa menambah rincian.
        [$lebar, $tinggi] = $this->unggah($this->buatPengguna(), 120, 90);

        $this->assertSame(120, $lebar);
        $this->assertSame(90, $tinggi);
    }
}
