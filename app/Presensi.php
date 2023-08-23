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
    'note',
    'gambar',
    'lokasi',
    'created_at',
    'update_at',
  ];
}
