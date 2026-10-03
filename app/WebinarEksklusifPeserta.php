<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Satu peserta KEDUA DAN SETERUSNYA dalam sebuah pendaftaran.
 *
 * Peserta pertama tetap di kolom nama/email pendaftarannya: dialah yang
 * dihubungi dan yang membayar. Tabel ini menyimpan sisanya, supaya
 * sertifikat bisa diterbitkan atas nama yang benar dan panitia tahu siapa
 * saja yang perlu dimasukkan ke grup.
 */
class WebinarEksklusifPeserta extends Model
{
    protected $table = 'webinar_eksklusif_peserta';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['pendaftaran_id', 'urutan', 'nama', 'email'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(WebinarEksklusifPendaftaran::class, 'pendaftaran_id');
    }
}
