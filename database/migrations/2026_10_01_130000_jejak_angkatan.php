<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak perubahan angkatan — sekaligus keranjang untuk yang dihapus.
 *
 * Dua kebutuhan, satu tabel, karena keduanya menjawab pertanyaan yang sama:
 * "apa yang terjadi pada angkatan ini, kapan, oleh siapa". Sebelum ini tidak
 * ada jawabannya sama sekali — menonaktifkan 30 angkatan tidak meninggalkan
 * bekas apa pun, dan menghapusnya permanen tanpa jalan kembali.
 *
 * `data` memuat salinan utuh barisnya saat dihapus, jadi memulihkannya cukup
 * memasukkan kembali isi kolom itu. Jalan ini dipilih daripada `deleted_at`
 * pada tabel aslinya: dua puluh lebih tempat di aplikasi ini membaca
 * `kategori_layanan` lewat DB::table() yang tidak mengenal soft delete sama
 * sekali, jadi angkatan "terhapus" akan tetap terpajang di halaman publik —
 * dan satu tempat yang terlewat sudah cukup untuk itu terjadi.
 *
 * `kategori_id` SENGAJA tanpa kunci asing: barisnya memang sudah tidak ada
 * lagi saat jejak penghapusannya ditulis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angkatan_jejak', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('kategori_id')->index();

            // Disalin, bukan dibaca lewat relasi: angkatannya bisa sudah
            // dihapus, dan jejak tanpa nama tidak bisa dibaca siapa pun.
            $t->string('nama')->nullable();

            $t->string('aksi', 20);
            $t->text('ringkasan')->nullable();

            // Salinan utuh barisnya; hanya terisi pada aksi 'dihapus'.
            $t->json('data')->nullable();

            $t->unsignedBigInteger('oleh_id')->nullable();
            $t->string('oleh_nama')->nullable();

            $t->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angkatan_jejak');
    }
};
