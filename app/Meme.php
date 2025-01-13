<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Meme extends Model
{
    /**
     * @var string
     */
    protected $table = 'meme';

    /**
     * @var array
     */
    protected $fillable = [
        'token',
        'sesi',
        'waktu_mulai',
        'waktu_selesai',
        'kuota',
        'biaya',
        'deskripsi',
        'lokasi',
        'status',
        'gambar',
        'created_at',
        'updated_at',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
