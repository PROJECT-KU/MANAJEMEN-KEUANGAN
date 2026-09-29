<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memisahkan PERAN dari JABATAN.
 *
 * Kolom `level` selama ini merangkap dua hal yang berbeda: tingkat akses
 * (siapa boleh membuka apa) dan jabatan (apa posisi orangnya). Nilainya
 * bercampur — 'admin' dan 'user' itu tingkat akses, sedangkan 'manager',
 * 'staff', 'trainer' dan 'ceo' itu jabatan. Akibatnya menambah jabatan baru
 * berarti menyentuh kontrol akses, dan sebaliknya.
 *
 * Sesudah migrasi ini:
 *   - `peran`   hanya tiga nilai: administrator, karyawan, user
 *   - `jobdesk` menampung jabatannya, seperti sebelumnya
 *
 * `level` SENGAJA tidak dihapus di sini. Masih ada ratusan pemeriksaan yang
 * membacanya, dan menghapus kolomnya lebih dulu berarti seluruh aplikasi mati
 * sebelum sempat diperbaiki. Ia dibuang di migrasi terpisah setelah semua
 * pemeriksaan pindah ke `peran`.
 */
return new class extends Migration
{
    /** Peta yang sudah disepakati. */
    private const PETA_PERAN = [
        'manager'  => 'administrator',
        'ceo'      => 'karyawan',
        'admin'    => 'karyawan',
        'staff'    => 'karyawan',
        'trainer'  => 'karyawan',
        'karyawan' => 'karyawan',
        'user'     => 'user',
    ];

    /**
     * Level lama yang sebenarnya jabatan; disalin ke jobdesk supaya
     * keterangannya tidak hilang saat perannya diseragamkan.
     */
    private const JADI_JABATAN = ['manager', 'ceo', 'staff', 'trainer'];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $tabel) {
            // string, bukan enum: menambah peran baru lewat enum menuntut
            // ALTER TABLE di MySQL, dan tes berjalan di atas SQLite yang
            // tidak mengenal enum sama sekali.
            $tabel->string('peran', 20)->default('user')->after('level')->index();
        });

        foreach (self::PETA_PERAN as $level => $peran) {
            DB::table('users')->where('level', $level)->update(['peran' => $peran]);
        }

        // Level yang tidak terdaftar di peta pun harus punya peran yang sah;
        // 'user' adalah yang paling sedikit haknya, jadi itu yang dipakai.
        DB::table('users')
            ->whereNotIn('level', array_keys(self::PETA_PERAN))
            ->orWhereNull('level')
            ->update(['peran' => 'user']);

        // Jabatan lama diselamatkan HANYA kalau jobdesk-nya masih kosong —
        // yang sudah diisi orangnya lebih tepat daripada tebakan dari level.
        foreach (self::JADI_JABATAN as $jabatan) {
            DB::table('users')
                ->where('level', $jabatan)
                ->where(fn ($q) => $q->whereNull('jobdesk')->orWhere('jobdesk', ''))
                ->update(['jobdesk' => strtoupper($jabatan)]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $tabel) {
            $tabel->dropIndex(['peran']);
            $tabel->dropColumn('peran');
        });
    }
};
