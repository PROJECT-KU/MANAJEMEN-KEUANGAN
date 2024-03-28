<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Artikel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('categories_artikel_id');
            $table->string('token', 300)->nullable();
            $table->string('judul', 300)->nullable();
            $table->string('kata_kunci', 300)->nullable();
            $table->string('gambar_depan', 300)->nullable();
            $table->string('gambar_cover', 300)->nullable();
            $table->text('isi')->nullable();
            $table->string('dilihat', 300)->nullable();
            $table->string('status', 300)->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('categories_artikel_id')
                ->references('id')->on('categories_artikel')
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
        Schema::dropIfExists('artikel');
    }
}
