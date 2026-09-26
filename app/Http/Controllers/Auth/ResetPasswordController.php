<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\PasswordResetSuccessMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class ResetPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showResetForm()
    {
        return view('auth.lupapassword');
    }

    /*
    |--------------------------------------------------------------------------
    | Alur atur ulang kata sandi pindah ke Livewire
    |--------------------------------------------------------------------------
    | Method formpassword() dan resetPassword() dihapus. resetPassword() dulu
    | mengganti kata sandi akun mana pun hanya berbekal alamat email, tanpa
    | token maupun kode verifikasi, sehingga siapa pun yang sudah masuk bisa
    | mengambil alih akun lain.
    |
    | Penggantinya: App\Livewire\Auth\LupaPassword (kirim kode 6 digit ke
    | email) dan App\Livewire\Auth\AturUlangPassword (verifikasi kode lalu
    | simpan kata sandi baru).
    */
}
