<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galeri foto, dipakai bersama lintas layanan.
 *
 * Satu foto bisa dipakai BEBERAPA layanan sekaligus, atau semuanya. Itu sebab
 * layanannya berada di tabel sambungan, bukan sebagai satu kolom pada fotonya:
 * dokumentasi satu acara yang dihadiri peserta Scopus Camp dan Webinar
 * Eksklusif sama-sama relevan di kedua halaman, dan menyalin barisnya dua kali
 * berarti mengganti keterangannya harus dua kali juga.
 *
 * Tiga cara sebuah foto dipilih, dari yang paling luas:
 *
 *   semua_layanan = true   -> tampil di SEMUA layanan, apa pun katalognya.
 *                             Foto merek seperti suasana kantor atau wisuda.
 *   baris sambungan        -> tampil hanya di layanan yang disebut
 *   kategori_id terisi     -> dipersempit lagi ke SATU angkatan
 *
 * `kategori_id` boleh NULL, dan itu yang membuatnya berguna: foto umum tetap
 * tampil pada sesi yang baru dijadwalkan, yang galerinya belum punya apa-apa —
 * padahal foto suasana sesi sebelumnya justru yang paling meyakinkan calon
 * peserta.
 *
 * Berkasnya sendiri tidak disimpan di sini maupun di `public/`: `berkas` memuat
 * jalur di storage, dan isinya selalu WebP (lihat App\Services\Gambar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Jalur WebP di cakram `unggahan`, mis. "galeri/3f2a....webp".
            $t->string('berkas');

            // Teks pengganti gambar. Wajib ada isinya di layar, tetapi boleh
            // NULL di sini supaya unggahan massal tidak tertahan.
            $t->string('keterangan')->nullable();

            /*
             * Tampil di semua layanan sekaligus. Kolom tersendiri, bukan
             * "tidak punya baris sambungan": foto yang baru diunggah dan belum
             * sempat dipilihkan layanannya juga tidak punya baris sambungan,
             * dan keduanya tidak boleh berarti hal yang sama.
             */
            $t->boolean('semua_layanan')->default(false)->index();

            // Tanpa kunci asing: angkatannya boleh dihapus sementara fotonya
            // tetap berguna sebagai dokumentasi umum.
            $t->uuid('kategori_id')->nullable()->index();

            /*
             * Urutan tampil. Diisi tangan, bukan mengikuti tanggal unggah:
             * foto terbaik biasanya bukan yang terbaru, dan panitia ingin
             * menaruhnya di depan.
             */
            $t->unsignedInteger('urutan')->default(0);

            $t->boolean('aktif')->default(true)->index();

            $t->unsignedBigInteger('penginput_id')->nullable();
            $t->timestamps();
        });

        Schema::create('galeri_layanan', function (Blueprint $t) {
            $t->uuid('galeri_id');
            $t->string('layanan', 60);

            $t->foreign('galeri_id')->references('id')->on('galeri')->cascadeOnDelete();

            // Satu foto tidak boleh terdaftar dua kali di layanan yang sama;
            // tanpa ini, menyimpan borang dua kali menggandakan barisnya dan
            // fotonya muncul dobel di halaman publik.
            $t->primary(['galeri_id', 'layanan']);
            $t->index('layanan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_layanan');
        Schema::dropIfExists('galeri');
    }
};
