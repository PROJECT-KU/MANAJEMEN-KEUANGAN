<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Penanda acak per peramban, disimpan pada kue terenkripsi berumur panjang.
 *
 * Bukan alat pengenal pribadi: isinya angka acak tanpa arti, dipakai semata
 * untuk membedakan "peramban yang pernah dipakai masuk" dari "peramban baru",
 * supaya pemilik akun bisa diberi tahu saat akunnya dibuka dari tempat baru.
 */
class PenandaPerangkat
{
    public const NAMA = 'penanda_perangkat';

    /** Umur kue (menit): dua tahun. */
    private const UMUR = 1051200;

    /** Penanda perangkat saat ini; dibuatkan baru bila belum ada. */
    public static function ambil(): string
    {
        $penanda = Cookie::get(self::NAMA);

        if (is_string($penanda) && preg_match('/^[A-Za-z0-9]{32}$/', $penanda)) {
            return $penanda;
        }

        // Kue yang sudah diantre pada permintaan ini dipakai ulang, supaya satu
        // permintaan tidak membuat dua penanda berbeda.
        $antre = Cookie::queued(self::NAMA);

        if ($antre && is_string($antre->getValue()) && $antre->getValue() !== '') {
            return $antre->getValue();
        }

        $penanda = Str::random(32);

        Cookie::queue(Cookie::make(
            self::NAMA,
            $penanda,
            self::UMUR,
            config('session.path'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site')
        ));

        return $penanda;
    }
}
