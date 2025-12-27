<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Analisisbibliometrik extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analisis_bibliometrik', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token')->nullable();
            $table->string('id_transaksi')->nullable();
            $table->uuid('categories_analisis_bibliometrik_id')->nullable();
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

            $table->foreign('categories_analisis_bibliometrik_id', 'fk_cat_analisis_bib')
                ->references('id')
                ->on('categories_analisis_bibliometrik')
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
        Schema::dropIfExists('analisis_bibliometrik');
    }
}
