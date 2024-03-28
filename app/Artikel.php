<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    /**
     * @var string
     */
    protected $table = 'artikel';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'categories_artikel_id',
        'token',
        'judul',
        'kata_kunci',
        'gambar_depan',
        'gambar_cover',
        'isi',
        'dilihat',
        'status',
        'created_at',
        'updated_at',
    ];
}
