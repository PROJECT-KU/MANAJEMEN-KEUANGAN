<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor tagihan yang dipakai di gerbang pembayaran, terpisah dari nomor
 * pendaftarannya.
 *
 * ---------------------------------------------------------------------------
 * Kenapa perlu kolom sendiri
 * ---------------------------------------------------------------------------
 *
 * Sampai sekarang nomor tagihan di DOKU SAMA PERSIS dengan nomor
 * pendaftarannya. Akibatnya satu pendaftaran hanya boleh punya satu tagihan
 * seumur hidupnya: DOKU menolak nomor yang sudah pernah dipakai dengan
 * "INVOICE ALREADY USED" — terukur 24 Okt 2026 pada CAMP-20261024-0001, yang
 * sejak itu tidak bisa dibayar sama sekali.
 *
 * Yang kena bukan kasus langka, melainkan yang paling sering terjadi:
 * pembayaran yang gagal di bank, dan tagihan yang tenggatnya lewat. Keduanya
 * membuat orangnya harus menghubungi panitia supaya dibuatkan ulang DENGAN
 * TANGAN, dan sampai itu terjadi ia memegang nomor yang tombol bayarnya
 * mengantar ke halaman galat DOKU.
 *
 * ---------------------------------------------------------------------------
 * Kenapa kolom, bukan menambahkan akhiran lalu dipotong saat dibaca
 * ---------------------------------------------------------------------------
 *
 * Percobaan berikutnya memakai nomor yang sama berakhiran '-R2'. Mencarinya
 * kembali saat DOKU memberi tahu berarti memotong akhiran itu dari untaian —
 * dan nomor pendaftaran kita SENDIRI berakhir dengan tanda hubung dan angka
 * (CAMP-20261024-0001). Pemotong yang membuang '-<angka>' di ujung akan
 * mengubahnya jadi CAMP-20261024, lalu pemberitahuan pembayaran tidak
 * menemukan barisnya dan uang yang sudah masuk tidak tercatat.
 *
 * Disimpan apa adanya di kolomnya sendiri, tidak ada yang perlu dipotong:
 * pencariannya `where id_transaksi = ? or bayar_nomor = ?`.
 *
 * Boleh kosong, dan itu keadaan yang BENAR untuk hampir semua baris: 153
 * pendaftaran lama dibayar lewat transfer manual, dan tagihan percobaan
 * pertama memang memakai nomor pendaftarannya sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_nomor')) {
                $t->string('bayar_nomor', 64)->nullable()->after('bayar_rujukan');
            }
        });

        /*
         * Indeksnya DIPERIKSA dulu keberadaannya.
         *
         * Migrasi ini dijalankan di basis data yang kolomnya mungkin sudah
         * ditambahkan dengan tangan, dan Schema::table yang menambahkan
         * indeks bernama sama melempar "Duplicate key name" — yang
         * menggagalkan seluruh migrasinya, bukan hanya baris ini.
         */
        $ada = collect(Schema::getIndexes('scopus_camp_pendaftaran'))
            ->pluck('name')
            ->contains('scopus_camp_pendaftaran_bayar_nomor_index');

        if (! $ada) {
            Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
                $t->index('bayar_nomor');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_nomor')) {
            return;
        }

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            $t->dropIndex('scopus_camp_pendaftaran_bayar_nomor_index');
            $t->dropColumn('bayar_nomor');
        });
    }
};
