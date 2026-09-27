<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

/**
 * Menangani tautan verifikasi email yang dikirim saat pendaftaran.
 *
 * Tautannya ditandatangani (signed URL) dan kedaluwarsa, jadi tidak perlu
 * menyimpan token tambahan di basis data.
 */
class VerifikasiEmailController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        $pengguna = User::find($id);

        // Hash dicocokkan dengan alamat email: kalau emailnya sudah diganti,
        // tautan lama otomatis tidak berlaku.
        if (! $pengguna || ! hash_equals($hash, sha1($pengguna->getEmailForVerification()))) {
            return redirect()->route('login')
                ->with('error', 'Tautan verifikasi tidak valid. Silakan minta tautan baru dari halaman profil.');
        }

        if ($pengguna->email_verified_at) {
            return redirect()->route('login')
                ->with('success', 'Email Anda sudah terverifikasi. Silakan masuk.');
        }

        $pengguna->forceFill([
            'email_verified_at' => now(),
            'status' => 'active',
            'code_verified_mail' => null,
            'code_verified_mail_sent_at' => null,
        ])->save();

        return redirect()->route('login')
            ->with('success', 'Email berhasil diverifikasi. Silakan masuk ke akun Anda.');
    }
}
