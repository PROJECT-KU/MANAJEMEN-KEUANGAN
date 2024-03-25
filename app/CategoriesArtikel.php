<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoriesArtikel extends Model
{
    /**
     * @var string
     */
    protected $table = 'categories_artikel';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'kategori',
        'jumlah_artikel',
        'created_at',
        'updated_at',
    ];
}
