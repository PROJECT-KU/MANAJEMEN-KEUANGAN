<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaji extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaji', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('presensi_id')->nullable();
            $table->string('id_transaksi', 300)->nullable();
            $table->string('gaji_pokok', 300)->nullable();

            // <!-- JUMLAH NOMINAL LEMBUR -->
            $table->string('lembur', 300)->nullable();
            $table->string('lembur1', 300)->nullable();
            $table->string('lembur2', 300)->nullable();
            $table->string('lembur3', 300)->nullable();
            $table->string('lembur4', 300)->nullable();
            $table->string('lembur5', 300)->nullable();
            $table->string('lembur6', 300)->nullable();
            $table->string('lembur7', 300)->nullable();
            $table->string('lembur8', 300)->nullable();
            $table->string('lembur9', 300)->nullable();
            $table->string('lembur10', 300)->nullable();
            // <!-- END -->

            // <!-- TOTAL JAM LEMBUR -->
            $table->text('jumlah_lembur')->nullable();
            $table->text('jumlah_lembur1')->nullable();
            $table->text('jumlah_lembur2')->nullable();
            $table->text('jumlah_lembur3')->nullable();
            $table->text('jumlah_lembur4')->nullable();
            $table->text('jumlah_lembur5')->nullable();
            $table->text('jumlah_lembur6')->nullable();
            $table->text('jumlah_lembur7')->nullable();
            $table->text('jumlah_lembur8')->nullable();
            $table->text('jumlah_lembur9')->nullable();
            $table->text('jumlah_lembur10')->nullable();
            // <!-- END -->

            $table->string('total_lembur', 300)->nullable();

            // <!-- JUMLAH NOMINAL BONUS JOGJA -->
            $table->string('bonus', 300)->nullable();
            $table->string('bonus1', 300)->nullable();
            $table->string('bonus2', 300)->nullable();
            $table->string('bonus3', 300)->nullable();
            $table->string('bonus4', 300)->nullable();
            $table->string('bonus5', 300)->nullable();
            $table->string('bonus6', 300)->nullable();
            $table->string('bonus7', 300)->nullable();
            $table->string('bonus8', 300)->nullable();
            $table->string('bonus9', 300)->nullable();
            $table->string('bonus10', 300)->nullable();
            // <!-- END -->

            // <!-- JUMLAH NOMINAL BONUS LUAR KOTA -->
            $table->string('bonus_luar', 300)->nullable();
            $table->string('bonus_luar1', 300)->nullable();
            $table->string('bonus_luar2', 300)->nullable();
            $table->string('bonus_luar3', 300)->nullable();
            $table->string('bonus_luar4', 300)->nullable();
            $table->string('bonus_luar5', 300)->nullable();
            $table->string('bonus_luar6', 300)->nullable();
            $table->string('bonus_luar7', 300)->nullable();
            $table->string('bonus_luar8', 300)->nullable();
            $table->string('bonus_luar9', 300)->nullable();
            $table->string('bonus_luar10', 300)->nullable();
            // <!-- END -->

            // <!-- TOTAL HARI BONUS JOGJA -->
            $table->string('jumlah_bonus', 300)->nullable();
            $table->string('jumlah_bonus1', 300)->nullable();
            $table->string('jumlah_bonus2', 300)->nullable();
            $table->string('jumlah_bonus3', 300)->nullable();
            $table->string('jumlah_bonus4', 300)->nullable();
            $table->string('jumlah_bonus5', 300)->nullable();
            $table->string('jumlah_bonus6', 300)->nullable();
            $table->string('jumlah_bonus7', 300)->nullable();
            $table->string('jumlah_bonus8', 300)->nullable();
            $table->string('jumlah_bonus9', 300)->nullable();
            $table->string('jumlah_bonus10', 300)->nullable();
            // <!-- END -->

            // <!-- TOTAL HARI BONUS LUAR KOTA -->
            $table->string('jumlah_bonus_luar', 300)->nullable();
            $table->string('jumlah_bonus_luar1', 300)->nullable();
            $table->string('jumlah_bonus_luar2', 300)->nullable();
            $table->string('jumlah_bonus_luar3', 300)->nullable();
            $table->string('jumlah_bonus_luar4', 300)->nullable();
            $table->string('jumlah_bonus_luar5', 300)->nullable();
            $table->string('jumlah_bonus_luar6', 300)->nullable();
            $table->string('jumlah_bonus_luar7', 300)->nullable();
            $table->string('jumlah_bonus_luar8', 300)->nullable();
            $table->string('jumlah_bonus_luar9', 300)->nullable();
            $table->string('jumlah_bonus_luar10', 300)->nullable();
            // <!-- END -->

            $table->string('total_bonus', 300)->nullable();
            $table->text('tunjangan')->nullable();
            $table->text('tunjangan_bpjs')->nullable();
            $table->text('tunjangan_thr')->nullable();
            $table->text('tunjangan_pulsa')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->text('potongan')->nullable();
            $table->text('pph')->nullable();
            $table->text('alpha')->nullable();
            $table->string('total', 300)->nullable();
            $table->text('status')->default('PENDING');
            $table->text('note')->nullable();
            $table->string('gambar', 300)->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT')
                ->onUpdate('CASCADE');

            $table->foreign('presensi_id')
                ->references('id')->on('presensi')
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
        Schema::dropIfExists('gaji');
    }
}
