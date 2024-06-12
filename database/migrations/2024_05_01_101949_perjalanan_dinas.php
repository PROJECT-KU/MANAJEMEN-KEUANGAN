<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerjalananDinas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perjalanan_dinas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 300)->nullable();
            $table->string('id_transaksi', 300)->nullable();

            // <!-- INPUT 1 -->
            $table->date('tanggal')->nullable();
            $table->bigInteger('uang_masuk')->nullable();
            $table->bigInteger('uang_keluar')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('gambar')->nullable();
            // <!-- END -->

            // <!-- INPUT 2 -->
            $table->bigInteger('uang_keluar2')->nullable();
            $table->text('keterangan2')->nullable();
            $table->string('gambar2')->nullable();
            // <!-- END -->

            // <!-- INPUT 3 -->
            $table->bigInteger('uang_keluar3')->nullable();
            $table->text('keterangan3')->nullable();
            $table->string('gambar3')->nullable();
            // <!-- END -->

            // <!-- INPUT 4 -->
            $table->bigInteger('uang_keluar4')->nullable();
            $table->text('keterangan4')->nullable();
            $table->string('gambar4')->nullable();
            // <!-- END -->

            // <!-- INPUT 5 -->
            $table->bigInteger('uang_keluar5')->nullable();
            $table->text('keterangan5')->nullable();
            $table->string('gambar5')->nullable();
            // <!-- END -->

            // <!-- INPUT 6 -->
            $table->bigInteger('uang_keluar6')->nullable();
            $table->text('keterangan6')->nullable();
            $table->string('gambar6')->nullable();
            // <!-- END -->

            // <!-- INPUT 7 -->
            $table->bigInteger('uang_keluar7')->nullable();
            $table->text('keterangan7')->nullable();
            $table->string('gambar7')->nullable();
            // <!-- END -->

            // <!-- INPUT 8 -->
            $table->bigInteger('uang_keluar8')->nullable();
            $table->text('keterangan8')->nullable();
            $table->string('gambar8')->nullable();
            // <!-- END -->

            // <!-- INPUT 9 -->
            $table->bigInteger('uang_keluar9')->nullable();
            $table->text('keterangan9')->nullable();
            $table->string('gambar9')->nullable();
            // <!-- END -->

            // <!-- INPUT 10 -->
            $table->bigInteger('uang_keluar10')->nullable();
            $table->text('keterangan10')->nullable();
            $table->string('gambar10')->nullable();
            // <!-- END -->

            // <!-- INPUT 11 -->
            $table->date('tanggal11')->nullable();
            $table->bigInteger('uang_masuk11')->nullable();
            $table->bigInteger('uang_keluar11')->nullable();
            $table->text('keterangan11')->nullable();
            $table->string('gambar11')->nullable();
            // <!-- END -->

            // <!-- INPUT 12 -->
            $table->bigInteger('uang_keluar12')->nullable();
            $table->text('keterangan12')->nullable();
            $table->string('gambar12')->nullable();
            // <!-- END -->

            // <!-- INPUT 13 -->
            $table->bigInteger('uang_keluar13')->nullable();
            $table->text('keterangan13')->nullable();
            $table->string('gambar13')->nullable();
            // <!-- END -->

            // <!-- INPUT 14 -->
            $table->bigInteger('uang_keluar14')->nullable();
            $table->text('keterangan14')->nullable();
            $table->string('gambar14')->nullable();
            // <!-- END -->

            // <!-- INPUT 15 -->
            $table->bigInteger('uang_keluar15')->nullable();
            $table->text('keterangan15')->nullable();
            $table->string('gambar15')->nullable();
            // <!-- END -->

            // <!-- INPUT 16 -->
            $table->bigInteger('uang_keluar16')->nullable();
            $table->text('keterangan16')->nullable();
            $table->string('gambar16')->nullable();
            // <!-- END -->

            // <!-- INPUT 17 -->
            $table->bigInteger('uang_keluar17')->nullable();
            $table->text('keterangan17')->nullable();
            $table->string('gambar17')->nullable();
            // <!-- END -->

            // <!-- INPUT 18 -->
            $table->bigInteger('uang_keluar18')->nullable();
            $table->text('keterangan18')->nullable();
            $table->string('gambar18')->nullable();
            // <!-- END -->

            // <!-- INPUT 19 -->
            $table->bigInteger('uang_keluar19')->nullable();
            $table->text('keterangan19')->nullable();
            $table->string('gambar19')->nullable();
            // <!-- END -->

            // <!-- INPUT 20 -->
            $table->bigInteger('uang_keluar20')->nullable();
            $table->text('keterangan20')->nullable();
            $table->string('gambar20')->nullable();
            // <!-- END -->

            // <!-- INPUT 21 -->
            $table->date('tanggal21')->nullable();
            $table->bigInteger('uang_masuk21')->nullable();
            $table->bigInteger('uang_keluar21')->nullable();
            $table->text('keterangan21')->nullable();
            $table->string('gambar21')->nullable();
            // <!-- END -->

            // <!-- INPUT 22 -->
            $table->bigInteger('uang_keluar22')->nullable();
            $table->text('keterangan22')->nullable();
            $table->string('gambar22')->nullable();
            // <!-- END -->

            // <!-- INPUT 23 -->
            $table->bigInteger('uang_keluar23')->nullable();
            $table->text('keterangan23')->nullable();
            $table->string('gambar23')->nullable();
            // <!-- END -->

            // <!-- INPUT 24 -->
            $table->bigInteger('uang_keluar24')->nullable();
            $table->text('keterangan24')->nullable();
            $table->string('gambar24')->nullable();
            // <!-- END -->

            // <!-- INPUT 25 -->
            $table->bigInteger('uang_keluar25')->nullable();
            $table->text('keterangan25')->nullable();
            $table->string('gambar25')->nullable();
            // <!-- END -->

            // <!-- INPUT 26 -->
            $table->bigInteger('uang_keluar26')->nullable();
            $table->text('keterangan26')->nullable();
            $table->string('gambar26')->nullable();
            // <!-- END -->

            // <!-- INPUT 27 -->
            $table->bigInteger('uang_keluar27')->nullable();
            $table->text('keterangan27')->nullable();
            $table->string('gambar27')->nullable();
            // <!-- END -->

            // <!-- INPUT 28 -->
            $table->bigInteger('uang_keluar28')->nullable();
            $table->text('keterangan28')->nullable();
            $table->string('gambar28')->nullable();
            // <!-- END -->

            // <!-- INPUT 29 -->
            $table->bigInteger('uang_keluar29')->nullable();
            $table->text('keterangan29')->nullable();
            $table->string('gambar29')->nullable();
            // <!-- END -->

            // <!-- INPUT 30 -->
            $table->bigInteger('uang_keluar30')->nullable();
            $table->text('keterangan30')->nullable();
            $table->string('gambar30')->nullable();
            // <!-- END -->

            // <!-- INPUT 31 -->
            $table->date('tanggal31')->nullable();
            $table->bigInteger('uang_masuk31')->nullable();
            $table->bigInteger('uang_keluar31')->nullable();
            $table->text('keterangan31')->nullable();
            $table->string('gambar31')->nullable();
            // <!-- END -->

            // <!-- INPUT 32 -->
            $table->bigInteger('uang_keluar32')->nullable();
            $table->text('keterangan32')->nullable();
            $table->string('gambar32')->nullable();
            // <!-- END -->

            // <!-- INPUT 33 -->
            $table->bigInteger('uang_keluar33')->nullable();
            $table->text('keterangan33')->nullable();
            $table->string('gambar33')->nullable();
            // <!-- END -->

            // <!-- INPUT 34 -->
            $table->bigInteger('uang_keluar34')->nullable();
            $table->text('keterangan34')->nullable();
            $table->string('gambar34')->nullable();
            // <!-- END -->

            // <!-- INPUT 35 -->
            $table->bigInteger('uang_keluar35')->nullable();
            $table->text('keterangan35')->nullable();
            $table->string('gambar35')->nullable();
            // <!-- END -->

            // <!-- INPUT 36 -->
            $table->date('tanggal36')->nullable();
            $table->bigInteger('uang_masuk36')->nullable();
            $table->bigInteger('uang_keluar36')->nullable();
            $table->text('keterangan36')->nullable();
            $table->string('gambar36')->nullable();
            // <!-- END -->

            // <!-- INPUT 37 -->
            $table->bigInteger('uang_keluar37')->nullable();
            $table->text('keterangan37')->nullable();
            $table->string('gambar37')->nullable();
            // <!-- END -->

            // <!-- INPUT 38 -->
            $table->bigInteger('uang_keluar38')->nullable();
            $table->text('keterangan38')->nullable();
            $table->string('gambar38')->nullable();
            // <!-- END -->

            // <!-- INPUT 39 -->
            $table->bigInteger('uang_keluar39')->nullable();
            $table->text('keterangan39')->nullable();
            $table->string('gambar39')->nullable();
            // <!-- END -->

            // <!-- INPUT 40 -->
            $table->bigInteger('uang_keluar40')->nullable();
            $table->text('keterangan40')->nullable();
            $table->string('gambar40')->nullable();
            // <!-- END -->

            $table->string('status', 300)->nullable();
            $table->string('tempat', 300)->nullable();
            $table->string('camp', 300)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->text('deskripsi')->nullable();
            $table->bigInteger('total_uang_masuk')->nullable();
            $table->bigInteger('total_uang_keluar')->nullable();
            $table->bigInteger('sisa_saldo')->nullable();
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
        Schema::dropIfExists('perjalanan_dinas');
    }
}
