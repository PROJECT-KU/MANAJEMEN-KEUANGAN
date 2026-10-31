<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode promo di tabel promo_layanan.
 *
 * Sampai sekarang promo di layar Promo Layanan berlaku OTOMATIS: dicentang
 * ke angkatan, dan harganya langsung turun di halaman umum lengkap dengan
 * coretan di harga lamanya. Kode diskon yang ada hanya milik ANGKATAN
 * (`kategori_layanan.kode_diskon`), satu kode per angkatan, dan harus
 * diketik ulang di tiap angkatan yang mau memakainya.
 *
 * Keputusan pemiliknya 31 Okt 2026: promo boleh punya kode, dan promo yang
 * BERKODE tidak lagi memotong harga dengan sendirinya — harganya baru turun
 * sesudah orangnya mengetikkan kodenya di borang dan kodenya terbukti sah.
 *
 * Kolomnya BOLEH KOSONG, dan itu yang menjaga promo yang sudah ada tetap
 * berjalan seperti sebelumnya: tanpa kode = otomatis seperti dulu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_layanan', function (Blueprint $table) {
            $table->string('kode', 60)->nullable()->after('nama');

            /*
             * Berindeks, bukan unik.
             *
             * Dicari dari jalur umum tiap kali orang mengetikkan kodenya,
             * jadi ia perlu indeks. TIDAK dibuat unik: promo lama boleh
             * memakai ulang kode yang sudah kedaluwarsa, dan keunikan di
             * tingkat basis data akan menolaknya dengan galat yang tidak
             * menyebut sebabnya. Yang menjaga bentrok kode AKTIF adalah
             * aturan di borangnya, yang bisa menjelaskan dirinya.
             */
            $table->index('kode', 'promo_layanan_kode_index');
        });
    }

    public function down(): void
    {
        Schema::table('promo_layanan', function (Blueprint $table) {
            $table->dropIndex('promo_layanan_kode_index');
            $table->dropColumn('kode');
        });
    }
};
