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
            $table->unsignedBigInteger('user_id');
            $table->uuid('biaya_persesi_id')->nullable();
            $table->string('sesi')->nullable();
            $table->string('sesi2')->nullable();
            $table->string('sesi3')->nullable();
            $table->string('sesi4')->nullable();
            $table->string('sesi5')->nullable();
            $table->string('sesi6')->nullable();
            $table->string('sesi7')->nullable();
            $table->string('sesi8')->nullable();
            $table->string('sesi9')->nullable();
            $table->string('spesialis')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('biaya_persesi_id')
                ->references('id')
                ->on('clinikscopus_biaya_persesi')
                ->onDelete('restrict')
                ->onUpdate('cascade');
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
