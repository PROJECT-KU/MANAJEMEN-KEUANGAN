<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('setActive')) {
    function setActive($route, $class = 'active')
    {
        return Request::is($route . '*') ? $class : '';
    }
}

if (!function_exists('aset_surat')) {
    /**
     * Alamat gambar untuk DI DALAM SURAT.
     *
     * Bukan asset(). asset() merakit alamatnya dari APP_URL, dan di
     * lingkungan lokal APP_URL berbunyi http://localhost — alamat yang
     * tidak bisa dijangkau siapa pun selain mesin yang menjalankannya.
     * Gmail mengambil gambar lewat proksinya sendiri di peladen Google,
     * jadi yang terbuka di sana mesin Google, dan yang tergambar di kotak
     * masuk ikon rusak.
     *
     * Terukur: seluruh gambar pada surat uji yang dikirim dari lokal rusak
     * — logo yayasannya dan ketiga ikon media sosial di kakinya.
     */
    function aset_surat(string $jalur): string
    {
        return rtrim((string) config('mail.aset_url'), '/') . '/' . ltrim($jalur, '/');
    }
}
