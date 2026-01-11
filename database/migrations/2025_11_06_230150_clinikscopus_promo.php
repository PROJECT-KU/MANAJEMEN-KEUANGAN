<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinikscopusPromo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinikscopus_promo', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nama_promo');
            $table->enum('status', ['active', 'non active'])->default('active');

            $table->dateTime('tanggal_mulai_promo');
            $table->dateTime('tanggal_selesai_promo');

            $table->integer('total_kuota_promo')->nullable();

            $table->string('harga_normal');
            $table->string('ppn')->nullable();

            $table->enum('tipe_diskon', ['persentase', 'nominal', 'bundling'])->nullable();
            $table->string('diskon_persentase')->nullable();
            $table->string('nominal_diskon')->nullable();

            $table->string('kode_diskon')->nullable();
            $table->string('total_biaya')->nullable();

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
        Schema::dropIfExists('clinikscopus_promo');
    }
}
