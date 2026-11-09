<?php

namespace App\Console\Commands;

use App\Artikel;
use Illuminate\Console\Command;

/**
 * Menyambungkan kembali sampul artikel yang barisnya menunjuk berkas hilang.
 *
 * ---------------------------------------------------------------------------
 * Bagaimana sampulnya bisa hilang
 * ---------------------------------------------------------------------------
 *
 * `artikel:kecilkan-sampul` mengerjakan TIGA hal sekaligus: mengubah sampul
 * jadi WebP, memperbarui barisnya ke nama baru, lalu menghapus berkas lama.
 * Ketiganya benar — selama dijalankan di lingkungan yang bersangkutan.
 *
 * Yang terjadi 8 Nov 2026 di produksi: perintah itu dijalankan di mesin
 * pengembang, hasil .webp-nya IKUT TERCATAT DI GIT, dan berkas aslinya ikut
 * TERHAPUS dari repositori pada commit yang sama (a32f725f, 22 Okt). Saat
 * commit itu sampai ke peladen, `git pull` mengerjakan persis apa yang
 * tertulis: menulis ketujuh belas .webp dan menghapus ketujuh belas berkas
 * aslinya — serentak, pukul 03:39. Basis data produksi tidak pernah ikut
 * diperbarui, sebab yang memperbaruinya perintah tadi, dan perintah itu
 * tidak pernah dijalankan di sana.
 *
 * Hasilnya: 17 dari 24 kolom gambar menunjuk berkas yang sudah tidak ada.
 * Delapan dari dua belas tulisan kehilangan sampulnya di /blog, dan
 * halamannya tetap 200 — yang 404 cuma gambarnya, jadi tidak ada satu pun
 * galat yang terbit di log.
 *
 * ---------------------------------------------------------------------------
 * Kenapa `artikel:kecilkan-sampul` TIDAK bisa membereskannya
 * ---------------------------------------------------------------------------
 *
 * Perintah itu membuka tiap berkas untuk mengukurnya, jadi barisnya diawali
 * `if (! is_file($jalur)) continue;` — baris yang berkasnya hilang justru
 * DILEWATI. Menjalankannya di produksi memperbaiki nol dari tujuh belas.
 *
 * Perintah ini mengerjakan yang sebaliknya: ia hanya menyentuh baris yang
 * berkasnya TIDAK ada, dan hanya kalau ada berkas lain bernama dasar sama
 * yang bisa dipakai. Tidak ada berkas yang dibuat, diubah, atau dihapus di
 * sini — yang berubah cuma nama yang tersimpan di baris.
 *
 * Aman diulang: sekali barisnya menunjuk berkas yang ada, ia tidak masuk
 * hitungan lagi.
 */
class PerbaikiSampulHilang extends Command
{
    protected $signature = 'artikel:perbaiki-sampul-hilang
                            {--kering : Hanya melaporkan, tidak mengubah apa pun}';

    protected $description = 'Menunjuk ulang sampul artikel yang berkasnya hilang ke berkas yang ada';

    private const FOLDER = 'images';

    /**
     * Urutan pilihan pengganti.
     *
     * .webp lebih dulu karena itulah yang dihasilkan kecilkan-sampul, dan
     * karena itu pula bentuk yang hampir pasti ada. Sisanya untuk perkara
     * lain yang bentuknya sama — berkas berganti akhiran sementara barisnya
     * tertinggal.
     */
    private const AKHIRAN = ['webp', 'jpg', 'jpeg', 'png'];

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        $diperiksa = 0;
        $hilang = 0;
        $diperbaiki = 0;
        $menggantung = [];

        foreach (Artikel::all() as $artikel) {
            foreach (['gambar_depan', 'gambar_cover'] as $kolom) {
                $nilai = trim((string) $artikel->{$kolom});

                if ($nilai === '') {
                    continue;
                }

                $diperiksa++;

                /*
                 * basename(): sebagian baris lama menyimpan jalur lengkap,
                 * dan public_path('images/'.$jalur) meleset diam-diam kalau
                 * nilainya sudah memuat foldernya sendiri. Disalin dari
                 * kecilkan-sampul dengan sengaja — dua perintah yang membaca
                 * kolom yang sama harus membacanya dengan cara yang sama.
                 */
                $nama = basename($nilai);

                if (is_file(public_path(self::FOLDER . '/' . $nama))) {
                    continue;
                }

                $hilang++;
                $dasar = preg_replace('/\.[^.\/]+$/', '', $nama);
                $pengganti = null;

                foreach (self::AKHIRAN as $akhiran) {
                    $calon = $dasar . '.' . $akhiran;

                    if ($calon !== $nama && is_file(public_path(self::FOLDER . '/' . $calon))) {
                        $pengganti = $calon;
                        break;
                    }
                }

                if ($pengganti === null) {
                    // DILAPORKAN, bukan didiamkan: baris yang tidak punya
                    // pengganti berarti gambarnya memang hilang, dan itu
                    // perkara lain yang perlu diketahui orangnya.
                    $menggantung[] = sprintf('artikel %d %s: %s', $artikel->getKey(), $kolom, $nama);

                    continue;
                }

                $this->line(sprintf('  artikel %-4d %-13s %s', $artikel->getKey(), $kolom, $nama));
                $this->info(sprintf('      -> %s', $pengganti));

                if (! $kering) {
                    /*
                     * `timestamps = false`, BUKAN cuma saveQuietly().
                     *
                     * saveQuietly() menahan PERISTIWA, bukan stempel waktu —
                     * updated_at tetap bergerak. Terukur begitu oleh ujinya
                     * sendiri: 2026-05-09 jadi 2026-11-09.
                     *
                     * Ini pembetulan tata data, bukan penyuntingan tulisan.
                     * Kalau updated_at ikut bergerak, delapan tulisan lama
                     * terbaca "baru diperbarui hari ini" — termasuk di
                     * <lastmod> peta situs, yang memberi tahu mesin pencari
                     * bahwa isinya berubah padahal yang berubah cuma nama
                     * berkas sampulnya.
                     */
                    $artikel->timestamps = false;
                    $artikel->forceFill([$kolom => $pengganti])->saveQuietly();
                    $artikel->timestamps = true;
                }

                $diperbaiki++;
            }
        }

        $this->newLine();
        $this->line('kolom gambar terisi : ' . $diperiksa);
        $this->line('berkasnya hilang    : ' . $hilang);
        $this->line(($kering ? 'bisa diperbaiki     : ' : 'diperbaiki          : ') . $diperbaiki);

        if ($menggantung !== []) {
            $this->newLine();
            $this->warn('TANPA PENGGANTI — gambarnya memang tidak ada lagi:');

            foreach ($menggantung as $baris) {
                $this->warn('  ' . $baris);
            }
        }

        return self::SUCCESS;
    }
}
