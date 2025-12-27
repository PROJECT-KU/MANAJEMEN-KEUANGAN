<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ScopusCampPendaftaran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scopus_camp_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token')->nullable();
            $table->string('id_transaksi')->nullable();
            $table->uuid('scopus_camp_kategori_id')->nullable();
            $table->string('email')->nullable();
            $table->string('nama')->nullable();
            $table->string('telp')->nullable();
            $table->string('affiliasi')->nullable();
            $table->string('ppn')->nullable();
            $table->string('kode_unik')->nullable();
            $table->string('gambar')->nullable();
            $table->string('jumlah_pendaftar')->nullable();
            $table->string('kode_diskon')->nullable();
            $table->string('nominal_diskon')->nullable();
            $table->string('total_pembayaran')->nullable();
            $table->string('status')->default('diproses');
            $table->string('tanggal_reschedule')->nullable();
            $table->string('group_wa')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('scopus_camp_kategori_id')
                ->references('id')
                ->on('scopus_camp_kategori')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scopus_camp_pendaftaran');
    }
}
