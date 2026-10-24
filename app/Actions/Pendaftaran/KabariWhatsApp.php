<?php

namespace App\Actions\Pendaftaran;

use App\Services\Fonnte;
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
     *   status   rute halaman status ber-UUID, boleh NULL. Hanya Webinar
     *            Eksklusif dan Scopus Camp yang punya. Yang tidak punya
     *            tidak diberi tautan palsu — tautan yang mengantar ke
     *            halaman yang salah lebih buruk daripada tidak ada tautan,
     *            sebab yang menekannya mengira ia sudah memeriksa.
     *
     *   gerbang  pembayarannya lewat gerbang (DOKU) atau transfer manual
     *            berbukti unggahan. Ini PEMBEDA TERPENTING di berkas ini:
     *            ia menentukan langkah berikutnya yang kami suruh kerjakan,
     *            ada-tidaknya batas waktu, dan — lihat kaki() — apakah kami
     *            boleh berjanji "pembayaran hanya lewat tautan resmi".
     *
     *   kabar_status  status mana yang mengabari, dan dengan pesan apa.
     *            Kosakatanya BERBEDA per layanan ('paid' vs 'Pendaftaran
     *            Diterima' vs 'pembayaran diterima'), jadi tidak bisa
     *            disamaratakan — dan status yang tidak terdaftar di sini
     *            memang tidak dikabari lewat WhatsApp.
     *
     *   kelanjutan  apa yang terjadi SESUDAH uangnya diterima. Berbeda
     *            sungguhan per layanan: webinar membagikan tautan masuk,
     *            camp berangkat ke tempat, bibliometrik mulai dikerjakan.
     *            Dikalimatkan seragam, tiga di antaranya jadi salah.
     *
     * @var array<string, array{status:?string, daftar:string, daftar_berangkatan:bool, indeks:string, gerbang:bool, kabar_status:array<string, string>, kelanjutan:string}>
     */
    private const LAYANAN = [
        'webinar_eksklusif' => [
            'status' => 'public.webinareksklusif.status',
            'daftar' => 'public.webinareksklusif.daftar',
            'daftar_berangkatan' => true,
            'indeks' => 'public.webinareksklusif.index',
            'gerbang' => true,

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
            'kabar_status' => ['paid' => 'lunas'],
            'kelanjutan' => 'Tautan masuk sesinya dibagikan panitia lewat grup peserta di WhatsApp.',
        ],
        'scopus_camp' => [
            'status' => 'public.scopuscamp.status',
            'daftar' => 'public.scopuscamp.formpendaftaran',
            'daftar_berangkatan' => true,
            'indeks' => 'public.scopuscamp.index',
            'gerbang' => true,

            /*
             * 'Pendaftaran Dibatalkan' TIDAK dikabari dari sini.
             *
             * Di layanan ini pembatalan adalah KEPUTUSAN PANITIA, dan
             * alasannya bisa apa saja — peserta mengundurkan diri, kelasnya
             * tidak jalan, berkasnya tidak lengkap. Pesan "kursi Anda
             * dilepas" menyebut sebab yang tegas: "pembayaran belum masuk
             * sampai batas waktunya". Dipasang di sini, ia akan menuduh
             * sebagian orang tidak membayar padahal bukan itu yang terjadi.
             *
             * Pesan itu dikirim perintah scopus-camp:kedaluwarsakan — satu-
             * satunya tempat yang sebabnya benar menurut rakitannya sendiri.
             */
            'kabar_status' => ['Pendaftaran Diterima' => 'lunas'],
            'kelanjutan' => 'Informasi teknis keberangkatannya dibagikan panitia lewat grup peserta di WhatsApp.',
        ],
        'bibliometrik' => [
            'status' => null,
            'daftar' => 'public.analisisbibliometrik.formpendaftaran',
            'daftar_berangkatan' => true,
            'indeks' => 'public.analisisbibliometrik.index',
            'gerbang' => false,

            /*
             * Hanya 'Pendaftaran Diterima'. Yang lain TIDAK dikabari lewat
             * WhatsApp, dan itu keputusan, bukan kelupaan: layanan ini
             * bertransfer manual, jadi tidak ada kejadian "pembayarannya
             * gagal di gerbang" maupun "kursinya dilepas otomatis" — dan
             * penolakan bukti punya surat sendiri yang menyebutkan alasannya,
             * sesuatu yang tidak bisa dipadatkan jadi pesan pendek.
             */
            'kabar_status' => ['Pendaftaran Diterima' => 'lunas'],
            'kelanjutan' => 'Tim kami mulai mengerjakan analisisnya dan menghubungi Anda lewat nomor ini.',
        ],
        'scopus_kafe' => [
            'status' => null,
            'daftar' => 'public.scopuskafe.formpendaftaran',

            // Satu-satunya yang tidak berangkatan: borangnya satu untuk
            // semua, dan rutenya memang tidak menerima parameter.
            'daftar_berangkatan' => false,
            'indeks' => 'public.scopuskafe.index',
            'gerbang' => false,
            'kabar_status' => ['pembayaran diterima' => 'lunas'],
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

        $status = $this->tautanStatus($layanan, $p);
        $batas = $this->batas($p);

        /*
         * Dua langkah berikutnya yang BERBEDA SAMA SEKALI.
         *
         * Lewat gerbang, orangnya belum membayar apa pun: yang ia butuhkan
         * tautan untuk membayar, dan peringatan bahwa kursinya cuma ditahan
         * sampai batas waktu. Lewat transfer manual, ia SUDAH mengunggah
         * buktinya di borang pendaftaran — menyuruhnya "selesaikan
         * pembayarannya" di situ membuatnya mengira unggahannya gagal, lalu
         * ia mentransfer dua kali.
         */
        $langkah = ($this->ciri($layanan)['gerbang'] && $status !== null)
            ? [
                '',
                'Selesaikan pembayarannya di sini:',
                $status,
                '',
                'Fakturnya kami kirim ke email Anda. Kursi ditahan sampai batas waktu di atas.',
            ]
            : [
                '',
                'Bukti pembayaran yang Anda unggah sedang kami periksa. Hasilnya kami '
                    . 'kabari lewat email dan nomor ini.',
                '',
                'Fakturnya kami kirim ke email Anda.',
            ];

        return $this->kirim($layanan, $p, array_filter([
            '*Pendaftaran tersimpan* — Rumah Scopus Foundation',
            '',
            'Halo ' . $this->nama($layanan, $p) . ', pendaftaran Anda untuk *'
                . $this->namaKelas($layanan, $angkatan) . '* sudah kami terima.',
            '',
            'No. pendaftaran: ' . $this->nomor($layanan, $p),
            'Total bayar: Rp ' . number_format($this->total($layanan, $p), 0, ',', '.'),
            $batas ? 'Batas bayar: ' . $batas : null,
            ...$langkah,
        ], fn ($b) => $b !== null));
    }

    /** Uangnya diterima — tempatnya aman. */
    public function lunas(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! self::melayani($layanan)) {
            return $this->dilewati($layanan);
        }

        $angkatan ??= $this->angkatanDari($layanan, $p);

        $kanal = $this->caraBayar($layanan, $p);
        $email = (string) ($p->{Katalog::kolomPeran($layanan, 'email') ?? 'email'} ?? '');

        return $this->kirim($layanan, $p, array_filter([
            '*Pembayaran diterima* — Rumah Scopus Foundation',
            '',
            'Halo ' . $this->nama($layanan, $p) . ', pembayaran Anda sudah kami terima '
                . 'dan *kursi Anda aman*.',
            '',
            'No. pendaftaran: ' . $this->nomor($layanan, $p),
            'Jumlah: Rp ' . number_format($this->total($layanan, $p), 0, ',', '.'),
            $kanal ? 'Cara bayar: ' . $kanal : null,
            '',
            'Kwitansinya kami kirim ke ' . ($email !== '' ? $email : 'email Anda') . '.',
            $this->ciri($layanan)['kelanjutan'],
        ], fn ($b) => $b !== null));
    }

    /**
     * Gerbang menolak pembayarannya — kursinya dilepas, tetapi bisa diulang.
     *
     * Hanya untuk layanan bergerbang: transfer manual tidak punya kejadian
     * "pembayarannya gagal" sama sekali. Yang buktinya ditolak panitia
     * menerima surat penolakan, bukan pesan ini.
     */
    public function gagalBayar(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! $this->bergerbang($layanan)) {
            return $this->dilewati($layanan);
        }

        $angkatan ??= $this->angkatanDari($layanan, $p);

        return $this->kirim($layanan, $p, [
            '*Pembayaran belum berhasil* — Rumah Scopus Foundation',
            '',
            'Halo ' . $this->nama($layanan, $p) . ', pembayaran untuk '
                . $this->nomor($layanan, $p) . ' tidak selesai, jadi kursinya kami lepas kembali.',
            '',
            'Ini biasanya urusan bank atau kartunya — bukan kesalahan Anda, dan bisa '
                . 'dicoba lagi sekarang juga.',
            '',
            'Kalau saldo Anda sempat terpotong, dana yang tertahan dikembalikan sendiri '
                . 'oleh bank dalam beberapa hari kerja.',
            '',
            'Mau daftar lagi?',
            $this->tautanDaftar($layanan, $angkatan),
        ]);
    }

    /** Batas waktunya habis tanpa pembayaran — kursinya diberikan ke orang lain. */
    public function kursiDilepas(string $layanan, object $p, ?object $angkatan = null): bool
    {
        if (! $this->bergerbang($layanan)) {
            return $this->dilewati($layanan);
        }

        $angkatan ??= $this->angkatanDari($layanan, $p);

        return $this->kirim($layanan, $p, [
            '*Kursi Anda dilepas* — Rumah Scopus Foundation',
            '',
            'Halo ' . $this->nama($layanan, $p) . ', pembayaran untuk '
                . $this->nomor($layanan, $p) . ' belum masuk sampai batas waktunya, '
                . 'jadi kursinya kami lepas untuk peserta lain.',
            '',
            'Merasa sudah membayar? Hubungi admin resmi kami dengan menyebut nomor di '
                . 'atas — pembayarannya tidak hilang, hanya konfirmasinya yang tertunda.',
            '',
            'Masih ingin ikut? Kursinya bisa diambil lagi selama kuotanya belum penuh:',
            $this->tautanDaftar($layanan, $angkatan),
        ]);
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

        $cara = $this->ciri($layanan)['kabar_status'][(string) ($p->status ?? '')] ?? null;

        if ($cara === null) {
            return false;
        }

        return $this->{$cara}($layanan, $p);
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

    /** @return array{status:?string, daftar:string, daftar_berangkatan:bool, indeks:string, gerbang:bool, kabar_status:array<string, string>, kelanjutan:string} */
    private function ciri(string $layanan): array
    {
        return self::LAYANAN[$layanan];
    }

    private function bergerbang(string $layanan): bool
    {
        return self::melayani($layanan) && self::LAYANAN[$layanan]['gerbang'];
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
     * Cara bayar yang disebut di pesan lunas.
     *
     * 'kanal_bayar' lebih dulu karena ia yang BENAR-BENAR dipakai — "VA
     * Mandiri", "QRIS" — sementara 'cara_bayar' cuma berbunyi "doku" untuk
     * semua pembayaran daring. Dua layanan bertransfer manual tidak punya
     * kolom kanal sama sekali, dan di sana 'cara_bayar' memang yang benar.
     */
    private function caraBayar(string $layanan, object $p): ?string
    {
        foreach (['kanal_bayar', 'cara_bayar'] as $peran) {
            $kolom = Katalog::kolomPeran($layanan, $peran);

            if ($kolom === null) {
                continue;
            }

            $nilai = trim((string) ($p->{$kolom} ?? ''));

            if ($nilai !== '') {
                return $nilai;
            }
        }

        return null;
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
    private function namaKelas(string $layanan, ?object $angkatan): string
    {
        $nama = trim((string) ($angkatan->nama ?? ''));

        if ($nama === '') {
            return Katalog::sumber($layanan)['nama'] ?? 'Rumah Scopus';
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
        $rute = $this->ciri($layanan)['status'];

        return $rute === null ? null : route($rute, $p->getKey());
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

        $modus = $this->ciri($layanan)['gerbang']
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
