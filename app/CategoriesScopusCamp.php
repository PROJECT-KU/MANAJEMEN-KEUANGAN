<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;

/**
 * Angkatan Scopus Camp.
 *
 * Tinggal penyaring di atas KategoriLayanan sejak kategorinya disatukan.
 * Dipertahankan supaya kode lama yang memakai kelas ini tidak perlu diubah
 * sekaligus — dan supaya tidak ada kueri yang lupa menyaring layanannya, yang
 * akibatnya layar Scopus Camp menampilkan angkatan Bibliometrik.
 */
class CategoriesScopusCamp extends KategoriLayanan
{
    public const LAYANAN = 'scopus_camp';

    protected static function booted(): void
    {
        static::addGlobalScope('layanan', fn (Builder $q) => $q->where('layanan', self::LAYANAN));

        static::creating(function ($model) {
            $model->layanan = self::LAYANAN;
        });
    }
}
