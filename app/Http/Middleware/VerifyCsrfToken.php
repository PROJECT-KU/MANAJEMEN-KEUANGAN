<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        /*
         * Pemberitahuan balik dari DOKU. Pengirimnya peladen DOKU, bukan
         * peramban peserta, jadi ia tidak punya — dan tidak mungkin punya —
         * token CSRF.
         *
         * Penjagaannya dipindah, BUKAN dihilangkan: isi permintaannya
         * ditandatangani HMAC-SHA256 dengan kunci rahasia, dan tanda tangan
         * itu diperiksa paling awal di PublicWebinarEksklusifController
         * sebelum satu pun status diubah.
         */
        'Webinar-Eksklusif/pemberitahuan/doku',
    ];
}
