<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    /**
     * @var string
     */
    protected $table = 'peserta';

    /**
     * @var array
     */
    protected $fillable = [
        'token',
        'email',
        'nama',
        'afiliasi',
        'judul',
        'jurnal',
        'refrensi',
        'digital_writing',
        'mendeley',
        'persentase_penyelesaian',
        'submit',
        'scopus_camp',
        'materi',
        'makanan',
        'pelayanan',
        'tempat',
        'terfavorit',
        'terbaik',
        'menyebalkan',
        'kritik',
        'created_at',
        'updated_at',
    ];
}
