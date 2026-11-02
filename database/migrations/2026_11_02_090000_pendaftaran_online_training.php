<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendaftaran Class Online (Online Training).
 *
 * Layanannya sudah ada di katalog sejak awal dan punya satu baris tarif,
 * tetapi TIDAK punya apa pun yang lain: tanpa tabel pendaftaran, tanpa
 * pengendali, tanpa tampilan, tanpa rute. Menu "Class Online" di kepala
 * situs menunjuk ke situs WordPress di luar aplikasi ini — jadi tidak ada
 * satu pun pendaftaran Class Online yang pernah tercatat di sini.
 *
 * Bentuknya MENYALIN scopus_camp_pendaftaran kolom demi kolom, dan itu
 * disengaja: seluruh lapisan bersama — PendaftaranSemuaLayanan, DampakPromo,
 * PotonganKode, ekspor, layar panitia — membaca tabel pendaftaran lewat peta
 * kolom yang sama. Tabel yang bentuknya berbeda berarti tiap lapisan itu
 * butuh cabang khususnya sendiri.
 *
 * Yang TIDAK ikut disalin: `tanggal_reschedule` dan `group_wa` khas Camp
 * (kelas tatap muka yang bisa dijadwalkan ulang dan punya grup per angkatan)
 * tetap dibawa, sebab kelas daring pun dijadwalkan ulang dan punya grup
 * pesertanya sendiri. Kolom `gambar` DIBUANG: pembayarannya seluruhnya lewat
 * gerbang sejak hari pertama, jadi tidak pernah ada bukti transfer yang
 * diunggah — dan kolom yang tidak pernah diisi hanya mengundang jalur unggah
 * dihidupkan lagi tanpa sengaja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_training_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token', 60)->nullable();
            $table->string('id_transaksi', 60)->nullable()->index();

            // Angkatannya baris kategori_layanan, sama seperti layanan
            // berangkatan lain; tanpa kunci asing sebab tabel itu dipakai
            // bersama enam layanan dan barisnya boleh dihapus panitia.
            $table->uuid('kategori_id')->nullable()->index();

            $table->string('nama')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('telp', 40)->nullable()->index();
            $table->string('affiliasi')->nullable();

            $table->string('ppn', 30)->nullable();
            $table->string('kode_unik', 30)->nullable();
            $table->string('jumlah_pendaftar', 10)->nullable();
            $table->string('kode_diskon', 60)->nullable();
            $table->string('nominal_diskon', 30)->nullable();
            $table->string('total_pembayaran', 30)->nullable();

            $table->string('status')->default('diproses')->index();
            $table->date('tanggal_reschedule')->nullable();
            $table->string('group_wa')->nullable();
            $table->text('note')->nullable();

            // Tali ke promo yang memotong harganya; dibaca layar Dampak Promo
            // dan penjaga kuota promo.
            $table->uuid('promo_id')->nullable()->index();

            /*
             * Medan gerbang pembayaran, sama persis dengan Scopus Camp.
             *
             * `bayar_nomor` ada sejak awal di sini: DOKU menolak nomor
             * tagihan yang sudah pernah dipakai dengan "INVOICE ALREADY
             * USED", jadi tagihan ulang memakai nomor berakhiran -R2, -R3.
             * Di Camp kolom itu baru ditambahkan belakangan lewat migrasi
             * tersendiri setelah cacatnya muncul di produksi.
             */
            $table->string('cara_bayar', 30)->nullable();
            $table->string('bayar_rujukan', 100)->nullable();
            $table->string('bayar_nomor', 80)->nullable()->index();
            $table->string('bayar_permintaan_id', 100)->nullable();
            $table->text('bayar_url')->nullable();
            $table->string('bayar_status', 40)->nullable();
            $table->string('bayar_kanal', 60)->nullable();
            $table->timestamp('bayar_pada')->nullable();
            $table->timestamp('kedaluwarsa_pada')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_training_pendaftaran');
    }
};
