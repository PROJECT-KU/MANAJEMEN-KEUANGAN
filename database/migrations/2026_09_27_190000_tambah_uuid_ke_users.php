<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Alamat halaman profil memakai id berurutan, jadi siapa pun yang sudah masuk
 * bisa menebak /profil/1/show sampai /profil/999/show dan tahu persis berapa
 * banyak akun yang ada serta kapan tiap akun dibuat. UUID menutup tebakan itu.
 *
 * Kolomnya ditambah, diisi untuk akun yang sudah ada, baru dikunci unik —
 * urutannya penting, karena kunci unik pada kolom yang masih NULL semua bisa
 * langsung bentrok.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'uuid')) {
            Schema::table('users', function (Blueprint $tabel) {
                $tabel->char('uuid', 36)->nullable()->after('id');
            });
        }

        DB::table('users')->whereNull('uuid')->orderBy('id')->chunkById(200, function ($akun) {
            foreach ($akun as $satu) {
                DB::table('users')->where('id', $satu->id)->update(['uuid' => (string) Str::uuid()]);
            }
        });

        $sudahAda = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_uuid_unique'"))->isNotEmpty();

        if (! $sudahAda) {
            Schema::table('users', function (Blueprint $tabel) {
                $tabel->unique('uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $tabel) {
            $tabel->dropUnique('users_uuid_unique');
            $tabel->dropColumn('uuid');
        });
    }
};
