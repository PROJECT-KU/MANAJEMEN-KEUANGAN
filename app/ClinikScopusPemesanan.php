<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\User;

class ClinikScopusPemesanan extends Model
{
    protected $table = 'clinikscopus_pemesanan';

    protected $fillable = [
        'clinikscopus_id',
        'trainer_id',
        'customer_id',
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
        'gambar',
        'ip_address',
        'browser',
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

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
