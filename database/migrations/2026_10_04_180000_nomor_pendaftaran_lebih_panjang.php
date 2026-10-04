<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Melebarkan kolom nomor pendaftaran Scopus Kafe.
 *
 * Nomornya berganti dari lima aksara acak ("HUQEZ") jadi berawalan dan
 * bertanggal ("KAFE-20261004-0001") supaya bisa dikenali tanpa menghafal.
 * Kolomnya varchar(10) — terukur, dan satu-satunya dari kelima tabel yang
 * terlalu sempit; empat lainnya sudah varchar(255).
 *
 * Tanpa migrasi ini MySQL dalam mode longgar akan MEMOTONG nomornya jadi
 * "KAFE-20261", diam-diam, dan sepuluh pendaftaran di hari yang sama
 * tersimpan dengan nomor yang sama persis.
 *
 * Baris lama TIDAK diubah. Nomor yang sudah beredar ada di email pendaftar,
 * bukti transfer, dan percakapan WhatsApp; menulis ulang semuanya membuat
 * nomor yang dipegang orang tidak cocok lagi dengan yang ada di sistem.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pendaftaran_scopus_kafe')) {
            return;
        }

        /*
         * SQL mentah, bukan Blueprint->change(): change() menuntut paket
         * doctrine/dbal pada sebagian versi, dan menambah kebergantungan untuk
         * satu ALTER adalah harga yang tidak sepadan.
         */
        DB::statement('ALTER TABLE pendaftaran_scopus_kafe MODIFY id_pemesanan VARCHAR(30) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('pendaftaran_scopus_kafe')) {
            return;
        }

        DB::statement('ALTER TABLE pendaftaran_scopus_kafe MODIFY id_pemesanan VARCHAR(10) NULL');
    }
};
