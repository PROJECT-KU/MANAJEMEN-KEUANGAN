<?php

namespace App\Support;

use App\KategoriLayanan;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Satu daftar pendaftar untuk SELURUH layanan jasa Rumah Scopus.
 *
 * Sebelum ini pendaftar tersebar di lima layar yang terpisah sama sekali —
 * Pendaftaran Scopus Camp, Analisis Bibliometrik, Data Pendaftaran Scopus
 * Kafe, Riwayat Pemesanan Clinik Scopus, dan Pendaftar Webinar — dan tidak
 * ada satu tempat pun yang bisa menjawab pertanyaan yang paling sering
 * ditanyakan: "siapa saja yang belum bayar?". Menjawabnya berarti membuka
 * lima layar, memakai lima kotak pencarian, lalu menjumlahkannya sendiri.
 *
 * Tabelnya memang tidak seragam, dan itu bukan sesuatu yang bisa dirapikan
 * dari sini:
 *
 *   scopus_camp_pendaftaran        21 kolom, berangkatan, berombongan
 *   analisis_bibliometrik          21 kolom, SAMA PERSIS dengan di atas
 *   webinar_eksklusif_pendaftaran  27 kolom, berangkatan, berpeserta, berDOKU
 *   pendaftaran_scopus_kafe        33 kolom, tiga sesi dalam satu baris
 *   clinikscopus_pemesanan         28 kolom, bernomor booking, bertrainer
 *
 * Jadi yang dikerjakan kelas ini: memproyeksikan kelimanya ke bentuk yang
 * sama lewat satu `UNION ALL`, supaya penyaringan, pengurutan, penomoran
 * halaman, dan penjumlahannya dikerjakan basis data sekali — bukan lima kali
 * di PHP lalu digabung dengan tangan.
 *
 * Online Training TIDAK ada di sini. Layanannya dijalankan di luar sistem ini
 * dan belum punya tabel pendaftaran maupun angkatan sama sekali; memberinya
 * baris kosong di layar ini hanya akan terbaca sebagai "belum ada yang
 * mendaftar", padahal yang benar adalah "pendaftarannya tidak lewat sini".
 */
class PendaftaranSemuaLayanan
{
    /**
     * Katalog sumber: tabel, nama kolomnya, dan ke mana barisnya dibuka.
     *
     * Didaftar sebagai data, bukan ditulis berulang sebagai lima kueri.
     * Menambah layanan berikutnya cukup menambah satu butir di sini — dan
     * `proyeksi()` akan mengisi sendiri kolom yang tidak dipunyai tabelnya.
     *
     * Tiap sumber menyebut modelnya, aturan angkatannya, pilihan statusnya,
     * dan surat mana yang terkirim saat statusnya berpindah. Keempatnya dulu
     * tersebar di lima pengendali; disatukan di sini supaya menambah layanan
     * berikutnya cukup satu butir, dan supaya tidak ada aturan yang hidup di
     * dua tempat sekaligus.
     */
    private const SUMBER = [
        'scopus_camp' => [
            'nama' => 'Scopus Camp',
            'tabel' => 'scopus_camp_pendaftaran',
            'ikon' => 'fa-campground',
            'warna' => 'mis-hijau',
            'bukti_folder' => 'ScopusCamp',
            'kolom_catatan' => 'note',
            'status_awal' => 'diproses',
            'nomor_awalan' => 'CAMP',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => self::KODE_UNIK,
            'boleh_dibuat_panitia' => true,
            'model' => \App\PendaftaranScopusCamp::class,
            'angkatan_model' => \App\CategoriesScopusCamp::class,
            'berangkatan' => true,
            'status' => [
                'diproses' => 'Diproses',
                'Pendaftaran Diterima' => 'Pendaftaran diterima',
                'Pendaftaran Reschedule' => 'Dijadwalkan ulang',
                'Pendaftaran Ditolak' => 'Pendaftaran ditolak',
                'Pendaftaran Dibatalkan' => 'Pendaftaran dibatalkan',
                'Pendaftaran Refund' => 'Dana dikembalikan',
            ],
            'surat' => [
                'Pendaftaran Diterima' => \App\Mail\ScopusCampUpdateDiterimaMail::class,
                'Pendaftaran Reschedule' => \App\Mail\ScopusCampUpdateResheduleMail::class,
            ],
            'kolom' => [
                'nomor' => 'id_transaksi',
                'nama_orang' => 'nama',
                'email' => 'email',
                'telp' => 'telp',
                'affiliasi' => 'affiliasi',
                'angkatan_id' => 'kategori_id',
                'jumlah' => 'jumlah_pendaftar',
                'total' => 'total_pembayaran',
                'kode_unik' => 'kode_unik',
                'kode_diskon' => 'kode_diskon',
                'nominal_diskon' => 'nominal_diskon',
                'bukti' => 'gambar',
                'cara_bayar' => 'cara_bayar',
                'catatan' => 'note',
            ],
        ],
        'bibliometrik' => [
            'nama' => 'Analisis Bibliometrik',
            'tabel' => 'analisis_bibliometrik',
            'ikon' => 'fa-chart-line',
            'warna' => 'mis-ungu',
            'bukti_folder' => 'bibliometrik',
            'kolom_catatan' => 'note',
            'status_awal' => 'diproses',
            'nomor_awalan' => 'BIB',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => self::KODE_UNIK,
            'boleh_dibuat_panitia' => true,
            'model' => \App\AnalisisBibliometrik::class,
            'angkatan_model' => \App\CategoriesAnalisisBibliometrik::class,
            'berangkatan' => true,
            'status' => [
                'diproses' => 'Diproses',
                'Pendaftaran Diterima' => 'Pendaftaran diterima',
                'Pendaftaran Reschedule' => 'Dijadwalkan ulang',
                'Pendaftaran Ditolak' => 'Pendaftaran ditolak',
                'Pendaftaran Dibatalkan' => 'Pendaftaran dibatalkan',
                'Pendaftaran Refund' => 'Dana dikembalikan',
            ],
            'surat' => [
                'Pendaftaran Diterima' => \App\Mail\AnalisisBibliometrikUpdateDiterimaMail::class,
                'Pendaftaran Reschedule' => \App\Mail\AnalisisBibliometrikUpdateResheduleMail::class,
            ],
            'kolom' => [
                'nomor' => 'id_transaksi',
                'nama_orang' => 'nama',
                'email' => 'email',
                'telp' => 'telp',
                'affiliasi' => 'affiliasi',
                'angkatan_id' => 'kategori_id',
                'jumlah' => 'jumlah_pendaftar',
                'total' => 'total_pembayaran',
                'kode_unik' => 'kode_unik',
                'kode_diskon' => 'kode_diskon',
                'nominal_diskon' => 'nominal_diskon',
                'bukti' => 'gambar',
                'cara_bayar' => 'cara_bayar',
                'catatan' => 'note',
            ],
        ],
        'webinar_eksklusif' => [
            'nama' => 'Webinar Eksklusif',
            'ikon' => 'fa-star',
            'warna' => 'mis-kuning',
            'tabel' => 'webinar_eksklusif_pendaftaran',
            // Webinar TIDAK pernah mengunggah bukti: pembayarannya dicocokkan
            // lewat kode unik Rp 500-1.500 terhadap mutasi rekening, bukan
            // lewat tangkapan layar. Terukur nol dari 4 baris berisi gambar.
            'bukti_folder' => null,
            'kolom_catatan' => 'note',
            'status_awal' => 'pending',
            'nomor_awalan' => 'WE',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => self::KODE_UNIK,
            'boleh_dibuat_panitia' => true,
            'model' => \App\WebinarEksklusifPendaftaran::class,
            'angkatan_model' => \App\KategoriLayanan::class,
            'berangkatan' => true,
            'status' => [
                'pending' => 'Menunggu bayar',
                'paid' => 'Lunas',
                'expired' => 'Kedaluwarsa',
                'cancel' => 'Dibatalkan',
            ],
            // Webinar tidak mengirim surat dari layar panitia: pemberitahuan
            // lunasnya sudah dikirim jalur pendaftarannya sendiri.
            'surat' => [],
            'kolom' => [
                'nomor' => 'id_transaksi',
                'nama_orang' => 'nama',
                'email' => 'email',
                'telp' => 'telp',
                'affiliasi' => 'affiliasi',
                'angkatan_id' => 'kategori_id',
                'jumlah' => 'jumlah_pendaftar',
                'total' => 'total_pembayaran',
                'kode_unik' => 'kode_unik',
                'kode_diskon' => 'kode_diskon',
                'nominal_diskon' => 'nominal_diskon',
                'bukti' => 'gambar',
                'cara_bayar' => 'cara_bayar',
                'catatan' => 'note',
            ],
        ],
        'scopus_kafe' => [
            'nama' => 'Scopus Kafe',
            // fa-mug-hot baru ada di Font Awesome 6; yang dibundel di sini
            // versi 5, dan nama FA6 tidak menggambar apa pun tanpa galat.
            'ikon' => 'fa-coffee',
            'warna' => 'mis-jingga',
            'tabel' => 'pendaftaran_scopus_kafe',
            'bukti_folder' => 'pendaftaran_scopus_kafe',
            // Kolomnya ditambahkan migrasi 2026_10_03_160000; sebelum itu
            // tabel ini tidak punya tempat mencatat jejak sama sekali, dan
            // 11 dari 187 baris perubahannya tidak terlacak.
            'kolom_catatan' => 'note',
            'status_awal' => 'menunggu verifikasi',
            'nomor_awalan' => 'KAFE',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_pemesanan',
            'kode_unik_rentang' => self::KODE_UNIK,
            'boleh_dibuat_panitia' => true,
            'model' => \App\PendaftaranScopusKafe::class,
            'angkatan_model' => null,
            'berangkatan' => false,
            'status' => [
                'menunggu verifikasi' => 'Menunggu verifikasi',
                'pembayaran diterima' => 'Pembayaran diterima',
                'pembayaran ditolak' => 'Pembayaran ditolak',
            ],
            'surat' => [
                'pembayaran diterima' => \App\Mail\UpdatePublicPendaftaranScopusKafeMail::class,
            ],
            'kolom' => [
                'nomor' => 'id_pemesanan',
                'nama_orang' => 'nama',
                'email' => 'email',
                'telp' => 'telp',
                /*
                 * SATU-SATUNYA layanan yang menyimpan variannya sendiri.
                 *
                 * Empat lainnya mewarisinya dari angkatan yang dipilih; Scopus
                 * Kafe tidak berangkatan, jadi tanpa kolom ini varian
                 * Online/Offline yang disetel di layar Layanan tidak punya
                 * tempat sama sekali. Tidak masuk daftar KOLOM union — keempat
                 * tabel lain tidak punya kolomnya.
                 */
                'varian' => 'varian',
                'total' => 'total_keseluruhan_pembayaran',
                'kode_unik' => 'kode_unik_pembayaran',
                'bukti' => 'gambar',
                'cara_bayar' => 'cara_bayar',
                // Tiga sesi muat dalam satu baris, dan yang pertama itulah
                // yang selalu terisi. Dipakai sebagai keterangan sesinya.
                'sesi' => 'sesi',
            ],
        ],
        'clinik_scopus' => [
            'nama' => 'Clinik Scopus',
            'ikon' => 'fa-user-md',
            'warna' => 'mis-biru',
            'tabel' => 'clinikscopus_pemesanan',
            'bukti_folder' => 'ClinikScopusPemesanan',
            'kolom_catatan' => 'note',
            'status_awal' => 'pending',
            'nomor_awalan' => 'KLINIK',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => self::KODE_UNIK,
            'boleh_dibuat_panitia' => false,
            'model' => \App\ClinikScopusPemesanan::class,
            'angkatan_model' => null,
            'berangkatan' => false,
            'status' => [
                'pending' => 'Menunggu bayar',
                'paid' => 'Sudah dibayar',
                'completed' => 'Selesai',
                'canceled' => 'Dibatalkan',
            ],
            'surat' => [],
            'kolom' => [
                // COALESCE: id_transaksi yang dipakai di tempat lain, tetapi
                // nomor yang disebut pemesan saat menghubungi panitia adalah
                // kode booking-nya.
                'nomor' => "COALESCE(NULLIF(id_transaksi, ''), kode_booking)",
                'nomor_mentah' => true,
                'nama_orang' => 'nama_pemesan',
                'email' => 'email_pemesan',
                'telp' => 'telp_pemesan',
                'affiliasi' => 'afiliasi_pemesan',
                'total' => 'total_pembayaran',
                'kode_unik' => 'kode_unik',
                'kode_diskon' => 'kode_diskon',
                'nominal_diskon' => 'diskon',
                'bukti' => 'gambar',
                'cara_bayar' => 'cara_bayar',
                'sesi' => "CONCAT_WS(' · ', NULLIF(sesi, ''), NULLIF(jam_sesi, ''))",
                'sesi_mentah' => true,
            ],
        ],
    ];

    /**
     * Cara bayar yang dikenali, beserta rupanya di layar.
     *
     * 'tunai' ada demi pendaftar yang dibantu panitia: sebelumnya satu-satunya
     * jalur yang bisa dicatat adalah transfer, jadi yang menyerahkan uang di
     * tempat tetap tercatat "menunggu bayar" TANPA bukti — tidak bisa
     * dibedakan dari yang memang belum membayar.
     *
     * 'doku' tidak pernah dipilih panitia; nilainya ditulis jalur pendaftaran
     * umum saat tagihannya dibuat. Didaftarkan di sini supaya barisnya tetap
     * punya label di layar panitia, bukan supaya bisa dipilih.
     *
     * Nilai di luar daftar ini jatuh ke `caraBayar()` sebagai 'lain' dengan
     * alasan yang sama seperti KEADAAN: kolomnya varchar, dan baris yang
     * nilainya belum terdaftar harus tetap bisa ditemukan.
     */
    /**
     * Rentang kode unik, SAMA untuk semua layanan.
     *
     * Dulu tiap layanan punya rentangnya sendiri — 1–99, 1–999, 1000–1500,
     * 500–1500 — dan sebagiannya bahkan tidak sepakat dengan halaman
     * pendaftaran umumnya: katalog Clinik Scopus menyebut 1000–1500
     * sementara halamannya membuat 500–1500. Satu tetapan supaya tidak ada
     * lagi dua angka untuk satu hal.
     *
     * 500–1500, bukan 1–99: dengan 99 pilihan, satu angkatan berisi puluhan
     * pendaftar bernominal sama akan kehabisan kode dan nominalnya mulai
     * kembar — dan dua transfer bernominal sama tidak bisa dibedakan milik
     * siapa. Terukur di data: 1.001 pilihan untuk kuota angkatan 20 kursi.
     */
    public const KODE_UNIK = [500, 1500];

    /**
     * Sesi yang boleh dipilih, per layanan dan per varian.
     *
     * Scopus Kafe berjalan pada jam yang TETAP, dan jamnya berbeda menurut
     * variannya: offline dua sesi (08.00–13.00 dan 13.00–18.00), online hanya
     * sesi pagi. Sebelum ini borang panitia menyodorkan kotak teks bebas, jadi
     * satu pendaftaran bisa tercatat "Sesi 1", yang lain "sesi1", yang lain
     * lagi "pagi" — dan daftar hadir per sesi tidak bisa dikelompokkan dari
     * data yang begitu.
     *
     * `nilai` sengaja huruf kecil "sesi 1"/"sesi 2", SAMA dengan yang ditulis
     * borang pendaftaran umum selama ini (terukur: 9 baris tersimpan, semuanya
     * berbentuk itu). Memakai bentuk lain di jalur panitia berarti satu sesi
     * yang sama tersimpan dalam dua ejaan, dan pengelompokannya pecah.
     *
     * Jamnya ikut disalin ke kolom `waktu_mulai`/`waktu_selesai` saat
     * disimpan — kolom itu sudah ada dan diisi jalur umum, jadi membiarkannya
     * kosong membuat pendaftaran buatan panitia terlihat belum berjadwal.
     *
     * Ditaruh di sini, bukan di layar Layanan: jamnya bukan sesuatu yang
     * disetel per layanan oleh admin, melainkan jam operasional yang tetap.
     * Begitu ia mulai berubah-ubah, tempatnya memang pindah ke sana.
     */
    public const SESI = [
        'scopus_kafe' => [
            'offline' => [
                ['nilai' => 'sesi 1', 'nama' => 'Sesi 1', 'mulai' => '08:00', 'selesai' => '13:00'],
                ['nilai' => 'sesi 2', 'nama' => 'Sesi 2', 'mulai' => '13:00', 'selesai' => '18:00'],
            ],
            'online' => [
                ['nilai' => 'sesi 1', 'nama' => 'Sesi 1', 'mulai' => '08:00', 'selesai' => '13:00'],
            ],
        ],
    ];

    /** @var array<string, array<string, list<array<string, string>>>>|null */
    private static ?array $sesiTersimpan = null;

    /**
     * Seluruh sesi yang ditawarkan, berkunci layanan lalu varian.
     *
     * Bawaannya konstanta SESI di atas, DITIMPA oleh tarif aktif yang
     * menyetel sesinya sendiri. Jam Scopus Kafe berubah sewaktu-waktu dan yang
     * tahu perubahannya panitia, bukan yang memegang kodenya — jadi layar
     * Tarif Layanan yang menentukan, dan konstanta ini tinggal jaring
     * pengaman supaya tarif tanpa setelan tidak berarti "tidak ada sesi".
     *
     * SATU kueri untuk seluruh layanan, bukan satu per pemanggilan: borang
     * pendaftaran membutuhkan seluruh pasangan layanan+varian sekaligus untuk
     * dikirim ke peramban.
     *
     * Disimpan di ingatan selama satu permintaan saja, dengan alasan yang sama
     * seperti katalog Layanan: tarif yang baru diubah harus langsung terlihat,
     * dan cache antar permintaan membuatnya belum muncul tanpa ada yang tahu
     * kenapa.
     *
     * @return array<string, array<string, list<array{nilai: string, nama: string, mulai: string, selesai: string}>>>
     */
    public static function sesiSemua(): array
    {
        if (self::$sesiTersimpan !== null) {
            return self::$sesiTersimpan;
        }

        $hasil = self::SESI;

        $tarif = \App\ClinikScopusBiayaPersesi::query()
            ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
            ->whereNotNull('sesi')
            ->get(['layanan', 'varian', 'sesi']);

        foreach ($tarif as $t) {
            $daftar = $t->daftar_sesi;

            /*
             * Tarif yang kolom sesinya terisi tetapi tak satu pun barisnya
             * utuh dilewati, bukan dipakai menimpa dengan larik kosong —
             * menimpanya berarti menghapus jadwal karena data yang rusak.
             */
            if ($daftar === []) {
                continue;
            }

            $hasil[$t->layanan][(string) ($t->varian ?? '')] = $daftar;
        }

        return self::$sesiTersimpan = $hasil;
    }

    /**
     * Layanan yang baris pendaftarannya PUNYA tempat menyimpan sesi.
     *
     * Dipakai layar Tarif Layanan untuk memutuskan kapan kotak sesinya
     * ditawarkan. Syaratnya kolomnya, bukan "sudah punya sesi": layanan yang
     * sesinya belum disetel justru yang paling butuh kotak itu muncul.
     *
     * @return list<string>
     */
    public static function layananBersesi(): array
    {
        $keluar = [];

        foreach (self::SUMBER as $kunci => $sumber) {
            if (($sumber['kolom']['sesi'] ?? null) === 'sesi') {
                $keluar[] = $kunci;
            }
        }

        return $keluar;
    }

    /** Membuang yang disimpan di ingatan; dipakai uji dan saat tarif disimpan. */
    public static function lupakanSesi(): void
    {
        self::$sesiTersimpan = null;
    }

    /**
     * Sesi yang ditawarkan untuk satu layanan dan varian.
     *
     * Larik kosong berarti sesinya memang tidak diatur — pemanggilnya yang
     * memutuskan artinya: borang tidak menampilkan apa pun, penyimpan tidak
     * memaksa pilihan.
     *
     * @return list<array{nilai: string, nama: string, mulai: string, selesai: string}>
     */
    public static function sesiPilihan(string $layanan, ?string $varian): array
    {
        $daftar = self::sesiSemua()[$layanan] ?? [];
        $kunci = trim((string) $varian);

        return $daftar[$kunci] ?? [];
    }

    /**
     * Sesi yang cocok dengan nilai tersimpan, atau null.
     *
     * Dipakai penyimpan untuk mengambil jamnya, dan untuk menolak nilai yang
     * tidak ada di daftarnya.
     *
     * @return array{nilai: string, nama: string, mulai: string, selesai: string}|null
     */
    public static function sesiCocok(string $layanan, ?string $varian, ?string $nilai): ?array
    {
        $cari = mb_strtolower(trim((string) $nilai));

        foreach (self::sesiPilihan($layanan, $varian) as $sesi) {
            if (mb_strtolower($sesi['nilai']) === $cari) {
                return $sesi;
            }
        }

        return null;
    }

    public const CARA_BAYAR = [
        'tunai' => [
            'label' => 'Bayar di tempat',
            'ringkas' => 'Tunai',
            'warna' => 'mis-hijau',
            'ikon' => 'fa-money-bill-wave',
            'ket' => 'Uangnya diserahkan langsung ke panitia. Tidak perlu unggah bukti.',
            'boleh_dipilih' => true,
            'perlu_bukti' => false,
        ],
        'transfer' => [
            'label' => 'Transfer bank',
            'ringkas' => 'Transfer',
            'warna' => 'mis-biru',
            'ikon' => 'fa-university',
            'ket' => 'Pendaftar mengirim ke rekening, lalu buktinya diunggah.',
            'boleh_dipilih' => true,
            'perlu_bukti' => true,
        ],
        'doku' => [
            'label' => 'Pembayaran daring (DOKU)',
            'ringkas' => 'DOKU',
            'warna' => 'mis-ungu',
            'ikon' => 'fa-credit-card',
            'ket' => 'Dibayar sendiri oleh pendaftar lewat halaman DOKU.',
            'boleh_dipilih' => false,
            'perlu_bukti' => false,
        ],
    ];

    /**
     * Rupa satu cara bayar; nilai tak dikenal tetap dapat barisnya sendiri.
     *
     * @return array<string, mixed>
     */
    public static function caraBayar(?string $nilai): array
    {
        $kunci = trim((string) $nilai);

        if (isset(self::CARA_BAYAR[$kunci])) {
            return self::CARA_BAYAR[$kunci] + ['kunci' => $kunci];
        }

        /*
         * Kosong berarti baris lama dari sebelum kolomnya ada. Disebut
         * transfer, bukan "lain": sebelum kolom itu ada, transfer satu-satunya
         * jalur yang disediakan borangnya.
         */
        if ($kunci === '') {
            return self::CARA_BAYAR['transfer'] + ['kunci' => 'transfer'];
        }

        return [
            'kunci' => $kunci,
            'label' => $kunci,
            'ringkas' => $kunci,
            'warna' => 'mis-abu',
            'ikon' => 'fa-question-circle',
            'ket' => 'Cara bayar ini belum dikenali layar panitia.',
            'boleh_dipilih' => false,
            'perlu_bukti' => false,
        ];
    }

    /**
     * Cara bayar yang boleh dipilih panitia saat mendaftarkan orang lain.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function caraBayarPilihan(): array
    {
        return array_filter(
            self::CARA_BAYAR,
            fn ($c) => ($c['boleh_dipilih'] ?? false) === true
        );
    }

    /**
     * Nilai status "sudah lunas" milik satu layanan, atau null kalau tak ada.
     *
     * Kelima layanan memakai kosakata status yang berbeda ('Pendaftaran
     * Diterima', 'paid', 'pembayaran diterima', …), jadi panitia yang menerima
     * uang tunai di tempat tidak bisa diberi satu nilai tetap. Dicocokkan
     * lewat KEADAAN supaya daftarnya hanya ada di satu tempat.
     */
    public static function statusLunas(string $layanan): ?string
    {
        $status = array_keys(self::SUMBER[$layanan]['status'] ?? []);
        $lunas = self::KEADAAN['lunas']['nilai'];

        foreach ($status as $nilai) {
            if (in_array($nilai, $lunas, true)) {
                return $nilai;
            }
        }

        return null;
    }

    /**
     * Kolom hasil proyeksi; dipakai SEMUA cabang union.
     *
     * `UNION ALL` mencocokkan kolom menurut posisinya, bukan namanya, jadi
     * satu cabang yang menyusun kolomnya dengan urutan berbeda akan menukar
     * nilainya diam-diam tanpa MySQL mengeluh sama sekali.
     *
     * Daftar bersama inilah yang membuat kegagalan itu tidak bisa terjadi:
     * tiap cabang menelusuri daftar yang sama dan alias tiap kolomnya dirakit
     * dari butir yang sama pula, sehingga urutan dan namanya tidak mungkin
     * berselisih. Menukar urutan daftar ini pun aman — sudah dicoba, dan
     * hasilnya identik. Yang masih bisa salah bukan urutannya melainkan
     * PEMETAANNYA per layanan di SUMBER, dan itulah yang dijaga uji
     * `kolomnya_tidak_tertukar_antar_cabang_union`.
     */
    private const KOLOM = [
        'nomor', 'nama_orang', 'email', 'telp', 'affiliasi', 'angkatan_id',
        'jumlah', 'total', 'kode_unik', 'kode_diskon', 'nominal_diskon',
        'bukti', 'catatan', 'sesi', 'cara_bayar',
    ];

    /**
     * Keadaan ringkas, dan nilai status mentah yang masuk ke masing-masing.
     *
     * Lima layanan memakai lima kosakata, seluruhnya di kolom varchar bebas —
     * terukur di basis data: 'Pendaftaran Diterima', 'Pendaftaran Dibatalkan',
     * 'Pendaftaran Reschedule', 'diproses', 'pending', 'expired',
     * 'menunggu verifikasi', 'pembayaran diterima', 'completed'. Sembilan
     * nilai untuk empat keadaan yang sebenarnya.
     *
     * Tidak ada nilai yang berarti berbeda di dua layanan, jadi pemetaannya
     * cukup satu daftar dan penyaringnya satu `whereIn` di kueri luar —
     * bukan lima `whereIn` per cabang.
     *
     * Nilai yang TIDAK ada di sini jatuh ke keadaan 'lain'. Itu bukan
     * keranjang sampah melainkan syarat: kolomnya varchar, jadi layanan mana
     * pun bisa menulis nilai baru kapan saja tanpa migrasi, dan baris yang
     * nilainya belum terdaftar harus tetap punya barisnya sendiri di layar —
     * kalau tidak, ia tidak bisa ditemukan lewat saringan mana pun.
     */
    public const KEADAAN = [
        'menunggu' => [
            'label' => 'Menunggu bayar',
            'warna' => 'kuning',
            'ikon' => 'fa-clock',
            'nilai' => ['diproses', 'pending', 'menunggu verifikasi'],
        ],
        'lunas' => [
            'label' => 'Lunas',
            'warna' => 'hijau',
            'ikon' => 'fa-check-circle',
            'nilai' => ['Pendaftaran Diterima', 'paid', 'pembayaran diterima', 'completed'],
        ],
        'jadwal' => [
            'label' => 'Dijadwalkan ulang',
            'warna' => 'biru',
            'ikon' => 'fa-calendar-alt',
            'nilai' => ['Pendaftaran Reschedule'],
        ],
        'refund' => [
            'label' => 'Dana dikembalikan',
            'warna' => 'ungu',
            'ikon' => 'fa-undo',
            'nilai' => ['Pendaftaran Refund'],
        ],
        'tidak_jadi' => [
            'label' => 'Tidak jadi',
            'warna' => 'merah',
            'ikon' => 'fa-times-circle',
            'nilai' => [
                'Pendaftaran Dibatalkan', 'Pendaftaran Ditolak', 'pembayaran ditolak',
                'cancel', 'canceled', 'cancelled', 'expired',
            ],
        ],
    ];

    /** Kolom yang boleh dipakai mengurutkan, beserta ungkapan SQL-nya. */
    public const URUTAN = [
        'waktu' => 'waktu',
        'nama' => 'nama_orang',
        'layanan' => 'layanan',
        // CAST: kolomnya varchar di empat dari lima tabel, jadi pengurutan
        // apa adanya menaruh "900000" di atas "1500000" (abjad, bukan angka).
        'total' => 'CAST(total AS DECIMAL(15,2))',
        'status' => 'status',
    ];

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $angkatan = null;

    /** Dibuang uji yang mengubah angkatannya di tengah jalan. */
    public static function lupakan(): void
    {
        self::$angkatan = null;
    }

    /**
     * Katalog layanan untuk tampilan: kunci => nama, ikon, warna.
     *
     * @return array<string, array{nama:string, ikon:string, warna:string}>
     */
    public static function katalog(): array
    {
        $katalog = [];

        foreach (self::SUMBER as $kunci => $s) {
            $katalog[$kunci] = [
                'nama' => $s['nama'],
                'ikon' => $s['ikon'],
                'warna' => $s['warna'],
            ];
        }

        return $katalog;
    }

    /**
     * Keterangan satu layanan apa adanya, atau null kalau kuncinya tidak ada.
     *
     * Dipakai lapisan tindakan (ubah status, hapus) yang perlu tahu model,
     * aturan angkatan, dan surat mana yang terkirim. Kuncinya SELALU dari
     * daftar tertutup ini, bukan dari kiriman orang — menerima nilai mentah
     * berarti menerima nama kelas mana pun untuk dipanggil.
     *
     * @return array<string, mixed>|null
     */
    public static function sumber(string $layanan): ?array
    {
        return self::SUMBER[$layanan] ?? null;
    }

    /**
     * Kolom tempat jejak perubahan ditulis, atau null kalau tabelnya tidak punya.
     *
     * Kelimanya kini punya `note`, tetapi dua di antaranya baru sejak migrasi
     * 2026_10_03_160000 — sebelum itu menuliskannya ke sana melempar
     * "Unknown column" dan menggagalkan SELURUH perubahan statusnya dengan
     * galat 500.
     *
     * Tetap dibaca dari katalog, bukan diandaikan ada di semuanya: layanan
     * berikutnya bisa saja datang dengan tabel yang tidak punya kolom itu,
     * dan jejak yang gagal ditulis tidak boleh menggagalkan perubahan
     * statusnya.
     */
    public static function kolomCatatan(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['kolom_catatan'] ?? null;
    }

    /** Kelas model Eloquent satu layanan, atau null. */
    public static function modelUntuk(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['model'] ?? null;
    }

    /**
     * Satu baris pendaftaran sebagai model Eloquent-nya sendiri.
     *
     * Dikembalikan modelnya, bukan baris mentah hasil union: tindakan apa pun
     * yang mengubah atau menghapus harus lewat modelnya supaya hook,
     * relasi, dan tipe kolomnya ikut berlaku.
     */
    public static function temukan(string $layanan, string $id)
    {
        $model = self::modelUntuk($layanan);

        return $model === null ? null : $model::find($id);
    }

    /**
     * Pilihan status yang boleh dipasang panitia untuk satu layanan.
     *
     * Diambil apa adanya dari borang lama tiap layanan supaya penyatuannya
     * tidak diam-diam MENGURANGI pilihan — Scopus Camp dan Bibliometrik punya
     * enam, Scopus Kafe tiga, Clinik Scopus empat, Webinar empat, dan tidak
     * ada satu pun yang sama.
     *
     * @return array<string, string>
     */
    public static function pilihanStatus(string $layanan): array
    {
        return self::SUMBER[$layanan]['status'] ?? [];
    }

    /**
     * Kelas surat yang harus dikirim saat status satu layanan jadi nilai ini.
     *
     * Ini bagian yang paling mudah hilang saat menyatukan layar: mengubah
     * status di layar lama MENGIRIM EMAIL ke pelanggannya — pendaftaran
     * diterima dan dijadwalkan ulang untuk Scopus Camp dan Bibliometrik,
     * pembayaran diterima untuk Scopus Kafe. Dihilangkan, pelanggan berhenti
     * diberi tahu dan tidak ada galat apa pun yang memberitahukannya.
     */
    public static function suratUntuk(string $layanan, ?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        return self::SUMBER[$layanan]['surat'][$status] ?? null;
    }

    /**
     * Status yang dipakai pendaftaran yang baru dibuat.
     *
     * Berbeda di tiap layanan — 'diproses', 'pending', dan 'menunggu
     * verifikasi' — dan ketiganya berarti hal yang sama: uangnya belum masuk.
     * Diambil dari katalog, bukan ditebak dari keadaan 'menunggu': keadaan
     * itu memuat ketiga nilainya sekaligus dan tidak tahu mana milik layanan
     * yang mana.
     */
    public static function statusAwal(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['status_awal'] ?? null;
    }

    /**
     * Nomor pendaftaran baru: AWALAN-YYYYMMDD-NNNN.
     *
     * Dulu tiga pola yang berbeda-beda, dan dua di antaranya tidak memberi
     * tahu apa pun:
     *
     *   acak5       lima huruf/angka acak — "NMMCG", "GNKMO", "HUQEZ"
     *   booking     BOOK-05072026111623-ZRWIJ — 25 aksara
     *   we_berurut  WE-20261003-0001
     *
     * "NMMCG" tidak menyebutkan layanannya, tanggalnya, maupun urutannya.
     * Panitia yang menerima pertanyaan "nomor saya NMMCG, kapan acaranya?"
     * harus mencarinya dulu untuk tahu itu Scopus Camp atau Bibliometrik — dan
     * lima aksara acak jauh lebih mudah salah didengar di telepon daripada
     * tanggal plus nomor urut. Pola ketiga, yang sudah dipakai Webinar
     * Eksklusif, memang yang benar; dua lainnya disamakan dengannya.
     *
     * Alasan yang sama persis sudah ditulis di PemesananLembaga::kodeBaru()
     * saat kode PL-YYYYMMDD-NNN dibuat. Bentuknya mengikuti Webinar, bukan
     * PemesananLembaga, sebab empat digitnya sudah beredar di data nyata dan
     * mencampur lebar nomor urut dalam satu hari membuat urutannya kacau.
     *
     * AWALANNYA KATA, bukan singkatan dua huruf. "SC" dan "CS" untuk Scopus
     * Camp dan Clinik Scopus hanya berbeda urutan hurufnya — dan yang membaca
     * layar ini memang bukan orang yang hafal singkatan.
     *
     * Baris lama tidak diubah. Nomor yang sudah beredar ada di email
     * pendaftar, bukti transfer, dan percakapan WhatsApp; menulis ulang
     * semuanya membuat nomor yang dipegang orang tidak cocok lagi dengan yang
     * ada di sistem. Jadi keduanya hidup berdampingan, dan pencariannya
     * menemukan keduanya.
     *
     * Keunikannya DIPERIKSA ke basis data, bukan diandaikan: nomor urutnya
     * dihitung dari yang terbesar hari itu, tetapi dua permintaan yang tiba
     * bersamaan bisa membaca angka yang sama — dan nomor kembar berarti dua
     * orang menyebut nomor yang sama saat menghubungi panitia.
     */
    public static function nomorBaru(string $layanan): string
    {
        $sumber = self::SUMBER[$layanan] ?? null;

        if ($sumber === null) {
            throw new \InvalidArgumentException('Layanan tidak dikenali: ' . $layanan);
        }

        /*
         * Kolom tempat nomornya DITULIS, bukan ungkapan yang dipakai
         * membacanya. Untuk Clinik Scopus keduanya berbeda: pembacanya
         * COALESCE atas dua kolom, dan memakainya di `where` membuat MySQL
         * mencari kolom bernama "COALESCE(...)" yang jelas tidak ada.
         */
        $kolom = $sumber['kolom_nomor'];
        $model = $sumber['model'];
        $awalan = $sumber['nomor_awalan'] . '-' . now()->format('Ymd') . '-';

        /*
         * Urutannya dihitung dari nomor TERBESAR hari itu, bukan dari jumlah
         * barisnya: pendaftaran yang dihapus akan membuat hitungan baris
         * memberi nomor yang sudah pernah dipakai.
         */
        $terakhir = $model::where($kolom, 'like', $awalan . '%')
            ->orderByDesc($kolom)
            ->value($kolom);

        $urutan = $terakhir === null ? 1 : ((int) substr((string) $terakhir, -4)) + 1;

        for ($coba = 0; $coba < 40; $coba++, $urutan++) {
            $nomor = $awalan . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);

            if (! $model::where($kolom, $nomor)->exists()) {
                return $nomor;
            }
        }

        throw new \RuntimeException(
            'Tidak menemukan nomor pendaftaran yang belum terpakai untuk ' . $layanan . '.'
        );
    }

    /**
     * Rentang kode unik yang dipakai satu layanan.
     *
     * Berbeda-beda, dan angkanya DIBACA dari data yang sudah ada supaya
     * pendaftaran yang dibuat panitia tidak terlihat ganjil di sebelah yang
     * didaftarkan sendiri: Scopus Camp dan Bibliometrik 1-99, Scopus Kafe
     * sampai 999, Clinik Scopus 1.000-1.500, Webinar 500-1.500.
     *
     * @return array{0:int, 1:int}
     */
    public static function rentangKodeUnik(string $layanan): array
    {
        return self::SUMBER[$layanan]['kode_unik_rentang'] ?? self::KODE_UNIK;
    }

    /** Kolom tempat nomor pendaftaran ditulis. */
    public static function kolomNomor(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['kolom_nomor'] ?? null;
    }

    /**
     * Apakah panitia boleh membuat pendaftaran layanan ini dari layar admin.
     *
     * Clinik Scopus TIDAK: pemesanannya mengikat sesi tertentu, trainer yang
     * mendampingi, dan akun pelanggan — ketiganya kolom NOT NULL yang menunjuk
     * baris lain, dan borang yang menebaknya akan membuat pemesanan yang
     * menunjuk sesi atau trainer yang salah. Pemesanan Clinik dibuat lewat
     * alurnya sendiri, yang memang memilih ketiganya.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function katalogBisaDibuat(): array
    {
        $hasil = [];

        foreach (self::SUMBER as $kunci => $s) {
            if (($s['boleh_dibuat_panitia'] ?? false) === true) {
                $hasil[$kunci] = [
                    'nama' => $s['nama'],
                    'ikon' => $s['ikon'],
                    'warna' => $s['warna'],
                    'berangkatan' => (bool) ($s['berangkatan'] ?? false),
                    // Potongan khusus hanya bisa disimpan tabel yang punya
                    // kolomnya. Scopus Kafe tidak punya — dan di sana
                    // nominalnya memang diketik langsung, jadi potongannya
                    // sudah termasuk di dalamnya.
                    'bisa_potongan' => isset($s['kolom']['nominal_diskon']),
                    /*
                     * Varian yang DIPILIH SENDIRI di borang, bukan diwarisi
                     * dari angkatan.
                     *
                     * Empat layanan lain mendapat variannya dari angkatan yang
                     * dipilih. Scopus Kafe tidak berangkatan, jadi variannya
                     * harus dipilih langsung — dan itu hanya mungkin kalau
                     * tabelnya punya kolomnya.
                     */
                    'varian_sendiri' => ! ($s['berangkatan'] ?? false)
                        && isset($s['kolom']['varian']),
                    // Nama sesi hanya dipunyai tabel Scopus Kafe.
                    'pakai_sesi' => ($s['kolom']['sesi'] ?? null) === 'sesi',
                    /*
                     * Sesi yang boleh dipilih, dikelompokkan per varian.
                     * Dibawa serta supaya borangnya tidak menyimpan salinan
                     * jam operasional sendiri — dua daftar yang harus diubah
                     * bersamaan selamanya.
                     */
                    'sesi' => self::SESI[$kunci] ?? [],
                    /*
                     * Pola nomornya disebut dengan kata, bukan dibiarkan jadi
                     * kejutan. Nomornya dibuat sistem sesudah disimpan, jadi
                     * panitia yang ditanya "nomor saya berapa" tidak bisa
                     * menjawab sebelum menyimpan — setidaknya bentuknya bisa
                     * disebutkan lebih dulu.
                     */
                    'pola_nomor_kata' => 'nomornya seperti '
                        . $s['nomor_awalan'] . '-' . now()->format('Ymd') . '-0001',
                ];
            }
        }

        return $hasil;
    }

    /** Apakah layanan ini memakai angkatan berkuota. */
    public static function berangkatan(string $layanan): bool
    {
        return (bool) (self::SUMBER[$layanan]['berangkatan'] ?? false);
    }

    /**
     * Kelas model angkatan satu layanan.
     *
     * Bukan selalu KategoriLayanan: surat Scopus Camp dan Bibliometrik
     * bertipe `CategoriesScopusCamp` dan `CategoriesAnalisisBibliometrik`
     * di tanda tangannya, jadi menyerahkan KategoriLayanan apa adanya
     * melempar TypeError saat suratnya dirakit.
     */
    public static function angkatanModel(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['angkatan_model'] ?? null;
    }

    /** Semua nilai status mentah yang sudah dikenali, dari seluruh keadaan. */
    public static function statusDikenal(): array
    {
        return array_merge(...array_values(array_column(self::KEADAAN, 'nilai')));
    }

    /**
     * Keadaan ringkas untuk satu nilai status mentah.
     *
     * Pencocokannya TIDAK peka huruf besar maupun spasi berlebih: tabel yang
     * sama memuat 'Pendaftaran Diterima' sementara borang lain bisa menulis
     * 'pendaftaran diterima', dan keduanya keadaan yang sama persis.
     */
    public static function keadaanDari(?string $status): string
    {
        $kunci = self::bakukan($status);

        foreach (self::KEADAAN as $nama => $k) {
            foreach ($k['nilai'] as $nilai) {
                if (self::bakukan($nilai) === $kunci) {
                    return $nama;
                }
            }
        }

        return 'lain';
    }

    private static function bakukan(?string $teks): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $teks) ?? ''));
    }

    /**
     * Satu kueri atas kelima tabel, berbentuk seragam.
     *
     * Dikembalikan sebagai subkueri supaya pemanggilnya bisa menyaring,
     * mengurutkan, dan menomori halaman seperti tabel biasa — termasuk
     * menyaring lewat kolom hasil proyeksi (`layanan`, `status`) yang tidak
     * ada namanya di satu tabel pun.
     */
    public static function kueri(): Builder
    {
        $gabung = null;

        foreach (self::SUMBER as $kunci => $s) {
            $cabang = self::proyeksi($kunci, $s);

            $gabung = $gabung === null ? $cabang : $gabung->unionAll($cabang);
        }

        return DB::query()->fromSub($gabung, 'p');
    }

    /**
     * Satu cabang union: tabel aslinya, diproyeksikan ke bentuk seragam.
     *
     * Kolom yang tidak dipunyai tabelnya diisi NULL — bukan dilewati. Melewati
     * satu kolom akan menggeser seluruh kolom sesudahnya, dan `UNION ALL`
     * mencocokkannya menurut posisi tanpa mengeluh.
     */
    private static function proyeksi(string $kunci, array $s): Builder
    {
        /*
         * Literal untaian di-CAST ke CHAR(40).
         *
         * MySQL menentukan tipe kolom union dari gabungan semua cabangnya,
         * tetapi panjang literal yang berbeda-beda ('scopus_camp' 11 huruf,
         * 'webinar_eksklusif' 17) termasuk hal yang pernah memotong nilai di
         * cabang berikutnya tanpa galat. Dipaksa satu panjang, perkaranya
         * tidak bisa muncul.
         */
        $pilih = [
            DB::raw("CAST('{$kunci}' AS CHAR(40)) as layanan"),
            DB::raw('CAST(id AS CHAR(36)) as id'),
        ];

        foreach (self::KOLOM as $kolom) {
            $pilih[] = DB::raw(self::ungkapan($s, $kolom) . ' as ' . $kolom);
        }

        // Status dan waktu selalu ada di kelima tabel, jadi tidak perlu
        // lewat katalog.
        $pilih[] = DB::raw('CAST(status AS CHAR(60)) as status');
        $pilih[] = DB::raw('created_at as waktu');

        return DB::table($s['tabel'])->select($pilih);
    }

    /**
     * Ungkapan SQL untuk satu kolom seragam pada satu sumber.
     *
     * Tiga bentuk yang dikenali di katalog:
     *   'nama_kolom'                     nama kolom biasa
     *   "EKSPRESI" + '<kolom>_mentah'    ungkapan SQL, dipakai apa adanya
     *   tidak ada                        tabelnya tidak punya; jadi NULL
     */
    private static function ungkapan(array $s, string $kolom): string
    {
        $kolom_sumber = $s['kolom'][$kolom] ?? null;

        if ($kolom_sumber === null) {
            /*
             * `jumlah` yang tidak ada bukan NULL melainkan 1.
             *
             * Scopus Kafe dan Clinik Scopus tidak punya kolom jumlah
             * pendaftar sebab barisnya memang selalu satu orang — tiga sesi
             * Scopus Kafe dalam satu baris itu tiga SESI, bukan tiga orang.
             * Dibiarkan NULL, penjumlahan "berapa orang" akan melewatkan
             * kesebelas baris itu.
             */
            if ($kolom === 'jumlah') {
                return '1';
            }

            // CAST-nya perlu: tanpa tipe, kolom NULL di cabang pertama
            // membuat MySQL menentukan tipe kolom union dari NULL saja.
            return 'CAST(NULL AS CHAR(255))';
        }

        if (($s['kolom'][$kolom . '_mentah'] ?? false) === true) {
            return '(' . $kolom_sumber . ')';
        }

        return $kolom_sumber;
    }

    /**
     * Keterangan angkatan untuk seluruh id yang dipakai, satu kueri untuk
     * semuanya.
     *
     * Dicari sekali per permintaan, bukan per baris: dua puluh baris yang
     * masing-masing memanggil KategoriLayanan::find() adalah dua puluh kueri
     * untuk tabel yang isinya 59 baris.
     *
     * Bukan cuma namanya: tanggal mulai dan sisa kursinya ikut, sebab daftar
     * pendaftar yang menyebut "Scopus Camp Jakarta" tanpa keduanya tidak
     * memberi tahu apakah itu angkatan bulan depan atau yang sudah lewat, dan
     * apakah kursinya masih ada.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function angkatanLengkap(): array
    {
        if (self::$angkatan !== null) {
            return self::$angkatan;
        }

        return self::$angkatan = KategoriLayanan::query()
            ->get(['id', 'layanan', 'nama', 'nama_ke', 'mulai', 'total_kuota', 'sisa_kuota'])
            ->mapWithKeys(fn ($a) => [$a->id => [
                'layanan' => (string) $a->layanan,
                'nama' => (string) $a->nama,
                'ringkas' => self::namaRingkas((string) $a->layanan, (string) $a->nama),
                /*
                 * Nomor angkatannya — "batch ke berapa".
                 *
                 * Terisi di seluruh 59 angkatan, dan rentangnya jauh berbeda
                 * antar tempat: Scopus Camp Yogyakarta sudah angkatan ke-202
                 * sementara Jakarta baru ke-9. Jadi nama tempat saja tidak
                 * cukup menunjuk satu angkatan, dan merekap tanpa nomornya
                 * berarti menggabungkan dua ratus angkatan jadi satu baris.
                 */
                'nomor' => ($a->nama_ke === null || $a->nama_ke === '') ? null : (string) $a->nama_ke,
                'mulai' => $a->mulai,
                'total_kuota' => $a->total_kuota === null ? null : (int) $a->total_kuota,
                'sisa_kuota' => $a->sisa_kuota === null ? null : (int) $a->sisa_kuota,
            ]])
            ->all();
    }

    /**
     * Nama angkatan saja — bentuk lama, dipakai kedua berkas unduhan.
     *
     * @return array<string, string>
     */
    public static function namaAngkatan(): array
    {
        return array_map(fn ($a) => $a['nama'], self::angkatanLengkap());
    }

    /**
     * Nama angkatan TANPA awalan nama layanannya.
     *
     * Terukur: 56 dari 59 angkatan namanya memuat nama layanannya sendiri,
     * sehingga tiap baris daftar menulis hal yang sama dua kali — kolom
     * Layanan berbunyi "Scopus Camp" dan kolom Sesi "Scopus Camp Jakarta".
     * Yang sebenarnya ingin dibaca cuma satu kata: Jakarta.
     *
     * Kalau sesudah dipangkas tidak tersisa apa-apa — angkatan Bibliometrik
     * bernama persis "Analisis Bibliometrik" — nama penuhnya dikembalikan.
     * Sel kosong lebih buruk daripada sel yang mengulang.
     */
    public static function namaRingkas(string $layanan, string $nama): string
    {
        $namaLayanan = self::SUMBER[$layanan]['nama'] ?? null;

        if ($namaLayanan === null) {
            return $nama;
        }

        // Dipangkas dari DEPAN saja: "Webinar Eksklusif Batch 2" jadi
        // "Batch 2", tetapi "Kelas Webinar Eksklusif" dibiarkan utuh — kata
        // yang berada di tengah bukan awalan yang mubazir.
        $pola = '/^' . preg_quote($namaLayanan, '/') . '\s*[-:\x{2013}\x{2014}]?\s*/iu';
        $ringkas = trim((string) preg_replace($pola, '', $nama));

        return $ringkas === '' ? $nama : $ringkas;
    }

    /**
     * Sebutan angkatan untuk berkas unduhan: nama penuh + nomornya.
     *
     * Dipakai PDF dan lembar kerja, yang justru berkas yang dipakai merekap —
     * dan rekap yang menyebut "Scopus Camp Yogyakarta" tanpa nomornya
     * menggabungkan dua ratus angkatan jadi satu baris.
     */
    public static function sesiUntukBerkas(object $baris): ?string
    {
        $angkatan = self::angkatanBaris($baris);

        if ($angkatan === null) {
            return self::sesiBaris($baris);
        }

        return $angkatan['nomor'] === null
            ? $angkatan['nama']
            : $angkatan['nama'] . ' — angkatan ke-' . $angkatan['nomor'];
    }

    /** Nomor angkatan satu baris, atau null. Dipakai kolom tersendiri di lembar kerja. */
    public static function nomorAngkatanBaris(object $baris): ?string
    {
        return self::angkatanBaris($baris)['nomor'] ?? null;
    }

    /**
     * Keterangan angkatan satu baris, atau null kalau barisnya tidak
     * berangkatan.
     *
     * @return array<string, mixed>|null
     */
    public static function angkatanBaris(object $baris): ?array
    {
        if (empty($baris->angkatan_id)) {
            return null;
        }

        return self::angkatanLengkap()[$baris->angkatan_id] ?? null;
    }

    /**
     * Keterangan sesi satu baris: nama angkatannya, atau sesi apa adanya.
     *
     * Dua layanan tidak berangkatan sama sekali — Scopus Kafe menyimpan nama
     * sesinya sebagai teks, Clinik Scopus menyimpan sesi dan jamnya — jadi
     * kolom ini tidak bisa cuma menengok kategori_layanan.
     */
    public static function sesiBaris(object $baris): ?string
    {
        $angkatan = self::angkatanBaris($baris);

        if ($angkatan !== null) {
            // Nama PENUH di sini: metode ini dipakai kedua berkas unduhan,
            // dan di sana tidak ada kolom layanan di sebelahnya yang membuat
            // awalannya mubazir.
            return $angkatan['nama'];
        }

        $sesi = trim((string) ($baris->sesi ?? ''));

        return $sesi === '' ? null : $sesi;
    }

    /**
     * Bukti bayar satu baris: alamatnya, dan apakah berkasnya memang ada.
     *
     * Dua hal yang harus dipisah, bukan satu.
     *
     * Nilai yang tersimpan di kolomnya tidak seragam — sebagian sudah memuat
     * nama foldernya ('ScopusCamp/berkas.jpeg'), sebagian hanya nama berkasnya
     * ('berkas.jpg') — jadi alamatnya selalu dirakit dari folder layanan +
     * `basename()`. Terukur atas seluruh 183 baris yang punya nilai: aturan
     * itu menemukan berkas sebanyak atau lebih daripada memakai nilainya apa
     * adanya, di kelima tabel.
     *
     * Dan keberadaan berkasnya DIPERIKSA, tidak diandaikan: dari 183 nilai itu
     * hanya 111 yang berkasnya benar-benar ada di cakram — 72 hilang, 29 di
     * antaranya di Analisis Bibliometrik. Pendaftaran yang buktinya hilang
     * tidak bisa diverifikasi siapa pun lagi, dan itu harus terlihat di layar
     * alih-alih jadi gambar rusak. Harganya satu pemeriksaan cakram per baris,
     * dua puluh per halaman — terbatas oleh ukuran halaman, bukan oleh jumlah
     * data.
     *
     * @return array{url:?string, ada:bool, nilai:bool}
     */
    public static function buktiBaris(object $baris): array
    {
        $nilai = trim((string) ($baris->bukti ?? ''));
        $folder = self::SUMBER[$baris->layanan]['bukti_folder'] ?? null;

        if ($nilai === '' || $folder === null) {
            return ['url' => null, 'ada' => false, 'nilai' => $nilai !== ''];
        }

        $jalur = $folder . '/' . basename($nilai);

        return [
            'url' => asset($jalur),
            'ada' => is_file(public_path($jalur)),
            'nilai' => true,
        ];
    }

    /**
     * Ke mana satu baris dibuka: halaman rinciannya.
     *
     * Dulu menunjuk layar layanan masing-masing. Sejak kelima layar
     * pendaftaran per layanan dibuang, seluruh tindakan — melihat rincian,
     * membetulkan data, memindahkan status, menghapus — ada di satu halaman
     * yang sama, dengan bagian borang yang berbeda per layanan.
     *
     * Katalog `rute` per sumber TIDAK dipakai lagi untuk ini dan sudah
     * dibuang; satu nama rute untuk semuanya.
     */
    public static function tautanBaris(object $baris): ?string
    {
        if (! isset(self::SUMBER[$baris->layanan])) {
            return null;
        }

        return route('account.pendaftaran-layanan.rincian', [
            $baris->layanan,
            $baris->id,
        ]);
    }

    /** Waktu pendaftaran sebagai Carbon, atau null kalau kolomnya kosong. */
    public static function waktuBaris(object $baris): ?Carbon
    {
        return empty($baris->waktu) ? null : Carbon::parse($baris->waktu);
    }
}
