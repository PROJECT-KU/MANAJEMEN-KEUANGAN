<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinikscopusPromo extends Model
{
    protected $table = 'clinikscopus_promo';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'nama_promo',
        'status',
        'tanggal_mulai_promo',
        'tanggal_selesai_promo',
        'harga_normal',
        'tipe_diskon',
        'diskon_persentase',
        'nominal_diskon',
        'kode_diskon',
        'total_biaya'
    ];

    // 🔗 Promo → Banyak Event
    public function events()
    {
        return $this->belongsToMany(
            Clinikscopus::class,
            'clinikscopus_promo_items',
            'promo_id',
            'clinikscopus_id'
        )->withTimestamps();
    }

    // 🔗 Promo → Banyak Sesi
    public function sesi()
    {
        return $this->hasMany(
            ClinikscopusPromoSesi::class,
            'promo_id'
        );
    }
}
