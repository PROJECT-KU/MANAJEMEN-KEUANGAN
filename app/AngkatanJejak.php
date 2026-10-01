<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Satu peristiwa pada satu angkatan: dibuat, diubah, dihapus, dipulihkan.
 *
 * Ditulis dari kait model KategoriLayanan, bukan dari pengendali, supaya tidak
 * ada jalan masuk yang bisa melewatinya — termasuk perubahan massal dan
 * perintah artisan.
 */
class AngkatanJejak extends Model
{
    protected $table = 'angkatan_jejak';

    protected $keyType = 'string';

    public $incrementing = false;

    /** Hanya created_at; peristiwa tidak pernah diubah sesudah dicatat. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'kategori_id', 'nama', 'aksi', 'ringkasan', 'data', 'oleh_id', 'oleh_nama',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Mencatat satu peristiwa.
     *
     * Nama penggunanya DISALIN, bukan ditunjuk lewat relasi: akun bisa dihapus
     * sesudahnya, dan jejak yang penulisnya jadi kosong kehilangan separuh
     * gunanya.
     *
     * @param  array<string, mixed>|null  $data
     */
    public static function catat(
        KategoriLayanan $angkatan,
        string $aksi,
        ?string $ringkasan = null,
        ?array $data = null
    ): void {
        $orang = Auth::user();

        self::create([
            'kategori_id' => $angkatan->getKey(),
            'nama' => trim($angkatan->nama . ($angkatan->nama_ke ? ' ke-' . $angkatan->nama_ke : '')),
            'aksi' => $aksi,
            'ringkasan' => $ringkasan,
            'data' => $data,
            'oleh_id' => $orang?->id,
            'oleh_nama' => $orang?->full_name ?: $orang?->username,
        ]);
    }

    /** Kalimat "oleh siapa" yang siap ditampilkan. */
    public function getOlehAttribute(): string
    {
        return $this->oleh_nama ?: 'sistem';
    }

    /**
     * Tanggal dan jam dalam bahasa Indonesia.
     *
     * translatedFormat, bukan format: APP_LOCALE di berkas .env masih 'en',
     * jadi format() menulis "October" di layar berbahasa Indonesia.
     */
    public function getWaktuAttribute(): string
    {
        return $this->created_at?->locale('id')->translatedFormat('j M Y, H:i') ?? '—';
    }
}
