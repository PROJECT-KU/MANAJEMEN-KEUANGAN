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
    /**
     * @param  bool  $tegakkan  meluruskan putaran EXIF-nya. MATI secara
     *                          bawaan, atas keputusan pemiliknya: foto yang
     *                          sudah melewati WhatsApp kehilangan penandanya
     *                          sementara pikselnya tetap miring, dan menebak
     *                          berarti sebagian foto justru dimiringkan
     *                          sistem. Galeri foto memakai tombol putar
     *                          manual sebagai gantinya.
     *
     *                          Dinyalakan di layar yang TIDAK punya tombol
     *                          itu — bukti transfer — sebab di sana yang
     *                          miring tidak bisa dibetulkan siapa pun.
     */
    public function simpan(UploadedFile $berkas, string $folder, bool $tegakkan = false): ?string
    {
        $sumber = $this->baca($berkas->getRealPath(), $tegakkan);

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

    /**
     * Mengecilkan sebuah berkas dan menulisnya sebagai WebP ke jalur ABSOLUT.
     *
     * Dipakai oleh unggahan yang tujuannya bukan cakram `unggahan` melainkan
     * folder di dalam public/ — sampul dan foto pemateri angkatan, yang
     * alamatnya sudah telanjur beredar lewat folder itu dan tidak bisa
     * dipindah tanpa memutus gambar di halaman yang sudah terbit.
     *
     * Mengembalikan true kalau berhasil. Pemanggil WAJIB menyiapkan jalan
     * mundur: kalau gambarnya tidak terbaca (format aneh, berkas rusak),
     * lebih baik menyimpan berkas aslinya daripada tidak menyimpan apa pun.
     */
    public function keJalur(string $jalurAsal, string $jalurTujuan): bool
    {
        if (! is_file($jalurAsal)) {
            return false;
        }

        // Sampul dan foto pemateri memang selalu diluruskan sejak semula.
        $sumber = $this->baca($jalurAsal, true);

        if ($sumber === null) {
            return false;
        }

        $hasil = $this->kecilkan($sumber);

        if ($hasil !== $sumber) {
            imagedestroy($sumber);
        }

        $folder = dirname($jalurTujuan);

        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $berhasil = imagewebp($hasil, $jalurTujuan, self::MUTU);
        imagedestroy($hasil);

        return (bool) $berhasil;
    }

    /**
     * Membakukan putaran EXIF ke pikselnya.
     *
     * BUKAN memutar gambar sesuka hati. Foto ponsel sering disimpan mendatar
     * dengan satu tanda EXIF yang menyuruh penampilnya memutar; peramban
     * menghormati tanda itu pada JPEG, jadi di layar tampak benar.
     *
     * GD mengabaikannya, dan WebP tidak membawa tanda itu. Jadi tanpa
     * langkah ini, foto yang semula tampak tegak berubah jadi miring begitu
     * dikonversi - terukur 2 Okt 2026 pada foto pemateri ber-Orientation 6.
     *
     * Hanya tiga nilai yang ditangani, dan hanya kalau tandanya MEMANG ada.
     * Yang tanpa tanda tidak disentuh sama sekali.
     */
    private function tegakkanDariExif($gambar, string $jalur)
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        $tentang = @getimagesize($jalur);

        // exif_read_data() hanya berlaku untuk JPEG/TIFF; dipanggil pada
        // format lain ia melempar peringatan tanpa guna.
        if (($tentang[2] ?? null) !== IMAGETYPE_JPEG) {
            return $gambar;
        }

        $exif = @exif_read_data($jalur);
        $arah = $exif['Orientation'] ?? null;

        /*
         * imagerotate() memutar BERLAWANAN arah jarum jam untuk sudut positif
         * - diperiksa langsung, bukan dibaca dari dokumentasi. Karena itu
         * Orientation 6, yang perlu diputar searah jarum jam, memakai -90.
         */
        $derajat = match ($arah) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($derajat === null) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $derajat, 0);

        if ($diputar === false) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
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
    /**
     * @param  bool  $tegakkan  meluruskan putaran EXIF berkas yang formatnya
     *                          dikenal GD. HEIC selalu diluruskan, lihat di
     *                          bawah — itu bagian dari membacanya dengan
     *                          benar, bukan pilihan kebijakan.
     */
    private function baca(string $jalur, bool $tegakkan = false)
    {
        $tentang = @getimagesize($jalur);

        /*
         * getimagesize() tidak mengenal HEIC/HEIF — format bawaan foto iPhone.
         * Yang seperti itu dibongkar dulu jadi berkas sementara oleh alat
         * luar, baru dibaca GD seperti biasa.
         */
        if ($tentang === false) {
            $sementara = $this->heicJadiGambar($jalur);

            if ($sementara === null) {
                return null;
            }

            $gambar = $this->bacaBiasa($sementara);

            /*
             * HEIC SELALU diluruskan, tanpa menunggu diminta — dan itu bukan
             * melanggar aturan "sistem tidak memutar sendiri".
             *
             * Aturan itu lahir dari foto yang penandanya HILANG di jalan
             * (lewat WhatsApp, lewat alat ekspor) sementara pikselnya tetap
             * miring: dari berkas begitu tidak ada yang bisa ditebak. HEIC
             * tidak punya keadaan itu. Penandanya selalu ikut, dan buffer
             * mentahnya memang TIDAK PERNAH jadi yang dilihat orang —
             * pemiliknya sendiri melihat fotonya sudah tegak di Finder.
             * Membiarkannya mentah berarti menampilkan sesuatu yang belum
             * pernah dilihat siapa pun.
             *
             * Penandanya dicari di berkas SEMENTARA, bukan di HEIC aslinya.
             * exif_read_data() tidak bisa membaca HEIC sama sekali — diperiksa
             * langsung pada foto iPhone 5712x4284: getimagesize() gagal,
             * exif_read_data() mengembalikan false. Yang membawa penandanya
             * justru hasil bongkarannya.
             */
            if ($gambar !== null) {
                $gambar = $this->tegakkanDariExif($gambar, $sementara);
            }

            @unlink($sementara);

            return $gambar;
        }

        $gambar = $this->bacaBiasa($jalur);

        if ($gambar === null || ! $tegakkan) {
            return $gambar;
        }

        return $this->tegakkanDariExif($gambar, $jalur);
    }

    /** Membaca berkas yang formatnya SUDAH dikenal GD. */
    private function bacaBiasa(string $jalur)
    {
        $tentang = @getimagesize($jalur);

        $gambar = match ($tentang[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($jalur),
            IMAGETYPE_PNG => @imagecreatefrompng($jalur),
            IMAGETYPE_WEBP => @imagecreatefromwebp($jalur),
            IMAGETYPE_GIF => @imagecreatefromgif($jalur),
            default => false,
        };

        return $gambar === false ? null : $gambar;
    }

    /**
     * Memutar berkas yang SUDAH tersimpan, atas perintah orang.
     *
     * Putaran OTOMATIS mengikuti EXIF sengaja TIDAK dipakai. Pemiliknya minta
     * sistem tidak memutar sendiri, dan alasannya terbukti di lapangan: foto
     * yang sudah melewati WhatsApp atau alat ekspor lain kehilangan penanda
     * EXIF-nya sementara pikselnya tetap miring — tidak ada yang bisa ditebak
     * dari berkas seperti itu, dan menebak berarti sebagian foto justru
     * dimiringkan oleh sistem.
     *
     * Gantinya: yang tersimpan persis seperti yang diunggah, dan yang
     * memutarnya orang, sekali klik, saat melihat hasilnya sendiri.
     *
     * Dicatat jujur: foto iPhone yang MASIH membawa penanda EXIF jadi perlu
     * satu klik itu, padahal sebelumnya lurus sendiri.
     *
     * @param  int  $derajat  90 atau -90; positif searah jarum jam
     */
    /**
     * Membongkar HEIC/HEIF jadi berkas sementara; null kalau tidak ada yang
     * bisa.
     *
     * HEIC adalah format bawaan foto iPhone, dan GD tidak bisa membacanya sama
     * sekali. Yang bisa membongkarnya berbeda-beda per peladen, jadi dicoba
     * berurutan — bukan dipatok satu:
     *
     *   1. Imagick dengan delegasi libheif, kalau ekstensinya terpasang
     *   2. `heif-convert` dari libheif-tools, yang lazim di peladen Linux
     *   3. `sips`, hanya ada di macOS — berguna di mesin pengembang
     *
     * Kalau TIDAK ADA yang bisa, mengembalikan null dan pengunggahnya diberi
     * tahu terus terang. Itu jauh lebih baik daripada diam: foto yang hilang
     * tanpa penjelasan membuat orang mengunggahnya berkali-kali.
     */
    private function heicJadiGambar(string $jalur): ?string
    {
        if (! $this->sepertiHeic($jalur)) {
            return null;
        }

        $sidik = sys_get_temp_dir() . '/heic-' . Str::uuid();
        $keluar = $sidik . '.png';

        // 1. Imagick — paling bersih, tanpa memanggil proses luar.
        if (class_exists(\Imagick::class)) {
            try {
                $im = new \Imagick($jalur);

                /*
                 * Imagick membaca putaran HEIC-nya sendiri, tetapi PNG tidak
                 * bisa membawa penanda itu — jadi putarannya dibakukan ke
                 * pikselnya di sini, selagi masih terbaca.
                 */
                if (method_exists($im, 'autoOrient')) {
                    $im->autoOrient();
                }

                $im->setImageFormat('png');
                $im->writeImage($keluar);
                $im->clear();

                if (is_file($keluar)) {
                    return $keluar;
                }
            } catch (\Throwable $e) {
                // Lanjut ke cara berikutnya; bukan kegagalan yang perlu
                // dilaporkan selama masih ada jalan lain.
            }
        }

        // 2 & 3. Alat baris perintah. Dilewati kalau exec() dimatikan hosting —
        // itu hal biasa di hosting bersama.
        /*
         * sips menulis JPEG, bukan PNG — dan itu BUKAN soal selera format.
         *
         * Foto iPhone disimpan mendatar dengan satu penanda yang menyuruh
         * penampilnya memutar. Diukur langsung pada IMG_3675.HEIC 5712x4284:
         *
         *   sips -s format png   -> 5712x4284, penandanya HILANG
         *   sips -s format jpeg  -> 5712x4284, Orientation: 6 TERBAWA
         *
         * PNG tidak punya tempat untuk penanda itu, jadi lewat PNG tidak ada
         * lagi yang bisa tahu fotonya harus diputar — dan yang tersimpan
         * miring 90 derajat. Lewat JPEG, pelurusan EXIF yang sudah ada di
         * baca() mendapat bahannya.
         *
         * heif-convert tetap menulis PNG: libheif menerapkan putarannya
         * sendiri saat membongkar, jadi hasilnya sudah tegak dan tidak perlu
         * penanda apa pun.
         */
        foreach ([
            ['heif-convert', '%s %s', '.png'],
            ['sips', '-s format jpeg -s formatOptions best %s --out %s', '.jpg'],
        ] as [$alat, $pola, $akhiran]) {
            $lokasi = $this->cariAlat($alat);

            if ($lokasi === null) {
                continue;
            }

            $tujuan = $sidik . $akhiran;

            $perintah = $lokasi . ' ' . sprintf($pola, escapeshellarg($jalur), escapeshellarg($tujuan));
            @exec($perintah . ' 2>/dev/null', $keluaran, $kode);

            if ($kode === 0 && is_file($tujuan) && @getimagesize($tujuan) !== false) {
                return $tujuan;
            }

            @unlink($tujuan);
        }

        @unlink($keluar);

        return null;
    }

    /** Berkasnya memang HEIC/HEIF, dilihat dari isinya — bukan dari namanya. */
    private function sepertiHeic(string $jalur): bool
    {
        $kepala = @file_get_contents($jalur, false, null, 0, 32);

        if ($kepala === false || strlen($kepala) < 12) {
            return false;
        }

        /*
         * Kotak "ftyp" pada bita ke-4, lalu merek berawalan heic/heix/mif1/msf1
         * — bentuk berkas HEIF. Diperiksa dari ISINYA, bukan ekstensinya: nama
         * berakhiran .jpg yang isinya HEIC itu hal biasa pada berkas yang sudah
         * berpindah-pindah aplikasi.
         */
        if (substr($kepala, 4, 4) !== 'ftyp') {
            return false;
        }

        $merek = strtolower(substr($kepala, 8, 4));

        return in_array($merek, ['heic', 'heix', 'heim', 'heis', 'hevc', 'mif1', 'msf1', 'avif'], true);
    }

    /** Lokasi alat baris perintah; null kalau tidak ada atau exec dimatikan. */
    private function cariAlat(string $nama): ?string
    {
        $mati = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (! function_exists('exec') || in_array('exec', $mati, true)) {
            return null;
        }

        @exec('command -v ' . escapeshellarg($nama) . ' 2>/dev/null', $keluaran, $kode);

        $lokasi = trim((string) ($keluaran[0] ?? ''));

        return $kode === 0 && $lokasi !== '' ? $lokasi : null;
    }

    /** Peladen ini bisa membaca HEIC. Dipakai layar unggah untuk berterus terang. */
    public function bisaHeic(): bool
    {
        if (class_exists(\Imagick::class)) {
            try {
                if (in_array('HEIC', (new \Imagick())->queryFormats(), true)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // abaikan; coba alat baris perintah
            }
        }

        return $this->cariAlat('heif-convert') !== null || $this->cariAlat('sips') !== null;
    }

    public function putar(string $jalur, int $derajat): bool
    {
        if (! in_array($derajat, [90, -90, 180], true)) {
            return false;
        }

        if (! Storage::disk(self::CAKRAM)->exists($jalur)) {
            return false;
        }

        $sumber = @imagecreatefromstring(Storage::disk(self::CAKRAM)->get($jalur));

        if ($sumber === false) {
            return false;
        }

        /*
         * Sudut imagerotate() berlawanan arah jarum jam, jadi tandanya dibalik
         * supaya $derajat positif berarti searah jarum jam — itu yang dipahami
         * orang saat menekan tombol "putar kanan".
         */
        $hasil = imagerotate($sumber, -$derajat, imagecolorallocatealpha($sumber, 0, 0, 0, 127));
        imagedestroy($sumber);

        if ($hasil === false) {
            return false;
        }

        imagealphablending($hasil, false);
        imagesavealpha($hasil, true);

        ob_start();
        imagewebp($hasil, null, self::MUTU);
        $isi = (string) ob_get_clean();
        imagedestroy($hasil);

        // Ditulis ke jalur yang SAMA: alamatnya sudah beredar di halaman
        // publik dan mungkin sudah ditembolok, jadi mengganti namanya berarti
        // tautan lama menunjuk berkas yang tidak ada.
        Storage::disk(self::CAKRAM)->put($jalur, $isi);

        return true;
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
