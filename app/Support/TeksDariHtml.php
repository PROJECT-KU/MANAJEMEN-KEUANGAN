<?php

namespace App\Support;

/**
 * Mengubah deskripsi yang berisi HTML jadi teks datar berbaris.
 *
 * Deskripsi angkatan ditampilkan halaman publik dengan {{ }}, bukan {!! !!} —
 * jadi HTML yang tertempel di sana TIDAK dirender, melainkan terbaca pengunjung
 * apa adanya: "<h2 data-start="151">📚 Pelatihan…</h2>". Empat angkatan
 * Bibliometrik memang begitu isinya, hasil tempel dari ChatGPT lengkap dengan
 * atribut data-start dan satu ikon SVG.
 *
 * Wadahnya sudah pakai white-space: pre-line di kedua halaman publik, jadi
 * pergantian baris teks datar tampil sebagaimana mestinya. Itu sebabnya
 * ubahannya baris, bukan spasi.
 *
 * Yang HTML-nya sudah bersih dibiarkan utuh, tidak ikut dilewatkan perapian —
 * teks yang sengaja diberi baris kosong ganda oleh admin tidak boleh dirapatkan
 * diam-diam hanya karena disimpan ulang.
 */
class TeksDariHtml
{
    /** Penutupnya jadi baris kosong: ini elemen yang berdiri sendiri. */
    private const BLOK = 'p|div|h[1-6]|blockquote|section|article|tr|table|figure';

    public static function ubah(?string $html): string
    {
        $teks = (string) $html;

        if (trim($teks) === '' || ! self::berhtml($teks)) {
            return $teks;
        }

        /*
         * Akhir baris diseragamkan PALING AWAL. Empat baris pengumuman ini
         * ber-CRLF (dari Word) dan sisanya LF; aturan <br> di bawah cuma
         * menelan \n, jadi tanpa penyeragaman ini delapan butir jadwal di dua
         * angkatan terpisah baris kosong satu-satu sementara di dua angkatan
         * lain berurutan rapi.
         */
        $teks = str_replace(["\r\n", "\r"], "\n", $teks);

        // Elemen yang tidak punya isi terbaca dibuang beserta isinya. SVG ikut
        // di sini: isinya koordinat path, dan dibiarkan ia jadi deretan angka.
        $teks = preg_replace('#<(svg|script|style)\b[^>]*>.*?</\1\s*>#is', '', $teks);

        $teks = self::tautan($teks);

        // Daftar diurus sebelum penutup blok, sebab <li> berisi <p> dan
        // penutup itu akan memecah satu butir jadi beberapa baris.
        $teks = preg_replace_callback(
            '#<ol\b[^>]*>(.*?)</ol\s*>#is',
            fn ($c) => "\n" . self::butir($c[1], true) . "\n",
            $teks
        );
        $teks = preg_replace_callback(
            '#<ul\b[^>]*>(.*?)</ul\s*>#is',
            fn ($c) => "\n" . self::butir($c[1], false) . "\n",
            $teks
        );

        /*
         * Pergantian baris yang menempel SESUDAH <br> di sumbernya ikut
         * ditelan. HTML menganggapnya spasi tak berarti, jadi dibiarkan ia
         * menumpuk jadi dua baris dan empat butir "✅ …" yang seharusnya
         * berurutan malah terpisah baris kosong satu-satu.
         */
        $teks = preg_replace('#[ \t]*<br\b[^>]*>[ \t]*\n?#i', "\n", $teks);
        $teks = preg_replace('#</(' . self::BLOK . ')\s*>#i', "\n\n", $teks);
        $teks = preg_replace('#<(' . self::BLOK . ')\b[^>]*>#i', "\n", $teks);

        return self::rapikan($teks);
    }

    /** Ada tanda HTML di dalamnya: tag pembuka, tag penutup, atau entitas. */
    private static function berhtml(string $teks): bool
    {
        return (bool) preg_match('#<\s*/?[a-zA-Z][a-zA-Z0-9]*[\s/>]#', $teks)
            || (bool) preg_match('#&(?:[a-zA-Z][a-zA-Z0-9]{1,30}|\#[0-9]{1,6}|\#x[0-9a-fA-F]{1,6});#', $teks);
    }

    /**
     * Teks tautan ditulis lengkap dengan alamatnya.
     *
     * Di teks datar, tautan yang cuma menyisakan tulisannya membuang satu-satunya
     * hal yang bisa dipakai pembaca. Alamat yang SUDAH tertulis di dalam
     * tulisannya tidak ditulis dua kali — "www.rumahscopus.com" tidak perlu
     * jadi "www.rumahscopus.com (https://www.rumahscopus.com)".
     */
    private static function tautan(string $teks): string
    {
        return preg_replace_callback(
            '#<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)</a\s*>#is',
            function ($c) {
                $alamat = trim($c[2]);
                $isi = trim(preg_replace('/\s+/u', ' ', strip_tags($c[3])));

                if ($alamat === '' || str_starts_with($alamat, '#')) {
                    return $isi;
                }

                $ringkas = preg_replace('#^(?:https?://)?(?:www\.)?#i', '', rtrim($alamat, '/'));

                return $isi === '' || stripos($isi, $ringkas) !== false
                    ? ($isi !== '' ? $isi : $alamat)
                    : $isi . ' (' . $alamat . ')';
            },
            $teks
        );
    }

    /**
     * Satu butir jadi satu baris, bernomor seperti daftar fasilitas di
     * PerakitDeskripsi supaya kedua sumber deskripsi terlihat sama.
     */
    private static function butir(string $isi, bool $bernomor): string
    {
        preg_match_all('#<li\b[^>]*>(.*?)</li\s*>#is', $isi, $cocok);

        $baris = [];

        foreach ($cocok[1] as $n => $satu) {
            // Tag dalam butir jadi spasi, bukan baris: nomornya sudah menandai
            // awal butir, dan baris tambahan di tengahnya memutus penandaan itu.
            // Entitas SENGAJA dibiarkan, baru disandikan sekali di rapikan().
            $datar = trim(preg_replace('/\s+/u', ' ', preg_replace('#<[^>]+>#', ' ', $satu)));

            if ($datar === '') {
                continue;
            }

            $baris[] = ($bernomor ? ($n + 1) . '. ' : '• ') . $datar;
        }

        return implode("\n", $baris);
    }

    private static function rapikan(string $teks): string
    {
        $teks = strip_tags($teks);

        // Sekali saja: disandikan dua kali, "&amp;lt;" yang memang ingin
        // ditulis admin berubah jadi tag.
        $teks = html_entity_decode($teks, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Spasi tak-putus dari penempelan Word terlihat seperti spasi biasa
        // tetapi tidak ikut dipotong trim, jadi baris "kosong" tetap tersisa.
        $teks = str_replace(["\xC2\xA0", "\r\n", "\r"], [' ', "\n", "\n"], $teks);

        /*
         * Spasi rangkap dalam satu baris dirapatkan. HTML memang merapatkannya
         * sendiri saat dirender, jadi di teks datar ia jadi lubang yang
         * kelihatan — "Rp 999.000  👉 Rp 699.000" menyisakan dua spasi di bekas
         * <del> yang dibuang.
         */
        $baris = array_map(
            fn ($b) => rtrim(preg_replace('/[ \t]{2,}/', ' ', $b)),
            explode("\n", $teks)
        );

        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", $baris)));
    }
}
