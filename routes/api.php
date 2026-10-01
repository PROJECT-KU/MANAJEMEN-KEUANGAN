<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Sengaja kosong.
|
| Endpoint /api/v1/* (masuk, daftar, saldo, debit, kredit, kategori, dan
| laporan) DIHAPUS pada 27 September 2026 karena tidak dipakai satu pun
| bagian aplikasi, sementara jalur masuknya melewati penjagaan yang berlaku
| di halaman masuk: tidak ada pembatas per akun, tidak tercatat di jejak
| masuk, dan tidak memicu pemberitahuan perangkat baru.
|
| Rute /oauth/* bawaan Passport juga dimatikan (lihat AuthServiceProvider).
| Paketnya sendiri dibiarkan terpasang supaya pencabutan token pada saat
| kata sandi diganti tetap berjalan bila suatu saat API dihidupkan lagi.
|
| Kalau nanti API dibutuhkan, hidupkan kembali dengan penjagaan yang sama
| dengan halaman masuk: pembatas per akun, pencatatan ke AktivitasMasuk,
| pemeriksaan status akun, dan pemberitahuan perangkat baru.
*/

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sharing Session — hanya baca, untuk halaman landing di subdomain
|--------------------------------------------------------------------------
|
| Halaman landing Sharing Session berdiri sendiri di subdomainnya dan isinya
| dulu diketik langsung ke berkas HTML. Dua endpoint di bawah membuatnya
| cukup mengambil data, jadi mengganti flyer, tanggal, atau pemateri cukup
| dilakukan sekali di layar Angkatan Layanan.
|
| Keduanya berbeda jenis dari endpoint yang dicabut di atas: TIDAK bersesi,
| TIDAK menulis apa pun, dan yang dikeluarkan hanya angkatan berstatus aktif
| — persis yang sudah terpampang di halaman publik rumahscopus.org. Alasan
| lengkapnya ada di ApiSharingSessionController.
|
| Dibatasi 60 permintaan per menit per alamat: halamannya halaman iklan, dan
| satu iklan yang jalan bisa membuat ratusan orang membukanya bersamaan.
*/
Route::middleware('throttle:60,1')->prefix('sharing-session')->group(function () {
    Route::get('/', 'Publict\ApiSharingSessionController@sekarang')->name('api.sharingsession.sekarang');
    Route::get('/daftar', 'Publict\ApiSharingSessionController@daftar')->name('api.sharingsession.daftar');
});
