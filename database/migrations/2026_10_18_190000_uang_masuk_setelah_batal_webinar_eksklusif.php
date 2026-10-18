<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dua kolom untuk satu keadaan yang selama ini tidak punya tempat:
 * UANG MASUK SESUDAH PENDAFTARANNYA DIBATALKAN.
 *
 * Membatalkan di layar panitia TIDAK memberi tahu DOKU apa pun — tagihannya
 * di sana tetap hidup sampai 60 menitnya habis. Pendaftar yang tabnya masih
 * terbuka bisa tetap membayar, dan sampai 18 Okt 2026 pembayaran itu
 * menghidupkan kembali barisnya jadi 'paid' tanpa mengambil kursinya lagi:
 * terukur di uji — status cancel → paid, sisa kuota TIDAK berkurang, dan
 * surat "Anda sudah lunas" berangkat ke orang yang pendaftarannya sudah
 * dibatalkan panitia.
 *
 *   perlu_dikembalikan_pada
 *     Diisi kalau uangnya masuk tetapi kursinya SUDAH TIDAK ADA. Barisnya
 *     tetap batal, dan angka ini yang membuat panitia tahu ada uang yang
 *     harus dikembalikan. Waktunya disimpan, bukan benar/salah: saat
 *     orangnya menagih, yang dicari jam pastinya.
 *
 *   bayar_permintaan_id
 *     Request-Id yang dipakai saat tagihannya dibuat. Selama ini dibuat lalu
 *     DIBUANG — yang tersimpan di bayar_rujukan nomor fakturnya, bukan ini.
 *     Cancel Order API DOKU mewajibkannya (payment.original_request_id),
 *     jadi tanpa kolom ini tagihannya tidak akan pernah bisa ditutup dari
 *     sisi kita. Diisi mulai sekarang; pendaftaran yang sudah ada tetap
 *     tidak bisa, dan itu memang tidak bisa diperbaiki surut.
 */
return new class extends Migration
{
    private const TABEL = 'webinar_eksklusif_pendaftaran';

    public function up(): void
    {
        Schema::table(self::TABEL, function (Blueprint $t) {
            if (! Schema::hasColumn(self::TABEL, 'perlu_dikembalikan_pada')) {
                $t->timestamp('perlu_dikembalikan_pada')->nullable()->after('bayar_pada');
            }

            if (! Schema::hasColumn(self::TABEL, 'bayar_permintaan_id')) {
                $t->string('bayar_permintaan_id', 128)->nullable()->after('bayar_rujukan');
            }
        });
    }

    public function down(): void
    {
        Schema::table(self::TABEL, function (Blueprint $t) {
            foreach (['perlu_dikembalikan_pada', 'bayar_permintaan_id'] as $kolom) {
                if (Schema::hasColumn(self::TABEL, $kolom)) {
                    $t->dropColumn($kolom);
                }
            }
        });
    }
};
