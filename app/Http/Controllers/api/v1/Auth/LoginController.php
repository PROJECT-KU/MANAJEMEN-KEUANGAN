<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        //validate data from form
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required',
                'password' => 'required',
            ],
            [
                'username.required' => 'Masukkan Username Anda !',
                'password.required' => 'Masukkan Password Anda !',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => $validator->errors()
            ], 401);
        } else {
            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                /** @var \App\User $user */
                $user = Auth::user();

                if ($user->status === 'nonactive') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun ini dinonaktifkan.',
                    ], 403);
                }

                try {
                    // Sebelumnya endpoint ini hanya mengembalikan objek user dan
                    // tidak pernah menerbitkan token, sehingga seluruh endpoint
                    // auth:api di bawahnya mustahil dipakai.
                    $token = $user->createToken('api')->accessToken;
                } catch (\Throwable $e) {
                    report($e);

                    return response()->json([
                        'success' => false,
                        'message' => 'Token tidak bisa diterbitkan. Passport belum disiapkan di server (jalankan passport:install).',
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'token_type' => 'Bearer',
                    'access_token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'level' => $user->level,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau Password Anda salah!'
                ], 401);
            }
        }
    }
}
