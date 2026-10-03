<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nama peserta lain dalam satu pendaftaran rombongan.
 *
 * Kelima tabel pendaftaran menyimpan SATU nama, satu email, satu nomor —
 * sementara `jumlah_pendaftar` bisa lebih dari satu. Terukur: 6 baris sudah
 * berjumlah lebih dari satu, dan nama peserta selain pemesannya tidak ada di
 * mana pun. Akibatnya daftar hadir untuk rombongan tidak bisa dibuat dari
 * sistem.
 *
 * Satu tabel untuk SEMUA layanan, dikunci pasangan (layanan, pendaftaran_id),
 * bukan satu tabel per layanan: kelima tabel pendaftarannya memakai kunci
 * utama char(36) dan tidak ada satu pun relasi yang bisa dibagi, jadi tabel
 * terpisah berarti lima tabel kembar yang harus diubah bersamaan selamanya.
 *
 * Tanpa foreign key, dan itu disengaja: sasarannya lima tabel berbeda
 * tergantung kolom `layanan`, dan MySQL tidak bisa menyatakan kendala
 * seperti itu. Pembersihannya ikut HapusPendaftaran.
 *
 * Menggantikan `webinar_eksklusif_peserta`, yang isinya NOL baris dan tidak
 * pernah ditulis dari mana pun — satu-satunya pemakainya sebuah relasi yang
 * hanya dibaca layar rincian. Dua tabel untuk satu hal yang sama berarti dua
 * tempat yang harus diubah bersamaan selamanya, jadi relasi itu diarahkan ke
 * sini. Tabel lamanya dibiarkan apa adanya, bukan dibuang: membuang tabel
 * bukan keputusan yang pantas diselipkan ke dalam penambahan fitur.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pendaftaran_peserta')) {
            return;
        }

        Schema::create('pendaftaran_peserta', function (Blueprint $t) {
            $t->char('id', 36)->primary();
            $t->string('layanan', 40);
            $t->char('pendaftaran_id', 36);

            /*
             * Urutan seperti yang diketik, BUKAN created_at: beberapa baris
             * lahir di detik yang sama, dan daftar hadir yang urutannya
             * berubah-ubah setiap dibuka tidak bisa dipakai memanggil nama.
             */
            $t->unsignedSmallInteger('urutan')->default(0);

            $t->string('nama');
            // Email dan nomor boleh kosong: yang dicatat panitia saat
            // rombongan datang hampir selalu hanya namanya.
            $t->string('email')->nullable();
            $t->string('telp', 40)->nullable();

            $t->timestamps();

            // Satu-satunya cara barisnya dicari: "siapa saja di pendaftaran
            // ini".
            $t->index(['layanan', 'pendaftaran_id'], 'peserta_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_peserta');
    }
};
