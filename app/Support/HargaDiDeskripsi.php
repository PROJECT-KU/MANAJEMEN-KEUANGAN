<?php

namespace App\Support;

use App\KategoriLayanan;

/**
 * Menyesuaikan harga yang TERTULIS di dalam naskah deskripsi angkatan.
 *
 * Deskripsi angkatan memuat harganya sebagai teks biasa — "💵 Rp 150.000 per
 * peserta" — karena naskah itu disalin apa adanya ke WhatsApp. Angka itu
 * tersimpan sebagai huruf, bukan sebagai kolom, jadi promo tidak bisa
 * menurunkannya sendiri.
 *
 * Akibatnya 15 Okt 2026 satu halaman menyebut DUA harga untuk satu sesi:
 * kotak pendaftarannya Rp 129.000 sesudah promo, naskah di sebelahnya tetap
 * Rp 150.000. Yang membacanya tidak punya cara tahu mana yang berlaku.
 *
 * -------------------------------------------------------------------------
 * Kenapa diganti seperlunya, BUKAN dirakit ulang dari cetakannya
 * -------------------------------------------------------------------------
 *
 * PerakitDeskripsi bisa merakit ulang seluruh naskah dari cetakan tarifnya,
 * dan itu pilihan yang menggoda karena lebih bersih. Tetapi naskah hasil
 * rakitan itu DISERAHKAN ke borangnya untuk disunting: admin menekan
 * "rakit", lalu menambah kalimat, mengganti kata, membuang baris. Merakit
 * ulang saat menampilkan berarti seluruh suntingan itu hilang tanpa pernah
 * diminta — dan admin tidak akan pernah tahu kenapa tulisannya berubah
 * sendiri.
 *
 * Jadi yang disentuh hanya satu hal: angka yang PERSIS SAMA dengan harga
 * normal angkatan itu. Angka lain di dalam naskah — nomor telepon, nominal
 * hadiah, harga layanan lain yang kebetulan disebut — tidak dikenali dan
 * tidak diubah.
 */
class HargaDiDeskripsi
{
    /**
     * Naskah dengan harga promonya sudah terpasang.
     *
     * Dikembalikan apa adanya kalau angkatannya tidak sedang promo, atau
     * kalau harga normalnya memang tidak disebut di dalam naskah.
     */
    public static function sesuaikan(KategoriLayanan $angkatan, ?string $naskah): string
    {
        $naskah = (string) $naskah;

        if (trim($naskah) === '') {
            return $naskah;
        }

        $normal = (int) $angkatan->harga_sebelum_promo;
        $promo = (int) $angkatan->harga_setelah_promo;

        if ($normal < 1 || $promo < 1 || $promo >= $normal) {
            return $naskah;
        }

        $tulisNormal = number_format($normal, 0, ',', '.');
        $tulisPromo = number_format($promo, 0, ',', '.');

        /*
         * "Rp", "Rp.", dengan atau tanpa spasi — ketiganya dipakai di naskah
         * yang sudah ada, dan yang dicari angka miliknya sendiri.
         *
         * Dijaga di kedua ujungnya supaya "Rp 1.150.000" tidak ikut tergaruk
         * saat harga normalnya "150.000": di depan tidak boleh ada angka atau
         * titik, di belakang juga tidak.
         */
        $pola = '/(?<![\d.])Rp\.?\s?' . preg_quote($tulisNormal, '/') . '(?![\d.])/u';

        $ganti = 'Rp ' . $tulisPromo . ' (harga normal Rp ' . $tulisNormal . ')';

        return (string) preg_replace($pola, $ganti, $naskah);
    }

    /**
     * Baris mana saja yang BERUBAH karena promonya.
     *
     * Dipakai layar "Ubah angkatan" untuk memberi tahu admin kalimat apa yang
     * sebenarnya dibaca pelanggan, tanpa menyentuh kotak suntingnya. Kotak itu
     * sengaja tetap berisi naskah asli: kalau ia ikut memajang harga promo,
     * sekali tekan Simpan angka promonya tertulis permanen ke naskah dan tidak
     * akan kembali saat promonya berakhir.
     *
     * @return list<string> kosong kalau tidak ada promo, atau kalau naskahnya
     *                      memang tidak menyebut harga normalnya
     */
    public static function barisBerubah(KategoriLayanan $angkatan, ?string $naskah): array
    {
        $naskah = (string) $naskah;
        $sesudah = self::sesuaikan($angkatan, $naskah);

        if ($sesudah === $naskah) {
            return [];
        }

        $asal = preg_split('/\r\n|\r|\n/', $naskah) ?: [];
        $jadi = preg_split('/\r\n|\r|\n/', $sesudah) ?: [];

        $berubah = [];

        foreach ($jadi as $i => $baris) {
            if (($asal[$i] ?? null) !== $baris && trim($baris) !== '') {
                $berubah[] = trim($baris);
            }
        }

        return $berubah;
    }
}
