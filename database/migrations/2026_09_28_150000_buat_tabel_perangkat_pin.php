<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan perangkat yang boleh masuk dengan PIN.
 *
 * Sampai sekarang izin itu hanya hidup di kue peramban: tidak ada satu pun
 * catatan di peladen tentang perangkat mana saja yang terdaftar. Akibatnya
 * "Lupakan perangkat" hanya berlaku untuk peramban yang sedang dipakai —
 * kalau HP hilang, pemiliknya tidak punya cara mencabut izin HP itu selain
 * mematikan PIN untuk semua perangkat sekaligus.
 *
 * Dengan tabel ini, izin masuk lewat PIN diperiksa di peladen, sehingga
 * mencabutnya dari perangkat lain benar-benar menutup pintu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('perangkat_pin')) {
            return;
        }

        Schema::create('perangkat_pin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Penanda acak 32 aksara milik peramban; lihat PenandaPerangkat.
            $table->string('penanda', 32);

            // Untuk ditampilkan ke pemiliknya, bukan untuk dipakai memutuskan.
            $table->string('peramban', 255)->nullable();
            $table->string('ip', 45)->nullable();

            $table->timestamp('terakhir_dipakai_pada')->nullable();
            $table->timestamps();

            // Satu baris per perangkat per akun: mendaftarkan ulang perangkat
            // yang sama harus memperbarui barisnya, bukan menumpuk baris baru.
            $table->unique(['user_id', 'penanda']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_pin');
    }
};
