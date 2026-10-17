<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat halaman bayar di gerbang pembayaran.
 *
 * Alamat ini lahir sekali saat tagihannya dibuat, lalu hanya dipakai untuk
 * mengalihkan pembayarnya ke sana — dan tidak pernah disimpan. Akibatnya
 * siapa pun yang menutup tab itu, atau membuka tautan di emailnya nanti,
 * mendarat di halaman status yang menghitung mundur TANPA satu pun tombol
 * untuk membayar. Kursinya dilepas saat waktunya habis, dan ia tidak pernah
 * punya jalan menyelesaikannya.
 *
 * Boleh kosong: pendaftaran lama tidak punya, dan yang transfer manual
 * memang tidak pernah punya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('webinar_eksklusif_pendaftaran', 'bayar_url')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            // 512: alamat checkout membawa penanda sesi yang panjang.
            $t->string('bayar_url', 512)->nullable()->after('bayar_rujukan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('webinar_eksklusif_pendaftaran', 'bayar_url')) {
            return;
        }

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $t) {
            $t->dropColumn('bayar_url');
        });
    }
};
