<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan akun bersandi bawaan di halaman ganti sandi.
 *
 * Akun peserta webinar lahir dengan sandi yang SAMA untuk semua orang, supaya
 * suratnya bisa menyebutkan sandi yang mudah diketik orang yang tidak
 * terbiasa. Akibatnya nyata dan tidak bisa ditutup dengan cara lain: MIS
 * masuk lewat username, username peserta dirakit dari namanya, jadi selama
 * sandinya belum diganti, NAMA ORANG LAIN ADALAH KUNCI AKUN ORANG LAIN.
 *
 * Penahan ini yang membuat keadaan itu berumur pendek — dari saat suratnya
 * dibuka sampai sandinya diganti, bukan selamanya.
 *
 * ---------------------------------------------------------------------------
 * Apa yang tetap boleh dibuka
 * ---------------------------------------------------------------------------
 *
 * Halaman ganti sandinya sendiri (kalau tidak, pengalihannya berputar
 * selamanya), keluar, dan berkas statis. Selebihnya dialihkan.
 *
 * Permintaan JSON/XHR dijawab 409 dengan alamat tujuannya, bukan dialihkan:
 * pengalihan 302 pada XHR menghasilkan halaman HTML yang masuk ke pengurai
 * JSON, dan layarnya cuma diam.
 */
class WajibGantiSandi
{
    /** Rute yang tetap boleh dibuka selama sandinya masih bawaan. */
    private const DIIZINKAN = [
        'account.sandi.wajib',
        'account.sandi.wajib.simpan',
        'logout',
        'login',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $akun = $request->user();

        if ($akun === null || ! $akun->wajib_ganti_sandi) {
            return $next($request);
        }

        $rute = $request->route()?->getName();

        if ($rute !== null && in_array($rute, self::DIIZINKAN, true)) {
            return $next($request);
        }

        /*
         * Keluar lewat POST /logout maupun GET /logout sama-sama dibiarkan.
         * Akun yang tidak bisa keluar dari halaman yang menahannya adalah
         * akun yang terkunci, bukan akun yang diarahkan.
         */
        if ($request->is('logout', 'account/keluar')) {
            return $next($request);
        }

        $tujuan = route('account.sandi.wajib');

        if ($request->expectsJson()) {
            return response()->json([
                'wajib_ganti_sandi' => true,
                'alihkan' => $tujuan,
                'pesan' => 'Ganti kata sandi sementara Anda dulu sebelum melanjutkan.',
            ], 409);
        }

        return redirect()->to($tujuan);
    }
}
