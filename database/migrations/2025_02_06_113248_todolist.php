<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Todolist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todolist', function (Blueprint $table) {
            // PAPER
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_id_kedua')->nullable();
            $table->string('id_task', 100)->nullable();
            $table->dateTime('tanggal_assign')->nullable();
            $table->dateTime('tanggal_deadline')->nullable();
            $table->string('status')->nullable();
            $table->string('prioritas_task')->nullable();
            $table->string('judul_task')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('tasklist')->nullable();
            $table->text('checked')->nullable();
            $table->string('link_akses')->nullable();
            $table->string('file_task')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');
            $table->foreign('user_id_kedua')
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
        Schema::dropIfExists('todolist');
    }
}
