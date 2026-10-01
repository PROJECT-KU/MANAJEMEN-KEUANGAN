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

    /** @var array<string, int>|null */
    private static ?array $pendaftaran = null;

    /** @var array<int, string>|null */
    private static ?array $nomorGanda = null;

    /** Dibuang uji yang mengubah pendaftarnya di tengah jalan. */
    public static function lupakanPendaftar(): void
    {
        self::$pendaftar = null;
        self::$pendaftaran = null;
        self::$nomorGanda = null;
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
        'jam_mulai',
        'jam_selesai',
        'platform',
        'pemateri',
        'pemateri_jabatan',
        'pemateri_foto',
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

        /*
         * Jejak ditulis dari kait model, BUKAN dari pengendali. Angkatan
         * diubah dari enam tempat — borang, tindakan massal, tombol Gandakan,
         * perintah penutup otomatis, dan dua layar lama yang masih hidup —
         * jadi mencatat di pengendali berarti lima tempat yang akan terlewat.
         */
        static::created(fn ($m) => AngkatanJejak::catat($m, 'dibuat'));

        static::updated(function ($m) {
            $berubah = $m->ringkasPerubahan();

            // Penyimpanan yang tidak mengubah apa pun tidak dicatat; kalau
            // tidak, jejaknya penuh baris "diubah" tanpa isi.
            if ($berubah !== '') {
                AngkatanJejak::catat($m, 'diubah', $berubah);
            }
        });

        /*
         * Salinan utuh barisnya ikut disimpan supaya bisa dipulihkan. Dicatat
         * di `deleted`, bukan `deleting`: kalau penghapusannya gagal karena
         * kunci asing, tidak boleh ada jejak yang mengaku sudah terhapus.
         */
        static::deleted(fn ($m) => AngkatanJejak::catat(
            $m, 'dihapus', 'bisa dipulihkan dari sini', $m->getOriginal()
        ));
    }

    /**
     * Kalimat pendek berisi apa saja yang berubah pada penyimpanan terakhir.
     *
     * Hanya kolom yang BERARTI bagi orang. `updated_at` selalu berubah dan
     * menyebutkannya cuma membuat tiap jejak berbunyi sama.
     */
    public function ringkasPerubahan(): string
    {
        $nama = [
            'status' => 'status',
            'nama' => 'nama',
            'nama_ke' => 'nomor angkatan',
            'mulai' => 'tanggal mulai',
            'selesai' => 'tanggal selesai',
            'lokasi' => 'lokasi',
            'total_kuota' => 'total kuota',
            'sisa_kuota' => 'sisa kuota',
            'biaya' => 'biaya',
            'total_biaya' => 'harga promo',
            'gambar' => 'sampul',
            'desc' => 'deskripsi',
            'group_wa' => 'tautan grup',
            'varian' => 'varian',
        ];

        $bagian = [];

        foreach ($this->getChanges() as $kolom => $baru) {
            if (! array_key_exists($kolom, $nama)) {
                continue;
            }

            $lama = $this->getOriginal($kolom);

            // Deskripsi bisa ribuan huruf; yang berguna cuma "berubah".
            if (in_array($kolom, ['desc', 'gambar'], true)) {
                $bagian[] = $nama[$kolom] . ' diganti';

                continue;
            }

            $bagian[] = $nama[$kolom] . ': ' . ($lama === null || $lama === '' ? '(kosong)' : $lama)
                . ' → ' . ($baru === null || $baru === '' ? '(kosong)' : $baru);
        }

        return implode(', ', $bagian);
    }

    /** Jejak perubahan angkatan ini, terbaru dulu. */
    public function jejak()
    {
        return $this->hasMany(AngkatanJejak::class, 'kategori_id')->latest('created_at');
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
     * Tabel pendaftaran per layanan: kunci layanan -> nama tabelnya.
     *
     * Didaftar di satu tempat karena dibaca dari empat tempat — penghitung
     * peserta, penjaga hapus satuan, penjaga hapus massal, dan penjaga kuota
     * di borang. Sebelumnya nama tabelnya diketik ulang di masing-masing, dan
     * layanan yang belum punya tabel pendaftaran tidak kelihatan dari mana
     * pun.
     *
     * Layanan yang TIDAK ada di sini — Scopus Cafe dan Clinik Scopus — memang
     * belum punya tabel pendaftaran. Angkatannya boleh dibuat, tetapi peserta
     * dan sisa kuotanya akan selalu 0; itu dinyatakan terang-terangan lewat
     * belumPunyaPendaftaran() daripada dibiarkan terbaca seperti "memang
     * belum ada yang daftar".
     */
    public const TABEL_PENDAFTARAN = [
        'scopus_camp' => 'scopus_camp_pendaftaran',
        'bibliometrik' => 'analisis_bibliometrik',
        'sharing_session' => 'sharing_session_pendaftaran',
    ];

    /** Layanan ini belum punya tempat menyimpan pendaftar sama sekali. */
    public function belumPunyaPendaftaran(): bool
    {
        return ! array_key_exists((string) $this->layanan, self::TABEL_PENDAFTARAN);
    }

    /**
     * Jumlah PESERTA semua angkatan sekaligus, dalam dua kueri berkelompok.
     *
     * Bukan satu kueri per baris: daftarnya berhalaman sepuluh dan jumlahnya
     * dipakai di tiga tempat (lencana kuota, penjaga kuota, tautan peserta),
     * jadi dibaca satu per satu ia jadi tiga puluh kueri.
     *
     * Yang dijumlahkan `jumlah_pendaftar`, BUKAN jumlah barisnya. Satu baris
     * pendaftaran boleh berisi rombongan: dari 80 baris Scopus Camp isinya
     * 105 orang, dan angkatan ke-175 yang barisnya 5 sebenarnya 25 orang.
     * Menghitung baris membuat layar ini menulis 5 sementara halaman publik —
     * yang sejak dulu memakai sum() — menulis 25 untuk angkatan yang sama,
     * dan membuat penjaga kuota mengizinkan kuota disetel di bawah jumlah
     * orang yang sudah terdaftar.
     *
     * Baris tanpa isian dihitung satu orang, bukan nol: yang mendaftar tetap
     * ada walau jumlahnya tidak terisi.
     *
     * @return array<string, int>
     */
    public static function hitungPendaftar(): array
    {
        if (self::$pendaftar !== null) {
            return self::$pendaftar;
        }

        $hasil = [];

        foreach (self::TABEL_PENDAFTARAN as $tabel) {
            $baris = DB::table($tabel)
                ->whereNotNull('kategori_id')
                ->selectRaw('kategori_id, sum(greatest(coalesce(jumlah_pendaftar, 1), 1)) as n')
                ->groupBy('kategori_id')
                ->pluck('n', 'kategori_id');

            foreach ($baris as $id => $n) {
                $hasil[$id] = ($hasil[$id] ?? 0) + (int) $n;
            }
        }

        return self::$pendaftar = $hasil;
    }

    /**
     * Jumlah BARIS pendaftaran per angkatan — bukan jumlah orangnya.
     *
     * Dipakai hanya untuk menyebut "3 pendaftaran" di samping jumlah orang
     * saat keduanya berbeda, supaya admin yang membuka daftar pendaftar tidak
     * bingung menemukan tiga baris padahal layar ini menulis tujuh orang.
     *
     * @return array<string, int>
     */
    public static function hitungPendaftaran(): array
    {
        if (self::$pendaftaran !== null) {
            return self::$pendaftaran;
        }

        $hasil = [];

        foreach (self::TABEL_PENDAFTARAN as $tabel) {
            $baris = DB::table($tabel)
                ->whereNotNull('kategori_id')
                ->selectRaw('kategori_id, count(*) as n')
                ->groupBy('kategori_id')
                ->pluck('n', 'kategori_id');

            foreach ($baris as $id => $n) {
                $hasil[$id] = ($hasil[$id] ?? 0) + (int) $n;
            }
        }

        return self::$pendaftaran = $hasil;
    }

    /** Berapa ORANG yang sudah mendaftar di angkatan ini. */
    public function getJumlahPendaftarAttribute(): int
    {
        return self::hitungPendaftar()[$this->getKey()] ?? 0;
    }

    /** Berapa BARIS pendaftaran yang masuk ke angkatan ini. */
    public function getJumlahPendaftaranAttribute(): int
    {
        return self::hitungPendaftaran()[$this->getKey()] ?? 0;
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
        'sharing_session' => 'SharingSession',
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
        'kuota-melenceng' => 'Sisa kuota melenceng',
        'nomor-ganda' => 'Nomor angkatan ganda',
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

        if ($this->kuota_melenceng) {
            $alasan[] = 'sisa kuotanya ' . (int) $this->sisa_kuota . ', seharusnya '
                . $this->sisa_kuota_seharusnya;
        }

        if ($this->nomor_ganda) {
            $alasan[] = 'nomor angkatannya dipakai angkatan lain di lokasi yang sama';
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
     * Sisa kuota yang SEHARUSNYA, dihitung dari total dikurangi peserta.
     *
     * null kalau tidak bisa dihitung: tanpa total kuota tidak ada yang bisa
     * dikurangi, dan layanan yang belum punya tabel pendaftaran sisanya
     * memang diisi tangan.
     */
    public function getSisaKuotaSeharusnyaAttribute(): ?int
    {
        if ($this->total_kuota === null || $this->belumPunyaPendaftaran()) {
            return null;
        }

        return max(0, (int) $this->total_kuota - $this->jumlah_pendaftar);
    }

    /**
     * Sisa kuota tersimpan tidak cocok dengan total dikurangi peserta.
     *
     * `sisa_kuota` bukan hitungan, melainkan kolom yang dinaik-turunkan tangan
     * di empat pengendali berbeda — dua layar pendaftaran admin, dan dua
     * halaman publik. Begitu salah satunya gagal di tengah jalan, angkanya
     * melenceng dan tidak ada apa pun yang memberi tahu: terukur 2 dari 60
     * angkatan, yang terburuk menulis sisa 11 padahal seharusnya 31.
     *
     * Yang dibaca halaman publik adalah kolom ini, jadi melencengnya berarti
     * angkatan menerima lebih banyak atau lebih sedikit orang daripada yang
     * disediakan.
     */
    public function getKuotaMelencengAttribute(): bool
    {
        $seharusnya = $this->sisa_kuota_seharusnya;

        return $seharusnya !== null && (int) $this->sisa_kuota !== $seharusnya;
    }

    /**
     * Id angkatan yang sisa kuotanya melenceng.
     *
     * Dihitung sekali untuk semua baris, lalu dipakai scopePerlu() menyaring
     * di SQL. Tanpa daftar ini, menyaring "sisa kuota melenceng" berarti
     * memuat seluruh tabel lalu menyaring di PHP, dan penomoran halamannya
     * ikut rusak.
     *
     * @return array<int, string>
     */
    public static function idKuotaMelenceng(): array
    {
        $peserta = self::hitungPendaftar();
        $id = [];

        self::query()
            ->whereNotNull('total_kuota')
            ->whereIn('layanan', array_keys(self::TABEL_PENDAFTARAN))
            ->select('id', 'total_kuota', 'sisa_kuota')
            ->get()
            ->each(function ($a) use ($peserta, &$id) {
                $seharusnya = max(0, (int) $a->total_kuota - ($peserta[$a->id] ?? 0));

                if ((int) $a->sisa_kuota !== $seharusnya) {
                    $id[] = $a->id;
                }
            });

        return $id;
    }

    /**
     * Nomor angkatannya sudah dipakai angkatan lain di lokasi yang sama.
     *
     * Nomor itu yang dipakai orang menyebut angkatannya ("Camp ke-188"), jadi
     * dua angkatan bernomor sama membuat percakapan admin dan peserta jadi
     * ambigu — dan keduanya tetap tampil berdampingan di halaman publik.
     * Terukur 7 pasang dari 60 angkatan.
     */
    public function getNomorGandaAttribute(): bool
    {
        return in_array($this->getKey(), self::idNomorGanda(), true);
    }

    /**
     * Id semua angkatan yang nomornya kembar dengan angkatan lain.
     *
     * Dihitung sekali lewat satu kueri berkelompok, bukan sekali per baris.
     *
     * @return array<int, string>
     */
    public static function idNomorGanda(): array
    {
        if (self::$nomorGanda !== null) {
            return self::$nomorGanda;
        }

        /*
         * Yang dibandingkan layanan + lokasi + nomor. Nomor berjalan PER
         * LOKASI, jadi Camp Jakarta ke-9 dan Camp Medan ke-9 bukan kembar.
         * Lokasi kosong disamakan jadi untaian kosong supaya Bibliometrik —
         * yang lokasinya memang selalu NULL — tetap terbandingkan.
         */
        $kembar = self::query()
            ->whereNotNull('nama_ke')->where('nama_ke', '!=', '')
            ->selectRaw("layanan, coalesce(lokasi, '') as lok, nama_ke")
            ->groupBy('layanan', 'lok', 'nama_ke')
            ->havingRaw('count(*) > 1')
            ->get();

        if ($kembar->isEmpty()) {
            return self::$nomorGanda = [];
        }

        $kueri = self::query()->select('id');

        $kueri->where(function (Builder $q) use ($kembar) {
            foreach ($kembar as $k) {
                $q->orWhere(fn (Builder $x) => $x
                    ->where('layanan', $k->layanan)
                    ->whereRaw("coalesce(lokasi, '') = ?", [$k->lok])
                    ->where('nama_ke', $k->nama_ke));
            }
        });

        return self::$nomorGanda = $kueri->pluck('id')->all();
    }

    /**
     * Nomor angkatan BEBAS berikutnya untuk layanan + lokasi ini.
     *
     * Dipakai tombol Gandakan. Menaikkan nomor asal satu saja tidak cukup:
     * menggandakan ke-187 menghasilkan 188, dan 188 sudah ada dua di
     * Yogyakarta — jadi menggandakan justru menambah kembar ketiga.
     */
    public static function nomorBebas(string $layanan, ?string $lokasi, string $dari): string
    {
        if (! is_numeric($dari)) {
            return $dari;
        }

        $terpakai = self::query()
            ->where('layanan', $layanan)
            ->whereRaw("coalesce(lokasi, '') = ?", [(string) $lokasi])
            ->pluck('nama_ke')
            ->map(fn ($n) => (string) $n)
            ->all();

        $nomor = (int) $dari;

        // Dibatasi supaya tidak berputar selamanya kalau datanya aneh; 500
        // sudah jauh di atas angkatan tertinggi yang ada (202).
        for ($i = 0; $i < 500; $i++) {
            $nomor++;

            if (! in_array((string) $nomor, $terpakai, true)) {
                return (string) $nomor;
            }
        }

        return (string) $nomor;
    }

    /**
     * Layanan ini acara DARING, jadi punya jam, platform, dan pemateri.
     *
     * Ditentukan dari ada-tidaknya isian itu, bukan dari daftar kode layanan:
     * layanan baru yang juga daring tidak boleh perlu menyentuh kode ini,
     * dan admin yang mengisi jamnya jelas bermaksud begitu.
     */
    public function acaraDaring(): bool
    {
        return $this->jam_mulai !== null || trim((string) $this->platform) !== '';
    }

    /**
     * Jam acara dalam bentuk yang dibaca orang: "09.30 - 11.30 WIB".
     *
     * Titik, bukan titik dua: itu kebiasaan penulisan jam di seluruh materi
     * Rumah Scopus, dan halaman ini dibaca orang yang sama.
     */
    public function getJamAttribute(): ?string
    {
        if ($this->jam_mulai === null) {
            return null;
        }

        $tulis = fn ($j) => $j === null ? null : Carbon::parse($j)->format('H.i');

        $akhir = $tulis($this->jam_selesai);

        return $tulis($this->jam_mulai) . ($akhir ? ' - ' . $akhir : '') . ' WIB';
    }

    /**
     * Alamat foto pemateri; null kalau berkasnya tidak ada.
     *
     * Diperiksa keberadaannya seperti sampul, dengan alasan yang sama: kolom
     * yang terisi tetapi berkasnya hilang membuat halaman publik menampilkan
     * gambar rusak, dan tidak ada yang tahu sampai ada yang melapor.
     */
    public function getAlamatPemateriAttribute(): ?string
    {
        if (! $this->pemateri_foto) {
            return null;
        }

        $berkas = public_path($this->folderSampul() . '/' . basename($this->pemateri_foto));

        return is_file($berkas) ? asset($this->folderSampul() . '/' . basename($this->pemateri_foto)) : null;
    }

    /**
     * Sebutan jumlah peserta untuk ditampilkan.
     *
     * Jumlah BARISnya ikut disebut hanya kalau berbeda dari jumlah orang —
     * angkatan ke-175 punya 5 pendaftaran berisi 25 orang, dan menulis "25
     * peserta" saja membuat admin yang membuka daftarnya mengira ada yang
     * hilang.
     */
    public function sebutPeserta(): string
    {
        $orang = $this->jumlah_pendaftar;
        $baris = $this->jumlah_pendaftaran;

        return $orang . ' peserta' . ($baris > 0 && $baris !== $orang ? ' / ' . $baris . ' pendaftaran' : '');
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
                ->orWhereIn('gambar', $sampulHilang)
                ->orWhereIn('id', self::idKuotaMelenceng())
                ->orWhereIn('id', self::idNomorGanda());
        });
    }

    /**
     * Rentang waktu siap pakai; kuncinya dipakai di alamat.
     *
     * Pilihan jadi, bukan dua kotak tanggal. Yang memakai layar ini bukan
     * orang yang terbiasa dengan pemilih tanggal, dan pertanyaan yang benar-
     * benar ditanyakan selalu bentuknya "yang mana bulan depan" — bukan
     * "antara 3 dan 19 November".
     */
    public const PERIODE = [
        'bulan-ini' => 'Bulan ini',
        'bulan-depan' => 'Bulan depan',
        'tiga-bulan' => '3 bulan ke depan',
        'tahun-ini' => 'Tahun ini',
        'sudah-lewat' => 'Sudah lewat',
    ];

    /**
     * Saringan rentang waktu; kunci yang tidak dikenal diabaikan.
     *
     * Yang dibandingkan tanggal MULAI, kecuali "sudah lewat" yang memakai
     * tanggal selesai — angkatan tiga hari yang mulai kemarin belum lewat.
     */
    public function scopePeriode(Builder $kueri, ?string $jenis): Builder
    {
        $hariIni = Carbon::today();

        return match ($jenis) {
            'bulan-ini' => $kueri->whereBetween('mulai', [
                $hariIni->copy()->startOfMonth(), $hariIni->copy()->endOfMonth(),
            ]),

            'bulan-depan' => $kueri->whereBetween('mulai', [
                $hariIni->copy()->addMonthNoOverflow()->startOfMonth(),
                $hariIni->copy()->addMonthNoOverflow()->endOfMonth(),
            ]),

            'tiga-bulan' => $kueri->whereBetween('mulai', [
                $hariIni->copy()->startOfDay(),
                $hariIni->copy()->addMonthsNoOverflow(3)->endOfDay(),
            ]),

            'tahun-ini' => $kueri->whereBetween('mulai', [
                $hariIni->copy()->startOfYear(), $hariIni->copy()->endOfYear(),
            ]),

            'sudah-lewat' => $kueri->whereRaw('coalesce(selesai, mulai) < ?', [$hariIni->toDateString()]),

            default => $kueri,
        };
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

            'kuota-melenceng' => $kueri->whereIn('id', self::idKuotaMelenceng()),

            'nomor-ganda' => $kueri->whereIn('id', self::idNomorGanda()),

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
