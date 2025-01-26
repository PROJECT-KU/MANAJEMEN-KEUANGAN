<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RefrensiPaper extends Model
{
    /**
     * @var string
     */
    protected $table = 'refrensi_paper';

    /**
     * @var array
     */
    protected $fillable = [
        // PAPER
        'token',
        'nama_author',
        'nama_journal',
        'quartile_journal',
        'subjek_area_journal',
        'abstrak',
        'judul_paper',
        'doi',
        'apc',
        'type',
        'file',
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
