<?php

namespace App\Support;

/**
 * Membaca daftar peserta rombongan — nama DAN nomor WhatsApp-nya — dari teks
 * yang ditempel panitia atau dari berkas Excel/CSV yang dikirim lembaga.
 *
 * Sebelum ini yang tercatat hanya namanya. Akibatnya rombongan tujuh orang
 * hanya punya SATU nomor yang bisa dihubungi, yaitu nomor pemesannya: undangan
 * grup, pengingat jadwal, dan tautan sertifikat harus lewat satu orang itu,
 * dan kalau ia tidak meneruskannya, enam orang lain tidak pernah tahu.
 *
 * SATU kelas untuk kedua jalur masuknya, bukan dua. Teks tempelan dan berkas
 * Excel memuat hal yang sama persis, dan dua pembaca terpisah akan berbeda
 * perlahan — lalu "Budi, 0812..." terbaca benar saat diketik dan salah saat
 * diunggah, tanpa ada yang bisa menjelaskan kenapa.
 *
 * Nomornya dinormalkan lewat NomorTelepon supaya bentuknya sama dengan nomor
 * pendaftar utama di kelima tabel pendaftaran; kalau tidak, dua nomor yang
 * sama orangnya tersimpan dalam dua bentuk dan pencarian hanya menemukan satu.
 */
class DaftarPeserta
{
    /** Sepanjang kolom `nama` di tabelnya. */
    public const PANJANG_NAMA = 255;

    /**
     * Pemisah yang dicoba berurutan, tab lebih dulu.
     *
     * Tab bukan pilihan yang dibuat-buat: menempelkan dua kolom langsung dari
     * Excel ke kotak teks menghasilkan tab, dan itu cara tercepat yang
     * sebenarnya dipakai orang — tanpa menyimpan berkasnya dulu.
     */
    private const PEMISAH = ["\t", ';', '|', ','];

    /** Yang dipakai saat daftarnya ditulis balik ke kotak teksnya. */
    public const PEMISAH_TULIS = ', ';

    /**
     * Kata yang berarti barisnya judul kolom, bukan orang.
     *
     * Berkas dari lembaga hampir selalu berjudul kolom. Tanpa ini, "Nama"
     * tersimpan sebagai peserta pertama dan nomor urut sertifikatnya meleset
     * satu untuk semua orang.
     */
    private const JUDUL_KOLOM = ['nama', 'name', 'nama lengkap', 'nama peserta', 'peserta', 'no', 'no.'];

    /**
     * Membaca teks yang ditempel panitia, satu orang per baris.
     *
     * @return list<array{nama: string, telp: string}>
     */
    public static function dariTeks(?string $teks): array
    {
        $hasil = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $teks) ?: [] as $baris) {
            $orang = self::satuBaris((string) $baris);

            if ($orang !== null) {
                $hasil[] = $orang;
            }
        }

        return $hasil;
    }

    /**
     * Membaca baris berkas Excel/CSV, satu larik sel per orang.
     *
     * Kolomnya TIDAK ditentukan dari judulnya, melainkan dari isinya: sel yang
     * bentuknya nomor telepon jadi nomor, sel berisi huruf pertama jadi nama.
     * Berkas yang dikirim lembaga tidak pernah berjudul sama — "No HP", "No.
     * WA", "Telepon", "Kontak" — dan mencocokkan judul berarti berkas yang
     * judulnya di luar daftar kehilangan seluruh nomornya diam-diam.
     *
     * @param  array<int, array<int, mixed>>  $baris
     * @return list<array{nama: string, telp: string}>
     */
    public static function dariBaris(array $baris): array
    {
        $hasil = [];

        foreach ($baris as $sel) {
            if (! is_array($sel)) {
                continue;
            }

            $nama = '';
            $telp = '';

            foreach ($sel as $isi) {
                $teks = trim((string) $isi);

                if ($teks === '') {
                    continue;
                }

                if ($telp === '' && self::sepertiNomor($teks)) {
                    $telp = NomorTelepon::rapikan($teks);

                    continue;
                }

                /*
                 * Nomor urut di kolom pertama dilewati, bukan dipakai jadi
                 * nama. Hampir semua berkas lembaga punya kolom "No" berisi
                 * 1, 2, 3 — dan tanpa ini seluruh pesertanya bernama angka.
                 */
                if ($nama === '' && ! preg_match('/^\d+[.)]?$/', $teks)) {
                    $nama = $teks;
                }
            }

            if ($nama === '' && $telp === '') {
                continue;
            }

            if (self::judulKolom($nama)) {
                continue;
            }

            $hasil[] = [
                'nama' => mb_substr($nama !== '' ? $nama : $telp, 0, self::PANJANG_NAMA),
                'telp' => $telp,
            ];
        }

        return $hasil;
    }

    /**
     * Menulis daftarnya kembali ke bentuk yang muncul di kotak teksnya.
     *
     * Dipakai sesudah berkas dibaca: panitia MELIHAT apa yang terbaca dan bisa
     * membetulkannya sebelum menyimpan. Mengirim isi berkas langsung ke basis
     * data berarti salah baca baru ketahuan di hari acara.
     *
     * Nomornya ditulis balik dalam bentuk 08xx, bukan 62xx yang disimpan.
     * Keduanya nomor yang sama, tetapi panitia yang baru saja mengetik
     * "081234567890" lalu melihatnya berubah jadi "6281234567890" akan
     * mengira berkasnya salah terbaca dan membetulkannya kembali.
     *
     * @param  list<array{nama: string, telp: string}>  $orang
     */
    public static function sebagaiTeks(array $orang): string
    {
        return implode("\n", array_map(
            fn ($o) => trim((string) ($o['telp'] ?? '')) !== ''
                ? $o['nama'] . self::PEMISAH_TULIS . self::bentukLokal((string) $o['telp'])
                : $o['nama'],
            $orang
        ));
    }

    /** 6281... -> 0811...; bentuk yang dibaca orang di Indonesia. */
    public static function bentukLokal(string $telp): string
    {
        return str_starts_with($telp, '62') ? '0' . substr($telp, 2) : $telp;
    }

    /** @return array{nama: string, telp: string}|null */
    private static function satuBaris(string $baris): ?array
    {
        // Penomoran yang ikut tersalin dari WhatsApp ("1. Budi") dibuang;
        // nama orang tidak berawalan angka dan titik.
        $bersih = trim(preg_replace('/^\s*\d+\s*[.)-]\s*/', '', $baris));

        if ($bersih === '' || self::judulKolom(self::kepala($bersih))) {
            return null;
        }

        foreach (self::PEMISAH as $pemisah) {
            if (! str_contains($bersih, $pemisah)) {
                continue;
            }

            $bagian = array_map('trim', explode($pemisah, $bersih));

            foreach ($bagian as $ke => $isi) {
                if (! self::sepertiNomor($isi)) {
                    continue;
                }

                /*
                 * Bagian yang TERSISA disatukan lagi jadi namanya, bukan
                 * bagian pertama saja: "Santoso, Budi, 0812..." tetap bernama
                 * "Santoso, Budi", dan kolom ketiga berisi keterangan lain
                 * tidak membuang namanya.
                 */
                unset($bagian[$ke]);

                return self::rakit(implode($pemisah === "\t" ? ' ' : $pemisah . ' ', $bagian), $isi);
            }

            // Ada pemisahnya tetapi tidak satu pun bagiannya nomor — berarti
            // pemisahnya bagian dari namanya. Dicoba pemisah berikutnya.
        }

        /*
         * Tanpa pemisah: nomor yang menempel di ujung baris, seperti "Budi
         * Santoso 081234567890". Diharuskan berawalan 0, 62, atau +62 — tanpa
         * syarat itu, "Angkatan 2" terbaca sebagai orang bernama "Angkatan"
         * bernomor 2.
         */
        if (preg_match('/^(?<nama>.*?)[\s,;:|\-–—]*(?<telp>(?:\+?62|0)[\d\s\-().]{7,})$/u', $bersih, $cocok)) {
            if (self::sepertiNomor($cocok['telp'])) {
                return self::rakit($cocok['nama'], $cocok['telp']);
            }
        }

        return self::rakit($bersih, '');
    }

    /** @return array{nama: string, telp: string} */
    private static function rakit(string $nama, string $telp): array
    {
        $nama = trim($nama);
        $telp = NomorTelepon::rapikan($telp);

        /*
         * Baris yang isinya HANYA nomor tetap disimpan, namanya diisi
         * nomornya. Membuangnya berarti satu kursi yang sudah dibayar hilang
         * tanpa jejak; disimpan begini, panitia melihatnya di halaman rincian
         * dan bisa membetulkan namanya.
         */
        if ($nama === '') {
            $nama = $telp;
        }

        return [
            'nama' => mb_substr($nama, 0, self::PANJANG_NAMA),
            'telp' => $telp,
        ];
    }

    /**
     * Bentuknya nomor telepon Indonesia yang masuk akal.
     *
     * Dua syarat, bukan satu: isinya hanya angka dan tanda pemisah, DAN
     * panjangnya di rentang yang wajar. Tanpa syarat pertama, "Juara 2 Lomba
     * 2024" lolos sebab angkanya cukup banyak.
     */
    private static function sepertiNomor(string $teks): bool
    {
        $teks = trim($teks);

        if ($teks === '' || ! preg_match('/^[+\d\s\-().]+$/', $teks)) {
            return false;
        }

        return NomorTelepon::masukAkal($teks);
    }

    /**
     * Isi kolom pertama baris itu, apa pun pemisahnya.
     *
     * Dipakai menyaring baris judul yang ikut tersalin saat tabel Excel
     * ditempel langsung ke kotak teksnya — "Nama<tab>No HP" tidak punya bagian
     * yang berbentuk nomor, jadi tanpa ini ia tersimpan sebagai peserta
     * pertama dan nomor urut sertifikat semua orang meleset satu.
     */
    private static function kepala(string $baris): string
    {
        foreach (self::PEMISAH as $pemisah) {
            if (str_contains($baris, $pemisah)) {
                return trim(explode($pemisah, $baris)[0]);
            }
        }

        return $baris;
    }

    private static function judulKolom(string $nama): bool
    {
        return in_array(mb_strtolower(trim($nama)), self::JUDUL_KOLOM, true);
    }
}
