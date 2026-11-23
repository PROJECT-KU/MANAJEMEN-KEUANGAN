<?php

namespace App\Support;

/**
 * Membaca tautan YouTube apa adanya, lalu memulangkan bentuk yang bisa disemat.
 *
 * ---------------------------------------------------------------------------
 * Kenapa ini kelas tersendiri
 * ---------------------------------------------------------------------------
 *
 * Admin tidak menempelkan "id video". Ia menempelkan apa pun yang ada di bilah
 * alamat peramban atau di tombol Bagikan, dan YouTube memulangkan setidaknya
 * enam bentuk yang berbeda untuk video yang sama:
 *
 *   https://www.youtube.com/watch?v=ID
 *   https://youtu.be/ID?si=EKOR
 *   https://www.youtube.com/embed/ID
 *   https://www.youtube.com/live/ID
 *   https://www.youtube.com/shorts/ID
 *   https://m.youtube.com/watch?v=ID&list=...&pp=...
 *
 * Menyematkan tautan MENTAH ke dalam <iframe src> gagal untuk lima dari enam
 * bentuk itu, dan gagalnya DIAM: iframe-nya terpasang rapi, ukurannya benar,
 * isinya kosong atau "Video unavailable". Tidak ada galat PHP, tidak ada baris
 * log; yang melihatnya hanya peserta yang sudah membayar.
 *
 * Karena itu id-nya diambil SEKALI di sini, dan hanya bentuk /embed/ yang
 * pernah sampai ke tampilan.
 *
 * ---------------------------------------------------------------------------
 * Yang TIDAK dikerjakan kelas ini
 * ---------------------------------------------------------------------------
 *
 * Ia tidak memeriksa videonya ada, bisa ditonton, atau milik siapa — itu
 * perlu memanggil YouTube dari peladen, dan peladen ini tidak boleh
 * bergantung pada layanan luar hanya untuk menyimpan satu tautan. Yang
 * dijamin di sini cuma bentuknya.
 */
class TautanYouTube
{
    /**
     * Id video YouTube selalu 11 huruf dari kumpulan yang aman untuk URL.
     *
     * Ditulis sebagai patokan, bukan ditebak panjangnya: tanpa batas pasti,
     * ekor seperti "?si=abcdef" ikut terbaca sebagai bagian id, dan tautan
     * sematnya jadi 404 yang diam.
     */
    private const POLA_ID = '[A-Za-z0-9_-]{11}';

    /** Inang yang diakui. Selain ini tidak dibaca sama sekali. */
    private const INANG = [
        'youtube.com', 'www.youtube.com', 'm.youtube.com',
        'youtu.be', 'www.youtu.be', 'youtube-nocookie.com',
        'www.youtube-nocookie.com',
    ];

    /** Jalur yang menaruh id-nya di ruas pertama sesudah nama jalur. */
    private const JALUR_BERID = ['embed', 'live', 'shorts', 'v', 'e'];

    /**
     * Id video dari tautan apa pun, atau null kalau bukan tautan YouTube.
     */
    public static function id(?string $tautan): ?string
    {
        $tautan = trim((string) $tautan);

        if ($tautan === '') {
            return null;
        }

        /*
         * Tanpa skema, parse_url() membaca "youtu.be/ID" sebagai JALUR, bukan
         * sebagai inang — host-nya null dan seluruh pembacaan di bawah gagal.
         * Admin yang menempelkan tautan tanpa "https://" bukan hal langka.
         */
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $tautan)) {
            $tautan = 'https://' . ltrim($tautan, '/');
        }

        $bagian = parse_url($tautan);

        if ($bagian === false || ! isset($bagian['host'])) {
            return null;
        }

        $inang = strtolower($bagian['host']);

        if (! in_array($inang, self::INANG, true)) {
            return null;
        }

        // youtu.be/ID — id-nya langsung jadi jalurnya.
        if (str_ends_with($inang, 'youtu.be')) {
            return self::saring(trim((string) ($bagian['path'] ?? ''), '/'));
        }

        // watch?v=ID
        if (isset($bagian['query'])) {
            parse_str($bagian['query'], $kueri);

            if (isset($kueri['v']) && ($id = self::saring((string) $kueri['v'])) !== null) {
                return $id;
            }
        }

        // /embed/ID, /live/ID, /shorts/ID, /v/ID
        $ruas = array_values(array_filter(explode('/', (string) ($bagian['path'] ?? ''))));

        if (count($ruas) >= 2 && in_array(strtolower($ruas[0]), self::JALUR_BERID, true)) {
            return self::saring($ruas[1]);
        }

        return null;
    }

    /** Tautan sematan yang aman dipasang di <iframe src>, atau null. */
    public static function sematkan(?string $tautan): ?string
    {
        $id = self::id($tautan);

        if ($id === null) {
            return null;
        }

        /*
         * youtube-nocookie.com, bukan youtube.com.
         *
         * Peserta membuka halaman ini SESUDAH masuk akun, jadi penonton yang
         * terbaca di sana adalah orang yang kita kenal namanya. Bentuk
         * -nocookie tidak menanam penanda iklan sampai videonya benar-benar
         * diputar, dan itu bawaan yang lebih sopan untuk halaman tertutup.
         *
         * rel=0 menahan "video berikutnya" dari kanal lain muncul di akhir.
         */
        return 'https://www.youtube-nocookie.com/embed/' . $id . '?rel=0';
    }

    /** Gambar mini video, untuk kartu di daftar rekaman. */
    public static function gambarMini(?string $tautan): ?string
    {
        $id = self::id($tautan);

        return $id === null ? null : 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
    }

    /** Tautan tontonan biasa, untuk tombol "buka di YouTube". */
    public static function tonton(?string $tautan): ?string
    {
        $id = self::id($tautan);

        return $id === null ? null : 'https://www.youtube.com/watch?v=' . $id;
    }

    /** Bisa dibaca sebagai tautan YouTube? Dipakai aturan validasi borang. */
    public static function sah(?string $tautan): bool
    {
        return self::id($tautan) !== null;
    }

    private static function saring(string $calon): ?string
    {
        return preg_match('/^' . self::POLA_ID . '$/', $calon) === 1 ? $calon : null;
    }
}
