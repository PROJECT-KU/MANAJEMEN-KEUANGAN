<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
  /**
   * @var string
   */
  protected $table = 'presensi';

  /**
   * @var array
   */
  protected $fillable = [
    'user_id',
    'status',
    'status_pulang',
    'note',
    'gambar',
    'gambar_pulang',
    'lokasi',
    'time_pulang',
    'latitude',
    'longitude',
    'created_at',
    'updated_at',
  ];
}
