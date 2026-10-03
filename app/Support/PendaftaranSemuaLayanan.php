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
     * `rute` menunjuk layar layanan itu sendiri. Layar ini TIDAK melunasi
     * atau membatalkan apa pun: aturan kuotanya berbeda di tiap layanan
     * (webinar mengembalikan kursi, camp tidak punya kursi untuk dikembalikan)
     * dan menyeragamkannya dari sini berarti menuliskan ulang lima aturan
     * yang sudah ada di tempatnya masing-masing. Yang dikerjakan layar ini
     * mencari dan menghitung; tindakannya tetap di layar layanannya.
     */
    private const SUMBER = [
        'scopus_camp' => [
            'nama' => 'Scopus Camp',
            'tabel' => 'scopus_camp_pendaftaran',
            'ikon' => 'fa-campground',
            'warna' => 'mis-hijau',
            'rute' => ['account.pendaftaranscopuscamp.edit', 'id'],
            'bukti_folder' => 'ScopusCamp',
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
            'rute' => ['account.analisisbibliometrik.edit', 'id'],
            'bukti_folder' => 'bibliometrik',
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
            // Satu-satunya layanan yang layar pendaftarnya memang punya
            // tindakan (lunasi/batalkan), tetapi tidak punya halaman per
            // baris. Dibuka lewat pencarian nomornya, jadi orangnya mendarat
            // tepat di baris itu beserta tombolnya.
            'rute' => ['account.webinarpendaftar.index', 'cari'],
            'rute_kunci' => 'nomor',
            // Webinar TIDAK pernah mengunggah bukti: pembayarannya dicocokkan
            // lewat kode unik Rp 500-1.500 terhadap mutasi rekening, bukan
            // lewat tangkapan layar. Terukur nol dari 4 baris berisi gambar.
            'bukti_folder' => null,
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
            'rute' => ['account.pendaftaran-scopus-kafe.edit', 'id'],
            'bukti_folder' => 'pendaftaran_scopus_kafe',
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
            'rute' => ['account.Clinik-Scopus-Riwayat-Pemesanan.detail', 'id'],
            'bukti_folder' => 'ClinikScopusPemesanan',
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

    /** @var array<string, string>|null */
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
     * Nama angkatan untuk seluruh id yang dipakai, satu kueri untuk semuanya.
     *
     * Dicari sekali per permintaan, bukan per baris: dua puluh baris yang
     * masing-masing memanggil KategoriLayanan::find() adalah dua puluh kueri
     * untuk tabel yang isinya 59 baris.
     *
     * @return array<string, string>
     */
    public static function namaAngkatan(): array
    {
        if (self::$angkatan !== null) {
            return self::$angkatan;
        }

        return self::$angkatan = KategoriLayanan::query()
            ->pluck('nama', 'id')->all();
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
        if (! empty($baris->angkatan_id)) {
            $nama = self::namaAngkatan()[$baris->angkatan_id] ?? null;

            if ($nama !== null) {
                return $nama;
            }
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
     * Ke mana satu baris dibuka: layar layanannya sendiri.
     *
     * Empat layanan punya halaman per baris; Webinar Eksklusif tidak, jadi ia
     * dibuka lewat pencarian nomornya di layar pendaftar webinar — orangnya
     * mendarat tepat di baris itu beserta tombol lunasi dan batalkannya.
     */
    public static function tautanBaris(object $baris): ?string
    {
        $s = self::SUMBER[$baris->layanan] ?? null;

        if ($s === null || empty($s['rute'])) {
            return null;
        }

        [$rute, $param] = $s['rute'];

        if (! \Illuminate\Support\Facades\Route::has($rute)) {
            return null;
        }

        $kunci = $s['rute_kunci'] ?? 'id';
        $nilai = $baris->{$kunci} ?? null;

        return $nilai === null ? null : route($rute, [$param => $nilai]);
    }

    /** Waktu pendaftaran sebagai Carbon, atau null kalau kolomnya kosong. */
    public static function waktuBaris(object $baris): ?Carbon
    {
        return empty($baris->waktu) ? null : Carbon::parse($baris->waktu);
    }
}
