<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promo untuk layanan apa pun, menggantikan promo yang cuma milik Clinik Scopus.
 *
 * Sebelum ini promo ada di DUA tempat yang tidak saling tahu: tabel
 * `clinikscopus_promo` (3 baris, semuanya sudah lewat) yang hanya melayani
 * Clinik Scopus, dan kolom diskon yang menempel di tiap baris
 * `kategori_layanan` (18 angkatan memakainya). Yang kedua tidak bisa dipakai
 * berulang — promo "Ulang Tahun Rumah Scopus" yang berlaku untuk enam
 * angkatan harus diketik enam kali, dan menghentikannya berarti menyunting
 * enam baris satu per satu.
 *
 * Sasarannya ANGKATAN, dicentang satu per satu lewat tabel sambungan.
 * Diminta begitu: tidak semua angkatan dapat promo, jadi "seluruh layanan
 * kena" justru bentuk yang salah.
 *
 * `tipe` varchar, BUKAN enum. Kolom enum di MySQL menolak nilai yang belum
 * terdaftar dengan galat di peladen saja — sementara uji berjalan di SQLite
 * yang menerima apa pun, jadi tipe promo baru akan selalu hijau di sini dan
 * selalu merah di sana.
 *
 * Tanggal disimpan sebagai `date`, bukan `datetime`. Promo lama memakai
 * datetime dan hasilnya tanggal seperti "2026-02-14 20:56:00" — jam yang
 * tidak pernah dimaksudkan siapa pun, cuma jejak saat borangnya dibuka.
 * Harinya yang berarti, dan `selesai` dihitung sampai habis hari itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_layanan', function (Blueprint $t) {
            $t->char('id', 36)->primary();
            $t->string('nama', 120);
            $t->string('tipe', 10)->default('persen');
            $t->unsignedTinyInteger('persen')->nullable();
            $t->unsignedInteger('nominal')->nullable();

            /*
             * Batas potongan untuk promo persen, boleh kosong.
             *
             * Promo 40% pada Scopus Camp Rp 6.500.000 memotong Rp 2.600.000.
             * Tanpa batas, angka sebesar itu hanya ketahuan setelah ada yang
             * mendaftar — dan saat itu harganya sudah terkirim ke orangnya.
             */
            $t->unsignedInteger('batas_potongan')->nullable();

            $t->date('mulai');
            $t->date('selesai');

            /*
             * Saklar tangan, terpisah dari tanggalnya.
             *
             * Keadaan yang dibaca orang (belum mulai / berjalan / selesai)
             * dihitung dari tanggalnya saat DIBACA, bukan disimpan dan bukan
             * dinaikkan penjadwal: cron di hosting ini belum pernah dipasang,
             * jadi status yang bergantung penjadwal akan diam-diam tertinggal.
             * Kolom ini cuma untuk mematikan promo lebih awal.
             */
            $t->boolean('aktif')->default(true);

            $t->text('catatan')->nullable();
            $t->unsignedBigInteger('penginput_id')->nullable();
            $t->timestamps();

            // Layar daftarnya menyaring keadaan dan mengurut tanggal mulai.
            $t->index(['aktif', 'mulai', 'selesai'], 'promo_keadaan');
            $t->index('nama', 'promo_nama');
        });

        Schema::create('promo_layanan_angkatan', function (Blueprint $t) {
            $t->id();
            $t->char('promo_id', 36);
            $t->char('kategori_id', 36);
            $t->timestamps();

            /*
             * Satu angkatan tidak boleh tercatat dua kali pada satu promo.
             * Tanpa ini, menyimpan borang dua kali melipatgandakan barisnya
             * dan penghitung "berlaku untuk N angkatan" ikut berlipat.
             */
            $t->unique(['promo_id', 'kategori_id'], 'promo_angkatan_sekali');
            $t->index('kategori_id', 'promo_angkatan_kategori');

            $t->foreign('promo_id', 'promo_angkatan_promo')
                ->references('id')->on('promo_layanan')->cascadeOnDelete();

            /*
             * Ikut terhapus bersama angkatannya. Dibiarkan menggantung,
             * penghitung promo menyebut angkatan yang sudah tidak ada dan
             * layar rinciannya menampilkan baris kosong tanpa nama.
             */
            $t->foreign('kategori_id', 'promo_angkatan_kategori_fk')
                ->references('id')->on('kategori_layanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_layanan_angkatan');
        Schema::dropIfExists('promo_layanan');
    }
};
