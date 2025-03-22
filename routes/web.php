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

use App\Http\Middleware\CheckTestimoniToken;

Route::get('/K4rY4w4N', 'Auth\LoginController@showLoginForm');

Route::get('/page-maintenance', 'account\MaintenanceController@page')->name('account.page-maintenance.blank');

// HOME PUBLIC
Route::get('/', 'Publict\PublicHomeController@home')->name('home');

// ARTIKEL PUBLIC
Route::get('/blog', 'Publict\PublicArticleController@public')->name('blog.artikel.blog');
Route::get('/blog/topic/{categories_artikel_id}{token}', 'Publict\PublicArticleController@publickategori')->name('blog.topic.kategori');
Route::get('/blog/topic/blog-single/{id}{token}', 'Publict\PublicArticleController@blogsingle')->name('blog.topic.blog-single');
Route::post('/blog/store', 'Publict\PublicArticleController@storekomentar')->name('blog.store.komentar');
Route::get('/contact', 'Publict\PublicArticleController@contact')->name('blog.contact.kontak');

// PLAGIASI
Route::get('/Cek-Plagiasi', 'Publict\PublicPlagiasiController@index')->name('cek.plagiasi.public');
Route::post('/Cek-Plagiasi/proses', 'Publict\PublicPlagiasiController@uploadFile')->name('cek.plagiasi.proses');

// SCOPUS KAFE PUBLIC
Route::get('/Scopus-Kafe', 'Publict\PublicScopusKafeController@public')->name('public.scopuskafe.index');
Route::get('/Scopus-Kafe/Form-Pendaftaran', 'Publict\PublicScopusKafeController@FormPendaftaran')->name('public.scopuskafe.formpendaftaran');
Route::get('/Scopus-Kafe/create', 'Publict\PublicPendaftaranScopusKafeController@create')->name('public.pendaftaranscopuskafe.create');
Route::post('/Scopus-Kafe/store', 'Publict\PublicPendaftaranScopusKafeController@store')->name('public.pendaftaranscopuskafe.store');

// PAPERISASI
Route::get('/paperisasi/public/data', 'Publict\PublicPaperisasiController@public')->name('public.papaperisasi.data');
Route::get('/paperisasi/public/data/search', 'Publict\PublicPaperisasiController@publicsearch')->name('public.paperisasi.search');

// refrensi paper
Route::get('/Refrensi-Paper', 'Publict\PublicRefrensiPaperController@PublicRefrensiPaper')->name('public.refrensi-paper.PublicRefrensiPaper');
Route::get('/Refrensi-Paper/selengkapnya/{id}', 'Publict\PublicRefrensiPaperController@Selengkapnya')->name('public.refrensi-paper.Selengkapnya');
Route::get('/Refrensi-Paper/Search', 'Publict\PublicRefrensiPaperController@searchpublic')->name('public.refrensi-paper.SearchPublic');

Auth::routes();

/**
 * account
 */
Route::prefix('account')->group(
    function () {
        // karir
        Route::get('/karir', 'account\KarirController@index')->name('karir.index');
        Route::get('/karir/list', 'account\KarirController@list')->name('karir.list');
        Route::get('/karir/detail/{id}{token}', 'account\KarirController@detail')->name('karir.detail');
        Route::post('/karir/terkirim', 'account\KarirController@store')->name('karir.store');
        Route::get('/karir/edit/{id}{token}', 'account\KarirController@edit')->name('karir.edit');
        Route::post('/karir/update/{id}', 'account\KarirController@update')->name('karir.update');
        Route::get('/karir/search', 'account\KarirController@search')->name('karir.search');
        Route::delete('/karir/{id}', 'account\KarirController@destroy')->name('account.karir.destroy');

        //reset password
        Route::get('formemail/reset', 'Auth\ResetPasswordController@showResetForm')->name('formemail.reset');
        Route::get('newpassword/reset', 'Auth\ResetPasswordController@formpassword')->name('newpassword.reset');
        Route::post('cekemail/reset', 'Auth\ResetPasswordController@resetPassword')->name('cekemail.reset');

        //dashboard account
        Route::get('/dashboard', 'account\DashboardController@index')->name('account.dashboard.index');

        // pengguna
        Route::get('/pengguna', 'account\PenggunaController@index')->name('account.pengguna.index');
        Route::get('/pengguna/create', 'account\PenggunaController@create')->name('account.pengguna.create');
        Route::post('/pengguna', 'account\PenggunaController@store')->name('account.pengguna.store');
        Route::get('/pengguna/{id}/edit', 'account\PenggunaController@edit')->name('account.pengguna.edit');
        Route::post('/pengguna/update/foto/{id}', 'account\PenggunaController@updatePhoto')->name('account.pengguna.update.updatePhoto');
        Route::post('/pengguna/update/data-diri/{id}', 'account\PenggunaController@updatediri')->name('account.pengguna.update.datadiri');
        Route::post('/pengguna/update/data-diri-pengguna/{id}', 'account\PenggunaController@update')->name('account.pengguna.update');
        Route::post('/pengguna/update/verifikasi-email/{id}', 'account\PenggunaController@verifyEmail')->name('account.pengguna.update.vertifikasiemail');
        Route::get('/pengguna/{id}/detail', 'account\PenggunaController@detail')->name('account.pengguna.detail');
        Route::delete('/pengguna/{id}', 'account\PenggunaController@destroy')->name('account.pengguna.destroy');
        Route::get('/pengguna/search', 'account\PenggunaController@search')->name('account.pengguna.search');


        // routes/web.php

        //download excel
        //Route::get('/account/laporan-semua/download-excel', 'account\LaporanSemuaController@downloadExcel')->name('account.laporan-semua.download-excel');
        //Route::get('/account/laporan-semua/export-users-to-excel', 'account\LaporanSemuaController@exportUsersToExcel')->name('account.laporan-semua.export-users-to-excel');

        //profil
        Route::get('/profil/{id}/show', 'account\ProfilController@show')->name('account.profil.show');
        Route::post('/profil/update-bank', 'account\ProfilController@update')->name('account.profil.update');
        Route::post('/profil/update/foto/{id}', 'account\ProfilController@updatePhoto')->name('account.profil.updatePhoto');
        Route::get('/profil/{id}/password', 'account\PenggunaController@password')->name('account.profil.password');
        Route::post('/profil/{id}/resetpassword', 'account\PenggunaController@resetPassword')->name('account.profil.resetpassword');
        Route::post('/profil/verify-email', 'account\ProfilController@verifyEmail')->name('account.profil.verify.email');
        Route::post('/profil/verify-code', 'account\ProfilController@verify')->name('account.profil.verify.code');
        Route::post('/profil/update-diri', 'account\ProfilController@updatediri')->name('account.profil.update.datadiri');
        Route::post('/profil/reset-password', 'account\ProfilController@resetPassword')->name('account.profil.reset.password');

        // download pdf
        Route::get('account/laporan_semua/download-pdf', 'account\LaporanSemuaController@downloadPdf')->name('account.laporan_semua.download-pdf');
        Route::get('/account/laporan-credit/download-pdf', 'account\LaporanCreditController@downloadPdf')->name('account.laporan_credit.download-pdf');
        Route::get('/account/laporan-debit/download-pdf', 'account\LaporanDebitController@downloadPdf')->name('account.laporan_debit.download-pdf');
        Route::get('account/laporan_neraca/download-pdf', 'account\NeracaController@downloadPdf')->name('account.laporan_neraca.download-pdf');

        //penyewaan
        Route::get('penyewaan/search', 'account\PenyewaanController@search')->name('account.penyewaan.search');
        Route::delete('account/penyewaan/{id}', 'PenyewaanController@destroy')->name('account.penyewaan.destroy');
        Route::get('/account/penyewaan/create', 'account\PenyewaanController@create')->name('account.penyewaan.create');
        Route::post('/account/penyewaan/store', 'account\PenyewaanController@store')->name('account.penyewaan.store');
        Route::get('account/penyewaan/{id}/edit', 'account\PenyewaanController@edit')->name('account.penyewaan.edit');
        Route::put('account/penyewaan/{id}', 'account\PenyewaanController@update')->name('account.penyewaan.update');
        Route::Resource('/penyewaan', 'account\PenyewaanController', ['as' => 'account']);
        Route::get('penyewaan/{id}/detail', 'account\PenyewaanController@detail')->name('account.penyewaan.detail');
        Route::get('/account/laporan_penyewaan/download-pdf', 'account\PenyewaanController@downloadPdf')->name('account.laporan_penyewaan.download-pdf');
        Route::get('/penyewaan/pdf/{id}', 'account\PenyewaanController@detailPDF')->name('pdf.show');

        //tambah barang
        Route::get('/tambah_barang/search', 'account\TambahBarangController@search')->name('account.tambah_barang.search');
        Route::Resource('/tambah_barang', 'account\TambahBarangController', ['as' => 'account']);
        Route::delete('account/tambah_barang/{id}', 'TambahBarangController@destroy')->name('account.tambah_barang.destroy');

        //categories debit
        Route::get('/categories_debit/search', 'account\CategoriesDebitController@search')->name('account.categories_debit.search');
        Route::Resource('/categories_debit', 'account\CategoriesDebitController', ['as' => 'account']);
        Route::delete('account/categories_debit/{id}', 'CategoriesDebitController@destroy')->name('account.categories_debit.destroy');

        //debit
        Route::get('/debit/search', 'account\DebitController@search')->name('account.debit.search');
        Route::Resource('/debit', 'account\DebitController', ['as' => 'account']);

        //categories credit
        Route::get('/categories_credit/search', 'account\CategoriesCreditController@search')->name('account.categories_credit.search');
        Route::Resource('/categories_credit', 'account\CategoriesCreditController', ['as' => 'account']);
        Route::delete('account/categories_credit/{id}', 'CategoriesCreditController@destroy')->name('account.categories_credit.destroy');

        //credit
        Route::get('/credit/search', 'account\CreditController@search')->name('account.credit.search');
        Route::Resource('/credit', 'account\CreditController', ['as' => 'account']);

        //laporan debit
        Route::get('/laporan_debit', 'account\LaporanDebitController@index')->name('account.laporan_debit.index');
        Route::get('/laporan_debit/check', 'account\LaporanDebitController@check')->name('account.laporan_debit.check');

        //laporan credit
        Route::get('/laporan_credit', 'account\LaporanCreditController@index')->name('account.laporan_credit.index');
        Route::get('/laporan_credit/check', 'account\LaporanCreditController@check')->name('account.laporan_credit.check');

        //laporan semua
        Route::get('/laporan_semua/search', 'account\LaporanSemuaController@search')->name('account.laporan_semua.search');
        Route::Resource('/laporan_semua', 'account\LaporanSemuaController', ['as' => 'account']);
        Route::get('/account/laporan-semua/filter', [LaporanSemuaController::class, 'filterByDate'])->name('laporan_semua.filter');

        //laporan neraca
        Route::get('/neraca/search', 'account\NeracaController@search')->name('account.neraca.search');
        Route::Resource('/neraca', 'account\NeracaController', ['as' => 'account']);

        //gaji
        Route::get('/gaji', 'account\GajiController@index')->name('account.gaji.index');
        Route::get('/gaji/create', 'account\GajiController@create')->name('account.gaji.create');
        Route::post('/gaji/store', 'account\GajiController@store')->name('account.gaji.store');
        Route::delete('/gaji/{id}', 'account\GajiController@destroy')->name('account.gaji.destroy');
        Route::get('/gaji/edit/{id}{token}', 'account\GajiController@edit')->name('account.gaji.edit');
        Route::get('/gaji/detail/{id}{token}', 'account\GajiController@detail')->name('account.gaji.detail');
        Route::post('account/gaji/{id}', 'account\GajiController@update')->name('account.gaji.update');
        Route::get('/gaji/searchmanager', 'account\GajiController@searchmanager')->name('account.gaji.searchmanager');
        Route::get('/gaji/searchkaryawan', 'account\GajiController@searchkaryawan')->name('account.gaji.searchkaryawan');
        Route::get('/gaji/filtermanager', 'account\GajiController@filtermanager')->name('account.gaji.filtermanager');
        Route::get('/gaji/filterkaryawan', 'account\GajiController@filterkaryawan')->name('account.gaji.filterkaryawan');
        Route::get('/laporan_gaji/download-pdf', 'account\GajiController@downloadPdf')->name('account.laporan_gaji.download-pdf');
        Route::get('/laporan_gaji/download-excel', 'account\GajiController@downloadExcel')->name('account.laporan_gaji.download-excel');
        Route::get('/laporan_gaji/{id}/Slip-Gaji', 'account\GajiController@SlipGaji')->name('account.laporan_gaji.Slip-Gaji');

        //presensi
        Route::get('/presensi', 'account\PresensiController@index')->name('account.presensi.index');
        Route::get('/presensi/create', 'account\PresensiController@create')->name('account.presensi.create');
        Route::post('/account/presensi/store', 'account\PresensiController@store')->name('account.presensi.store');
        Route::get('/presensi/detail/{id}', 'account\PresensiController@detail')->name('account.presensi.detail');
        Route::get('/presensi/edit/{id}', 'account\PresensiController@edit')->name('account.presensi.edit');
        Route::post('account/presensi/{id}', 'account\PresensiController@update')->name('account.presensi.update');
        Route::delete('/presensi/{id}', 'account\PresensiController@destroy')->name('account.presensi.destroy');
        Route::get('/presensi/search', 'account\PresensiController@search')->name('account.presensi.search');
        Route::get('/presensi/filter', 'account\PresensiController@filter')->name('account.presensi.filter');
        Route::get('/laporan_presensi/download-pdf', 'account\PresensiController@downloadPdf')->name('account.laporan_presensi.download-pdf');


        //email
        Route::get('/email', 'account\EmailController@index')->name('account.email.index');

        // company
        Route::get('/company/{id}/edit', 'account\PenggunaController@company')->name('account.company.edit');
        Route::put('/company/{id}', 'account\PenggunaController@updateCompany')->name('account.company.update');

        // notifikasi
        Route::get('/notifikasi', 'account\NotifikasiController@showNotifications')->name('account.notifikasi.index');

        // maintenance
        Route::get('/maintenance', 'account\MaintenanceController@index')->name('account.maintenance.index');
        Route::get('/maintenance/create', 'account\MaintenanceController@create')->name('account.maintenance.create');
        Route::post('/maintenance', 'account\MaintenanceController@store')->name('account.maintenance.store');
        Route::get('/maintenance/{id}/edit', 'account\MaintenanceController@edit')->name('account.maintenance.edit');
        Route::post('/maintenance/{id}', 'account\MaintenanceController@update')->name('account.maintenance.update');
        Route::get('/maintenance/blank', 'account\MaintenanceController@maintenance')->name('account.maintenance.blank');
        Route::delete('/maintenance/{id}', 'account\MaintenanceController@destroy')->name('account.maintenance.destroy');

        // sewa
        Route::get('/sewa', 'account\SewaController@index')->name('account.sewa.index');
        Route::get('/sewa/create', 'account\SewaController@create')->name('account.sewa.create');
        Route::post('/sewa', 'account\SewaController@store')->name('account.sewa.store');
        Route::get('/sewa/{id}/edit', 'account\SewaController@edit')->name('account.sewa.edit');
        Route::put('/sewa/{id}', 'account\SewaController@update')->name('account.sewa.update');

        Route::get('/get-user-phone/{userId}', 'account\PresensiController@getUserPhone')->name('account.getUserPhone');

        // laporan camp
        Route::get('/camp', 'account\CampController@index')->name('account.camp.index');
        Route::get('/camp/create', 'account\CampController@create')->name('account.camp.create');
        Route::post('/camp/store', 'account\CampController@store')->name('account.camp.store');
        Route::get('/camp/search', 'account\CampController@search')->name('account.camp.search');
        Route::get('/camp/filter', 'account\CampController@filter')->name('account.camp.filter');
        Route::get('/camp/detail/{id}{token}', 'account\CampController@detail')->name('account.camp.detail');
        Route::delete('/camp/{id}', 'account\CampController@destroy')->name('account.camp.destroy');
        Route::get('/camp/edit/{id}{token}', 'account\CampController@edit')->name('account.camp.edit');
        Route::post('/camp/{id}', 'account\CampController@update')->name('account.camp.update');
        Route::get('/laporan_camp/download-pdf', 'account\CampController@downloadPdf')->name('account.laporan_camp.download-pdf');
        Route::get('/laporan_camp/download-excel', 'account\CampController@downloadExcel')->name('account.laporan_camp.download-excel');
        Route::get('/laporan_camp/{id}/Slip-Camp', 'account\CampController@SlipCamp')->name('account.laporan_Camp.Slip-Camp');

        // Laporan peserta
        Route::get('/Laporan-Peserta/list', 'account\PesertaController@list')->name('account.peserta.list');
        Route::get('/Laporan-Peserta/detail/{id}{token}', 'account\PesertaController@detail')->name('account.peserta.detail');
        Route::delete('/Laporan-Peserta/{id}', 'account\PesertaController@destroy')->name('account.peserta.destroy');
        Route::get('/Laporan-Peserta/search', 'account\PesertaController@search')->name('account.peserta.search');
        Route::get('/Laporan-Peserta/filter', 'account\PesertaController@filter')->name('account.peserta.filter');
        Route::get('/Laporan-Peserta', 'account\PesertaController@index')->name('account.peserta.form');
        // Route::get('/Laporan-Peserta/testimoni/{id}/{token}', 'account\PesertaController@testimoni')->name('account.peserta.testimoni')->middleware('checkToken');
        Route::get('/Laporan-Peserta/testimoni/{id}{token}', 'account\PesertaController@testimoni')->name('account.peserta.testimoni');
        Route::post('/Laporan-Peserta/simpan', 'account\PesertaController@store')->name('account.peserta.store');
        Route::post('/Laporan-Peserta/selesai/{id}', 'account\PesertaController@update')->name('account.peserta.update');

        // Pendaftaran Scopus Camp
        Route::get('/Scopus-Camp', 'account\ScopusCampController@form')->name('account.scopuscamp.form');
        Route::post('/Scopus-Camp/store', 'account\ScopusCampController@store')->name('account.scopuscamp.store');
        Route::get('/Scopus-Camp/Data-Pendaftaran', 'account\ScopusCampController@index')->name('account.scopuscamp.index');
        Route::get('/Scopus-Camp/Edit/{id}{token}', 'account\ScopusCampController@edit')->name('account.scopuscamp.edit');
        Route::post('/Scopus-Camp/update/{id}', 'account\ScopusCampController@update')->name('account.scopuscamp.update');
        Route::delete('/Scopus-Camp/delete/{id}', 'account\ScopusCampController@destroy')->name('account.scopuscamp.delete');


        // Kategori Pendaftaran Scopus Camp
        Route::get('/kategori/ScopusCamp', 'account\CategoriesScopusCampController@index')->name('account.kategori.index');
        Route::get('/kategori/ScopusCamp/create', 'account\CategoriesScopusCampController@create')->name('account.kategori.create');
        Route::post('/kategori/ScopusCamp/store', 'account\CategoriesScopusCampController@store')->name('account.kategori.store');
        Route::get('/kategori/ScopusCamp/edit/{id}/{token}', 'account\CategoriesScopusCampController@edit')->name('account.kategori.edit');
        Route::post('/kategori/ScopusCamp/update/{id}', 'account\CategoriesScopusCampController@update')->name('account.kategori.update');
        Route::delete('/kategori/ScopusCamp/delete/{id}', 'account\CategoriesScopusCampController@destroy')->name('account.kategori.destroy');
        Route::get('/kategori/ScopusCamp/search', 'account\CategoriesScopusCampController@search')->name('account.ketegori.search');
        Route::get('/kategori/ScopusCamp/filter', 'account\CategoriesScopusCampController@filter')->name('account.ketegori.filter');

        // kategori artikel
        Route::get('/artikel-kategori', 'account\CategoriesArtikelController@index')->name('account.Kategori-Artikel.index');
        Route::get('/artikel-kategori/create', 'account\CategoriesArtikelController@create')->name('account.Kategori-Artikel.create');
        Route::post('/artikel-kategori/store', 'account\CategoriesArtikelController@store')->name('account.Kategori-Artikel.store');
        Route::get('/artikel-kategori/edit/{id}{token}', 'account\CategoriesArtikelController@edit')->name('account.Kategori-Artikel.edit');
        Route::post('/artikel-kategori/update/{id}', 'account\CategoriesArtikelController@update')->name('account.Kategori-Artikel.update');
        Route::delete('/artikel-kategori/delete/{id}', 'account\CategoriesArtikelController@destroy')->name('account.Kategori-Artikel.destroy');
        Route::get('/artikel-kategori/search', 'account\CategoriesArtikelController@search')->name('account.Kategori-Artikel.search');
        Route::get('/artikel-kategori/filter', 'account\CategoriesArtikelController@filter')->name('account.Kategori-Artikel.filter');

        // ARRIKEL ADMIN
        Route::get('/article', 'account\ArtikelController@index')->name('account.Artikel.index');
        Route::get('/article/create', 'account\ArtikelController@create')->name('account.Artikel.create');
        Route::post('/article/store', 'account\ArtikelController@store')->name('account.Artikel.store');
        Route::get('/article/edit/{id}{token}', 'account\ArtikelController@edit')->name('account.Artikel.edit');
        Route::put('/article/update/{id}', 'account\ArtikelController@update')->name('account.Artikel.update');
        Route::post('/article/upload/', 'account\ArtikelController@upload')->name('account.Artikel.upload');
        Route::delete('/article/delete/{id}', 'account\ArtikelController@destroy')->name('account.Artikel.destroy');
        Route::get('/article/search', 'account\ArtikelController@search')->name('account.Artikel.search');
        Route::get('/article/filter', 'account\ArtikelController@filter')->name('account.Artikel.filter');

        // more
        Route::get('/more', 'account\MoreController@index')->name('account.more.index');

        // perjalanan dinas
        Route::get('/Perjalanan-Dinas', 'account\PerjalananDinasController@index')->name('account.PerjalananDinas.index');
        Route::get('/Perjalanan-Dinas/create', 'account\PerjalananDinasController@create')->name('account.PerjalananDinas.create');
        Route::get('/Perjalanan-Dinas/addcreate/{id}', 'account\PerjalananDinasController@addcreate')->name('account.PerjalananDinas.addcreate');
        Route::post('/Perjalanan-Dinas/store', 'account\PerjalananDinasController@store')->name('account.PerjalananDinas.store');
        Route::post('/Perjalanan-Dinas/addstore/{id}', 'account\PerjalananDinasController@addstore')->name('account.PerjalananDinas.addstore');
        Route::get('/Perjalanan-Dinas/search', 'account\PerjalananDinasController@search')->name('account.PerjalananDinas.search');
        Route::get('/Perjalanan-Dinas/Detail-Ajukan/{id}', 'account\PerjalananDinasController@DetailAjukan')->name('account.PerjalananDinas.DetailAjukan');
        Route::get('/Perjalanan-Dinas/Detail-Diterima/{id}', 'account\PerjalananDinasController@DetailDiterima')->name('account.PerjalananDinas.DetailDiterima');
        Route::get('/Perjalanan-Dinas/Detail-Ditolak/{id}', 'account\PerjalananDinasController@DetailDitolak')->name('account.PerjalananDinas.DetailDitolak');
        Route::get('/Perjalanan-Dinas/Edit/{id}', 'account\PerjalananDinasController@Edit')->name('account.PerjalananDinas.Edit');
        Route::get('/Perjalanan-Dinas/AddEdit/{id}', 'account\PerjalananDinasController@AddEdit')->name('account.PerjalananDinas.AddEdit');
        Route::post('/Perjalanan-Dinas/Update-Edit/{id}', 'account\PerjalananDinasController@UpdateEdit')->name('account.PerjalananDinas.UpdateEdit');
        Route::post('/Perjalanan-Dinas/Update-Manager/{id}', 'account\PerjalananDinasController@PengajuanManager')->name('account.PerjalananDinas.PengajuanManager');
        Route::post('/Perjalanan-Dinas/Update-AddEdit/{id}', 'account\PerjalananDinasController@UpdateAddEdit')->name('account.PerjalananDinas.UpdateAddEdit');
        Route::delete('/Perjalanan-Dinas/delete/{id}', 'account\PerjalananDinasController@destroy')->name('account.PerjalananDinas.destroy');

        // meme
        Route::get('/meme/data', 'account\DataMemeController@index')->name('account.meme.index');
        Route::get('/meme/create-data', 'account\DataMemeController@create')->name('account.meme.create');
        Route::post('/meme/store-data', 'account\DataMemeController@store')->name('account.meme.store');
        Route::get('/meme/edit-data/{id}', 'account\DataMemeController@edit')->name('account.meme.edit');
        Route::post('/meme/update-data/{id}', 'account\DataMemeController@update')->name('account.meme.update');
        Route::delete('/meme/delete/{id}', 'account\DataMemeController@destroy')->name('account.meme.delete');

        // paperisasi
        Route::get('/paperisasi/data', 'account\PaperisasiController@index')->name('account.paperisasi.index');
        Route::get('/paperisasi/data/search', 'account\PaperisasiController@search')->name('account.paperisasi.search');
        Route::get('/paperisasi/data/filter', 'account\PaperisasiController@filter')->name('account.paperisasi.filter');
        Route::get('/paperisasi/data/create', 'account\PaperisasiController@create')->name('account.paperisasi.create');
        Route::post('/paperisasi/data/store', 'account\PaperisasiController@store')->name('account.paperisasi.store');
        Route::get('/paperisasi/data/edit/{id}', 'account\PaperisasiController@edit')->name('account.paperisasi.editdata');
        Route::post('/paperisasi/data/update-data/{id}', 'account\PaperisasiController@update')->name('account.paperisasi.update');
        Route::delete('/paperisasi/data/delete/{id}', 'account\PaperisasiController@destroy')->name('account.paperisasi.delete');

        // pendaftaran scopuS kafe
        Route::get('/pendaftaran-scopus-kafe/data', 'account\PendaftaranScopusKafeController@index')->name('account.pendaftaran-scopus-kafe.index');
        Route::get('/pendaftaran-scopus-kafe/data/filter', 'account\PendaftaranScopusKafeController@filter')->name('account.pendaftaran-scopus-kafe.filter');
        Route::get('/pendaftaran-scopus-kafe/data/search', 'account\PendaftaranScopusKafeController@search')->name('account.pendaftaran-scopus-kafe.search');
        Route::get('/pendaftaran-scopus-kafe/data/edit/{id}', 'account\PendaftaranScopusKafeController@edit')->name('account.pendaftaran-scopus-kafe.edit');
        Route::post('/pendaftaran-scopus-kafe/data/update-data/{id}', 'account\PendaftaranScopusKafeController@update')->name('account.pendaftaran-scopus-kafe.update');
        Route::delete('/pendaftaran-scopus-kafe/data/delete/{id}', 'account\PendaftaranScopusKafeController@destroy')->name('account.pendaftaran-scopus-kafe.delete');

        // refrensi paper
        Route::get('/refrensi-paper/data', 'account\RefrensiPaperController@index')->name('account.refrensi-paper.index');
        Route::get('/refrensi-paper/data/filter', 'account\RefrensiPaperController@filter')->name('account.refrensi-paper.filter');
        Route::get('/refrensi-paper/data/search', 'account\RefrensiPaperController@search')->name('account.refrensi-paper.search');
        Route::get('/refrensi-paper/data/create', 'account\RefrensiPaperController@create')->name('account.refrensi-paper.create');
        Route::post('/refrensi-paper/data/store', 'account\RefrensiPaperController@store')->name('account.refrensi-paper.store');
        Route::get('/refrensi-paper/data/edit/{id}', 'account\RefrensiPaperController@edit')->name('account.refrensi-paper.edit');
        Route::post('/refrensi-paper/data/update-data/{id}', 'account\RefrensiPaperController@update')->name('account.refrensi-paper.update');
        Route::delete('/refrensi-paper/data/delete/{id}', 'account\RefrensiPaperController@destroy')->name('account.refrensi-paper.delete');

        // to do list
        Route::get('/todolist/data', 'account\ToDoListController@index')->name('account.todolist.index');
        Route::get('/todolist/data/create', 'account\ToDoListController@create')->name('account.todolist.create');
        Route::post('/todolist/data/store', 'account\ToDoListController@store')->name('account.todolist.store');
        Route::post('/todolist/data/UpdateStatusTaskOto', 'account\ToDoListController@updateStatus')->name('account.updatestatusoto.updatestatus');
        Route::get('/todolist/data/edit/{id}', 'account\ToDoListController@edit')->name('account.todolist.edit');
        Route::post('/todolist/data/update-data/{id}', 'account\ToDoListController@update')->name('account.todolist.update');
        Route::post('/todolist/data/update-checklist', 'account\ToDoListController@updateChecklist')->name('account.todolist.updateChecklist');
        Route::post('/todolist/data/add-tasklist', 'account\ToDoListController@addTask')->name('account.todolist.addTask');
        Route::post('/todolist/data/removeTask', 'account\ToDoListController@removeTask')->name('account.todolist.removeTask');
        Route::delete('/todolist/data/delete/{id}', 'account\ToDoListController@destroy')->name('account.todolist.delete');
    }


);
