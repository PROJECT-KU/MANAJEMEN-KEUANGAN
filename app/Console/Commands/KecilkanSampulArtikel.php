<?php

namespace App\Console\Commands;

use App\Artikel;
use App\Services\Gambar;
use Illuminate\Console\Command;

/**
 * Mengecilkan sampul artikel blog yang telanjur tersimpan mentah.
 *
 * Terukur 19 Okt 2026 di /blog: dua belas sampul berjumlah 5,43 MB dari
 * total 6,84 MB halaman itu — yang terbesar 976 KB. Semuanya PNG 1280x720,
 * digambar 411x257 di kartu dan 691x389 di sorotan. Di kuota ponsel, orang
 * membayar lima megabait untuk dua belas gambar seukuran kartu nama.
 *
 * Bentuknya MENYALIN angkatan:kecilkan-gambar — perintah yang sudah ada dan
 * sudah terbukti untuk perkara yang sama persis di sampul angkatan. Barisnya
 * diperbarui ke nama .webp yang baru, lalu berkas lamanya dihapus HANYA
 * kalau tidak ada artikel lain yang masih menunjuknya: satu gambar bisa
 * dipakai beberapa tulisan sekaligus.
 *
 * Sampulnya ada di public/images/, bukan di jalur yang tersimpan di kolom —
 * kolomnya cuma nama berkas, dan tampilannya yang menambahkan 'images/'.
 */
class KecilkanSampulArtikel extends Command
{
    protected $signature = 'artikel:kecilkan-sampul
                            {--kering : Hanya melaporkan, tidak mengubah apa pun}
                            {--minimal=120 : Lewati berkas yang sudah di bawah ukuran ini (KB)}';

    protected $description = 'Mengecilkan sampul artikel blog yang tersimpan mentah';

    private const FOLDER = 'images';

    public function handle(Gambar $gambar): int
    {
        $kering = (bool) $this->option('kering');
        $minimal = (int) $this->option('minimal') * 1024;

        $diperiksa = 0;
        $diubah = 0;
        $sebelum = 0;
        $sesudah = 0;

        foreach (Artikel::all() as $artikel) {
            foreach (['gambar_depan', 'gambar_cover'] as $kolom) {
                $nilai = trim((string) $artikel->{$kolom});

                if ($nilai === '') {
                    continue;
                }

                // basename(): kolomnya pernah terisi jalur lengkap di sebagian
                // baris lama, dan public_path('images/'.$jalur) meleset diam-
                // diam kalau nilainya sudah memuat foldernya sendiri.
                $nama = basename($nilai);
                $jalur = public_path(self::FOLDER . '/' . $nama);

                if (! is_file($jalur)) {
                    continue;
                }

                $diperiksa++;
                $ukuran = filesize($jalur);
                $tentang = @getimagesize($jalur);
                $lebar = $tentang[0] ?? 0;

                // Yang sudah WebP dan sudah ringan dibiarkan: menyalinnya
                // ulang hanya menurunkan mutunya tanpa menghemat apa pun.
                if (str_ends_with(strtolower($nama), '.webp') && $ukuran < $minimal) {
                    continue;
                }

                if ($ukuran < $minimal && $lebar <= Gambar::LEBAR_MAKS) {
                    continue;
                }

                $namaBaru = preg_replace('/\.[^.\/]+$/', '', $nama) . '.webp';
                $jalurBaru = public_path(self::FOLDER . '/' . $namaBaru);

                $this->line(sprintf('  %s  %dx%d  %d KB', $nama, $lebar, $tentang[1] ?? 0, round($ukuran / 1024)));

                $sebelum += $ukuran;

                if ($kering) {
                    $diubah++;

                    continue;
                }

                if (! $gambar->keJalur($jalur, $jalurBaru)) {
                    $this->warn('    gagal dibaca, dilewati');

                    continue;
                }

                $sesudah += filesize($jalurBaru);

                $artikel->forceFill([$kolom => $namaBaru])->saveQuietly();

                /*
                 * Berkas lama dihapus SESUDAH barisnya menunjuk yang baru, dan
                 * hanya kalau tidak ada artikel lain yang masih memakainya —
                 * termasuk lewat kolom yang satunya.
                 */
                if ($jalurBaru !== $jalur) {
                    $masihDipakai = Artikel::where('id', '!=', $artikel->getKey())
                        ->where(function ($kueri) use ($nama) {
                            $kueri->where('gambar_depan', $nama)->orWhere('gambar_cover', $nama);
                        })
                        ->exists()
                        || ($kolom === 'gambar_depan' && basename((string) $artikel->gambar_cover) === $nama)
                        || ($kolom === 'gambar_cover' && basename((string) $artikel->gambar_depan) === $nama);

                    if (! $masihDipakai) {
                        @unlink($jalur);
                    }
                }

                $this->info(sprintf('    -> %s  %d KB', $namaBaru, round(filesize($jalurBaru) / 1024)));
                $diubah++;
            }
        }

        $this->newLine();
        $this->info(sprintf('%d berkas diperiksa, %d %sdikecilkan.',
            $diperiksa, $diubah, $kering ? 'AKAN ' : ''));

        if (! $kering && $sebelum > 0) {
            $this->info(sprintf('%d KB -> %d KB (turun %d%%).',
                round($sebelum / 1024), round($sesudah / 1024),
                round((1 - $sesudah / $sebelum) * 100)));
        }

        return self::SUCCESS;
    }
}
