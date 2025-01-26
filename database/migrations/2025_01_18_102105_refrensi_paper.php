<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefrensiPaper extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refrensi_paper', function (Blueprint $table) {
            // PAPER
            $table->uuid('id')->primary();
            $table->string('token', 100)->nullable();
            $table->string('nama_author')->nullable();
            $table->string('nama_journal')->nullable();
            $table->string('quartile_journal')->nullable();
            $table->text('subjek_area_journal')->nullable();
            $table->text('abstrak')->nullable();
            $table->text('judul_paper')->nullable();
            $table->string('doi')->nullable();
            $table->string('apc')->nullable();
            $table->string('type')->nullable();
            $table->string('file')->nullable();
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
        Schema::dropIfExists('refrensi_paper');
    }
}
