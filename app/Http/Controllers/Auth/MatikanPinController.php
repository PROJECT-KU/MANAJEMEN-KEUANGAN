<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PemberitahuanPinMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mematikan PIN lewat tautan bertanda tangan dari email.
 *
 * Tautan ini hanya MENCABUT sebuah jalan pintas; ia tidak pernah bisa
 * dipakai untuk masuk, jadi risikonya kecil sekalipun tautannya terbaca
 * orang lain.
 */
class MatikanPinController extends Controller
{
    public function __invoke(Request $request, $id, string $hash)
    {
        $pengguna = User::find($id);

        if (! $pengguna || ! hash_equals(sha1($pengguna->getEmailForVerification()), $hash)) {
            return redirect()->route('login')->with('error', 'Tautan tidak sah atau akunnya sudah tidak ada.');
        }

        if (! $pengguna->pinAktif()) {
            return redirect()->route('login')->with('success', 'PIN memang sudah tidak aktif. Silakan masuk dengan kata sandi.');
        }

        $pengguna->matikanPin();

        try {
            Mail::to($pengguna->email)->send(
                new PemberitahuanPinMail($pengguna, 'dinonaktifkan', (string) $request->ip())
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pemberitahuan PIN dimatikan: ' . $e->getMessage());
        }

        return redirect()->route('login')->with('success', 'PIN berhasil dimatikan. Masuk dengan kata sandi, lalu buat PIN baru dari halaman profil bila perlu.');
    }
}
