<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel 'maintenance' dan 'tambah_barang' sudah lama dipakai kode (model,
 * controller, dan view-nya ada) tetapi tidak punya migrasi, sehingga database
 * baru/lokal tidak memilikinya dan halaman More, Maintenance, serta Laporan
 * Peserta galat "Base table or view not found".
 *
 * Kolomnya mengikuti $fillable pada model plus kolom yang dipakai view.
 * Dijaga hasTable() supaya aman dijalankan di database yang tabelnya sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance')) {
            Schema::create('maintenance', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('title', 200)->nullable();
                $table->text('note')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->string('status', 50)->nullable();
                $table->string('gambar', 300)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tambah_barang')) {
            Schema::create('tambah_barang', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('nama_barang', 200)->nullable();
                // Nominal disimpan sebagai string, mengikuti tabel gaji.
                $table->string('harga_barang', 100)->nullable();
                $table->string('diskon', 100)->nullable();
                $table->string('stok', 100)->nullable();
                $table->string('jenis', 100)->nullable();
                $table->string('perhari', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tambah_barang');
        Schema::dropIfExists('maintenance');
    }
};
