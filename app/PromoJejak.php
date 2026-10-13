<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Satu peristiwa pada satu promo: dibuat, diubah, dinyalakan, dimatikan, dihapus.
 *
 * Bentuknya sengaja sama persis dengan AngkatanJejak. Promo mengubah HARGA
 * yang ditagihkan ke orang, jadi ia tidak boleh kalah terlacak dibanding
 * angkatan — dan dua bentuk jejak yang berbeda untuk dua hal yang sama berarti
 * dua layar yang harus dibaca dengan cara berbeda.
 *
 * Ditulis dari kait model PromoLayanan, bukan dari pengendali: promo diubah
 * dari borang, dari saklar di layar daftar, dan dari tombol gandakan. Mencatat
 * di pengendali berarti tiga tempat yang harus diingat, dan yang keempat pasti
 * terlewat.
 */
class PromoJejak extends Model
{
    protected $table = 'promo_jejak';

    /** Hanya created_at; peristiwa tidak pernah diubah sesudah dicatat. */
    public const UPDATED_AT = null;

    /*
     * Disimpan sampai MILIDETIK supaya dua peristiwa dalam satu detik tetap
     * bisa diurutkan. Tanpa ini, dibuat-lalu-langsung-dimatikan seri, dan
     * "yang terbaru" jadi bergantung apa pun yang kebetulan dikembalikan
     * MySQL — terukur: jejak()->first() memulangkan baris "dibuat".
     */
    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $fillable = [
        'promo_id', 'nama', 'aksi', 'ringkasan', 'data', 'oleh_id', 'oleh_nama',
    ];

    protected $casts = [
        'data' => 'array',
    ];

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
        PromoLayanan $promo,
        string $aksi,
        ?string $ringkasan = null,
        ?array $data = null
    ): void {
        $orang = Auth::user();

        self::create([
            'promo_id' => $promo->getKey(),
            'nama' => $promo->nama,
            'aksi' => $aksi,
            'ringkasan' => $ringkasan,
            'data' => $data,
            'oleh_id' => $orang?->id,
            'oleh_nama' => $orang?->full_name ?: $orang?->username,
        ]);
    }

    /** Ikon dan warna per jenis peristiwa, dipetakan di PHP bukan di Blade. */
    public const RUPA = [
        'dibuat' => ['fa-plus', 'hijau'],
        'diubah' => ['fa-pen', 'biru'],
        'dinyalakan' => ['fa-toggle-on', 'hijau'],
        'dimatikan' => ['fa-toggle-off', 'merah'],
        'digandakan' => ['fa-copy', 'ungu'],
        'dihapus' => ['fa-trash', 'merah'],
    ];

    public function getIkonAttribute(): string
    {
        return self::RUPA[$this->aksi][0] ?? 'fa-circle';
    }

    public function getWarnaAttribute(): string
    {
        return self::RUPA[$this->aksi][1] ?? 'abu';
    }

    /**
     * Kapan, dalam bahasa Indonesia.
     *
     * locale('id') dipaksa karena APP_LOCALE=en di .env — tanpa itu bulannya
     * tertulis "October" di layar yang seluruh tulisannya bahasa Indonesia.
     */
    public function getWaktuTerbacaAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->locale('id')->translatedFormat('j M Y, H:i')
            : '-';
    }
}
