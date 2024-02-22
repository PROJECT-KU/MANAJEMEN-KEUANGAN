<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Scopuscamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scopuscamp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 300)->nullable();
            $table->string('id_transaksi', 300)->nullable();
            $table->unsignedBigInteger('categories_scopuscamp_id')->nullable();
            $table->string('email', 300)->nullable();
            $table->string('nama', 300)->nullable();
            $table->string('judul', 300)->nullable();
            $table->string('telp', 300)->nullable();
            $table->string('afiliasi', 300)->nullable();
            $table->string('pembayaran', 300)->nullable();
            $table->string('gambar', 300)->nullable();
            $table->string('status', 300)->nullable();
            $table->string('camp', 300)->nullable();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->string('tempat', 300)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('categories_scopuscamp_id')
                ->references('id')->on('categories_scopuscamp')
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
        Schema::dropIfExists('scopuscamp');
    }
}
