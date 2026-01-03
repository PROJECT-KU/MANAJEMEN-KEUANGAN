<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Clinikscopus extends Model
{
    /**
     * @var string
     */

    protected $table = 'clinikscopus';

    /**
     * @var array
     */
    protected $fillable = [

        'user_id',
        'sesi',
        'sesi2',
        'sesi3',
        'sesi4',
        'sesi5',
        'sesi6',
        'sesi7',
        'spesialis',
        'status',
        'tanggal',
        'foto',
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
