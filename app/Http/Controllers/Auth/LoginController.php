<?php

namespace App\Http\Controllers\Auth;

use App\AktivitasMasuk;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/account/dashboard/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    // Override the logout method to show a logout message
    public function logout(Request $request)
    {
        // Dicatat sebelum sesi dibersihkan, selagi identitas penggunanya
        // masih diketahui.
        $pengguna = $this->guard()->user();

        if ($pengguna) {
            AktivitasMasuk::catat($pengguna, $pengguna->username ?? $pengguna->email, true, 'keluar');
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Session::flash('success', 'Anda berhasil keluar.');

        return $this->loggedOut($request) ?: redirect()->route('login');
    }
}
