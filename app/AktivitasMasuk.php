<?php

namespace App;

use App\Support\PenandaPerangkat;
use Illuminate\Database\Eloquent\Model;

class AktivitasMasuk extends Model
{
    protected $table = 'aktivitas_masuk';

    /** Tabel ini hanya punya created_at. */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'identitas', 'berhasil', 'alasan', 'ip', 'peramban', 'perangkat',
    ];

    protected $casts = [
        'berhasil' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Catat satu percobaan masuk; kegagalan pencatatan tidak boleh menghambat login. */
    public static function catat(?User $pengguna, string $identitas, bool $berhasil, ?string $alasan = null): void
    {
        try {
            static::create([
                'user_id' => $pengguna?->getKey(),
                'identitas' => mb_substr($identitas, 0, 150),
                'berhasil' => $berhasil,
                'alasan' => $alasan,
                'ip' => request()->ip(),
                'peramban' => mb_substr((string) request()->userAgent(), 0, 255),
                'perangkat' => PenandaPerangkat::ambil(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
