<?php

namespace App\Support;

use App\PromoLayanan;
use Illuminate\Support\Carbon;

/**
 * Naskah "pendaftaran berhasil, silakan bayar" — SATU untuk dua saluran.
 *
 * Dipakai dua pengirim yang sama sekali berbeda cara kerjanya:
 *
 *   1. KabariWhatsApp, yang mengirim sendiri lewat Fonnte begitu borangnya
 *      tersimpan;
 *   2. PesanWaPendaftaran, yang mengisi kotak ketik WhatsApp panitia sebagai
 *      DRAF untuk ditindaklanjuti dengan tangan.
 *
 * Satu naskah, bukan dua, dan itu permintaan pemiliknya: pendaftar yang
 * menerima pesan otomatis lalu ditindaklanjuti panitia menerima dua pesan
 * tentang satu hal, dan kalau keduanya berbunyi berbeda ia bertanya mana
 * yang berlaku — paling sering soal nominal, yang justru satu-satunya angka
 * yang tidak boleh diragukan.
 *
 * Bentuknya mengikuti naskah yang sudah dipakai panitia sehari-hari (ucapan
 * selamat, langkah selanjutnya, catatan penting, penutup "Salam Q1"), dengan
 * satu perubahan yang diminta: transfer ke rekening diganti tautan
 * pembayaran.
 *
 * ---------------------------------------------------------------------------
 * YANG TIDAK DIKARANG
 * ---------------------------------------------------------------------------
 *
 * Naskah aslinya memuat "kode kupon hanya berlaku untuk 20 pendaftar
 * pertama" dan "harga normal Rp 1.875.000". Dua angka itu TIDAK ditulis
 * tetap di sini:
 *
 *   - batas pendaftarnya dibaca dari promo_layanan.kuota, dan terukur 24 Okt
 *     2026 keempat promo yang ada BERNILAI NULL. Ditulis tetap "20", pesannya
 *     mengancam dengan batas yang tidak pernah ditegakkan sistemnya sendiri —
 *     lalu yang terlambat tetap mendapat harga diskon, dan ancaman kita
 *     berikutnya tidak lagi dipercaya;
 *
 *   - harga normalnya dihitung dari total + potongannya, bukan diketik. Harga
 *     tiap angkatan berbeda, dan satu angka yang diketik di sini akan salah
 *     untuk semua angkatan selain satu.
 *
 * Yang tidak diketahui DILEWATI, bukan ditebak: satu kalimat yang hilang
 * tidak merugikan siapa pun, sedangkan satu angka yang salah dalam pesan
 * tagihan dibayar orang.
 */
class PesanTagihanWa
{
    /**
     * @param  array{
     *     nama: ?string,
     *     layanan: ?string,
     *     kelas: ?string,
     *     nomor: ?string,
     *     jumlah: int,
     *     total: int,
     *     diskon: int,
     *     kode_diskon: ?string,
     *     promo: ?string,
     *     tenggat: mixed,
     *     tautan: ?string,
     *     bergerbang: bool,
     *     diperiksa: bool,
     *     mulai: mixed,
     *     perkenalan: ?string,
     * }  $isi
     * @return array<int, string>
     */
    public static function baris(array $isi): array
    {
        $total = max(0, (int) ($isi['total'] ?? 0));
        $diskon = max(0, (int) ($isi['diskon'] ?? 0));
        $berdiskon = $diskon > 0 || trim((string) ($isi['kode_diskon'] ?? '')) !== '';

        $baris = [
            $berdiskon
                ? '🎉 *Selamat, pendaftaran Anda dengan diskon sudah berhasil!*'
                : '🎉 *Selamat, pendaftaran Anda sudah berhasil!*',
            '',
            '📩 Halo Bapak/Ibu ' . self::sapaan($isi['nama'] ?? null),
            'Terima kasih, kami telah menerima detail pendaftaran Anda untuk *'
                . self::sebutan($isi) . '* 🙏',
        ];

        /*
         * Perkenalan HANYA untuk draf yang dikirim panitia dengan tangan.
         *
         * Pesan otomatis tidak memakainya: tidak ada orang yang sedang
         * menulisnya, dan kakinya sendiri sudah berkata pesan itu dikirim
         * otomatis. "Perkenalkan, saya admin" di pesan mesin adalah
         * kalimat yang langsung dibantah kalimat di bawahnya.
         */
        $perkenalan = trim((string) ($isi['perkenalan'] ?? ''));

        if ($perkenalan !== '') {
            $baris[] = $perkenalan;
        }

        $rincian = self::rincian($isi, $total);

        if ($rincian !== []) {
            $baris[] = '';

            foreach ($rincian as $r) {
                $baris[] = '• ' . $r;
            }
        }

        $baris[] = '';
        $baris = [...$baris, ...self::langkah($isi, $total)];

        $catatan = self::catatanPenting($isi, $total, $diskon);

        if ($catatan !== []) {
            $baris[] = '';
            $baris = [...$baris, ...$catatan];
        }

        $tenggat = ($isi['diperiksa'] ?? false)
            ? null
            : self::tenggat($isi['tenggat'] ?? null);

        if ($tenggat !== null) {
            $baris[] = '';
            $baris[] = '⏳ *Batas pembayaran: ' . $tenggat . '*';
        }

        if (! ($isi['diperiksa'] ?? false)) {
            /*
             * Desakannya DILEWATI untuk yang buktinya sedang diperiksa.
             *
             * "Mohon segera diselesaikan" ke orang yang sudah membayar
             * adalah kalimat yang paling menyinggung dari seluruh pesan ini,
             * dan ia membantah kalimat di atasnya sendiri yang baru saja
             * berkata tidak ada yang perlu ia kerjakan.
             */
            $baris[] = '';
            $baris[] = 'Mohon segera diselesaikan agar tempat Bapak/Ibu tetap kami tahan '
                . 'dan pendaftarannya bisa kami proses secepatnya ✅';
        }

        $baris[] = '';
        $baris[] = self::soalBukti($isi);

        $baris[] = '';
        $baris = [...$baris, ...self::penutup()];

        return $baris;
    }

    /** Naskahnya sebagai satu untaian siap kirim. */
    public static function rakit(array $isi): string
    {
        return implode("\n", self::baris($isi));
    }

    /**
     * Soal bukti pembayaran — dan ini kalimat yang paling sering dikeluhkan.
     *
     * Naskah aslinya menutup dengan "silakan konfirmasi jika sudah transfer".
     * Itu benar selama uangnya masuk ke rekening yang dicocokkan dengan
     * tangan. Lewat gerbang, pembayarannya tercatat sendiri — dan kalimat itu
     * membuat orang mengira pembayarannya belum sah sampai ia mengirim
     * tangkapan layar, padahal tidak ada yang menunggu tangkapan layar itu.
     *
     * Terbalik pun sama merugikannya: di layanan bertransfer manual, berkata
     * "tidak perlu mengirim bukti" membuat uangnya masuk tanpa ada yang bisa
     * mencocokkannya dengan pendaftarnya.
     */
    private static function soalBukti(array $isi): string
    {
        if ($isi['diperiksa'] ?? false) {
            return 'Bapak/Ibu tidak perlu mengirimkan apa pun lagi — kami akan '
                . 'mengabari melalui nomor ini setelah pemeriksaannya selesai.';
        }

        return ($isi['bergerbang'] ?? false)
            ? 'Pembayaran lewat tautan di atas tercatat otomatis sehingga Bapak/Ibu '
                . 'tidak perlu mengirimkan bukti pembayaran ke nomor ini.'
            : 'Setelah transfer, mohon kirimkan bukti pembayarannya agar segera '
                . 'kami periksa.';
    }

    /**
     * Penutup yang sama di SEMUA pesan, apa pun keadaannya.
     *
     * Dua kalimat milik pemiliknya sendiri, dan keduanya dipertahankan apa
     * adanya: "Salam Q1" itu tanda tangan lembaganya — yang menerimanya
     * mengenalinya — dan pesan yang kehilangannya terbaca seperti datang
     * dari orang lain.
     *
     * @return array<int, string>
     */
    public static function penutup(): array
    {
        return [
            'Terima kasih, semoga ini jadi langkah awal menuju publikasi di jurnal '
                . 'bereputasi! 🙌',
            'Salam Q1 ✨',
        ];
    }

    // ------------------------------------------------------------ bagiannya

    /**
     * Nama yang SELURUHNYA huruf kecil dibesarkan awal katanya.
     *
     * Borangnya tidak memaksa huruf besar, dan sebagian mengisinya apa adanya
     * dari papan ketik ponsel. Hasilnya "Halo Bapak/Ibu joko" — justru di
     * baris sapaan, hal pertama yang dibaca orangnya.
     *
     * Hanya yang seluruhnya huruf kecil yang disentuh: nama yang sudah memuat
     * huruf besar dibiarkan utuh, sebab di dalamnya ada singkatan dan gelar
     * ("M. Rizki", "Deni Adha Akbari, S.E.") yang rusak kalau dibesar-kecilkan
     * ulang.
     */
    private static function sapaan(?string $nama): string
    {
        $nama = trim((string) $nama);

        if ($nama === '') {
            return '';
        }

        return $nama === mb_strtolower($nama, 'UTF-8')
            ? mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8')
            : $nama;
    }

    /** "Scopus Camp — Yogyakarta #199", atau nama layanannya saja. */
    private static function sebutan(array $isi): string
    {
        $layanan = trim((string) ($isi['layanan'] ?? ''));
        $kelas = trim((string) ($isi['kelas'] ?? ''));

        if ($layanan === '') {
            return $kelas !== '' ? $kelas : 'Rumah Scopus';
        }

        return $kelas === '' ? $layanan : $layanan . ' — ' . $kelas;
    }

    /** @return array<int, string> */
    private static function rincian(array $isi, int $total): array
    {
        $butir = [];

        $nomor = trim((string) ($isi['nomor'] ?? ''));

        if ($nomor !== '') {
            $butir[] = 'Nomor pendaftaran: *' . mb_strtoupper($nomor) . '*';
        }

        $orang = (int) ($isi['jumlah'] ?? 1);

        if ($orang > 1) {
            $butir[] = 'Jumlah peserta: *' . $orang . ' orang*';
        }

        /*
         * Tanggal mulainya, kalau diketahui. Ini keterangan yang paling
         * dicari pendaftar sesudah nominalnya — dan satu-satunya yang tidak
         * bisa ia hitung sendiri dari pesan ini.
         */
        $mulai = $isi['mulai'] ?? null;

        if ($mulai !== null && $mulai !== '') {
            try {
                $butir[] = 'Mulai: ' . Carbon::parse($mulai)->locale('id')
                    ->translatedFormat('j F Y');
            } catch (\Throwable) {
                // Tanggal yang tidak bisa dibaca dilewati, bukan dicetak
                // mentah.
            }
        }

        if ($total > 0) {
            $butir[] = 'Total pembayaran: *Rp ' . number_format($total, 0, ',', '.') . '*';
        }

        return $butir;
    }

    /**
     * Langkah berikutnya — TIGA cabang, dan ketiganya pernah salah.
     *
     * @return array<int, string>
     */
    private static function langkah(array $isi, int $total): array
    {
        /*
         * Yang buktinya SUDAH ada di tangan kami tidak ditagih.
         *
         * Scopus Kafe mewajibkan unggahan buktinya di borang pendaftaran: ia
         * sudah mengirim uang DAN buktinya sebelum pesan ini berangkat.
         * Disuruh "silakan selesaikan pembayaran", orangnya mengira
         * unggahannya gagal lalu mentransfer untuk kedua kalinya — uangnya
         * masuk dua kali, dan pengembaliannya dikerjakan dengan tangan.
         */
        if ($isi['diperiksa'] ?? false) {
            return [
                '✅ *Langkah selanjutnya:*',
                'Tidak ada. Bukti pembayaran yang Bapak/Ibu unggah sedang kami periksa, '
                    . 'dan hasilnya kami kabari melalui nomor ini.',
            ];
        }

        $tautan = trim((string) ($isi['tautan'] ?? ''));
        $nominal = $total > 0 ? ' senilai *Rp ' . number_format($total, 0, ',', '.') . '*' : '';

        if (($isi['bergerbang'] ?? false) && $tautan !== '') {
            return [
                '✅ *Langkah selanjutnya:*',
                'Silakan selesaikan pembayaran' . $nominal . ' melalui tautan berikut:',

                /*
                 * Alamatnya BERDIRI SENDIRI di barisnya, tanpa titik di
                 * ujungnya. WhatsApp menebak batas tautan dari spasi, dan
                 * tanda baca yang menempel ikut termakan ke dalam alamat —
                 * yang ditekan pendaftar lalu alamat yang tidak ada.
                 */
                $tautan,
            ];
        }

        if ($isi['bergerbang'] ?? false) {
            /*
             * Bergerbang tetapi tautannya tidak bisa dirakit — barisnya
             * belum punya kunci, atau rutenya dipanggil dari tempat yang
             * tidak punya konteks alamat.
             *
             * Yang TIDAK boleh terjadi: jatuh ke kalimat transfer manual.
             * Layanan ini tidak punya rekening yang dicocokkan dengan
             * tangan, jadi uang yang masuk ke sana tidak akan pernah
             * tercatat sebagai pembayaran pendaftaran ini.
             */
            return [
                '✅ *Langkah selanjutnya:*',
                'Silakan selesaikan pembayaran' . $nominal . ' melalui tautan '
                    . 'pembayaran yang kami kirimkan.',
            ];
        }

        /*
         * Tanpa gerbang: rekening yayasannya TIDAK dirakit di sini.
         *
         * Nomor rekening yang diketik di perakit pesan adalah hal paling
         * berbahaya yang bisa ditaruh di berkas ini — salah satu angka, dan
         * uang orang masuk ke rekening orang lain tanpa ada yang tahu sampai
         * ia menagih. Yang disebut: ke mana ia harus melihat, yaitu halaman
         * pendaftaran yang memang menampilkannya.
         */
        return [
            '✅ *Langkah selanjutnya:*',
            'Silakan selesaikan pembayaran' . $nominal . ' ke rekening atas nama '
                . 'yayasan yang tertera di halaman pendaftaran.',
        ];
    }

    /**
     * "Catatan penting" soal potongan harganya — hanya yang benar-benar diketahui.
     *
     * @return array<int, string>
     */
    private static function catatanPenting(array $isi, int $total, int $diskon): array
    {
        $kode = trim((string) ($isi['kode_diskon'] ?? ''));
        $promo = self::promo($isi['promo'] ?? null);

        if ($diskon <= 0 && $kode === '' && $promo === null) {
            return [];
        }

        $baris = ['📌 *Catatan penting:*'];

        $kuota = $promo === null ? null : $promo->kuota;
        $sampai = $promo === null ? null : $promo->selesai;

        if ($kuota !== null && (int) $kuota > 0) {
            $baris[] = 'Potongan harga ini hanya berlaku untuk *' . (int) $kuota
                . ' pendaftar pertama*.';
        } elseif ($sampai !== null) {
            $baris[] = 'Potongan harga ini hanya berlaku sampai *'
                . Carbon::parse($sampai)->locale('id')->translatedFormat('j F Y') . '*.';
        } else {
            /*
             * Tidak tahu batasnya, jadi yang disebut batas yang MEMANG ada:
             * tenggat pembayarannya sendiri. Sesudah itu tagihannya mati dan
             * ia mendaftar ulang dengan harga yang berlaku saat itu — benar
             * tanpa perlu mengarang kuota.
             */
            $baris[] = $kode === ''
                ? 'Potongan harga ini menempel pada tagihan ini saja.'
                : 'Potongan dengan kode *' . mb_strtoupper($kode)
                    . '* ini menempel pada tagihan ini saja.';
        }

        if ($diskon > 0) {
            $baris[] = '';
            $baris[] = '⏳ Jika pembayarannya terlewat, harganya kembali ke harga normal *Rp '
                . number_format($total + $diskon, 0, ',', '.') . '*.';
        }

        return $baris;
    }

    /** Promo dari kuncinya, modelnya, atau null. */
    private static function promo($promo): ?PromoLayanan
    {
        if ($promo instanceof PromoLayanan) {
            return $promo;
        }

        $kunci = trim((string) $promo);

        return $kunci === '' ? null : PromoLayanan::find($kunci);
    }

    private static function tenggat($tenggat): ?string
    {
        if ($tenggat === null || $tenggat === '') {
            return null;
        }

        try {
            return Carbon::parse($tenggat)->locale('id')
                ->translatedFormat('j F Y, H:i') . ' WIB';
        } catch (\Throwable) {
            // Nilai yang tidak bisa dibaca DILEWATI, bukan dicetak apa
            // adanya: "Batas pembayaran: 0000-00-00" lebih buruk daripada
            // tidak ada barisnya.
            return null;
        }
    }
}
