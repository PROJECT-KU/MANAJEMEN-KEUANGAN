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
use App\Support\FotoProfil;
use App\Mail\VerifikasiEmailMail;
use App\Mail\EmailAkunDipindahMail;
use App\AktivitasMasuk;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password as AturanKataSandi;

class ProfilController extends Controller
{
  /** Kode kliring BRI; lihat config/bank.php. */
  private const BANK_PENGGAJIAN = '002';


  public function show($uuid)
  {
    // Dicari lewat UUID, bukan id berurutan: dengan id, siapa pun yang sudah
    // masuk bisa menaikkan angka di alamatnya dan memetakan seluruh akun.
    $user = User::cariUuid($uuid);

    if (! $user) {
      abort(404);
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

    /*
     * Halaman ini HANYA profil sendiri.
     *
     * Dulu manager boleh membuka profil rekan sekantor, dan hasilnya
     * halaman yang menampilkan dua orang sekaligus: kotak nama dan username
     * terisi data rekan, kartu kontak di sebelahnya menampilkan email milik
     * yang membuka, sedangkan tombol Simpan menulis ke akun yang membuka.
     * Tidak ada urutan klik yang membuat itu masuk akal.
     *
     * Untuk melihat data orang lain sudah ada halaman pengelolaan pengguna,
     * yang memang dibuat untuk itu dan punya pemeriksaan wewenangnya
     * sendiri. Di sini, alamat milik orang lain dikembalikan ke profil
     * sendiri — bukan pesan "akses ditolak", karena yang membuka biasanya
     * hanya salah tautan, bukan sedang mencoba menerobos.
     *
     * Cabang lama juga menunjuk route('account.profil.index') yang tidak
     * pernah ada, jadi penolakannya berakhir sebagai galat 500.
     */
    if ($user->getKey() !== Auth::id()) {
      return redirect()
        ->route('account.profil.show', Auth::user()->uuid)
        ->with('info', 'Halaman profil hanya menampilkan akun Anda sendiri.');
    }

    return view('account.profil.index', [
      'user' => $user,
      'workDuration' => $workDuration,
      'kelengkapan' => $this->kelengkapanProfil($user),
    ]);
  }

  /**
   * Daftar hal yang masih kurang dari sebuah profil.
   *
   * Sebelumnya tidak ada penanda apa pun: profil yang separuh terisi
   * tampak sama saja dengan yang lengkap, jadi orang baru tahu ada yang
   * kurang ketika sistem lain menolaknya — misalnya penggajian yang
   * butuh nomor rekening.
   *
   * Yang didaftar hanya yang BISA diperbaiki sendiri oleh pemiliknya.
   * Status akun dan perannya ditetapkan admin, jadi memasukkannya ke sini
   * hanya akan jadi daftar tugas yang mustahil diselesaikan.
   */
  private function kelengkapanProfil(User $user): array
  {
    $butir = [
      [
        'kunci' => 'foto',
        'judul' => 'Foto profil',
        'catatan' => 'Supaya rekan mengenali Anda.',
        'ikon' => 'fa-camera',
        'warna' => 'biru',
        'selesai' => filled($user->gambar) && $user->gambar !== 'no-image.jpg',
        'tab' => null,
      ],
      [
        'kunci' => 'email',
        'judul' => 'Verifikasi email',
        'catatan' => 'Dipakai kalau kata sandi terlupa.',
        'ikon' => 'fa-envelope',
        'warna' => 'kuning',
        'selesai' => (bool) $user->email_verified_at,
        'tab' => null,
      ],
      [
        'kunci' => 'telp',
        'judul' => 'Nomor WhatsApp',
        'catatan' => 'Untuk kabar yang mendesak.',
        'ikon' => 'fa-mobile-alt',
        'warna' => 'hijau',
        'selesai' => filled($user->telp),
        'tab' => 'activity',
        'medan' => 'prof-telp',
      ],
      [
        'kunci' => 'lahir',
        'judul' => 'Tanggal lahir',
        'catatan' => 'Dipakai untuk data kepegawaian.',
        'ikon' => 'fa-birthday-cake',
        'warna' => 'ungu',
        'selesai' => filled($user->tanggal_lahir),
        'tab' => 'activity',
        'medan' => 'tanggal_lahir',
      ],
    ];

    // Rekening hanya relevan untuk yang memang digaji lewat sistem ini.
    if ($user->level !== 'user') {
      $butir[] = [
        'kunci' => 'rekening',
        'judul' => 'Rekening penggajian',
        'catatan' => 'Tanpa ini gaji tidak bisa dikirim.',
        'ikon' => 'fa-wallet',
        'warna' => 'jingga',
        'selesai' => filled($user->norek) && filled($user->bank),
        'tab' => 'activity',
        'medan' => 'prof-bank-nama',
      ];
    }

    $butir[] = [
      'kunci' => 'pin',
      'judul' => 'PIN masuk',
      'catatan' => 'Masuk cukup dengan 6 angka.',
      'ikon' => 'fa-key',
      'warna' => 'merah',
      'selesai' => $user->pinAktif(),
      'tab' => 'pin',
    ];

    $selesai = count(array_filter($butir, fn ($b) => $b['selesai']));

    return [
      'butir' => $butir,
      'selesai' => $selesai,
      'total' => count($butir),
      'persen' => (int) round($selesai / max(count($butir), 1) * 100),
      'kurang' => array_values(array_filter($butir, fn ($b) => ! $b['selesai'])),
    ];
  }

  // <!--================== UPDATE FOTO PROFIL ==================-->
  public function updatePhoto(Request $request)
  {
    // 1. Validasi input (Sesuaikan max dengan script JS Anda: 3MB = 3072)
    $request->validate([
      // webp ikut diterima: layar pengelolaan pengguna sudah menerimanya,
      // dan sejak semua foto disimpan sebagai webp, menolaknya di sini berarti
      // foto yang baru diunduh dari sistem ini sendiri tidak bisa diunggah balik.
      'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
    ]);

    // 2. Langsung ambil User yang sedang login (Hanya diri sendiri)
    $user = Auth::user();

    if ($request->hasFile('gambar')) {

      // 3. Simpan berkas baru lebih dulu. Namanya dibuat di sisi peladen,
      //    isinya digambar ulang jadi WebP, dan yang asli tidak ikut tersimpan.
      $fileName = FotoProfil::simpan($request->file('gambar'), 'profil-' . $user->id);

      if (! $fileName) {
        return redirect()->back()->with('error', 'Berkas tidak dikenali sebagai gambar. Gunakan JPG, PNG, GIF, atau WEBP.');
      }

      // 4. Foto lama baru dihapus setelah yang baru aman tersimpan.
      FotoProfil::hapus($user->gambar);

      // 5. Simpan ke DB
      $user->gambar = $fileName;
      $user->save();

      $this->catatPerubahan($user, 'foto profil diganti');

      return redirect()->back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    return redirect()->back()->with('error', 'Gagal memperbarui foto.');
  }
  // <!--================== END ==================-->

  /**
   * Hapus foto profil dan kembali ke gambar bawaan.
   *
   * Sebelumnya foto hanya bisa diganti, tidak bisa dihapus: begitu ada yang
   * salah unggah — foto orang lain, tangkapan layar, berkas pribadi — satu-
   * satunya jalan mundur adalah mengunggah gambar lain sebagai penutup.
   */
  public function hapusFoto()
  {
    $user = Auth::user();

    if (! FotoProfil::punyaFoto($user->gambar)) {
      return redirect()->back()->with('error', 'Tidak ada foto yang perlu dihapus.');
    }

    // Berkasnya dihapus lebih dulu; kalau gagal, kolomnya tetap dikosongkan
    // supaya pengguna tidak terjebak dengan foto yang tidak bisa dibuang.
    FotoProfil::hapus($user->gambar);

    $user->gambar = null;
    $user->save();

    $this->catatPerubahan($user, 'foto profil dihapus');

    return redirect()->back()->with('success', 'Foto profil dihapus.');
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

    // Kodenya SENGAJA tidak ikut dicatat: berkas log dibaca banyak pihak
    // (admin hosting, cadangan), dan kode itu cukup untuk memverifikasi email
    // orang lain.
    Log::info('Kode verifikasi dibuat untuk pengguna ' . $user->getKey());

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

      $emailLama = (string) $user->email;

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
      $this->catatPerubahan($user, 'alamat email diganti');

      /*
       * Kabar ke alamat LAMA, bukan hanya ke yang baru.
       *
       * Tautan verifikasi memang dikirim ke alamat baru, tetapi yang perlu
       * tahu justru pemilik alamat lama: alamat email adalah jalan
       * memulihkan akun, jadi memindahkannya diam-diam sama dengan
       * mengambil alih akun. Kegagalan kirim tidak boleh membatalkan
       * perubahan yang sudah tersimpan.
       */
      try {
        Mail::to($emailLama)->send(
          new EmailAkunDipindahMail($user, $emailLama, (string) $user->email, (string) $request->ip())
        );
      } catch (\Throwable $e) {
        Log::error('Gagal mengabari alamat email lama: ' . $e->getMessage());
      }

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

    // Nomor rekening dipakai saat penggajian, jadi isinya diperiksa: hanya
    // angka dan pemisah, panjang wajar. Sebelumnya apa pun diterima.
    if ($request->has('norek')) {
      $request->validate([
        'norek' => ['nullable', 'string', 'max:40', 'regex:/^[0-9 .\-]*$/'],
      ], [
        'norek.regex' => 'Nomor rekening hanya boleh berisi angka.',
      ]);

      $user->norek = $request->input('norek');
    }

    /*
     * Bank penggajian dikunci ke BRI.
     *
     * Gaji dikirim lewat satu bank, jadi rekening yang didaftarkan harus
     * rekening BRI. Nilainya ditetapkan di sini, BUKAN diambil dari
     * kiriman — kalau hanya dikunci di layar, siapa pun masih bisa
     * mengirim kode bank lain langsung ke alamat ini.
     *
     * Akun lama yang terlanjur memakai bank lain ikut dibetulkan begitu
     * pemiliknya menyimpan profilnya.
     */
    if ($request->has('norek')) {
      $user->bank = self::BANK_PENGGAJIAN;
    }

    // Nomor telepon dan jabatan ikut formulir ini, bukan jendela terpisah:
    // keduanya isian biasa yang tidak butuh kata sandi, dan dua jalan untuk
    // satu hal hanya membingungkan. Ganti email tetap terpisah karena harus
    // disertai kata sandi.
    if ($request->has('telp')) {
      $request->validate([
        'telp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9 +.\-]*$/'],
      ], [
        'telp.regex' => 'Nomor telepon hanya boleh berisi angka.',
      ]);

      $user->telp = $request->input('telp');
    }

    if ($request->has('jobdesk')) {
      $request->validate([
        'jobdesk' => ['nullable', 'string', 'max:50'],
      ]);

      $user->jobdesk = $request->input('jobdesk');
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

    /*
     * Dicatat SEBELUM save(): wasChanged() baru terisi sesudahnya, tetapi
     * yang dibandingkan di sini nilai lama dari basis data, jadi keduanya
     * harus diambil selagi modelnya masih kotor.
     */
    $usernameBerubah = $user->isDirty('username');
    $norekBerubah = $user->isDirty('norek');

    $user->save();

    // Dua hal ini yang paling layak ditelusuri: username adalah cara masuk,
    // dan nomor rekening adalah ke mana gaji dikirim.
    if ($usernameBerubah) {
      $this->catatPerubahan($user, 'username diganti');
    }

    if ($norekBerubah) {
      $this->catatPerubahan($user, 'nomor rekening diganti');
    }

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

    $this->catatPerubahan($user, 'kata sandi diganti');

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
  /**
   * Catat satu perubahan penting pada akun ke riwayat keamanan.
   *
   * Sebelumnya hanya percobaan masuk yang tercatat, padahal yang paling
   * perlu ditelusuri justru perubahan seperti alamat email, username, dan
   * nomor rekening penggajian — semuanya bisa dipakai mengalihkan akun atau
   * mengalihkan gaji, dan tidak meninggalkan jejak apa pun di mana pun.
   *
   * Tabelnya memang bernama aktivitas_masuk, tetapi sejak awal juga sudah
   * dipakai mencatat "keluar" dan "keluar dari perangkat lain"; tab di
   * halaman profil karena itu disebut Riwayat keamanan, bukan riwayat masuk.
   */
  private function catatPerubahan(User $user, string $apa): void
  {
    AktivitasMasuk::catat($user, (string) ($user->username ?? $user->email), true, $apa);
  }

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
