<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use App\Services\Gambar;
use App\Support\AlamatGambar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Mengubah sampul dan foto pemateri yang sudah ada jadi WebP di storage.
 *
 * Dijalankan SEKALI setelah deploy. Sebelum ini semua sampul angkatan tersimpan
 * di `public/ScopusCamp/` dan `public/bibliometrik/` sebagai JPEG/PNG — berkas
 * terbesar 7,5 MB, dan halaman publik mengirimkannya apa adanya ke pengunjung.
 *
 * ---------------------------------------------------------------------------
 * Yang membuat perintah ini tidak merusak apa pun
 * ---------------------------------------------------------------------------
 *
 * 1. BERKAS YANG DIPAKAI BERSAMA dikonversi SEKALI. Empat puluh satu angkatan
 *    Yogyakarta menunjuk satu berkas `camp-jogja.jpeg` yang sama; dikonversi
 *    per baris, hasilnya 41 salinan WebP yang isinya identik, dan mengganti
 *    flyer tidak lagi cukup sekali untuk semuanya.
 *
 * 2. BERKAS ASLINYA DIHAPUS hanya kalau WebP-nya sudah benar-benar tersimpan
 *    DAN tidak ada baris lain yang masih menunjuk berkas itu.
 *
 * 3. YANG GAGAL DILEWATI, bukan membatalkan seluruh proses. Satu berkas rusak
 *    di tengah 60 angkatan tidak boleh membuat 59 lainnya batal.
 *
 * Jalur mundurnya ada di AlamatGambar: selama nilai lama masih di basis data,
 * halaman publik tetap membacanya dari `public/<folder>`. Jadi jeda antara
 * deploy kode dan perintah ini dijalankan tidak mengosongkan sampul siapa pun.
 *
 * ---------------------------------------------------------------------------
 * URUTAN DEPLOYNYA MENGIKAT
 * ---------------------------------------------------------------------------
 *
 * Berkas asal sampul IKUT TERLACAK GIT. Karena itu penghapusannya TIDAK boleh
 * dicommit bersamaan dengan kode ini: `git pull` akan menghapusnya dari
 * peladen lebih dulu, dan perintah ini lalu tidak punya apa pun untuk
 * dikonversi — sementara basis datanya masih menunjuk berkas yang sudah hilang.
 *
 * Urutan yang benar:
 *
 *   1. deploy kode  (berkas asal masih ada, halaman publik membacanya seperti
 *                    biasa lewat jalur mundur AlamatGambar)
 *   2. `php artisan angkatan:konversi-gambar --kering`  -> lihat apa yang akan
 *      tersentuh
 *   3. `php artisan angkatan:konversi-gambar`           -> konversi + hapus
 *      berkas asal DI PELADEN
 *   4. periksa halaman publiknya
 *   5. baru buang berkas asalnya dari git, sebagai commit tersendiri
 */
class KonversiGambarAngkatan extends Command
{
    protected $signature = 'angkatan:konversi-gambar
                            {--kering : Hanya melaporkan, tidak mengubah apa pun}
                            {--simpan-asli : Biarkan berkas aslinya, jangan dihapus}';

    protected $description = 'Mengubah sampul & foto pemateri angkatan jadi WebP di storage';

    public function handle(Gambar $gambar): int
    {
        $kering = (bool) $this->option('kering');
        $hapusAsal = ! $this->option('simpan-asli');

        $semua = KategoriLayanan::query()
            ->where(fn ($q) => $q->whereNotNull('gambar')->orWhereNotNull('pemateri_foto'))
            ->get();

        if ($semua->isEmpty()) {
            $this->info('Tidak ada angkatan yang punya gambar.');

            return self::SUCCESS;
        }

        /*
         * Peta "jalur lama -> jalur WebP baru", supaya berkas yang dipakai
         * bersama cukup dikonversi sekali.
         */
        $sudah = [];
        $diubah = 0;
        $dilewati = 0;
        $hematBita = 0;

        foreach ($semua as $a) {
            foreach (['gambar', 'pemateri_foto'] as $kolom) {
                $nilai = trim((string) $a->{$kolom});

                if ($nilai === '') {
                    continue;
                }

                // Yang sudah berbentuk baru tidak disentuh lagi; perintah ini
                // harus aman dijalankan dua kali.
                if (AlamatGambar::diStorage($nilai)) {
                    continue;
                }

                $asal = $this->cariBerkas($nilai, $a->folderSampul());

                if ($asal === null) {
                    $this->warn(sprintf('  dilewati: %s — berkasnya tidak ada (%s)', $a->nama, $nilai));
                    $dilewati++;

                    continue;
                }

                if (isset($sudah[$asal])) {
                    // Berkas yang sama sudah dikonversi untuk baris lain.
                    if (! $kering) {
                        $a->forceFill([$kolom => $sudah[$asal]])->saveQuietly();
                    }

                    $diubah++;

                    continue;
                }

                $besarAsal = (int) @filesize($asal);

                if ($kering) {
                    $this->line(sprintf('  %s — %s (%s KB)', $a->nama, basename($asal), number_format($besarAsal / 1024)));
                    $sudah[$asal] = '(kering)';
                    $diubah++;

                    continue;
                }

                $baru = $gambar->dariJalur($asal, 'angkatan/' . $a->layanan, false);

                if ($baru === null) {
                    $this->warn(sprintf('  dilewati: %s — berkasnya tidak terbaca', $a->nama));
                    $dilewati++;

                    continue;
                }

                $sudah[$asal] = $baru;

                /*
                 * saveQuietly: perintah ini mengubah 60 baris sekaligus, dan
                 * kait jejak akan menulis 60 baris "diubah: sampul diganti"
                 * yang tidak memberi tahu apa pun kepada siapa pun.
                 */
                $a->forceFill([$kolom => $baru])->saveQuietly();

                // Ukurannya dibaca lewat CAKRAMNYA, bukan dengan menebak
                // jalurnya di cakram: root cakram unggahan bisa dipindah, dan
                // jalur yang ditebak akan diam-diam melaporkan 0 KB.
                $besarBaru = (int) Storage::disk(Gambar::CAKRAM)->size($baru);
                $hematBita += max(0, $besarAsal - $besarBaru);

                $this->line(sprintf(
                    '  %s — %s KB jadi %s KB',
                    mb_substr($a->nama, 0, 40),
                    number_format($besarAsal / 1024),
                    number_format($besarBaru / 1024)
                ));

                $diubah++;
            }
        }

        if (! $kering && $hapusAsal) {
            $this->hapusAsal(array_keys($sudah));
        }

        $this->newLine();
        $this->info(sprintf(
            '%d gambar %sdikonversi, %d dilewati.%s',
            $diubah,
            $kering ? 'AKAN ' : '',
            $dilewati,
            $hematBita > 0 ? ' Hemat ' . number_format($hematBita / 1048576, 1) . ' MB.' : ''
        ));

        if ($kering) {
            $this->comment('Jalan kering: tidak ada yang diubah. Jalankan tanpa --kering untuk benar-benar mengonversi.');
        }

        return self::SUCCESS;
    }

    /**
     * Menemukan berkas asalnya dari nilai kolom yang bentuknya bermacam-macam.
     *
     * Nilai yang beredar: "ScopusCamp/abc.jpeg", "abc.jpeg", dan sesekali
     * jalur dengan awalan lain. Ketiganya dicari di folder layanan yang
     * bersangkutan dan apa adanya di dalam public/.
     */
    private function cariBerkas(string $nilai, string $folder): ?string
    {
        $calon = [
            public_path($folder . '/' . basename($nilai)),
            public_path($nilai),
        ];

        foreach ($calon as $jalur) {
            if (is_file($jalur)) {
                return $jalur;
            }
        }

        return null;
    }

    /**
     * Menghapus berkas asal yang sudah tidak ditunjuk baris mana pun.
     *
     * Diperiksa ulang ke basis data, bukan mengandalkan daftar yang dibawa dari
     * atas: kalau ada baris yang gagal dikonversi dan masih memakai nilai lama,
     * berkasnya tidak boleh ikut hilang.
     *
     * @param  array<int, string>  $jalurAsal
     */
    private function hapusAsal(array $jalurAsal): void
    {
        $dihapus = 0;

        foreach ($jalurAsal as $jalur) {
            $nama = basename($jalur);

            $masihDipakai = KategoriLayanan::where('gambar', 'like', '%' . $nama)
                ->orWhere('pemateri_foto', 'like', '%' . $nama)
                ->exists();

            if ($masihDipakai || ! is_file($jalur)) {
                continue;
            }

            @unlink($jalur);
            $dihapus++;
        }

        if ($dihapus > 0) {
            $this->line(sprintf('  %d berkas asal dihapus.', $dihapus));
        }
    }
}
