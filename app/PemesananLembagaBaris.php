<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu pendaftaran yang terikat ke sebuah pesanan lembaga.
 *
 * Tabel pengikat tersendiri, bukan kolom di kelima tabel pendaftaran:
 * menambah kolom di lima tabel berarti lima migrasi yang harus berjalan
 * bersamaan, dan kelimanya menunjuk hal yang sama.
 */
class PemesananLembagaBaris extends Model
{
    use HasUuids;

    protected $table = 'pemesanan_lembaga_baris';

    protected $fillable = ['pemesanan_id', 'layanan', 'pendaftaran_id'];

    public function pemesanan()
    {
        return $this->belongsTo(PemesananLembaga::class, 'pemesanan_id');
    }
}
