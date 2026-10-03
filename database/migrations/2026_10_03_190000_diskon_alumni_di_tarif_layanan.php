<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Potongan alumni, disetel sekali per layanan di Tarif Layanan.
 *
 * Diminta pemilik: alumni mendapat potongan khusus, dan potongan itu TIDAK
 * BOLEH digabung dengan promo lain.
 *
 * Disimpan sebagai PERSENTASE, bukan nominal rupiah, dan itu bukan demi
 * kesederhanaan. Tarif disetel per LAYANAN sementara harganya berbeda-beda
 * per angkatan — terukur, Scopus Camp Yogyakarta Rp 4.500.000 dan Padang
 * Rp 5.500.000. Potongan rupiah tetap akan berarti persentase yang berbeda
 * di tiap angkatan, dan "alumni dapat potongan 10%" adalah janji yang bisa
 * dipegang sementara "alumni dapat Rp 450.000" berubah artinya tiap kali
 * harganya naik.
 *
 * AMAN dideploy kapan saja: menambah kolom yang boleh kosong, tidak menyentuh
 * satu baris pun yang sudah ada. Selama belum diisi, tidak ada potongan
 * alumni yang ditawarkan di mana pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('clinikscopus_biaya_persesi', 'diskon_alumni_persen')) {
            return;
        }

        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $tabel) {
            $tabel->unsignedTinyInteger('diskon_alumni_persen')->nullable()->after('ppn');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('clinikscopus_biaya_persesi', 'diskon_alumni_persen')) {
            return;
        }

        Schema::table('clinikscopus_biaya_persesi', function (Blueprint $tabel) {
            $tabel->dropColumn('diskon_alumni_persen');
        });
    }
};
