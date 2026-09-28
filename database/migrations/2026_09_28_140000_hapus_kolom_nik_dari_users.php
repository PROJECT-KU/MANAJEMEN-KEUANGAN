<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buang kolom users.nik.
 *
 * Kolomnya sudah ada sejak migrasi pertama tetapi tidak pernah benar-benar
 * dipakai: isian NIK di halaman detail pengguna tidak punya penangan
 * penyimpanan sama sekali, dan tidak satu pun tampilan atau ekspor
 * menampilkannya. Yang ada hanya belasan select() yang ikut mengambilnya
 * lalu membuangnya lagi.
 *
 * 28 dari 147 baris sempat terisi — nilainya dicadangkan lebih dulu ke
 * database/backup/users-nik-2026-09-28.sql, lengkap dengan perintah UPDATE
 * untuk memulihkannya kalau ternyata masih dibutuhkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'nik')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nik');
        });
    }

    /**
     * Kolomnya dikembalikan kosong. Isinya TIDAK ikut pulih dari sini —
     * jalankan database/backup/users-nik-2026-09-28.sql untuk itu.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'nik')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 100)->nullable()->after('title');
        });
    }
};
