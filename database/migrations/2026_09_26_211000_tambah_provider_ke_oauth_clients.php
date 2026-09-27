<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passport 9+ menambahkan kolom 'provider' pada oauth_clients. Basis data ini
 * dibuat pada masa Passport 8, jadi kolomnya belum ada dan pembuatan client
 * baru gagal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('oauth_clients') || Schema::hasColumn('oauth_clients', 'provider')) {
            return;
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('secret');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('oauth_clients') && Schema::hasColumn('oauth_clients', 'provider')) {
            Schema::table('oauth_clients', function (Blueprint $table) {
                $table->dropColumn('provider');
            });
        }
    }
};
