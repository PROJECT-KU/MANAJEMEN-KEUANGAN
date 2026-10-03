<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diskon rombongan, disetel di Tarif Layanan.
 *
 * Sebelum ini satu-satunya cara memberi harga rombongan adalah "potongan
 * khusus" berupa nominal rupiah — panitia menghitung sendiri diskon 30
 * orangnya lalu mengetik hasilnya, dan besarannya bergantung ingatan orang.
 *
 * PERSEN, bukan rupiah, dengan alasan yang sama seperti diskon alumni:
 * tarifnya satu per layanan sedangkan harga angkatannya berbeda-beda.
 *
 * Dua kolom, sebab diskonnya bersyarat: berlaku mulai berapa orang, dan
 * berapa persen. Tanpa ambangnya, "diskon rombongan" berlaku juga untuk satu
 * orang.
 */
return new class extends Migration
{
    private const TABEL = 'clinikscopus_biaya_persesi';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABEL) || Schema::hasColumn(self::TABEL, 'diskon_rombongan_persen')) {
            return;
        }

        Schema::table(self::TABEL, function (Blueprint $t) {
            $t->unsignedSmallInteger('diskon_rombongan_min')->nullable()->after('diskon_alumni_persen');
            $t->unsignedTinyInteger('diskon_rombongan_persen')->nullable()->after('diskon_rombongan_min');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABEL) || ! Schema::hasColumn(self::TABEL, 'diskon_rombongan_persen')) {
            return;
        }

        Schema::table(self::TABEL, function (Blueprint $t) {
            $t->dropColumn(['diskon_rombongan_min', 'diskon_rombongan_persen']);
        });
    }
};
