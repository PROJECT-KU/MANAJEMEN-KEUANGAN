<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoriesScopuscamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_scopuscamp', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('token', 300)->nullable();
            $table->string('camp', 300)->nullable();
            $table->string('tempat', 300)->nullable();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->string('kuota', 300)->nullable();
            $table->string('status', 300)->default('AKTIF');
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
        Schema::dropIfExists('categories_scopuscamp');
    }
}
