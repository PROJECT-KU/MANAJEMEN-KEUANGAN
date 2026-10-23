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
        'kategori_id',
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
        // Tali ke promo yang memotong harganya; dibaca layar dampak promo.
        'promo_id',
        'total_pembayaran',
        'status',
        'tanggal_reschedule',
        'group_wa',
        'note',
        'created_at',
        'updated_at',
        'cara_bayar',

        /*
         * Tagihan di gerbang pembayaran (DOKU).
         *
         * Namanya MENYALIN Webinar Eksklusif persis, sebab layar Pendaftar
         * Layanan membaca kelima layanan lewat satu UNION. Yang hilang dari
         * daftar ini tidak akan pernah tersimpan dan tidak ada galat apa pun
         * yang memberi tahu — nilainya cuma menguap saat create().
         */
        'bayar_rujukan',
        'bayar_permintaan_id',
        'bayar_url',
        'bayar_status',
        'bayar_kanal',
        'bayar_pada',
        'kedaluwarsa_pada',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
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
