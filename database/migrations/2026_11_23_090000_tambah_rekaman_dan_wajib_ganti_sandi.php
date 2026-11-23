<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekaman webinar, dan penanda akun yang sandinya masih bawaan.
 *
 * Rekamannya menempel pada ANGKATAN, bukan pada pendaftaran. Satu webinar
 * menghasilkan satu rekaman, dan yang berhak menontonnya seluruh peserta
 * angkatan itu — menyimpannya per pendaftaran berarti menyalin tautan yang
 * sama puluhan kali, lalu salah satunya tertinggal saat tautannya diganti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_layanan', function (Blueprint $tabel) {
            /*
             * 500 huruf, bukan 255. Tautan YouTube biasa memang pendek,
             * tetapi yang disalin orang dari peramban membawa ekor panjang
             * (?si=..., &list=..., &pp=...). Dipotong di 255, tautannya
             * tersimpan rusak TANPA galat — yang terlihat kemudian cuma
             * pemutar yang kosong.
             */
            $tabel->string('rekaman_url', 500)->nullable()->after('group_wa');

            // Kapan rekamannya dipasang: dipakai memberi tahu peserta
            // "rekaman baru tersedia", dan untuk tahu angkatan mana yang
            // sudah selesai diurus.
            $tabel->timestamp('rekaman_dipasang_pada')->nullable()->after('rekaman_url');
        });

        Schema::table('users', function (Blueprint $tabel) {
            /*
             * Akun yang dibuatkan sistem lahir dengan sandi bawaan yang SAMA
             * untuk semua orang. Selama sandi itu belum diganti, nama orang
             * lain adalah kunci akun orang lain — sebab MIS masuk lewat
             * username, dan username peserta dirakit dari namanya.
             *
             * Penandanya disimpan di baris, bukan ditebak dari
             * Hash::check('Password123'): mencocokkan sandi bawaan berarti
             * menuliskan sandi itu di dalam kode yang memeriksanya, di
             * tempat yang tidak ada hubungannya dengan pembuatan akun.
             */
            $tabel->boolean('wajib_ganti_sandi')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_layanan', function (Blueprint $tabel) {
            $tabel->dropColumn(['rekaman_url', 'rekaman_dipasang_pada']);
        });

        Schema::table('users', function (Blueprint $tabel) {
            $tabel->dropColumn('wajib_ganti_sandi');
        });
    }
};
