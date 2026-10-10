<?php

namespace App\Support;

use App\PembayaranPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

/**
 * Berapa uang yang BENAR-BENAR sudah diterima untuk satu pendaftaran.
 *
 * Berbeda dari tagihannya, dan perbedaan itu yang sempat membuat dua
 * penjaga uang diam-diam tidak berlaku sama sekali.
 *
 * Penjaga pertama — catatan penghapusan — menjumlahkan baris termin. Terukur
 * di basis data ini: NOL baris termin untuk pendaftaran, sementara 172
 * pendaftaran berstatus lunas. Jadi menghapus pendaftaran lunas tetap
 * mengurangi ringkasan "uang masuk", tetapi arsipnya mencatat "tanpa
 * pembayaran" — persis lubang yang hendak ditutupnya, cuma pindah tempat.
 *
 * Penjaga kedua — batas pengembalian dana — membandingkan dengan TAGIHAN,
 * sehingga pendaftaran yang belum membayar sepeser pun tetap bisa dicatat
 * dikembalikan dananya.
 *
 * Keduanya sekarang bertanya ke sini. Aturannya mengikuti cara ringkasan
 * layar daftar menghitung "uang masuk", supaya kedua angka itu tidak pernah
 * bercerita berbeda tentang pendaftaran yang sama.
 */
class UangPendaftaran
{
    /**
     * Uang masuk untuk satu pendaftaran.
     *
     * Urutannya disengaja:
     *
     *   1. Kalau terminnya dicatat, ITU yang dipakai — ia catatan uang
     *      sungguhan, lengkap dengan tanggal dan caranya.
     *   2. Tanpa termin, status lunas berarti tagihannya memang sudah
     *      masuk. Inilah keadaan SELURUH data yang ada sekarang.
     *   3. Selain itu nol. Pendaftaran yang masih menunggu memang belum
     *      menyetor apa pun, berapa pun tagihannya.
     */
    public static function diterima(string $layanan, $pendaftaran): int
    {
        $termin = (int) PembayaranPendaftaran::milik(
            PembayaranPendaftaran::PENDAFTARAN, (string) $pendaftaran->getKey()
        )->sum('nominal');

        if ($termin > 0) {
            return $termin;
        }

        return Pendaftaran::keadaanDari($pendaftaran->status ?? null) === 'lunas'
            ? self::tagihan($layanan, $pendaftaran)
            : 0;
    }

    /**
     * Tagihannya, dibaca lewat katalog.
     *
     * Kolomnya bernama berbeda di tiap layanan (total_pembayaran vs
     * total_keseluruhan_pembayaran), dan sebagian menyimpannya sebagai
     * varchar berformat — jadi angkanya disaring, bukan di-cast begitu saja.
     */
    public static function tagihan(string $layanan, $pendaftaran): int
    {
        $kolom = Pendaftaran::kolomPeran($layanan, 'total');

        if ($kolom === null) {
            return 0;
        }

        return (int) preg_replace('/\D+/', '', (string) ($pendaftaran->{$kolom} ?? ''));
    }

    /** Berapa baris termin yang tercatat; dipakai menerangkan angkanya. */
    public static function jumlahTermin(string $pendaftaranId): int
    {
        return PembayaranPendaftaran::milik(
            PembayaranPendaftaran::PENDAFTARAN, $pendaftaranId
        )->count();
    }
}
