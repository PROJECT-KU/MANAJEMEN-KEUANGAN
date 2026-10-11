<?php

namespace App\Support;

use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Carbon;

/**
 * Teks siap pakai untuk tombol WhatsApp di layar pendaftaran.
 *
 * Sebelum ini tombolnya membuka percakapan KOSONG. Panitia lalu mengetik
 * ulang nama, nomor pendaftaran, nominal, dan kode uniknya — padahal
 * keempatnya sudah tergambar di baris yang tombolnya baru saja ditekan.
 * Yang paling sering salah ketik justru kode uniknya, dan transfer yang
 * nominalnya meleset tidak bisa dicocokkan sama sekali.
 *
 * Teksnya DRAF, bukan kiriman otomatis: WhatsApp membuka kotak ketik yang
 * sudah terisi dan panitia masih bisa menyuntingnya sebelum mengirim.
 */
class PesanWaPendaftaran
{
    /**
     * @param  object  $b  satu baris hasil Pendaftaran::kueri(), atau model
     *                     yang sudah diberi nama_orang/nomor/total/layanan
     */
    public static function untuk(object $b, ?string $sesi = null): string
    {
        /*
         * Didelegasikan: dulu di sini ada percabangan sendiri (menunggu /
         * lunas / selain itu diam) yang cuma memuat dua dari lima belas
         * status. Pendaftar yang dihubungi saat statusnya "Dijadwalkan
         * ulang" menerima sapaan tanpa satu kata pun tentang jadwalnya.
         */
        return self::tindakLanjut($b, $sesi);
    }

    /**
     * Kalimat penutup per KEADAAN, khusus WhatsApp.
     *
     * Penutup milik suratnya TIDAK dipakai ulang di sini. Isinya ditulis
     * untuk email — "balas email ini", "lewat email yang sama" — dan
     * menambalnya kata per kata dengan str_replace rapuh: sekali kalimatnya
     * diubah di sana, tambalannya lolos dan pendaftar disuruh membalas email
     * yang tidak sedang ia buka. Terbukti saat dicoba: dua kalimat lolos
     * pada percobaan pertama.
     *
     * Enam keadaan, bukan lima belas status: yang perlu dikerjakan pendaftar
     * sesudah membaca pesan ini sama untuk semua status dalam satu keadaan.
     */
    private const PENUTUP = [
        /*
         * 'menunggu' dipakai hanya sebagai cadangan. Kalimat yang benar
         * untuk yang belum bayar bergantung pada CARA BAYARNYA — lihat
         * PENUTUP_MENAGIH.
         */
        'menunggu' => 'Apabila pembayaran telah dilakukan, mohon kabari melalui pesan ini.',
        /*
         * Buktinya SUDAH masuk dan sedang diperiksa.
         *
         * Keadaannya tetap 'menunggu' di katalog — statusnya memang belum
         * lunas — tetapi menagih orang yang sudah mengirim uang DAN bukti
         * adalah kesalahan yang paling menyinggung dari seluruh pesan ini.
         * Terukur saat mencobanya: pesan Scopus Kafe berbunyi "bukti Anda
         * sedang diperiksa" lalu menutup dengan "kalau sudah transfer,
         * kirimkan buktinya" — dua kalimat yang saling membantah dalam satu
         * pesan.
         */
        'diperiksa' => 'Anda tidak perlu mengirimkan apa pun lagi. Kami akan mengabari '
            . 'melalui nomor ini setelah pemeriksaan selesai.',
        'lunas' => 'Mohon simpan pesan ini sebagai bukti. Sampai berjumpa di acara.',
        'jadwal' => 'Apabila jadwal baru tersebut tidak dapat Anda ikuti, mohon balas '
            . 'pesan ini agar kami carikan solusinya.',
        'refund' => 'Apabila dalam satu minggu dananya belum juga masuk, mohon balas '
            . 'pesan ini agar kami telusuri.',
        'tidak_jadi' => 'Apabila menurut Anda terdapat kekeliruan, mohon balas pesan '
            . 'ini agar panitia memeriksanya kembali.',
        'lain' => 'Apabila ada yang ingin ditanyakan, mohon balas pesan ini.',
    ];

    /**
     * Kalimat penagihan per CARA BAYAR.
     *
     * "Kirimkan bukti transfernya" hanya benar untuk transfer manual. Lewat
     * pembayaran daring tidak ada bukti yang perlu dikirim — gatewaynya yang
     * mengabari sistem — dan menyuruh orang mengirim tangkapan layar ke
     * WhatsApp membuat ia mengira pembayarannya belum sah sampai ia
     * melakukannya. Untuk bayar di tempat, tidak ada yang perlu ditransfer
     * sama sekali.
     *
     * NAMA GATEWAYNYA TIDAK DISEBUT ke pendaftar. Itu urusan internal: yang
     * perlu ia tahu cuma bahwa pembayarannya diselesaikan lewat tautan yang
     * dikirim sistem. Disebut namanya, pesan ini berbohong pada hari
     * gatewaynya diganti — dan yang menerimanya akan mencari halaman yang
     * sudah tidak dipakai lagi.
     *
     * Terukur hari ini ketiga ratus barisnya masih 'transfer', jadi
     * kekeliruan ini belum menggigit — tetapi ia akan mengenai SELURUH
     * pendaftar di hari pembayaran daring dinyalakan.
     */
    private const PENUTUP_MENAGIH = [
        'transfer' => 'Apabila pembayaran telah dilakukan, mohon kirimkan bukti '
            . 'transfernya ke nomor ini. Terima kasih.',
        'doku' => 'Pembayaran diselesaikan melalui tautan pembayaran yang dikirimkan '
            . 'sistem. Anda tidak perlu mengirimkan bukti apa pun ke nomor ini.',
        'tunai' => 'Pembayaran diserahkan langsung kepada panitia pada hari acara.',
    ];

    /**
     * Pesan tindak lanjut untuk STATUS yang sedang berlaku.
     *
     * Kalimat keadaannya diambil dari KabarStatusPendaftaran — sumber yang
     * sama dengan surat emailnya. Ditulis ulang di sini, dua saluran yang
     * mengabarkan hal yang sama akan berbeda bunyi perlahan, dan pendaftar
     * yang membaca keduanya tidak tahu mana yang berlaku.
     *
     * @param  string|null  $sesi  nama sesi/angkatan, kalau ada
     */
    public static function tindakLanjut(object $b, ?string $sesi = null): string
    {
        $status = (string) ($b->status ?? '');
        $kabar = KabarStatusPendaftaran::untuk($status);
        $keadaan = Pendaftaran::keadaanDari($status);

        $nama = trim((string) ($b->nama_orang ?? ''));

        /*
         * Nama yang SELURUHNYA huruf kecil dibesarkan awal katanya.
         *
         * Borang pendaftarannya tidak memaksa huruf besar, dan sebagian
         * mengisinya apa adanya dari papan ketik ponsel. Hasilnya sapaan
         * "Yth. Bapak/Ibu joko," — justru di kalimat yang paling resmi
         * dari seluruh pesan ini.
         *
         * Hanya yang seluruhnya huruf kecil yang disentuh. Nama yang sudah
         * memuat huruf besar dibiarkan utuh: di dalamnya ada singkatan
         * ("M. Rizki", gelar, nama lembaga) yang akan rusak kalau
         * dibesarkan-kecilkan ulang.
         */
        if ($nama !== '' && $nama === mb_strtolower($nama, 'UTF-8')) {
            $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
        }
        $total = (int) ($b->total ?? 0);

        /*
         * "Yth. Bapak/Ibu", bukan "Halo".
         *
         * Pesan ini mewakili lembaga, bukan percakapan pribadi — dan yang
         * menerimanya sebagian dosen dan peneliti. Sapaan santai membuat
         * tagihan di dalamnya terbaca seperti pesan asal, bukan pemberitahuan
         * resmi yang perlu ditindaklanjuti.
         */
        $baris = ['Yth. Bapak/Ibu' . ($nama === '' ? '' : ' ' . $nama) . ','];
        $baris[] = '';
        /*
         * "Admin Rumah Scopus", bukan "panitia <nama layanan>".
         *
         * Satu orang memegang kelima layanan, dan memperkenalkan diri sebagai
         * panitia satu layanan membuat percakapan berikutnya — yang sering
         * soal pendaftaran lain milik orang yang sama — terbaca seperti
         * datang dari orang yang berbeda. Nama layanannya tetap disebut, di
         * rincian, tempat ia memang keterangan.
         */
        $baris[] = 'Perkenalkan, saya admin Rumah Scopus.';

        /*
         * Status yang belum punya kalimat tidak menghasilkan pesan setengah
         * jadi: kalimat keadaannya dilewati, rinciannya tetap terkirim.
         * Panitia masih bisa mengetik sisanya sendiri — jauh lebih baik
         * daripada tombol yang mati.
         */
        if ($kabar !== null) {
            $baris[] = '';
            $baris[] = $kabar['pembuka'];
        }

        /*
         * Menagih atau tidak. Yang buktinya sudah diunggah TIDAK ditagih:
         * keadaannya memang masih 'menunggu', tetapi uangnya sudah dikirim
         * dan yang ditunggu panitia, bukan dia.
         */
        $menagih = $keadaan === 'menunggu' && ! Pendaftaran::perluDiperiksa($b);

        $rincian = self::rincian($b, $sesi, $menagih, $total);

        if ($rincian !== []) {
            $baris[] = '';
            $baris[] = 'Berikut rincian pendaftaran Anda:';

            foreach ($rincian as $r) {
                $baris[] = '• ' . $r;
            }
        }

        $cara = Pendaftaran::caraBayar($b->cara_bayar ?? null);

        /*
         * Peringatan "jangan dibulatkan" hanya untuk cara bayar yang BUTUH
         * BUKTI, yaitu transfer manual. Kode unik gunanya mencocokkan mutasi
         * rekening dengan tangan; lewat pembayaran daring, gatewaynya yang
         * mencocokkan, dan menyuruh orang menjaga tiga angka terakhir cuma
         * membingungkan.
         */
        if ($menagih && $total > 0 && (int) ($b->kode_unik ?? 0) > 0
            && ($cara['perlu_bukti'] ?? false) === true) {
            $baris[] = '';
            $baris[] = 'Mohon ditransfer *tepat* sampai angka terakhir. Tiga angka '
                . 'terakhir merupakan kode unik Anda, dan angka itulah yang kami gunakan '
                . 'untuk mencocokkan pembayaran.';
        }

        $baris[] = '';
        if ($menagih) {
            // Cara bayar yang belum terdaftar jatuh ke kalimat 'menunggu'
            // yang netral: ia tidak menyebut transfer maupun pembayaran
            // daring.
            $baris[] = self::PENUTUP_MENAGIH[$cara['kunci'] ?? ''] ?? self::PENUTUP['menunggu'];
        } else {
            $kunciPenutup = $keadaan === 'menunggu' ? 'diperiksa' : $keadaan;

            $baris[] = self::PENUTUP[$kunciPenutup] ?? self::PENUTUP['lain'];
        }

        return implode("\n", $baris);
    }

    /**
     * Butir rincian pendaftaran, diambil dari BARISNYA — tidak ada yang
     * dikarang.
     *
     * Tiap butir dilewati kalau nilainya memang tidak ada: Scopus Kafe dan
     * Clinik Scopus tidak berangkatan sama sekali, dan pendaftaran satu
     * orang tidak perlu diberi tahu bahwa ia satu orang.
     *
     * @return list<string>
     */
    private static function rincian(object $b, ?string $sesi, bool $menagih, int $total): array
    {
        $butir = [];

        $layanan = Pendaftaran::katalog()[$b->layanan ?? '']['nama'] ?? null;

        if ($layanan !== null) {
            $butir[] = 'Layanan: *' . $layanan . '*';
        }

        /*
         * Angkatan KE-BERAPA, bukan cuma namanya.
         *
         * "Angkatan Penuh YMO7ZY" tidak menunjuk apa pun bagi yang
         * menerimanya — nama itu dipakai panitia, sedangkan yang diingat
         * pendaftar nomor angkatannya. Keduanya disebut kalau dua-duanya ada.
         *
         * Dibaca lewat helper hanya kalau barisnya memang punya angkatan_id;
         * pemanggil yang menyerahkan baris rakitan sendiri tidak punya kolom
         * itu, dan membacanya akan melempar galat atas properti yang tidak
         * ada.
         */
        $angkatan = isset($b->angkatan_id) ? Pendaftaran::angkatanBaris($b) : null;

        if ($angkatan !== null) {
            $sebut = $angkatan['nomor'] !== null
                ? 'Angkatan ke-' . $angkatan['nomor']
                : 'Angkatan';

            $butir[] = $sebut . ': ' . $angkatan['nama'];

            if (! empty($angkatan['mulai'])) {
                $butir[] = 'Mulai: ' . Carbon::parse($angkatan['mulai'])
                    ->locale('id')->translatedFormat('j F Y');
            }
        } elseif ($sesi !== null && trim($sesi) !== '') {
            // Layanan tanpa angkatan: sesinya teks bebas, itu saja yang ada.
            $butir[] = 'Sesi: ' . trim($sesi);
        }

        $orang = (int) ($b->jumlah ?? 1);

        if ($orang > 1) {
            $butir[] = 'Jumlah peserta: *' . $orang . ' orang*';
        }

        $nomor = trim((string) ($b->nomor ?? ''));

        if ($nomor !== '') {
            $butir[] = 'Nomor pendaftaran: *' . strtoupper($nomor) . '*';
        }

        if ($total > 0) {
            // Disebut "total" untuk yang sudah lunas dan "nominal" untuk yang
            // belum: kata yang sama untuk dua keadaan membuat yang sudah
            // membayar mengira masih ditagih.
            $butir[] = ($menagih ? 'Nominal yang harus dibayarkan' : 'Total pembayaran')
                . ': *Rp ' . number_format($total, 0, ',', '.') . '*';
        }

        return $butir;
    }

    /** Sapaan singkat untuk peserta rombongan, yang bukan pendaftarnya. */
    public static function peserta(?string $nama, string $layanan): string
    {
        $sapaan = trim((string) $nama) === '' ? 'Halo' : 'Halo ' . trim((string) $nama);

        return $sapaan . " 👋\n\nSaya panitia "
            . (Pendaftaran::katalog()[$layanan]['nama'] ?? 'Rumah Scopus Foundation')
            . ' Rumah Scopus Foundation.';
    }
}
