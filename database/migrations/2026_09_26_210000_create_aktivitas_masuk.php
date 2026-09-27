<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak percobaan masuk: siapa, kapan, dari mana, berhasil atau tidak.
 * Dipakai untuk menelusuri akses mencurigakan pada sistem keuangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aktivitas_masuk')) {
            return;
        }

        Schema::create('aktivitas_masuk', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // Identitas yang diketik: bisa username atau email, dan bisa saja
            // milik akun yang tidak ada.
            $table->string('identitas', 150)->nullable();
            $table->boolean('berhasil')->default(false)->index();
            $table->string('alasan', 100)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('peramban', 255)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_masuk');
    }
};
