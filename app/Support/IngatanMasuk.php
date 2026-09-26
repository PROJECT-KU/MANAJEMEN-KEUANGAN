<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;

/**
 * Ingatan perangkat pada halaman masuk.
 *
 * Kue ini menyimpan HANYA username/email terakhir, cara masuk pilihan
 * terakhir, dan penanda apakah "Ingat saya" dicentang. Kata sandi dan PIN
 * tidak pernah ikut disimpan.
 *
 * Penanda 'ingat' membedakan dua hal yang sering tertukar:
 *  - ingat = true  : pengguna minta username-nya diisikan otomatis.
 *  - ingat = false : username hanya disimpan supaya PIN bisa dipakai di
 *                    perangkat ini, tanpa mengisi apa pun di isian.
 * Kue Laravel sendiri sudah terenkripsi dan httpOnly, jadi isinya tidak bisa
 * dibaca skrip di peramban.
 */
class IngatanMasuk
{
    public const NAMA = 'masuk_tersimpan';

    /** @return array{identitas:string,mode:string,ingat:bool,uid:int|null}|null */
    public static function baca(): ?array
    {
        $isi = Cookie::get(self::NAMA);

        if (! is_string($isi) || $isi === '') {
            return null;
        }

        $data = json_decode($isi, true);

        if (! is_array($data) || ! isset($data['identitas']) || ! is_string($data['identitas'])) {
            return null;
        }

        $identitas = trim($data['identitas']);

        if ($identitas === '' || mb_strlen($identitas) > 150) {
            return null;
        }

        return [
            'identitas' => $identitas,
            'mode' => ($data['mode'] ?? 'sandi') === 'pin' ? 'pin' : 'sandi',
            'ingat' => (bool) ($data['ingat'] ?? false),
            'uid' => isset($data['uid']) && is_numeric($data['uid']) ? (int) $data['uid'] : null,
        ];
    }

    public static function simpan(string $identitas, string $mode = 'sandi', bool $ingat = false, ?int $uid = null): bool
    {
        $identitas = trim($identitas);

        if ($identitas === '' || mb_strlen($identitas) > 150) {
            return false;
        }

        $isi = json_encode([
            'identitas' => $identitas,
            'mode' => $mode === 'pin' ? 'pin' : 'sandi',
            'ingat' => $ingat,
            'uid' => $uid,
        ]);

        Cookie::queue(Cookie::make(
            self::NAMA,
            (string) $isi,
            (int) config('auth.ingat_identitas_menit', 43200),
            config('session.path'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site')
        ));

        return true;
    }

    public static function lupakan(): void
    {
        Cookie::queue(Cookie::forget(
            self::NAMA,
            config('session.path'),
            config('session.domain')
        ));
    }
}
