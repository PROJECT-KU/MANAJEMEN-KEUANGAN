<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PerjalananDinas extends Model
{
    /**
     * @var string
     */

    protected $table = 'perjalanan_dinas';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'id_transaksi',

        // <!-- INPUT 1 -->
        'tanggal',
        'uang_masuk',
        'uang_keluar',
        'keterangan',
        'gambar',
        // <!-- END -->

        // <!-- INPUT 2 -->
        'uang_keluar2',
        'keterangan2',
        'gambar2',
        // <!-- END -->

        // <!-- INPUT 3 -->
        'uang_keluar3',
        'keterangan3',
        'gambar3',
        // <!-- END -->

        // <!-- INPUT 4 -->
        'uang_keluar4',
        'keterangan4',
        'gambar4',
        // <!-- END -->

        // <!-- INPUT 5 -->
        'uang_keluar5',
        'keterangan5',
        'gambar5',
        // <!-- END -->

        // <!-- INPUT 6 -->
        'uang_keluar6',
        'keterangan6',
        'gambar6',
        // <!-- END -->

        // <!-- INPUT 7 -->
        'uang_keluar7',
        'keterangan7',
        'gambar7',
        // <!-- END -->

        // <!-- INPUT 8 -->
        'uang_keluar8',
        'keterangan8',
        'gambar8',
        // <!-- END -->

        // <!-- INPUT 9 -->
        'uang_keluar9',
        'keterangan9',
        'gambar9',
        // <!-- END -->

        // <!-- INPUT 10 -->
        'uang_keluar10',
        'keterangan10',
        'gambar10',
        // <!-- END -->

        // <!-- INPUT 11 -->
        'tanggal11',
        'uang_masuk11',
        'uang_keluar11',
        'keterangan11',
        'gambar11',
        // <!-- END -->

        // <!-- INPUT 12 -->
        'uang_keluar12',
        'keterangan12',
        'gambar12',
        // <!-- END -->

        // <!-- INPUT 13 -->
        'uang_keluar13',
        'keterangan13',
        'gambar13',
        // <!-- END -->

        // <!-- INPUT 14 -->
        'uang_keluar14',
        'keterangan14',
        'gambar14',
        // <!-- END -->

        // <!-- INPUT 15 -->
        'uang_keluar15',
        'keterangan15',
        'gambar15',
        // <!-- END -->

        // <!-- INPUT 16 -->
        'uang_keluar16',
        'keterangan16',
        'gambar16',
        // <!-- END -->

        // <!-- INPUT 17 -->
        'uang_keluar17',
        'keterangan17',
        'gambar17',
        // <!-- END -->

        // <!-- INPUT 18 -->
        'uang_keluar18',
        'keterangan18',
        'gambar18',
        // <!-- END -->

        // <!-- INPUT 19 -->
        'uang_keluar19',
        'keterangan19',
        'gambar19',
        // <!-- END -->

        // <!-- INPUT 20 -->
        'uang_keluar20',
        'keterangan20',
        'gambar20',
        // <!-- END -->

        // <!-- INPUT 21 -->
        'tanggal21',
        'uang_masuk21',
        'uang_keluar21',
        'keterangan21',
        'gambar21',
        // <!-- END -->

        // <!-- INPUT 22 -->
        'uang_keluar22',
        'keterangan22',
        'gambar22',
        // <!-- END -->

        // <!-- INPUT 23 -->
        'uang_keluar23',
        'keterangan23',
        'gambar23',
        // <!-- END -->

        // <!-- INPUT 24 -->
        'uang_keluar24',
        'keterangan24',
        'gambar24',
        // <!-- END -->

        // <!-- INPUT 25 -->
        'uang_keluar25',
        'keterangan25',
        'gambar25',
        // <!-- END -->

        // <!-- INPUT 26 -->
        'uang_keluar26',
        'keterangan26',
        'gambar26',
        // <!-- END -->

        // <!-- INPUT 27 -->
        'uang_keluar27',
        'keterangan27',
        'gambar27',
        // <!-- END -->

        // <!-- INPUT 28 -->
        'uang_keluar28',
        'keterangan28',
        'gambar28',
        // <!-- END -->

        // <!-- INPUT 29 -->
        'uang_keluar29',
        'keterangan29',
        'gambar29',
        // <!-- END -->

        // <!-- INPUT 30 -->
        'uang_keluar30',
        'keterangan30',
        'gambar30',
        // <!-- END -->

        // <!-- INPUT 31 -->
        'tanggal31',
        'uang_masuk31',
        'uang_keluar31',
        'keterangan31',
        'gambar31',
        // <!-- END -->

        // <!-- INPUT 32 -->
        'uang_keluar32',
        'keterangan32',
        'gambar32',
        // <!-- END -->

        // <!-- INPUT 33 -->
        'uang_keluar33',
        'keterangan33',
        'gambar33',
        // <!-- END -->

        // <!-- INPUT 34 -->
        'uang_keluar34',
        'keterangan34',
        'gambar34',
        // <!-- END -->

        // <!-- INPUT 35 -->
        'uang_keluar35',
        'keterangan35',
        'gambar35',
        // <!-- END -->

        // <!-- INPUT 36 -->
        'tanggal36',
        'uang_masuk36',
        'uang_keluar36',
        'keterangan36',
        'gambar36',
        // <!-- END -->

        // <!-- INPUT 37 -->
        'uang_keluar37',
        'keterangan37',
        'gambar37',
        // <!-- END -->

        // <!-- INPUT 38 -->
        'uang_keluar38',
        'keterangan38',
        'gambar38',
        // <!-- END -->

        // <!-- INPUT 39 -->
        'uang_keluar39',
        'keterangan39',
        'gambar39',
        // <!-- END -->

        // <!-- INPUT 40 -->
        'uang_keluar40',
        'keterangan40',
        'gambar40',
        // <!-- END -->

        'status',
        'tempat',
        'camp',
        'tanggal_mulai',
        'tanggal_akhir',
        'deskripsi',
        'total_uang_masuk',
        'total_uang_keluar',
        'sisa_saldo',
        'created_at',
        'updated_at',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
