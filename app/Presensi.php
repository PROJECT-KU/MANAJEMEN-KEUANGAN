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
    'lokasi',
    'time_pulang',
    'created_at',
    'updated_at',
  ];
}
