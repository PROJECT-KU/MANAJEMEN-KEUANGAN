<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Buang kode verifikasi email yang masih tersimpan apa adanya.
 *
 * Sejak kodenya disimpan teracak, nilai lama yang polos tidak lagi bisa
 * dipakai memverifikasi apa pun — Hash::check() tidak akan pernah cocok
 * dengannya. Tetapi membiarkannya tergeletak di basis data dan di berkas
 * cadangan tidak ada gunanya sama sekali, jadi sekalian dibersihkan.
 *
 * Tidak ada yang hilang: kode verifikasi hanya berlaku dua menit, sehingga
 * semua nilai yang tersisa di sini sudah lama kedaluwarsa. Pemiliknya cukup
 * menekan "kirim ulang" untuk mendapat kode baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('code_verified_mail')
            ->update([
                'code_verified_mail' => null,
                'code_verified_mail_sent_at' => null,
            ]);
    }

    /**
     * Tidak bisa dikembalikan, dan memang tidak perlu: yang dibuang adalah
     * kode sekali pakai yang sudah kedaluwarsa.
     */
    public function down(): void
    {
        //
    }
};
