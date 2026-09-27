<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda perangkat pada jejak masuk. Dipakai untuk mengenali "perangkat
 * baru" sehingga pemilik akun bisa diberi tahu lewat email saat akunnya
 * dibuka dari peramban yang belum pernah dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_masuk', function (Blueprint $table) {
            if (! Schema::hasColumn('aktivitas_masuk', 'perangkat')) {
                $table->string('perangkat', 64)->nullable()->after('peramban')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_masuk', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas_masuk', 'perangkat')) {
                $table->dropColumn('perangkat');
            }
        });
    }
};
