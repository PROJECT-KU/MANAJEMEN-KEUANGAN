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
    'token',
    'lembur',
    'lembur1',
    'lembur2',
    'lembur3',
    'lembur4',
    'lembur5',
    'lembur6',
    'lembur7',
    'lembur8',
    'lembur9',
    'lembur10',
    'jumlah_lembur',
    'jumlah_lembur1',
    'jumlah_lembur2',
    'jumlah_lembur3',
    'jumlah_lembur4',
    'jumlah_lembur5',
    'jumlah_lembur6',
    'jumlah_lembur7',
    'jumlah_lembur8',
    'jumlah_lembur9',
    'jumlah_lembur10',
    'bonus',
    'bonus1',
    'bonus2',
    'bonus3',
    'bonus4',
    'bonus5',
    'bonus6',
    'bonus7',
    'bonus8',
    'bonus9',
    'bonus10',
    'bonus_luar',
    'bonus_luar1',
    'bonus_luar2',
    'bonus_luar3',
    'bonus_luar4',
    'bonus_luar5',
    'bonus_luar6',
    'bonus_luar7',
    'bonus_luar8',
    'bonus_luar9',
    'bonus_luar10',
    'jumlah_bonus',
    'jumlah_bonus1',
    'jumlah_bonus2',
    'jumlah_bonus3',
    'jumlah_bonus4',
    'jumlah_bonus5',
    'jumlah_bonus6',
    'jumlah_bonus7',
    'jumlah_bonus8',
    'jumlah_bonus9',
    'jumlah_bonus10',
    'jumlah_bonus_luar',
    'jumlah_bonus_luar1',
    'jumlah_bonus_luar2',
    'jumlah_bonus_luar3',
    'jumlah_bonus_luar4',
    'jumlah_bonus_luar5',
    'jumlah_bonus_luar6',
    'jumlah_bonus_luar7',
    'jumlah_bonus_luar8',
    'jumlah_bonus_luar9',
    'jumlah_bonus_luar10',
    'tunjangan',
    'tunjangan_bpjs',
    'tunjangan_thr',
    'tunjangan_pulsa',
    'tanggal',
    'total_lembur',
    'total_bonus',
    'webinar',
    'kinerja',
    'potongan',
    'pph',
    'alpha',
    'total',
    'status',
    'note',
    'gambar',
    'email',
  ];
}
