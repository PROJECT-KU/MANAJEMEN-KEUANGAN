<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu pendaftaran yang sudah dihapus, beserta potret lengkapnya.
 *
 * @see \App\Actions\Pendaftaran\HapusPendaftaran yang menulisnya
 */
class PendaftaranDihapus extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_dihapus';

    protected $guarded = [];

    protected $casts = [
        'potret' => 'array',
        'total' => 'integer',
        'uang_terhapus' => 'integer',
        'jumlah_pembayaran' => 'integer',
        'jumlah_jejak' => 'integer',
    ];

    public function scopeTerbaru($kueri)
    {
        return $kueri->orderByDesc('created_at')->orderByDesc('id');
    }

    /** Nama layanannya seperti tertulis di katalog, bukan kuncinya. */
    public function getLayananNamaAttribute(): string
    {
        return \App\Support\PendaftaranSemuaLayanan::katalog()[$this->layanan]['nama']
            ?? $this->layanan;
    }

    public function getUangTerhapusTulisAttribute(): string
    {
        return 'Rp ' . number_format($this->uang_terhapus, 0, ',', '.');
    }

    public function getTotalTulisAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
