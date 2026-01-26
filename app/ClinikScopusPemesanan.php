<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClinikScopusPemesanan extends Model
{
    protected $table = 'clinikscopus_pemesanan';

    protected $fillable = [
        'clinikscopus_id',
        'user_id',
        'id_transaksi',
        'kode_booking',
        'sesi',
        'jam_sesi',
        'nama_pemesan',
        'afiliasi_pemesan',
        'email_pemesan',
        'telp_pemesan',
        'kendala',
        'desc_kendala',
        'harga_persesi',
        'diskon',
        'ppn',
        'kode_unik',
        'kode_diskon',
        'tipe_promo',
        'total_pembayaran',
        'status',
        'tanggal',
        'tanggal_booking',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
