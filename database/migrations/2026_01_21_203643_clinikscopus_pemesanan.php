<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinikscopusPemesanan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinikscopus_pemesanan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('id_transaksi')->nullable();
            $table->string('kode_booking')->nullable();
            $table->string('sesi')->nullable();
            $table->string('jam_sesi')->nullable();
            $table->string('nama_pemesan')->nullable();
            $table->string('afiliasi_pemesan')->nullable();
            $table->string('email_pemesan')->nullable();
            $table->string('telp_pemesan')->nullable();
            $table->string('kendala')->nullable();
            $table->string('desc_kendala')->nullable();
            $table->string('harga_persesi')->nullable();
            $table->string('diskon')->nullable();
            $table->string('ppn')->nullable();
            $table->string('kode_unik')->nullable();
            $table->string('kode_diskon')->nullable();
            $table->string('tipe_promo')->nullable();
            $table->string('total_pembaya ran')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('clinikscopus_pemesanan');
    }
}
