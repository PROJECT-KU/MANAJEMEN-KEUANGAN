<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan KAPAN pendaftar menyetujui dipakainya datanya.
 *
 * Borangnya sekarang punya kotak centang persetujuan. Yang dicentang lalu
 * dilupakan tidak ada gunanya saat ditanya belakangan — yang bisa dijawab
 * adalah waktunya, jadi waktunya yang disimpan, bukan sekadar true/false.
 *
 * Boleh kosong: pendaftaran yang sudah ada sebelum kolom ini memang tidak
 * pernah ditanyai, dan mengarangkan waktunya justru menjadikan catatan ini
 * tidak bisa dipercaya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            $tabel->timestamp('disetujui_pada')->nullable()->after('affiliasi');
        });
    }

    public function down(): void
    {
        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            $tabel->dropColumn('disetujui_pada');
        });
    }
};
