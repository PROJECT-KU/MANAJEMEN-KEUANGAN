<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom catatan untuk Scopus Kafe dan Clinik Scopus.
 *
 * Tiga dari lima tabel pendaftaran punya kolom `note`; kedua tabel ini tidak.
 * Akibatnya siapa pun yang memindahkan status pendaftaran di sana TIDAK
 * meninggalkan jejak apa pun — terukur 11 dari 187 baris tidak terlacak,
 * sementara 176 lainnya mencatat siapa yang mengubah dan dari status apa ke
 * status apa.
 *
 * Itu bukan perkara kerapian: kalau belakangan ada selisih uang pada
 * pemesanan Clinik Scopus, tidak ada satu pun keterangan tentang siapa yang
 * menandainya lunas dan kapan.
 *
 * AMAN dideploy kapan saja: menambah kolom yang boleh kosong, tidak menyentuh
 * satu baris pun yang sudah ada, dan kodenya tetap berjalan tanpa migrasi ini
 * — katalog `kolom_catatan` yang menentukan ditulis atau tidak. Tetapi selama
 * belum dijalankan, jejaknya memang tidak tercatat untuk kedua layanan itu.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tabel = ['pendaftaran_scopus_kafe', 'clinikscopus_pemesanan'];

    public function up(): void
    {
        foreach ($this->tabel as $nama) {
            if (Schema::hasColumn($nama, 'note')) {
                continue;
            }

            Schema::table($nama, function (Blueprint $tabel) {
                $tabel->text('note')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $nama) {
            if (! Schema::hasColumn($nama, 'note')) {
                continue;
            }

            Schema::table($nama, function (Blueprint $tabel) {
                $tabel->dropColumn('note');
            });
        }
    }
};
