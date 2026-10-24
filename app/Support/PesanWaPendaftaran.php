<?php

namespace App\Support;

use App\Support\KabarStatusPendaftaran;
use App\Support\PesanTagihanWa;
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
     * untuk email — "lewat email yang sama", "hubungi admin kami lewat
     * WhatsApp" (yang di surat menunjuk tombolnya, sedangkan di sini
     * pembacanya SUDAH di WhatsApp) — dan menambalnya kata per kata dengan
     * str_replace rapuh: sekali kalimatnya diubah di sana, tambalannya
     * lolos. Terbukti saat dicoba: dua kalimat lolos pada percobaan
     * pertama.
     *
     * Enam keadaan, bukan lima belas status: yang perlu dikerjakan pendaftar
     * sesudah membaca pesan ini sama untuk semua status dalam satu keadaan.
     */
    private const PENUTUP = [
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
     * Kalimat penagihan untuk keadaan SELAIN "menunggu".
     *
     * Keadaan "menunggu" tidak memakai ini: naskahnya dirakit PesanTagihanWa,
     * sumber yang sama dengan pesan otomatis lewat Fonnte. Dirakit dua kali,
     * pendaftar yang menerima pesan otomatis lalu ditindaklanjuti panitia
     * menerima dua pesan tentang satu hal yang berbunyi berbeda — dan yang
     * paling mudah berselisih justru nominalnya.
     *
     * Bayar di tempat satu-satunya yang masih dirakit di sini: uangnya tidak
     * lewat gerbang mana pun, dan menyuruh orang yang akan membayar di meja
     * panitia membuka tautan pembayaran mengirimnya membayar dua kali.
     */
    private const MENAGIH_TUNAI = 'Pembayaran diserahkan langsung kepada panitia '
        . 'pada hari acara.';

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
        $layanan = (string) ($b->layanan ?? '');

        /*
         * Keadaan "menunggu" memakai NASKAH BERSAMA, bukan rakitan di bawah.
         *
         * Itu naskah yang sudah dipakai panitia sehari-hari — ucapan selamat,
         * langkah selanjutnya, catatan penting soal potongannya, penutup
         * "Salam Q1" — dan naskah yang sama dikirim otomatis lewat Fonnte
         * begitu borangnya tersimpan. Satu sumber untuk keduanya: pendaftar
         * yang menerima pesan otomatis lalu ditindaklanjuti panitia menerima
         * dua pesan tentang satu hal, dan kalau keduanya berbunyi berbeda ia
         * bertanya mana yang berlaku.
         *
         * Bayar di tempat DIKECUALIKAN: uangnya tidak lewat gerbang mana pun
         * dan tidak ada bukti yang diunggah, jadi naskah tagihan itu tidak
         * berlaku sama sekali untuknya.
         */
        $tunai = (Pendaftaran::caraBayar($b->cara_bayar ?? null)['kunci'] ?? '') === 'tunai';

        if ($keadaan === 'menunggu' && ! $tunai) {
            return PesanTagihanWa::rakit([
                'nama' => $b->nama_orang ?? null,
                'layanan' => Pendaftaran::katalog()[$layanan]['nama'] ?? null,
                'kelas' => self::namaKelas($b, $sesi),
                'nomor' => $b->nomor ?? null,
                'jumlah' => (int) ($b->jumlah ?? 1),
                'total' => (int) ($b->total ?? 0),
                'diskon' => (int) ($b->nominal_diskon ?? 0),
                'kode_diskon' => $b->kode_diskon ?? null,
                'promo' => $b->promo ?? null,

                /*
                 * Tenggatnya tidak ada di baris union — kolomnya memang tidak
                 * diproyeksikan — jadi dibaca dari barisnya kalau pemanggilnya
                 * menyerahkan model, dan dilewati kalau tidak. Dilewati berarti
                 * satu kalimat hilang; dikarang berarti panitia menjanjikan
                 * batas waktu yang bukan batas waktunya.
                 */
                'tenggat' => $b->kedaluwarsa_pada ?? null,
                'mulai' => self::tanggalMulai($b),

                /*
                 * Perkenalannya hanya di draf panitia, bukan di pesan
                 * otomatisnya: satu orang memegang kelima layanan, dan
                 * memperkenalkan diri sebagai panitia satu layanan membuat
                 * percakapan berikutnya — yang sering soal pendaftaran lain
                 * milik orang yang sama — terbaca seperti datang dari orang
                 * yang berbeda.
                 */
                'perkenalan' => 'Perkenalkan, saya admin Rumah Scopus.',
                'tautan' => Pendaftaran::tautanStatusUmum($layanan, (string) ($b->id ?? '')),
                'bergerbang' => Pendaftaran::pakaiGerbang($layanan),
                'diperiksa' => Pendaftaran::perluDiperiksa($b),
            ]);
        }

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
         * "📩 Halo Bapak/Ibu", mengikuti naskah yang dipakai pemiliknya.
         *
         * Sebelumnya "Yth. Bapak/Ibu", dengan alasan bahwa pesan ini mewakili
         * lembaga. Alasannya masih berlaku, tetapi sapaan yang BERBEDA antar
         * keadaan lebih merugikan: satu orang bisa menerima dua pesan dalam
         * satu hari — tagihan lalu pelunasan — dan yang sapaannya berganti
         * terbaca seperti datang dari dua orang yang berbeda.
         */
        $baris = ['📩 Halo Bapak/Ibu' . ($nama === '' ? '' : ' ' . $nama)];
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

        /*
         * Peringatan "jangan dibulatkan sampai angka terakhir" DIBUANG.
         *
         * Tiga angka terakhir itu kode unik, gunanya mencocokkan mutasi
         * rekening dengan tangan. Lewat gerbang pembayaran nominalnya sudah
         * terpatok di tautannya — tidak ada yang bisa dibulatkan — dan
         * menyuruh orang menjaga angkanya sendiri cuma menakuti.
         */

        /*
         * Ajakan masuk grup, HANYA untuk yang sudah jadi peserta.
         *
         * Dua keadaan: 'lunas' dan 'jadwal' — yang dijadwalkan ulang sudah
         * membayar, cuma pindah tanggal. Yang belum bayar tidak diberi
         * tautannya: grupnya akan terisi orang yang kursinya belum ada,
         * dan mengeluarkan mereka kembali satu per satu pekerjaan panitia
         * yang tidak perlu ada. Yang batal dan yang dananya dikembalikan
         * jelas tidak diajak.
         */
        if (in_array($keadaan, ['lunas', 'jadwal'], true)) {
            $grup = Pendaftaran::tautanGrup($b);

            if ($grup !== null) {
                $baris[] = '';
                $baris[] = 'Silakan bergabung ke grup peserta melalui tautan berikut:';
                /*
                 * Alamatnya BERDIRI SENDIRI di barisnya, tanpa titik di
                 * ujungnya. WhatsApp menebak batas tautan dari spasi, dan
                 * tanda baca yang menempel ikut termakan ke dalam alamat —
                 * yang ditekan pendaftar lalu alamat yang tidak ada.
                 */
                $baris[] = $grup;
            }
        }

        $baris[] = '';

        /*
         * Sampai di sini, satu-satunya yang masih menunggu pembayaran adalah
         * yang membayar di tempat — sisanya sudah dipulangkan di atas lewat
         * naskah tagihan bersama. Kalimat tagihan untuk gerbang dan untuk
         * transfer manual ikut pindah ke sana; ditinggal di sini, ia akan
         * berselisih dengan yang di sana tanpa ada yang menandainya.
         */
        if ($menagih) {
            $baris[] = self::MENAGIH_TUNAI;
        } else {
            $kunciPenutup = $keadaan === 'menunggu' ? 'diperiksa' : $keadaan;

            $baris[] = self::PENUTUP[$kunciPenutup] ?? self::PENUTUP['lain'];
        }

        // Tanda tangan lembaganya, sama dengan naskah tagihan dan pesan
        // otomatisnya. Yang menerimanya mengenalinya; pesan yang
        // kehilangannya terbaca seperti datang dari orang lain.
        $baris[] = '';
        $baris = [...$baris, ...PesanTagihanWa::penutup()];

        return implode("\n", $baris);
    }

    /** Tanggal mulai angkatannya, kalau barisnya memang berangkatan. */
    private static function tanggalMulai(object $b)
    {
        $angkatan = isset($b->angkatan_id) ? Pendaftaran::angkatanBaris($b) : null;

        return $angkatan === null ? null : ($angkatan['mulai'] ?? null);
    }

    /**
     * "Yogyakarta #199", ejaan yang sama dengan halaman, surat, dan fakturnya.
     *
     * Dibaca lewat helper hanya kalau barisnya memang punya angkatan_id:
     * pemanggil yang menyerahkan baris rakitan sendiri tidak punya kolom itu,
     * dan membacanya melempar galat atas properti yang tidak ada.
     */
    private static function namaKelas(object $b, ?string $sesi): ?string
    {
        $angkatan = isset($b->angkatan_id) ? Pendaftaran::angkatanBaris($b) : null;

        if ($angkatan !== null) {
            $nama = trim((string) ($angkatan['nama'] ?? ''));
            $nomor = $angkatan['nomor'] ?? null;

            if ($nama !== '') {
                return $nomor === null ? $nama : $nama . ' #' . $nomor;
            }
        }

        $sesi = trim((string) $sesi);

        return $sesi === '' ? null : $sesi;
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
