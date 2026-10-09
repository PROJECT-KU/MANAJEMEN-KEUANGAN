<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kali dana dikembalikan ke pendaftar.
 *
 * Induknya ditunjuk pasangan (layanan, pendaftaran_id), bukan relasi
 * Eloquent: sasarannya lima tabel berbeda tergantung layanannya — pola yang
 * sama dengan pendaftaran_jejak dan pendaftaran_peserta.
 *
 * BOLEH LEBIH DARI SATU untuk satu pendaftaran: refund sebagian lalu
 * sebagian lagi memang terjadi, dan satu baris per pendaftaran akan menimpa
 * catatan yang pertama tanpa jejak.
 */
class PendaftaranPengembalian extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_pengembalian';

    protected $fillable = [
        'layanan', 'pendaftaran_id', 'nominal', 'tanggal',
        'cara', 'catatan', 'oleh_id', 'oleh_nama',
    ];

    protected $casts = ['tanggal' => 'date', 'nominal' => 'integer'];

    /** @return \Illuminate\Database\Eloquent\Builder<self> */
    public function scopeMilik($kueri, string $layanan, string $pendaftaranId)
    {
        return $kueri->where('layanan', $layanan)->where('pendaftaran_id', $pendaftaranId);
    }

    /** Terbaru di atas: yang dicari saat membukanya adalah yang terakhir. */
    public function scopeTerurut($kueri)
    {
        return $kueri->orderByDesc('tanggal')->orderByDesc('created_at');
    }

    /** Jumlah yang sudah dikembalikan untuk satu pendaftaran. */
    public static function totalMilik(string $layanan, string $pendaftaranId): int
    {
        return (int) static::milik($layanan, $pendaftaranId)->sum('nominal');
    }

    /** Nominal yang bisa dibaca orang. */
    public function getNominalTulisAttribute(): string
    {
        return 'Rp ' . number_format((int) $this->nominal, 0, ',', '.');
    }

    /**
     * Tanggalnya dalam bahasa Indonesia.
     *
     * locale('id') disebut sendiri: APP_LOCALE=en, jadi translatedFormat
     * tanpa itu memulangkan nama bulan Inggris di layar berbahasa Indonesia.
     */
    public function getTanggalTulisAttribute(): string
    {
        return $this->tanggal
            ? $this->tanggal->locale('id')->translatedFormat('d F Y')
            : '';
    }
}
