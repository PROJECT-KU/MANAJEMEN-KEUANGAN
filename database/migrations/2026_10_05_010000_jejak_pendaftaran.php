<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak perubahan pendaftaran, terpisah dari catatan panitia.
 *
 * Sebelum ini jejak perpindahan status DITULIS KE KOLOM `note` — kolom yang
 * sama yang dipakai panitia menulis catatannya sendiri. Tiga akibatnya:
 *
 * 1. Catatan panitia bercampur jejak sistem, dan panitia yang menyunting
 *    catatannya bisa menghapus riwayat audit tanpa sadar.
 * 2. Kolomnya memanjang tanpa batas; satu baris di data ini sudah memuat
 *    lima perpindahan status dalam satu paragraf.
 * 3. Scopus Kafe dan Clinik Scopus TIDAK punya kolom `note` sama sekali,
 *    jadi perpindahan status di kedua layanan itu tidak pernah terekam di
 *    mana pun. Tabel ini menutup lubang itu sekalian.
 *
 * Bentuknya mengikuti `angkatan_jejak` yang sudah ada supaya dua tabel jejak
 * di aplikasi ini tidak punya dua tata nama berbeda.
 *
 * `layanan` + `pendaftaran_id` SENGAJA tanpa kunci asing: pendaftarannya
 * tersebar di lima tabel yang bentuknya berbeda, dan jejaknya harus tetap ada
 * walau barisnya kelak dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_jejak', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->string('layanan', 32);
            $t->string('pendaftaran_id', 64);

            $t->string('aksi', 20);
            $t->string('dari')->nullable();
            $t->string('ke')->nullable();
            $t->text('ringkasan')->nullable();

            // Disalin, bukan dibaca lewat relasi: akunnya bisa sudah dihapus,
            // dan jejak tanpa nama tidak menjawab "siapa yang mengubah".
            $t->unsignedBigInteger('oleh_id')->nullable();
            $t->string('oleh_nama')->nullable();

            $t->timestamp('created_at')->nullable();

            // Satu indeks gabungan: jejak selalu dibaca per pendaftaran,
            // tidak pernah per layanan saja.
            $t->index(['layanan', 'pendaftaran_id'], 'pendaftaran_jejak_milik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_jejak');
    }
};
