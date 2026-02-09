<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinikScopusTestimoni extends Model
{


    protected $table = 'clinik_scopus_testimoni';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'clinikscopus_id',
        'clinikscopus_pemesanan_id',
        'trainer_id',
        'customer_id',
        'id_transaksi',
        'kode_booking',
        'sesi',
        'jam_sesi',
        'rating',
        'komentar',
        'rating_aplikasi',
        'komentar_aplikasi',
        'is_anonymous',
        'status',
    ];

    // RELASI KE TRAINER
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id', 'id');
    }

    // RELASI KE CUSTOMER
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    // RELASI KE PEMESANAN
    public function sesi()
    {
        return $this->belongsTo(ClinikScopus::class, 'clinikscopus_id', 'id');
    }

    // RELASI KE PEMESANAN
    public function pemesanan()
    {
        return $this->belongsTo(ClinikScopusPemesanan::class, 'clinikscopus_pemesanan_id', 'id');
    }
}
