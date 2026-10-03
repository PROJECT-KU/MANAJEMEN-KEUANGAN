<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nama tiap peserta dalam satu pendaftaran, dan penghubung ke akun.
 *
 * DUA MASALAH NYATA YANG DITUTUP SEKALIGUS.
 *
 * 1. Borangnya menerima sampai 50 peserta tetapi hanya meminta SATU nama.
 *    Di basis data ini sudah ada pendaftaran berisi 13 dan 37 orang, dengan
 *    satu nama masing-masing. Padahal yang dijanjikan "E-sertifikat resmi
 *    atas nama peserta" dan "Grup diskusi peserta" — untuk yang 37 itu, 36
 *    sertifikat tidak bisa diterbitkan dan 36 orang tidak bisa dimasukkan ke
 *    grup, sebab namanya memang tidak pernah ditanyakan.
 *
 * 2. Tidak satu pun tabel pendaftaran tertaut ke akun, bahkan untuk orang
 *    yang mendaftar dalam keadaan sudah masuk. Akibatnya riwayat pendaftaran
 *    tidak bisa ditampilkan di akunnya, dan admin tidak punya cara pasti
 *    mengenali pendaftar yang sama selain menebak dari email.
 *
 * Peserta pertama TIDAK disalin ke tabel ini: ia tetap di kolom nama/email
 * pendaftarannya, sebab dialah yang dihubungi dan yang membayar. Tabel ini
 * untuk peserta kedua dan seterusnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webinar_eksklusif_peserta', function (Blueprint $tabel) {
            $tabel->uuid('id')->primary();

            $tabel->uuid('pendaftaran_id');

            /*
             * Urutan ketiknya DISIMPAN, tidak diandalkan dari created_at.
             *
             * Beberapa peserta tersimpan dalam detik yang sama, jadi
             * mengurutkan dari stempel waktu memulangkan urutan yang berubah-
             * ubah — terbukti di uji: "Peserta Dua, Peserta Tiga" keluar
             * terbalik. Untuk sertifikat dan daftar grup, urutannya harus
             * sama dengan yang diketik pendaftarnya.
             */
            $tabel->unsignedSmallInteger('urutan')->default(0);

            $tabel->string('nama', 120);
            $tabel->string('email', 120)->nullable();

            $tabel->timestamps();

            /*
             * Dihapus bersama pendaftarannya: baris peserta tanpa pendaftaran
             * tidak berarti apa-apa, dan membiarkannya hanya menumpuk baris
             * yatim yang membingungkan saat merekap.
             */
            $tabel->foreign('pendaftaran_id')
                ->references('id')->on('webinar_eksklusif_pendaftaran')
                ->cascadeOnDelete();

            $tabel->index('pendaftaran_id');
        });

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            /*
             * Boleh kosong, dan akan sering kosong: sebagian besar pendaftar
             * datang dari iklan tanpa pernah masuk akun. Yang diisi hanya
             * kalau ia memang sedang masuk saat mendaftar.
             *
             * Tanpa kunci asing: akun yang dihapus tidak boleh menghapus
             * pendaftarannya — pendaftarannya catatan keuangan.
             */
            $tabel->unsignedBigInteger('user_id')->nullable()->after('kategori_id')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webinar_eksklusif_peserta');

        Schema::table('webinar_eksklusif_pendaftaran', function (Blueprint $tabel) {
            $tabel->dropColumn('user_id');
        });
    }
};
