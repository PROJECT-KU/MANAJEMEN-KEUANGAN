<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyeragamkan cara bayar di SEMUA layanan.
 *
 * Sampai sekarang hanya Webinar Eksklusif yang menyimpan `cara_bayar`; empat
 * layanan lainnya cuma punya kolom bukti (`gambar`), jadi satu-satunya cara
 * bayar yang bisa dicatat adalah transfer. Akibatnya pendaftar yang membayar
 * tunai di tempat tetap tercatat sebagai "menunggu bayar" tanpa bukti —
 * tidak bisa dibedakan dari yang memang belum membayar, dan ikut tertandai
 * menggantung setelah 7 hari.
 *
 * Nilainya varchar, bukan enum: MySQL menuntut migrasi ALTER untuk setiap
 * nilai enum baru, dan daftar cara bayarnya masih akan bertambah (DOKU, dan
 * kemungkinan kanal lain sesudahnya).
 *
 * Baris yang sudah ada diisi 'transfer'. Itu bukan terkaan: sebelum kolom ini
 * ada, transfer adalah satu-satunya jalur yang disediakan borangnya.
 */
return new class extends Migration
{
    /** Tabel yang belum punya kolomnya; webinar sengaja tidak ikut. */
    private const TABEL = [
        'scopus_camp_pendaftaran',
        'analisis_bibliometrik',
        'pendaftaran_scopus_kafe',
        'clinikscopus_pemesanan',
    ];

    public function up(): void
    {
        foreach (self::TABEL as $tabel) {
            if (! Schema::hasTable($tabel) || Schema::hasColumn($tabel, 'cara_bayar')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $t) {
                /*
                 * Diindeks karena layar Pendaftar Layanan menyaringnya, dan
                 * saringannya berjalan di atas UNION lima tabel — tanpa indeks
                 * setiap saringan memaksa pembacaan penuh kelima-limanya.
                 */
                $t->string('cara_bayar', 20)->default('transfer')->index();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABEL as $tabel) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, 'cara_bayar')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $t) {
                $t->dropIndex(['cara_bayar']);
                $t->dropColumn('cara_bayar');
            });
        }
    }
};
