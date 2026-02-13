<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinikScopusChats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinik_scopus_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('pemesanan_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');

            $table->text('message')->nullable();

            // 🔥 simpan banyak gambar
            $table->json('images')->nullable();

            $table->timestamps();

            $table->foreign('pemesanan_id')
                ->references('id')
                ->on('clinikscopus_pemesanan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinik_scopus_chats');
    }
}
