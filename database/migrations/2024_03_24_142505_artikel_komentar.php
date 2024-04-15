<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArtikelKomentar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artikel_komentar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 300)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('categories_artikel_id');
            $table->unsignedBigInteger('artikel_id');
            $table->string('reply', 300)->nullable();
            $table->string('nama', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->string('link_website', 300)->nullable();
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('categories_artikel_id')
                ->references('id')->on('categories_artikel')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('artikel_id')
                ->references('id')->on('artikel')
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
        Schema::dropIfExists('artikel_komentar');
    }
}
