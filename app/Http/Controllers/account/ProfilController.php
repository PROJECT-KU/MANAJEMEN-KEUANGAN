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
use App\Mail\PemberitahuanPinMail;
use App\Support\BerkasGambar;
use App\Mail\VerifikasiEmailMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password as AturanKataSandi;

class ProfilController extends Controller
{
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
  public function updatePhoto(Request $request)
  {
    // 1. Validasi input (Sesuaikan max dengan script JS Anda: 3MB = 3072)
    $request->validate([
      'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:3072',
    ]);

    // 2. Langsung ambil User yang sedang login (Hanya diri sendiri)
    $user = Auth::user();

    if ($request->hasFile('gambar')) {

      // 3. Simpan berkas baru lebih dulu. Namanya dibuat di sisi peladen dan
      //    ekstensinya ditebak dari isi berkas, bukan dari nama kiriman.
      $fileName = BerkasGambar::simpan($request->file('gambar'), 'assets/img/profil', 'profil-' . $user->id);

      if (! $fileName) {
        return redirect()->back()->with('error', 'Berkas tidak dikenali sebagai gambar. Gunakan JPG, PNG, GIF, atau WEBP.');
      }

      // 4. Foto lama baru dihapus setelah yang baru aman tersimpan.
      BerkasGambar::hapus('assets/img/profil', $user->gambar, ['default.png', 'no-image.jpg']);

      // 5. Simpan ke DB
      $user->gambar = $fileName;
      $user->save();

      return redirect()->back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    return redirect()->back()->with('error', 'Gagal memperbarui foto.');
  }
  // <!--================== END ==================-->

  // <!--================== VERIFIKASI EMAIL ==================-->
  public function verifyEmail(Request $request)
  {
    $user = Auth::user();

    // Check if a code was already sent within the last 2 minutes
    if ($user->code_verified_mail_sent_at && (int) now()->diffInMinutes($user->code_verified_mail_sent_at, true) <= 1) {
      return response()->json(['statuswaitingsend' => 'error', 'message' => 'Kode verifikasi sudah dikirim. Harap tunggu 60 detik sebelum mencoba lagi.'], 200);
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
    $verificationCode = (string) $request->input('verification_code');

    // Kode hanya enam angka, jadi tanpa pembatas ia bisa ditebak beruntun
    // selama jendela dua menit itu.
    $kunci = 'kode-verifikasi|' . $user->getKey();

    if (RateLimiter::tooManyAttempts($kunci, 5)) {
      return response()->json([
        'statustidakvalid' => 'error',
        'message' => 'Terlalu banyak percobaan. Minta kode baru dalam ' . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
      ]);
    }

    // hash_equals, bukan '==': dua string angka dibandingkan sebagai bilangan
    // oleh PHP, sehingga kode "000123" cocok dengan tebakan "123".
    if ($user->code_verified_mail !== null && hash_equals((string) $user->code_verified_mail, $verificationCode)) {
      if ((int) now()->diffInMinutes($user->code_verified_mail_sent_at, true) <= 2) {
        // Mark email as verified
        $user->email_verified_at = now();
        $user->status = 'active';
        $user->code_verified_mail = null;
        $user->code_verified_mail_sent_at = null;
        $user->save();

        RateLimiter::clear($kunci);

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
      RateLimiter::hit($kunci, 600);

      // Sesudah batasnya tercapai, kodenya dibuang sekalian supaya tebakan
      // berikutnya tidak punya sasaran.
      if (RateLimiter::attempts($kunci) >= 5) {
        $user->code_verified_mail = null;
        $user->code_verified_mail_sent_at = null;
        $user->save();
      }

      return response()->json([
        'statustidakvalid' => 'error',
        'message' => 'Kode verifikasi tidak valid!',
      ]);
    }
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE DATA DIRI SIDE BAR ==================-->
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
    $emailBerubah = false;

    if ($request->has('email') && $request->input('email') !== $user->email) {
      // Ganti email wajib disertai kata sandi saat ini. Tanpa ini, sesi yang
      // terlanjur dibajak bisa memindahkan alamat email lalu memakai "lupa
      // kata sandi" untuk mengambil alih akun sepenuhnya.
      if (! $request->filled('kata_sandi_email') || ! Hash::check($request->input('kata_sandi_email'), $user->password)) {
        return redirect()->back()->with('errorsandiemail', 'Kata sandi salah. Alamat email tidak jadi diganti.');
      }

      $user->email = $request->input('email');
      $user->email_verified_at = null; // Reset email verification if email changes
      $emailBerubah = true;
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

    if ($emailBerubah) {
      // Status verifikasi sudah direset di atas; kirimkan tautan baru supaya
      // pengguna tidak tertinggal tanpa cara memverifikasi alamat barunya.
      $terkirim = $this->kirimTautanVerifikasi($user);

      return redirect()->back()->with('statusdataprofil', $terkirim
        ? 'Data profil berhasil diperbarui. Tautan verifikasi sudah dikirim ke alamat email baru Anda.'
        : 'Data profil berhasil diperbarui, tetapi tautan verifikasi gagal dikirim. Silakan kirim ulang dari halaman verifikasi.');
    }

    // Return success message
    return redirect()->back()->with('statusdataprofil', 'Data profil berhasil diperbarui.');
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE DATA DIRI ==================-->
  public function update(Request $request)
  {
    $user = Auth::user();

    if ($request->has('tanggal_lahir') && $request->input('tanggal_lahir') != null) {
      try {
        $request->validate([
          // Memastikan tanggal lahir adalah 17 tahun yang lalu atau lebih lama
          'tanggal_lahir' => 'date|before:-17 years',
        ], [
          'tanggal_lahir.before' => 'Usia Anda harus minimal 17 tahun.'
        ]);

        $user->tanggal_lahir = $request->input('tanggal_lahir');
      } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
          ->with('errortanggallahir', 'Usia Anda harus minimal 17 tahun.')
          ->withErrors($e->validator);
      }
    }

    if ($request->has('norek')) {
      $user->norek = $request->input('norek');
    }

    if ($request->has('bank')) {
      $user->bank = $request->input('bank');
    }

    // status, level, company, dan jenis SENGAJA tidak diambil dari permintaan.
    // Formulir ini milik pengguna sendiri; di layar isian itu memang dikunci,
    // tetapi penguncian di layar bisa dilewati. Perubahan peran hanya boleh
    // lewat halaman pengelolaan pengguna oleh admin/manager/CEO.

    if ($request->has('full_name')) {
      $request->validate(['full_name' => 'nullable|string|max:255']);
      $user->full_name = $request->input('full_name');
    }

    if ($request->has('username')) {
      // Username dipakai untuk masuk, jadi tidak boleh bentrok dengan akun lain.
      $request->validate([
        'username' => 'nullable|string|max:150|unique:users,username,' . $user->id,
      ], [
        'username.unique' => 'Username sudah dipakai akun lain.',
      ]);

      $user->username = $request->input('username');
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
      // Aturan yang sama dengan pendaftaran & atur ulang: minimal 8, ada huruf
      // dan angka, serta bukan kata sandi yang pernah bocor.
      'password' => ['required', 'string', 'max:72', 'confirmed', AturanKataSandi::defaults()],
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

    // PIN ikut dimatikan: kata sandi berganti berarti akses lama harus
    // berhenti seluruhnya, termasuk jalan pintas enam angka di perangkat
    // yang mungkin sudah tidak dipegang pemiliknya.
    if ($user->pinAktif()) {
      $user->matikanPin();

      try {
        Mail::to($user->email)->send(
          new PemberitahuanPinMail($user, 'dinonaktifkan', (string) $request->ip())
        );
      } catch (\Throwable $e) {
        Log::error('Gagal mengirim pemberitahuan PIN dimatikan: ' . $e->getMessage());
      }
    }

    // Token API ikut dicabut: kata sandi berganti berarti akses lama harus
    // berhenti, bukan hanya sesi peramban.
    try {
      $user->tokens()->update(['revoked' => true]);
    } catch (\Throwable $e) {
      report($e);
    }

    return response()->json([
      'statussuksesreset' => 'success',
      'message' => 'Password anda berhasil diubah!'
    ]);
  }
  // <!--================== END ==================-->


  /** Kirim tautan verifikasi ke alamat email pengguna saat ini. */
  private function kirimTautanVerifikasi($user): bool
  {
    $tautan = URL::temporarySignedRoute('verification.verify', now()->addHours(48), [
      'id' => $user->getKey(),
      'hash' => sha1($user->getEmailForVerification()),
    ]);

    try {
      Mail::to($user->email)->send(new VerifikasiEmailMail($user, $tautan, 48));

      return true;
    } catch (\Throwable $e) {
      Log::error('Gagal mengirim tautan verifikasi setelah ganti email: ' . $e->getMessage());

      return false;
    }
  }
}
