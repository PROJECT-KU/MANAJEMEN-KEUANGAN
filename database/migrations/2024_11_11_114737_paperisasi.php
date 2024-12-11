<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Paperisasi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paperisasi', function (Blueprint $table) {
            // PAPER
            $table->uuid('id')->primary();
            $table->string('token', 100)->nullable();
            $table->string('id_paper', 10)->nullable();
            $table->dateTime('tanggal_masuk_paper')->nullable();
            $table->text('judul_paper')->nullable();
            $table->text('first_author')->nullable();
            $table->text('affiliasi_first_author')->nullable();
            $table->text('co_author')->nullable();
            $table->text('affiliasi_co_author')->nullable();
            $table->string('jurnal_target')->nullable();
            $table->string('q_jurnal')->nullable();
            $table->string('estimasi_terbit')->nullable();
            $table->string('apc_jurnal')->nullable();
            $table->string('status_paper')->nullable();

            // PROGRES ANATOMI
            $table->dateTime('progres_anatomi_tanggal')->nullable();
            $table->string('progres_anatomi_status')->nullable();
            $table->string('progres_anatomi_estimasi')->nullable();
            $table->text('progres_anatomi_keterangan')->nullable();
            $table->string('file_anatomi')->nullable();

            $table->dateTime('progres_anatomi_tanggal_second')->nullable();
            $table->string('progres_anatomi_status_second')->nullable();
            $table->string('progres_anatomi_estimasi_second')->nullable();
            $table->text('progres_anatomi_keterangan_second')->nullable();
            $table->string('file_anatomi_second')->nullable();

            $table->dateTime('progres_anatomi_tanggal_third')->nullable();
            $table->string('progres_anatomi_status_third')->nullable();
            $table->string('progres_anatomi_estimasi_third')->nullable();
            $table->text('progres_anatomi_keterangan_third')->nullable();
            $table->string('file_anatomi_third')->nullable();

            // PROGRES PAPER
            $table->dateTime('progres_paper_tanggal')->nullable();
            $table->string('progres_paper_status')->nullable();
            $table->string('progres_paper_estimasi')->nullable();
            $table->text('progres_paper_keterangan')->nullable();
            $table->string('file_paper')->nullable();

            $table->dateTime('progres_paper_tanggal_second')->nullable();
            $table->string('progres_paper_status_second')->nullable();
            $table->string('progres_paper_estimasi_second')->nullable();
            $table->text('progres_paper_keterangan_second')->nullable();
            $table->string('file_paper_second')->nullable();

            $table->dateTime('progres_paper_tanggal_third')->nullable();
            $table->string('progres_paper_status_third')->nullable();
            $table->string('progres_paper_estimasi_third')->nullable();
            $table->text('progres_paper_keterangan_third')->nullable();
            $table->string('file_paper_third')->nullable();


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
        Schema::dropIfExists('meme');
    }
}
