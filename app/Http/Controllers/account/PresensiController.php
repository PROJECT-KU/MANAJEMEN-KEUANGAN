<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Presensi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PresensiNotification;

class PresensiController extends Controller
{
  /**
   * PenyewaanController constructor.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function generateRandomId($length)
  {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = '';
    for ($i = 0; $i < $length; $i++) {
      $id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $id;
  }

  public function index()
  {
    $user = Auth::user();
    $currentMonth = date('Y-m-01 00:00:00');
    $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));

    if ($user->level == 'manager' || $user->level == 'staff') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.time_pulang', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.time_pulang', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensi = Presensi::select('presensi.*', 'users.name as full_name')
        ->join('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('account.presensi.index', compact('presensi', 'maintenances'));
  }

  public function create()
  {
    $user = Auth::user();
    $currentTime = now()->format('H:i:s');

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::where('company', $user->company)
        ->select('id', 'full_name')
        ->get();
      return view('account.presensi.create', compact('users', 'currentTime'));
    } else {
      $users = User::where('id', $user->id)
        ->select('id', 'full_name')
        ->get();
      return view('account.presensi.create', compact('users', 'currentTime'));
    }
  }

  public function store(Request $request)
  {
    $user = Auth::user();

    $this->validate(
      $request,
      [
        'status' => 'required',
        'gambar' => 'required|max:5120',
      ],
      [
        'status.required' => 'Masukkan Status Presensi Karyawan!',
        'gambar.required' => 'Masukkan Gambar Untuk Bukti Presensi!',
        'gambar.max' => 'Ukuran gambar tidak boleh melebihi 5MB!',
      ]
    );

    //menyinpan image di path
    $imagePath = null;

    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
    }
    //end

    $currentTime = now()->format('H:i:s');
    $clientDateTime = Carbon::parse($request->input('client_date_time'));

    // Check if the current time is between 07:00:00 and 09:00:00
    if ($currentTime >= '07:00:00' && $currentTime <= '09:00:00') {
      // Use the provided status if available, or set to 'terlambat' by default
      $status = $request->input('status', 'terlambat');
    } else if ($currentTime > '09:00:00') {
      // Check if an existing record for the user and date exists with specific statuses
      $existingHadirRecord = Presensi::where('user_id', $request->input('user_id'))
        ->whereIn('status', ['hadir', 'dinas luar kota', 'remote'])
        ->whereDate('created_at', $clientDateTime->format('Y-m-d'))
        ->exists();

      if (!$existingHadirRecord) {
        $status = 'hadir';
      } else {
        $status = 'terlambat';
      }
      // if (!$existingHadirRecord) {

      //   $status = 'terlambat';
      // }
    } else {
      // Use the provided status if available, or set to 'terlambat' by default
      $status = $request->input('status', 'terlambat');
    }

    //$clientDateTime = Carbon::parse($request->input('client_date_time'));
    $save = Presensi::create([
      'user_id' => $request->input('user_id'),
      'status' => $request->input('status'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown', // City is just an example; you can use other location data
      'gambar' => $imagePath ?? null, // Store the image path
      'created_at' => $clientDateTime,

    ]);

    // Redirect with success or error message
    if ($save) {
      $employee = User::find($request->input('user_id'));

      //if ($employee && $employee->email) {
      //  Mail::to($employee->email)->send(new PresensiNotification($employee->full_name));
      //}
      return redirect()->route('account.presensi.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.presensi.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
    }
  }

  public function detail($id)
  {
    $user = Auth::user();
    $presensi = Presensi::findOrFail($id);

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::join('presensi', 'users.id', '=', 'presensi.user_id')
        ->where('users.company', $user->company)
        ->get(['users.*']);
    } else {
      $users = User::where('id', $presensi->user_id)->get();
    }
    return view('account.presensi.detail', compact('presensi', 'users')); // Sesuaikan path template dengan benar
  }

  public function edit($id)
  {
    $user = Auth::user();
    $presensi = Presensi::findOrFail($id);

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::join('presensi', 'users.id', '=', 'presensi.user_id')
        ->where('users.company', $user->company)
        ->get(['users.*']);
    } else {
      $users = User::where('id', $presensi->user_id)->get();
    }

    return view('account.presensi.edit', compact('presensi', 'users')); // Sesuaikan path template dengan benar
  }

  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $presensi = Presensi::findOrFail($id);
    //$this->validate(
    //  $request,
    //  [
    //    'status' => 'required',
    //    'gambar' => 'required|max:5120',
    //  ],
    //  [
    //    'status.required' => 'Masukkan Status Presensi Karyawan!',
    //    'gambar.required' => 'Masukkan Gambar Untuk Bukti Presensi!',
    //    'gambar.max' => 'Ukuran gambar tidak boleh melebihi 5MB!',
    //  ]
    //);

    //save image to path
    $imagePath = null;

    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
    } else {
      $imagePath = $presensi->gambar;
    }
    //end

    $presensi->update([
      'status' => $presensi->status ?? $request->input('status'),
      'status_pulang' => $request->input('status_pulang'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown', // City is just an example; you can use other location data
      'gambar' => $imagePath ?? null, // Store the image path
      'time_pulang' => now(),
    ]);

    // Redirect with success or error message
    if ($presensi) {
      return redirect()->route('account.presensi.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.presensi.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
    }
  }

  public function destroy($id)
  {
    try {
      $presensi = Presensi::find($id);

      if ($presensi) {
        $presensi->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
      } else {
        return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
      }
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
    }
  }

  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    $presensi = DB::table('presensi')
      ->select('presensi.id', 'presensi.status', 'presensi.note', 'presensi.gambar', 'presensi.created_at', 'users.id as user_id', 'users.full_name as full_name')
      ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->where(function ($query) use ($search) {
        $query->where('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('presensi.status', 'LIKE', '%' . $search . '%')
          ->orWhere(function ($subquery) use ($search) {
            // Menggunakan DATE_FORMAT untuk mendapatkan nama hari, tanggal, nama bulan, tahun, dan waktu
            $subquery->whereRaw('LOWER(DATE_FORMAT(presensi.created_at, "%W %d %M %Y %H:%i")) LIKE ?', ['%' . strtolower($search) . '%']);
          });
      })
      ->orderBy('presensi.created_at', 'DESC')
      ->paginate(10);

    $presensi->appends(['q' => $search]);

    if ($presensi->isEmpty()) {
      return redirect()->route('account.presensi.index')->with('error', 'Data presensi Karyawan tidak ditemukan.');
    }

    return view('account.presensi.index', compact('presensi'));
  }
}
