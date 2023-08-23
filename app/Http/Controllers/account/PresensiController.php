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

    $currentMonth = now()->startOfMonth();
    $nextMonth = now()->addMonth()->startOfMonth();

    if ($user->level == 'manager' || $user->level == 'staff') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.note', 'presensi.gambar', 'presensi.created_at', 'users.id as user_id', 'users.full_name as full_name')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan') {
      $presensi = DB::table('presensi')
        ->select('presensi.id', 'presensi.status', 'presensi.note', 'presensi.gambar', 'presensi.created_at', 'users.id as user_id', 'users.full_name as full_name')
        ->leftJoin('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    } else {
      $presensi = Presensi::select('presensi.*', 'users.name as full_name')
        ->join('users', 'presensi.user_id', '=', 'users.id')
        ->where('presensi.user_id', $user->id)
        ->orderBy('presensi.created_at', 'DESC')
        ->paginate(10);
    }

    return view('account.presensi.index', compact('presensi'));
  }

  public function create()
  {
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::where('company', $user->company)
        ->select('id', 'full_name')
        ->get();
      return view('account.presensi.create', compact('users'));
    } else {
      $users = User::where('id', $user->id)
        ->select('id', 'full_name')
        ->get();
      return view('account.presensi.create', compact('users'));
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
    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName;
      $image->storeAs('public/assets/img/presensi', $imageName); // Store the image

    }

    $client = new Client();
    $ipinfoResponse = $client->get('http://ipinfo.io');
    $ipinfoData = json_decode($ipinfoResponse->getBody(), true);

    $save = Presensi::create([
      'user_id' => $request->input('user_id'),
      'status' => $request->input('status'),
      'note' => $request->input('note'),
      'lokasi' => $request->input('lokasi'),
      'lokasi' => $ipinfoData['city'] ?? 'Unknown', // City is just an example; you can use other location data
      'gambar' => $imagePath ?? null, // Store the image path
    ]);

    // Redirect with success or error message
    if ($save) {
      return redirect()->route('account.presensi.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.presensi.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
    }
  }
}
