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
use App\Mail\CreatePresensiMail;
use App\Mail\UpdatePresensiMail;
use App\Mail\NotifPresensiMail;
use Illuminate\Support\Facades\Mail;

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

  public function index(Request $request)
  {
    $user = Auth::user();

    $currentDate = Carbon::now()->format('Y-m-d');
    $startOfDay = Carbon::now()->startOfDay();
    $endOfDay = Carbon::now()->endOfDay();

    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');

    if (!$startDate || !$endDate) {
      $currentMonth = date('Y-m-01 00:00:00');
      $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
    } else {
      $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
      $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
    }

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan' || $user->level == 'trainer') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
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

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the presensi data for the logged-in user
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('account.presensi.index', compact('presensi', 'maintenances', 'startDate', 'endDate', 'presensihariini'));
  }

  public function filter(Request $request)
  {
    $user = Auth::user();
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');

    $currentDate = Carbon::now()->format('Y-m-d');
    $startOfDay = Carbon::now()->startOfDay();
    $endOfDay = Carbon::now()->endOfDay();

    if (!$startDate || !$endDate) {
      $currentMonth = date('Y-m-01 00:00:00');
      $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
    } else {
      $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
      $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
    }

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan' || $user->level == 'trainer') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the presensi data for the logged-in user
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('account.presensi.index', compact('presensi', 'maintenances', 'startDate', 'endDate', 'presensihariini'));
  }

  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    $currentDate = Carbon::now()->format('Y-m-d');
    $startOfDay = Carbon::now()->startOfDay();
    $endOfDay = Carbon::now()->endOfDay();

    // Default date range to the current month if not provided
    $startDate = $request->get('start_date') ?? date('Y-m-01');
    $endDate = $request->get('end_date') ?? date('Y-m-t');

    if (Auth::user()->level == 'manager') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$startDate, $endDate])
        ->where(function ($query) use ($search) {
          $query->where('users.full_name', 'LIKE', '%' . $search . '%')
            ->orWhere('presensi.status', 'LIKE', '%' . $search . '%')
            ->orWhere('presensi.status_pulang', 'LIKE', '%' . $search . '%')
            ->orWhere(function ($subquery) use ($search) {
              $subquery->whereRaw('LOWER(DATE_FORMAT(presensi.created_at, "%W %d %M %Y %H:%i")) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        })
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)
        ->whereBetween('presensi.created_at', [$startDate, $endDate])
        ->where(function ($query) use ($search) {
          $query->where('users.full_name', 'LIKE', '%' . $search . '%')
            ->orWhere('presensi.status', 'LIKE', '%' . $search . '%')
            ->orWhere('presensi.status_pulang', 'LIKE', '%' . $search . '%')
            ->orWhere(function ($subquery) use ($search) {
              $subquery->whereRaw('LOWER(DATE_FORMAT(presensi.created_at, "%W %d %M %Y %H:%i")) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        })
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensihariini = DB::table('presensi')
        ->select(
          'presensi.id',
          'presensi.status',
          'presensi.status_pulang',
          'presensi.note',
          'presensi.gambar',
          'presensi.gambar_pulang',
          'presensi.time_pulang',
          'presensi.status_pulang',
          'presensi.latitude',
          'presensi.longitude',
          'presensi.created_at',
          'presensi.updated_at',
          'users.id as user_id',
          'users.full_name as full_name',
          'users.telp as telp'
        )
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the presensi data for the logged-in user
        ->whereBetween('presensi.created_at', [$startOfDay, $endOfDay])
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    $presensi->appends(['q' => $search, 'start_date' => $startDate, 'end_date' => $endDate]);

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    if ($presensi->isEmpty()) {
      return redirect()->route('account.presensi.index')->with('error', 'Data Presensi tidak ditemukan.');
    }
    return view('account.presensi.index', compact('presensi', 'maintenances', 'startDate', 'endDate', 'presensihariini'));
  }

  public function create()
  {
    $user = Auth::user();
    $currentTime = now()->format('H:i:s');

    $users = User::where('company', $user->company)
      ->select('id', 'full_name')
      ->get();

    return view('account.presensi.create', compact('users', 'currentTime'));
  }

  public function store(Request $request)
  {
    $user = Auth::user();

    $this->validate(
      $request,
      [
        'status' => 'required',
        'gambar' => 'max:5120',
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

    // Mengkonvert ke angka dari status
    $alpha = $request->input('status') === 'alpha' ? 1 : null;
    $Hadir = $request->input('status') === 'hadir' ? 1 : null;
    $campjogja = $request->input('status') === 'camp jogja' ? 1 : null;
    $perjalananjawa = $request->input('status') === 'perjalanan luar kota jawa' ? 0.25 : null;
    $perjalananluarjawa = $request->input('status') === 'perjalanan luar kota luar jawa' ? 0.5 : null;
    $campluarkota = $request->input('status') === 'camp luar kota' ? 1 : null;
    $remote = $request->input('status') === 'remote' ? 1 : null;
    $izin = $request->input('status') === 'izin' ? 1 : null;
    // End

    $userRole = $user->level;
    $timePulang = null;
    $statusPulang = null;

    if ($userRole === 'manager' && $request->input('status_pulang') === 'pulang') {
      $timePulang = now(); // Use Carbon to get the current time
      $statusPulang = $request->input('status_pulang');
    }

    $clientDateTime = Carbon::parse($request->input('client_date_time'));

    // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
    $currentTime = now()->format('H:i:s');

    // Get the user's location from the request
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    $save = Presensi::create([
      'user_id' => $request->input('user_id'),
      'status' => $request->input('status'),
      'status_pulang' => $statusPulang,
      'time_pulang' => $timePulang,
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown',
      'gambar' => $imagePath ?? null,
      'latitude' => $latitude,
      'longitude' => $longitude,
      'created_at' => $clientDateTime,
      'alpha' => $alpha,
      'hadir' => $Hadir,
      'camp_jogja' => $campjogja,
      'perjalanan_jawa' => $perjalananjawa,
      'perjalanan_luar_jawa' => $perjalananluarjawa,
      'camp_luar_kota' => $campluarkota,
      'remote' => $remote,
      'izin' => $izin,
    ]);


    if ($save) {
      // $user = User::findOrFail($request->input('user_id'));
      // $appName = 'Rumah Scopus Foundation';
      // Mail::to($user->email)->send(new CreatePresensiMail($user, $save, $appName));
      return redirect()->route('account.dashboard.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.dashboard.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
    }
  }

  public function detail($id)
  {
    $user = Auth::user();
    $presensi = Presensi::findOrFail($id);

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
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

    // Mengkonvert ke angka dari status
    $alpha = $request->input('status') === 'alpha' ? 1 : null;
    $Hadir = $request->input('status') === 'hadir' ? 1 : null;
    $campjogja = $request->input('status') === 'camp jogja' ? 1 : null;
    $perjalananjawa = $request->input('status') === 'perjalanan luar kota jawa' ? 0.25 : null;
    $perjalananluarjawa = $request->input('status') === 'perjalanan luar kota luar jawa' ? 0.5 : null;
    $campluarkota = $request->input('status') === 'camp luar kota' ? 1 : null;
    $remote = $request->input('status') === 'remote' ? 1 : null;
    $izin = $request->input('status') === 'izin' ? 1 : null;

    //save image to path
    $imagePath = null;

    if ($request->hasFile('gambar_pulang')) {
      $image = $request->file('gambar_pulang');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName;
      $image->move(public_path('images'), $imageName);
    } else {
      $imagePath = $presensi->gambar_pulang;
    }
    //end

    $presensi->update([
      'status' => $request->input('status'),
      'status_pulang' => $request->input('status_pulang'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown',
      'gambar_pulang' => $imagePath ?? null,
      'time_pulang' => $request->has('status_pulang') ? now() : null,
      'alpha' => $request->input('status') === 'alpha' ? 1 : $alpha,
      'hadir' => $request->input('status') === 'hadir' ? 1 : $Hadir,
      'camp_jogja' => $request->input('status') === 'camp jogja' ? 1 : $campjogja,
      'perjalanan_jawa' => $request->input('status') === 'perjalanan luar kota jawa' ? 0.25 : $perjalananjawa,
      'perjalanan_luar_jawa' => $request->input('status') === 'perjalanan luar kota luar jawa' ? 0.5 : $perjalananluarjawa,
      'camp_luar_kota' => $request->input('status') === 'camp luar kota' ? 1 : $campluarkota,
      'remote' => $request->input('status') === 'remote' ? 1 : $remote,
      'izin' => $request->input('status') === 'izin' ? 1 : $izin,
    ]);

    // Redirect with success or error message
    $user = User::findOrFail($presensi->user_id);
    if ($presensi) {
      // $appName = 'Rumah Scopus Foundation';
      // Mail::to($user->email)->send(new UpdatePresensiMail($user, $presensi, $appName));

      return redirect()->route('account.dashboard.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
    } else {
      return redirect()->route('account.dashboard.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
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

  public function getUserPhone($userId)
  {
    $user = User::find($userId);

    return response()->json(['phone_number' => $user->telp]);
  }

  public function downloadPdf(Request $request)
  {
    $user = Auth::user();
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');
    $searchQuery = $request->input('q');

    if (!$startDate || !$endDate) {
      // Jika tanggal_awal atau tanggal_akhir tidak ada dalam request, gunakan rentang bulan ini
      $currentMonth = date('Y-m-01 00:00:00');
      $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
    } else {
      // Jika tanggal_awal dan tanggal_akhir ada dalam request, gunakan rentang tersebut
      $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
      $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
    }

    $presensi = DB::table('presensi')
      ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->whereBetween('presensi.created_at', [$currentMonth, $nextMonth])
      ->when($searchQuery, function ($query) use ($searchQuery) {
        $query->where(function ($subquery) use ($searchQuery) {
          // Adjust this part based on your search requirements
          $subquery->where('users.full_name', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('presensi.status', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('presensi.status_pulang', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere(function ($dateSubquery) use ($searchQuery) {
              $dateSubquery->whereRaw('LOWER(DATE_FORMAT(presensi.created_at, "%W %d %M %Y %H:%i")) LIKE ?', ['%' . strtolower($searchQuery) . '%']);
            });
        });
      })
      ->orderBy('presensi.created_at', 'DESC')
      ->get();

    // Additional data retrieval for 'maintenance'
    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    $html = view('account.presensi.pdf', compact('presensi', 'user', 'startDate', 'endDate'))->render();

    // Instantiate Dompdf with the default configuration
    $dompdf = new Dompdf();

    // Load the HTML content into Dompdf
    $dompdf->loadHtml($html);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the PDF
    $dompdf->render();

    // Get the output as a string
    $output = $dompdf->output();

    // Set the response headers
    $headers = [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="Laporan-Presensi_' . date('d-m-Y') . '.pdf"',
    ];
    return Response::make($dompdf->output(), 200, $headers);
  }
}
