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
use Illuminate\Support\Facades\Broadcast;


Route::get('/page-maintenance', 'account\MaintenanceController@page')->name('account.page-maintenance.blank');

// HOME PUBLIC
Route::get('/', 'Publict\PublicHomeController@home')->name('home');

// ARTIKEL PUBLIC
Route::get('/blog', 'Publict\PublicArticleController@public')->name('blog.artikel.blog');
Route::get('/blog/topic/{categories_artikel_id}', 'Publict\PublicArticleController@publickategori')->name('blog.topic.kategori');
Route::get('/blog/topic/blog-single/{id}', 'Publict\PublicArticleController@blogsingle')->name('blog.topic.blog-single');
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

// ANALASIS BIBLIOMETRIK PUBLIC
Route::get('/Analisis-Bibliometrik', 'Publict\PublicAnalisisBibliometrikController@public')->name('public.analisisbibliometrik.index');
Route::get('/Analisis-Bibliometrik/selengkapnya/{id}', 'Publict\PublicAnalisisBibliometrikController@Selengkapnya')->name('public.analisisbibliometrik.Selengkapnya');
Route::get('/Analisis-Bibliometrik/Form-Pendaftaran/{id}', 'Publict\PublicAnalisisBibliometrikController@FormPendaftaran')->name('public.analisisbibliometrik.formpendaftaran');
Route::get('/cek-kode-diskon/{id}', 'Publict\PublicAnalisisBibliometrikController@cekKodeDiskon')->name('public.cekkodediskon.formpendaftaran');
Route::post('/Analisis-Bibliometrik/store', 'Publict\PublicAnalisisBibliometrikController@store')->name('public.analisisbibliometrik.store');

// SCOPUS CAMP PUBLIC
Route::get('/Scopus-Camp', 'Publict\PublicScopusCampController@public')->name('public.scopuscamp.index');
Route::get('/Scopus-Camp/selengkapnya/{id}', 'Publict\PublicScopusCampController@Selengkapnya')->name('public.scopuscamp.Selengkapnya');
Route::get('/Scopus-Camp/Form-Pendaftaran/{id}', 'Publict\PublicScopusCampController@FormPendaftaran')->name('public.scopuscamp.formpendaftaran');
Route::get('/Scopus-Camp/cek-kode-diskon/{id}', 'Publict\PublicScopusCampController@cekKodeDiskon')->name('public.scopuscamp.cekkodediskon');
Route::post('/Scopus-Camp/store', 'Publict\PublicScopusCampController@store')->name('public.scopuscamp.store');

// WEBINAR EKSKLUSIF
// Halaman pemasarannya ada di subdomain tersendiri dan mengambil datanya
// lewat /api/webinar-eksklusif; yang di sini bagian yang menulis ke basis data.
Route::get('/Webinar-Eksklusif', 'Publict\PublicWebinarEksklusifController@index')->name('public.webinareksklusif.index');
/*
 * UUID saja, tanpa token.
 *
 * Angkatan dan pendaftaran sama-sama berkunci Str::uuid() acak — 122 bit yang
 * tidak bisa ditebak — jadi token di belakangnya tidak menambah apa pun selain
 * alamat yang dua kali lebih panjang dan dua nilai yang harus dijaga tetap
 * cocok.
 */
Route::get('/Webinar-Eksklusif/Daftar/{id}', 'Publict\PublicWebinarEksklusifController@daftar')->name('public.webinareksklusif.daftar');
// Dibatasi 6 kiriman per menit per alamat IP. Borang ini publik, diiklankan,
// dan TIAP kiriman yang berhasil langsung memotong kuota — satu skrip
// sederhana bisa menghabiskan seluruh kursi sebelum ada yang sadar. Enam
// masih longgar untuk orang yang salah ketik beberapa kali.
/*
 * Pencarian data pendaftar dari nomor WhatsApp, untuk mengisi borang
 * otomatis. Dibatasi 10 per menit per IP: jalur ini menukar nomor jadi nama
 * dan email, jadi yang perlu dihambat bukan salah ketik melainkan penyisiran.
 */
Route::post('/Webinar-Eksklusif/cari-pendaftar', 'Publict\PublicWebinarEksklusifController@cariPendaftar')
    ->middleware('throttle:10,1')
    ->name('public.webinareksklusif.caripendaftar');

// Pemeriksa kode diskon, supaya orangnya tahu kodenya dipakai SEBELUM
// mengirim borang. Dibatasi 20 per menit: ini juga jalur yang bisa dipakai
// menebak kode satu per satu.
Route::post('/Webinar-Eksklusif/{id}/cek-diskon', 'Publict\PublicWebinarEksklusifController@cekDiskon')
    ->middleware('throttle:20,1')
    ->name('public.webinareksklusif.cekdiskon');

Route::post('/Webinar-Eksklusif/store', 'Publict\PublicWebinarEksklusifController@store')
    ->middleware('throttle:6,1')
    ->name('public.webinareksklusif.store');
Route::get('/Webinar-Eksklusif/Status/{id}', 'Publict\PublicWebinarEksklusifController@status')->name('public.webinareksklusif.status');
// Dibatasi 3 per menit: mengirim surat itu pekerjaan yang memakan waktu, dan
// tombolnya bisa ditekan berkali-kali oleh orang yang tidak sabar menunggu.
Route::post('/Webinar-Eksklusif/Status/{id}/kirim-ulang', 'Publict\PublicWebinarEksklusifController@kirimUlang')
    ->middleware('throttle:3,1')
    ->name('public.webinareksklusif.kirimulang');
// Pengirimnya peladen DOKU, bukan peramban peserta, jadi tanpa token CSRF —
// penggantinya pemeriksaan tanda tangan di dalam pengendalinya.
Route::post('/Webinar-Eksklusif/pemberitahuan/doku', 'Publict\PublicWebinarEksklusifController@pemberitahuan')->name('public.webinareksklusif.pemberitahuan');

// CLINIK SCOPUS
Route::get('/Clinik-Scopus', 'Publict\PublicClinikScopusController@index')->name('public.clinikscopus.index');
Route::get('/Clinik-Scopus/Sesi/{id}', 'Publict\PublicClinikScopusController@sesi')->name('public.clinikscopus.sesi');
Route::post('/cek-diskon-sesi/Clinik-Scopus', 'Publict\PublicClinikScopusController@cekDiskon')->name('public.CekDiskonclinikscopus.CekKodeDiskon');
Route::get('/cek-ppn-sesi/Clinik-Scopus', 'Publict\PublicClinikScopusController@cekPpn')->name('public.CekPpnclinikscopus.CekPpn');
Route::post('/Clinik-Scopus/Pemesanan', 'Publict\PublicClinikScopusController@store')->name('public.ClinikScopusPemesanan.store');
Route::post('/Clinik-Scopus/Pemesanan/upload-bukti', 'Publict\PublicClinikScopusController@uploadBukti')->name('public.ClinikScopusPemesanan.uploadBukti');

/**
 * account
 */
Route::prefix('account')
    ->middleware(['auth', 'terverifikasi'])
    ->group(
        function () {

            // karir
            Route::get('/karir', 'account\KarirController@index')->name('karir.index');
            Route::get('/karir/list', 'account\KarirController@list')->name('karir.list');
            Route::get('/karir/detail/{id}', 'account\KarirController@detail')->name('karir.detail');
            Route::post('/karir/terkirim', 'account\KarirController@store')->name('karir.store');
            Route::get('/karir/edit/{id}', 'account\KarirController@edit')->name('karir.edit');
            Route::post('/karir/update/{id}', 'account\KarirController@update')->name('karir.update');
            Route::get('/karir/search', 'account\KarirController@search')->name('karir.search');
            Route::get('/karir/filter', 'account\KarirController@filter')->name('karir.filter');
            Route::delete('/karir/{id}', 'account\KarirController@destroy')->name('account.karir.destroy');

            //reset password

            //dashboard account
            Route::get('/dashboard', 'account\DashboardController@index')->name('account.dashboard.index');

            // Jejak percobaan masuk (manager, ceo, admin)
            Route::get('/aktivitas-masuk', 'account\AktivitasMasukController@index')->name('account.aktivitas-masuk.index');
            Route::get('/aktivitas-masuk/ekspor', 'account\AktivitasMasukController@ekspor')->name('account.aktivitas-masuk.ekspor');
            Route::post('/aktivitas-masuk/buka-kunci', 'account\AktivitasMasukController@bukaKunci')->name('account.aktivitas-masuk.buka-kunci');

            // pengguna
            Route::get('/pengguna', 'account\PenggunaController@index')->name('account.pengguna.index');
            Route::get('/pengguna/create', 'account\PenggunaController@create')->name('account.pengguna.create');
            Route::post('/pengguna', 'account\PenggunaController@store')->name('account.pengguna.store');
            Route::get('/pengguna/{id}/edit', 'account\PenggunaController@edit')->name('account.pengguna.edit');
            // {pengguna:uuid}, bukan {id}: alamatnya ikut tercatat di riwayat
            // peramban, catatan peladen, dan tautan yang disalin orang. id
            // berurut membuat satu tautan cukup untuk menebak tautan akun lain.
            Route::post('/pengguna/update/foto/{pengguna:uuid}', 'account\PenggunaController@updatePhoto')->name('account.pengguna.update.updatePhoto');
            Route::post('/pengguna/update/data-diri/{pengguna:uuid}', 'account\PenggunaController@updatediri')->name('account.pengguna.update.datadiri');
            Route::post('/pengguna/update/data-diri-pengguna/{pengguna:uuid}', 'account\PenggunaController@update')->name('account.pengguna.update');
            Route::post('/pengguna/update/verifikasi-email/{pengguna:uuid}', 'account\PenggunaController@verifyEmail')->name('account.pengguna.update.vertifikasiemail');
            Route::get('/pengguna/{id}/detail', 'account\PenggunaController@detail')->name('account.pengguna.detail');
            Route::post('/pengguna/{id}/matikan-pin', 'account\PenggunaController@matikanPin')->name('account.pengguna.matikan-pin');
            Route::delete('/pengguna/delete/{id}', 'account\PenggunaController@destroy')->name('account.pengguna.destroy');
            Route::get('/pengguna/search', 'account\PenggunaController@search')->name('account.pengguna.search');

            // routes/web.php

            //download excel
            //Route::get('/account/laporan-semua/download-excel', 'account\LaporanSemuaController@downloadExcel')->name('account.laporan-semua.download-excel');
            //Route::get('/account/laporan-semua/export-users-to-excel', 'account\LaporanSemuaController@exportUsersToExcel')->name('account.laporan-semua.export-users-to-excel');

            //profil
            Route::get('/profil/{uuid}/show', 'account\ProfilController@show')->name('account.profil.show');
            Route::post('/profil/update-bank', 'account\ProfilController@update')->name('account.profil.update');
            Route::post('/profil/update/foto', 'account\ProfilController@updatePhoto')->name('account.profil.updatePhoto');
            Route::post('/profil/hapus-foto', 'account\ProfilController@hapusFoto')->name('account.profil.hapusFoto');
            Route::get('/profil/riwayat-keamanan/ekspor', 'account\ProfilController@eksporRiwayat')->name('account.profil.ekspor.riwayat');
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
            // Route::get('penyewaan/search', 'account\PenyewaanController@search')->name('account.penyewaan.search');
            // Route::delete('account/penyewaan/{id}', 'PenyewaanController@destroy')->name('account.penyewaan.destroy');
            // Route::get('/account/penyewaan/create', 'account\PenyewaanController@create')->name('account.penyewaan.create');
            // Route::post('/account/penyewaan/store', 'account\PenyewaanController@store')->name('account.penyewaan.store');
            // Route::get('account/penyewaan/{id}/edit', 'account\PenyewaanController@edit')->name('account.penyewaan.edit');
            // Route::put('account/penyewaan/{id}', 'account\PenyewaanController@update')->name('account.penyewaan.update');
            // Route::Resource('/penyewaan', 'account\PenyewaanController', ['as' => 'account']);
            // Route::get('penyewaan/{id}/detail', 'account\PenyewaanController@detail')->name('account.penyewaan.detail');
            // Route::get('/account/laporan_penyewaan/download-pdf', 'account\PenyewaanController@downloadPdf')->name('account.laporan_penyewaan.download-pdf');
            // Route::get('/penyewaan/pdf/{id}', 'account\PenyewaanController@detailPDF')->name('pdf.show');

            //tambah barang
            Route::get('/tambah_barang/search', 'account\TambahBarangController@search')->name('account.tambah_barang.search');
            Route::Resource('/tambah_barang', 'account\TambahBarangController', ['as' => 'account', 'except' => ['show']]);

            //categories debit
            Route::get('/categories_debit/search', 'account\CategoriesDebitController@search')->name('account.categories_debit.search');
            Route::Resource('/categories_debit', 'account\CategoriesDebitController', ['as' => 'account', 'except' => ['show']]);

            // Fitur Uang Masuk (debit) dan Uang Keluar (credit) dihapus
            // 27 September 2026 atas permintaan pemilik. Tabelnya sengaja
            // TIDAK ikut dihapus karena Neraca, Laporan Semua, dan Pesanan
            // masih membaca data yang sudah terlanjur tercatat di sana.

            //categories credit
            Route::get('/categories_credit/search', 'account\CategoriesCreditController@search')->name('account.categories_credit.search');
            Route::Resource('/categories_credit', 'account\CategoriesCreditController', ['as' => 'account', 'except' => ['show']]);


            //laporan debit
            Route::get('/laporan_debit', 'account\LaporanDebitController@index')->name('account.laporan_debit.index');
            Route::get('/laporan_debit/check', 'account\LaporanDebitController@check')->name('account.laporan_debit.check');

            //laporan credit
            Route::get('/laporan_credit', 'account\LaporanCreditController@index')->name('account.laporan_credit.index');
            Route::get('/laporan_credit/check', 'account\LaporanCreditController@check')->name('account.laporan_credit.check');

            //laporan semua
            Route::get('/laporan_semua', 'account\LaporanSemuaController@index')->name('account.laporan_semua.index');

            //laporan neraca
            Route::get('/neraca', 'account\NeracaController@index')->name('account.neraca.index');

            //gaji
            Route::get('/gaji', 'account\GajiController@index')->name('account.gaji.index');
            Route::get('/gaji/create', 'account\GajiController@create')->name('account.gaji.create');
            Route::post('/gaji/store', 'account\GajiController@store')->name('account.gaji.store');
            Route::delete('/gaji/delete/{id}', 'account\GajiController@destroy')->name('account.gaji.destroy');
            Route::get('/gaji/edit/{id}', 'account\GajiController@edit')->name('account.gaji.edit');
            Route::get('/gaji/detail/{id}', 'account\GajiController@detail')->name('account.gaji.detail');
            Route::post('account/gaji/{id}', 'account\GajiController@update')->name('account.gaji.update');
            Route::get('/gaji/search', 'account\GajiController@searchGaji')->name('account.gaji.search');
            Route::get('/gaji/filter', 'account\GajiController@filterGaji')->name('account.gaji.filter');
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
            Route::get('/laporan_presensi/download-excel', 'account\PresensiController@downloadExcel')->name('account.laporan_presensi.download-excel');

            //email

            // company
            Route::get('/company/{id}/edit', 'account\PenggunaController@company')->name('account.company.edit');
            Route::put('/company/{id}', 'account\PenggunaController@updateCompany')->name('account.company.update');

            // notifikasi

            // maintenance
            Route::get('/maintenance', 'account\MaintenanceController@index')->name('account.maintenance.index');
            Route::get('/maintenance/create', 'account\MaintenanceController@create')->name('account.maintenance.create');
            Route::post('/maintenance', 'account\MaintenanceController@store')->name('account.maintenance.store');
            Route::get('/maintenance/{id}/edit', 'account\MaintenanceController@edit')->name('account.maintenance.edit');
            Route::post('/maintenance/{id}', 'account\MaintenanceController@update')->name('account.maintenance.update');
            Route::get('/maintenance/blank', 'account\MaintenanceController@maintenance')->name('account.maintenance.blank');
            Route::delete('/maintenance/{id}', 'account\MaintenanceController@destroy')->name('account.maintenance.destroy');

            // sewa
            // Route::get('/sewa', 'account\SewaController@index')->name('account.sewa.index');
            // Route::get('/sewa/create', 'account\SewaController@create')->name('account.sewa.create');
            // Route::post('/sewa', 'account\SewaController@store')->name('account.sewa.store');
            // Route::get('/sewa/{id}/edit', 'account\SewaController@edit')->name('account.sewa.edit');
            // Route::put('/sewa/{id}', 'account\SewaController@update')->name('account.sewa.update');

            Route::get('/get-user-phone/{userId}', 'account\PresensiController@getUserPhone')->name('account.getUserPhone');

            // laporan camp
            Route::get('/camp', 'account\CampController@index')->name('account.camp.index');
            Route::get('/camp/create', 'account\CampController@create')->name('account.camp.create');
            Route::post('/camp/store', 'account\CampController@store')->name('account.camp.store');
            Route::get('/camp/search', 'account\CampController@search')->name('account.camp.search');
            Route::get('/camp/filter', 'account\CampController@filter')->name('account.camp.filter');
            Route::get('/camp/detail/{id}', 'account\CampController@detail')->name('account.camp.detail');
            Route::delete('/camp/{id}', 'account\CampController@destroy')->name('account.camp.destroy');
            Route::get('/camp/edit/{id}', 'account\CampController@edit')->name('account.camp.edit');
            Route::post('/camp/{id}', 'account\CampController@update')->name('account.camp.update');
            Route::get('/laporan_camp/download-pdf', 'account\CampController@downloadPdf')->name('account.laporan_camp.download-pdf');
            Route::get('/laporan_camp/download-excel', 'account\CampController@downloadExcel')->name('account.laporan_camp.download-excel');
            Route::get('/laporan_camp/{id}/Slip-Camp', 'account\CampController@SlipCamp')->name('account.laporan_Camp.Slip-Camp');

            // Laporan peserta
            Route::get('/Laporan-Peserta/list', 'account\PesertaController@list')->name('account.peserta.list');
            Route::get('/Laporan-Peserta/detail/{id}', 'account\PesertaController@detail')->name('account.peserta.detail');
            Route::delete('/Laporan-Peserta/{id}', 'account\PesertaController@destroy')->name('account.peserta.destroy');
            Route::get('/Laporan-Peserta/search', 'account\PesertaController@search')->name('account.peserta.search');
            Route::get('/Laporan-Peserta/filter', 'account\PesertaController@filter')->name('account.peserta.filter');
            Route::get('/Laporan-Peserta', 'account\PesertaController@index')->name('account.peserta.form');
            /*
             * SATU-SATUNYA rute di sini yang ruas keduanya benar-benar
             * dipakai: testimoni() mencarinya ke basis data lewat kolom
             * token_update, bukan sekadar menerimanya lalu mengabaikannya
             * seperti rute-rute lain yang tokennya dibuang 3 Okt 2026.
             *
             * Namanya ditulis token_update supaya cocok dengan nama
             * parameter metodenya — sebelumnya {token}, dan Laravel
             * mengisinya hanya karena kebetulan urutannya sama.
             */
            Route::get('/Laporan-Peserta/testimoni/{id}/{token_update}', 'account\PesertaController@testimoni')->name('account.peserta.testimoni');
            Route::post('/Laporan-Peserta/simpan', 'account\PesertaController@store')->name('account.peserta.store');
            Route::post('/Laporan-Peserta/selesai/{id}', 'account\PesertaController@update')->name('account.peserta.update');


            // Kategori Analisis Bibliometrik
            Route::get('/kategori/analisis-bibliometrik', 'account\CategoriesAnalisisBibliometrikController@index')->name('account.kategori.index');
            Route::get('/kategori/analisis-bibliometrik/create', 'account\CategoriesAnalisisBibliometrikController@create')->name('account.kategori.create');
            Route::post('/kategori/analisis-bibliometrik/store', 'account\CategoriesAnalisisBibliometrikController@store')->name('account.kategori.store');
            Route::get('/kategori/analisis-bibliometrik/edit/{id}', 'account\CategoriesAnalisisBibliometrikController@edit')->name('account.kategori.edit');
            Route::post('/kategori/analisis-bibliometrik/update/{id}', 'account\CategoriesAnalisisBibliometrikController@update')->name('account.kategori.update');
            Route::delete('/kategori/analisis-bibliometrik/delete/{id}', 'account\CategoriesAnalisisBibliometrikController@destroy')->name('account.kategori.destroy');
            Route::get('/kategori/analisis-bibliometrik/search', 'account\CategoriesAnalisisBibliometrikController@search')->name('account.ketegori.search');
            Route::get('/kategori/analisis-bibliometrik/filter', 'account\CategoriesAnalisisBibliometrikController@filter')->name('account.ketegori.filter');
            Route::get('/kategori/analisis-bibliometrik/download-pdf', 'account\CategoriesAnalisisBibliometrikController@downloadPdf')->name('account.ketegori.download-pdf');
            Route::get('/kategori/analisis-bibliometrik/download-excel', 'account\CategoriesAnalisisBibliometrikController@downloadExcel')->name('account.ketegori.download-excel');

            // kategori artikel
            Route::get('/artikel-kategori', 'account\CategoriesArtikelController@index')->name('account.Kategori-Artikel.index');
            Route::get('/artikel-kategori/create', 'account\CategoriesArtikelController@create')->name('account.Kategori-Artikel.create');
            Route::post('/artikel-kategori/store', 'account\CategoriesArtikelController@store')->name('account.Kategori-Artikel.store');
            Route::get('/artikel-kategori/edit/{id}', 'account\CategoriesArtikelController@edit')->name('account.Kategori-Artikel.edit');
            Route::post('/artikel-kategori/update/{id}', 'account\CategoriesArtikelController@update')->name('account.Kategori-Artikel.update');
            Route::delete('/artikel-kategori/delete/{id}', 'account\CategoriesArtikelController@destroy')->name('account.Kategori-Artikel.destroy');
            Route::get('/artikel-kategori/search', 'account\CategoriesArtikelController@search')->name('account.Kategori-Artikel.search');
            Route::get('/artikel-kategori/filter', 'account\CategoriesArtikelController@filter')->name('account.Kategori-Artikel.filter');

            // ARRIKEL ADMIN
            Route::get('/article', 'account\ArtikelController@index')->name('account.Artikel.index');
            Route::get('/article/create', 'account\ArtikelController@create')->name('account.Artikel.create');
            Route::post('/article/store', 'account\ArtikelController@store')->name('account.Artikel.store');
            Route::get('/article/edit/{id}', 'account\ArtikelController@edit')->name('account.Artikel.edit');
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

            // cuti karyawan
            Route::get('/cuti/data', 'account\CutiController@index')->name('account.cuti.index');
            Route::get('/cuti/data/create', 'account\CutiController@create')->name('account.cuti.create');

            // data cutomer
            // {pelanggan:uuid}, bukan {id}: id berurut membuat tautan satu
            // pelanggan bisa dipakai menebak tautan pelanggan lain.
            // Rute search dan live dihapus — pencariannya sekarang jadi satu
            // dengan daftar lewat parameter ?cari=, dan poll-nya tak pernah
            // dipakai berkas mana pun.
            Route::get('/customer/data', 'account\CustomerController@index')->name('account.customer.index');
            // Ekspor didaftar SEBELUM rute ber-{uuid}: kalau sesudahnya,
            // "ekspor" akan terbaca sebagai uuid dan tidak pernah tercapai.
            Route::get('/customer/data/ekspor', 'account\CustomerController@ekspor')->name('account.customer.ekspor');
            Route::get('/customer/data/ekspor-excel', 'account\CustomerController@eksporExcel')->name('account.customer.ekspor.excel');
            Route::get('/customer/data/{pelanggan:uuid}', 'account\CustomerController@edit')->name('account.customer.edit');
            Route::post('/customer/data/massal', 'account\CustomerController@massal')->name('account.customer.massal');
            Route::post('/customer/data/{pelanggan:uuid}/kirim-verifikasi', 'account\CustomerController@kirimVerifikasi')->name('account.customer.kirim.verifikasi');
            Route::post('/customer/data/{pelanggan:uuid}/atur-ulang-sandi', 'account\CustomerController@kirimAturUlangSandi')->name('account.customer.kirim.sandi');
            Route::delete('/customer/data/{pelanggan:uuid}', 'account\CustomerController@destroy')->name('account.customer.destroy');

            //clinik scopus trainer
            Route::get('/clinikscopus/data', 'account\ClinikScopusTrainerController@index')->name('account.clinikscopus.index');
            Route::get('/clinikscopus/data/create', 'account\ClinikScopusTrainerController@create')->name('account.clinikscopus.create');
            Route::post('/clinikscopus/data/store', 'account\ClinikScopusTrainerController@store')->name('account.clinikscopus.store');
            Route::get('/clinikscopus/data/edit/{id}', 'account\ClinikScopusTrainerController@edit')->name('account.clinikscopus.edit');
            Route::post('/clinikscopus/data/update-data/{id}', 'account\ClinikScopusTrainerController@update')->name('account.clinikscopus.update');
            Route::delete('/clinikscopus/data/{id}', 'account\ClinikScopusTrainerController@destroy')->name('account.clinikscopus.destroy');
            Route::get('/clinikscopus/search', 'account\ClinikScopusTrainerController@search')->name('account.clinikscopus.search');

            // kategori scopus camp
            Route::get('scopus-camp/kategori/', 'account\CategoriesScopusCampController@index')->name('account.kategoriscopuscamp.index');
            Route::get('scopus-camp/kategori/create', 'account\CategoriesScopusCampController@create')->name('account.kategoriscopuscamp.create');
            Route::post('scopus-camp/kategori/store', 'account\CategoriesScopusCampController@store')->name('account.kategoriscopuscamp.store');
            Route::get('scopus-camp/kategori/edit/{id}', 'account\CategoriesScopusCampController@edit')->name('account.kategoriscopuscamp.edit');
            Route::post('scopus-camp/kategori/update/{id}', 'account\CategoriesScopusCampController@update')->name('account.kategoriscopuscamp.update');
            Route::delete('/scopus-camp/kategori/delete/{id}', 'account\CategoriesScopusCampController@destroy')->name('account.kategoriscopuscamp.destroy');
            Route::get('scopus-camp/kategori/search', 'account\CategoriesScopusCampController@search')->name('account.kategoriscopuscamp.search');
            Route::get('scopus-camp/kategori/filter', 'account\CategoriesScopusCampController@filter')->name('account.kategoriscopuscamp.filter');


            /*
             * Pendaftar SELURUH layanan dalam satu daftar, beserta rincian dan
             * tindakannya.
             *
             * MENGGANTIKAN empat layar pendaftaran per layanan yang dibuang
             * 3 Okt 2026: Scopus Camp, Analisis Bibliometrik, Scopus Kafe, dan
             * Webinar Eksklusif. Seluruh kemampuannya pindah ke sini —
             * rincian, suntingan, perpindahan status beserta email
             * pemberitahuannya, dan penghapusan beserta pengembalian kuota.
             *
             * Riwayat Pemesanan Clinik Scopus TIDAK ikut dibuang: layar itu
             * dipakai PELANGGAN untuk melihat pesanannya sendiri, dan
             * membuangnya mencabut sesuatu yang bukan milik panitia.
             */
            Route::get('Pendaftaran-Layanan', 'account\\PendaftaranLayananController@index')
                ->name('account.pendaftaran-layanan.index');
            Route::get('Pendaftaran-Layanan/unduh-pdf', 'account\\PendaftaranLayananController@eksporPdf')
                ->name('account.pendaftaran-layanan.pdf');
            Route::get('Pendaftaran-Layanan/unduh-excel', 'account\\PendaftaranLayananController@eksporExcel')
                ->name('account.pendaftaran-layanan.excel');

            /*
             * Mendaftarkan orang dari sisi panitia — untuk yang mendaftar
             * lewat WhatsApp atau datang langsung. Ditaruh SEBELUM rute
             * rincian yang berpola {layanan}/{id} supaya 'baru' tidak terbaca
             * sebagai nama layanan.
             */
            Route::get('Pendaftaran-Layanan/baru', 'account\\PendaftaranLayananController@baru')
                ->name('account.pendaftaran-layanan.baru');
            Route::post('Pendaftaran-Layanan/baru', 'account\\PendaftaranLayananController@simpan')
                ->name('account.pendaftaran-layanan.simpan');

            /*
             * Rincian satu pendaftaran, dan ketiga tindakannya.
             *
             * {layanan} ditaruh di alamatnya supaya satu rute melayani kelima
             * layanan; nilainya dicocokkan ke katalog tertutup di pengendalinya
             * sebelum dipakai, sebab ia menentukan model mana yang dipanggil.
             * Ditaruh SESUDAH rute unduhan supaya 'unduh-pdf' tidak terbaca
             * sebagai nama layanan.
             */
            /*
             * Slip cetak. DI ATAS rute rincian: '{id}/slip' tidak boleh
             * terbaca sebagai id sebuah pendaftaran.
             */
            Route::get('Pendaftaran-Layanan/{layanan}/{id}/slip', 'account\\PendaftaranLayananController@slip')
                ->name('account.pendaftaran-layanan.slip');
            Route::get('Pendaftaran-Layanan/{layanan}/{id}', 'account\\PendaftaranLayananController@rincian')
                ->name('account.pendaftaran-layanan.rincian');
            Route::put('Pendaftaran-Layanan/{layanan}/{id}', 'account\\PendaftaranLayananController@ubahData')
                ->name('account.pendaftaran-layanan.ubah');
            Route::post('Pendaftaran-Layanan/{layanan}/{id}/status', 'account\\PendaftaranLayananController@ubahStatus')
                ->name('account.pendaftaran-layanan.status');
            Route::delete('Pendaftaran-Layanan/{layanan}/{id}', 'account\\PendaftaranLayananController@hapus')
                ->name('account.pendaftaran-layanan.hapus');


            //clinik scopus promo
            Route::get('/Clinik-Scopus-Promo/data', 'account\ClinikScopusPromoController@index')->name('account.Clinik-Scopus-Promo.index');
            Route::get('/Clinik-Scopus-Promo/data/create', 'account\ClinikScopusPromoController@create')->name('account.Clinik-Scopus-Promo.create');
            Route::post('/Clinik-Scopus-Promo/data/store', 'account\ClinikScopusPromoController@store')->name('account.Clinik-Scopus-Promo.store');
            Route::get('/Clinik-Scopus-Promo/data/edit/{id}', 'account\ClinikScopusPromoController@edit')->name('account.Clinik-Scopus-Promo.edit');
            Route::post('/Clinik-Scopus-Promo/data/update-data/{id}', 'account\ClinikScopusPromoController@update')->name('account.Clinik-Scopus-Promo.update');
            Route::delete('/Clinik-Scopus-Promo/data/{id}', 'account\ClinikScopusPromoController@destroy')->name('account.Clinik-Scopus-Promo.destroy');
            Route::get('/Clinik-Scopus-Promo/search', 'account\ClinikScopusPromoController@search')->name('account.Clinik-Scopus-Promo.search');

            /*
             * Clinik Scopus — tarif per sesi.
             *
             * Satu layar, bukan lagi daftar berikut halaman tambah dan ubah
             * sendiri-sendiri: yang diatur di sini hanya SATU nilai, yaitu
             * harga sesi yang berlaku sekarang. Rute create/edit/search yang
             * dulu ada ikut dibuang bersama halamannya.
             *
             * {tarif} terikat ke kunci utamanya, dan kunci utama tabel ini
             * memang UUID — jadi alamatnya tidak bisa ditebak dengan menambah
             * satu seperti nomor berurut.
             */
            Route::get('/Clinik-Scopus-Biaya-Persesi/data', 'account\ClinikScopusBiayaPersesiController@index')->name('account.Clinik-Scopus-Biaya-Persesi.index');
            Route::post('/Clinik-Scopus-Biaya-Persesi/data', 'account\ClinikScopusBiayaPersesiController@simpan')->name('account.Clinik-Scopus-Biaya-Persesi.simpan');
            Route::get('/Clinik-Scopus-Biaya-Persesi/cetak', 'account\ClinikScopusBiayaPersesiController@cetakPdf')->name('account.Clinik-Scopus-Biaya-Persesi.cetak');
            Route::post('/Clinik-Scopus-Biaya-Persesi/data/{tarif}/berlakukan', 'account\ClinikScopusBiayaPersesiController@berlakukan')->name('account.Clinik-Scopus-Biaya-Persesi.berlakukan');
            Route::delete('/Clinik-Scopus-Biaya-Persesi/data/{tarif}', 'account\ClinikScopusBiayaPersesiController@destroy')->name('account.Clinik-Scopus-Biaya-Persesi.destroy');

            // Katalog layanannya sendiri: menambah jenis jasa baru tanpa rilis.
            Route::post('/layanan', 'account\LayananController@store')->name('account.layanan.store');
            Route::post('/layanan/{layanan}', 'account\LayananController@update')->name('account.layanan.update');
            Route::delete('/layanan/{layanan}', 'account\LayananController@destroy')->name('account.layanan.destroy');

            /*
             * Angkatan seluruh layanan, satu layar. Penggantinya dua layar
             * kategori yang lama; keduanya masih hidup sampai layar ini
             * dipakai sehari-hari, supaya tidak ada pekerjaan yang terhenti
             * di tengah jalan.
             */
            Route::get('/kategori-layanan', 'account\KategoriLayananController@index')->name('account.kategori-layanan.index');
            Route::get('/kategori-layanan/baru', 'account\KategoriLayananController@create')->name('account.kategori-layanan.create');
            Route::post('/kategori-layanan', 'account\KategoriLayananController@store')->name('account.kategori-layanan.store');
            Route::post('/kategori-layanan/rakit', 'account\KategoriLayananController@rakit')->name('account.kategori-layanan.rakit');
            Route::get('/kategori-layanan/{angkatan}/ubah', 'account\KategoriLayananController@edit')->name('account.kategori-layanan.edit');
            Route::post('/kategori-layanan/{angkatan}/gandakan', 'account\KategoriLayananController@gandakan')->name('account.kategori-layanan.gandakan');
            Route::post('/kategori-layanan/massal/status', 'account\KategoriLayananController@massal')->name('account.kategori-layanan.massal');
            // Jalur sendiri, bukan salah satu nilai dari massal/status:
            // menghapus tidak bisa dibatalkan.
            Route::post('/kategori-layanan/massal/hapus', 'account\KategoriLayananController@massalHapus')->name('account.kategori-layanan.massal-hapus');
            Route::get('/kategori-layanan/{angkatan}/detail', 'account\KategoriLayananController@detail')->name('account.kategori-layanan.detail');
            // Mengembalikan angkatan yang baru dihapus, lewat jejaknya.
            Route::post('/kategori-layanan/pulihkan/{jejak}', 'account\KategoriLayananController@pulihkan')->name('account.kategori-layanan.pulihkan');
            Route::get('/kategori-layanan/cetak/pdf', 'account\KategoriLayananController@cetakPdf')->name('account.kategori-layanan.cetak');
            Route::get('/kategori-layanan/cetak/excel', 'account\KategoriLayananController@cetakExcel')->name('account.kategori-layanan.excel');
            Route::post('/kategori-layanan/{angkatan}', 'account\KategoriLayananController@update')->name('account.kategori-layanan.update');
            Route::delete('/kategori-layanan/{angkatan}', 'account\KategoriLayananController@destroy')->name('account.kategori-layanan.destroy');

            // Galeri foto per layanan. Unggahannya langsung jadi WebP di
            // storage; berkas aslinya dihapus.
            Route::get('/galeri', 'account\GaleriController@index')->name('account.galeri.index');
            Route::post('/galeri', 'account\GaleriController@store')->name('account.galeri.store');
            Route::post('/galeri/{galeri}/ubah', 'account\GaleriController@update')->name('account.galeri.update');
            Route::post('/galeri/{galeri}/putar', 'account\GaleriController@putar')->name('account.galeri.putar');
            Route::delete('/galeri/{galeri}', 'account\GaleriController@destroy')->name('account.galeri.destroy');

            // riwayat pemesanan clinik scopus
            Route::get('/Clinik-Scopus-Riwayat-Pemesanan/data', 'account\ClinikScopusRiwayatPemesananController@index')->name('account.Clinik-Scopus-Riwayat-Pemesanan.index');
            Route::get('/Clinik-Scopus-Riwayat-Pemesanan/detail/{id}', 'account\ClinikScopusRiwayatPemesananController@detail')->name('account.Clinik-Scopus-Riwayat-Pemesanan.detail');
            Route::put('/Clinik-Scopus-Riwayat-Pemesanan/update-status/{id}', 'account\ClinikScopusRiwayatPemesananController@updateStatus')->name('account.Clinik-Scopus-Riwayat-Pemesanan.updateStatus');
            Route::delete('/Clinik-Scopus-Riwayat-Pemesanan/delete/{id}', 'account\ClinikScopusRiwayatPemesananController@destroy')->name('account.Clinik-Scopus-Riwayat-Pemesanan.destroy');
            Route::get('/Clinik-Scopus-Riwayat-Pemesanan/search', 'account\ClinikScopusRiwayatPemesananController@search')->name('account.Clinik-Scopus-Riwayat-Pemesanan.search');

            // clinik scopus chat
            Route::get('/clinik-scopus/chat/{pemesanan}', 'account\ClinikScopusChatController@index')->name('chat.index');
            Route::post('/clinik-scopus/chat/send', 'account\ClinikScopusChatController@send')->name('chat.send');
            Route::get('/clinik-scopus/chat/load/{pemesanan}', 'account\ClinikScopusChatController@load')->name('chat.load');
            Route::post('/clinik-scopus/chat/clear/{pemesanan}', 'account\ClinikScopusChatController@clearChat')->name('chat.clear');

            // clinik scopus testimoni
            Route::post('/Clinik-Scopus-Testimoni/data/store', 'account\ClinikScopusTestimoniController@store')->name('account.Clinik-Scopus-Testimoni.store');
        }
    );
