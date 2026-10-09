<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berapa dana yang dikembalikan, dan kapan.
 *
 * Status "Dana dikembalikan" sudah ada sejak lama, tetapi ANGKANYA tidak
 * pernah tersimpan di mana pun. Akibatnya pendaftaran yang sudah direfund
 * tetap terhitung penuh di ringkasan uang masuk — terukur di layar
 * Pendaftar Layanan, ubin "uang masuk dari yang lunas" tidak pernah
 * dikurangi sepeser pun oleh refund.
 *
 * TABEL TERSENDIRI, bukan dua kolom di lima tabel pendaftaran:
 *
 *   - lima migrasi kolom berarti lima tempat yang harus sepakat, dan kueri
 *     gabungan di PendaftaranSemuaLayanan harus menyebut kelimanya;
 *   - satu pendaftaran bisa direfund SEBAGIAN lalu sebagian lagi, dan dua
 *     kolom hanya memuat satu kali;
 *   - polanya sudah terbukti di pendaftaran_jejak dan pendaftaran_peserta:
 *     induknya ditunjuk pasangan (layanan, pendaftaran_id), sebab sasarannya
 *     lima tabel berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_pengembalian', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('layanan', 40);
            $t->string('pendaftaran_id', 64);
            $t->unsignedBigInteger('nominal');
            $t->date('tanggal');
            $t->string('cara', 40)->nullable();
            $t->string('catatan', 255)->nullable();
            $t->string('oleh_id', 64)->nullable();
            $t->string('oleh_nama', 120)->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();

            // Dicari selalu berpasangan; satu indeks untuk keduanya.
            $t->index(['layanan', 'pendaftaran_id'], 'pengembalian_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_pengembalian');
    }
};
