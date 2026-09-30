<?php

use App\ClinikScopusBiayaPersesi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Mengembalikan varian Pulau Jawa / Luar Pulau Jawa pada Scopus Camp.
 *
 * Varian ini sempat dibuang 30 Sep 2026 karena datanya terbaca seolah harganya
 * ditentukan waktu, bukan pulau — Yogyakarta 4,5jt sampai September lalu 5,5jt
 * sejak Oktober. Pemiliknya membetulkan: yang membedakan bukan cuma harga,
 * melainkan FASILITASNYA, dan itu tidak kelihatan dari kolom biaya sama
 * sekali.
 *
 *   Pulau Jawa       7 fasilitas — termasuk penginapan dan mushola, kolam
 *                    renang & treadmill, karena acaranya di rumah sendiri
 *   Luar Pulau Jawa  5 fasilitas — tanpa keduanya, acaranya menyewa tempat
 *
 * Harganya kebetulan sama-sama 5,5jt sekarang; yang dulu berbeda (Yogyakarta
 * 4,5jt vs Padang 5,5jt) sudah bertemu di angka itu. Jadi varian ini menahan
 * perbedaan fasilitas lebih dulu, dan siap menahan perbedaan harga lagi kalau
 * keduanya berpisah.
 *
 * Angkatan yang sudah ada diberi varian dari `lokasi`-nya. Pemetaan kota
 * ditulis di sini sekali, bukan jadi aturan tetap di kode: kota baru dipilih
 * sendiri variannya oleh admin lewat borang.
 */
return new class extends Migration
{
    private const LUAR_JAWA = ['Padang', 'Medan'];

    private const FASILITAS_JAWA = [
        'Konsumsi selama kegiatan',
        'Penginapan ala Rumah Scopus',
        'Pendampingan handling jurnal pasca camp',
        'Sertifikat & Seminar Kit',
        'Cek plagiasi',
        'E-Materi',
        'Mushola, kolam renang & treadmill',
    ];

    private const FASILITAS_LUAR_JAWA = [
        'Konsumsi selama kegiatan',
        'Pendampingan handling jurnal pasca camp',
        'Sertifikat & Seminar Kit',
        'Cek plagiasi',
        'E-Materi',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $this->tarif();
            $this->angkatan();
        });
    }

    private function tarif(): void
    {
        $berlaku = DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'scopus_camp')
            ->where('status', ClinikScopusBiayaPersesi::AKTIF)
            ->orderByDesc('updated_at')
            ->first();

        if (! $berlaku) {
            return;
        }

        // Semua baris camp yang ada jadi milik varian Jawa: itulah riwayat
        // yang benar — 41 dari 48 angkatan Yogyakarta, sisanya Jakarta.
        DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'scopus_camp')
            ->update(['varian' => 'jawa']);

        DB::table('clinikscopus_biaya_persesi')
            ->where('id', $berlaku->id)
            ->update(['fasilitas' => json_encode(self::FASILITAS_JAWA)]);

        /*
         * Varian luar Jawa disalin dari yang berlaku, lalu fasilitasnya
         * dikurangi. Disalin, bukan dibuat dari nol, supaya cetakan deskripsi,
         * kegiatan, dan kontaknya ikut — ketiganya sama persis untuk kedua
         * varian, dan mengetiknya ulang di sini hanya menambah satu tempat
         * lagi yang bisa berselisih.
         */
        $sudahAda = DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'scopus_camp')->where('varian', 'luar_jawa')->exists();

        if ($sudahAda) {
            return;
        }

        DB::table('clinikscopus_biaya_persesi')->insert([
            'id' => (string) Str::uuid(),
            'layanan' => 'scopus_camp',
            'varian' => 'luar_jawa',
            'biaya_persesi' => $berlaku->biaya_persesi,
            'ppn' => $berlaku->ppn,
            'fasilitas' => json_encode(self::FASILITAS_LUAR_JAWA),
            'template_deskripsi' => $berlaku->template_deskripsi,
            'kegiatan' => $berlaku->kegiatan,
            'kontak' => $berlaku->kontak,
            'status' => ClinikScopusBiayaPersesi::AKTIF,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function angkatan(): void
    {
        DB::table('kategori_layanan')
            ->where('layanan', 'scopus_camp')
            ->update(['varian' => 'jawa']);

        DB::table('kategori_layanan')
            ->where('layanan', 'scopus_camp')
            ->whereIn('lokasi', self::LUAR_JAWA)
            ->update(['varian' => 'luar_jawa']);
    }

    public function down(): void
    {
        DB::transaction(function () {
            DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', 'scopus_camp')->where('varian', 'luar_jawa')->delete();

            DB::table('clinikscopus_biaya_persesi')
                ->where('layanan', 'scopus_camp')->update(['varian' => null]);

            DB::table('kategori_layanan')
                ->where('layanan', 'scopus_camp')->update(['varian' => null]);
        });
    }
};
