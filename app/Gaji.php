<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
  /**
   * @var string
   */
  protected $table = 'gaji';

  /**
   * @var array
   */
  protected $fillable = [
    'user_id',
    'id_transaksi',
    'gaji_pokok',
    'lembur',
    'bonus',
    'bonus_luar',
    'tunjangan',
    'jumlah_lembur',
    'jumlah_bonus',
    'jumlah_bonus_luar',
    'tanggal',
    'total_lembur',
    'total_bonus',
    'potongan',
    'total',
  ];
}
