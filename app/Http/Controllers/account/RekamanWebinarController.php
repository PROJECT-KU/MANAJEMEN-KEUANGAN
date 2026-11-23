<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Support\RekamanWebinarSaya;
use App\Support\TautanYouTube;
use Illuminate\Support\Facades\Auth;

/**
 * Rekaman webinar untuk peserta yang sudah membayar.
 *
 * Aturan "siapa boleh menonton" tidak ditulis di sini melainkan di
 * RekamanWebinarSaya — dipakai bersama penghitung di menu. Dua salinan aturan
 * yang sama akan berselisih perlahan, dan selisihnya berupa menu yang
 * menyebut angka tiga sementara halamannya kosong.
 */
class RekamanWebinarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $angkatan = RekamanWebinarSaya::angkatan(Auth::user());

        /*
         * Yang BELUM ada rekamannya ikut ditampilkan, terpisah.
         *
         * Kalau hanya yang sudah ada yang muncul, peserta yang webinarnya
         * baru selesai kemarin membuka halaman kosong — dan halaman kosong
         * tidak bisa ia bedakan dari "akun saya salah" atau "saya tidak
         * terdaftar".
         */
        return view('account.rekaman.index', [
            'siap' => $angkatan->filter(fn ($a) => TautanYouTube::sah($a->rekaman_url))->values(),
            'belum' => $angkatan->reject(fn ($a) => TautanYouTube::sah($a->rekaman_url))->values(),
        ]);
    }

    public function tonton(string $token)
    {
        $angkatan = KategoriLayanan::where('token', $token)
            ->where('layanan', 'webinar_eksklusif')
            ->firstOrFail();

        /*
         * Haknya diperiksa ULANG di sini, bukan diandalkan pada daftar.
         *
         * Alamat halaman ini bisa ditebak-tempel oleh siapa pun yang punya
         * akun — dan sejak fitur ini ada, yang punya akun persis sebanyak
         * orang yang pernah membayar webinar apa pun. Daftar yang rapi bukan
         * penjagaan; yang menjaga baris ini.
         */
        abort_unless(RekamanWebinarSaya::boleh(Auth::user(), $angkatan), 403,
            'Rekaman ini hanya untuk peserta webinar tersebut.');

        $semat = TautanYouTube::sematkan($angkatan->rekaman_url);

        abort_if($semat === null, 404, 'Rekaman webinar ini belum tersedia.');

        return view('account.rekaman.tonton', [
            'angkatan' => $angkatan,
            'semat' => $semat,
            'tautanYouTube' => TautanYouTube::tonton($angkatan->rekaman_url),
        ]);
    }
}
