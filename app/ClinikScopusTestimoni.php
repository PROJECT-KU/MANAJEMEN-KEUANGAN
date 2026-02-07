<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinikScopusTestimoni extends Model
{
    protected $table = 'clinik_scopus_testimoni';
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
        'clinikscopus_id',
        'trainer_id',
        'customer_id',
        'tipe_testimoni',
        'id_transaksi',
        'kode_booking',
        'sesi',
        'jam_sesi',
        'rating',
        'komentar',
        'rating_aplikasi',
        'komentar_aplikasi',
        'platform',
        'is_anonymous',
        'status',
    ];
}
