<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;

/**
 * Angkatan Class Online.
 *
 * Barisnya hidup di `kategori_layanan` bersama kelima layanan lain, jadi
 * kelas ini ADA justru untuk menyaringnya. Katalog sumber sempat menunjuk
 * langsung ke KategoriLayanan telanjang, dan akibatnya dua dan keduanya
 * buruk: kuerinya memulangkan seluruh angkatan SEMUA layanan — terukur 380+
 * baris, dan satu berkas uji yang biasanya beberapa detik jadi lebih dari
 * lima menit — sementara angkatan yang dibuat lewatnya tersimpan tanpa
 * kolom `layanan` sama sekali, jadi ia tidak pernah muncul lagi di layar
 * mana pun.
 */
class CategoriesOnlineTraining extends KategoriLayanan
{
    public const LAYANAN = 'online_training';

    protected static function booted(): void
    {
        static::addGlobalScope('layanan', fn (Builder $q) => $q->where('layanan', self::LAYANAN));

        static::creating(function ($model) {
            $model->layanan = self::LAYANAN;
        });
    }
}
