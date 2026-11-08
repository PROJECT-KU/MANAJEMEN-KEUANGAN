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
     * Angkatan ini BISA menerima kode diskon dari mana pun.
     *
     * -----------------------------------------------------------------
     * Kenapa ini ada
     * -----------------------------------------------------------------
     *
     * Borang pendaftaran menggambar kotak "kode diskon" hanya kalau ada
     * kode yang mungkin dipakai — kotak yang tidak bisa diisi apa pun cuma
     * membuat orang mengira ia kehilangan sesuatu. Sampai 8 Nov 2026
     * pertanyaannya dijawab `$angkatan->kode_diskon !== ''`, yaitu kode
     * gaya lama yang menempel di angkatannya sendiri.
     *
     * Itu MELEWATKAN promo berkode. Terukur pada "testing webinar eksklusif
     * 3": promo "Promo Eksklusif" berkode EKSKLUSIF menyasar angkatan itu
     * dan peladennya memang memotong Rp 51.000 untuk nomor yang memenuhi
     * syaratnya — tetapi `kode_diskon` angkatannya NULL, jadi kotak
     * isiannya tidak pernah digambar dan kodenya tidak bisa diketik
     * siapa pun. Promo yang berfungsi sempurna di peladen, mati di layar.
     *
     * -----------------------------------------------------------------
     * Yang TIDAK diperiksa di sini, dan itu disengaja
     * -----------------------------------------------------------------
     *
     * Syarat riwayat ("hanya untuk yang pernah ikut") TIDAK diperiksa.
     * Saat borangnya digambar, nomor pendaftarnya belum diketik — jadi
     * memeriksanya di sini berarti menyembunyikan kotak isian dari orang
     * yang justru berhak. Yang menentukan tetap hitung(), saat kodenya
     * benar-benar dikirim bersama nomornya.
     *
     * Jadi jawabannya "mungkin ada kode", bukan "pasti dapat potongan".
     */
    public static function mungkinAdaKode(KategoriLayanan $angkatan): bool
    {
        if (trim((string) $angkatan->kode_diskon) !== '') {
            return true;
        }

        $id = (string) $angkatan->getKey();

        if ($id === '') {
            return false;
        }

        return PromoLayanan::query()
            ->berjalan()
            ->whereNotNull('kode')
            ->whereRaw("TRIM(kode) <> ''")
            ->whereHas('angkatan', fn ($q) => $q->where('kategori_layanan.id', $id))
            ->get()
            ->contains(fn (PromoLayanan $p) => ! $p->kuotaHabis());
    }

    /**
     * @return array{kode: ?string, potongan: int, promo: ?PromoLayanan, label: ?string}
     */
    public static function hitung(
        KategoriLayanan $angkatan,
        ?string $kode,
        int $total,
        ?string $telp = null
    ): array {
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

        /*
         * NOMORNYA ikut, sebab promo boleh menuntut pendaftarnya pernah
         * lunas di layanan lain. Tanpa nomor, promo bersyarat riwayat tidak
         * pernah terpenuhi — dan itu memang yang benar: lebih baik potongan
         * tidak keluar daripada keluar untuk orang yang belum tentu alumni.
         */
        $promo = PromoAngkatan::denganKode($angkatan, $kode, $telp);

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
