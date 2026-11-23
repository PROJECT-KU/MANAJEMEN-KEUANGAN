<?php

namespace App\Support;

use App\KategoriLayanan;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Angkatan webinar yang boleh ditonton oleh satu orang.
 *
 * SATU TEMPAT, dipakai pengendali rekaman DAN penghitung di menu. Dua salinan
 * aturan "siapa boleh menonton" akan berselisih perlahan, dan selisihnya
 * berupa salah satu dari dua kegagalan yang sama-sama diam: menu menyebut
 * angka tiga sementara halamannya kosong, atau halamannya membuka rekaman
 * yang menurut menu tidak ada.
 *
 * ---------------------------------------------------------------------------
 * Kuncinya ALAMAT EMAIL, bukan user_id
 * ---------------------------------------------------------------------------
 *
 * Kolom `user_id` pada pendaftaran hanya terisi kalau orangnya kebetulan
 * sedang masuk saat mendaftar — dan hampir semua pendaftar webinar mendaftar
 * TANPA akun, sebab akunnya baru dibuatkan sesudah pembayarannya lunas.
 * Menyaring dengan user_id membuat hampir semua peserta tidak melihat apa
 * pun, dan diamnya terbaca seperti rekamannya belum dipasang.
 *
 * Alamat itu pula yang jadi alamat akunnya, sebab akunnya memang dibuat dari
 * alamat itu.
 */
class RekamanWebinarSaya
{
    /**
     * Id angkatan webinar yang sudah dibayar oleh pemilik akun ini.
     *
     * @return list<mixed>
     */
    public static function idAngkatan(?User $akun): array
    {
        $email = strtolower(trim((string) $akun?->email));

        if ($email === '') {
            return [];
        }

        // Sebagai PEMESAN.
        $sebagaiPemesan = WebinarEksklusifPendaftaran::query()
            ->where('status', 'paid')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNotNull('kategori_id')
            ->pluck('kategori_id');

        /*
         * Sebagai salah satu PESERTA ROMBONGAN.
         *
         * Rekan rombongan membayar lewat pemesannya dan tidak pernah muncul
         * di kolom email pendaftaran; tanpa bagian ini, seluruh rombongan —
         * selain pemesannya — ditolak menonton sesuatu yang sudah mereka
         * bayar.
         *
         * Tabelnya dipakai bersama semua layanan, jadi `layanan` ikut
         * disaring: tanpa itu, peserta rombongan Scopus Camp yang emailnya
         * sama ikut membuka rekaman webinar yang tidak pernah ia bayar.
         */
        $sebagaiPeserta = DB::table('pendaftaran_peserta')
            ->join('webinar_eksklusif_pendaftaran as w', 'w.id', '=', 'pendaftaran_peserta.pendaftaran_id')
            ->where('pendaftaran_peserta.layanan', 'webinar_eksklusif')
            ->whereRaw('LOWER(pendaftaran_peserta.email) = ?', [$email])
            ->where('w.status', 'paid')
            ->whereNotNull('w.kategori_id')
            ->pluck('w.kategori_id');

        return $sebagaiPemesan->merge($sebagaiPeserta)->unique()->values()->all();
    }

    /** @return Collection<int, KategoriLayanan> */
    public static function angkatan(?User $akun): Collection
    {
        $id = self::idAngkatan($akun);

        if ($id === []) {
            return collect();
        }

        return KategoriLayanan::whereIn('id', $id)
            ->where('layanan', 'webinar_eksklusif')
            ->orderByDesc('mulai')
            ->get();
    }

    public static function boleh(?User $akun, KategoriLayanan $angkatan): bool
    {
        return in_array(
            (string) $angkatan->id,
            array_map('strval', self::idAngkatan($akun)),
            true
        );
    }

    /**
     * Berapa rekaman yang SIAP DITONTON — bukan berapa webinar yang diikuti.
     *
     * Angka di menu harus sama dengan jumlah yang bisa ditekan. Menghitung
     * yang belum ada rekamannya membuat menu menjanjikan tiga tontonan lalu
     * halamannya memajang tiga baris "belum tersedia".
     */
    public static function jumlahSiap(?User $akun): int
    {
        return self::angkatan($akun)
            ->filter(fn ($a) => TautanYouTube::sah($a->rekaman_url))
            ->count();
    }
}
