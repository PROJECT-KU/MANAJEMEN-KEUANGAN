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
        '{durasi}' => 'Lama acara dihitung dari tanggalnya, mis. "3 hari 2 malam"',
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
            '{durasi}' => self::durasi($angkatan),
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

        foreach (self::tanpaBlokKosong($cetakan, $nilai) as $baris) {
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
     * Membuang BLOK yang seluruh penandanya kosong, berikut judulnya.
     *
     * Aturan per baris di bawah hanya membuang baris yang berpenanda. Judul
     * seperti "🔹 Yang dipelajari" tidak berpenanda, jadi ia bertahan — dan
     * untuk layanan yang daftar kegiatannya masih kosong, judul itu
     * menggantung tanpa isi apa pun di bawahnya. Begitu juga "📞 Kontak" pada
     * tarif yang kontaknya belum diisi.
     *
     * Blok di sini artinya kumpulan baris yang dipisahkan baris kosong —
     * persis cara orang membaca alinea. Blok dibuang hanya kalau ia MEMANG
     * punya penanda dan semuanya kosong; blok tanpa penanda sama sekali, yang
     * berarti kalimat yang sengaja ditulis admin, tidak pernah disentuh.
     *
     * @param  array<string, string>  $nilai
     * @return array<int, string>
     */
    private static function tanpaBlokKosong(string $cetakan, array $nilai): array
    {
        $baris = preg_split('/\r\n|\r|\n/', $cetakan);

        $keluar = [];
        $blok = [];

        $tuntaskan = function () use (&$keluar, &$blok, $nilai) {
            if ($blok === []) {
                return;
            }

            preg_match_all('/\{[a-z_]+\}/', implode("\n", $blok), $ada);
            $penanda = array_filter($ada[0], fn ($p) => array_key_exists($p, $nilai));

            if ($penanda === [] || ! self::semuaKosong($penanda, $nilai)) {
                foreach ($blok as $b) {
                    $keluar[] = $b;
                }
            }

            $blok = [];
        };

        foreach ($baris as $b) {
            if (trim($b) === '') {
                $tuntaskan();
                $keluar[] = $b;

                continue;
            }

            $blok[] = $b;
        }

        $tuntaskan();

        return $keluar;
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

    /**
     * Lama acara, dihitung dari tanggalnya.
     *
     * Sebelum ini cetakan Scopus Camp menulis "Selama 3 hari 2 malam" apa
     * adanya. Angkanya lalu ikut tercetak di angkatan yang tanggalnya 1–2
     * Oktober — dua hari, bukan tiga — dan pembacanya tidak punya cara tahu
     * mana yang benar.
     */
    private static function durasi(KategoriLayanan $angkatan): string
    {
        if (! $angkatan->mulai) {
            return '';
        }

        $mulai = Carbon::parse($angkatan->mulai)->startOfDay();
        $selesai = $angkatan->selesai ? Carbon::parse($angkatan->selesai)->startOfDay() : $mulai;

        if ($selesai->lessThan($mulai)) {
            return '';
        }

        $hari = $mulai->diffInDays($selesai) + 1;

        if ($hari <= 1) {
            return 'sehari penuh';
        }

        // "malam" dihitung dari jumlah pergantian hari, bukan dari jumlah hari:
        // acara tiga hari menginap dua malam.
        return $hari . ' hari ' . ($hari - 1) . ' malam';
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
