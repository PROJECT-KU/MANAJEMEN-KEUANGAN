<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu layanan jasa yang dijual Rumah Scopus.
 *
 * Katalognya dulu konstanta di model tarif, jadi menambah layanan berarti
 * mengubah kode lalu deploy. Sekarang datanya, dan admin menambahnya sendiri.
 *
 * `kode` yang jadi kuncinya bagi tabel lain — `clinikscopus_biaya_persesi` dan
 * `kategori_layanan` menyimpan nilai itu di kolom `layanan`. Karena itu kode
 * dibuat sekali dari namanya lalu DIKUNCI: menggantinya akan memutus tarif dan
 * seluruh angkatan yang menunjuknya, tanpa galat apa pun.
 */
class Layanan extends Model
{
    protected $table = 'layanan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['kode', 'nama', 'satuan', 'ikon', 'warna', 'varian', 'urutan', 'aktif'];

    protected $casts = [
        'varian' => 'array',
        'aktif' => 'boolean',
    ];

    /**
     * Ikon yang boleh dipilih.
     *
     * Daftar tertutup, bukan isian bebas: proyek ini memakai Font Awesome 5,
     * dan nama FA6 tidak merender apa pun TANPA galat sama sekali. Uji
     * IkonAdaGlifnyaTest memindai berkas tampilan, tetapi nama yang datang dari
     * basis data lolos dari pemindaian itu — jadi penjaganya harus di sini.
     */
    public const IKON = [
        'fa-user-md' => 'Dokter / konsultasi',
        'fa-chart-line' => 'Grafik naik',
        'fa-campground' => 'Tenda / camp',
        'fa-coffee' => 'Kopi',
        'fa-chalkboard-teacher' => 'Mengajar',
        'fa-users' => 'Kelompok orang',
        'fa-comments' => 'Diskusi',
        'fa-microphone' => 'Mikrofon',
        'fa-video' => 'Video',
        'fa-laptop' => 'Laptop',
        'fa-book' => 'Buku',
        'fa-graduation-cap' => 'Wisuda',
        'fa-lightbulb' => 'Ide',
        'fa-file-alt' => 'Naskah',
        'fa-pen-fancy' => 'Menulis',
        'fa-search' => 'Pencarian',
        'fa-award' => 'Penghargaan',
        'fa-handshake' => 'Kerja sama',
        'fa-calendar-alt' => 'Jadwal',
        'fa-globe' => 'Global',
        'fa-flask' => 'Riset',
        'fa-briefcase' => 'Profesional',
        'fa-star' => 'Eksklusif',
        'fa-tag' => 'Umum',
    ];

    /** Warna ubin ikon; enam gradien yang sama dipakai seluruh MIS. */
    public const WARNA = [
        'mis-ungu' => 'Ungu',
        'mis-biru' => 'Biru',
        'mis-hijau' => 'Hijau',
        'mis-jingga' => 'Jingga',
        'mis-kuning' => 'Kuning',
        'mis-merah' => 'Merah',
    ];

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $katalog = null;

    /** @var array<string, array<string, int>>|null */
    private static ?array $pemakaian = null;

    /** @var \Illuminate\Support\Collection<string, self>|null */
    private static $modelAktif = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });

        // Katalog yang disimpan di ingatan harus dibuang begitu isinya berubah,
        // kalau tidak layar yang sama masih memakai daftar yang lama.
        static::saved(fn () => self::lupakanKatalog());
        static::deleted(fn () => self::lupakanKatalog());
    }

    /**
     * Katalog layanan aktif, berbentuk sama persis dengan konstanta lamanya.
     *
     * Disimpan di ingatan selama satu permintaan, bukan di cache: katalognya
     * dibaca beberapa kali per halaman, tetapi cache yang bertahan antar
     * permintaan berarti layanan yang baru ditambah bisa belum muncul, dan
     * itu jenis kebingungan yang mahal.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function katalog(): array
    {
        if (self::$katalog !== null) {
            return self::$katalog;
        }

        return self::$katalog = self::aktifBerurutan()
            ->mapWithKeys(fn (self $l) => [$l->kode => [
                'nama' => $l->nama,
                'satuan' => $l->satuan,
                'ikon' => $l->ikon,
                'warna' => $l->warna,
                'varian' => $l->varian_peta,
            ]])
            ->all();
    }

    /** Membuang semua yang disimpan di ingatan; dipakai uji dan saat menyimpan. */
    public static function lupakanKatalog(): void
    {
        self::$katalog = null;
        self::$pemakaian = null;
        self::$modelAktif = null;
    }

    /**
     * Model layanan aktif, terkunci per permintaan.
     *
     * Layar tarif butuh modelnya (untuk id dan varian aslinya) di samping
     * katalognya, dan mengambil keduanya terpisah berarti tabel yang sama
     * dibaca dua kali dalam satu halaman.
     *
     * @return \Illuminate\Support\Collection<string, self>
     */
    public static function aktifBerurutan()
    {
        if (self::$modelAktif !== null) {
            return self::$modelAktif;
        }

        return self::$modelAktif = static::query()
            ->where('aktif', true)->orderBy('urutan')->orderBy('nama')
            ->get()->keyBy('kode');
    }

    /**
     * Varian sebagai peta kode => nama, DALAM URUTAN yang diketik admin.
     *
     * Kolomnya menyimpan larik berurut, bukan objek, karena MySQL mengurutkan
     * ulang kunci objek JSON-nya sendiri — terukur: {zulu, alfa, bravo_panjang}
     * terbaca kembali sebagai {alfa, zulu, bravo_panjang}, yaitu menurut
     * panjang lalu abjad. Larik dipertahankan apa adanya.
     *
     * Bentuk objek yang lama tetap dikenali supaya data yang sudah ada tidak
     * perlu ikut bermigrasi saat aturannya berubah.
     *
     * @return array<string, string>
     */
    public function getVarianPetaAttribute(): array
    {
        $isi = $this->varian ?? [];

        if ($isi === []) {
            return [];
        }

        // Larik berurut: tiap butir {kode, nama}.
        if (array_is_list($isi)) {
            $peta = [];

            foreach ($isi as $butir) {
                if (is_array($butir) && isset($butir['kode'], $butir['nama'])) {
                    $peta[$butir['kode']] = $butir['nama'];
                }
            }

            return $peta;
        }

        return $isi;
    }

    /**
     * Menyimpan varian dari peta kode => nama, jadi larik berurut.
     *
     * @param  array<string, string>|null  $peta
     */
    public function setVarianDari(?array $peta): void
    {
        if (! $peta) {
            $this->varian = null;

            return;
        }

        $this->varian = array_values(array_map(
            fn ($kode, $nama) => ['kode' => $kode, 'nama' => $nama],
            array_keys($peta),
            $peta
        ));
    }

    /**
     * Membuat kode dari nama, dijamin belum terpakai.
     *
     * Dibuat sekali saat layanan dibuat, lalu tidak pernah berubah lagi.
     */
    public static function kodeDari(string $nama): string
    {
        $dasar = Str::slug($nama, '_') ?: 'layanan';
        $dasar = Str::limit($dasar, 34, '');

        $kode = $dasar;
        $n = 2;

        while (static::where('kode', $kode)->exists()) {
            $kode = $dasar . '_' . $n++;
        }

        return $kode;
    }

    // --------------------------------------------------------------- pemakai

    /**
     * Jumlah pemakaian SEMUA layanan sekaligus.
     *
     * Dua kueri berkelompok, bukan dua kueri per layanan. Dibaca satu per satu,
     * layar dengan lima layanan menjalankan sepuluh `count(*)` — dan jumlahnya
     * tumbuh seiring katalognya bertambah.
     *
     * @return array{tarif: array<string,int>, angkatan: array<string,int>}
     */
    public static function hitungPemakaian(): array
    {
        if (self::$pemakaian !== null) {
            return self::$pemakaian;
        }

        return self::$pemakaian = [
            'tarif' => DB::table('clinikscopus_biaya_persesi')
                ->selectRaw('layanan, count(*) as n')->groupBy('layanan')->pluck('n', 'layanan')->all(),
            'angkatan' => DB::table('kategori_layanan')
                ->selectRaw('layanan, count(*) as n')->groupBy('layanan')->pluck('n', 'layanan')->all(),
        ];
    }

    /** Berapa baris tarif yang memakai layanan ini, termasuk riwayatnya. */
    public function getJumlahTarifAttribute(): int
    {
        return self::hitungPemakaian()['tarif'][$this->kode] ?? 0;
    }

    /** Berapa angkatan yang memakai layanan ini. */
    public function getJumlahAngkatanAttribute(): int
    {
        return self::hitungPemakaian()['angkatan'][$this->kode] ?? 0;
    }

    /**
     * Varian yang sudah terpakai, jadi tidak boleh dibuang.
     *
     * @return array<int, string>
     */
    public function varianTerpakai(): array
    {
        $dariTarif = DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', $this->kode)->whereNotNull('varian')->distinct()->pluck('varian');

        $dariAngkatan = DB::table('kategori_layanan')
            ->where('layanan', $this->kode)->whereNotNull('varian')->distinct()->pluck('varian');

        return $dariTarif->merge($dariAngkatan)->unique()->values()->all();
    }
}
