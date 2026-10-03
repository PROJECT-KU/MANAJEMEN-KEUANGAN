<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tempat menyimpan varian pada pendaftaran Scopus Kafe.
 *
 * Empat layanan lain mendapat variannya dari ANGKATAN yang dipilih
 * (`kategori_layanan.varian`). Scopus Kafe tidak berangkatan — terukur nol
 * baris angkatan — jadi variannya tidak punya tempat sama sekali, dan varian
 * Online/Offline yang disetel di layar Layanan tidak bisa dipilih maupun
 * disimpan dari borang pendaftaran.
 *
 * Varchar, bukan enum, dan sengaja TANPA foreign key: daftar variannya hidup
 * di kolom JSON `layanan.varian` yang disunting orang lewat aplikasi, bukan
 * di tabel tersendiri.
 */
return new class extends Migration
{
    private const TABEL = 'pendaftaran_scopus_kafe';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABEL) || Schema::hasColumn(self::TABEL, 'varian')) {
            return;
        }

        Schema::table(self::TABEL, function (Blueprint $t) {
            /*
             * Nullable tanpa nilai bawaan, dan itu disengaja: 9 baris yang
             * sudah ada dibuat sebelum layanannya punya varian, jadi menebak
             * salah satunya berarti mengarang keterangan yang tidak pernah
             * dipilih siapa pun. Tanpa after(): nama kolom di tabel ini
             * `nama`, bukan `nama_pemesan` seperti tabel Clinik.
             */
            $t->string('varian', 40)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABEL) || ! Schema::hasColumn(self::TABEL, 'varian')) {
            return;
        }

        Schema::table(self::TABEL, function (Blueprint $t) {
            $t->dropIndex(['varian']);
            $t->dropColumn('varian');
        });
    }
};
