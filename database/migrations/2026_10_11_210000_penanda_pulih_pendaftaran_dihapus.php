<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda bahwa satu catatan penghapusan sudah DIKEMBALIKAN.
 *
 * Barisnya tidak dihapus saat dipulihkan melainkan ditandai. Catatan
 * penghapusan gunanya menjawab "ke mana perginya pendaftaran itu" — dan
 * baris yang lenyap saat dipulihkan membuat pertanyaan itu tidak terjawab
 * untuk kedua kalinya.
 *
 * Jalur berkas buktinya ikut disimpan. Buktinya tidak lagi dibuang dari
 * cakram melainkan dipindahkan ke folder 'terhapus/', dan tanpa catatan
 * jalurnya tidak ada yang tahu berkas mana yang harus dikembalikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->timestamp('dipulihkan_at')->nullable()->after('oleh_nama');
            $t->string('dipulihkan_oleh', 120)->nullable()->after('dipulihkan_at');
            $t->string('bukti_parkir', 255)->nullable()->after('dipulihkan_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->dropColumn(['dipulihkan_at', 'dipulihkan_oleh', 'bukti_parkir']);
        });
    }
};
