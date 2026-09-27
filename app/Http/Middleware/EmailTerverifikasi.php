<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;

/**
 * Menahan akun yang belum memverifikasi email.
 *
 * Penegakan sengaja hanya berlaku untuk akun yang dibuat sejak tanggal pada
 * config('auth.verifikasi_wajib_sejak'). Saat aturan ini dipasang ada 47 akun
 * lama yang belum terverifikasi -- 14 di antaranya staf dan trainer -- dan
 * mengunci mereka serentak akan menghentikan pekerjaan.
 */
class EmailTerverifikasi
{
    public function handle($request, Closure $next)
    {
        $pengguna = $request->user();

        if (! $pengguna || $pengguna->email_verified_at) {
            return $next($request);
        }

        $wajibSejak = config('auth.verifikasi_wajib_sejak');

        if ($wajibSejak && $pengguna->created_at && $pengguna->created_at->lt(Carbon::parse($wajibSejak))) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Email Anda belum diverifikasi.',
            ], 403);
        }

        return redirect()->route('verification.resend')
            ->with('error', 'Verifikasi email Anda dulu untuk memakai sistem. Butuh tautan baru? Kirim ulang di bawah ini.');
    }
}
