<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyatukan kategori (angkatan) semua layanan jadi satu tabel.
 *
 * Sebelum ini ada dua tabel yang isinya sama persis: `scopus_camp_kategori`
 * (23 kolom, 48 baris) dan `categories_analisis_bibliometrik` (21 kolom, 10
 * baris). Ke-21 kolom yang kedua adalah himpunan bagian dari yang pertama;
 * bedanya cuma `lokasi` dan `best_price`, dan keduanya boleh NULL.
 *
 * Dua tabel untuk satu bentuk berarti dua pengendali, dua layar, dan dua
 * tempat yang harus diubah setiap kali aturannya berubah. Scopus Kafe, Online
 * Training, dan Clinik Scopus yang menyusul akan menambah dua-dua lagi.
 *
 * Tabelnya DIGANTI NAMA, bukan dibuat baru lalu diisi. InnoDB ikut memindahkan
 * kunci asing yang menunjuk tabel itu, jadi 80 baris `scopus_camp_pendaftaran`
 * tidak perlu disentuh sama sekali. Beda dengan tarif layanan, di sini nama
 * lamanya memang diganti: "scopus_camp_kategori" yang berisi bibliometrik akan
 * menyesatkan setiap kali dibaca.
 *
 * Baris bibliometrik dipindah DENGAN id aslinya, supaya 92 baris
 * `analisis_bibliometrik` yang menunjuknya tetap cocok tanpa dipetakan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('scopus_camp_kategori', 'kategori_layanan');

        Schema::table('kategori_layanan', function (Blueprint $t) {
            // Bawaan 'scopus_camp' supaya 48 baris yang sudah ada langsung
            // benar tanpa perlu diperbarui satu per satu.
            $t->string('layanan', 40)->default('scopus_camp')->index()->after('token');
            $t->string('varian', 40)->nullable()->after('layanan');
        });

        $kolom = Schema::getColumnListing('categories_analisis_bibliometrik');

        DB::table('categories_analisis_bibliometrik')->orderBy('id')->chunk(100, function ($baris) use ($kolom) {
            $isi = [];

            foreach ($baris as $b) {
                $satu = ['layanan' => 'bibliometrik', 'varian' => null];

                foreach ($kolom as $k) {
                    $satu[$k] = $b->$k;
                }

                $isi[] = $satu;
            }

            DB::table('kategori_layanan')->insert($isi);
        });

        /*
         * Nama kolom kunci asingnya ikut dibereskan. Dibiarkan,
         * `analisis_bibliometrik.categories_analisis_bibliometrik_id` menunjuk
         * tabel yang namanya bukan itu lagi — persis jenis keterangan salah
         * yang bikin orang berikutnya salah menebak.
         *
         * Kunci asingnya dibuang dulu: MySQL menolak mengganti nama kolom yang
         * masih dipakai constraint.
         */
        Schema::table('analisis_bibliometrik', function (Blueprint $t) {
            $t->dropForeign('fk_cat_analisis_bib');
            $t->renameColumn('categories_analisis_bibliometrik_id', 'kategori_id');
        });

        Schema::table('analisis_bibliometrik', function (Blueprint $t) {
            $t->foreign('kategori_id', 'fk_bibliometrik_kategori')
                ->references('id')->on('kategori_layanan');
        });

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            $t->dropForeign('scopus_camp_pendaftaran_scopus_camp_kategori_id_foreign');
            $t->renameColumn('scopus_camp_kategori_id', 'kategori_id');
        });

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            $t->foreign('kategori_id', 'fk_camp_kategori')
                ->references('id')->on('kategori_layanan');
        });

        Schema::drop('categories_analisis_bibliometrik');
    }

    public function down(): void
    {
        Schema::create('categories_analisis_bibliometrik', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('token')->nullable();
            $t->string('nama')->nullable();
            $t->string('nama_ke')->nullable();
            $t->dateTime('mulai')->nullable();
            $t->dateTime('selesai')->nullable();
            $t->string('total_kuota')->nullable();
            $t->string('sisa_kuota')->nullable();
            $t->longText('desc')->nullable();
            $t->string('biaya')->nullable();
            $t->string('ppn')->nullable();
            $t->string('tipe_diskon')->nullable();
            $t->string('diskon_persentase')->nullable();
            $t->string('nominal_diskon')->nullable();
            $t->string('kode_diskon')->nullable();
            $t->string('total_biaya')->nullable();
            $t->string('status')->nullable();
            $t->string('group_wa')->nullable();
            $t->string('gambar')->nullable();
            $t->timestamps();
        });

        $kolom = Schema::getColumnListing('categories_analisis_bibliometrik');

        DB::table('kategori_layanan')->where('layanan', 'bibliometrik')
            ->orderBy('id')->chunk(100, function ($baris) use ($kolom) {
                $isi = [];

                foreach ($baris as $b) {
                    $satu = [];

                    foreach ($kolom as $k) {
                        $satu[$k] = $b->$k ?? null;
                    }

                    $isi[] = $satu;
                }

                DB::table('categories_analisis_bibliometrik')->insert($isi);
            });

        DB::table('kategori_layanan')->where('layanan', 'bibliometrik')->delete();

        Schema::table('analisis_bibliometrik', function (Blueprint $t) {
            $t->dropForeign('fk_bibliometrik_kategori');
            $t->renameColumn('kategori_id', 'categories_analisis_bibliometrik_id');
        });

        Schema::table('analisis_bibliometrik', function (Blueprint $t) {
            $t->foreign('categories_analisis_bibliometrik_id', 'fk_cat_analisis_bib')
                ->references('id')->on('categories_analisis_bibliometrik');
        });

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            $t->dropForeign('fk_camp_kategori');
            $t->renameColumn('kategori_id', 'scopus_camp_kategori_id');
        });

        Schema::table('kategori_layanan', function (Blueprint $t) {
            $t->dropIndex(['layanan']);
            $t->dropColumn(['layanan', 'varian']);
        });

        Schema::rename('kategori_layanan', 'scopus_camp_kategori');

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            $t->foreign('scopus_camp_kategori_id', 'scopus_camp_pendaftaran_scopus_camp_kategori_id_foreign')
                ->references('id')->on('scopus_camp_kategori');
        });
    }
};
