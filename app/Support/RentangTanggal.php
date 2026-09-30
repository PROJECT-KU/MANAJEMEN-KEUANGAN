<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Menulis rentang tanggal seperti orang menulisnya di pengumuman.
 *
 *   sehari            15 April 2026
 *   sebulan           15 – 29 April 2026
 *   beda bulan        30 Oktober – 1 November 2026
 *   beda tahun        30 Desember 2026 – 1 Januari 2027
 *
 * Yang diulang cuma bagian yang berbeda. Menulis "15 April 2026 – 29 April
 * 2026" memang tidak salah, tetapi tidak ada yang menulis begitu, dan
 * deskripsi yang dirakit mesin harus tidak terasa dirakit mesin.
 *
 * Nama bulannya dipaksa Indonesia lewat locale Carbon karena APP_LOCALE=en di
 * .env — tanpa itu keluarannya "October", bukan "Oktober".
 */
class RentangTanggal
{
    /** Pemisahnya en dash, seperti yang dipakai di pengumuman mereka. */
    private const PISAH = ' – ';

    /**
     * @param  bool  $pendek  Nama bulan disingkat ("Okt", bukan "Oktober").
     *
     * Bentuk pendek dipakai daftar bertabel, tempat kolom tanggalnya sempit:
     * terukur "30 Oktober – 1 November 2026" patah dua baris di kolom 157px
     * dan membuat barisnya lebih tinggi daripada baris lain. Pengumuman yang
     * dirakit tetap memakai nama bulan lengkap — di kalimat, singkatan justru
     * terasa dirakit mesin.
     */
    public static function tulis(?CarbonInterface $mulai, ?CarbonInterface $selesai = null, bool $pendek = false): string
    {
        if (! $mulai) {
            return '';
        }

        $bulan = $pendek ? 'M' : 'F';
        $awal = $mulai->locale('id');

        if (! $selesai || $awal->isSameDay($selesai)) {
            return $awal->translatedFormat("j {$bulan} Y");
        }

        $akhir = $selesai->locale('id');

        // Tahun beda: tidak ada yang bisa dihemat, tulis lengkap dua-duanya.
        if ($awal->year !== $akhir->year) {
            return $awal->translatedFormat("j {$bulan} Y") . self::PISAH . $akhir->translatedFormat("j {$bulan} Y");
        }

        // Tahunnya sama, bulannya beda: tahun cukup sekali, di belakang.
        if ($awal->month !== $akhir->month) {
            return $awal->translatedFormat("j {$bulan}") . self::PISAH . $akhir->translatedFormat("j {$bulan} Y");
        }

        // Bulan dan tahun sama: tanggalnya saja yang diulang.
        return $awal->translatedFormat('j') . self::PISAH . $akhir->translatedFormat("j {$bulan} Y");
    }
}
