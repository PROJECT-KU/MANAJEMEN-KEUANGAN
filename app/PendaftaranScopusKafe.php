<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranScopusKafe extends Model
{
    /**
     * @var string
     */
    protected $table = 'pendaftaran_scopus_kafe';

    /**
     * @var array
     */
    protected $fillable = [
        'id_pemesanan',

        // detail data diri
        'nama',
        'tanggal_pemesanan',
        'email',
        'telp',

        // form sesi 1
        'sesi',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'biaya',
        'kode_unik_pembayaran',
        'subtotal_pembayaran',

        // form sesi 2
        'sesi_kedua',
        'waktu_mulai_kedua',
        'waktu_selesai_kedua',
        'lokasi_kedua',
        'biaya_kedua',
        'kode_unik_pembayaran_kedua',
        'subtotal_pembayaran_kedua',

        // form sesi 3
        'sesi_ketiga',
        'waktu_mulai_ketiga',
        'waktu_selesai_ketiga',
        'lokasi_ketiga',
        'biaya_ketiga',
        'kode_unik_pembayaran_ketiga',
        'subtotal_pembayaran_ketiga',

        'total_keseluruhan_pembayaran',
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
