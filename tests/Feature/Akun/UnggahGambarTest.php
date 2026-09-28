<?php

namespace Tests\Feature\Akun;

use App\Support\BerkasGambar;
use App\Support\FotoProfil;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Unggahan tidak boleh bisa menanam berkas yang dijalankan peladen.
 */
class UnggahGambarTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengguna(string $level = 'user'): User
    {
        $pengguna = User::create([
            'full_name' => 'Uji Unggah',
            'username' => 'uji_unggah_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => $level,
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    private function bersihkan(?string $nama): void
    {
        if ($nama) {
            FotoProfil::hapus($nama);
        }
    }

    public function test_nama_berkas_tidak_memakai_ekstensi_kiriman(): void
    {
        $pengguna = $this->buatPengguna();

        // Isinya PNG sah tetapi namanya .html, sehingga aturan 'mimes' lolos
        // (Laravel hanya memblokir nama berekstensi php). Kode lama memakai
        // nama kiriman itu apa adanya, jadi berkasnya tersimpan sebagai .html
        // di folder publik — halaman yang bisa memuat skrip.
        $berkas = $this->gambarBernama('serangan.html');

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), ['gambar' => $berkas])
            ->assertRedirect();

        $nama = $pengguna->refresh()->gambar;

        $this->assertNotNull($nama);
        // Sejak foto profil selalu diubah ke WebP, ekstensinya tidak lagi
        // mengikuti berkas masuk sama sekali — apa pun yang dikirim keluar
        // sebagai .webp, jadi nama kiriman tidak punya jalan sama sekali.
        $this->assertStringEndsWith('.webp', $nama);
        $this->assertStringNotContainsString('.html', $nama);
        $this->assertStringNotContainsString('serangan', $nama);

        $this->bersihkan($nama);
    }

    /** PNG 1x1 yang sah, dengan nama kiriman yang ditentukan pemanggil. */
    private function gambarBernama(string $nama): UploadedFile
    {
        $isi = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        $jalur = sys_get_temp_dir() . '/unggah_' . uniqid();
        file_put_contents($jalur, $isi);

        return new UploadedFile($jalur, $nama, 'image/png', null, true);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        $berkas = UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;');

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), ['gambar' => $berkas])
            ->assertSessionHasErrors('gambar');

        $this->assertNull($pengguna->refresh()->gambar);
    }

    public function test_pembantu_menolak_ekstensi_di_luar_daftar_putih(): void
    {
        $svg = UploadedFile::fake()->createWithContent('gambar.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->assertNull(BerkasGambar::simpan($svg, 'assets/img/profil', 'uji'));
    }

    public function test_hapus_tidak_bisa_keluar_dari_foldernya(): void
    {
        $berkas = public_path('assets/img/uji-jangan-hilang.jpg');
        file_put_contents($berkas, 'x');

        // Nama aneh di basis data tidak boleh menjangkau berkas di folder lain.
        BerkasGambar::hapus('assets/img/profil', '../uji-jangan-hilang.jpg');

        $this->assertFileExists($berkas);

        @unlink($berkas);
    }

    public function test_folder_unggahan_menolak_berkas_yang_bisa_dijalankan(): void
    {
        foreach (['images', 'assets/img/profil', 'paperisasi', 'karir'] as $folder) {
            $penjaga = public_path($folder . '/.htaccess');

            $this->assertFileExists($penjaga, "Folder {$folder} belum punya penjaga .htaccess");
            $this->assertStringContainsString('php', file_get_contents($penjaga));
        }
    }

    public function test_nama_berekstensi_php_tetap_ditolak(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), ['gambar' => $this->gambarBernama('serangan.php')])
            ->assertSessionHasErrors('gambar');

        $this->assertNull($pengguna->refresh()->gambar);
    }
}
