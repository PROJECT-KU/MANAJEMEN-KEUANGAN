<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Meme_Pendaftaran extends Model
{
    /**
     * @var string
     */
    protected $table = 'meme_pendaftaran';

    /**
     * @var array
     */
    protected $fillable = [
        'meme_id',
        'token',
        'nama_pendaftar',
        'telp_pendaftar',
        'email_pendaftar',
        'institusi_pendaftar',
        'judul_pendaftar',
        'tanggal',
        'sesi',
        'waktu_mulai',
        'waktu_selesai',
        'kode_unik_biaya',
        'biaya',
        'total',
        'status',
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
