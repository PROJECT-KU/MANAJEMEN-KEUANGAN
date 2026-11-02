<?php

namespace App\Support;

use App\KategoriLayanan;
use App\PromoLayanan;

/**
 * Promo mana yang sedang berlaku untuk sebuah angkatan.
 *
 * SATU-SATUNYA pintu untuk pertanyaan itu. Dibaca dari enam tempat — borang
 * pendaftaran panitia, layar daftar angkatan, rinciannya, ekspor, halaman
 * umum, dan layar promo sendiri — dan tiap tempat yang menyusun kuerinya
 * sendiri adalah satu kesempatan lagi untuk lupa salah satu syaratnya.
 *
 * Dua hal yang dikerjakan di sini dan tidak boleh ditiru di tempat lain:
 *
 * 1. **Dimuat berkelompok.** Layar angkatan memuat 10 baris per halaman;
 *    dibaca satu per satu, itu 10 kueri yang bentuknya sama persis, dan
 *    jumlahnya tumbuh seiring daftar per halamannya dinaikkan.
 *
 * 2. **Promo yang menang dipilih menurut POTONGANNYA, bukan tanggalnya.**
 *    Satu angkatan boleh kena lebih dari satu promo — "Promo Akhir Tahun"
 *    30% dan "Promo Alumni Reuni" Rp 500.000 bisa dicentang pada angkatan
 *    yang sama tanpa ada yang salah. Yang dipakai yang paling menguntungkan
 *    pendaftarnya, sama seperti aturan alumni lawan rombongan yang sudah
 *    berlaku di BuatPendaftaran.
 */
class PromoAngkatan
{
    /**
     * kategori_id => promo yang menang, atau false kalau sudah dicari dan
     * memang tidak ada. Dibedakan dari null supaya angkatan tanpa promo tidak
     * dicari ulang setiap kali ditanya.
     *
     * @var array<string, PromoLayanan|false>
     */
    private static array $peta = [];

    /**
     * layanan|varian => tarif yang berlaku, atau false kalau sudah dicari
     * dan memang tidak ada.
     *
     * Tanpa ingatan ini, hargaDasar() memanggil basis data SEKALI PER
     * ANGKATAN. Terukur saat tarif jadi sumber harga: pemuatan promo untuk
     * sepuluh angkatan naik dari 2 kueri jadi 13 — persis pemuatan per
     * baris yang kelas ini dibuat untuk menghindarinya.
     *
     * @var array<string, \App\ClinikScopusBiayaPersesi|false>
     */
    private static array $tarif = [];

    /** Dibuang uji yang mengubah promonya di tengah jalan. */
    public static function lupakan(): void
    {
        self::$peta = [];
        self::$tarif = [];
    }

    /**
     * Memuat promo untuk sekumpulan angkatan sekaligus.
     *
     * Dipanggil pengendali sebelum daftarnya dirender. Aman dipanggil
     * berulang: yang sudah ada di ingatan tidak dicari lagi.
     *
     * @param  iterable<KategoriLayanan>  $daftar
     */
    public static function muat(iterable $daftar): void
    {
        $perlu = [];

        foreach ($daftar as $angkatan) {
            $id = (string) $angkatan->getKey();

            if ($id !== '' && ! array_key_exists($id, self::$peta)) {
                $perlu[$id] = $angkatan;
            }
        }

        if ($perlu === []) {
            return;
        }

        /*
         * Satu kueri untuk semua angkatan yang diminta. Pivotnya ikut
         * diambil supaya tiap promo tahu ia milik angkatan yang mana —
         * tanpa itu, promo yang kena lima angkatan tidak bisa dibedakan
         * dari lima promo yang masing-masing kena satu.
         */
        /*
         * Kuota disaring SESUDAH kueri, bukan di dalamnya.
         *
         * Jumlah pemakaian tersebar di tiga tabel pendaftaran, jadi tidak ada
         * satu pun kolom di `promo_layanan` yang bisa dibandingkan di SQL.
         * Dipaksa lewat subkueri, kuerinya jadi tiga subkueri bersarang yang
         * dijalankan untuk SETIAP promo — sementara DampakPromo sudah
         * menghitung semuanya sekali untuk seluruh promo.
         */
        /*
         * HANYA promo otomatis — yang tidak punya kode.
         *
         * Kelas ini menjawab "harga berapa yang dipajang kepada orang yang
         * belum mengetikkan apa-apa". Promo berkode ikut di sini berarti
         * harganya sudah turun dan harga lamanya sudah tercoret sebelum satu
         * pun kode dimasukkan — dan kodenya tidak ada gunanya lagi.
         *
         * Keputusan pemiliknya 31 Okt 2026. Promo berkode dicari lewat
         * denganKode() di bawah, dan hanya saat kodenya memang dikirim.
         */
        $promo = PromoLayanan::query()
            ->berjalan()
            ->otomatis()
            ->whereHas('angkatan', fn ($q) => $q->whereIn('kategori_layanan.id', array_keys($perlu)))
            ->with(['angkatan' => fn ($q) => $q
                ->whereIn('kategori_layanan.id', array_keys($perlu))
                ->select('kategori_layanan.id')])
            ->get()
            ->reject(fn (PromoLayanan $p) => $p->kuotaHabis())
            ->values();

        // Semua yang diminta ditandai sudah dicari — termasuk yang tidak
        // kebagian promo. Tanpa penandaan itu, angkatan tanpa promo memicu
        // satu kueri tunggal tiap kali ditanya, dan pemuatan berkelompoknya
        // jadi tidak ada gunanya.
        foreach ($perlu as $id => $angkatan) {
            self::$peta[$id] = false;
        }

        foreach ($promo as $p) {
            foreach ($p->angkatan as $kena) {
                $id = (string) $kena->getKey();

                if (! isset($perlu[$id])) {
                    continue;
                }

                self::$peta[$id] = self::menang(
                    self::$peta[$id] ?: null, $p, self::hargaDasar($perlu[$id])
                );
            }
        }
    }

    /** Promo yang berlaku untuk satu angkatan, atau null. */
    public static function untuk(KategoriLayanan $angkatan): ?PromoLayanan
    {
        $id = (string) $angkatan->getKey();

        if ($id === '') {
            return null;
        }

        if (! array_key_exists($id, self::$peta)) {
            self::muat([$angkatan]);
        }

        return self::$peta[$id] ?: null;
    }

    /**
     * Promo BERKODE yang cocok untuk satu angkatan, atau null.
     *
     * Tidak lewat $peta: yang disimpan di sana jawaban untuk "promo apa yang
     * berlaku tanpa diminta", dan jawaban itu tidak boleh berubah hanya
     * karena seseorang mengetikkan kode di satu permintaan.
     *
     * Syaratnya sama ketatnya dengan promo otomatis — sedang berjalan,
     * kuotanya belum habis, dan angkatan ini memang dicentang padanya —
     * ditambah kodenya cocok. Yang longgar salah satunya membuat kode
     * kedaluwarsa tetap memotong harga.
     */
    public static function denganKode(
        KategoriLayanan $angkatan,
        ?string $kode,
        ?string $telp = null
    ): ?PromoLayanan {
        $kode = PromoLayanan::rapikanKode($kode);

        if ($kode === '') {
            return null;
        }

        $id = (string) $angkatan->getKey();

        if ($id === '') {
            return null;
        }

        return PromoLayanan::query()
            ->berjalan()
            ->berkode($kode)
            ->whereHas('angkatan', fn ($q) => $q->where('kategori_layanan.id', $id))
            ->get()
            ->reject(fn (PromoLayanan $p) => $p->kuotaHabis())

            /*
             * SYARAT RIWAYAT diperiksa di sini, bukan di dalam kueri.
             *
             * Pertanyaannya "nomor ini pernah lunas di layanan apa saja",
             * dan jawabannya tersebar di enam tabel pendaftaran yang
             * kosakata statusnya berbeda-beda. Dipaksa jadi subkueri, ia
             * jadi enam subkueri bersarang untuk SETIAP promo; di sini ia
             * satu pemeriksaan untuk promo yang sudah lolos saringan lain,
             * dan promo tanpa syarat riwayat tidak menyentuh basis data
             * sama sekali.
             */
            ->filter(fn (PromoLayanan $p) => RiwayatLunas::pernahLunas(
                $telp, $p->syarat_layanan_bersih
            ))

            /*
             * Yang paling menguntungkan pendaftarnya kalau kebetulan ada
             * lebih dari satu promo berkode sama pada angkatan ini. Aturan
             * yang sama dipakai promo otomatis di menang().
             */
            ->sortByDesc(fn (PromoLayanan $p) => $p->potonganUntuk(self::hargaDasar($angkatan)))
            ->first();
    }

    /**
     * Harga satu kursi SEBELUM promo.
     *
     * `total_biaya` sudah memuat diskon angkatannya sendiri dan itulah yang
     * ditagihkan; `biaya` harga sebelum diskon itu. Rumusnya harus sama
     * persis dengan BuatPendaftaran::hitungTotal — kalau berbeda, harga yang
     * dipajang di halaman umum dan harga yang benar-benar ditagih tidak
     * pernah cocok, dan tidak ada galat yang memberi tahu.
     */
    public static function hargaDasar(KategoriLayanan $angkatan): int
    {
        /*
         * TARIF LAYANAN yang menentukan, bukan kolom harga di angkatannya.
         *
         * Keputusan pemiliknya 2 Nov 2026. Sebelum ini harganya dibaca dari
         * `total_biaya ?: biaya` milik angkatan — angka yang diketik ulang
         * tiap kali angkatan baru dibuat. Akibatnya terukur di Scopus Camp
         * Yogyakarta ke-202: tarifnya Rp 5.500.000, tetapi angkatan itu
         * menyimpan `total_biaya` Rp 4.950.000 beserta diskon 10% yang
         * sudah dibakukan ke dalamnya. Halaman umum memajang 4.950.000 dan
         * tidak ada yang tahu dari mana angka itu, sebab layar Tarif Layanan
         * menyebut angka yang lain.
         *
         * Lebih buruk lagi, angkatan itu juga menyimpan kode diskon bernama
         * sama dengan promonya. Mengetik kodenya memotong 550.000 SEKALI
         * LAGI di atas harga yang sudah dipotong — 4.400.000 untuk kelas
         * yang dimaksudkan diskon 10%, tanpa satu galat pun.
         *
         * Tarifnya dibaca menurut VARIAN angkatannya: Scopus Camp Jawa dan
         * luar Jawa berbeda harganya, dan satu tarif untuk keduanya akan
         * menagih peserta luar Jawa dengan harga Jawa.
         */
        /*
         * DUA LAYANAN, bukan semuanya — dan daftarnya DIUKUR, bukan dipilih
         * sembarangan.
         *
         * Dicoba dulu untuk SEMUA layanan: rangkaian ujinya memerah di 9
         * tempat — Webinar Eksklusif, Doku, perakit deskripsi, dan layar
         * promo. Sebabnya bukan ujinya rewel, melainkan artinya memang
         * berbeda: sesi Webinar Eksklusif punya harga SENDIRI-SENDIRI dan
         * tarifnya cuma angka bawaan, sementara angkatan Scopus Camp
         * memang dimaksudkan mengikuti satu tarif baku per varian.
         *
         * Class Online ikut sejak 2 Nov 2026, hari layanan itu dibuka.
         * Bentuknya memang disalin dari Scopus Camp atas permintaan
         * pemiliknya — berangkatan, satu tarif baku, harga yang sama untuk
         * seluruh angkatan — jadi alasan yang membuat Camp membaca tarif
         * berlaku sama persis di sini. Dibiarkan membaca kolom harga
         * angkatannya, layar Tarif Layanan dan halaman umum akan menyebut
         * dua angka berbeda untuk satu kelas yang sama, persis cacat yang
         * ditemukan pada angkatan Camp ke-202.
         *
         * INI PERKARA UANG. Menambah layanan ketiga ke daftar ini mengubah
         * angka yang ditagihkan kepada orang, jadi daftarnya ditulis di sini
         * terang-terangan dan tidak boleh ditebak dari nama layanan.
         */
        if (! in_array((string) $angkatan->layanan, ['scopus_camp', 'online_training'], true)) {
            return (int) ($angkatan->total_biaya ?: $angkatan->biaya);
        }

        $varian = $angkatan->varian !== null && $angkatan->varian !== ''
            ? (string) $angkatan->varian
            : null;

        $kunciTarif = $angkatan->layanan . '|' . ($varian ?? '');

        if (! array_key_exists($kunciTarif, self::$tarif)) {
            self::$tarif[$kunciTarif] = \App\ClinikScopusBiayaPersesi::berlaku(
                (string) $angkatan->layanan, $varian
            ) ?: false;
        }

        $tarif = self::$tarif[$kunciTarif] ?: null;

        if ($tarif !== null) {
            $dariTarif = (int) preg_replace('/\D+/', '', (string) $tarif->biaya_persesi);

            if ($dariTarif > 0) {
                return $dariTarif;
            }
        }

        /*
         * Jatuh kembali ke kolom angkatannya kalau tarifnya belum disetel.
         *
         * Dipaksa nol saat tarifnya tidak ada, seluruh angkatan layanan itu
         * mendadak berharga Rp 0 dan borangnya menerima pendaftaran gratis —
         * kegagalan yang jauh lebih mahal daripada harga yang ketinggalan.
         */
        return (int) ($angkatan->total_biaya ?: $angkatan->biaya);
    }

    /**
     * Yang mana dari dua promo yang lebih menguntungkan pada harga ini.
     *
     * Seri dimenangkan yang sudah ada, bukan yang baru datang: urutan `get()`
     * tidak dijamin, jadi seri yang dimenangkan pendatang membuat promo yang
     * tampil berganti-ganti antar muat halaman untuk data yang sama.
     */
    private static function menang(?PromoLayanan $lama, PromoLayanan $baru, int $harga): PromoLayanan
    {
        if ($lama === null) {
            return $baru;
        }

        return $baru->potonganUntuk($harga) > $lama->potonganUntuk($harga) ? $baru : $lama;
    }
}
