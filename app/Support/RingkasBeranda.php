<?php

namespace App\Support;

use App\Artikel;
use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Isi beranda yang datang dari basis data, dirakit di satu tempat.
 *
 * Sebelum ada kelas ini pengendali berandanya begini — seluruhnya:
 *
 *     public function home(Request $request)
 *     {
 *         return view('public.home');
 *     }
 *
 * Nol data. Seluruh beranda ditulis tangan di markahnya, sehingga halaman
 * depan tidak tahu apa pun tentang aplikasi di belakangnya: tidak ada jadwal
 * padahal 287 angkatan menunggu, tidak ada satu pun harga padahal tarifnya
 * sudah diurus admin, dan tidak ada artikel padahal blognya hidup. Orang yang
 * datang ingin tahu KAPAN dan BERAPA, lalu harus menebaknya lewat enam
 * halaman layanan satu-satu.
 *
 * TIDAK disinggung di sini: tiga angka besar di bagian "Angkanya"
 * (5.000 peserta, 2.315 naskah, 1.851 artikel terbit). Itu capaian yayasan
 * sejak jauh sebelum aplikasi ini ada — basis datanya cuma tahu 136
 * pendaftaran Scopus Camp — jadi menggantinya dengan hitungan hidup akan
 * MENGECILKAN capaiannya, bukan membuatnya jujur. Angka itu tetap milik
 * pemiliknya dan tinggal di config/beranda.php.
 */
class RingkasBeranda
{
    /** Sebanyak ini sesi yang dipajang; sisanya lewat tombol "lihat semua". */
    public const MAKS_SESI = 4;

    public const MAKS_ARTIKEL = 3;

    /**
     * Sebanyak ini angkatan terdekat dibaca sebagai kandidat.
     *
     * Dibatasi, bukan get() seluruhnya: angkatan terbukanya 233 dan yang
     * dipakai cuma empat.
     */
    public const JANGKAUAN_KANDIDAT = 60;

    /**
     * @return array{sesi: \Illuminate\Support\Collection, harga: array<string, int>, artikel: \Illuminate\Support\Collection, jumlah_sesi: int}
     */
    public static function rakit(): array
    {
        return [
            'sesi' => self::kartuSesi(self::sesiTerdekat()),
            'harga' => self::hargaMulaiDari(),
            'artikel' => self::artikelTerbaru(),
            'jumlah_sesi' => self::jumlahSesiDibuka(),
            'jumlah_referensi' => self::jumlahReferensi(),
        ];
    }

    /**
     * Angkatan diubah jadi larik siap-tampil.
     *
     * Dirapikan DI SINI, bukan di Blade, karena dua hal yang mudah salah:
     *
     *   - `mulai` dan `selesai` tersimpan sebagai STRING, bukan Carbon —
     *     model ini tidak punya $casts untuk keduanya. Memanggil
     *     ->translatedFormat() di Blade langsung melempar galat, dan
     *     Carbon::parse() yang berserakan di tampilan berarti tiap pemakai
     *     berikutnya harus menemukan sendiri bahwa itu string;
     *
     *   - nama bulannya harus dipaksa Indonesia. APP_LOCALE=en di .env, jadi
     *     format bawaan memulangkan "October". Itu sebabnya lewat
     *     RentangTanggal, yang sudah mengurusnya untuk seluruh aplikasi.
     *
     * @param  iterable<KategoriLayanan>  $daftar
     * @return Collection<int, array<string, mixed>>
     */
    public static function kartuSesi(iterable $daftar): Collection
    {
        return (new Collection($daftar))->map(function (KategoriLayanan $s) {
            $mulai = Carbon::parse($s->mulai)->locale('id');
            $selesai = $s->selesai ? Carbon::parse($s->selesai) : null;

            $harga = (int) $s->harga_setelah_promo;
            $penuh = (int) $s->harga_sebelum_promo;

            return [
                'judul' => (string) $s->nama,
                'layanan' => (string) $s->layanan,
                'layanan_nama' => $s->nama_layanan,
                'tanggal' => RentangTanggal::tulis($mulai, $selesai, true),
                'hari' => $mulai->translatedFormat('l'),
                'tgl' => $mulai->translatedFormat('j'),
                'bulan' => $mulai->translatedFormat('M'),
                'jam' => $s->jam,
                'daring' => $s->acaraDaring(),
                'tempat' => $s->acaraDaring()
                    ? (trim((string) $s->platform) ?: 'Daring')
                    : (trim((string) $s->lokasi) ?: null),
                'pemateri' => trim((string) $s->pemateri) ?: null,
                'pemateri_jabatan' => trim((string) $s->pemateri_jabatan) ?: null,
                'pemateri_foto' => $s->alamat_pemateri,

                // Harga nol TIDAK dipajang sebagai "Rp 0" oleh tampilannya;
                // terukur ada angkatan uji yang biayanya memang 0.
                'harga' => $harga,
                'harga_penuh' => $penuh > $harga ? $penuh : null,

                'sisa' => $s->total_kuota === null ? null : (int) $s->sisa_kuota,
                'tautan' => self::tautanSesi($s),
            ];
        })->values();
    }

    /**
     * Ke mana kartu jadwal mengarah.
     *
     * Tiap layanan punya bentuk tautannya sendiri dan tidak ada yang seragam:
     * Scopus Camp dan Bibliometrik punya halaman "selengkapnya" per angkatan,
     * Webinar Eksklusif langsung ke borang daftarnya. Yang belum punya
     * halaman per angkatan diarahkan ke daftar layanannya — lebih baik
     * mendarat di daftar yang benar daripada di 404.
     */
    private static function tautanSesi(KategoriLayanan $s): string
    {
        return match ($s->layanan) {
            'scopus_camp' => route('public.scopuscamp.Selengkapnya', ['id' => $s->getKey()]),
            'bibliometrik' => route('public.analisisbibliometrik.Selengkapnya', ['id' => $s->getKey()]),
            'webinar_eksklusif' => route('public.webinareksklusif.daftar', ['id' => $s->getKey()]),
            'scopus_kafe' => url('/Scopus-Kafe'),
            'clinik_scopus' => url('/Clinik-Scopus'),
            default => url('/'),
        };
    }

    /**
     * Angkatan terdekat yang BENAR-BENAR masih bisa didaftari.
     *
     * Tiga syaratnya, dan tiap satunya pernah jadi keluhan:
     *
     *   - `status = active`, jelas;
     *
     *   - tanggal mulainya BELUM tiba. Bukan `coalesce(selesai, mulai) >=
     *     hari ini` seperti saringan halaman webinar: patokan itu masih
     *     memuat angkatan tiga hari yang sudah masuk hari kedua, dan
     *     KategoriLayanan::tanggalMulaiSudahTiba() sendiri menyebut angkatan
     *     begitu sudah tertutup. Memajangnya di beranda berarti mengajak
     *     orang membayar acara yang sudah separuh jalan;
     *
     *   - kuotanya masih ada. Kuota NULL berarti tak dibatasi, bukan nol.
     *
     * @return \Illuminate\Support\Collection<int, KategoriLayanan>
     */
    public static function sesiTerdekat(int $maks = self::MAKS_SESI)
    {
        /*
         * Diambil JAUH lebih banyak daripada yang dipajang, lalu disaring
         * satu per layanan di bawah.
         *
         * Tanpa itu, empat kartunya terukur jadi tiga Scopus Camp dan satu
         * webinar — sebab Scopus Camp punya 285 angkatan terbuka sementara
         * layanan lain punya satu. Orang yang datang untuk Analisis
         * Bibliometrik melihat bagian "jadwal terdekat" yang tidak
         * menyebutnya sama sekali, lalu menyimpulkan kelasnya tidak ada.
         */
        $kandidat = self::kueriTerbuka()
            ->orderBy('mulai')
            ->orderBy('id')
            ->limit(self::JANGKAUAN_KANDIDAT)
            ->get();

        $sesi = self::satuPerLayanan($kandidat, $maks);

        /*
         * Promonya dimuat SEKALI untuk seluruh daftar. Dibaca per kartu,
         * empat sesi berarti empat kueri yang bentuknya sama persis — dan
         * tanpa ini harga_setelah_promo memulangkan harga penuh, sehingga
         * beranda menjanjikan angka yang lebih besar daripada yang ditagih
         * borangnya.
         */
        PromoAngkatan::muat($sesi);

        return $sesi;
    }

    /** Berapa angkatan yang sedang dibuka seluruhnya, untuk kalimat pengantarnya. */
    public static function jumlahSesiDibuka(): int
    {
        return self::kueriTerbuka()->count();
    }

    /**
     * Harga termurah yang masih berlaku per layanan, buat label "mulai dari".
     *
     * Sumbernya tarif induk (`clinikscopus_biaya_persesi`), bukan `biaya`
     * tiap angkatan: tarif itulah yang diurus admin dan yang dipakai borang
     * pendaftaran, dan layanan tanpa angkatan terbuka — Scopus Kafe, Clinik
     * Scopus — tetap punya harga untuk dipajang.
     *
     * Yang tarif berlakunya tidak ada atau nol TIDAK muncul di hasil, jadi
     * kartunya diam saja alih-alih memajang "mulai dari Rp 0".
     *
     * @return array<string, int>
     */
    public static function hargaMulaiDari(): array
    {
        $harga = [];

        foreach (ClinikScopusBiayaPersesi::semuaYangBerlaku() as $tarif) {
            $nominal = (int) $tarif->biaya_persesi;

            if ($nominal < 1) {
                continue;
            }

            $kode = (string) $tarif->layanan;

            // Varian termurah yang menang: Scopus Camp Jawa 5,5 jt dipajang,
            // bukan luar Jawa 6,5 jt — "mulai dari" memang berarti yang
            // paling rendah.
            $harga[$kode] = isset($harga[$kode]) ? min($harga[$kode], $nominal) : $nominal;
        }

        return $harga;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Artikel>
     */
    public static function artikelTerbaru(int $maks = self::MAKS_ARTIKEL)
    {
        return Artikel::query()
            ->where('status', 'publish')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($maks)
            ->get(['id', 'judul', 'gambar_depan', 'created_at', 'dilihat']);
    }

    /**
     * Memilih yang paling dekat dari TIAP layanan dulu, baru sisanya.
     *
     * Urutan tanggalnya dipertahankan di kedua putaran, jadi kartu pertama
     * tetap angkatan yang paling cepat berlangsung.
     *
     * @param  \Illuminate\Support\Collection<int, KategoriLayanan>  $kandidat
     * @return \Illuminate\Support\Collection<int, KategoriLayanan>
     */
    private static function satuPerLayanan($kandidat, int $maks)
    {
        $terpilih = $kandidat->unique('layanan')->take($maks);

        if ($terpilih->count() < $maks) {
            $sisa = $kandidat
                ->reject(fn (KategoriLayanan $s) => $terpilih->contains(
                    fn (KategoriLayanan $p) => $p->getKey() === $s->getKey()
                ))
                ->take($maks - $terpilih->count());

            $terpilih = $terpilih->concat($sisa);
        }

        return $terpilih->sortBy('mulai')->values();
    }

    /**
     * Berapa paper yang bisa ditelusuri di Refrensi Paper.
     *
     * Dihitung, bukan ditulis: beranda menyebut angka ini sebagai alasan
     * membuka halamannya, dan angka yang ditulis tangan akan berhenti benar
     * pada hari admin menambah entri berikutnya.
     */
    public static function jumlahReferensi(): int
    {
        return \App\RefrensiPaper::query()->count();
    }

    /** Satu saringan untuk daftarnya dan untuk hitungannya. */
    private static function kueriTerbuka()
    {
        return KategoriLayanan::query()
            ->where('status', 'active')
            ->whereNotNull('mulai')
            ->whereDate('mulai', '>', Carbon::today()->toDateString())
            ->where(function ($kueri) {
                $kueri->whereNull('total_kuota')->orWhere('sisa_kuota', '>', 0);
            });
    }
}
