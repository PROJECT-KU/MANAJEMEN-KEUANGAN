<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Todolist extends Model
{
    /**
     * @var string
     */
    protected $table = 'todolist';

    /**
     * @var array
     */
    protected $fillable = [
        // PAPER
        'user_id',
        'user_id_kedua',
        'id_task',
        'tanggal_assign',
        'tanggal_deadline',
        'status',
        'prioritas_task',
        'judul_task',
        'deskripsi',
        'tasklist',
        'checked',
        'link_akses',
        'file_task',
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
