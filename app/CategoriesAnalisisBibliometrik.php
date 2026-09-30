<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;

/**
 * Angkatan Analisis Bibliometrik.
 *
 * Penyaring di atas KategoriLayanan; lihat keterangan di CategoriesScopusCamp.
 */
class CategoriesAnalisisBibliometrik extends KategoriLayanan
{
    public const LAYANAN = 'bibliometrik';

    protected static function booted(): void
    {
        static::addGlobalScope('layanan', fn (Builder $q) => $q->where('layanan', self::LAYANAN));

        static::creating(function ($model) {
            $model->layanan = self::LAYANAN;
        });
    }
}
