<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kanal pembayaran yang BENAR-BENAR dipakai lewat gerbang pembayaran.
 *
 * `cara_bayar` hanya membedakan transfer manual dari gerbang — lewat gerbang
 * ia selalu berbunyi "DOKU", padahal orangnya bisa membayar dengan VA
 * Mandiri, QRIS, OVO, atau kartu. Panitia yang mencocokkan uang masuk tidak
 * punya cara tahu yang mana, dan itu justru pertanyaan pertama saat satu
 * pembayaran perlu ditelusuri.
 *
 * Boleh kosong: pendaftaran lama tidak punya nilainya, dan transfer manual
 * memang tidak pernah punya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('webinar_eksklusif_pendaftaran', 'bayar_kanal')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            $t->string('bayar_kanal', 80)->nullable()->after('bayar_status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('webinar_eksklusif_pendaftaran', 'bayar_kanal')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            $t->dropColumn('bayar_kanal');
        });
    }
};
