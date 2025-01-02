<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PendaftaranScopusKafe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftaran_scopus_kafe', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('id_pemesanan', 10)->nullable();

            // detail data diri
            $table->string('nama')->nullable();
            $table->dateTime('tanggal_pemesanan')->nullable();
            $table->string('email')->nullable();
            $table->string('telp')->nullable();

            // form sesi 1
            $table->text('sesi')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('biaya')->nullable();
            $table->string('kode_unik_pembayaran')->nullable();
            $table->text('subtotal_pembayaran')->nullable();

            // form sesi 2
            $table->text('sesi_kedua')->nullable();
            $table->time('waktu_mulai_kedua')->nullable();
            $table->time('waktu_selesai_kedua')->nullable();
            $table->string('lokasi_kedua')->nullable();
            $table->string('biaya_kedua')->nullable();
            $table->string('kode_unik_pembayaran_kedua')->nullable();
            $table->text('subtotal_pembayaran_kedua')->nullable();

            // form sesi 3
            $table->text('sesi_ketiga')->nullable();
            $table->time('waktu_mulai_ketiga')->nullable();
            $table->time('waktu_selesai_ketiga')->nullable();
            $table->string('lokasi_ketiga')->nullable();
            $table->string('biaya_ketiga')->nullable();
            $table->string('kode_unik_pembayaran_ketiga')->nullable();
            $table->text('subtotal_pembayaran_ketiga')->nullable();

            $table->string('total_keseluruhan_pembayaran')->nullable();
            $table->string('status')->default('menunggu verifikasi');
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meme');
    }
}
