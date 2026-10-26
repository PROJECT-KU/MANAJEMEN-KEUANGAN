<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tagihan gerbang pembayaran untuk Scopus Kafe.
 *
 * Sampai sekarang satu-satunya cara bayarnya transfer manual berbukti
 * unggahan: pemesan menyalin nomor rekening, pindah ke aplikasi banknya,
 * memotret bukti, lalu kembali mengunggahnya — dan panitia membuka gambarnya
 * satu per satu untuk memutuskan. Pola yang sama sudah dicabut dari Scopus
 * Camp 26 Okt 2026 atas keputusan pemiliknya: pembayaran seluruh layanan
 * lewat gerbang.
 *
 * Kolomnya MENYALIN nama yang sudah dipakai Scopus Camp dan Webinar
 * Eksklusif — bayar_* dengan arti yang sama persis — bukan nama baru. Layar
 * Pendaftar Layanan membaca kelima layanan lewat satu UNION, dan nama yang
 * berbeda untuk hal yang sama berarti satu cabang lagi di setiap tempat yang
 * membacanya.
 *
 * `bayar_nomor` ikut sejak awal di sini, tidak menyusul seperti di Scopus
 * Camp: nomor tagihan di gerbang hanya boleh dipakai SEKALI, dan tanpa kolom
 * ini satu pemesanan yang pembayarannya gagal tidak akan pernah bisa dibayar
 * lagi — "INVOICE ALREADY USED", terukur di Scopus Camp 24 Okt 2026.
 *
 * Semuanya boleh kosong: sembilan pemesanan yang sudah ada dibayar lewat
 * transfer manual dan memang tidak punya tagihan di gerbang mana pun.
 */
return new class extends Migration
{
    /**
     * @var array<string, array{0: string, 1: int|null, 2: string}>
     *      nama kolom => [tipe, panjang, kolom yang didahuluinya]
     */
    private const KOLOM = [
        'bayar_nomor' => ['string', 64, 'cara_bayar'],
        'bayar_rujukan' => ['string', 100, 'bayar_nomor'],
        'bayar_permintaan_id' => ['string', 100, 'bayar_rujukan'],
        'bayar_url' => ['string', 500, 'bayar_permintaan_id'],
        'bayar_status' => ['string', 40, 'bayar_url'],
        'bayar_kanal' => ['string', 60, 'bayar_status'],
        'bayar_pada' => ['timestamp', null, 'bayar_kanal'],
        'kedaluwarsa_pada' => ['timestamp', null, 'bayar_pada'],
    ];

    public function up(): void
    {
        Schema::table('pendaftaran_scopus_kafe', function (Blueprint $t) {
            foreach (self::KOLOM as $nama => [$tipe, $panjang, $sesudah]) {
                if (Schema::hasColumn('pendaftaran_scopus_kafe', $nama)) {
                    continue;
                }

                $kolom = $tipe === 'timestamp'
                    ? $t->timestamp($nama)
                    : $t->string($nama, $panjang);

                $kolom->nullable()->after($sesudah);
            }
        });

        /*
         * Indeksnya DIPERIKSA dulu keberadaannya: migrasi ini bisa jalan di
         * basis data yang kolomnya sudah ditambahkan dengan tangan, dan
         * menambahkan indeks bernama sama melempar "Duplicate key name" —
         * yang menggagalkan SELURUH migrasinya, bukan hanya baris itu.
         */
        $ada = collect(Schema::getIndexes('pendaftaran_scopus_kafe'))->pluck('name');

        Schema::table('pendaftaran_scopus_kafe', function (Blueprint $t) use ($ada) {
            if (! $ada->contains('pendaftaran_scopus_kafe_bayar_nomor_index')) {
                $t->index('bayar_nomor');
            }

            /*
             * Sapuan kedaluwarsa menyaring tenggat yang sudah lewat tiap
             * sepuluh menit, dan halaman umum ikut memanggilnya. Tanpa indeks
             * ia memindai seluruh tabel tiap kali.
             */
            if (! $ada->contains('pendaftaran_scopus_kafe_kedaluwarsa_pada_index')) {
                $t->index('kedaluwarsa_pada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_scopus_kafe', function (Blueprint $t) {
            foreach (['bayar_nomor', 'kedaluwarsa_pada'] as $indeks) {
                if (Schema::hasColumn('pendaftaran_scopus_kafe', $indeks)) {
                    $t->dropIndex('pendaftaran_scopus_kafe_' . $indeks . '_index');
                }
            }

            $punya = array_filter(
                array_keys(self::KOLOM),
                fn ($k) => Schema::hasColumn('pendaftaran_scopus_kafe', $k)
            );

            if ($punya !== []) {
                $t->dropColumn(array_values($punya));
            }
        });
    }
};
