<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk saringan dan pengurut catatan penghapusan.
 *
 * Tabelnya cuma punya (layanan, created_at) dan (pendaftaran_id) — dibuat
 * saat layarnya hanya bisa mencari teks. Sejak saringan keadaan, "dihapus
 * oleh", dan pengurut ditambahkan, empat kolom lain ikut dipakai tanpa satu
 * indeks pun.
 *
 * `dipulihkan_at` yang paling menggigit: ia dipakai SELURUH angka ringkasan
 * dan saringan keadaan, jadi tiap kali layarnya dibuka seluruh tabel
 * dipindai — terukur lima dari sembilan kueri per muat sebelum
 * agregatnya disatukan.
 *
 * Hari ini tabelnya cuma berisi beberapa baris, jadi tidak ada yang terasa.
 * Yang dijaga indeks ini keadaan dua tahun lagi, saat arsipnya sudah
 * memuat ribuan penghapusan dan tidak ada lagi yang ingat kenapa layarnya
 * melambat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->index('dipulihkan_at', 'dihapus_dipulihkan');
            $t->index('oleh_nama', 'dihapus_oleh');
            $t->index('uang_terhapus', 'dihapus_uang');
            $t->index('nama', 'dihapus_nama');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_dihapus', function (Blueprint $t) {
            $t->dropIndex('dihapus_dipulihkan');
            $t->dropIndex('dihapus_oleh');
            $t->dropIndex('dihapus_uang');
            $t->dropIndex('dihapus_nama');
        });
    }
};
