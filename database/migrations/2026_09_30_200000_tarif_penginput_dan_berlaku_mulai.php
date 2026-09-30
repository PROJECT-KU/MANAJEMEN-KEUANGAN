<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak penyetel tarif, dan tanggal mulai berlakunya.
 *
 * `penginput_id` — menyetel tarif itu keputusan uang, dan sampai sekarang
 * tidak ada apa pun yang mencatat siapa yang melakukannya. Kalau harga
 * tiba-tiba berbeda, tidak ada yang bisa ditanya.
 *
 * `berlaku_mulai` — kenaikan harga selalu diketahui jauh hari, tetapi harus
 * disetel manual tepat di hari H. Dengan kolom ini tarifnya disiapkan lebih
 * awal dan naik sendiri pada tanggalnya.
 *
 * Keduanya boleh NULL: baris yang sudah ada memang tidak punya jejaknya, dan
 * tarif tanpa tanggal berarti "berlaku sejak disetel" seperti selama ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            /*
             * Tanpa kunci asing ke users: penyetelnya bisa berhenti bekerja
             * dan akunnya dihapus, sementara jejak "siapa yang menaikkan harga
             * ini" justru paling dibutuhkan setelah orangnya tidak ada.
             */
            $t->unsignedBigInteger('penginput_id')->nullable()->after('status');
            $t->date('berlaku_mulai')->nullable()->after('penginput_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            $t->dropIndex(['berlaku_mulai']);
            $t->dropColumn(['penginput_id', 'berlaku_mulai']);
        });
    }
};
