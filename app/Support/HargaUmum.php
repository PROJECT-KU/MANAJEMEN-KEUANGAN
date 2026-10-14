<?php

namespace App\Support;

use App\KategoriLayanan;
use App\PromoLayanan;

/**
 * Harga pendaftaran yang DIPAJANG dan DITAGIH di halaman umum.
 *
 * Satu rumus untuk keduanya, dan itu inti dari kelas ini. Sebelum ada kelas
 * ini, halaman umum memakai tiga angka berbeda untuk satu angkatan:
 *
 *   - halaman daftarnya MENCORET `biaya` dan menjanjikan `total_biaya`;
 *   - borang pendaftarannya memakai `biaya`, yaitu angka yang dicoret;
 *   - jalur panitia memakai `total_biaya ?: biaya`.
 *
 * Terukur 13 Okt 2026: 18 angkatan terkena. Scopus Camp Padang menjanjikan
 * Rp 5.060.000 di halaman daftarnya lalu menagih Rp 5.500.000 di borangnya —
 * selisih Rp 440.000 per kursi, dan tidak ada galat apa pun yang memberi tahu.
 *
 * Yang benar `total_biaya ?: biaya` dikurangi promo, sebab itulah yang sudah
 * dijanjikan halaman daftarnya kepada orang.
 *
 * Totalnya dihitung ULANG di peladen saat menyimpan, bukan diterima apa adanya
 * dari kiriman borang. Sebelumnya `total_pembayaran` datang dari peramban dan
 * disimpan mentah — nominal yang ditagihkan sepenuhnya ditentukan oleh sisi
 * yang tidak bisa dipercaya.
 */
class HargaUmum
{
    /**
     * Rincian harga satu pendaftaran umum.
     *
     * Rumusnya MENYALIN urutan yang dipakai borangnya, termasuk kejanggalan
     * yang sudah ada di sana: PPN dihitung dari SATU kursi, bukan dari
     * subtotal. Itu dipertahankan apa adanya — membetulkannya di sini berarti
     * mengubah nominal yang ditagihkan untuk alasan yang sama sekali berbeda
     * dari perkara promo, dan dua perubahan uang dalam satu langkah tidak bisa
     * ditelusuri kalau salah satunya keliru.
     *
     * @return array{satuan:int, subtotal:int, ppn:int, kode_unik:int, potongan:int, total:int, promo:?PromoLayanan}
     */
    public static function hitung(
        KategoriLayanan $angkatan,
        int $jumlah,
        $kodeUnikDikirim = null,
        $potonganKode = 0
    ): array {
        $jumlah = max(1, $jumlah);

        $promo = PromoAngkatan::untuk($angkatan);
        $satuan = (int) $angkatan->harga_setelah_promo;

        $persenPpn = (int) preg_replace('/\D+/', '', (string) $angkatan->ppn);
        $ppn = $persenPpn > 0 ? (int) round($satuan * $persenPpn / 100) : 0;

        $potongan = max(0, (int) preg_replace('/\D+/', '', (string) $potonganKode));
        $subtotal = $satuan * $jumlah;
        $potongan = min($subtotal, $potongan);

        /*
         * Kode uniknya dihitung SEKALI ke dalam peubah.
         *
         * Dipanggil dua kali — sekali untuk kolomnya, sekali di dalam
         * penjumlahan total — keduanya akan memberi angka BERBEDA begitu
         * jatuh ke pembuat acak. Akibatnya nominal yang ditagihkan tidak
         * berakhir dengan kode yang tersimpan di sebelahnya, dan tidak ada
         * satu pun transfer yang bisa dicocokkan dengannya.
         */
        $kodeUnik = self::kodeUnik($kodeUnikDikirim);

        return [
            'satuan' => $satuan,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'kode_unik' => $kodeUnik,
            'potongan' => $potongan,
            'total' => max(0, $subtotal + $kodeUnik - $potongan + $ppn),
            'promo' => $promo,
        ];
    }

    /**
     * Kode unik yang dipakai: yang dikirim borangnya kalau masuk akal, kalau
     * tidak dibuat baru.
     *
     * Dipercaya mentah, siapa pun bisa mengirim kode unik 9.999.999 dan
     * menaikkan tagihannya sendiri — atau mengirim 0 dan menghapus satu-satunya
     * penanda yang dipakai mencocokkan mutasi rekening.
     */
    private static function kodeUnik($dikirim): int
    {
        [$min, $maks] = PendaftaranSemuaLayanan::KODE_UNIK;

        $angka = (int) preg_replace('/\D+/', '', (string) $dikirim);

        return $angka >= $min && $angka <= $maks ? $angka : random_int($min, $maks);
    }
}
