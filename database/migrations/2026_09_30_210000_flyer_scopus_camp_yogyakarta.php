<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memasang flyer resmi sebagai sampul seluruh angkatan Scopus Camp Yogyakarta.
 *
 * Berkasnya `public/ScopusCamp/camp-jogja.jpeg`, mengikuti kebiasaan yang
 * sudah ada: halaman publik membaca sampul dari folder itu lewat basename(),
 * dan foldernya ikut git sehingga berkasnya ikut terdeploy.
 *
 * Dikerjakan lewat migrasi, bukan sekali jalan di lokal, supaya hasilnya sama
 * di peladen tanpa ada yang harus mengingat langkah tambahan.
 *
 * Sampul lamanya TIDAK dihapus dari cakram: beberapa angkatan berbagi berkas
 * yang sama, dan menghapusnya akan mengosongkan gambar angkatan lain yang
 * tidak ikut diubah di sini.
 */
return new class extends Migration
{
    private const FLYER = 'ScopusCamp/camp-jogja.jpeg';

    public function up(): void
    {
        if (! is_file(public_path(self::FLYER))) {
            // Berkasnya tidak ada — lebih baik tidak mengubah apa pun daripada
            // menunjuk gambar yang hilang dan membuat halaman publik kosong.
            return;
        }

        DB::table('kategori_layanan')
            ->where('layanan', 'scopus_camp')
            ->where('lokasi', 'Yogyakarta')
            ->update(['gambar' => self::FLYER]);
    }

    public function down(): void
    {
        /*
         * Tidak dikembalikan. Sampul lama tiap angkatan berbeda-beda dan tidak
         * disimpan di mana pun sebelum diganti, jadi menebaknya saat mundur
         * justru memasang gambar yang salah.
         */
    }
};
