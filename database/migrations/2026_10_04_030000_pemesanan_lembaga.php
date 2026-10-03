<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan atas nama LEMBAGA, yang mengikat beberapa pendaftaran jadi satu.
 *
 * Kuota tiap angkatan 20 kursi (terukur: Scopus Camp dan Bibliometrik
 * keduanya 20), sedangkan lembaga rutin memesan lebih dari itu — rombongan
 * terbesar yang pernah ada 37 orang. Pesanan sebesar itu TERPAKSA dipecah ke
 * beberapa angkatan, dan sebelum ini hasil pecahannya tidak saling tahu
 * bahwa mereka satu pesanan: merekap dan menagihnya berarti mengumpulkan
 * barisnya satu per satu dari ingatan.
 *
 * Mengikat, BUKAN menggantikan: tiap pendaftaran tetap berdiri sendiri
 * dengan nomor, kuota, dan kode uniknya masing-masing. Yang ditambahkan
 * hanya tali yang menghubungkannya — jadi logika kuota dan kode unik yang
 * sudah terbukti tidak perlu dibongkar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pemesanan_lembaga')) {
            Schema::create('pemesanan_lembaga', function (Blueprint $t) {
                $t->char('id', 36)->primary();

                // Nomor yang disebut saat menagih; dibuat sistem.
                $t->string('kode', 30)->unique();

                $t->string('nama_lembaga');

                /*
                 * Alamat, NPWP, dan nomor PO: tiganya yang dituntut lembaga
                 * untuk pencairan, dan tidak satu pun punya tempat sebelum
                 * ini — yang ada cuma `affiliasi`, satu kolom teks bebas.
                 */
                $t->text('alamat')->nullable();
                $t->string('npwp', 40)->nullable();
                $t->string('no_po', 60)->nullable();

                // PIC-nya disimpan terpisah dari pendaftarnya: yang mengurus
                // administrasi sering bukan yang ikut acaranya.
                $t->string('pic_nama');
                $t->string('pic_email')->nullable();
                $t->string('pic_telp', 40)->nullable();

                $t->text('catatan')->nullable();
                $t->string('dibuat_oleh')->nullable();

                /*
                 * Pesanan yang sudah selesai ditagih tidak perlu muncul lagi
                 * di borang pendaftaran. Varchar, bukan enum: menambah
                 * keadaan baru lewat enum menuntut migrasi ALTER.
                 */
                $t->string('status', 20)->default('terbuka')->index();

                $t->timestamps();
            });
        }

        if (Schema::hasTable('pemesanan_lembaga_baris')) {
            return;
        }

        Schema::create('pemesanan_lembaga_baris', function (Blueprint $t) {
            $t->char('id', 36)->primary();
            $t->char('pemesanan_id', 36)->index();

            /*
             * Menunjuk salah satu dari LIMA tabel pendaftaran, ditentukan
             * kolom `layanan` — jadi tidak bisa dinyatakan sebagai kunci
             * asing. Pembersihannya ikut HapusPendaftaran, sama seperti
             * peserta rombongan.
             */
            $t->string('layanan', 40);
            $t->char('pendaftaran_id', 36);

            $t->timestamps();

            // Satu pendaftaran hanya boleh terikat ke satu pesanan.
            $t->unique(['layanan', 'pendaftaran_id'], 'baris_sekali');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_lembaga_baris');
        Schema::dropIfExists('pemesanan_lembaga');
    }
};
