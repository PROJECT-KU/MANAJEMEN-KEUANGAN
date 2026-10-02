<?php

namespace App\Console\Commands;

use App\KategoriLayanan;
use App\Services\Gambar;
use Illuminate\Console\Command;

/**
 * Mengecilkan sampul dan foto pemateri angkatan yang telanjur tersimpan mentah.
 *
 * Sampai 2 Okt 2026 kedua unggahan itu dipindah apa adanya ke public/, tanpa
 * dikecilkan. Terukur: satu foto pemateri 4000x2252 piksel seberat 2,8 MB,
 * padahal di halaman publik tampil 230x287 — dan ikut terunduh di layar
 * pertama halaman iklan.
 *
 * Jalur unggahnya sudah diperbaiki; perintah ini untuk yang sudah berada di
 * sana. Barisnya diperbarui ke nama .webp yang baru, lalu berkas lamanya
 * dihapus HANYA kalau tidak ada angkatan lain yang masih menunjuknya — satu
 * flyer dan satu foto pemateri biasa dipakai beberapa angkatan sekaligus.
 */
class KecilkanGambarAngkatan extends Command
{
    protected $signature = 'angkatan:kecilkan-gambar
                            {--kering : Hanya melaporkan, tidak mengubah apa pun}
                            {--minimal=200 : Lewati berkas yang sudah di bawah ukuran ini (KB)}';

    protected $description = 'Mengecilkan sampul & foto pemateri angkatan yang tersimpan mentah';

    public function handle(Gambar $gambar): int
    {
        $kering = (bool) $this->option('kering');
        $minimal = (int) $this->option('minimal') * 1024;

        $diperiksa = 0;
        $diubah = 0;
        $hematSebelum = 0;
        $hematSesudah = 0;

        foreach (KategoriLayanan::all() as $angkatan) {
            foreach (['gambar', 'pemateri_foto'] as $kolom) {
                $nilai = trim((string) $angkatan->{$kolom});

                if ($nilai === '') {
                    continue;
                }

                $jalur = public_path($nilai);

                if (! is_file($jalur)) {
                    continue;
                }

                $diperiksa++;
                $ukuran = filesize($jalur);

                // Yang sudah WebP dan sudah ringan dibiarkan: menyalinnya ulang
                // hanya menurunkan mutunya tanpa menghemat apa pun.
                if (str_ends_with(strtolower($jalur), '.webp') && $ukuran < $minimal) {
                    continue;
                }

                $tentang = @getimagesize($jalur);
                $lebar = $tentang[0] ?? 0;

                if ($ukuran < $minimal && $lebar <= Gambar::LEBAR_MAKS) {
                    continue;
                }

                $baru = preg_replace('/\.[^.\/]+$/', '', $nilai) . '.webp';
                $jalurBaru = public_path($baru);

                $this->line(sprintf('  %s  %dx%d  %d KB', $nilai, $lebar, $tentang[1] ?? 0, round($ukuran / 1024)));

                $hematSebelum += $ukuran;

                if ($kering) {
                    $diubah++;

                    continue;
                }

                if (! $gambar->keJalur($jalur, $jalurBaru)) {
                    $this->warn('    gagal dibaca, dilewati');

                    continue;
                }

                $hematSesudah += filesize($jalurBaru);

                $angkatan->forceFill([$kolom => $baru])->saveQuietly();

                /*
                 * Berkas lama dihapus SESUDAH barisnya menunjuk yang baru, dan
                 * hanya kalau tidak ada angkatan lain yang masih memakainya.
                 */
                if ($jalurBaru !== $jalur) {
                    $masihDipakai = KategoriLayanan::where('id', '!=', $angkatan->getKey())
                        ->where($kolom, $nilai)
                        ->exists();

                    if (! $masihDipakai) {
                        @unlink($jalur);
                    }
                }

                $this->info(sprintf('    -> %s  %d KB', $baru, round(filesize($jalurBaru) / 1024)));
                $diubah++;
            }
        }

        $this->newLine();
        $this->info(sprintf('%d berkas diperiksa, %d %sdikecilkan.',
            $diperiksa, $diubah, $kering ? 'AKAN ' : ''));

        if (! $kering && $hematSebelum > 0) {
            $this->info(sprintf('%d KB -> %d KB (turun %d%%).',
                round($hematSebelum / 1024),
                round($hematSesudah / 1024),
                round((1 - $hematSesudah / $hematSebelum) * 100)));
        }

        if ($kering) {
            $this->comment('Jalan kering: tidak ada yang diubah.');
        }

        return self::SUCCESS;
    }
}
