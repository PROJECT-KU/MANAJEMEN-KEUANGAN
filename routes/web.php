<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'Auth\LoginController@showLoginForm');

Auth::routes();

/**
 * account
 */
Route::prefix('account')->group(function () {

    //dashboard account
    Route::get('/dashboard', 'account\DashboardController@index')->name('account.dashboard.index');
    // Delete pengguna
    Route::get('/pengguna', 'account\PenggunaController@index')->name('account.pengguna.index');
    Route::get('/pengguna/create', 'account\PenggunaController@create')->name('account.pengguna.create');
    Route::post('/pengguna', 'account\PenggunaController@store')->name('account.pengguna.store');
    Route::get('/pengguna/{id}/edit', 'account\PenggunaController@edit')->name('account.pengguna.edit');
    Route::get('/pengguna/{id}/detail', 'account\PenggunaController@detail')->name('account.pengguna.detail');
    Route::put('/pengguna/{id}',
        'account\PenggunaController@update'
    )->name('account.pengguna.update');
    Route::delete('/pengguna/{id}', 'account\PenggunaController@destroy')->name('account.pengguna.destroy');
    // routes/web.php

    //download excel
    //Route::get('/account/laporan-semua/download-excel', 'account\LaporanSemuaController@downloadExcel')->name('account.laporan-semua.download-excel');
    //Route::get('/account/laporan-semua/export-users-to-excel', 'account\LaporanSemuaController@exportUsersToExcel')->name('account.laporan-semua.export-users-to-excel');

    // download pdf
    Route::get('account/laporan_semua/download-pdf', 'account\LaporanSemuaController@downloadPdf')->name('account.laporan_semua.download-pdf');
    Route::get('/account/laporan-credit/download-pdf', 'account\LaporanCreditController@downloadPdf')->name('account.laporan_credit.download-pdf');
    Route::get('/account/laporan-debit/download-pdf', 'account\LaporanDebitController@downloadPdf')->name('account.laporan_debit.download-pdf');

    //penyewaan
    Route::get('account/penyewaan/search', 'account\PenyewaanController@search')->name('account.penyewaan.search');
    Route::delete('account/penyewaan/{id}', 'PenyewaanController@destroy')->name('account.penyewaan.destroy');
    Route::get('/account/penyewaan/create', 'account\PenyewaanController@create')->name('account.penyewaan.create');
    Route::post('/account/penyewaan/store', 'account\PenyewaanController@store')->name('account.penyewaan.store');
    Route::get('account/penyewaan/{id}/edit', 'account\PenyewaanController@edit')->name('account.penyewaan.edit');
    Route::put('account/penyewaan/{id}', 'account\PenyewaanController@update')->name('account.penyewaan.update');
    Route::Resource('/penyewaan', 'account\PenyewaanController', ['as' => 'account']);
    Route::get('penyewaan/{id}/detail', 'account\PenyewaanController@detail')->name('account.penyewaan.detail');


    //tambah barang
    Route::get('/tambah_barang/search', 'account\TambahBarangController@search')->name('account.tambah_barang.search');
    Route::Resource('/tambah_barang', 'account\TambahBarangController', ['as' => 'account']);
    Route::delete('account/tambah_barang/{id}', 'TambahBarangController@destroy')->name('account.tambah_barang.destroy');

    //categories debit
    Route::get('/categories_debit/search', 'account\CategoriesDebitController@search')->name('account.categories_debit.search');
    Route::Resource('/categories_debit', 'account\CategoriesDebitController',['as' => 'account']);
    Route::delete('account/categories_debit/{id}', 'CategoriesDebitController@destroy')->name('account.categories_debit.destroy');

    //debit
    Route::get('/debit/search', 'account\DebitController@search')->name('account.debit.search');
    Route::Resource('/debit', 'account\DebitController',['as' => 'account']);
    //categories credit
    Route::get('/categories_credit/search', 'account\CategoriesCreditController@search')->name('account.categories_credit.search');
    Route::Resource('/categories_credit', 'account\CategoriesCreditController',['as' => 'account']);
    //credit
    Route::get('/credit/search', 'account\CreditController@search')->name('account.credit.search');
    Route::Resource('/credit', 'account\CreditController',['as' => 'account']);
    //laporan debit
    Route::get('/laporan_debit', 'account\LaporanDebitController@index')->name('account.laporan_debit.index');
    Route::get('/laporan_debit/check', 'account\LaporanDebitController@check')->name('account.laporan_debit.check');
    //laporan credit
    Route::get('/laporan_credit', 'account\LaporanCreditController@index')->name('account.laporan_credit.index');
    Route::get('/laporan_credit/check', 'account\LaporanCreditController@check')->name('account.laporan_credit.check');

    Route::get('/laporan_semua/search', 'account\LaporanSemuaController@search')->name('account.laporan_semua.search');
    Route::Resource('/laporan_semua', 'account\LaporanSemuaController', ['as' => 'account']);

});
