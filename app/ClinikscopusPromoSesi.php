<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClinikscopusPromoSesi extends Model
{
    protected $table = 'clinikscopus_promo_sesi';

    protected $fillable = [
        'promo_id',
        'clinikscopus_id',
        'sesi_key',
        'sesi_nomor'
    ];

    public function promo()
    {
        return $this->belongsTo(
            ClinikscopusPromo::class,
            'promo_id'
        );
    }

    public function event()
    {
        return $this->belongsTo(
            Clinikscopus::class,
            'clinikscopus_id'
        );
    }
}
