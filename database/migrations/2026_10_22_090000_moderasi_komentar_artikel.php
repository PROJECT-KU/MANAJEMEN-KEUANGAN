<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moderasi komentar blog.
 *
 * Sampai 22 Okt 2026 komentar publik langsung tampil: tabelnya tidak punya
 * kolom status sama sekali, pengendalinya tanpa validasi, rutenya tanpa
 * batas laju, dan TIDAK ADA satu pun layar untuk menghapusnya — rute
 * komentar yang ada di seluruh aplikasi cuma POST publiknya.
 *
 * Bawaannya 'tertahan': komentar baru menunggu dilihat orang dalam. Itu
 * pilihan yang disengaja — pintu yang terbuka lebih mudah ditutup daripada
 * halaman yang terlanjur penuh tautan judi.
 *
 * Komentar YANG SUDAH ADA ditandai 'terbit', bukan 'tertahan': semuanya
 * sudah tampil di halaman selama ini, dan menahannya mendadak berarti
 * menghilangkan percakapan yang sudah dibaca orang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel_komentar', function (Blueprint $tabel) {
            $tabel->string('status', 20)->default('tertahan')->after('komentar');

            // Dipakai saringan daftar publik di tiap halaman artikel dan
            // saringan "perlu dicek" di layar moderasi.
            $tabel->index(['status', 'artikel_id'], 'artikel_komentar_status_artikel_idx');

            /*
             * user_id boleh NULL — komentar tamu memang tidak punya pemilik.
             *
             * Kolomnya dulu NOT NULL dan borangnya selalu mengirim
             * $artikel->user_id, yaitu id PENULIS artikelnya. Jadi setiap
             * komentar tamu tersimpan atas nama penulis tulisan itu, dan
             * batasan NOT NULL-nya tidak pernah terasa karena tidak ada
             * kiriman yang jujur mengaku tanpa pemilik.
             */
            $tabel->unsignedBigInteger('user_id')->nullable()->change();

            /*
             * categories_artikel_id juga boleh NULL.
             *
             * Isinya DITURUNKAN dari tulisan yang dikomentari, bukan diisi
             * pengirimnya — dan tulisan boleh tidak berkategori. Dengan
             * NOT NULL, komentar pada tulisan semacam itu gagal tersimpan
             * dengan galat basis data, bukan dengan pesan yang bisa dibaca
             * orang. Tidak pernah terasa sejauh ini karena kebetulan semua
             * tulisan yang ada punya kategori.
             */
            $tabel->unsignedBigInteger('categories_artikel_id')->nullable()->change();
        });

        DB::table('artikel_komentar')->update(['status' => 'terbit']);
    }

    public function down(): void
    {
        Schema::table('artikel_komentar', function (Blueprint $tabel) {
            $tabel->dropIndex('artikel_komentar_status_artikel_idx');
            $tabel->dropColumn('status');
        });
    }
};
