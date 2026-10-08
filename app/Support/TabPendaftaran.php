<?php

namespace App\Support;

use App\Actions\Pendaftaran\UbahDataPendaftaran;

/**
 * Tab mana yang berdiri sendiri di layar rincian, dan mana yang dilebur.
 *
 * Tab dipakai karena Scopus Kafe punya 26 isian: menyuguhkan semuanya
 * sekaligus menuntut menggulung jauh hanya untuk menemukan satu nominal.
 * Tetapi aturan yang sama diterapkan apa adanya ke layanan lain menghasilkan
 * TAB BERISI SATU ISIAN — terukur 7 Okt 2026, empat dari lima layanan punya
 * satu: Scopus Camp dan Webinar Eksklusif (tab Jadwal, 1 isian), Clinik
 * Scopus (2), dan Scopus Kafe (tab Pembayaran, 1). Satu tab untuk satu isian
 * menambah satu klik dan satu tempat yang harus diingat, tanpa menghemat
 * gulungan apa pun.
 *
 * Maka tab yang isinya kurang dari tiga isian DILEBUR ke tab identitas, yang
 * lalu bernama "Data pendaftaran". Dilebur ke situ, BUKAN ke tab Pembayaran:
 * isian Angkatan pernah duduk di sana dan justru itu yang dibuang — panitia
 * yang hendak memindahkan peserta tidak mencarinya di layar pembayaran, dan
 * sempat mengisi "Tanggal jadwal ulang" yang tidak dibaca apa pun sambil
 * mengira pesertanya sudah pindah.
 *
 * SATU SUMBER untuk tampilan dan pengendali. Pengendali memakainya untuk
 * mengembalikan panitia ke tab yang baru ia simpan; kalau aturannya ditulis
 * dua kali, satu tekan Simpan akan mendarat di tab yang sudah tidak ada dan
 * halamannya diam-diam kembali ke Ringkasan.
 */
class TabPendaftaran
{
    /** Tab identitas: sasaran peleburan, dan tidak pernah ikut dilebur. */
    public const UTAMA = 'diri';

    /**
     * Isian paling sedikit supaya satu tab layak berdiri sendiri.
     *
     * Tiga, bukan dua: tab berisi dua isian pun masih lebih mahal diklik
     * daripada dibaca di tempat. Dengan angka ini, satu-satunya tab yang
     * masih berdiri sendiri adalah yang memang penuh — Jadwal Scopus Kafe
     * (22 isian) dan Pembayaran Scopus Camp/Bibliometrik (6).
     */
    public const BATAS_BERDIRI_SENDIRI = 3;

    /**
     * Medan milik tab mana, SEBELUM peleburan.
     *
     * Pindahan dari PendaftaranLayananController::tabDari(); di sanalah
     * daftar ini dulu tinggal sendirian.
     */
    public const MEDAN = [
        'bayar' => ['jumlah_pendaftar', 'ppn', 'kode_unik', 'kode_diskon',
            'nominal_diskon', 'total_pembayaran', 'total_keseluruhan_pembayaran'],
        'sesi' => ['kategori_id', 'tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai',
            'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran',
            'subtotal_pembayaran', 'sesi_kedua', 'waktu_mulai_kedua', 'waktu_selesai_kedua',
            'lokasi_kedua', 'biaya_kedua', 'kode_unik_pembayaran_kedua',
            'subtotal_pembayaran_kedua', 'sesi_ketiga', 'waktu_mulai_ketiga',
            'waktu_selesai_ketiga', 'lokasi_ketiga', 'biaya_ketiga',
            'kode_unik_pembayaran_ketiga', 'subtotal_pembayaran_ketiga', 'group_wa'],
        'diri' => ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp',
            'telp_pemesan', 'affiliasi', 'afiliasi_pemesan', 'note',
            'kendala', 'desc_kendala'],
    ];

    /**
     * Tab yang dileburkan ke tab identitas untuk satu layanan.
     *
     * @return array<int, string>
     */
    public static function dilebur(string $layanan): array
    {
        $punya = array_keys(UbahDataPendaftaran::medan($layanan));
        $hasil = [];

        foreach (self::MEDAN as $tab => $daftar) {
            if ($tab === self::UTAMA) {
                continue;
            }

            $jumlah = count(array_intersect($punya, $daftar));

            // Nol berarti tabnya memang tidak ada untuk layanan ini — bukan
            // dilebur, melainkan tidak pernah tergambar.
            if ($jumlah > 0 && $jumlah < self::BATAS_BERDIRI_SENDIRI) {
                $hasil[] = $tab;
            }
        }

        return $hasil;
    }

    /** Apakah tab identitas layanan ini menampung isian dari tab lain. */
    public static function adaYangDilebur(string $layanan): bool
    {
        return self::dilebur($layanan) !== [];
    }

    /** Nama tab identitas, mengikuti isinya. */
    public static function namaTabUtama(string $layanan): string
    {
        return self::adaYangDilebur($layanan) ? 'Data pendaftaran' : 'Identitas';
    }

    /**
     * Tab tempat satu medan BENAR-BENAR tergambar, sesudah peleburan.
     *
     * Medan yang tidak dikenali jatuh ke tab identitas — sama seperti
     * sebelumnya, dan itu memang tab yang paling mungkin memuatnya.
     */
    public static function tabMedan(string $layanan, string $kolom): string
    {
        $dilebur = self::dilebur($layanan);

        foreach (self::MEDAN as $tab => $daftar) {
            if (in_array($kolom, $daftar, true)) {
                return in_array($tab, $dilebur, true) ? self::UTAMA : $tab;
            }
        }

        return self::UTAMA;
    }

    /**
     * Tab yang harus dibuka sesudah sekelompok medan disimpan.
     *
     * @param  array<int, string>  $medanKiriman
     */
    public static function tabDariKiriman(string $layanan, array $medanKiriman): string
    {
        foreach (array_keys(self::MEDAN) as $tab) {
            foreach ($medanKiriman as $kolom) {
                if (in_array($kolom, self::MEDAN[$tab], true)) {
                    return self::tabMedan($layanan, $kolom);
                }
            }
        }

        return self::UTAMA;
    }
}
