<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PIN masuk: jalan pintas enam angka yang HANYA aktif bila pemilik akun
 * menyalakannya sendiri dari halaman profil. Nilainya disimpan teracak
 * (hash) sama seperti kata sandi, jadi tidak bisa dibaca kembali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pin')) {
                $table->string('pin', 255)->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'pin_aktif')) {
                $table->boolean('pin_aktif')->default(false)->after('pin');
            }

            if (! Schema::hasColumn('users', 'pin_diubah_pada')) {
                $table->timestamp('pin_diubah_pada')->nullable()->after('pin_aktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['pin_diubah_pada', 'pin_aktif', 'pin'] as $kolom) {
                if (Schema::hasColumn('users', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
