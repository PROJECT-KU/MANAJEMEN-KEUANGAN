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

  public function index(Request $request)
  {
    $user = Auth::user();
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');

    if (!$startDate || !$endDate) {
      $currentMonth = date('Y-m-01 00:00:00');
      $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
    } else {
      $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
      $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
    }

    if ($user->level == 'manager' || $user->level == 'staff') {
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
    } else if ($user->level == 'admin') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.status_pulang', 'presensi.note', 'presensi.gambar', 'presensi.gambar_pulang', 'presensi.time_pulang', 'presensi.status_pulang', 'presensi.latitude', 'presensi.longitude', 'presensi.created_at', 'presensi.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
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

    return view('account.presensi.index', compact('presensi', 'maintenances', 'startDate', 'endDate'));
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

    // ... (validasi gambar dan lainnya)

    $clientDateTime = Carbon::parse($request->input('client_date_time'));

    // Mendapatkan kode hari (1 untuk Senin, 2 untuk Selasa, dst.)
    $currentDay = $clientDateTime->dayOfWeek;

    // Mendapatkan waktu saat ini dalam format "HH:MM:SS"
    $currentTime = now()->format('H:i:s');

    // Inisialisasi status default
    $status = 'terlambat';

    // Logika berdasarkan hari dan waktu
    if ($currentDay == 1 && ($currentTime >= '08:00:00' && $currentTime <= '10:00:00')) {
      $status = 'hadir';
    } elseif (in_array($currentDay, [2, 3])) {
      $status = 'libur';
    } elseif ($currentDay == 4 && ($currentTime >= '12:00:00' && $currentTime <= '14:00:00')) {
      $status = 'hadir';
    } elseif (in_array($currentDay, [5, 6, 7]) && ($currentTime >= '07:00:00' && $currentTime <= '08:30:00')) {
      $status = 'hadir';
    }

    // Get the user's location from the request
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    //$clientDateTime = Carbon::parse($request->input('client_date_time'));
    $save = Presensi::create([
      'user_id' => $request->input('user_id'),
      'status' => $request->input('status'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown', // City is just an example; you can use other location data
      'gambar' => $imagePath ?? null, // Store the image path
      'latitude' => $latitude,
      'longitude' => $longitude,
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

    if ($request->hasFile('gambar_pulang')) {
      $image = $request->file('gambar_pulang');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar_pulang ke direktori public/images
    } else {
      $imagePath = $presensi->gambar_pulang;
    }
    //end

    $presensi->update([
      'status' => $presensi->status ?? $request->input('status'),
      'status_pulang' => $request->input('status_pulang'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown', // City is just an example; you can use other location data
      'gambar_pulang' => $imagePath ?? null, // Store the image path
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
      ->select('presensi.id', 'presensi.status', 'presensi.note', 'presensi.gambar', 'presensi.created_at', 'presensi.time_pulang', 'presensi.status_pulang', 'users.id as user_id', 'users.full_name as full_name', 'presensi.time_pulang')
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

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();


    return view('account.presensi.index', compact('presensi', 'maintenances'));
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
