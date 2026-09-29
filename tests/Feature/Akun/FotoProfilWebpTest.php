<?php

namespace Tests\Feature\Akun;

use App\Support\FotoProfil;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Foto profil apa pun bentuknya tersimpan sebagai satu berkas WebP di
 * storage — berkas aslinya tidak pernah ikut mendarat di mana pun.
 */
class FotoProfilWebpTest extends TestCase
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
            'full_name' => 'Uji Foto WebP',
            'username' => 'uji_webp_' . uniqid(),
            'email' => uniqid() . '@contoh.test',
            'password' => Hash::make('RahasiaUji2026'),
            'level' => 'user',
        ]);

        $pengguna->forceFill(['status' => 'active', 'email_verified_at' => now()])->save();

        return $pengguna->refresh();
    }

    /**
     * Administrator: satu-satunya peran yang boleh mengunggah foto untuk akun
     * orang lain. Dipakai untuk menguji jalur layar pelanggan, yang memang
     * dikerjakan orang dalam atas nama pelanggannya.
     */
    private function buatAdministrator(): User
    {
        $admin = $this->buatPengguna();

        $admin->forceFill([
            'full_name' => 'Uji Administrator Foto',
            'peran' => User::PERAN_ADMINISTRATOR,
        ])->save();

        return $admin->refresh();
    }

    /** Gambar sungguhan (bukan fake Laravel) supaya GD benar-benar membacanya. */
    private function gambar(string $jenis, string $nama): UploadedFile
    {
        $gd = imagecreatetruecolor(24, 16);
        imagefill($gd, 0, 0, imagecolorallocate($gd, 200, 30, 90));

        $jalur = sys_get_temp_dir() . '/uji_' . uniqid() . '.' . $jenis;

        match ($jenis) {
            'jpg' => imagejpeg($gd, $jalur),
            'png' => imagepng($gd, $jalur),
            'gif' => imagegif($gd, $jalur),
            'webp' => imagewebp($gd, $jalur),
        };

        imagedestroy($gd);

        $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'][$jenis];

        return new UploadedFile($jalur, $nama, $mime, null, true);
    }

    public static function bentukMasuk(): array
    {
        return [
            'JPG' => ['jpg', 'foto.jpg'],
            'PNG' => ['png', 'foto.png'],
            'GIF' => ['gif', 'foto.gif'],
            'WebP' => ['webp', 'foto.webp'],
        ];
    }

    #[Test]
    #[DataProvider('bentukMasuk')]
    public function apa_pun_yang_diunggah_tersimpan_sebagai_webp(string $jenis, string $nama): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), ['gambar' => $this->gambar($jenis, $nama)])
            ->assertRedirect();

        $tersimpan = $pengguna->refresh()->gambar;
        $this->sampah[] = $tersimpan;

        $this->assertNotNull($tersimpan, "Unggahan {$jenis} seharusnya tersimpan.");
        $this->assertStringEndsWith('.webp', $tersimpan);

        // Berkasnya memang ada di storage, bukan di folder publik yang lama.
        $this->assertTrue(Storage::disk(FotoProfil::DISK)->exists($tersimpan));
        $this->assertFileDoesNotExist(public_path(FotoProfil::FOLDER_LAMA . '/' . $tersimpan));

        // Dan isinya benar-benar WebP, bukan berkas lama yang sekadar diganti nama.
        $isi = Storage::disk(FotoProfil::DISK)->get($tersimpan);
        $this->assertSame('RIFF', substr($isi, 0, 4));
        $this->assertSame('WEBP', substr($isi, 8, 4));
    }

    #[Test]
    public function berkas_asli_tidak_ikut_tersimpan(): void
    {
        $pengguna = $this->buatPengguna();
        $berkas = $this->gambar('png', 'asli.png');

        $this->actingAs($pengguna)
            ->post(route('account.profil.updatePhoto'), ['gambar' => $berkas])
            ->assertRedirect();

        $tersimpan = $pengguna->refresh()->gambar;
        $this->sampah[] = $tersimpan;

        // Satu berkas saja yang tertinggal untuk pengguna ini: yang .webp.
        $milikDia = array_filter(
            Storage::disk(FotoProfil::DISK)->files(),
            fn ($f) => str_starts_with($f, 'profil-' . $pengguna->getKey() . '_')
        );

        $this->assertCount(1, $milikDia);
        $this->assertStringEndsWith('.webp', array_values($milikDia)[0]);
    }

    #[Test]
    public function mengganti_foto_membuang_yang_lama(): void
    {
        $pengguna = $this->buatPengguna();

        $this->actingAs($pengguna)->post(route('account.profil.updatePhoto'), ['gambar' => $this->gambar('png', 'satu.png')]);
        $pertama = $pengguna->refresh()->gambar;

        $this->actingAs($pengguna)->post(route('account.profil.updatePhoto'), ['gambar' => $this->gambar('jpg', 'dua.jpg')]);
        $kedua = $pengguna->refresh()->gambar;
        $this->sampah[] = $kedua;

        $this->assertNotSame($pertama, $kedua);
        $this->assertFalse(Storage::disk(FotoProfil::DISK)->exists($pertama), 'Foto lama seharusnya sudah dibuang.');
        $this->assertTrue(Storage::disk(FotoProfil::DISK)->exists($kedua));
    }

    #[Test]
    public function foto_lama_di_folder_publik_tetap_tampil(): void
    {
        // Ratusan foto yang sudah ada menunjuk ke public/assets/img/profil.
        // Kalau url() berhenti mengenalinya, semuanya hilang begitu ini naik.
        $pengguna = $this->buatPengguna();
        $nama = 'uji-lama-' . uniqid() . '.jpg';
        $jalur = public_path(FotoProfil::FOLDER_LAMA . '/' . $nama);
        file_put_contents($jalur, 'x');

        $pengguna->forceFill(['gambar' => $nama])->save();

        $this->assertStringContainsString(FotoProfil::FOLDER_LAMA, $pengguna->refresh()->foto_url);
        $this->assertTrue($pengguna->punya_foto);

        FotoProfil::hapus($nama);
        $this->assertFileDoesNotExist($jalur);
    }

    /**
     * Rute yang dipakai layar pelanggan dan layar pengguna, BUKAN rute profil.
     *
     * Keduanya memang memanggil FotoProfil::simpan() yang sama, tetapi lewat
     * pengendali yang berbeda dengan penjagaan hak, pengikatan {pengguna:uuid},
     * dan pencatatan jejaknya sendiri. Sampai diuji di sini, jalur itu hanya
     * kelihatan benar dari membaca kodenya.
     */
    #[Test]
    #[DataProvider('bentukMasuk')]
    public function unggahan_lewat_layar_pelanggan_tersimpan_sebagai_webp(string $jenis, string $nama): void
    {
        $pelanggan = $this->buatPengguna();
        $admin = $this->buatAdministrator();

        $this->actingAs($admin)
            ->post(
                route('account.pengguna.update.updatePhoto', $pelanggan->uuid),
                ['gambar' => $this->gambar($jenis, $nama)]
            )
            ->assertRedirect();

        $tersimpan = $pelanggan->refresh()->gambar;
        $this->sampah[] = $tersimpan;

        $this->assertNotNull($tersimpan, "Unggahan {$jenis} lewat layar pelanggan seharusnya tersimpan.");
        $this->assertStringEndsWith('.webp', $tersimpan);

        $this->assertTrue(Storage::disk(FotoProfil::DISK)->exists($tersimpan));
        $this->assertFileDoesNotExist(public_path(FotoProfil::FOLDER_LAMA . '/' . $tersimpan));

        $isi = Storage::disk(FotoProfil::DISK)->get($tersimpan);
        $this->assertSame('RIFF', substr($isi, 0, 4));
        $this->assertSame('WEBP', substr($isi, 8, 4));

        // Satu berkas saja untuk pelanggan ini: yang asli tidak ikut mendarat.
        $milikDia = array_filter(
            Storage::disk(FotoProfil::DISK)->files(),
            fn ($f) => str_starts_with($f, 'profil-' . $pelanggan->getKey() . '_')
        );

        $this->assertCount(1, $milikDia);
    }

    #[Test]
    public function layar_pelanggan_membuang_foto_lama_saat_diganti(): void
    {
        $pelanggan = $this->buatPengguna();
        $admin = $this->buatAdministrator();
        $alamat = route('account.pengguna.update.updatePhoto', $pelanggan->uuid);

        $this->actingAs($admin)->post($alamat, ['gambar' => $this->gambar('png', 'satu.png')]);
        $pertama = $pelanggan->refresh()->gambar;

        $this->actingAs($admin)->post($alamat, ['gambar' => $this->gambar('jpg', 'dua.jpg')]);
        $kedua = $pelanggan->refresh()->gambar;
        $this->sampah[] = $kedua;

        $this->assertNotSame($pertama, $kedua);
        $this->assertFalse(Storage::disk(FotoProfil::DISK)->exists($pertama), 'Foto lama seharusnya sudah dibuang.');
        $this->assertTrue(Storage::disk(FotoProfil::DISK)->exists($kedua));
    }

    #[Test]
    public function gambar_bawaan_tidak_bisa_dihapus_lewat_nama_di_basis_data(): void
    {
        FotoProfil::hapus('no-image.jpg');
        FotoProfil::hapus('../../../.env');

        $this->assertFileExists(public_path(FotoProfil::FOLDER_LAMA . '/no-image.jpg'));
        $this->assertFileExists(base_path('.env'));
    }
}
