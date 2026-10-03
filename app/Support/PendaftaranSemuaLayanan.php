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
            'nomor_pola' => 'acak5',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => [1, 99],
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
            'nomor_pola' => 'acak5',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => [1, 99],
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
            'nomor_pola' => 'we_berurut',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => [500, 1500],
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
            'nomor_pola' => 'acak5',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_pemesanan',
            'kode_unik_rentang' => [1, 999],
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
                'total' => 'total_keseluruhan_pembayaran',
                'kode_unik' => 'kode_unik_pembayaran',
                'bukti' => 'gambar',
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
            'nomor_pola' => 'booking',
            'pakai_kode_unik' => true,
            'kolom_nomor' => 'id_transaksi',
            'kode_unik_rentang' => [1000, 1500],
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
                'sesi' => "CONCAT_WS(' · ', NULLIF(sesi, ''), NULLIF(jam_sesi, ''))",
                'sesi_mentah' => true,
            ],
        ],
    ];

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
        'bukti', 'catatan', 'sesi',
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
     * Nomor pendaftaran baru, mengikuti pola layanan yang bersangkutan.
     *
     * Tiga pola yang sudah dipakai jalur publik, dan ketiganya dipertahankan
     * apa adanya supaya baris yang dibuat panitia tidak bisa dibedakan dari
     * yang didaftarkan sendiri oleh orangnya:
     *
     *   acak5       lima huruf/angka besar — Scopus Camp, Bibliometrik, Kafe
     *   we_berurut  WE-YYYYMMDD-NNNN, berurut dalam satu hari
     *   booking     BOOK-dmYHis-ACAK5 — Clinik Scopus
     *
     * Keunikannya DIPERIKSA ke basis data, bukan diandaikan: lima aksara acak
     * dari 36 kemungkinan memberi 60 juta kombinasi, tetapi "jarang
     * bertabrakan" bukan "tidak pernah", dan nomor kembar berarti dua orang
     * menyebut nomor yang sama saat menghubungi panitia.
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

        for ($coba = 0; $coba < 40; $coba++) {
            $nomor = match ($sumber['nomor_pola']) {
                'we_berurut' => self::nomorWebinar(),
                'booking' => 'BOOK-' . now()->format('dmYHis') . '-' . self::acak(5),
                default => self::acak(5),
            };

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
        return self::SUMBER[$layanan]['kode_unik_rentang'] ?? [1, 999];
    }

    /** Kolom tempat nomor pendaftaran ditulis. */
    public static function kolomNomor(string $layanan): ?string
    {
        return self::SUMBER[$layanan]['kolom_nomor'] ?? null;
    }

    /** Lima aksara dari abjad besar dan angka — pola jalur publik. */
    private static function acak(int $panjang): string
    {
        $huruf = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $hasil = '';

        for ($i = 0; $i < $panjang; $i++) {
            $hasil .= $huruf[random_int(0, strlen($huruf) - 1)];
        }

        return $hasil;
    }

    /**
     * WE-YYYYMMDD-NNNN, berurut dalam satu hari.
     *
     * Urutannya dihitung dari nomor TERBESAR hari itu, bukan dari jumlah
     * barisnya: pendaftaran yang dihapus akan membuat hitungan baris memberi
     * nomor yang sudah pernah dipakai.
     */
    private static function nomorWebinar(): string
    {
        $awalan = 'WE-' . now()->format('Ymd') . '-';

        $terakhir = \App\WebinarEksklusifPendaftaran::where('id_transaksi', 'like', $awalan . '%')
            ->orderByDesc('id_transaksi')
            ->value('id_transaksi');

        $urutan = $terakhir === null ? 1 : ((int) substr($terakhir, -4)) + 1;

        return $awalan . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
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
