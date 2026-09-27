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
