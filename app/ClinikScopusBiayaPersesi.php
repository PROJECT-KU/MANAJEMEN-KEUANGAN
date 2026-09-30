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
 * Varian membedakan harga di dalam satu layanan:
 *
 *   Scopus Camp    jawa / luar_jawa   — ongkos penyelenggaraan berbeda
 *   Bibliometrik   online / offline   — offline menanggung tempat & konsumsi
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
        'fasilitas',
        'status',
    ];

    protected $casts = [
        'fasilitas' => 'array',
    ];

    public const AKTIF = 'active';

    public const NONAKTIF = 'non active';

    /**
     * Katalog layanan beserta varian dan satuannya.
     *
     * Ditulis sebagai data, bukan disebar sebagai @if di tampilan: menambah
     * layanan berikutnya cukup menambah satu baris di sini, dan layarnya
     * langsung ikut.
     */
    public const LAYANAN = [
        'clinik_scopus' => [
            'nama' => 'Clinik Scopus',
            'satuan' => 'per satu sesi',
            'ikon' => 'fa-user-md',
            'warna' => 'mis-biru',
            'varian' => [],
        ],
        'bibliometrik' => [
            'nama' => 'Analisis Bibliometrik',
            'satuan' => 'per peserta',
            'ikon' => 'fa-chart-line',
            'warna' => 'mis-ungu',
            'varian' => ['online' => 'Online', 'offline' => 'Offline'],
        ],
        'scopus_camp' => [
            'nama' => 'Scopus Camp',
            'satuan' => 'per peserta',
            'ikon' => 'fa-campground',
            'warna' => 'mis-hijau',
            'varian' => ['jawa' => 'Pulau Jawa', 'luar_jawa' => 'Luar Pulau Jawa'],
        ],
        'scopus_kafe' => [
            'nama' => 'Scopus Kafe',
            'satuan' => 'per pertemuan',
            'ikon' => 'fa-coffee',
            'warna' => 'mis-jingga',
            'varian' => [],
        ],
        'online_training' => [
            'nama' => 'Online Training',
            'satuan' => 'per paket',
            'ikon' => 'fa-chalkboard-teacher',
            'warna' => 'mis-kuning',
            'varian' => [],
        ],
    ];

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
        });
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
        return static::query()
            ->untuk($layanan, $varian)
            ->aktif()
            ->latest('updated_at')
            ->first();
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

    public function getNamaLayananAttribute(): string
    {
        return self::LAYANAN[$this->layanan]['nama'] ?? Str::title(str_replace('_', ' ', (string) $this->layanan));
    }

    public function getNamaVarianAttribute(): ?string
    {
        if (! $this->varian) {
            return null;
        }

        return self::LAYANAN[$this->layanan]['varian'][$this->varian]
            ?? Str::title(str_replace('_', ' ', $this->varian));
    }

    public function getSatuanAttribute(): string
    {
        return self::LAYANAN[$this->layanan]['satuan'] ?? 'per satuan';
    }

    /** Daftar fasilitas, selalu berupa larik walau kolomnya kosong. */
    public function getDaftarFasilitasAttribute(): array
    {
        return array_values(array_filter(
            (array) ($this->fasilitas ?? []),
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
