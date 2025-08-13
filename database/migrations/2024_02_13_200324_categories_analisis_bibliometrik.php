<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoriesAnalisisBibliometrik extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_analisis_bibliometrik', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('token')->nullable();
            $table->string('nama')->nullable();
            $table->string('nama_ke')->nullable();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->string('total_kuota')->nullable();
            $table->string('sisa_kuota')->nullable();
            $table->text('desc')->nullable();
            $table->string('biaya')->nullable();
            $table->string('ppn')->nullable();
            $table->string('tipe_diskon')->nullable();
            $table->string('diskon_persentase')->nullable();
            $table->string('nominal_diskon')->nullable();
            $table->string('kode_diskon')->nullable();
            $table->string('total_biaya')->nullable();
            $table->string('status')->default('draft');
            $table->string('group_wa')->nullable();
            $table->string('gambar')->nullable();
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
        Schema::dropIfExists('categories_analisis_bibliometrik');
    }
}
