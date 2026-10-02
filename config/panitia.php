<?php

/*
 * Nomor dan alamat panitia yang muncul di halaman publik.
 *
 * Sebelumnya nomor WhatsApp-nya ditulis langsung di dalam Blade halaman
 * status, jadi menggantinya berarti menyunting kode, membuat commit, dan
 * deploy ulang — untuk sesuatu yang berubah saat orangnya berganti.
 *
 * Nilai bawaannya tetap diisi, bukan kosong: tanpa .env yang terisi, tombol
 * "Kirim bukti transfer" akan menunjuk ke wa.me tanpa nomor dan orangnya
 * sampai di halaman WhatsApp yang kosong.
 */
return [

    /*
     * Format internasional tanpa tanda plus, seperti yang diminta wa.me.
     * 0889-8356-7819 (Kumala) ditulis 6288983567819.
     */
    'whatsapp' => env('PANITIA_WHATSAPP', '6288983567819'),

    'whatsapp_nama' => env('PANITIA_WHATSAPP_NAMA', 'Panitia Rumah Scopus'),

];
