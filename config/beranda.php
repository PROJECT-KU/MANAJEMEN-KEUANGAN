<?php

/*
 * Angka dan isi beranda yang TIDAK bisa dihitung dari basis data.
 *
 * Tiga angka besar di bagian "Angkanya" dulu ditulis langsung di dalam
 * resources/views/public/home.blade.php, jadi menggantinya berarti menyunting
 * tampilan — dan angka itulah bagian halaman yang paling sering diminta
 * berubah.
 *
 * Sengaja TIDAK dibuat hidup dari basis data. Aplikasi ini baru, yayasannya
 * tidak: yang tercatat di sini cuma 136 pendaftaran Scopus Camp, 8 webinar,
 * dan 9 Scopus Kafe. Menghitungnya dari tabel akan mengubah "5.000 orang"
 * jadi "153 orang" — bukan membuatnya jujur, melainkan menghapus sepuluh
 * tahun kerja yang memang terjadi tapi tidak lewat sistem ini. Angkanya milik
 * pemiliknya; tempat ini cuma supaya ia tidak perlu mencari-cari di markah.
 */
return [

    'angka' => [
        [
            'ikon' => 'bi-mortarboard-fill',
            'nilai' => (int) env('BERANDA_ANGKA_PESERTA', 5000),
            'ket' => 'orang sudah ikut kelas kami',
        ],
        [
            'ikon' => 'bi-send-check-fill',
            'nilai' => (int) env('BERANDA_ANGKA_NASKAH', 2315),
            'ket' => 'naskah sudah dikirim ke jurnal internasional',
        ],
        [
            'ikon' => 'bi-journal-check',
            'nilai' => (int) env('BERANDA_ANGKA_TERBIT', 1851),
            'ket' => 'artikel sudah terbit dan terindeks Scopus',
        ],
    ],

];
