<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mencatat kapan email pengingat dikirim.
 *
 * Perintah pengingat jalan tiap hari dan bisa dijalankan ulang tangan. Tanpa
 * catatan ini, tiap jalan mengirim surat lagi kepada orang yang sama — dan
 * peserta yang menerima lima pengingat untuk satu sesi berhenti membaca
 * surat dari kami sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            $tabel->timestamp('pengingat_pada')->nullable()->after('disetujui_pada');
        });
    }

    public function down(): void
    {
        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            $tabel->dropColumn('pengingat_pada');
        });
    }
};
