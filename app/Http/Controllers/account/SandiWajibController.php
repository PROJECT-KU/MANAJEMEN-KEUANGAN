<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Halaman "ganti kata sandi sementara" untuk akun yang dibuatkan sistem.
 *
 * Akun peserta webinar lahir dengan sandi bawaan yang sama untuk semua orang.
 * Middleware `sandi.wajib` menahan akun itu di sini sampai ia memilih
 * sandinya sendiri.
 *
 * SENGAJA TIDAK meminta sandi lama. Orangnya baru saja memakainya untuk
 * masuk, sandi itu tertulis di surat yang baru ia buka, dan memintanya lagi
 * hanya menambah satu isian yang bisa salah ketik pada layar yang tidak bisa
 * ia lewati. Yang menjaga pintu di sini sesi yang sudah masuk, bukan
 * pengetahuan atas sandi bawaan yang memang bukan rahasia.
 */
class SandiWajibController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function form()
    {
        /*
         * Yang sandinya sudah bukan bawaan tidak punya urusan di sini.
         * Tanpa pengalihan ini, halamannya bisa dibuka siapa saja lewat
         * alamatnya langsung, dan ia akan terbaca seperti tuntutan.
         */
        if (! Auth::user()?->wajib_ganti_sandi) {
            return redirect()->route('account.more.index');
        }

        return view('account.sandi.wajib');
    }

    public function simpan(Request $request)
    {
        $akun = Auth::user();

        if (! $akun?->wajib_ganti_sandi) {
            return redirect()->route('account.more.index');
        }

        /*
         * Aturan yang SAMA dengan pendaftaran dan atur ulang — termasuk
         * uncompromised(), yang menolak kata sandi yang pernah bocor. Itu
         * berarti "Password123" sendiri ditolak di sini, dan memang itu
         * yang diinginkan: halaman ini ada supaya sandi bawaannya berhenti
         * dipakai, bukan supaya diketik ulang.
         */
        $data = $request->validate([
            'password' => ['required', 'string', 'max:72', 'confirmed', AturanKataSandi::defaults()],
        ], [], ['password' => 'kata sandi baru']);

        $akun->forceFill([
            'password' => Hash::make($data['password']),
            'wajib_ganti_sandi' => false,
        ])->save();

        /*
         * PIN ikut dimatikan kalau ada, sama seperti jalur ganti sandi di
         * halaman profil: sandi berganti berarti akses lama berhenti
         * seluruhnya.
         */
        try {
            if (method_exists($akun, 'pinAktif') && $akun->pinAktif()) {
                $akun->matikanPin();
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mematikan PIN sesudah ganti sandi wajib: ' . $e->getMessage());
        }

        /*
         * Hash sandi berubah, dan AuthenticateSession mengikat sesi ke hash
         * itu — tanpa penyegaran ini, orangnya langsung terlempar ke halaman
         * masuk tepat sesudah berhasil mengganti sandinya.
         */
        Auth::logoutOtherDevices($data['password']);
        $request->session()->put('password_hash_' . Auth::getDefaultDriver(), $akun->getAuthPassword());

        return redirect()->route('account.rekaman.index')
            ->with('success', 'Kata sandi Anda sudah diganti. Selamat datang di MIS.');
    }
}
