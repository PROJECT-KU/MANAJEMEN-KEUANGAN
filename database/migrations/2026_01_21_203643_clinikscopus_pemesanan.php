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
            $table->uuid('clinikscopus_id');
            $table->unsignedBigInteger('trainer_id');
            $table->unsignedBigInteger('customer_id');
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
            $table->integer('harga_persesi')->nullable();
            $table->integer('diskon')->nullable();
            $table->integer('ppn')->nullable();
            $table->integer('kode_unik')->nullable();
            $table->string('kode_diskon')->nullable();
            $table->string('tipe_promo')->nullable();
            $table->integer('total_pembayaran')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->dateTime('tanggal_booking')->nullable();
            $table->string('gambar')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();

            $table->foreign('trainer_id')
                ->references('id')
                ->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('clinikscopus_id')
                ->references('id')->on('clinikscopus')
                ->onDelete('CASCADE');
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
