<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan pendaftaran yang dihapus — termasuk uang yang ikut terhapus.
 *
 * Menghapus satu pendaftaran bukan cuma membuang satu baris: pembayaran yang
 * sudah tercatat atas namanya ikut dibuang, dan angka itu keluar dari
 * pembukuan tanpa menyisakan apa pun. Layar rinciannya mencatat dengan
 * teliti tiap medan yang disunting, tiap percobaan kirim surat, dan tiap
 * rupiah yang dikembalikan — lalu satu klik menghapus seluruhnya, dan tidak
 * ada satu layar pun yang bisa menjawab "ke mana perginya".
 *
 * Yang disimpan di sini POTRETNYA, bukan barisnya sendiri. Soft delete di
 * lima tabel pendaftaran berarti lima migrasi dan setiap kueri di seluruh
 * aplikasi — termasuk union lima cabang — harus ingat mengecualikan yang
 * terhapus; satu yang lupa membuat pendaftaran yang sudah dibuang muncul
 * lagi di tempat yang tidak terduga. Potret JSON tidak menuntut apa pun dari
 * kueri yang sudah ada.
 *
 * Ini CATATAN, bukan tempat sampah: tidak ada jalan mengembalikannya, dan
 * layarnya menyebut itu apa adanya supaya tidak ada yang menghapus sambil
 * mengira masih bisa dibatalkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_dihapus', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('layanan', 40);
            $t->string('pendaftaran_id', 64);

            // Keempatnya disalin keluar dari potretnya supaya daftar arsipnya
            // bisa dicari dan diurutkan tanpa membongkar JSON tiap baris.
            $t->string('nomor', 120)->nullable();
            $t->string('nama', 190)->nullable();
            $t->string('email', 190)->nullable();
            $t->string('status', 60)->nullable();

            $t->unsignedBigInteger('total')->default(0);

            /*
             * Uang yang BENAR-BENAR ikut terhapus: jumlah baris pembayaran
             * yang dibuang bersama pendaftarannya. Berbeda dari `total`, yang
             * cuma tagihannya — dan tagihan yang belum dibayar sepeser pun
             * tidak meninggalkan lubang di pembukuan.
             */
            $t->unsignedBigInteger('uang_terhapus')->default(0);
            $t->unsignedInteger('jumlah_pembayaran')->default(0);
            $t->unsignedInteger('jumlah_jejak')->default(0);

            // Seluruh barisnya, pembayarannya, pesertanya, dan jejaknya.
            $t->json('potret')->nullable();

            $t->string('oleh_id', 64)->nullable();
            $t->string('oleh_nama', 120)->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();

            $t->index(['layanan', 'created_at'], 'dihapus_layanan_waktu');
            $t->index('pendaftaran_id', 'dihapus_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_dihapus');
    }
};
