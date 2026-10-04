<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesi beserta jamnya, disetel di Tarif Layanan.
 *
 * Jam Scopus Kafe sebelumnya ditulis di kode — offline dua sesi, online satu —
 * jadi mengubah jamnya menuntut deploy. Padahal yang tahu jamnya berubah
 * adalah panitia, bukan yang memegang kodenya.
 *
 * Ditaruh di TARIF, bukan di tabel `layanan`, sebab daftarnya memang
 * bergantung pada varian: online dan offline punya jam yang berbeda, dan
 * pasangan (layanan, varian) persis kunci baris tarif. Di tabel `layanan`,
 * variannya cuma daftar nama tanpa tempat menggantungkan jam.
 *
 * JSON, bukan tabel tersendiri: isinya paling banyak dua-tiga baris, selalu
 * dibaca utuh bersama tarifnya, dan tidak pernah dicari sendiri. Bentuknya
 * sama dengan `fasilitas` dan `kegiatan` yang sudah ada di tabel ini.
 *
 * Boleh kosong, dan kosong BUKAN berarti tidak ada sesi: yang kosong jatuh
 * kembali ke daftar bawaan di PendaftaranSemuaLayanan::SESI. Tanpa itu, tarif
 * baru yang dibuat saat harganya naik akan menghapus jadwal sesi tanpa ada
 * yang meminta.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('clinikscopus_biaya_persesi', 'sesi')) {
            return;
        }

        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            $t->json('sesi')->nullable()->after('kegiatan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('clinikscopus_biaya_persesi', 'sesi')) {
            return;
        }

        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $t) {
            $t->dropColumn('sesi');
        });
    }
};
