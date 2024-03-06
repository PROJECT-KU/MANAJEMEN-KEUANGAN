<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Karir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karir', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 300)->nullable();
            $table->string('nama', 300)->nullable();
            $table->string('telp', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->string('cv', 1000)->nullable();
            $table->string('lamaran', 1000)->nullable();
            $table->string('lainnya', 1000)->nullable();
            $table->string('pendidikan', 300)->nullable();
            $table->string('posisi', 300)->nullable();
            $table->text('desc')->nullable();
            $table->string('status', 300)->nullable();
            $table->dateTime('tanggal_interview')->nullable();
            $table->text('lokasi_interview')->nullable();
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
        Schema::dropIfExists('karir');
    }
}
