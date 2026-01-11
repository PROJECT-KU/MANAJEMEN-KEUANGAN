<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\ClinikScopusPromo;
use Carbon\Carbon;

class Clinikscopus extends Model
{
    protected $table = 'clinikscopus';
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

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'biaya_persesi_id',
        'sesi',
        'sesi2',
        'sesi3',
        'sesi4',
        'sesi5',
        'sesi6',
        'sesi7',
        'sesi8',
        'sesi9',
        'spesialis',
        'status',
        'tanggal',
        'foto'
    ];

    // 🔗 Event → Banyak Promo
    public function promos()
    {
        return $this->belongsToMany(
            ClinikscopusPromo::class,
            'clinikscopus_promo_items',
            'clinikscopus_id',
            'promo_id'
        )->withTimestamps();
    }

    // 🔗 Event → Banyak Sesi Promo
    public function promoSesi()
    {
        return $this->hasMany(
            ClinikscopusPromoSesi::class,
            'clinikscopus_id'
        );
    }

    public function scopeActiveToday($query)
    {
        return $query->whereDate('tanggal', Carbon::today());
    }

    // Trainer
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSesiListAttribute()
    {
        return collect([
            'sesi1' => $this->sesi,
            'sesi2' => $this->sesi2,
            'sesi3' => $this->sesi3,
            'sesi4' => $this->sesi4,
            'sesi5' => $this->sesi5,
            'sesi6' => $this->sesi6,
            'sesi7' => $this->sesi7,
            'sesi8' => $this->sesi8,
            'sesi9' => $this->sesi9,
        ])->filter()->values(); // ⬅️ buang null & rapikan index
    }

    public function biayaPersesi()
    {
        return $this->belongsTo(
            \App\ClinikScopusBiayaPersesi::class,
            'biaya_persesi_id'
        );
    }
}
