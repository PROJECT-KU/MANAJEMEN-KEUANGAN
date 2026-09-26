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
use App\Livewire\Auth\AturUlangPassword;
use App\Livewire\Auth\Daftar;
use App\Livewire\Auth\LupaPassword;
use App\Livewire\Auth\Masuk;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Masuk::class)->name('login');
    Route::get('/K4rY4w4N', Masuk::class)->name('login.karyawan');
    Route::get('/register', Daftar::class)->name('register');

    // Alur atur ulang kata sandi wajib bisa diakses tamu. Sebelumnya rutenya
    // berada di grup middleware 'auth', sehingga orang yang lupa kata sandi
    // justru dilempar balik ke halaman masuk.
    Route::get('/lupa-password', LupaPassword::class)->name('formemail.reset');
    Route::get('/atur-ulang-password', AturUlangPassword::class)->name('password.atur-ulang');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
