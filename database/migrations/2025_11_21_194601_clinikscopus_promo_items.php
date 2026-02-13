<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClinikscopusPromoItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinikscopus_promo_items', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('promo_id');
            $table->uuid('clinikscopus_id');

            $table->timestamps();

            $table->foreign('promo_id')
                ->references('id')
                ->on('clinikscopus_promo')
                ->onDelete('cascade');

            $table->foreign('clinikscopus_id')
                ->references('id')
                ->on('clinikscopus')
                ->onDelete('cascade');

            $table->unique(['promo_id', 'clinikscopus_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinikscopus_promo_items');
    }
}
