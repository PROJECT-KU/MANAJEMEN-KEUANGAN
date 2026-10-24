<?php

namespace App\Support;

/**
 * Naskah kabar PERPINDAHAN STATUS — satu untuk dua saluran.
 *
 * Pasangan PesanTagihanWa, yang memegang satu keadaan saja ("menunggu
 * pembayaran"). Yang di sini memegang sisanya: pembayaran diterima, jadwal
 * dipindahkan, dana dikembalikan, pendaftaran ditolak atau dibatalkan,
 * pembayaran gagal di gerbang, dan batas waktunya terlewat.
 *
 * Dipakai dua pengirim, sama seperti naskah tagihannya:
 *
 *   1. KabariWhatsApp, yang mengirim sendiri lewat Fonnte;
 *   2. PesanWaPendaftaran, yang mengisi kotak ketik WhatsApp panitia.
 *
 * Sebelum ini keduanya punya naskah sendiri, dan selisihnya sudah terlihat:
 * yang otomatis memakai sapaan "Halo <nama>," dengan judul bercetak tebal
 * bergaris pisah, yang draf memakai "Yth. Bapak/Ibu" tanpa judul sama sekali.
 * Satu pendaftar bisa menerima keduanya dalam satu hari.
 *
 * ---------------------------------------------------------------------------
 * KALIMAT KEADAANNYA DIAMBIL DARI SUMBER SURATNYA
 * ---------------------------------------------------------------------------
 *
 * Pembukanya dibaca dari KabarStatusPendaftaran — sumber yang sama dengan
 * surat emailnya. Ditulis ulang di sini, dua saluran yang mengabarkan hal yang
 * sama akan berbeda bunyi perlahan, dan pendaftar yang membaca keduanya tidak
 * tahu mana yang berlaku.
 *
 * ---------------------------------------------------------------------------
 * "MOHON BALAS PESAN INI" TIDAK BOLEH MASUK KE PESAN OTOMATIS
 * ---------------------------------------------------------------------------
 *
 * Tiga dari enam penutup milik draf panitia berbunyi "mohon balas pesan ini".
 * Itu benar untuk draf — yang mengirimnya orang, dari nomor yang ia baca
 * sendiri. Di pesan otomatis ia membantah kakinya sendiri, yang baru saja
 * berkata "mohon jangan dibalas": yang membalas lalu menunggu jawaban yang
 * tidak akan pernah datang, sementara masalahnya tetap tidak tertangani.
 *
 * Karena itu $isi['bisa_dibalas']. Penjagaan lamanya melarang "Balas pesan
 * ini" berhuruf besar saja, dan kalimat berhuruf kecil ini lolos darinya —
 * jadi pengamannya sendiri ikut diperketat.
 */
class PesanKabarWa
{
    /**
     * Jenis kabar menurut NILAI STATUS mentahnya.
     *
     * Kelima layanan memakai kosakata yang berbeda di kolom varchar bebas,
     * jadi pemetaannya ditulis sebagai data. Nilai yang tidak terdaftar jatuh
     * ke 'lain', dan itu bukan keranjang sampah melainkan syarat: kolomnya
     * varchar, jadi layanan mana pun bisa menulis nilai baru kapan saja tanpa
     * migrasi — dan pesannya harus tetap terkirim utuh, bukan setengah jadi.
     *
     * @var array<string, string>
     */
    private const JENIS = [
        'Pendaftaran Diterima' => 'lunas',
        'paid' => 'lunas',
        'pembayaran diterima' => 'lunas',
        'completed' => 'lunas',

        'Pendaftaran Reschedule' => 'jadwal',

        'Pendaftaran Refund' => 'refund',

        'Pendaftaran Ditolak' => 'ditolak',
        'pembayaran ditolak' => 'ditolak',

        'Pendaftaran Dibatalkan' => 'dibatalkan',
        'cancel' => 'dibatalkan',
        'canceled' => 'dibatalkan',

        'expired' => 'kedaluwarsa',
    ];

    /**
     * Judul per jenis.
     *
     * Emojinya PENANDA, bukan hiasan: di daftar percakapan WhatsApp yang
     * terlihat hanya baris pertama, dan penerimanya sudah tahu kabar apa yang
     * datang sebelum membukanya. 🎉 dan ⚠️ tidak tertukar sekilas pandang.
     *
     * @var array<string, string>
     */
    private const JUDUL = [
        'lunas' => '🎉 *Pembayaran Anda sudah kami terima!*',
        'jadwal' => '📅 *Jadwal pendaftaran Anda kami pindahkan*',
        'refund' => '💸 *Dana Anda kami kembalikan*',
        'ditolak' => '⚠️ *Pendaftaran Anda belum dapat kami terima*',
        'dibatalkan' => '⚠️ *Pendaftaran Anda dibatalkan*',
        'gagal_bayar' => '⚠️ *Pembayaran Anda belum berhasil*',
        'kedaluwarsa' => '⏳ *Batas waktu pembayarannya telah terlewat*',
        'lain' => 'ℹ️ *Kabar pendaftaran Anda*',
    ];

    public static function jenisDariStatus(?string $status): string
    {
        return self::JENIS[trim((string) $status)] ?? 'lain';
    }

    /**
     * @param  array{
     *     jenis: string,
     *     status: ?string,
     *     nama: ?string,
     *     layanan: ?string,
     *     kelas: ?string,
     *     nomor: ?string,
     *     jumlah: int,
     *     total: int,
     *     kanal: ?string,
     *     email: ?string,
     *     kelanjutan: ?string,
     *     grup: ?string,
     *     tautan_daftar: ?string,
     *     perkenalan: ?string,
     *     bisa_dibalas: bool,
     * }  $isi
     * @return array<int, string>
     */
    public static function baris(array $isi): array
    {
        $jenis = (string) ($isi['jenis'] ?? 'lain');

        $baris = [
            self::JUDUL[$jenis] ?? self::JUDUL['lain'],
            '',
            '📩 Halo Bapak/Ibu ' . PesanTagihanWa::namaSapaan($isi['nama'] ?? null),
        ];

        /*
         * Perkenalannya SESUDAH sapaan dan SEBELUM kabarnya: orang
         * memperkenalkan diri lebih dulu, baru menyampaikan urusannya.
         * Terbalik, ia terbaca seperti tempelan — dan di pesan pengembalian
         * dana ia menyusup di antara kabar buruk dan rinciannya.
         */
        $perkenalan = trim((string) ($isi['perkenalan'] ?? ''));

        if ($perkenalan !== '') {
            $baris[] = $perkenalan;
        }

        $pembuka = self::pembuka($isi, $jenis);

        if ($pembuka !== null) {
            $baris[] = $pembuka;
        }

        $rincian = self::rincian($isi, $jenis);

        if ($rincian !== []) {
            $baris[] = '';

            foreach ($rincian as $r) {
                $baris[] = '• ' . $r;
            }
        }

        foreach ([self::catatan($isi, $jenis), self::langkah($isi, $jenis)] as $blok) {
            if ($blok !== []) {
                $baris[] = '';
                $baris = [...$baris, ...$blok];
            }
        }

        $baris[] = '';
        $baris[] = self::penutup($isi, $jenis);

        $baris[] = '';

        return [...$baris, ...PesanTagihanWa::penutup($jenis)];
    }

    public static function rakit(array $isi): string
    {
        return implode("\n", self::baris($isi));
    }

    // ------------------------------------------------------------ bagiannya

    /**
     * Kalimat keadaannya, dari sumber yang sama dengan suratnya.
     *
     * 'gagal_bayar' tidak punya nilai status sama sekali — ia kejadian di
     * gerbang pembayaran, bukan status yang tersimpan — jadi kalimatnya
     * dirakit di sini. Satu-satunya yang begitu.
     */
    private static function pembuka(array $isi, string $jenis): ?string
    {
        if ($jenis === 'gagal_bayar') {
            /*
             * Nomornya TIDAK disebut di kalimat ini: butir rinciannya di
             * bawah sudah menyebutnya, dan dua kali dalam empat baris
             * membuat pesannya terbaca seperti rakitan mesin yang ceroboh.
             */
            return 'Pembayaran Anda tidak selesai sehingga tempatnya kami lepas kembali.';
        }

        $kabar = KabarStatusPendaftaran::untuk((string) ($isi['status'] ?? ''));

        if ($kabar !== null) {
            return $kabar['pembuka'];
        }

        /*
         * Status yang belum punya kalimatnya tidak menghasilkan pesan
         * setengah jadi: pembukanya dilewati, rinciannya tetap terkirim.
         * Panitia masih dapat mengetik sisanya sendiri — jauh lebih baik
         * daripada tombol yang mati.
         */
        return null;
    }

    /** @return array<int, string> */
    private static function rincian(array $isi, string $jenis): array
    {
        $butir = [];

        $nomor = trim((string) ($isi['nomor'] ?? ''));

        if ($nomor !== '') {
            $butir[] = 'Nomor pendaftaran: *' . mb_strtoupper($nomor) . '*';
        }

        $layanan = trim((string) ($isi['layanan'] ?? ''));
        $kelas = trim((string) ($isi['kelas'] ?? ''));

        if ($layanan !== '' || $kelas !== '') {
            $butir[] = 'Pendaftaran: *' . PesanTagihanWa::sebutanLayanan($isi) . '*';
        }

        $orang = (int) ($isi['jumlah'] ?? 1);

        if ($orang > 1) {
            $butir[] = 'Jumlah peserta: *' . $orang . ' orang*';
        }

        $total = max(0, (int) ($isi['total'] ?? 0));

        /*
         * Nominalnya TIDAK ditampilkan pada penolakan dan pembatalan.
         *
         * Sebagian yang ditolak belum pernah membayar apa pun, dan angka
         * yang berdiri sendiri di pesan penolakan terbaca sebagai tagihan —
         * terutama oleh yang memang belum bayar, yang lalu mentransfer untuk
         * pendaftaran yang sudah tidak ada.
         */
        if ($total > 0 && ! in_array($jenis, ['ditolak', 'dibatalkan'], true)) {
            /*
             * Labelnya BERBEDA per jenis, dan itu bukan kerapian: kata
             * "total pembayaran" di pesan pelunasan membuat orang yang sudah
             * membayar mengira masih ditagih, dan di pesan pengembalian dana
             * ia tidak menunjukkan berapa yang akan ia terima.
             */
            $label = match ($jenis) {
                'lunas' => 'Jumlah yang diterima',
                'refund' => 'Dana yang dikembalikan',
                default => 'Total pembayaran',
            };

            $butir[] = $label . ': *Rp ' . number_format($total, 0, ',', '.') . '*';
        }

        /*
         * Cara bayarnya hanya di pesan pelunasan, dan hanya kalau kanalnya
         * benar-benar diketahui — "VA Mandiri", "QRIS", "Transfer bank".
         *
         * NAMA GERBANGNYA TIDAK PERNAH DISEBUT. Itu urusan internal, dan
         * nilai mentah kolomnya memang berbunyi "doku": dicetak apa adanya,
         * pesan ini berbohong pada hari gerbangnya diganti, dan yang
         * menerimanya mencari halaman yang sudah tidak dipakai.
         */
        $kanal = trim((string) ($isi['kanal'] ?? ''));

        if ($jenis === 'lunas' && $kanal !== '') {
            $butir[] = 'Cara bayar: *' . $kanal . '*';
        }

        return $butir;
    }

    /** @return array<int, string> */
    private static function catatan(array $isi, string $jenis): array
    {
        if ($jenis === 'gagal_bayar') {
            return [
                '📌 *Catatan penting:*',

                /*
                 * "Bukan kesalahan Anda" ada karena yang paling sering
                 * terjadi memang bukan: limit kartu, saldo VA yang belum
                 * sinkron, sesi gerbang yang kedaluwarsa. Tanpa kalimat itu,
                 * sebagian orang berhenti mencoba karena mengira datanya
                 * yang bermasalah.
                 */
                'Ini biasanya urusan bank atau kartunya — bukan kesalahan Anda, dan '
                    . 'dapat dicoba lagi sekarang juga.',
                '',
                'Apabila saldo Anda sempat terpotong, dana yang tertahan dikembalikan '
                    . 'sendiri oleh bank dalam beberapa hari kerja.',
            ];
        }

        if ($jenis === 'kedaluwarsa') {
            return [
                '📌 *Catatan penting:*',

                /*
                 * Dibaca banyak orang yang MEMANG sudah membayar — mutasinya
                 * belum tercocokkan, atau pembayarannya masuk beberapa menit
                 * sesudah tenggatnya. Tanpa kalimat ini, pesannya terbaca
                 * sebagai tuduhan, dan yang pertama ia kerjakan adalah
                 * marah, bukan menghubungi kami.
                 */
                'Merasa sudah membayar? Sebutkan nomor pendaftaran di atas kepada admin '
                    . 'resmi kami — pembayarannya tidak hilang, hanya konfirmasinya '
                    . 'yang tertunda.',
            ];
        }

        if ($jenis === 'refund') {
            return [
                '📌 *Catatan penting:*',
                'Dananya kami kembalikan melalui jalur pembayaran yang Anda gunakan, '
                    . 'dan prosesnya mengikuti jadwal banknya.',
            ];
        }

        return [];
    }

    /** @return array<int, string> */
    private static function langkah(array $isi, string $jenis): array
    {
        $daftar = trim((string) ($isi['tautan_daftar'] ?? ''));
        $grup = trim((string) ($isi['grup'] ?? ''));

        if ($jenis === 'lunas' || $jenis === 'jadwal') {
            $blok = ['✅ *Langkah selanjutnya:*'];

            $email = trim((string) ($isi['email'] ?? ''));

            if ($jenis === 'lunas') {
                $blok[] = 'Kwitansinya kami kirimkan ke '
                    . ($email !== '' ? $email : 'alamat email Anda') . '.';
            }

            $kelanjutan = trim((string) ($isi['kelanjutan'] ?? ''));

            if ($kelanjutan !== '') {
                $blok[] = $kelanjutan;
            }

            if ($grup !== '') {
                $blok[] = '';
                $blok[] = 'Silakan bergabung ke grup peserta melalui tautan berikut:';

                /*
                 * Alamatnya BERDIRI SENDIRI di barisnya, tanpa titik di
                 * ujungnya. WhatsApp menebak batas tautan dari spasi, dan
                 * tanda baca yang menempel ikut termakan ke dalam alamat —
                 * yang ditekan pendaftar lalu alamat yang tidak ada.
                 */
                $blok[] = $grup;
            }

            return count($blok) > 1 ? $blok : [];
        }

        /*
         * Jalan masuk kembali, untuk yang pendaftarannya tidak jadi.
         *
         * Hanya kalau alamatnya memang ada: rute borang tiga layanan menuntut
         * parameter angkatan, dan yang angkatannya sudah terhapus tidak punya
         * alamat yang sah. Satu kalimat yang hilang jauh lebih baik daripada
         * satu tautan yang mengantar ke halaman galat.
         */
        if ($daftar === '' || ! in_array($jenis, ['gagal_bayar', 'kedaluwarsa', 'dibatalkan'], true)) {
            return [];
        }

        return [
            '✅ *Langkah selanjutnya:*',
            $jenis === 'gagal_bayar'
                ? 'Silakan mencoba kembali melalui tautan berikut:'
                : 'Tempatnya dapat diambil lagi selama kuotanya belum penuh:',
            $daftar,
        ];
    }

    /**
     * Kalimat penutup per jenis.
     *
     * "Mohon balas pesan ini" HANYA untuk draf panitia. Di pesan otomatis ia
     * membantah kakinya sendiri — lihat keterangan kelas di atas.
     */
    private static function penutup(array $isi, string $jenis): string
    {
        $balas = (bool) ($isi['bisa_dibalas'] ?? false);

        $hubungi = $balas
            ? 'mohon balas pesan ini'
            : 'mohon hubungi admin resmi kami di nomor yang tertera di bawah';

        return match ($jenis) {
            'lunas' => 'Mohon simpan pesan ini sebagai bukti. Sampai berjumpa di acara.',
            'jadwal' => 'Apabila jadwal baru tersebut tidak dapat Anda ikuti, '
                . $hubungi . ' agar kami carikan solusinya.',
            'refund' => 'Apabila dalam satu minggu dananya belum juga masuk, '
                . $hubungi . ' agar kami telusuri.',
            'ditolak', 'dibatalkan' => 'Apabila menurut Anda terdapat kekeliruan, '
                . $hubungi . ' agar panitia memeriksanya kembali.',
            default => 'Apabila ada yang ingin ditanyakan, ' . $hubungi . '.',
        };
    }
}
