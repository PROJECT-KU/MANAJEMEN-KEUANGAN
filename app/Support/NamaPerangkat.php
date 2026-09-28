<?php

namespace App\Support;

/**
 * Menerjemahkan user-agent jadi kalimat yang bisa dibaca orang.
 *
 * Di halaman keamanan, pemilik akun harus bisa menjawab satu pertanyaan:
 * "baris ini saya atau bukan?". Deretan seperti
 * "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 ..."
 * tidak menjawab itu — "Chrome di Mac" menjawabnya.
 *
 * Sengaja tidak memakai pustaka pihak ketiga: yang dibutuhkan hanya nama
 * peramban dan nama sistemnya, bukan versi atau mesin render. Kalau polanya
 * tidak dikenali, potongan aslinya tetap ditampilkan supaya tidak ada
 * informasi yang hilang diam-diam.
 */
class NamaPerangkat
{
    /**
     * Urutannya penting. Edge dan Opera menyebut "Chrome" di user-agent-nya,
     * dan Chrome menyebut "Safari" — yang lebih khusus harus diperiksa dulu.
     */
    private const PERAMBAN = [
        'Edge' => '/\bEdg(?:e|A|iOS)?\//i',
        'Opera' => '/\bOPR\/|\bOpera\//i',
        'Samsung Internet' => '/\bSamsungBrowser\//i',
        'Firefox' => '/\bFirefox\/|\bFxiOS\//i',
        // Sebelum Chrome: "HeadlessChrome" tidak punya batas kata sebelum
        // "Chrome", jadi pola Chrome di bawah meleset dan peramban ini dulu
        // salah dinamai "Safari". Peramban tanpa jendela hampir selalu berarti
        // alat otomatis — justru itu yang perlu dilihat pemilik akun.
        'Chrome otomatis' => '/HeadlessChrome\//i',
        'Chrome' => '/\bChrome\/|\bCriOS\//i',
        'Safari' => '/\bSafari\//i',
    ];

    private const SISTEM = [
        'iPhone' => '/\biPhone\b/i',
        'iPad' => '/\biPad\b/i',
        'Android' => '/\bAndroid\b/i',
        'Windows' => '/\bWindows NT\b/i',
        'Mac' => '/\bMac OS X\b|\bMacintosh\b/i',
        'Linux' => '/\bLinux\b|\bX11\b/i',
    ];

    /** Nama pendek yang bisa dibaca, mis. "Chrome di Mac". */
    public static function ringkas(?string $userAgent): string
    {
        $ua = trim(strip_tags((string) $userAgent));

        if ($ua === '') {
            return 'Peramban tidak dikenali';
        }

        $peramban = self::cocok(self::PERAMBAN, $ua);
        $sistem = self::cocok(self::SISTEM, $ua);

        if ($peramban !== null && $sistem !== null) {
            return $peramban . ' di ' . $sistem;
        }

        if ($peramban !== null) {
            return $peramban;
        }

        if ($sistem !== null) {
            return 'Peramban lain di ' . $sistem;
        }

        // Tidak dikenali: tampilkan potongan aslinya, bukan "tidak dikenali".
        // Bisa jadi ini memang alat yang perlu dicurigai pemilik akun.
        return \Illuminate\Support\Str::limit($ua, 40);
    }

    /**
     * Nama ikon Font Awesome yang cocok dengan jenis perangkatnya.
     *
     * Sebelumnya tampilan memilih ikon dari "ini perangkat saya atau bukan",
     * sehingga baris bertuliskan "Safari di iPhone" bisa bergambar komputer.
     * Nama ikonnya harus ada di Font Awesome 5.5; lihat IkonAdaGlifnyaTest.
     */
    public static function ikon(?string $userAgent): string
    {
        $ua = trim(strip_tags((string) $userAgent));

        return match (self::cocok(self::SISTEM, $ua)) {
            'iPhone', 'Android' => 'fa-mobile-alt',
            'iPad' => 'fa-tablet-alt',
            'Mac', 'Windows', 'Linux' => 'fa-laptop',
            default => 'fa-desktop',
        };
    }

    private static function cocok(array $pola, string $ua): ?string
    {
        foreach ($pola as $nama => $regex) {
            if (preg_match($regex, $ua) === 1) {
                return $nama;
            }
        }

        return null;
    }
}
