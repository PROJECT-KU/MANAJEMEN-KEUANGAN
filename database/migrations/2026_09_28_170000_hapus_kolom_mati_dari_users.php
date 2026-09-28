<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buang tiga kolom users yang tidak pernah terisi satu baris pun.
 *
 *  avatar   0 dari 146 baris, dan tidak dirujuk kode mana pun.
 *  title    0 dari 146, hanya pernah DITULIS oleh layar pengelolaan pengguna
 *           dari isian yang tidak ada, lalu tidak pernah dibaca.
 *  tenggat  0 dari 146. Sumber "tenggat sewa" untuk fitur Sewa, yang seluruh
 *           rutenya dikomentari dan tampilannya tidak ada — jadi tidak pernah
 *           bisa diisi. Layout ikut menghitung $isTenggatExpired enam kali di
 *           tiap halaman back office dari kolom ini, dan hasilnya tidak pernah
 *           dibaca sekali pun.
 *
 * Tidak ada cadangan data karena memang tidak ada data: ketiganya kosong di
 * seluruh tabel. Bandingkan dengan users.nik, yang 28 barisnya dicadangkan
 * lebih dulu ke database/backup/users-nik-2026-09-28.sql.
 */
return new class extends Migration
{
    private const KOLOM = ['avatar', 'title', 'tenggat'];

    public function up(): void
    {
        $ada = array_values(array_filter(
            self::KOLOM,
            fn ($k) => Schema::hasColumn('users', $k)
        ));

        if ($ada === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($ada) {
            $table->dropColumn($ada);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }

            if (! Schema::hasColumn('users', 'title')) {
                $table->string('title')->nullable();
            }

            if (! Schema::hasColumn('users', 'tenggat')) {
                $table->string('tenggat')->nullable();
            }
        });
    }
};
