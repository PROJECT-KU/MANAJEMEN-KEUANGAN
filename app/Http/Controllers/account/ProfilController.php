<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
  // ...

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $user = User::find($id);

    // If user data not found, redirect or show an error message
    if (!$user) {
      return redirect()->route('account.profil.index')->with('error', 'User not found.');
    }

    // Calculate work duration if email is verified and status is active
    $workDuration = '';
    if ($user->email_verified_at && $user->status === 'active') {
      $now = now();
      $diff = $user->created_at->diff($now);

      $years = $diff->y;
      $months = $diff->m;
      $days = $diff->d;

      if ($years > 0) {
        $workDuration .= $years . ($years > 1 ? ' tahun ' : ' tahun ');
      }

      if ($months > 0 || $years > 0) {
        $workDuration .= $months . ($months > 1 ? ' bulan ' : ' bulan ');
      }

      if ($days > 0 || $months == 0 || $years == 0) {
        $workDuration .= $days . ($days > 1 ? ' hari' : ' hari');
      }
    } else {
      $workDuration = 'Email belum diverifikasi atau status tidak aktif';
    }

    // Check if user is allowed to view the profile
    if (
      Auth::check() && Auth::user()->level == 'manager' && Auth::user()->company == $user->company ||
      Auth::check() && Auth::user()->id == $user->id
    ) {
      return view('account.profil.index', compact('user', 'workDuration'));
    } else {
      return redirect()->route('account.profil.index')->with('error', 'Access denied.');
    }
  }

  // <!--================== UPDATE FOTO PROFIL ==================-->
  public function updatePhoto(Request $request, $id)
  {
    $user = User::find($id);

    $user = Auth::user();

    // Menghapus foto lama jika ada
    if ($user->gambar && file_exists(public_path('assets/img/profil/' . $user->gambar))) {
      unlink(public_path('assets/img/profil/' . $user->gambar));
    }

    // Menyimpan foto baru di assets/public/img/profil
    $fileName = time() . '.' . $request->gambar->extension();
    $request->gambar->move(public_path('assets/img/profil'), $fileName);

    // Update nama file gambar di database
    $user->gambar = $fileName;
    $user->save();

    // Redirect dengan session success
    return redirect()->back()->with('success', 'Foto profil berhasil diperbarui.');
  }
  // <!--================== END ==================-->

  // <!--================== VERIFIKASI EMAIL ==================-->
  public function verifyEmail(Request $request)
  {
    $user = Auth::user();

    // Check if a code was already sent within the last 2 minutes
    if ($user->code_verified_mail_sent_at && now()->diffInMinutes($user->code_verified_mail_sent_at) <= 2) {
      return response()->json(['statuswaitingsend' => 'error', 'message' => 'Kode verifikasi sudah dikirim. Harap tunggu 2 menit sebelum mencoba lagi.'], 200);
    }

    // Generate a new verification code
    $verificationCode = sprintf('%06d', random_int(0, 999999));

    // Log for debugging purposes
    Log::info('Generating verification code: ' . $verificationCode);

    // Update user's verification code and timestamp
    $user->code_verified_mail = $verificationCode;
    $user->code_verified_mail_sent_at = now();
    $user->save();

    $appName = 'Rumah Scopus Foundation';
    // Send verification code via email
    Mail::to($user->email)->send(new VerificationCodeMail($user, $appName, $verificationCode));

    return response()->json(['statusterkirim' => 'success', 'message' => 'Kode Verifikasi berhasil dikirim ke email Anda.'], 200);
  }

  // Verify the submitted code
  public function verify(Request $request)
  {
    $user = Auth::user();
    $verificationCode = $request->input('verification_code');

    // Check if the code is correct and was sent within the last 2 minutes
    if ($user->code_verified_mail == $verificationCode) {
      if (now()->diffInMinutes($user->code_verified_mail_sent_at) <= 2) {
        // Mark email as verified
        $user->email_verified_at = now();
        $user->code_verified_mail = null;
        $user->code_verified_mail_sent_at = null;
        $user->save();

        return response()->json([
          'statusvalid' => 'success',
          'message' => 'Email berhasil diverifikasi!',
        ]);
      } else {
        return response()->json([
          'statuskadaluarsa' => 'error',
          'message' => 'Kode verifikasi sudah kadaluarsa!',
        ]);
      }
    } else {
      return response()->json([
        'statustidakvalid' => 'error',
        'message' => 'Kode verifikasi tidak valid!',
      ]);
    }
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE DATA DIRI ==================-->
  public function updatediri(Request $request)
  {
    $user = Auth::user();

    // Validate input data
    try {
      $request->validate([
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'jobdesk' => 'nullable|string',
        'telp' => 'nullable|string',
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Check if the validation error is for the email field
      if ($e->validator->errors()->has('email')) {
        // Return back with SweetAlert message for duplicate email
        return redirect()->back()->with('erroremailterpakai', 'Email sudah terdaftar.')->withErrors($e->validator);
      }

      // Handle other validation errors
      throw $e;
    }

    // Update email only if provided and different from the current email
    if ($request->has('email') && $request->input('email') !== $user->email) {
      $user->email = $request->input('email');
      $user->email_verified_at = null; // Reset email verification if email changes
    }

    // Update jobdesk and telp if present
    if ($request->has('jobdesk')) {
      $user->jobdesk = $request->input('jobdesk');
    }

    if ($request->has('telp')) {
      $user->telp = $request->input('telp');
    }

    // Save user data
    $user->save();

    // Return success message
    return redirect()->back()->with('statusdataprofil', 'Data profil berhasil diperbarui.');
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE BANK ==================-->
  public function update(Request $request)
  {
    $user = Auth::user();

    // Validate input data
    $request->validate([
      'nik' => 'nullable|string',
      'norek' => 'nullable|string',
      'bank' => 'nullable|string',
    ]);

    if ($request->has('nik')) {
      $user->nik = $request->input('nik');
    }

    if ($request->has('norek')) {
      $user->norek = $request->input('norek');
    }

    if ($request->has('bank')) {
      $user->bank = $request->input('bank');
    }

    $user->save();

    // Return a success message
    return redirect()->back()->with('statusdataprofil', 'Data profil berhasil diperbarui.');
  }
  // <!--================== END ==================-->

  // <!--================== RESET PASSWORD ==================-->
  public function resetPassword(Request $request)
  {
    $user = Auth::user();

    // Validate input
    $request->validate([
      'old_password' => 'required',
      'password' => 'required|string|min:8|confirmed',
    ]);

    // Check if old password matches
    if (!Hash::check($request->input('old_password'), $user->password)) {
      return response()->json([
        'statuserrorreset' => 'error',
        'message' => 'Password lama tidak sesuai, Silahkan masukan password lama yang sesuai!'
      ]);
    }

    // Update password
    $user->password = Hash::make($request->input('password'));
    $user->save();

    return response()->json([
      'statussuksesreset' => 'success',
      'message' => 'Password anda berhasil diubah!'
    ]);
  }
  // <!--================== END ==================-->

}
