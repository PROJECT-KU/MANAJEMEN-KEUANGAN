<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Camp extends Model
{
    /**
     * @var string
     */
    protected $table = 'camp';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'id_transaksi',
        'title',
        'camp_ke',
        'uang_masuk',
        'lain_lain',
        'total_uang_masuk',
        'gaji_trainer',
        'gaji_trainer_nama',
        'gaji_trainer1',
        'gaji_trainer_nama1',
        'gaji_trainer2',
        'gaji_trainer_nama2',
        'gaji_trainer3',
        'gaji_trainer_nama3',
        'gaji_trainer4',
        'gaji_trainer_nama4',
        'gaji_trainer5',
        'gaji_trainer_nama5',
        'gaji_trainer6',
        'gaji_trainer_nama6',
        'gaji_team',
        'gaji_team_nama',
        'gaji_team1',
        'gaji_team_nama1',
        'gaji_team2',
        'gaji_team_nama2',
        'gaji_team3',
        'gaji_team_nama3',
        'gaji_team4',
        'gaji_team_nama4',
        'gaji_team5',
        'gaji_team_nama5',
        'gaji_team6',
        'gaji_team_nama6',
        'gaji_team7',
        'gaji_team_nama7',
        'gaji_team8',
        'gaji_team_nama8',
        'gaji_team9',
        'gaji_team_nama9',
        'gaji_team10',
        'gaji_team_nama10',
        'team_cabang',
        'booknote',
        'grammarly',
        'tiket_trainer',
        'tiket_team',
        'hotel',
        'marketing',
        'konsumsi_tambahan',
        'lainnya',
        'total',
        'total_gaji_trainer',
        'total_gaji_team',
        'keuntungan',
        'persentase_keuntungan',
        'tanggal',
        'tanggal_akhir',
        'status',
        'note',
    ];
}
