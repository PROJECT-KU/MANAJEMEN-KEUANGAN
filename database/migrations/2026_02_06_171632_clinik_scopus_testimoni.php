<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinikScopusTestimoni extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinik_scopus_testimoni', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ================== RELASI ==================
            $table->uuid('clinikscopus_id');
            $table->unsignedBigInteger('trainer_id');
            $table->unsignedBigInteger('customer_id');

            // ================== DATA TRANSAKSI (TRAINER) ==================
            $table->string('id_transaksi')->nullable();
            $table->string('kode_booking')->nullable();
            $table->string('sesi')->nullable();
            $table->string('jam_sesi')->nullable();

            // ================== TESTIMONI TRAINER ==================
            $table->tinyInteger('rating')->nullable()->comment('1-5');
            $table->text('komentar')->nullable();

            // ================== TESTIMONI APLIKASI (WEB ONLY) ==================
            $table->tinyInteger('rating_aplikasi')->nullable()->comment('1-5');
            $table->text('komentar_aplikasi')->nullable();

            // ================== PROPERTI UMUM ==================
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'published', 'hidden'])
                ->default('pending');

            $table->timestamps();

            $table->foreign('trainer_id')
                ->references('id')
                ->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('clinikscopus_id')
                ->references('id')->on('clinikscopus')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinik_scopus_testimoni');
    }
}
