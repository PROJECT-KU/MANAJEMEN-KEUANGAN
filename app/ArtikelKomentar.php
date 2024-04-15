<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArtikelKomentar extends Model
{
    /**
     * @var string
     */
    protected $table = 'artikel_komentar';

    /**
     * @var array
     */
    protected $fillable = [
        'token',
        'user_id',
        'categories_artikel_id',
        'artikel_id',
        'reply',
        'nama',
        'email',
        'link_website',
        'komentar',
        'created_at',
        'updated_at',
    ];
}
