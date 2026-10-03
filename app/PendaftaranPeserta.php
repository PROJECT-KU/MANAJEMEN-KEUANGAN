<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Peserta lain dalam satu pendaftaran rombongan.
 *
 * Induknya ditunjuk pasangan (layanan, pendaftaran_id), bukan relasi
 * Eloquent: sasarannya lima tabel berbeda tergantung layanannya.
 */
class PendaftaranPeserta extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_peserta';

    protected $fillable = ['layanan', 'pendaftaran_id', 'urutan', 'nama', 'email', 'telp'];

    /** @return \Illuminate\Database\Eloquent\Builder<self> */
    public function scopeMilik($kueri, string $layanan, string $pendaftaranId)
    {
        return $kueri->where('layanan', $layanan)->where('pendaftaran_id', $pendaftaranId);
    }

    /** Terurut seperti yang diketik; created_at tidak bisa dipakai. */
    public function scopeTerurut($kueri)
    {
        return $kueri->orderBy('urutan');
    }
}
