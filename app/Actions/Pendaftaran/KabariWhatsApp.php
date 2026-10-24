<?php

namespace App\Actions\Pendaftaran;

use App\Services\Fonnte;
use App\Support\PesanKabarWa;
use App\Support\PesanTagihanWa;
use App\Support\PendaftaranSemuaLayanan as Katalog;
use Illuminate\Support\Facades\Log;

/**
 * Pemberitahuan WhatsApp untuk SEMUA layanan, sepasang dengan suratnya.
 *
 * Alasannya satu kalimat dari pemilik produk: tidak semua orang membuka
 * emailnya. Yang tidak membukanya kehilangan nomor pendaftaran, tagihan, dan
 * batas waktunya sekaligus — lalu kursinya dilepas tanpa ia pernah tahu apa
 * yang terjadi.
 *
 * Sampai 24 Okt 2026 hanya Webinar Eksklusif yang mengirimkannya; empat
 * layanan lain diam sama sekali. Diangkat jadi satu aksi bersama atas
 * permintaan pemiliknya — bukan disalin per layanan, sebab yang disalin akan
 * berbeda perlahan, dan yang paling mudah berbeda justru KAKI PESANNYA: di
 * situ tertulis satu-satunya nomor yang dipakai orang untuk memastikan ia
 * tidak sedang ditipu.
 *
 * TIDAK PERNAH MENGGAGALKAN PEMANGGILNYA. Dipanggil dari jalur pendaftaran
 * dan dari pemberitahuan gerbang pembayaran; kegagalan di sini tidak boleh
 * membatalkan pendaftaran maupun membuat kami menjawab bukan-200 ke DOKU —
 * yang lalu mengulang pemberitahuan pembayaran yang sudah tercatat.
 *
 * Pesannya SENGAJA PENDEK dan tidak mengulang seluruh isi suratnya. WhatsApp
 * dibaca sambil lalu di layar kecil; yang panjang digulung tanpa dibaca. Yang
 * harus ada cuma: apa yang terjadi, nomornya, angkanya, dan satu tautan.
 */
class KabariWhatsApp
{
    /**
     * Apa yang berbeda antar layanan — dan hanya itu.
     *
     * Ditaruh DI SINI, bukan di katalog PendaftaranSemuaLayanan: katalog itu
     * memerikan DATA (tabel dan nama kolomnya), sementara yang di bawah ini
     * alamat halaman umum dan kalimat yang dibaca orang. Keduanya berubah
     * karena alasan yang sama sekali berbeda.
     *
     * Kuncinya:
     *
     *   kabar_status  JENIS kabar mana yang dikirim otomatis untuk layanan
     *            ini. Nilai statusnya sendiri diterjemahkan ke jenis oleh
     *            PesanKabarWa, sebab kelima layanan memakai kosakata yang
     *            berbeda untuk hal yang sama. Jenis yang tidak terdaftar
     *            memang tidak dikabari lewat WhatsApp.
     *
     *   kelanjutan  apa yang terjadi SESUDAH uangnya diterima. Berbeda
     *            sungguhan per layanan: webinar membagikan tautan masuk,
     *            camp berangkat ke tempat, bibliometrik mulai dikerjakan.
     *            Dikalimatkan seragam, tiga di antaranya jadi salah.
     *
     * @var array<string, array{daftar:string, daftar_berangkatan:bool, indeks:string, kabar_status:array<int, string>, kelanjutan:string}>
     */
    private const LAYANAN = [
        'webinar_eksklusif' => [
            'daftar' => 'public.webinareksklusif.daftar',
            'daftar_berangkatan' => true,
            'indeks' => 'public.webinareksklusif.index',

            /*
             * HANYA 'paid'.
             *
             * 'cancel' dan 'expired' sengaja TIDAK di sini, meski keduanya
             * punya pesannya sendiri: layanan ini sudah punya sapuan
             * KabariKursiDilepas yang merebut penanda sebelum mengirim,
             * supaya satu kursi yang dilepas tidak dikabari dua kali.
             * Ditambahkan di sini, perpindahan status yang dicatat panitia
             * tidak merebut penanda itu — dan sapuannya mengirim pesan yang
             * sama lagi beberapa menit kemudian.
             */
            'kabar_status' => ['lunas'],
            'kelanjutan' => 'Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.',
        ],
        'scopus_camp' => [
            'daftar' => 'public.scopuscamp.formpendaftaran',
            'daftar_berangkatan' => true,
            'indeks' => 'public.scopuscamp.index',

            /*
             * 'kedaluwarsa' TIDAK ada di sini, dan itu keputusan.
             *
             * Pesannya menyebut sebab yang tegas — "batas waktu
             * pembayarannya telah terlewat" — dan itu hanya benar kalau
             * saringannya memang memastikannya. Satu-satunya tempat yang
             * memastikannya perintah scopus-camp:kedaluwarsakan, yang
             * memanggil kursiDilepas() sendiri.
             *
             * 'dibatalkan' BOLEH ada: pesannya sengaja tidak menyebut sebab
             * apa pun, sebab pembatalan di layanan ini keputusan panitia
             * dengan alasan yang bisa apa saja — peserta mengundurkan diri,
             * kelasnya tidak jalan, berkasnya tidak lengkap.
             */
            'kabar_status' => ['lunas', 'jadwal', 'ditolak', 'refund', 'dibatalkan'],
            'kelanjutan' => 'Informasi teknis keberangkatannya dibagikan panitia lewat grup peserta di WhatsApp.',
        ],
        'bibliometrik' => [
            'daftar' => 'public.analisisbibliometrik.formpendaftaran',
            'daftar_berangkatan' => true,
            'indeks' => 'public.analisisbibliometrik.index',

            /*
             * Tanpa 'kedaluwarsa' dan tanpa 'gagal_bayar': layanan ini
             * bertransfer manual, jadi tidak ada tenggat yang bisa terlewat
             * maupun gerbang yang bisa menolak. Keduanya memang tidak pernah
             * terjadi di sini.
             */
            'kabar_status' => ['lunas', 'jadwal', 'ditolak', 'refund', 'dibatalkan'],
            'kelanjutan' => 'Tim kami mulai mengerjakan analisisnya dan menghubungi Anda lewat nomor ini.',
        ],
        'scopus_kafe' => [
            'daftar' => 'public.scopuskafe.formpendaftaran',

            // Satu-satunya yang tidak berangkatan: borangnya satu untuk
            // semua, dan rutenya memang tidak menerima parameter.
            'daftar_berangkatan' => false,
            'indeks' => 'public.scopuskafe.index',
            // Dua status saja; layanan ini tidak berangkatan dan tidak
            // mengenal penjadwalan ulang maupun pengembalian dana.
            'kabar_status' => ['lunas', 'ditolak'],
            'kelanjutan' => 'Jadwal dan tautan sesinya dibagikan panitia lewat grup peserta di WhatsApp.',
        ],

        /*
         * Clinik Scopus TIDAK ada di sini, dan itu bukan kelupaan.
         *
         * Layanan itu belum punya satu surat pun — katalognya menyebut
         * 'surat' => [] — jadi menambahkan WhatsApp di sana berarti
         * pemesannya dikabari lewat WhatsApp untuk kejadian yang tidak
         * pernah ia terima suratnya. Ditawarkan ke pemiliknya, belum
         * dijawab. Sampai suratnya ada, melayani() memulangkan false dan
         * pemanggilnya MENCATAT dilewati, bukan diam-diam tidak mengirim.
         */
    ];

    public function __construct(private Fonnte $fonnte)
    {
    }

    /** Layanan yang memang dikabari lewat WhatsApp. */
    public static function melayani(string $layanan): bool
    {
        return isset(self::LAYANAN[$layanan]);
    }

    /**
     * Pendaftaran baru tersimpan — dan inilah langkah berikutnya.
     *
     * @param  object  $p  baris pendaftarannya
     * @param  object|null  $angkatan  kelas/sesi yang dipilih, kalau layanannya berangkatan
     */
    public function pendaftaranMasuk(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! self::melayani($layanan)) {
            return $this->dilewati($layanan);
        }

        $angkatan ??= $this->angkatanDari($layanan, $p);

        /*
         * Naskahnya dirakit PesanTagihanWa, bukan di sini.
         *
         * Naskah yang sama dipakai draf follow-up panitia. Dirakit dua kali,
         * pendaftar yang menerima pesan otomatis lalu ditindaklanjuti panitia
         * menerima dua pesan tentang satu hal yang berbunyi berbeda — dan
         * yang paling mudah berselisih justru nominalnya.
         */
        return $this->kirim($layanan, $p, PesanTagihanWa::baris([
            'nama' => $this->nama($layanan, $p),
            'layanan' => Katalog::sumber($layanan)['nama'] ?? null,
            'kelas' => $this->namaKelas($layanan, $angkatan, true),
            'nomor' => $this->nomor($layanan, $p),
            'jumlah' => (int) ($p->{Katalog::kolomPeran($layanan, 'jumlah') ?? 'jumlah_pendaftar'} ?? 1),
            'total' => $this->total($layanan, $p),
            'diskon' => (int) ($p->{Katalog::kolomPeran($layanan, 'nominal_diskon') ?? 'nominal_diskon'} ?? 0),
            'kode_diskon' => $p->{Katalog::kolomPeran($layanan, 'kode_diskon') ?? 'kode_diskon'} ?? null,
            'promo' => $p->{Katalog::kolomPeran($layanan, 'promo') ?? 'promo_id'} ?? null,
            'tenggat' => $p->kedaluwarsa_pada ?? null,
            'tautan' => $this->tautanStatus($layanan, $p),
            'bergerbang' => $this->bergerbang($layanan),

            /*
             * Buktinya SUDAH terunggah di borang pendaftaran — Scopus Kafe
             * mewajibkannya. Dibaca dari kolomnya, bukan diandaikan dari
             * layanannya: Analisis Bibliometrik memakai borang yang sama
             * tetapi unggahannya tidak wajib, jadi dua pendaftaran di satu
             * layanan bisa berbeda keadaan.
             */
            'diperiksa' => trim((string) ($p->{Katalog::kolomPeran($layanan, 'bukti') ?? 'gambar'} ?? '')) !== '',
        ]));
    }

    /** Uangnya diterima — tempatnya aman. */
    public function lunas(string $layanan, object $p, ?object $angkatan = null): bool
    {
        return $this->kabar($layanan, $p, $angkatan, 'lunas');
    }

    /**
     * Gerbang menolak pembayarannya — tempatnya dilepas, tetapi bisa diulang.
     *
     * Hanya untuk layanan bergerbang: transfer manual tidak punya kejadian
     * "pembayarannya gagal" sama sekali. Yang buktinya ditolak panitia
     * menerima kabar penolakan, bukan pesan ini.
     */
    public function gagalBayar(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! $this->bergerbang($layanan)) {
            return $this->dilewati($layanan);
        }

        return $this->kabar($layanan, $p, $angkatan, 'gagal_bayar');
    }

    /** Batas waktunya habis tanpa pembayaran — tempatnya diberikan ke orang lain. */
    public function kursiDilepas(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! $this->bergerbang($layanan)) {
            return $this->dilewati($layanan);
        }

        return $this->kabar($layanan, $p, $angkatan, 'kedaluwarsa');
    }

    /**
     * Satu pintu untuk SELURUH kabar perpindahan status.
     *
     * Naskahnya dirakit PesanKabarWa, yang dipakai bersama draf follow-up
     * panitia. Sebelum ini keempat pesan di kelas ini punya naskahnya
     * sendiri-sendiri, dan selisihnya sudah terlihat: judulnya bergaris
     * pisah bercetak tebal sementara draf panitia tidak punya judul sama
     * sekali, dan sapaannya "Halo <nama>," sementara draf memakai
     * "Yth. Bapak/Ibu". Satu pendaftar bisa menerima keduanya dalam satu hari.
     */
    private function kabar(string $layanan, object $p, ?object $angkatan, string $jenis): bool
    {
        if (! self::melayani($layanan)) {
            return $this->dilewati($layanan);
        }

        $angkatan ??= $this->angkatanDari($layanan, $p);

        return $this->kirim($layanan, $p, PesanKabarWa::baris([
            'jenis' => $jenis,
            'status' => $p->status ?? null,
            'nama' => $this->nama($layanan, $p),
            'layanan' => Katalog::sumber($layanan)['nama'] ?? null,
            'kelas' => $this->namaKelas($layanan, $angkatan, true),
            'nomor' => $this->nomor($layanan, $p),
            'jumlah' => (int) ($p->{Katalog::kolomPeran($layanan, 'jumlah') ?? 'jumlah_pendaftar'} ?? 1),
            'total' => $this->total($layanan, $p),
            'kanal' => $this->caraBayar($layanan, $p),
            'email' => $p->{Katalog::kolomPeran($layanan, 'email') ?? 'email'} ?? null,
            'kelanjutan' => $jenis === 'lunas' ? $this->ciri($layanan)['kelanjutan'] : null,
            'grup' => $this->tautanGrup($layanan, $angkatan, $p),
            'tautan_daftar' => $this->tautanDaftar($layanan, $angkatan),

            /*
             * Perkenalan DIKOSONGKAN, dan penutupnya tidak boleh menyuruh
             * membalas: tidak ada orang yang sedang menulis pesan ini, dan
             * kakinya sendiri sudah berkata pesan ini dikirim otomatis.
             */
            'perkenalan' => null,
            'bisa_dibalas' => false,
        ]));
    }

    /**
     * Mengabari sesuai STATUS BARUNYA — satu pintu untuk kelima layanan.
     *
     * Dipanggil dari UbahStatusPendaftaran, tepat sesudah suratnya. Di sana,
     * bukan di tiap layar panitia: ada tiga layar yang memindahkan status,
     * dan dipasang di masing-masing, ketiganya akan berbeda perlahan.
     *
     * TIDAK dipasang di KirimSuratStatus, meski di situ pun satu tempat.
     * Aksi itu juga dipanggil tombol "kirim ulang surat" di layar rincian —
     * dan tombol yang menjanjikan surat lalu ikut mengirim WhatsApp
     * mengejutkan panitia yang menekannya, sekaligus memakan kuota pesan
     * untuk sesuatu yang tidak ia minta.
     *
     * Status yang tidak terdaftar memulangkan false tanpa mengirim apa pun;
     * kebanyakan perpindahan status memang tidak perlu dikabari dua kali.
     */
    public function untukStatus(string $layanan, object $p): bool
    {
        if (! self::melayani($layanan)) {
            return $this->dilewati($layanan);
        }

        /*
         * Statusnya diterjemahkan ke JENIS kabar lewat PesanKabarWa, bukan
         * dipetakan lagi di sini. Kelima layanan memakai kosakata yang
         * berbeda untuk hal yang sama, dan petanya hanya boleh ada di satu
         * tempat — kalau tidak, satu nilai baru harus ditambahkan di dua
         * tempat, dan yang terlupa diam tanpa galat.
         */
        $jenis = PesanKabarWa::jenisDariStatus($p->status ?? null);

        if (! in_array($jenis, $this->ciri($layanan)['kabar_status'], true)) {
            return false;
        }

        return $this->kabar($layanan, $p, null, $jenis);
    }

    /**
     * Angkatan pendaftarannya, atau null.
     *
     * Dicari DI SINI, dan tiap pintu masuk memanggilnya sendiri saat
     * angkatannya tidak diserahkan. Bukan kemudahan: tanpa itu, pemanggil
     * yang menyerahkan relasi yang tidak ada — $pendaftaran->kategori pada
     * model yang tidak punya relasi itu memulangkan null tanpa galat —
     * mengirim pesan yang menyebut "Scopus Camp" alih-alih "Yogyakarta
     * #199", dan tidak ada yang menandainya sampai ada yang mengadu.
     */
    private function angkatanDari(string $layanan, object $p): ?object
    {
        $model = Katalog::angkatanModel($layanan);
        $kolom = Katalog::kolomPeran($layanan, 'angkatan_id');

        if ($model === null || $kolom === null) {
            return null;
        }

        return $model::find($p->{$kolom} ?? null);
    }

    // ------------------------------------------------------------ bagiannya

    /** @return array{daftar:string, daftar_berangkatan:bool, indeks:string, kabar_status:array<int, string>, kelanjutan:string} */
    private function ciri(string $layanan): array
    {
        return self::LAYANAN[$layanan];
    }

    private function bergerbang(string $layanan): bool
    {
        return self::melayani($layanan) && Katalog::pakaiGerbang($layanan);
    }

    private function nama(string $layanan, object $p): string
    {
        return (string) ($p->{Katalog::kolomPeran($layanan, 'nama_orang') ?? 'nama'} ?? '');
    }

    private function nomor(string $layanan, object $p): string
    {
        return (string) ($p->{Katalog::kolomPeran($layanan, 'nomor') ?? 'id_transaksi'} ?? '');
    }

    private function total(string $layanan, object $p): int
    {
        return (int) ($p->{Katalog::kolomPeran($layanan, 'total') ?? 'total_pembayaran'} ?? 0);
    }

    private function telp(string $layanan, object $p): ?string
    {
        $kolom = Katalog::kolomPeran($layanan, 'telp') ?? 'telp';

        $telp = $p->{$kolom} ?? null;

        return $telp === null ? null : (string) $telp;
    }

    /**
     * Cara bayar yang AMAN disebut di pesan lunas.
     *
     * 'kanal_bayar' lebih dulu karena ia yang BENAR-BENAR dipakai — "VA
     * Mandiri", "QRIS" — sementara 'cara_bayar' cuma berbunyi "doku" untuk
     * semua pembayaran daring.
     *
     * Dan nilai itu TIDAK BOLEH tercetak. Nama gerbangnya urusan internal:
     * yang perlu diketahui pendaftar cuma bahwa pembayarannya diselesaikan
     * lewat tautan kami. Disebut namanya, pesan ini berbohong pada hari
     * gerbangnya diganti — dan yang menerimanya mencari halaman yang sudah
     * tidak dipakai lagi.
     *
     * Dibaca lewat katalog, bukan dibandingkan dengan untaian 'doku' di sini:
     * labelnya sendiri pun memuat namanya ("Pembayaran daring (DOKU)"), jadi
     * yang dilewati keduanya sekaligus. Layanan bertransfer manual tetap
     * menyebutkan caranya — "Transfer bank" benar dan tidak membocorkan apa
     * pun.
     */
    private function caraBayar(string $layanan, object $p): ?string
    {
        $kolomKanal = Katalog::kolomPeran($layanan, 'kanal_bayar');

        if ($kolomKanal !== null) {
            $kanal = trim((string) ($p->{$kolomKanal} ?? ''));

            if ($kanal !== '') {
                return $kanal;
            }
        }

        $kolomCara = Katalog::kolomPeran($layanan, 'cara_bayar');

        if ($kolomCara === null) {
            return null;
        }

        $cara = Katalog::caraBayar($p->{$kolomCara} ?? null);

        return ($cara['kunci'] ?? '') === 'doku' ? null : ($cara['label'] ?? null);
    }

    /**
     * Nama kelas/sesi yang didaftar, atau nama layanannya kalau angkatannya
     * tidak diserahkan.
     *
     * Berbentuk "Nama #nomor" — ejaan yang SAMA dengan yang dipakai halaman
     * pendaftaran, surat, dan fakturnya. Disebut berbeda di sini, orang yang
     * mencocokkan pesan WhatsApp-nya dengan fakturnya mengira keduanya dua
     * pendaftaran yang berbeda.
     */
    private function namaKelas(string $layanan, ?object $angkatan, bool $bolehKosong = false): ?string
    {
        $nama = trim((string) ($angkatan->nama ?? ''));

        if ($nama === '') {
            /*
             * $bolehKosong dipakai perakit naskah tagihan, yang menyebut nama
             * layanannya sendiri di medan terpisah. Memulangkan nama layanan
             * di situ membuat pesannya berbunyi "Scopus Camp — Scopus Camp".
             */
            return $bolehKosong ? null : (Katalog::sumber($layanan)['nama'] ?? 'Rumah Scopus');
        }

        $ke = trim((string) ($angkatan->nama_ke ?? ''));

        return $nama . ($ke !== '' ? ' #' . $ke : '');
    }

    private function batas(object $p): ?string
    {
        $batas = $p->kedaluwarsa_pada ?? null;

        return $batas instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($batas)
                ->locale('id')->translatedFormat('j F Y, H:i') . ' WIB'
            : null;
    }

    private function tautanStatus(string $layanan, object $p): ?string
    {
        // Dibaca dari katalog: draf follow-up panitia memakai alamat yang
        // sama, dan diketik di dua tempat, yang satu akan tertinggal.
        return Katalog::tautanStatusUmum($layanan, (string) $p->getKey());
    }

    /**
     * Tautan grup peserta, HANYA untuk yang sudah jadi peserta.
     *
     * Kolomnya varchar bebas, dan terukur di basis data ia lebih sering
     * berisi catatan panitia daripada alamat — 153 dari 228 isian berbunyi
     * "menyusul" atau "link nyusul". Penyaringnya dipakai bersama layar
     * panitia lewat katalog, bukan diulang di sini: dicetak apa adanya, yang
     * terkirim adalah "bergabung ke grup melalui tautan berikut: menyusul".
     */
    private function tautanGrup(string $layanan, ?object $angkatan, object $p): ?string
    {
        /*
         * Barisnya dirakit seperti yang diharapkan Katalog::tautanGrup():
         * ia membaca `grup` pada barisnya, dan di sini sumbernya bisa dua —
         * angkatannya (empat layanan) atau pendaftarannya sendiri.
         */
        $grup = $angkatan->group_wa
            ?? ($p->{Katalog::kolomPeran($layanan, 'grup') ?? 'group_wa'} ?? null);

        return Katalog::tautanGrup((object) ['grup' => $grup]);
    }

    private function tautanDaftar(string $layanan, ?object $angkatan): string
    {
        $ciri = $this->ciri($layanan);

        /*
         * Tanpa angkatan, yang dibuka halaman INDEKS layanannya — bukan
         * borang pendaftaran. Rute borang tiga layanan menuntut parameter
         * angkatan; diserahkan null, route() melempar dan pemberitahuannya
         * gagal justru di satu-satunya baris yang berisi jalan keluarnya.
         *
         * Scopus Kafe tidak berangkatan: borangnya satu untuk semua. Di
         * sana angkatan memang selalu null, dan rutenya memang tidak
         * menerima parameter.
         */
        if (! $ciri['daftar_berangkatan']) {
            return route($ciri['daftar']);
        }

        return $angkatan === null
            ? route($ciri['indeks'])
            : route($ciri['daftar'], $angkatan->getKey());
    }

    /**
     * Kaki yang sama untuk SEMUA pemberitahuan, di SEMUA layanan.
     *
     * Tiga hal, dan ketiganya menjawab persoalan yang berbeda:
     *
     *   1. "jangan dibalas" — nomor pengirimnya nomor SISTEM. Balasan ke sini
     *      tidak dibaca siapa pun, dan yang membalas lalu menunggu jawaban
     *      yang tidak akan pernah datang;
     *   2. ke mana harus menghubungi — tanpa itu, butir pertama cuma menutup
     *      pintu tanpa menunjukkan pintu lain;
     *   3. PERINGATAN PENIPUAN, dan ini yang paling penting.
     *
     * Butir ketiga ada karena orang yang baru saja mendaftar adalah sasaran
     * empuk: ia memang sedang menunggu dihubungi soal pembayaran, jadi siapa
     * pun yang menyapa mengaku Rumah Scopus akan dipercaya. Menyebut SATU
     * nomor resmi mengubah "siapa pun yang mengaku" jadi sesuatu yang bisa
     * dicocokkan sendiri oleh orangnya, di pesan yang sudah ada di tangannya.
     *
     * "admin resmi", bukan "panitia": panitia bisa berarti siapa saja yang
     * terlibat acara, dan penipu pun menyebut dirinya panitia.
     *
     * ---------------------------------------------------------------------
     * KALIMAT TERAKHIRNYA BERCABANG, dan itu wajib
     * ---------------------------------------------------------------------
     *
     * Versi gerbang berbunyi "pembayaran hanya lewat tautan resmi". Itu BOLEH
     * ditulis setegas itu untuk Webinar Eksklusif dan Scopus Camp: keduanya
     * hanya menerima DOKU, jadi siapa pun yang meminta transfer di luar
     * tautan itu memang penipu.
     *
     * Scopus Kafe dan Analisis Bibliometrik masih memakai TRANSFER MANUAL ke
     * rekening yayasan. Kalimat yang sama di sana jadi janji yang dilanggar
     * sistemnya sendiri — orangnya baru saja diminta mentransfer — dan
     * peringatan penipuan yang terbukti salah mengajari orang mengabaikan
     * peringatan kita yang berikutnya. Jadi di sana yang ditegaskan bukan
     * CARANYA melainkan TUJUANNYA: rekening atas nama yayasan, bukan pribadi.
     *
     * Nomornya dibaca dari config('panitia.whatsapp'), BUKAN diketik di sini:
     * ia berubah saat orangnya berganti, dan nomor yang diketik ulang di tiap
     * tempat akan tertinggal di sebagian tempat.
     *
     * @return array<int, string>
     */
    private function kaki(string $layanan): array
    {
        $wa = preg_replace('/^62/', '0', (string) config('panitia.whatsapp'));
        $tampil = trim(chunk_split((string) $wa, 4, '-'), '-');

        $modus = $this->bergerbang($layanan)
            ? 'Kami tidak pernah meminta transfer ke rekening pribadi, dan pembayaran '
                . 'hanya lewat tautan resmi di pesan maupun email ini.'
            : 'Kami tidak pernah meminta transfer ke rekening pribadi — pembayaran hanya '
                . 'ke rekening atas nama yayasan yang tertulis di halaman pendaftaran.';

        return [
            '',
            '— — —',
            'Pesan ini dikirim otomatis, *mohon jangan dibalas*.',
            '',
            'Ada kendala atau pertanyaan? Hubungi *admin resmi Rumah Scopus Foundation*, '
                . 'dan hanya di nomor ini:',
            '*' . $tampil . '*',
            '',
            '*Hati-hati penipuan.* Ada pihak yang mengatasnamakan Rumah Scopus '
                . 'Foundation. ' . $modus,
        ];
    }

    /**
     * Layanan yang memang tidak dikabari — dicatat, bukan didiamkan.
     *
     * Diam membuat "WhatsApp-nya tidak sampai" terlihat sama dengan
     * "Fonnte-nya bermasalah", dan yang mencarinya akan memeriksa token,
     * kuota, dan perangkatnya sebelum sampai pada kesimpulan yang benar:
     * layanan itu memang belum disambungkan.
     */
    private function dilewati(string $layanan): bool
    {
        Log::warning('Kabar WhatsApp dilewati: layanan tidak dilayani.', [
            'layanan' => $layanan,
        ]);

        return false;
    }

    /**
     * @param  array<int, string>  $baris
     */
    private function kirim(string $layanan, object $p, array $baris): bool
    {
        $pesan = implode("\n", [...$baris, ...$this->kaki($layanan)]);

        $hasil = $this->fonnte->kirim($this->telp($layanan, $p), $pesan);

        if (! $hasil['berhasil']) {
            /*
             * Dicatat sebagai PERINGATAN, bukan galat: tidak tersetelnya
             * token dan nomor yang memang tidak bisa dihubungi keduanya
             * normal, dan galat yang normal mengajari orang mengabaikan
             * lognya.
             */
            Log::warning('Kabar WhatsApp tidak terkirim.', [
                'layanan' => $layanan,
                'nomor' => $this->nomor($layanan, $p),
                'sebab' => $hasil['pesan'],
            ]);
        }

        return $hasil['berhasil'];
    }
}
