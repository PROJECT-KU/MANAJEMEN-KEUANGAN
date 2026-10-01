<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galeri foto per layanan, boleh dikhususkan ke satu angkatan.
 *
 * Dibuat untuk Webinar Eksklusif lebih dulu — itu yang mendesak — tetapi
 * bentuknya SENGAJA tidak mengunci layanan itu saja: kolom `layanan` memakai
 * kunci yang sama dengan katalog, jadi Scopus Camp dan Bibliometrik tinggal
 * memakainya tanpa migrasi lagi.
 *
 * `kategori_id` boleh NULL, dan itu yang membuatnya berguna:
 *
 *   - NULL      -> foto UMUM layanan itu; tampil di semua sesinya
 *   - terisi    -> dokumentasi satu sesi tertentu
 *
 * Tanpa jalur NULL, sesi yang baru dijadwalkan galerinya kosong sampai ada
 * yang mengunggah ulang — padahal foto suasana sesi sebelumnya justru yang
 * paling meyakinkan calon peserta.
 *
 * Berkasnya sendiri TIDAK disimpan di tabel ini maupun di `public/`:
 * `berkas` memuat jalur di storage, dan isinya selalu WebP (lihat
 * App\Services\Gambar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri_layanan', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->string('layanan', 60)->index();

            // Tanpa kunci asing: angkatannya boleh dihapus sementara fotonya
            // tetap berguna sebagai dokumentasi umum layanan itu. Yang
            // membersihkan tautan yatim adalah pengendalinya, bukan basis data.
            $t->uuid('kategori_id')->nullable()->index();

            // Jalur WebP di cakram `public`, mis. "galeri/webinar_eksklusif/3f2a.webp".
            $t->string('berkas');

            // Teks pengganti gambar. Wajib ada isinya di layar, tetapi boleh
            // NULL di sini supaya unggahan massal tidak tertahan.
            $t->string('keterangan')->nullable();

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
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_layanan');
    }
};
