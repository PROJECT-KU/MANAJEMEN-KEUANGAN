<?php

namespace App\Support;

use App\KategoriLayanan;
use Carbon\Carbon;

/**
 * Merakit deskripsi satu angkatan dari cetakan milik tarif induknya.
 *
 * Teks pengumuman tiap angkatan nyaris sama persis; yang berganti cuma nomor,
 * tanggal, lokasi, harga, dan kontak. Semuanya sudah jadi kolom di
 * `kategori_layanan`, jadi tidak ada satu pun yang perlu diketik dua kali.
 *
 * Penanda yang tidak dikenal SENGAJA dibiarkan apa adanya. Diganti kosong,
 * salah ketik "{tanggl}" menghilang diam-diam dan admin baru sadar setelah
 * pengumumannya tersebar; dibiarkan, ia kelihatan di pratinjau.
 */
class PerakitDeskripsi
{
    /** Daftar penanda yang dikenali, untuk ditampilkan sebagai bantuan di layar. */
    public const PENANDA = [
        '{nama}' => 'Nama angkatan, mis. "SCOPUS CAMP YOGYAKARTA #202"',
        '{layanan}' => 'Nama layanan, mis. "Scopus Camp"',
        '{nomor}' => 'Nomor angkatan, mis. "202"',
        '{tanggal}' => 'Rentang tanggal, mis. "30 Oktober – 1 November 2026"',
        '{lokasi}' => 'Lokasi angkatan',
        '{harga}' => 'Harga normal, mis. "Rp 5.500.000"',
        '{harga_promo}' => 'Harga sesudah diskon; kosong kalau tidak ada promo',
        '{kode_promo}' => 'Kode diskon; kosong kalau tidak ada promo',
        '{kuota}' => 'Total kuota peserta',
        '{sisa_kuota}' => 'Sisa kuota peserta',
        '{fasilitas}' => 'Daftar fasilitas bernomor, dari tarif induk',
        '{kegiatan}' => 'Daftar kegiatan bernomor, dari tarif induk',
        '{kontak}' => 'Kontak panitia, dari tarif induk',
        '{grup_wa}' => 'Tautan grup WhatsApp',
    ];

    public static function rakit(KategoriLayanan $angkatan): string
    {
        $tarif = $angkatan->tarif();
        $cetakan = (string) ($tarif?->template_deskripsi ?? '');

        if (trim($cetakan) === '') {
            return '';
        }

        return self::pasang($cetakan, self::nilai($angkatan, $tarif));
    }

    /**
     * Nilai tiap penanda untuk satu angkatan.
     *
     * @return array<string, string>
     */
    private static function nilai(KategoriLayanan $angkatan, $tarif): array
    {
        $rupiah = fn ($n) => $n === null || $n === '' || (int) $n < 1
            ? ''
            : 'Rp ' . number_format((int) $n, 0, ',', '.');

        // Harga angkatan didahulukan; tarif induk cuma cadangan kalau
        // angkatannya belum mengisi. Angkatan yang sudah berjalan memakai harga
        // saat itu, bukan harga yang berlaku sekarang.
        $harga = $angkatan->biaya ?: $tarif?->biaya_persesi;

        $promo = (int) $angkatan->total_biaya > 0
            && (int) $angkatan->total_biaya !== (int) $harga
                ? $angkatan->total_biaya
                : null;

        return [
            '{nama}' => (string) $angkatan->nama,
            '{layanan}' => $angkatan->nama_layanan,
            '{nomor}' => (string) $angkatan->nama_ke,
            '{tanggal}' => RentangTanggal::tulis(
                $angkatan->mulai ? Carbon::parse($angkatan->mulai) : null,
                $angkatan->selesai ? Carbon::parse($angkatan->selesai) : null
            ),
            '{lokasi}' => (string) $angkatan->lokasi,
            '{harga}' => $rupiah($harga),
            '{harga_promo}' => $rupiah($promo),
            '{kode_promo}' => (string) $angkatan->kode_diskon,
            '{kuota}' => (string) $angkatan->total_kuota,
            '{sisa_kuota}' => (string) $angkatan->sisa_kuota,
            '{fasilitas}' => self::bernomor($tarif?->daftar_fasilitas ?? []),
            '{kegiatan}' => self::bernomor($tarif?->daftar_kegiatan ?? []),
            '{kontak}' => trim((string) $tarif?->kontak),
            '{grup_wa}' => (string) $angkatan->group_wa,
        ];
    }

    /**
     * @param  array<string, string>  $nilai
     */
    private static function pasang(string $cetakan, array $nilai): string
    {
        $keluar = [];

        foreach (preg_split('/\r\n|\r|\n/', $cetakan) as $baris) {
            preg_match_all('/\{[a-z_]+\}/', $baris, $ada);
            $penanda = array_filter($ada[0], fn ($p) => array_key_exists($p, $nilai));

            $terisi = strtr($baris, $nilai);

            /*
             * Baris yang SELURUH penandanya kosong ikut dibuang. Tanpa ini,
             * angkatan Bibliometrik yang memang tidak punya lokasi mencetak
             * "📍 Lokasi:" menggantung tanpa isi — dan itu justru terlihat
             * seperti data yang hilang.
             */
            if ($penanda !== [] && self::semuaKosong($penanda, $nilai)) {
                continue;
            }

            $keluar[] = rtrim($terisi);
        }

        // Baris kosong beruntun sisa pembuangan dirapatkan jadi satu.
        $rapi = preg_replace("/\n{3,}/", "\n\n", implode("\n", $keluar));

        return trim($rapi);
    }

    /**
     * @param  array<int, string>  $penanda
     * @param  array<string, string>  $nilai
     */
    private static function semuaKosong(array $penanda, array $nilai): bool
    {
        foreach ($penanda as $p) {
            if (trim($nilai[$p]) !== '') {
                return false;
            }
        }

        return true;
    }

    /** @param array<int, string> $butir */
    private static function bernomor(array $butir): string
    {
        $baris = [];

        foreach (array_values($butir) as $i => $b) {
            $baris[] = ($i + 1) . '. ' . $b;
        }

        return implode("\n", $baris);
    }
}
