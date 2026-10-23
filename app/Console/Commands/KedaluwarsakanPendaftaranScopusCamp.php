<?php

namespace App\Console\Commands;

use App\CategoriesScopusCamp;
use App\Http\Controllers\Publict\PublicScopusCampController;
use App\PendaftaranScopusCamp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Melepas kembali kursi Scopus Camp yang dipesan tetapi tidak jadi dibayar,
 * dan membetulkan `sisa_kuota` yang terlanjur salah hitung.
 *
 * ---------------------------------------------------------------------------
 * Kenapa perlu
 * ---------------------------------------------------------------------------
 *
 * Kuota dipotong saat orang menekan "Lanjut ke pembayaran", bukan saat ia
 * membayar — kalau tidak, kursi yang sedang dibayar masih terlihat kosong dan
 * bisa diambil orang lain, lalu yang terlanjur membayar tidak punya tempat.
 *
 * Akibatnya pendaftaran yang ditinggalkan di tengah jalan menahan kursinya
 * SELAMANYA. `kedaluwarsa_pada` sudah ditulis sejak jalur DOKU dipasang, tapi
 * sampai 23 Okt 2026 tidak ada satu pun yang membacanya selain tombol bayar di
 * halaman status — Webinar Eksklusif punya sapuannya sejak lama, Scopus Camp
 * tidak:
 *
 *     * / 10 * * * *  webinar-eksklusif:kedaluwarsakan   <- ada
 *                     scopus-camp:kedaluwarsakan          <- tidak ada
 *
 * ---------------------------------------------------------------------------
 * Dua pekerjaan, bukan satu
 * ---------------------------------------------------------------------------
 *
 * Selain melepas yang kedaluwarsa, perintah ini MENGHITUNG ULANG `sisa_kuota`
 * seluruh angkatan. Rumus lamanya ikut menghitung kursi pendaftaran yang sudah
 * dibatalkan, ditolak, dan direfund — terukur 23 Okt 2026: 23 kursi tertahan
 * di 5 angkatan, dan Scopus Camp Yogyakarta #199 terbaca "tinggal 4 kursi"
 * padahal 15 dari 16 yang terpakai milik pendaftaran yang sudah batal.
 *
 * Membetulkannya hanya di jalur pendaftaran baru tidak cukup: angkatan yang
 * sudah telanjur salah tidak akan pernah menyentuh jalur itu lagi kalau
 * kursinya sudah terbaca habis.
 *
 * ---------------------------------------------------------------------------
 * Yang TIDAK dilakukan perintah ini
 * ---------------------------------------------------------------------------
 *
 * Statusnya tidak diturunkan jadi 'Pendaftaran Dibatalkan'. Di layanan ini
 * pembatalan adalah KEPUTUSAN PANITIA — ia menyeret surat pembatalan ke
 * pendaftarnya — sedangkan tagihan yang mati cuma berarti tagihannya mati.
 * Yang dicatat di `bayar_status`, dan kursinya dikembalikan lewat hitung
 * ulang di bawah.
 */
class KedaluwarsakanPendaftaranScopusCamp extends Command
{
    protected $signature = 'scopus-camp:kedaluwarsakan {--kering : Hanya melaporkan, tidak mengubah apa pun}';

    protected $description = 'Melepas kursi Scopus Camp dari pendaftaran yang batas bayarnya sudah lewat';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');

        /*
         * Yang disapu HANYA yang lewat gerbang DAN tenggatnya sudah lewat DAN
         * masih berstatus awal. Transfer manual tidak punya tenggat di
         * layanan ini, dan yang sudah diterima panitia jelas tidak boleh
         * disentuh.
         */
        $lewat = PendaftaranScopusCamp::query()
            ->where('cara_bayar', 'doku')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where('status', \App\Support\PendaftaranSemuaLayanan::statusAwal('scopus_camp'))
            ->whereNull('bayar_pada')
            ->get();

        foreach ($lewat as $p) {
            $this->line(sprintf(
                '  %s — %s, %d orang, batas %s',
                $p->id_transaksi,
                $p->nama ?: '(tanpa nama)',
                (int) $p->jumlah_pendaftar,
                $p->kedaluwarsa_pada->format('d M Y H:i')
            ));
        }

        if (! $kering && $lewat->isNotEmpty()) {
            /*
             * Ditandai dulu, baru kuotanya dihitung ulang. Urutannya
             * MENGIKAT: hitung ulang membaca status, jadi menjalankannya
             * lebih dulu akan tetap menghitung kursi yang baru saja dilepas.
             */
            PendaftaranScopusCamp::whereKey($lewat->modelKeys())->update([
                'status' => 'Pendaftaran Dibatalkan',
                'bayar_status' => 'expired',
                'bayar_url' => null,
            ]);
        }

        $this->info($lewat->count() . ' pendaftaran kedaluwarsa, '
            . $lewat->sum('jumlah_pendaftar') . ' kursi.');

        $berubah = $this->hitungUlangKuota($kering);

        $this->info($berubah . ' angkatan dibetulkan sisa kuotanya.');

        if ($kering) {
            $this->comment('Mode kering: tidak ada yang diubah.');
        }

        return self::SUCCESS;
    }

    /** Menyetel ulang `sisa_kuota` tiap angkatan dari pendaftaran yang hidup. */
    private function hitungUlangKuota(bool $kering): int
    {
        $berubah = 0;

        CategoriesScopusCamp::where('layanan', 'scopus_camp')
            ->whereNotNull('total_kuota')
            ->chunkById(100, function ($angkatan) use (&$berubah, $kering) {
                foreach ($angkatan as $a) {
                    $benar = PublicScopusCampController::hitungUlangSisaKursi($a);

                    if ($benar === null || (int) $a->sisa_kuota === $benar) {
                        continue;
                    }

                    $this->line(sprintf(
                        '  %s #%s — sisa %s -> %d',
                        $a->nama, $a->nama_ke, $a->sisa_kuota, $benar
                    ));

                    $berubah++;

                    if (! $kering) {
                        /*
                         * Saringan `layanan` ikut disebut walau kuncinya
                         * sudah primer.
                         *
                         * Tabel kategori_layanan dipakai BERSAMA kelima
                         * layanan, dan kueri tanpa saringan itu adalah cara
                         * paling mudah menyentuh baris milik layanan lain
                         * saat kuncinya suatu saat salah. Dijaga uji
                         * KategoriLayananTest.
                         */
                        DB::table('kategori_layanan')
                            ->where('layanan', 'scopus_camp')
                            ->where('id', $a->getKey())
                            ->update(['sisa_kuota' => (string) $benar]);
                    }
                }
            });

        return $berubah;
    }
}
