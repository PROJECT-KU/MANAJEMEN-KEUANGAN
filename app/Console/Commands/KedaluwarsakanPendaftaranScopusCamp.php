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
 * Suratnya tidak dikirim. Perpindahan statusnya dikerjakan dengan `update()`
 * borongan — sengaja, supaya ratusan baris tidak jadi ratusan kueri — dan
 * jalur itu tidak melewati UbahStatusPendaftaran, satu-satunya tempat yang
 * memilih kelas suratnya dari katalog lalu mencatat hasil kirimnya di jejak.
 *
 * Yang DIKIRIM pemberitahuan WhatsApp-nya, lewat KabariWhatsApp. Itu bukan
 * tukar-menukar: tanpa satu pun pemberitahuan, orangnya tahu kursinya hilang
 * hanya kalau ia kebetulan membuka halaman statusnya lagi. Suratnya menyusul
 * begitu layanan ini punya surat kedaluwarsa sendiri — Webinar Eksklusif
 * sudah punya, Scopus Camp belum.
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
        /*
         * DIBATASI per jalankan, sama seperti sapuan Webinar Eksklusif.
         *
         * Yang membuat nomor WhatsApp diblokir bukan banyaknya pesan dalam
         * sehari melainkan LEDAKAN: puluhan pesan beruntun dalam hitungan
         * detik, ke nomor berbeda, berbunyi hampir sama. Perintah ini satu-
         * satunya tempat di layanan ini yang bisa meledak — ia memindahkan
         * puluhan pendaftaran sekaligus.
         *
         * Batasnya dipasang pada BARIS YANG DIPROSES, bukan pada pesan yang
         * dikirim. Dipasang pada pesannya, kursinya tetap dilepas sementara
         * pemberitahuannya tidak — dan barisnya sudah keluar dari saringan
         * ini, jadi tidak ada jalankan berikutnya yang akan melihatnya lagi.
         *
         * Yang tertinggal diambil jalankan berikutnya, sepuluh menit
         * kemudian. Yang paling lama menunggu didahulukan.
         */
        $batas = max(0, (int) config('services.fonnte.batas_sapuan', 40));

        $kueri = PendaftaranScopusCamp::query()
            ->where('cara_bayar', 'doku')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where('status', \App\Support\PendaftaranSemuaLayanan::statusAwal('scopus_camp'))
            ->whereNull('bayar_pada')
            ->orderBy('kedaluwarsa_pada')

            // Kunci kedua: 'kedaluwarsa_pada' seri untuk pendaftaran yang
            // dibuat dalam detik yang sama, dan urutan tanpa pemecah seri
            // tidak ditentukan — barisnya bisa berpindah antar jalankan,
            // dan yang tertinggal tidak selalu yang sama.
            ->orderBy('id');

        if ($batas > 0) {
            $kueri->limit($batas);
        }

        $lewat = $kueri->get();

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
             * Dilepas lewat RAKITAN YANG SAMA dengan jalur web.
             *
             * Dulu di sini ada `update()` borongan tersendiri. Dua rakitan
             * untuk satu pekerjaan berarti keduanya akan berbeda perlahan —
             * dan yang satu sudah berbeda: jalur web mengunci barisnya
             * sebelum memindahkan, yang ini tidak. Dua proses yang menyapu
             * bersamaan karena itu bisa sama-sama memindahkan baris yang
             * sama, lalu mengabari pendaftarnya dua kali.
             */
            $berpindah = PendaftaranScopusCamp::lepaskanYangKedaluwarsa(
                [], null, max(1, $lewat->count())
            );

            $this->line('  ' . $berpindah . ' pendaftaran benar-benar berpindah.');

            // Hanya yang BENAR-BENAR berpindah yang dikabari; lihat alasannya
            // di kabari().
            $this->kabari(PendaftaranScopusCamp::whereKey($lewat->modelKeys())
                ->where('bayar_status', 'expired')
                ->get());
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

    /**
     * Mengabari lewat WhatsApp bahwa kursinya dilepas.
     *
     * DI SINI, bukan lewat UbahStatusPendaftaran: perintah ini memindahkan
     * status dengan satu `update()` borongan — sengaja, supaya ratusan baris
     * tidak jadi ratusan kueri — dan jalur borongan itu tidak melewati aksi
     * mana pun. Pesannya juga satu-satunya yang sebabnya BENAR di sini:
     * saringannya sendiri yang memastikan tenggatnya memang sudah lewat dan
     * pembayarannya memang belum pernah masuk. Dipanggil dari layar panitia,
     * pesan yang sama akan menuduh orang yang dibatalkan karena alasan lain.
     *
     * SESUDAH status tersimpan, bukan sebelum: pesan yang menyatakan kursinya
     * dilepas sementara barisnya masih berstatus awal berarti orangnya
     * membuka halaman statusnya dan melihat tombol bayar yang masih hidup.
     *
     * Dibungkus per orang. Satu nomor yang tidak bisa dihubungi tidak boleh
     * menghentikan sapuannya — sisanya ikut tidak dikabari, dan kuotanya
     * tetap terlepas, jadi kegagalannya tidak akan pernah muncul lagi di
     * jalankan berikutnya.
     *
     * @param  \Illuminate\Support\Collection<int, PendaftaranScopusCamp>  $lewat
     */
    private function kabari($lewat): void
    {
        $wa = app(\App\Actions\Pendaftaran\KabariWhatsApp::class);

        /*
         * SURAT ikut dikirim, tidak lagi WhatsApp saja.
         *
         * Sampai 26 Okt 2026 kursi yang dilepas hanya dikabari lewat
         * WhatsApp — dan perangkat Fonnte-nya sedang putus, jadi tidak
         * dikabari ke mana pun. Orangnya baru tahu kursinya hilang kalau ia
         * kebetulan membuka halaman statusnya lagi.
         *
         * Webinar Eksklusif sudah punya suratnya sejak lama; layanan ini
         * tidak. Dua saluran, sebab yang satu gagal tidak boleh membuat
         * orangnya tidak tahu sama sekali.
         */

        /*
         * JEDA ANTAR PESAN. Amannya karena pemanggilnya perintah terjadwal —
         * tidak ada orang yang menunggu layarnya. Di jalur web, menidurkan
         * permintaan berarti halamannya menggantung.
         */
        $jeda = max(0, (int) config('services.fonnte.jeda_sapuan_detik', 25));
        $pertama = true;

        foreach ($lewat as $p) {
            if (! $pertama && $jeda > 0) {
                sleep($jeda);
            }

            $pertama = false;

            try {
                $kategori = \App\CategoriesScopusCamp::find($p->kategori_id);

                if ($kategori !== null && filter_var($p->email, FILTER_VALIDATE_EMAIL)) {
                    \Illuminate\Support\Facades\Mail::to($p->email)->send(
                        new \App\Mail\ScopusCampKedaluwarsaMail($p, $kategori)
                    );
                }
            } catch (\Throwable $e) {
                $this->warn('  Surat ' . $p->id_transaksi . ' gagal: ' . $e->getMessage());

                \Illuminate\Support\Facades\Log::warning('Surat kedaluwarsa Scopus Camp gagal.', [
                    'nomor' => $p->id_transaksi,
                    'pesan' => $e->getMessage(),
                ]);
            }

            try {
                $wa->kursiDilepas('scopus_camp', $p->refresh());
            } catch (\Throwable $e) {
                $this->warn('  WhatsApp ' . $p->id_transaksi . ' gagal: ' . $e->getMessage());

                \Illuminate\Support\Facades\Log::warning('Kabar WhatsApp kursi Scopus Camp dilepas gagal.', [
                    'nomor' => $p->id_transaksi,
                    'pesan' => $e->getMessage(),
                ]);
            }
        }
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
