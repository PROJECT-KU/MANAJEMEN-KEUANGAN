<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cuti extends Model
{
    /**
     * @var string
     */
    protected $table = 'cuti';

    /**
     * @var array
     */
    protected $fillable = [
        // PAPER
        'user_id',
        'id_pengajuan',
        'jabatan',
        'jenis_cuti',
        'tanggal_mulai_cuti',
        'tanggal_selesai_cuti',
        'total_hari_cuti',
        'keterangan',
        'dokumen',
        'status',
        'disetujui_pada',
        'alasan_ditolak',
        'dibatalkan_pada',
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
