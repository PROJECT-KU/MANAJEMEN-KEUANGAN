<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Recrutment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recrutment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->string('cv', 300)->nullable();
            $table->string('lamaran', 300)->nullable();
            $table->string('lainnya', 300)->nullable();
            $table->string('pendidikan', 300)->nullable();
            $table->string('info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recrutment');
    }
}
