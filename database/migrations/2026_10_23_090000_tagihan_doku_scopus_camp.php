<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tagihan gerbang pembayaran untuk Scopus Camp.
 *
 * Sampai sekarang satu-satunya cara bayarnya transfer manual: pendaftar
 * menyalin nomor rekening, pindah ke aplikasi banknya, memotret bukti, lalu
 * kembali mengunggahnya — dan panitia membuka gambarnya satu per satu untuk
 * memutuskan. Terukur di basis data ini, enam bukti menunggu diperiksa
 * selama 177, 127, 74, 28, 17, dan 6 hari; orang-orang itu sudah mengirim
 * uangnya dan masih tercatat belum bayar.
 *
 * Kolomnya MENYALIN nama yang sudah dipakai Webinar Eksklusif — bayar_*
 * dengan arti yang sama persis — bukan nama baru. Layar Pendaftar Layanan
 * membaca kelima layanan lewat satu UNION, dan nama yang berbeda untuk hal
 * yang sama berarti satu cabang lagi di setiap tempat yang membacanya.
 *
 * Semuanya boleh kosong: 153 pendaftaran yang sudah ada dibayar lewat
 * transfer manual dan memang tidak punya tagihan di gerbang mana pun.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>  nama kolom => kolom yang didahuluinya
     */
    private const KOLOM = [
        'bayar_rujukan' => 'cara_bayar',
        'bayar_permintaan_id' => 'bayar_rujukan',
        'bayar_url' => 'bayar_permintaan_id',
        'bayar_status' => 'bayar_url',
        'bayar_kanal' => 'bayar_status',
        'bayar_pada' => 'bayar_kanal',
        'kedaluwarsa_pada' => 'bayar_pada',
    ];

    public function up(): void
    {
        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) {
            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_rujukan')) {
                $t->string('bayar_rujukan', 100)->nullable()->after('cara_bayar');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_permintaan_id')) {
                // Diwajibkan Cancel Order API DOKU sebagai original_request_id;
                // nomor faktur saja tidak cukup untuk menutup tagihan.
                $t->string('bayar_permintaan_id', 64)->nullable()->after('bayar_rujukan');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_url')) {
                // 512: alamat checkout membawa penanda sesi yang panjang.
                $t->string('bayar_url', 512)->nullable()->after('bayar_permintaan_id');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_status')) {
                $t->string('bayar_status', 40)->nullable()->after('bayar_url');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_kanal')) {
                $t->string('bayar_kanal', 80)->nullable()->after('bayar_status');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'bayar_pada')) {
                $t->timestamp('bayar_pada')->nullable()->after('bayar_kanal');
            }

            if (! Schema::hasColumn('scopus_camp_pendaftaran', 'kedaluwarsa_pada')) {
                $t->timestamp('kedaluwarsa_pada')->nullable()->after('bayar_pada');
            }
        });
    }

    public function down(): void
    {
        $ada = array_values(array_filter(
            array_keys(self::KOLOM),
            fn ($k) => Schema::hasColumn('scopus_camp_pendaftaran', $k)
        ));

        if ($ada === []) {
            return;
        }

        Schema::table('scopus_camp_pendaftaran', function (Blueprint $t) use ($ada) {
            $t->dropColumn($ada);
        });
    }
};
