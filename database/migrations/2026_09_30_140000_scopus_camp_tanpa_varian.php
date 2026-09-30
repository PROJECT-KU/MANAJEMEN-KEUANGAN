<?php

use App\ClinikScopusBiayaPersesi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Membuang varian Pulau Jawa / Luar Pulau Jawa dari tarif Scopus Camp.
 *
 * Varian itu dipasang dengan anggapan harganya ditentukan pulau. Datanya
 * membantah: Yogyakarta berharga 4.500.000 dari Januari sampai September 2026,
 * lalu 5.500.000 sejak 2 Oktober — kota yang sama, dua harga. Yang membedakan
 * ternyata WAKTU, bukan pulau, dan riwayat tarif memang sudah menanganinya.
 *
 * Lagi pula tiap angkatan sudah menyimpan `lokasi` dan `biaya`-nya sendiri.
 * Varian di tarif induk cuma menduplikasi dimensi yang hidup di tempat lain,
 * dan dua salinan dari satu kenyataan pasti berselisih cepat atau lambat.
 *
 * Yang tersisa satu tarif patokan. Angkatan tetap boleh memakai harganya
 * sendiri seperti sekarang.
 *
 * Varian Bibliometrik online/offline TIDAK ikut dibuang — itu beda produk,
 * bukan beda angkatan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $baris = DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', 'scopus_camp')
                ->orderByDesc('updated_at')
                ->get();

            if ($baris->isEmpty()) {
                return;
            }

            /*
             * Yang paling belakangan disetel jadi tarif berlakunya — itu harga
             * yang sedang dipakai. Sisanya turun jadi riwayat, bukan dihapus:
             * angkatan yang sudah berjalan memakai harga saat itu.
             */
            $berlaku = $baris->first();

            DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', 'scopus_camp')
                ->update([
                    'varian' => null,
                    'status' => ClinikScopusBiayaPersesi::NONAKTIF,
                ]);

            DB::table('clinikscopus_biaya_persesi')
                ->where('id', $berlaku->id)
                ->update(['status' => ClinikScopusBiayaPersesi::AKTIF]);
        });
    }

    public function down(): void
    {
        /*
         * Tidak dikembalikan. Pemetaan kota ke pulau tidak tersimpan di baris
         * tarifnya, jadi menebaknya saat mundur justru menghasilkan varian yang
         * belum tentu sama dengan sebelumnya.
         */
    }
};
