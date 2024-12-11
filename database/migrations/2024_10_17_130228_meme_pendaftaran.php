<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Memependaftaran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meme_pendaftaran', function (Blueprint $table) {
            // PAPER
            $table->uuid('id')->primary();
            $table->string('meme_id', 10)->nullable();
            $table->string('token', 100)->nullable();
            $table->string('nama_pendaftar')->nullable();
            $table->string('telp_pendaftar')->nullable();
            $table->string('email_pendaftar')->nullable();
            $table->string('institusi_pendaftar')->nullable();
            $table->string('judul_pendaftar')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->string('sesi')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('kode_unik_biaya')->nullable();
            $table->string('biaya')->nullable();
            $table->text('total')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('meme_id')
                ->references('id')->on('meme')
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
        Schema::dropIfExists('meme');
    }
}
