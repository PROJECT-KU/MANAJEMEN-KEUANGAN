<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    /**
     * @var string
     */
    protected $table = 'recruitment';

    /**
     * @var array
     */
    protected $fillable = [
        'nama',
        'email',
        'cv',
        'lamaran',
        'lainnya',
        'info',
        'pendidikan',
    ];
}
