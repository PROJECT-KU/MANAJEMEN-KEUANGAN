<?php

namespace App\Support;

use App\KategoriLayanan;
use App\PromoLayanan;

/**
 * Berapa rupiah yang dipotong sebuah kode, DIHITUNG DI PELADEN.
 *
 * Satu-satunya pintu untuk pertanyaan itu, dan ia lahir dari lubang yang
 * terukur 31 Okt 2026 di Scopus Camp:
 *
 *     POST /Scopus-Camp/store
 *       kode_diskon   = KODE-KARANGAN      (angkatannya tidak punya kode)
 *       nominal_diskon = 5.499.000
 *
 * Kursi seharga Rp 5.500.000 tersimpan dengan tagihan **Rp 2.039**, dan
 * tagihan DOKU-nya benar-benar terbit atas angka itu. Besar potongannya
 * diambil mentah dari kiriman borang: tidak ada satu baris pun yang
 * memeriksa apakah kodenya sah, apalagi apakah nominalnya cocok. Jalur
 * Analisis Bibliometrik memakai pola yang sama persis.
 *
 * Yang diterima dari borang sekarang hanya KODENYA. Nominalnya dicari
 * sendiri di sini, dari dua sumber:
 *
 *   1. kode milik ANGKATAN itu sendiri (`kategori_layanan.kode_diskon`) —
 *      jalur lama, dipertahankan apa adanya;
 *   2. promo BERKODE di layar Promo Layanan yang menyasar angkatan itu.
 *
 * Polanya menyalin PublicWebinarEksklusifController::hitungPotongan(), satu-
 * satunya jalur yang sejak awal sudah benar — ia membandingkan kodenya
 * dengan hash_equals lalu membaca nominalnya dari basis data.
 */
class PotonganKode
{
    /**
     * @return array{kode: ?string, potongan: int, promo: ?PromoLayanan, label: ?string}
     */
    public static function hitung(KategoriLayanan $angkatan, ?string $kode, int $total): array
    {
        $kosong = ['kode' => null, 'potongan' => 0, 'promo' => null, 'label' => null];

        $kode = trim((string) $kode);
        $total = max(0, $total);

        if ($kode === '' || $total === 0) {
            return $kosong;
        }

        /*
         * Kode milik angkatannya didahulukan.
         *
         * Bukan karena lebih menguntungkan, melainkan karena ia yang sudah
         * dipakai dan sudah dicetak di bahan promosi yang beredar. Promo
         * berkode fitur baru; ia tidak boleh mengubah arti kode yang sudah
         * terlanjur disebar.
         */
        $kodeAngkatan = trim((string) $angkatan->kode_diskon);

        if ($kodeAngkatan !== '' && hash_equals($kodeAngkatan, $kode)) {
            $nominal = (int) preg_replace('/\D+/', '', (string) $angkatan->nominal_diskon);

            return [
                'kode' => $kodeAngkatan,
                'potongan' => max(0, min($nominal, $total)),
                'promo' => null,
                'label' => 'Potongan ' . $kodeAngkatan,
            ];
        }

        $promo = PromoAngkatan::denganKode($angkatan, $kode);

        if ($promo === null) {
            return $kosong;
        }

        /*
         * Potongannya dihitung dari HARGA SATU KURSI, lalu dikalikan jumlah
         * kursi oleh pemanggilnya lewat $total? TIDAK.
         *
         * potonganUntuk() memulangkan potongan untuk angka yang diberikan,
         * dan di sini yang diberikan subtotal seluruh kursi. Untuk promo
         * persen hasilnya sama saja dikali di mana pun; untuk promo rupiah
         * dan untuk batas_potongan, memotong dari subtotal berarti potongan
         * Rp 500.000 berlaku SEKALI untuk satu pemesanan — bukan sekali per
         * kursi. Itu yang dimaksud panitia saat menulis "potongan Rp 500.000"
         * di bahan promosinya.
         */
        return [
            'kode' => PromoLayanan::rapikanKode($promo->kode),
            'potongan' => max(0, min($promo->potonganUntuk($total), $total)),
            'promo' => $promo,
            'label' => $promo->nama,
        ];
    }
}
