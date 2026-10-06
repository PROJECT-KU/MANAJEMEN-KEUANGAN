<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom `medan` untuk jejak perubahan DATA, bukan hanya status.
 *
 * Jejaknya dulu cuma mencatat perpindahan status; semua suntingan lain —
 * angkatan, nama, email, nominal — tidak meninggalkan bekas sama sekali.
 *
 * Nama medannya tidak bisa dititipkan ke kolom `aksi`: kolom itu 20 huruf,
 * sementara `total_keseluruhan_pembayaran` saja 28. Dan tidak dititipkan ke
 * `ringkasan` sebagai kalimat jadi, sebab kalimatnya sengaja dirakit saat
 * ditampilkan — supaya bisa diperbaiki kapan saja tanpa menyentuh baris yang
 * sudah tersimpan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran_jejak', function (Blueprint $t) {
            $t->string('medan', 40)->nullable()->after('aksi');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_jejak', function (Blueprint $t) {
            $t->dropColumn('medan');
        });
    }
};
