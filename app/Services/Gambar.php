<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan gambar unggahan sebagai WebP di storage.
 *
 * Satu pintu untuk semua unggahan gambar yang baru. Sebelum ini tiap pengendali
 * memindahkan berkasnya sendiri ke `public/` dengan aturan penamaan masing-
 * masing, dan hasilnya tujuh folder berisi JPEG 1–7 MB yang tidak pernah
 * disentuh lagi.
 *
 * Tiga hal yang dikerjakan di sini:
 *
 * 1. DIUBAH JADI WEBP. Berkas sumber boleh JPEG, PNG, atau WebP; yang keluar
 *    selalu WebP. Terukur pada flyer Scopus Camp: 1,43 MB jadi 66 KB.
 *
 * 2. BERKAS ASLINYA DIHAPUS. Unggahan sementara milik PHP memang terhapus
 *    sendiri, tetapi berkas yang sudah terlanjur dipindah ke tujuan tidak —
 *    dan itu yang membuat folder unggahan menyimpan dua salinan tiap gambar.
 *
 * 3. DISIMPAN DI STORAGE, bukan di `public/`. Berkas unggahan bukan bagian dari
 *    kode: ia tidak ikut git, tidak ikut deploy, dan tidak boleh hilang saat
 *    kode dipasang ulang. `storage/app/public` ditautkan ke `public/storage`
 *    oleh `php artisan storage:link`.
 *
 * Ukurannya juga dibatasi. Flyer yang diunggah panitia biasanya langsung dari
 * Canva pada 2000px lebih, padahal yang dipakai di layar paling lebar 900px.
 */
class Gambar
{
    /**
     * Cakram tempat unggahan disimpan.
     *
     * 'unggahan', BUKAN 'public'. Root cakram 'public' di proyek ini sudah
     * dialihkan ke public/images dan dipakai ratusan berkas lain — memakainya
     * berarti berkas ini menumpang di folder yang bukan tempatnya, dan
     * AlamatGambar yang merakit alamat "/storage/..." tidak akan menemukannya.
     */
    public const CAKRAM = 'unggahan';

    /** Lebar terbesar yang disimpan; selebihnya diperkecil sebanding. */
    public const LEBAR_MAKS = 1400;

    /** Mutu WebP. 82 dipilih dengan membandingkan hasilnya, bukan ditebak:
     *  di bawah 78 gradien flyer mulai berpita, di atas 88 berkasnya
     *  membesar tanpa bedanya kelihatan. */
    public const MUTU = 82;

    /**
     * Menyimpan satu unggahan dan mengembalikan jalurnya di cakram `public`.
     *
     * Nilai kembaliannya relatif terhadap storage/app/public, misalnya
     * "angkatan/webinar_eksklusif/3f2a....webp" — itu yang disimpan di kolom,
     * dan AlamatGambar yang mengubahnya jadi alamat yang bisa dibuka.
     *
     * null kalau berkasnya bukan gambar yang bisa dibaca; pemanggilnya yang
     * memutuskan apa artinya itu. Dilempar sebagai pengecualian, satu unggahan
     * rusak akan menggagalkan seluruh borang yang sudah susah payah diisi.
     */
    public function simpan(UploadedFile $berkas, string $folder): ?string
    {
        $sumber = $this->baca($berkas->getRealPath());

        if ($sumber === null) {
            return null;
        }

        $jalur = trim($folder, '/') . '/' . Str::uuid() . '.webp';

        $hasil = $this->kecilkan($sumber);
        imagedestroy($sumber);

        ob_start();
        imagewebp($hasil, null, self::MUTU);
        $isi = (string) ob_get_clean();
        imagedestroy($hasil);

        Storage::disk(self::CAKRAM)->put($jalur, $isi);

        /*
         * Berkas sumbernya dihapus di sini juga, bukan diserahkan ke PHP.
         *
         * Unggahan yang masih di folder sementara memang dibersihkan sendiri,
         * tetapi metode ini juga dipakai untuk berkas yang SUDAH ada di
         * cakram — perintah konversi gambar lama memanggilnya begitu — dan di
         * sana tidak ada yang membersihkan.
         */
        $this->hapusAsli($berkas->getRealPath());

        return $jalur;
    }

    /**
     * Mengubah berkas yang SUDAH ada di cakram jadi WebP di storage.
     *
     * Dipakai perintah konversi gambar lama. Mengembalikan jalur barunya, atau
     * null kalau berkasnya tidak terbaca.
     */
    public function dariJalur(string $jalurAsal, string $folder, bool $hapusAsal = true): ?string
    {
        if (! is_file($jalurAsal)) {
            return null;
        }

        $sumber = $this->baca($jalurAsal);

        if ($sumber === null) {
            return null;
        }

        $jalur = trim($folder, '/') . '/' . Str::uuid() . '.webp';

        $hasil = $this->kecilkan($sumber);
        imagedestroy($sumber);

        ob_start();
        imagewebp($hasil, null, self::MUTU);
        $isi = (string) ob_get_clean();
        imagedestroy($hasil);

        Storage::disk(self::CAKRAM)->put($jalur, $isi);

        if ($hapusAsal) {
            $this->hapusAsli($jalurAsal);
        }

        return $jalur;
    }

    /** Membuang berkas WebP yang tidak dipakai lagi. */
    public function buang(?string $jalur): void
    {
        if ($jalur && Storage::disk(self::CAKRAM)->exists($jalur)) {
            Storage::disk(self::CAKRAM)->delete($jalur);
        }
    }

    // ------------------------------------------------------------- dalam

    /**
     * Membaca gambar apa pun jenisnya jadi sumber daya GD.
     *
     * getimagesize(), BUKAN ekstensi nama berkasnya: nama berakhiran .jpg yang
     * isinya PNG itu hal biasa pada unggahan dari ponsel, dan memilih pembaca
     * menurut namanya membuat berkas seperti itu gagal tanpa alasan yang
     * kelihatan.
     *
     * @return \GdImage|null
     */
    private function baca(string $jalur)
    {
        $tentang = @getimagesize($jalur);

        if ($tentang === false) {
            return null;
        }

        $gambar = match ($tentang[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($jalur),
            IMAGETYPE_PNG => @imagecreatefrompng($jalur),
            IMAGETYPE_WEBP => @imagecreatefromwebp($jalur),
            IMAGETYPE_GIF => @imagecreatefromgif($jalur),
            default => false,
        };

        return $gambar === false ? null : $gambar;
    }

    /**
     * Memperkecil sampai LEBAR_MAKS; yang sudah lebih kecil dibiarkan.
     *
     * Ketembusan PNG ikut dipertahankan — logo berlatar tembus yang diratakan
     * jadi hitam adalah cacat yang baru ketahuan sesudah terpasang di halaman.
     *
     * @param  \GdImage  $sumber
     * @return \GdImage
     */
    private function kecilkan($sumber)
    {
        $l = imagesx($sumber);
        $t = imagesy($sumber);

        if ($l <= self::LEBAR_MAKS) {
            imagepalettetotruecolor($sumber);
            imagealphablending($sumber, false);
            imagesavealpha($sumber, true);

            return $sumber;
        }

        $lb = self::LEBAR_MAKS;
        $tb = (int) round($t * ($lb / $l));

        $baru = imagecreatetruecolor($lb, $tb);
        imagealphablending($baru, false);
        imagesavealpha($baru, true);
        imagefill($baru, 0, 0, imagecolorallocatealpha($baru, 0, 0, 0, 127));
        imagealphablending($baru, true);

        imagecopyresampled($baru, $sumber, 0, 0, 0, 0, $lb, $tb, $l, $t);

        return $baru;
    }

    /**
     * Menghapus berkas asal, dengan satu penjagaan.
     *
     * Yang dihapus HARUS berada di dalam folder unggahan sementara, `public/`,
     * atau `storage/`. Tanpa batas itu, satu jalur yang salah dari pemanggil
     * bisa menghapus berkas mana pun yang bisa dijangkau proses PHP.
     */
    private function hapusAsli(string $jalur): void
    {
        $nyata = realpath($jalur);

        if ($nyata === false || ! is_file($nyata)) {
            return;
        }

        $boleh = array_filter([
            realpath(sys_get_temp_dir()),
            realpath(public_path()),
            realpath(storage_path()),
        ]);

        foreach ($boleh as $akar) {
            if (str_starts_with($nyata, $akar . DIRECTORY_SEPARATOR)) {
                @unlink($nyata);

                return;
            }
        }
    }
}
