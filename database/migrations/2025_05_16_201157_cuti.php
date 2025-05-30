<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Cuti extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('id_pengajuan')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('jenis_cuti')->nullable();
            $table->dateTime('tanggal_mulai_cuti')->nullable();
            $table->dateTime('tanggal_selesai_cuti')->nullable();
            $table->string('total_hari_cuti')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('dokumen')->nullable();
            $table->text('status')->nullable();
            $table->dateTime('disetujui_pada')->nullable();
            $table->text('alasan_ditolak')->nullable();
            $table->dateTime('dibatalkan_pada')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('cuti');
    }
}
