<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranScopusCamp extends Model
{
    /**
     * @var string
     */
    protected $table = 'scopus_camp_pendaftaran';

    /**
     * @var array
     */
    protected $fillable = [
        'token',
        'id_transaksi',
        'scopus_camp_kategori_id',
        'email',
        'nama',
        'telp',
        'affiliasi',
        'ppn',
        'kode_unik',
        'gambar',
        'jumlah_pendaftar',
        'kode_diskon',
        'nominal_diskon',
        'total_pembayaran',
        'status',
        'tanggal_reschedule',
        'group_wa',
        'note',
        'created_at',
        'updated_at',
    ];
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
