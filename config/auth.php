<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Sejak kapan verifikasi email diwajibkan
    |--------------------------------------------------------------------------
    | Akun yang dibuat sebelum tanggal ini tetap bisa masuk walau emailnya
    | belum terverifikasi, supaya pengguna lama tidak terkunci serentak.
    */

    'verifikasi_wajib_sejak' => env('VERIFIKASI_WAJIB_SEJAK', '2026-09-26'),

    /*
    |--------------------------------------------------------------------------
    | Masa simpan jejak aktivitas masuk (hari)
    |--------------------------------------------------------------------------
    */

    'simpan_aktivitas_masuk_hari' => env('SIMPAN_AKTIVITAS_MASUK_HARI', 90),

    /*
    |--------------------------------------------------------------------------
    | Masa berlaku "ingat saya" (menit)
    |--------------------------------------------------------------------------
    | Bawaan Laravel lima tahun; di sini dipersingkat menjadi 30 hari.
    */

    'ingat_saya_menit' => env('INGAT_SAYA_MENIT', 43200),

    /*
    |--------------------------------------------------------------------------
    | Masa simpan identitas pada peramban (menit)
    |--------------------------------------------------------------------------
    | Saat "Ingat saya" dicentang, username/email terakhir disimpan di kue
    | (cookie) terenkripsi supaya tidak perlu diketik ulang. Kata sandi dan
    | PIN TIDAK pernah ikut disimpan.
    */

    'ingat_identitas_menit' => env('INGAT_IDENTITAS_MENIT', 43200),

    /*
    |--------------------------------------------------------------------------
    | PIN masuk
    |--------------------------------------------------------------------------
    | Jalan pintas enam angka yang harus diaktifkan sendiri dari profil.
    | Karena ruang tebakannya kecil, PIN dimatikan otomatis setelah beberapa
    | kali salah dan pemiliknya diberi tahu lewat email.
    */

    'pin' => [
        'panjang' => (int) env('PIN_PANJANG', 6),
        'batas_gagal' => (int) env('PIN_BATAS_GAGAL', 5),
        'kunci_detik' => (int) env('PIN_KUNCI_DETIK', 900),
    ],

    'password_timeout' => 10800,

];
