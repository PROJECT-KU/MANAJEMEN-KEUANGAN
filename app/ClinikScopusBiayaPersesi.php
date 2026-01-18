<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinikScopusBiayaPersesi extends Model
{
    protected $table = 'clinikscopus_biaya_persesi';
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
        'biaya_persesi',
        'ppn',
        'status',

    ];

    public function clinikScopus()
    {
        return $this->hasMany(
            \App\ClinikScopus::class,
            'biaya_persesi_id'
        );
    }
}
