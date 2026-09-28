<?php

namespace App;

use App\Support\PenandaPerangkat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Perangkat yang diizinkan masuk dengan PIN.
 *
 * Izinnya tinggal di peladen, bukan hanya di kue peramban. Bedanya terasa
 * saat sebuah perangkat hilang: barisnya bisa dihapus dari perangkat lain,
 * dan perangkat yang hilang itu langsung kehilangan izinnya walau kuenya
 * masih utuh di sana.
 */
class PerangkatPin extends Model
{
    protected $table = 'perangkat_pin';

    protected $fillable = ['user_id', 'penanda', 'peramban', 'ip', 'terakhir_dipakai_pada'];

    protected $casts = [
        'terakhir_dipakai_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Daftarkan perangkat yang sedang dipakai, atau perbarui catatannya. */
    public static function daftarkanPerangkatIni(User $pengguna): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $pengguna->getKey(),
                'penanda' => PenandaPerangkat::ambil(),
            ],
            [
                'peramban' => mb_substr((string) request()->userAgent(), 0, 255),
                'ip' => mb_substr((string) request()->ip(), 0, 45),
                'terakhir_dipakai_pada' => Carbon::now(),
            ]
        );
    }

    /** Apakah perangkat yang sedang dipakai boleh masuk dengan PIN? */
    public static function perangkatIniTerdaftar(User $pengguna): bool
    {
        return static::where('user_id', $pengguna->getKey())
            ->where('penanda', PenandaPerangkat::ambil())
            ->exists();
    }

    /** Cabut izin perangkat yang sedang dipakai. */
    public static function lupakanPerangkatIni(User $pengguna): void
    {
        static::where('user_id', $pengguna->getKey())
            ->where('penanda', PenandaPerangkat::ambil())
            ->delete();
    }

    /** Cabut izin semua perangkat; dipakai saat PIN dimatikan. */
    public static function lupakanSemua(User $pengguna): void
    {
        static::where('user_id', $pengguna->getKey())->delete();
    }

    /** Catat bahwa perangkat ini baru saja dipakai masuk. */
    public static function tandaiDipakai(User $pengguna): void
    {
        static::where('user_id', $pengguna->getKey())
            ->where('penanda', PenandaPerangkat::ambil())
            ->update(['terakhir_dipakai_pada' => Carbon::now()]);
    }

    /** Ini perangkat yang sedang dipakai membuka halaman? */
    public function getIniAttribute(): bool
    {
        return $this->penanda === PenandaPerangkat::ambil();
    }
}
