<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
