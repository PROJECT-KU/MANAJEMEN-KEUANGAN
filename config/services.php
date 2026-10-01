<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
     * DOKU Checkout — pembayaran Sharing Session.
     *
     * Kosong sampai kredensialnya diisi di .env. Selama kosong, borang
     * pendaftaran menawarkan transfer manual dan mengatakannya terus terang,
     * bukan melempar galat di tengah pendaftaran.
     *
     * 'produksi' sengaja HARUS dinyatakan eksplisit dan bawaannya false:
     * salah arah di sini berarti uang sungguhan masuk ke lingkungan uji coba,
     * atau sebaliknya pembayaran uji dianggap nyata.
     */
    'doku' => [
        'client_id' => env('DOKU_CLIENT_ID'),
        'secret_key' => env('DOKU_SECRET_KEY'),
        'produksi' => env('DOKU_PRODUKSI', false),
    ],

];
