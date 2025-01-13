<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Paperisasi extends Model
{
    /**
     * @var string
     */
    protected $table = 'paperisasi';

    /**
     * @var array
     */
    protected $fillable = [
        // PAPER
        'token',
        'id_paper',
        'tanggal_masuk_paper',
        'judul_paper',
        'first_author',
        'affiliasi_first_author',
        'co_author',
        'affiliasi_co_author',
        'jurnal_target',
        'q_jurnal',
        'estimasi_terbit',
        'apc_jurnal',
        'status_paper',

        // PROGERS ANATOMI
        'progres_anatomi_tanggal',
        'progres_anatomi_status',
        'progres_anatomi_estimasi',
        'progres_anatomi_keterangan',
        'file_anatomi',

        'progres_anatomi_tanggal_second',
        'progres_anatomi_status_second',
        'progres_anatomi_estimasi_second',
        'progres_anatomi_keterangan_second',
        'file_anatomi_second',

        'progres_anatomi_tanggal_third',
        'progres_anatomi_status_third',
        'progres_anatomi_estimasi_third',
        'progres_anatomi_keterangan_third',
        'file_anatomi_third',

        // PROGRES PAPER
        'progres_paper_tanggal',
        'progres_paper_status',
        'progres_paper_estimasi',
        'progres_paper_keterangan',
        'file_paper',

        'progres_paper_tanggal_second',
        'progres_paper_status_second',
        'progres_paper_estimasi_second',
        'progres_paper_keterangan_second',
        'file_paper_second',

        'progres_paper_tanggal_third',
        'progres_paper_status_third',
        'progres_paper_estimasi_third',
        'progres_paper_keterangan_third',
        'file_paper_third',

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
