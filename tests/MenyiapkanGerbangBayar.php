<?php

namespace Tests;

use Illuminate\Support\Facades\Http;

/**
 * Menyiapkan gerbang pembayaran untuk uji yang MENDAFTAR lewat borang umum.
 *
 * Sejak transfer manual dicabut 17 Okt 2026, pendaftaran Webinar Eksklusif
 * hanya bisa lewat gerbang pembayaran — dan borangnya MENOLAK kalau
 * kredensialnya kosong, supaya produksi yang belum disetel tidak menerima
 * pendaftaran yang tidak bisa ditagih sama sekali.
 *
 * Akibatnya seluruh uji yang mendaftar lewat borang ikut ditolak, sebab
 * phpunit.xml sengaja mengosongkan kunci DOKU: tanpa itu, ujinya berubah
 * perilaku begitu seseorang mengisi kunci di `.env` miliknya.
 *
 * Trait ini memasang kredensial PALSU dan memalsukan jawaban gerbangnya,
 * jadi ujinya tidak pernah menyentuh jaringan. Uji yang memang menguji
 * keadaan "gerbang belum disetel" mengosongkannya lagi sendiri.
 */
trait MenyiapkanGerbangBayar
{
    protected function siapkanGerbangBayar(): void
    {
        config()->set('services.doku.client_id', 'BRN-UJI-0000');
        config()->set('services.doku.secret_key', 'SK-UJI-0000');
        config()->set('services.doku.produksi', false);

        /*
         * Dua bentuk jawaban sekaligus:
         *
         *   - pembuatan tagihan memulangkan alamat halaman bayar;
         *   - cek status memulangkan "belum ada kabar", supaya pendaftaran
         *     uji TIDAK diam-diam jadi lunas dan mengubah arti ujinya.
         */
        Http::fake([
            '*/orders/v1/status/*' => Http::response([], 404),
            '*' => Http::response(
                ['response' => ['payment' => ['url' => 'https://bayar.uji.test/x']]],
                200
            ),
        ]);
    }
}
