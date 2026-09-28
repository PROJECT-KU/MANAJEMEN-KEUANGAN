<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan foto profil: selalu WebP, selalu di storage.
 *
 * Dua hal yang dikerjakan di sini dan tidak boleh dipisah:
 *
 * 1. Berkas unggahan diubah ke WebP lalu yang asli dibuang. Yang tersimpan
 *    hanya satu berkas .webp — bukan dua. Selain jauh lebih kecil, ini juga
 *    berarti isi berkasnya digambar ulang oleh GD, sehingga apa pun yang
 *    menumpang di dalam berkas gambar (skrip, data EXIF, sisa berkas lain)
 *    tidak ikut tersimpan.
 *
 * 2. Berkasnya pindah dari public/assets/img/profil ke storage. Folder di
 *    bawah public/ disajikan apa adanya oleh peladen; storage tidak.
 *
 * Foto lama yang masih menunjuk ke public/assets/img/profil TETAP tampil:
 * url() memeriksa storage dulu, lalu folder lama. Tanpa itu, ratusan foto
 * yang sudah ada langsung hilang begitu perubahan ini naik.
 */
class FotoProfil
{
    public const DISK = 'profil';

    /** Folder lama di bawah public/, dari sebelum pindah ke storage. */
    public const FOLDER_LAMA = 'assets/img/profil';

    /** Gambar bawaan milik bersama; tidak boleh ikut terhapus. */
    public const BAWAAN = ['no-image.jpg', 'default.png'];

    /** Yang boleh diunggah. SVG tidak: isinya bisa memuat skrip. */
    public const EKSTENSI_MASUK = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Mutu WebP. 82 sudah tidak terbedakan mata pada foto orang. */
    private const MUTU = 82;

    /**
     * Simpan unggahan sebagai WebP dan kembalikan nama berkasnya.
     *
     * Berkas aslinya tidak pernah ikut tersimpan: GD membaca isinya dari
     * berkas sementara milik PHP, hasilnya ditulis sebagai WebP, lalu
     * berkas sementara itu ditutup sendiri oleh PHP di akhir permintaan.
     */
    public static function simpan(UploadedFile $berkas, string $awalan = 'profil'): ?string
    {
        // extension() menebak dari isi berkas, bukan dari nama kiriman.
        $ekstensi = Str::lower((string) $berkas->extension());

        if (! in_array($ekstensi, self::EKSTENSI_MASUK, true)) {
            return null;
        }

        $sumber = self::baca($berkas->getRealPath(), $ekstensi);

        if ($sumber === null) {
            return null;
        }

        // JPEG dari ponsel hampir selalu membawa penanda orientasi di EXIF,
        // dan WebP tidak menyimpannya. Tanpa diputar di sini, foto yang tadinya
        // tampil tegak akan tersimpan miring.
        if (in_array($ekstensi, ['jpg', 'jpeg'], true)) {
            $sumber = self::tegakkan($sumber, $berkas->getRealPath());
        }

        // PNG dan GIF bisa punya bagian tembus pandang; tanpa dua baris ini
        // bagian itu jadi hitam pekat di hasil WebP-nya.
        imagepalettetotruecolor($sumber);
        imagealphablending($sumber, false);
        imagesavealpha($sumber, true);

        $nama = Str::slug($awalan) . '_' . now()->timestamp . '_' . Str::random(8) . '.webp';

        $sementara = tempnam(sys_get_temp_dir(), 'webp');

        if ($sementara === false) {
            imagedestroy($sumber);

            return null;
        }

        $jadi = imagewebp($sumber, $sementara, self::MUTU);
        imagedestroy($sumber);

        if (! $jadi) {
            @unlink($sementara);

            return null;
        }

        $isi = file_get_contents($sementara);
        @unlink($sementara);

        if ($isi === false || ! Storage::disk(self::DISK)->put($nama, $isi)) {
            return null;
        }

        return $nama;
    }

    /** Hapus foto, baik yang sudah di storage maupun yang masih di folder lama. */
    public static function hapus(?string $nama): void
    {
        $nama = basename((string) $nama);

        if ($nama === '' || $nama === '.' || $nama === '..' || in_array($nama, self::BAWAAN, true)) {
            return;
        }

        $ekstensi = Str::lower((string) pathinfo($nama, PATHINFO_EXTENSION));

        if (! in_array($ekstensi, array_merge(self::EKSTENSI_MASUK, ['webp']), true)) {
            return;
        }

        if (Storage::disk(self::DISK)->exists($nama)) {
            Storage::disk(self::DISK)->delete($nama);
        }

        $jalurLama = public_path(self::FOLDER_LAMA . '/' . $nama);

        if (is_file($jalurLama)) {
            @unlink($jalurLama);
        }
    }

    /** Alamat gambar yang bisa dipasang di src. Selalu mengembalikan sesuatu. */
    public static function url(?string $nama): string
    {
        $nama = basename((string) $nama);

        if ($nama !== '' && ! in_array($nama, self::BAWAAN, true) && $nama !== '.' && $nama !== '..') {
            if (Storage::disk(self::DISK)->exists($nama)) {
                return Storage::disk(self::DISK)->url($nama);
            }

            if (is_file(public_path(self::FOLDER_LAMA . '/' . $nama))) {
                return asset(self::FOLDER_LAMA . '/' . $nama);
            }
        }

        return self::bawaan();
    }

    /** Apakah ini foto milik pengguna sendiri, bukan gambar bawaan. */
    public static function punyaFoto(?string $nama): bool
    {
        $nama = basename((string) $nama);

        if ($nama === '' || in_array($nama, self::BAWAAN, true)) {
            return false;
        }

        return Storage::disk(self::DISK)->exists($nama)
            || is_file(public_path(self::FOLDER_LAMA . '/' . $nama));
    }

    public static function bawaan(): string
    {
        return asset(self::FOLDER_LAMA . '/no-image.jpg');
    }

    /** @return \GdImage|null */
    private static function baca(string $jalur, string $ekstensi)
    {
        try {
            $gambar = match ($ekstensi) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($jalur),
                'png' => @imagecreatefrompng($jalur),
                // Hanya bingkai pertama yang terbaca; GIF bergerak jadi diam.
                'gif' => @imagecreatefromgif($jalur),
                'webp' => @imagecreatefromwebp($jalur),
                default => false,
            };
        } catch (\Throwable $e) {
            Log::warning('Gambar profil gagal dibaca: ' . $e->getMessage());

            return null;
        }

        return $gambar ?: null;
    }

    /** @return \GdImage */
    private static function tegakkan($gambar, string $jalur)
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        try {
            $exif = @exif_read_data($jalur);
        } catch (\Throwable $e) {
            return $gambar;
        }

        $sudut = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($sudut === 0) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $sudut, 0);

        if ($diputar === false) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
    }
}
