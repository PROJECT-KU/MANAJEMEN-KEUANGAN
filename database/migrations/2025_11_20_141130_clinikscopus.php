<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Clinikscopus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinikscopus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->nullable();
            $table->string('sesi')->nullable();
            $table->string('spesialis')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinikscopus');
    }
}
