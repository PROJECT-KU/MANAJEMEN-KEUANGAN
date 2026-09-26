<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use App\Mail\VerifikasiEmailMail;
use App\Rules\BukanEmailSekaliPakai;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [

                'full_name'     => 'required',
                'username'      => ['required', 'unique:users'],
                'email'         => ['required', 'email', new BukanEmailSekaliPakai, 'unique:users'],
                'password'      => ['required', 'string', AturanKataSandi::defaults()],
                'telp'          => ['nullable', 'string', 'max:20'],
                'jenis'         => ['required', 'in:perorangan,bisnis'],

            ],
            [
                'full_name.required'    => 'Masukkan Nama Lengkap Anda !',
                'username.required'     => 'Masukkan Username Anda !',
                'username.unique'       => 'Username Sudah Terdaftar !',
                'email.required'        => 'Masukkan Alamat Email Anda !',
                'email.unique'          => 'Alamat Email Sudah Terdaftar !',
                'password.required'     => 'Masukkan Password Anda !',
                'telp.required'         => 'Masukkan No Telp Anda !',
                'jenis.required'        => 'Silahkan Pilih Jenis Akun Anda !',
            ]
        );

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'data'    => $validator->errors()
            ], 401);
        } else {

            // PENTING: kolom diisi satu per satu, tidak lagi dari
            // $request->all(). Sebelumnya penyerang cukup menyertakan
            // "level":"manager" pada permintaan untuk mendapatkan akun
            // manager, karena 'level' termasuk kolom yang boleh diisi massal.
            $user = User::create([
                'full_name' => $request->input('full_name'),
                'username'  => $request->input('username'),
                'email'     => $request->input('email'),
                'telp'      => $request->input('telp'),
                'jenis'     => $request->input('jenis'),
                'level'     => 'user',   // ditentukan server, bukan pengirim
                'password'  => bcrypt($request->input('password')),
            ]);

            $this->kirimTautanVerifikasi($user);

            $apiToken = $user->createToken('apiToken')->accessToken;

            return response()->json([
                'success'   => true,
                'message'   => 'Akun dibuat. Tautan verifikasi sudah dikirim ke email Anda.',
                'data'      => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'level'     => $user->level,
                    'verified'  => (bool) $user->email_verified_at,
                ],
                'apiToken'  => $apiToken,
            ], 201);
        }
    }

    /** Kirim tautan verifikasi; kegagalannya tidak membatalkan pendaftaran. */
    private function kirimTautanVerifikasi(User $user): void
    {
        $tautan = URL::temporarySignedRoute('verification.verify', now()->addHours(48), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        try {
            Mail::to($user->email)->send(new VerifikasiEmailMail($user, $tautan, 48));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim tautan verifikasi (API): ' . $e->getMessage());
        }
    }
}
