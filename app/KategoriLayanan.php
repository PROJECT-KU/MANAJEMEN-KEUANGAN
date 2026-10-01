<?php

namespace App;

use App\ClinikScopusBiayaPersesi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu angkatan (kategori) dari layanan jasa mana pun.
 *
 * Dulu ini dua tabel yang isinya sama persis — `scopus_camp_kategori` dan
 * `categories_analisis_bibliometrik` — dengan 21 kolom yang identik. Dua tabel
 * untuk satu bentuk berarti dua pengendali, dua layar, dan dua tempat yang
 * harus diubah setiap kali aturannya berubah.
 *
 * Kolom `layanan` memakai kunci yang SAMA dengan katalog di
 * katalog Layanan, supaya satu angkatan bisa langsung
 * menemukan tarif dan fasilitas induknya tanpa peta perantara.
 *
 * `lokasi` dan `best_price` hanya terpakai Scopus Camp; keduanya boleh NULL
 * dan dibiarkan kosong oleh layanan lain.
 */
class KategoriLayanan extends Model
{
    /** @var array<string, int>|null */
    private static ?array $pendaftar = null;

    /** Dibuang uji yang mengubah pendaftarnya di tengah jalan. */
    public static function lupakanPendaftar(): void
    {
        self::$pendaftar = null;
    }

    protected $table = 'kategori_layanan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'token',
        'layanan',
        'varian',
        'nama',
        'nama_ke',
        'mulai',
        'selesai',
        'total_kuota',
        'sisa_kuota',
        'desc',
        'best_price',
        'lokasi',
        'biaya',
        'ppn',
        'tipe_diskon',
        'diskon_persentase',
        'nominal_diskon',
        'kode_diskon',
        'total_biaya',
        'status',
        'group_wa',
        'gambar',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // ----------------------------------------------------------------- kueri

    public function scopeLayanan(Builder $kueri, string $layanan): Builder
    {
        return $kueri->where('layanan', $layanan);
    }

    // -------------------------------------------------------------- tautan

    /** Tarif induk yang jadi harga patokan angkatan ini. */
    public function tarif(): ?ClinikScopusBiayaPersesi
    {
        return ClinikScopusBiayaPersesi::berlaku($this->layanan, $this->varian);
    }

    /**
     * Jumlah pendaftar SEMUA angkatan sekaligus, dalam dua kueri berkelompok.
     *
     * Bukan satu kueri per baris: daftarnya berhalaman sepuluh dan jumlahnya
     * dipakai di tiga tempat (lencana kuota, penjaga kuota, tautan pendaftar),
     * jadi dibaca satu per satu ia jadi tiga puluh kueri.
     *
     * Dua tabel karena pendaftarannya memang dua: Scopus Camp dan Bibliometrik
     * menyimpan pendaftarnya sendiri-sendiri, keduanya menunjuk `kategori_id`.
     *
     * @return array<string, int>
     */
    public static function hitungPendaftar(): array
    {
        if (self::$pendaftar !== null) {
            return self::$pendaftar;
        }

        $hasil = [];

        foreach (['scopus_camp_pendaftaran', 'analisis_bibliometrik'] as $tabel) {
            $baris = DB::table($tabel)
                ->whereNotNull('kategori_id')
                ->selectRaw('kategori_id, count(*) as n')
                ->groupBy('kategori_id')
                ->pluck('n', 'kategori_id');

            foreach ($baris as $id => $n) {
                $hasil[$id] = ($hasil[$id] ?? 0) + (int) $n;
            }
        }

        return self::$pendaftar = $hasil;
    }

    /** Berapa orang yang sudah mendaftar di angkatan ini. */
    public function getJumlahPendaftarAttribute(): int
    {
        return self::hitungPendaftar()[$this->getKey()] ?? 0;
    }

    /** Kuotanya sudah habis. */
    public function getKuotaHabisAttribute(): bool
    {
        return $this->total_kuota !== null && (int) $this->sisa_kuota < 1;
    }

    /**
     * Aktif padahal tanggalnya sudah lewat.
     *
     * Tidak ada apa pun yang menutup angkatan otomatis, jadi ia bisa terpajang
     * sebagai "Aktif" berbulan-bulan sesudah acaranya selesai.
     */
    public function getSudahLewatAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $akhir = $this->selesai ?: $this->mulai;

        return $akhir && \Carbon\Carbon::parse($akhir)->endOfDay()->isPast();
    }

    /**
     * Folder tempat sampul angkatan disimpan, menurut layanannya.
     *
     * Kebiasaannya sudah terlanjur berbeda per layanan — halaman publik Scopus
     * Camp membaca dari `ScopusCamp/`, Bibliometrik dari `bibliometrik/` —
     * dan keduanya memakai basename(), jadi yang penting nama berkasnya ada di
     * folder yang benar. Layanan yang belum punya halaman publik ditaruh di
     * folder sendiri daripada menumpang salah satu di atas.
     */
    public const FOLDER_SAMPUL = [
        'scopus_camp' => 'ScopusCamp',
        'bibliometrik' => 'bibliometrik',
    ];

    public function folderSampul(): string
    {
        return self::FOLDER_SAMPUL[$this->layanan] ?? 'angkatan';
    }

    /** Alamat sampul untuk ditampilkan; null kalau belum ada. */
    public function getAlamatSampulAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        $berkas = public_path($this->folderSampul() . '/' . basename($this->gambar));

        return is_file($berkas) ? asset($this->folderSampul() . '/' . basename($this->gambar)) : null;
    }

    /**
     * Keadaan yang perlu ditindaklanjuti; kuncinya dipakai di alamat.
     *
     * Labelnya sengaja pendek: ia tampil di dalam menu saringan selebar satu
     * kolom, dan "Tidak menemukan tarif induk" terpotong jadi
     * "Tidak menemukan tarif i…".
     */
    public const PERLU = [
        'aktif-lewat' => 'Aktif tapi sudah lewat',
        'draf-lewat' => 'Draf kadaluwarsa',
        'tanpa-tarif' => 'Tanpa tarif induk',
        'sampul-hilang' => 'Sampul hilang',
    ];

    /**
     * Draf yang tanggalnya sudah lewat.
     *
     * Lencana "Lewat" hanya untuk angkatan AKTIF — ia memang peringatan bahwa
     * sesuatu masih terpajang padahal sudah selesai. Draf tidak terpajang di
     * mana pun, jadi ia tidak pernah ditandai dan menumpuk diam-diam: terukur
     * 24 dari 60 baris, yang terlama bertanggal Oktober 2025.
     */
    public function getDrafKadaluwarsaAttribute(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $akhir = $this->selesai ?: $this->mulai;

        return $akhir && Carbon::parse($akhir)->endOfDay()->isPast();
    }

    /**
     * Alasan baris ini perlu dicek; kosong artinya tidak ada masalah.
     *
     * Dihitung di model, bukan di Blade. Versinya yang di Blade memakai blok
     * @php ... @endphp — dan blok itu ditaruh SESUDAH beberapa @php(...)
     * sebaris yang sudah ada di berkas yang sama. Blade memasangkan @php(
     * sebaris itu dengan @endphp milik blok baru, menelan semua yang di
     * antaranya, lalu berhenti mengompilasi; halamannya galat 500 sambil
     * menyebut variabel yang sama sekali tidak bersalah.
     *
     * @return array<int, string>
     */
    public function getPerluDicekAttribute(): array
    {
        $alasan = [];

        /*
         * Yang PALING mendesak justru ini: angkatannya masih terpajang di
         * halaman publik padahal acaranya sudah selesai. Versi pertama ubin
         * "Perlu dicek" melewatkannya — ia cuma memuat tiga keadaan lain —
         * sehingga keadaan yang paling perlu ditindaklanjuti tidak bisa
         * ditemukan lewat saringan yang justru dibuat untuk itu.
         */
        if ($this->sudah_lewat) {
            $alasan[] = 'masih aktif padahal tanggalnya sudah lewat';
        }

        if ($this->draf_kadaluwarsa) {
            $alasan[] = 'draf yang tanggalnya sudah lewat';
        }

        if ($this->tarif_hilang) {
            $alasan[] = 'tidak menemukan tarif induk, jadi harga dan fasilitasnya kosong';
        }

        if ($this->sampul_hilang) {
            $alasan[] = 'berkas sampulnya tidak ada di peladen';
        }

        return $alasan;
    }

    /**
     * Alasan yang belum punya lencananya sendiri di daftar.
     *
     * "Masih aktif padahal tanggalnya lewat" sudah ditandai lencana "Lewat"
     * yang bisa ditekan untuk menonaktifkan. Menambahkan lencana "Perlu dicek"
     * di sebelahnya berarti dua peringatan untuk satu hal yang sama.
     *
     * @return array<int, string>
     */
    public function getPerluDicekLainAttribute(): array
    {
        return array_values(array_filter(
            $this->perlu_dicek,
            fn ($a) => ! str_starts_with($a, 'masih aktif')
        ));
    }

    /** Tidak menemukan tarif induk, jadi harga dan fasilitasnya kosong. */
    public function getTarifHilangAttribute(): bool
    {
        return $this->tarif() === null;
    }

    /** Kolom gambarnya terisi tetapi berkasnya tidak ada di cakram. */
    public function getSampulHilangAttribute(): bool
    {
        return trim((string) $this->gambar) !== '' && $this->alamat_sampul === null;
    }

    /**
     * Pasangan "layanan|varian" yang PUNYA tarif berlaku.
     *
     * Dipakai menyaring kebalikannya di SQL; tanpa ini, mencari angkatan yang
     * tarifnya hilang berarti memuat seluruh tabel lalu menyaring di PHP, dan
     * penomoran halamannya ikut rusak.
     *
     * @return array<int, string>
     */
    public static function pasanganBertarif(): array
    {
        return array_keys(ClinikScopusBiayaPersesi::semuaYangBerlaku()->all());
    }

    /**
     * Jalur sampul yang berkasnya TIDAK ada di cakram.
     *
     * Yang diperiksa jalur yang BERBEDA, bukan tiap baris: empat puluh satu
     * angkatan Yogyakarta menunjuk satu berkas yang sama, jadi memeriksa per
     * baris berarti empat puluh satu kali stat() untuk jawaban yang sama.
     *
     * @return array<int, string>
     */
    public static function jalurSampulHilang(): array
    {
        $baris = self::query()
            ->whereNotNull('gambar')->where('gambar', '<>', '')
            ->select('layanan', 'gambar')->distinct()->get();

        $hilang = [];

        foreach ($baris as $b) {
            $folder = self::FOLDER_SAMPUL[$b->layanan] ?? 'angkatan';

            if (! is_file(public_path($folder . '/' . basename((string) $b->gambar)))) {
                $hilang[] = $b->gambar;
            }
        }

        return array_values(array_unique($hilang));
    }

    /**
     * Baris yang kena SALAH SATU dari ketiga keadaan, dihitung sekali saja.
     *
     * Bukan penjumlahan ketiganya: angkatan Bibliometrik yang drafnya
     * kadaluwarsa SEKALIGUS tidak menemukan tarif induk akan terhitung dua
     * kali, dan ubinnya menulis 47 dari 60 baris padahal yang benar jauh
     * lebih sedikit.
     */
    public function scopePerluApaPun(Builder $kueri): Builder
    {
        $bertarif = self::pasanganBertarif();
        $sampulHilang = self::jalurSampulHilang();

        return $kueri->where(function (Builder $q) use ($bertarif, $sampulHilang) {
            $q->where(fn (Builder $x) => $x->whereIn('status', ['draft', 'active'])
                ->whereRaw('coalesce(selesai, mulai) < ?', [Carbon::today()->toDateString()]))
                ->orWhereNotIn(
                    DB::raw("concat(layanan, '|', coalesce(varian, ''))"),
                    $bertarif
                )
                ->orWhereIn('gambar', $sampulHilang);
        });
    }

    /** Saringan "perlu ditindaklanjuti"; kunci yang tidak dikenal diabaikan. */
    public function scopePerlu(Builder $kueri, ?string $jenis): Builder
    {
        return match ($jenis) {
            'aktif-lewat' => $kueri->where('status', 'active')
                ->whereRaw('coalesce(selesai, mulai) < ?', [Carbon::today()->toDateString()]),

            'draf-lewat' => $kueri->where('status', 'draft')
                ->whereRaw('coalesce(selesai, mulai) < ?', [Carbon::today()->toDateString()]),

            'tanpa-tarif' => $kueri->whereNotIn(
                DB::raw("concat(layanan, '|', coalesce(varian, ''))"),
                self::pasanganBertarif()
            ),

            // Larik kosong pada whereIn menghasilkan "0 = 1" di SQL, yang
            // justru benar: tidak ada satu pun yang sampulnya hilang.
            'sampul-hilang' => $kueri->whereIn('gambar', self::jalurSampulHilang()),

            default => $kueri,
        };
    }

    /**
     * Nomor angkatan berikutnya untuk tiap pasangan layanan + lokasi.
     *
     * Nomornya berjalan PER LOKASI, bukan per layanan: Yogyakarta sudah sampai
     * 202 sementara Jakarta baru 9 dan Medan baru 3. Dihitung per layanan saja,
     * borang tambah menyodorkan 203 untuk angkatan Medan — nomor milik kota
     * lain, dan lompatannya baru ketahuan berbulan-bulan kemudian.
     *
     * Layanan yang angkatannya tidak berlokasi (Bibliometrik) masuk ke kunci
     * berlokasi kosong, jadi deretnya tetap berjalan seperti semula.
     *
     * @return array<string, int> Berkunci "layanan|lokasi", lokasi huruf kecil.
     */
    public static function nomorBerikutnyaPerLokasi(): array
    {
        $baris = DB::table('kategori_layanan')
            // Nomor yang bukan angka murni dilewati: dibaca sebagai bilangan,
            // "VIP" jadi 0 dan menyeret maksimumnya.
            ->whereRaw("nama_ke REGEXP '^[0-9]+$'")
            ->selectRaw("layanan, lower(trim(coalesce(lokasi, ''))) as lok, max(cast(nama_ke as unsigned)) as maks")
            ->groupBy('layanan', 'lok')
            ->get();

        $hasil = [];

        foreach ($baris as $b) {
            $hasil[$b->layanan . '|' . $b->lok] = ((int) $b->maks) + 1;
        }

        return $hasil;
    }

    /**
     * Sampul yang sudah jadi KEBIASAAN untuk pasangan layanan + lokasi.
     *
     * Empat puluh satu angkatan Scopus Camp Yogyakarta memakai satu flyer yang
     * sama, dan tiap angkatan baru di sana selalu memakainya lagi — tetapi
     * admin harus mencarinya sendiri di cakram dan mengunggah ulang berkas yang
     * sudah ada di peladen.
     *
     * Yang diusulkan HANYA kalau memang sudah jadi kebiasaan: berkasnya dipakai
     * sedikitnya dua angkatan DAN jadi mayoritas di lokasi itu. Jakarta punya
     * lima berkas berbeda dan tak satu pun dipakai lebih dari sekali — di sana
     * menebak satu di antaranya sama saja dengan menebak acak, dan flyer salah
     * yang terlanjur terbit di halaman publik lebih mahal daripada tidak ada
     * usulan sama sekali.
     *
     * @return array<string, array{jalur: string, alamat: string, jumlah: int}>
     *         Berkunci "layanan|lokasi" dengan lokasi huruf kecil.
     */
    public static function sampulLazim(): array
    {
        $baris = DB::table('kategori_layanan')
            ->whereNotNull('gambar')->where('gambar', '<>', '')
            ->whereNotNull('lokasi')->where('lokasi', '<>', '')
            ->selectRaw('layanan, lower(trim(lokasi)) as lok, gambar, count(*) as n')
            ->groupBy('layanan', 'lok', 'gambar')
            ->get();

        // Dikelompokkan di PHP, bukan lewat satu kueri berjenjang: MySQL tidak
        // punya cara ringkas memilih baris terbanyak per kelompok, dan jumlah
        // barisnya di sini puluhan, bukan ribuan.
        $per = [];

        foreach ($baris as $b) {
            $per[$b->layanan . '|' . $b->lok][$b->gambar] = (int) $b->n;
        }

        $hasil = [];

        foreach ($per as $kunci => $berkas) {
            arsort($berkas);

            $jalur = (string) array_key_first($berkas);
            $terbanyak = (int) reset($berkas);

            if ($terbanyak < 2 || $terbanyak <= array_sum($berkas) - $terbanyak) {
                continue;
            }

            // Berkas yang sudah hilang dari cakram tidak diusulkan: yang
            // tampil nanti gambar rusak, dan itu lebih membingungkan daripada
            // kotak kosong.
            if (! is_file(public_path($jalur))) {
                continue;
            }

            $hasil[$kunci] = [
                'jalur' => $jalur,
                'alamat' => asset($jalur),
                'jumlah' => $terbanyak,
            ];
        }

        return $hasil;
    }

    // ------------------------------------------------------------- tampilan

    public function getNamaLayananAttribute(): string
    {
        return Layanan::katalog()[$this->layanan]['nama']
            ?? Str::title(str_replace('_', ' ', (string) $this->layanan));
    }

    /**
     * Fasilitas angkatan ini — miliknya sendiri kalau ada, kalau tidak
     * mengikuti tarif induk.
     *
     * Angkatan menyimpan salinannya sendiri karena peserta yang sudah
     * mendaftar berhak atas fasilitas yang dijanjikan saat itu, bukan yang
     * berlaku sekarang.
     *
     * @return array<int, string>
     */
    public function getDaftarFasilitasAttribute(): array
    {
        return $this->tarif()?->daftar_fasilitas ?? [];
    }
}
