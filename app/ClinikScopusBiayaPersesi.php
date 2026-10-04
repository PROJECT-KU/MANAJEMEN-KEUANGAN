<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tarif layanan jasa Rumah Scopus, beserta PPN dan fasilitas yang didapat.
 *
 * Namanya tinggalan: tabelnya dulu hanya menyimpan tarif per sesi Clinik
 * Scopus. Sekarang ia menaungi LIMA layanan, tetapi nama tabel dan kelasnya
 * sengaja tidak diganti — clinikscopus.biaya_persesi_id berkunci asing ke
 * sini dan sepuluh sesi sudah menunjuk barisnya.
 *
 * Isinya RIWAYAT tarif, bukan daftar pilihan: satu tarif berlaku untuk satu
 * pasangan layanan+varian pada satu waktu, dan baris lama dipertahankan
 * karena pesanan yang sudah terjadi memakai harga saat itu.
 *
 * Varian membedakan harga atau fasilitas di dalam satu layanan — Bibliometrik
 * online/offline, Scopus Camp Jawa/luar Jawa. Dipakai untuk beda PRODUK, bukan
 * beda angkatan. Daftarnya ikut katalog di tabel `layanan`.
 *
 * Layanan tanpa varian menyimpan NULL, bukan untaian kosong: keduanya berbeda
 * di whereNull, dan kolom kosong yang tercampur membuat pencarian tarifnya
 * kadang ketemu kadang tidak.
 */
class ClinikScopusBiayaPersesi extends Model
{
    protected $table = 'clinikscopus_biaya_persesi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'layanan',
        'varian',
        'biaya_persesi',
        'ppn',
        'diskon_alumni_persen',
        'diskon_rombongan_min',
        'diskon_rombongan_persen',
        'fasilitas',
        'template_deskripsi',
        'kegiatan',
        'sesi',
        'kontak',
        'status',
        'penginput_id',
        'berlaku_mulai',
    ];

    protected $casts = [
        'fasilitas' => 'array',
        'kegiatan' => 'array',
        'sesi' => 'array',
        'berlaku_mulai' => 'date',
    ];

    public const AKTIF = 'active';

    public const NONAKTIF = 'non active';

    /** Sudah disetel, menunggu tanggalnya. Naik sendiri saat harinya tiba. */
    public const TERJADWAL = 'terjadwal';

    /** Penanda bahwa jadwal sudah diperiksa sekali dalam permintaan ini. */
    private static bool $sudahDiperiksa = false;

    /**
     * Katalog layanan — sekarang datanya, bukan konstanta.
     *
     * Dulu daftar ini ditulis di sini sebagai const LAYANAN, jadi menambah
     * satu layanan berarti mengubah kode lalu deploy. Sekarang ia tabel
     * `layanan` yang diisi admin sendiri; bentuk larik yang dikembalikan sama
     * persis dengan konstanta lamanya.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function layanan(): array
    {
        return Layanan::katalog();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }

            if (! $model->layanan) {
                $model->layanan = 'clinik_scopus';
            }

            // Siapa yang menyetel dicatat otomatis, bukan diminta dari borang:
            // isian yang bisa dilewati bukan jejak.
            if (! $model->penginput_id && auth()->check()) {
                $model->penginput_id = auth()->id();
            }
        });

        /*
         * Daftar sesi yang disimpan di ingatan harus dibuang begitu tarifnya
         * berubah. Tanpa ini, borang pendaftaran yang dibuka di permintaan
         * yang sama dengan penyimpanan tarif masih menawarkan jam yang lama —
         * dan jadwal yang baru saja diubah terlihat tidak tersimpan.
         *
         * Juga pada `deleted` dan `updated`: jadikanBerlaku() memindahkan
         * status baris lain, dan daftar sesi dibaca dari yang berstatus aktif.
         */
        static::saved(fn () => \App\Support\PendaftaranSemuaLayanan::lupakanSesi());
        static::deleted(fn () => \App\Support\PendaftaranSemuaLayanan::lupakanSesi());
    }

    public function clinikScopus()
    {
        return $this->hasMany(\App\ClinikScopus::class, 'biaya_persesi_id');
    }

    // ----------------------------------------------------------------- kueri

    public function scopeAktif($kueri)
    {
        return $kueri->where('status', self::AKTIF);
    }

    /** Menyaring satu pasangan layanan+varian, termasuk varian kosong. */
    public function scopeUntuk($kueri, string $layanan, ?string $varian = null)
    {
        return $kueri->where('layanan', $layanan)
            ->where(fn ($q) => $varian === null || $varian === ''
                ? $q->whereNull('varian')
                : $q->where('varian', $varian));
    }

    /**
     * Tarif yang sedang berlaku — SATU-SATUNYA pintu untuk mengambilnya.
     *
     * Terbaru didahulukan sebagai jaring pengaman: kalau entah bagaimana ada
     * dua yang aktif untuk pasangan yang sama, yang dipakai adalah yang paling
     * belakangan disetel, bukan sembarang seperti first() tanpa urutan.
     */
    public static function berlaku(string $layanan = 'clinik_scopus', ?string $varian = null): ?self
    {
        self::naikkanYangSudahWaktunya();

        return static::query()
            ->untuk($layanan, $varian)
            ->aktif()
            ->latest('updated_at')
            ->first();
    }

    /**
     * Menaikkan tarif terjadwal yang tanggalnya sudah tiba.
     *
     * Dikerjakan saat tarifnya DIBACA, bukan oleh penjadwal. Alasannya bukan
     * kemalasan: penjadwal yang tidak jalan membuat harga tertinggal tanpa ada
     * yang tahu, dan itu justru jenis kegagalan yang paling mahal di sini.
     * Dibaca, ia tidak mungkin terlewat — yang membaca tarifnya pasti
     * mendapat harga yang benar.
     *
     * Dibungkus transaksi dan diperiksa ulang di dalamnya supaya dua
     * permintaan yang datang bersamaan tidak menaikkan dua kali.
     */
    private static function naikkanYangSudahWaktunya(): void
    {
        /*
         * Diperiksa SEKALI per permintaan, untuk semua layanan sekaligus.
         *
         * Semula pemeriksaannya per pasangan layanan+varian, jadi satu halaman
         * dengan tujuh kartu menjalankan tujuh kueri `exists` yang hampir
         * selalu menjawab "tidak ada" — dan jumlahnya tumbuh seiring layanan
         * bertambah, tepat karena katalognya dibuat supaya tumbuh.
         */
        if (self::$sudahDiperiksa) {
            return;
        }

        self::$sudahDiperiksa = true;

        $adaJatuhTempo = static::query()
            ->where('status', self::TERJADWAL)
            ->whereDate('berlaku_mulai', '<=', now())
            ->exists();

        if (! $adaJatuhTempo) {
            return;
        }

        DB::transaction(function () {
            $jatuhTempo = static::query()
                ->where('status', self::TERJADWAL)
                ->whereDate('berlaku_mulai', '<=', now())
                ->orderBy('berlaku_mulai')
                ->lockForUpdate()
                ->get();

            // Kalau ada beberapa yang terlewat sekaligus, yang paling akhir
            // tanggalnya yang menang; sisanya turun jadi riwayat. jadikanBerlaku
            // sendiri membatasi diri pada pasangan layanan+varian barisnya.
            foreach ($jatuhTempo as $t) {
                $t->jadikanBerlaku();
            }
        });
    }

    /** Dibuang uji yang perlu memeriksa ulang dalam satu permintaan. */
    public static function lupakanPemeriksaanJadwal(): void
    {
        self::$sudahDiperiksa = false;
    }

    /**
     * Semua tarif yang berlaku, dalam SATU kueri.
     *
     * berlaku() dipanggil sekali per kartu, dan layar tarif maupun borang
     * angkatan menyusun satu kartu per pasangan layanan+varian — jadi tujuh
     * kueri yang bentuknya sama persis, tumbuh seiring katalognya bertambah.
     *
     * Kuncinya "layanan|varian", dengan varian kosong untuk yang tidak
     * bervarian. Yang paling belakangan disetel menang, sama seperti berlaku().
     *
     * @return \Illuminate\Support\Collection<string, self>
     */
    public static function semuaYangBerlaku()
    {
        self::naikkanYangSudahWaktunya();

        return static::query()
            ->aktif()
            ->orderBy('updated_at')
            ->get()
            ->keyBy(fn (self $t) => $t->layanan . '|' . ($t->varian ?: ''));
    }

    // ------------------------------------------------------------- perubahan

    /**
     * Menjadikan tarif ini satu-satunya yang berlaku UNTUK PASANGANNYA.
     *
     * Batasnya layanan+varian, bukan seluruh tabel: menyetel tarif Scopus Camp
     * luar Jawa tidak boleh mematikan tarif Clinik Scopus.
     *
     * Dibungkus transaksi — kalau penonaktifan yang lain berhasil tetapi
     * pengaktifan baris ini gagal, layanan itu kehilangan tarif berlaku sama
     * sekali, dan borang pemesanannya menampilkan harga kosong.
     */
    public function jadikanBerlaku(): void
    {
        DB::transaction(function () {
            static::query()
                ->untuk($this->layanan, $this->varian)
                ->where('id', '!=', $this->getKey())
                ->where('status', self::AKTIF)
                ->update(['status' => self::NONAKTIF]);

            $this->forceFill(['status' => self::AKTIF])->save();
        });
    }

    /** Penyetel tarif ini, kalau akunnya masih ada. */
    public function penginput()
    {
        return $this->belongsTo(User::class, 'penginput_id');
    }

    // -------------------------------------------------------------- tampilan

    public function getTarifTerbacaAttribute(): string
    {
        return 'Rp ' . number_format((int) $this->biaya_persesi, 0, ',', '.');
    }

    /**
     * PPN sebagai bilangan bulat persen.
     *
     * Kolomnya varchar dan boleh kosong — NULL berarti "tanpa PPN", bukan nol
     * yang sengaja disetel.
     */
    public function getPpnPersenAttribute(): int
    {
        return (int) $this->ppn;
    }

    public function getBerlakuAttribute(): bool
    {
        return $this->status === self::AKTIF;
    }

    /** Berapa sesi Clinik Scopus yang harganya mengacu ke baris ini. */
    public function getDipakaiSesiAttribute(): int
    {
        return $this->clinikScopus()->count();
    }

    /**
     * Apakah pemakaian tarif ini bisa dihitung sama sekali.
     *
     * Hanya sesi Clinik Scopus yang menyimpan `biaya_persesi_id`. Angkatan
     * layanan lain menyalin angkanya, tidak menunjuk barisnya — jadi untuk
     * mereka jumlahnya BUKAN nol, melainkan tidak diketahui. Menuliskannya
     * "Belum dipakai" adalah angka yang berbohong.
     */
    public function getPemakaianTerhitungAttribute(): bool
    {
        return $this->layanan === 'clinik_scopus';
    }

    public function getTerjadwalAttribute(): bool
    {
        return $this->status === self::TERJADWAL;
    }

    public function getNamaLayananAttribute(): string
    {
        return self::layanan()[$this->layanan]['nama'] ?? Str::title(str_replace('_', ' ', (string) $this->layanan));
    }

    public function getNamaVarianAttribute(): ?string
    {
        if (! $this->varian) {
            return null;
        }

        return self::layanan()[$this->layanan]['varian'][$this->varian]
            ?? Str::title(str_replace('_', ' ', $this->varian));
    }

    public function getSatuanAttribute(): string
    {
        return self::layanan()[$this->layanan]['satuan'] ?? 'per satuan';
    }

    /** Daftar fasilitas, selalu berupa larik walau kolomnya kosong. */
    public function getDaftarFasilitasAttribute(): array
    {
        return self::bersihkanDaftar($this->fasilitas);
    }

    /** Daftar kegiatan utama; diperlakukan sama persis seperti fasilitas. */
    public function getDaftarKegiatanAttribute(): array
    {
        return self::bersihkanDaftar($this->kegiatan);
    }

    /**
     * Sesi beserta jamnya, sudah dibersihkan.
     *
     * Larik kosong berarti tarif ini tidak menyetel sesi sendiri — dan itu
     * BUKAN berarti layanannya tidak punya sesi: pemanggilnya jatuh kembali
     * ke daftar bawaan di PendaftaranSemuaLayanan::SESI. Tanpa itu, tarif baru
     * yang dibuat saat harganya naik akan menghapus jadwal sesi tanpa ada yang
     * meminta.
     *
     * @return list<array{nilai: string, nama: string, mulai: string, selesai: string}>
     */
    public function getDaftarSesiAttribute(): array
    {
        $keluar = [];

        foreach ((array) ($this->sesi ?? []) as $sesi) {
            if (! is_array($sesi)) {
                continue;
            }

            $nama = trim((string) ($sesi['nama'] ?? ''));
            $mulai = trim((string) ($sesi['mulai'] ?? ''));
            $selesai = trim((string) ($sesi['selesai'] ?? ''));

            if ($nama === '' || $mulai === '' || $selesai === '') {
                continue;
            }

            $keluar[] = [
                'nilai' => self::nilaiSesi($nama),
                'nama' => $nama,
                'mulai' => $mulai,
                'selesai' => $selesai,
            ];
        }

        return $keluar;
    }

    /**
     * Sesinya dalam bentuk yang diketik di borang: satu baris satu sesi.
     *
     * Dipakai mengisi kotaknya kembali saat tarifnya dibuka untuk diubah.
     */
    public function getSesiTeksAttribute(): string
    {
        return self::sesiSebagaiTeks($this->daftar_sesi);
    }

    /**
     * Daftar sesi mana pun dalam bentuk yang diketik di borang.
     *
     * Dipakai juga untuk daftar BAWAAN, bukan hanya milik barisnya: borang
     * tarif mengisi kotaknya dengan jam yang SEDANG berlaku, supaya yang
     * terbaca di kartu dan yang terbaca di kotak isiannya sama. Kotak kosong
     * di sebelah kartu yang menyebut dua sesi terbaca seperti data yang gagal
     * dimuat.
     *
     * @param  list<array{nama: string, mulai: string, selesai: string}>  $daftar
     */
    public static function sesiSebagaiTeks(array $daftar): string
    {
        return implode("\n", array_map(
            fn ($s) => $s['nama'] . ', ' . $s['mulai'] . ' - ' . $s['selesai'],
            $daftar
        ));
    }

    /**
     * Nilai yang TERSIMPAN di baris pendaftaran untuk sebuah nama sesi.
     *
     * Namanya sendiri, huruf kecil, spasinya dirapatkan — jadi "Sesi 1"
     * tersimpan "sesi 1", sama persis dengan yang ditulis borang pendaftaran
     * umum selama ini (terukur 9 baris, semuanya berbentuk itu).
     *
     * Bukan nomor urut, dan bukan id tersembunyi. Nomor urut berubah artinya
     * begitu satu sesi dihapus — baris lama yang menunjuk "sesi 2" tiba-tiba
     * menunjuk sesi yang berbeda. Id tersembunyi aman, tetapi membuat kolom
     * `sesi` berisi sesuatu yang tidak bisa dibaca siapa pun saat menengok
     * basis datanya.
     *
     * Akibatnya mengganti NAMA sesi berarti pendaftaran berikutnya memakai
     * nilai baru sementara yang lama tetap memakai yang lama. Itu memang yang
     * diinginkan: yang sudah terjadi tidak berubah namanya belakangan.
     */
    public static function nilaiSesi(string $nama): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $nama)));
    }

    /**
     * Membaca sesi dari kotak teksnya: satu baris satu sesi.
     *
     * Bentuk yang diterima "Nama, 08.00 - 13.00", dan juga "Nama, 08.00,
     * 13.00" atau "Nama 08:00-13:00" — pemisahnya tidak dipaksa satu, sebab
     * yang mengetiknya bukan orang yang hafal format. Yang dicari dua jam di
     * barisnya; apa pun sebelum jam pertama jadi namanya.
     *
     * null kalau kotaknya memang kosong, supaya kolomnya bernilai NULL dan
     * jatuh ke daftar bawaan — berbeda artinya dari larik kosong.
     *
     * @return list<array{nama: string, mulai: string, selesai: string}>|null
     */
    public static function uraikanSesi(?string $teks): ?array
    {
        $baris = preg_split('/\r\n|\r|\n/', (string) $teks) ?: [];
        $keluar = [];

        foreach ($baris as $b) {
            // Penomoran yang ikut tersalin ("1. Sesi pagi") dibuang; yang
            // dipakai sebagai penanda sesi adalah namanya, bukan nomornya.
            $bersih = trim(preg_replace('/^\s*\d+\s*[.)]\s*/', '', (string) $b));

            if ($bersih === '') {
                continue;
            }

            if (! preg_match_all('/(\d{1,2})[.:](\d{2})/', $bersih, $jam, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            if (count($jam[0]) < 2) {
                continue;
            }

            $nama = trim(rtrim(substr($bersih, 0, $jam[0][0][1]), " \t,;|-–—"));

            if ($nama === '') {
                continue;
            }

            $keluar[] = [
                'nama' => mb_substr($nama, 0, 60),
                // Disimpan "HH:MM", bentuk yang langsung bisa masuk kolom
                // waktu_mulai/waktu_selesai di tabel pendaftarannya.
                'mulai' => self::jamRapi($jam[1][0][0], $jam[2][0][0]),
                'selesai' => self::jamRapi($jam[1][1][0], $jam[2][1][0]),
            ];
        }

        return $keluar === [] ? null : $keluar;
    }

    private static function jamRapi(string $jam, string $menit): string
    {
        return str_pad($jam, 2, '0', STR_PAD_LEFT) . ':' . $menit;
    }

    /** Apakah layanan ini sudah punya cetakan deskripsi yang bisa dipakai. */
    public function getAdaCetakanAttribute(): bool
    {
        return trim((string) $this->template_deskripsi) !== '';
    }

    /** @param mixed $nilai */
    private static function bersihkanDaftar($nilai): array
    {
        return array_values(array_filter(
            (array) ($nilai ?? []),
            fn ($f) => trim((string) $f) !== ''
        ));
    }

    /** Jumlah yang benar-benar dibayar pelanggan, sebelum promo dan kode unik. */
    public function getTotalDibayarAttribute(): int
    {
        $dasar = (int) $this->biaya_persesi;

        return $dasar + (int) round($dasar * $this->ppn_persen / 100);
    }
}
