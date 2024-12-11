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
            $table->string('token', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('id_transaksi', 100)->nullable();
            $table->string('gaji_pokok', 100)->nullable();

            // <!-- JUMLAH NOMINAL LEMBUR -->
            $table->string('lembur', 100)->nullable();
            $table->string('lembur1', 100)->nullable();
            $table->string('lembur2', 100)->nullable();
            $table->string('lembur3', 100)->nullable();
            $table->string('lembur4', 100)->nullable();
            $table->string('lembur5', 100)->nullable();
            $table->string('lembur6', 100)->nullable();
            $table->string('lembur7', 100)->nullable();
            $table->string('lembur8', 100)->nullable();
            $table->string('lembur9', 100)->nullable();
            $table->string('lembur10', 100)->nullable();
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

            $table->string('total_lembur', 100)->nullable();

            // <!-- JUMLAH NOMINAL BONUS JOGJA -->
            $table->string('bonus', 100)->nullable();
            $table->string('bonus1', 100)->nullable();
            $table->string('bonus2', 100)->nullable();
            $table->string('bonus3', 100)->nullable();
            $table->string('bonus4', 100)->nullable();
            $table->string('bonus5', 100)->nullable();
            $table->string('bonus6', 100)->nullable();
            $table->string('bonus7', 100)->nullable();
            $table->string('bonus8', 100)->nullable();
            $table->string('bonus9', 100)->nullable();
            $table->string('bonus10', 100)->nullable();
            // <!-- END -->

            // <!-- JUMLAH NOMINAL BONUS LUAR KOTA -->
            $table->string('bonus_luar', 100)->nullable();
            $table->string('bonus_luar1', 100)->nullable();
            $table->string('bonus_luar2', 100)->nullable();
            $table->string('bonus_luar3', 100)->nullable();
            $table->string('bonus_luar4', 100)->nullable();
            $table->string('bonus_luar5', 100)->nullable();
            $table->string('bonus_luar6', 100)->nullable();
            $table->string('bonus_luar7', 100)->nullable();
            $table->string('bonus_luar8', 100)->nullable();
            $table->string('bonus_luar9', 100)->nullable();
            $table->string('bonus_luar10', 100)->nullable();
            // <!-- END -->

            // <!-- TOTAL HARI BONUS JOGJA -->
            $table->string('jumlah_bonus', 100)->nullable();
            $table->string('jumlah_bonus1', 100)->nullable();
            $table->string('jumlah_bonus2', 100)->nullable();
            $table->string('jumlah_bonus3', 100)->nullable();
            $table->string('jumlah_bonus4', 100)->nullable();
            $table->string('jumlah_bonus5', 100)->nullable();
            $table->string('jumlah_bonus6', 100)->nullable();
            $table->string('jumlah_bonus7', 100)->nullable();
            $table->string('jumlah_bonus8', 100)->nullable();
            $table->string('jumlah_bonus9', 100)->nullable();
            $table->string('jumlah_bonus10', 100)->nullable();
            // <!-- END -->

            // <!-- TOTAL HARI BONUS LUAR KOTA -->
            $table->string('jumlah_bonus_luar', 100)->nullable();
            $table->string('jumlah_bonus_luar1', 100)->nullable();
            $table->string('jumlah_bonus_luar2', 100)->nullable();
            $table->string('jumlah_bonus_luar3', 100)->nullable();
            $table->string('jumlah_bonus_luar4', 100)->nullable();
            $table->string('jumlah_bonus_luar5', 100)->nullable();
            $table->string('jumlah_bonus_luar6', 100)->nullable();
            $table->string('jumlah_bonus_luar7', 100)->nullable();
            $table->string('jumlah_bonus_luar8', 100)->nullable();
            $table->string('jumlah_bonus_luar9', 100)->nullable();
            $table->string('jumlah_bonus_luar10', 100)->nullable();
            // <!-- END -->

            $table->string('total_bonus', 100)->nullable();
            $table->text('tunjangan')->nullable();
            $table->text('tunjangan_bpjs')->nullable();
            $table->text('tunjangan_thr')->nullable();
            $table->text('tunjangan_pulsa')->nullable();
            $table->text('webinar')->nullable();
            $table->text('kinerja')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->text('potongan')->nullable();
            $table->text('pph')->nullable();
            $table->text('alpha')->nullable();
            $table->string('total', 100)->nullable();
            $table->text('status')->nullable();
            $table->text('note')->nullable();
            $table->string('gambar', 100)->nullable();
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
