<?php

/*
|--------------------------------------------------------------------------
| Rute Autentikasi
|--------------------------------------------------------------------------
| Berkas ini dimuat tanpa namespace controller (lihat RouteServiceProvider),
| sebab halaman masuk, daftar, dan atur ulang kata sandi ditangani komponen
| Livewire, bukan controller.
|
| Auth::routes() bawaan laravel/ui sengaja tidak dipakai lagi: sebagian
| rutenya menunjuk method yang tidak ada di controller aplikasi ini.
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerifikasiEmailController;
use App\Livewire\Auth\AturUlangPassword;
use App\Livewire\Auth\Daftar;
use App\Livewire\Auth\KirimUlangVerifikasi;
use App\Livewire\Auth\LupaPassword;
use App\Livewire\Auth\Masuk;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Masuk::class)->name('login');
    Route::get('/register', Daftar::class)->name('register');

    // Alur atur ulang kata sandi wajib bisa diakses tamu. Sebelumnya rutenya
    // berada di grup middleware 'auth', sehingga orang yang lupa kata sandi
    // justru dilempar balik ke halaman masuk.
    Route::get('/lupa-password', LupaPassword::class)->name('formemail.reset');
    Route::get('/atur-ulang-password/{token?}', AturUlangPassword::class)->name('password.atur-ulang');
});

// Alamat lama pintu masuk karyawan: dialihkan supaya tautan & pintasan lama
// tetap bekerja, tanpa memelihara dua halaman yang sama.
Route::redirect('/K4rY4w4N', '/login')->name('login.karyawan');

// Tautan verifikasi dari email pendaftaran (URL bertanda tangan + dibatasi).
Route::get('/verifikasi-email/{id}/{hash}', VerifikasiEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Kirim ulang tautan verifikasi. Sengaja di luar grup 'guest' supaya pengguna
// yang sudah masuk tapi belum terverifikasi juga bisa memakainya.
Route::get('/kirim-ulang-verifikasi', KirimUlangVerifikasi::class)->name('verification.resend');

// Halaman yang ditunjuk kotak centang pada formulir pendaftaran.
Route::view('/ketentuan-layanan', 'halaman.ketentuan')->name('ketentuan');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
