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

class ResetPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showResetForm()
    {
        return view('auth.lupapassword');
    }

    public function formpassword()
    {
        return view('auth.newpassword');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'min:8', 'confirmed'],
            ],
            [
                'email.required' => 'Masukkan Alamat Email Anda!',
                'password.required' => 'Masukkan Password Baru Anda!',
                'password.confirmed'    => 'Konfirmasi Password Salah !',
            ]
        );
        if ($validator->fails()) {
            return redirect()->route('formemail.reset')->withErrors($validator)->withInput();
        }

        // Check if the email exists in the users table
        $user = DB::table('users')->where('email', $request->email)->first();

        if ($user) {
            // Update the user's password with the new one
            DB::table('users')->where('id', $user->id)->update([
                'password' => Hash::make($request->input('password')),
            ]);

            $updatedUser = User::find($user->id);
            $appName = 'Rumah Scopus Foundation';

            Mail::to($request->email)->send(new PasswordResetSuccessMail($updatedUser, $appName));

            // Redirect to the login page
            return redirect()->route('login')->with('reset', 'Password Anda Berhasil Diperbarui!');
        } else {
            // Email doesn't exist, display error message and redirect back
            return Redirect::back()->withInput()->with('error', 'Alamat Email Tidak Terdaftar!');
        }
    }
}
