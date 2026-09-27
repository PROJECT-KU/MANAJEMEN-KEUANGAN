<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Penyimpanan gambar unggahan yang namanya tidak pernah berasal dari
 * pengunggah.
 *
 * Sebelumnya nama berkas dirangkai dari getClientOriginalExtension(), yaitu
 * ekstensi yang diketik pengunggah. Padahal aturan 'mimes' memeriksa ekstensi
 * TEBAKAN dari isi berkas, bukan nama kirimannya. Akibatnya berkas berisi
 * JPEG asli yang dinamai "x.php" lolos pemeriksaan lalu tersimpan sebagai
 * .php di dalam folder publik — dan ikut dijalankan oleh peladen.
 *
 * Di sini ekstensi selalu ditebak dari isi berkas dan harus ada di daftar
 * putih; nama berkasnya dibuat acak.
 */
class BerkasGambar
{
    /** Ekstensi yang boleh disimpan. SVG sengaja tidak masuk: isinya bisa memuat skrip. */
    public const EKSTENSI_AMAN = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Simpan gambar ke folder di bawah public/ dan kembalikan nama berkasnya.
     *
     * @param  string  $folder  relatif terhadap public/, mis. 'assets/img/profil'
     */
    public static function simpan(UploadedFile $berkas, string $folder, string $awalan = 'img'): ?string
    {
        // extension() menebak dari isi berkas (MIME), bukan dari nama kiriman.
        $ekstensi = Str::lower((string) $berkas->extension());

        if ($ekstensi === 'jpeg') {
            $ekstensi = 'jpg';
        }

        if (! in_array($ekstensi, self::EKSTENSI_AMAN, true)) {
            return null;
        }

        $nama = Str::slug($awalan) . '_' . now()->timestamp . '_' . Str::random(8) . '.' . $ekstensi;

        $tujuan = public_path($folder);

        if (! is_dir($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        $berkas->move($tujuan, $nama);

        return $nama;
    }

    /**
     * Hapus gambar lama. Nama berkas dibersihkan lebih dulu supaya nilai
     * aneh di basis data (mis. "../../.env") tidak bisa menghapus berkas di
     * luar foldernya.
     */
    public static function hapus(string $folder, ?string $nama, array $kecualikan = []): void
    {
        $nama = basename((string) $nama);

        if ($nama === '' || $nama === '.' || $nama === '..' || in_array($nama, $kecualikan, true)) {
            return;
        }

        $ekstensi = Str::lower((string) pathinfo($nama, PATHINFO_EXTENSION));

        if (! in_array($ekstensi, self::EKSTENSI_AMAN, true)) {
            return;
        }

        $jalur = public_path(trim($folder, '/') . '/' . $nama);

        if (is_file($jalur)) {
            @unlink($jalur);
        }
    }
}
