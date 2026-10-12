<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alasan penghapusan.
 *
 * Catatan penghapusan sudah menjawab KE MANA perginya satu pendaftaran —
 * potretnya, siapa yang menghapus, kapan. Yang tidak pernah terjawab
 * KENAPA, dan itu pertanyaan kedua yang selalu menyusul: salah input,
 * pendaftaran ganda, permintaan pendaftarnya sendiri, atau uji coba.
 *
 * Nullable: ratusan penghapusan yang sudah tercatat tidak punya alasan, dan
 * memaksanya berarti migrasi ini mengarang jawaban untuk masa lalu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->string('alasan', 255)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->dropColumn('alasan');
        });
    }
};
