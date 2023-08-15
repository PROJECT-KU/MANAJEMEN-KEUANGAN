<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Gaji;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;

class GajiController extends Controller
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
      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan') {
      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->whereBetween('gaji.created_at', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    } else {
      $gaji = Gaji::select('gaji.*', 'users.name as full_name')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)
        ->whereBetween('gaji.created_at', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    }

    return view('account.gaji.index', compact('gaji'));
  }


  public function create()
  {
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::where('company', $user->company)
        ->select('id', 'full_name', 'nik', 'norek', 'bank', 'telp')
        ->get();
      return view('account.gaji.create', compact('users'));
    } else {
      $users = User::where('id', $user->id)
        ->select('id', 'full_name', 'nik', 'norek', 'bank', 'telp')
        ->get();
      return view('account.gaji.create', compact('users'));
    }
  }



  public function store(Request $request)
  {
    $user = Auth::user();

    $id_transaksi = $this->generateRandomId(5);

    $this->validate(
      $request,
      //[
      //  'gaji_pokok' => 'required',
      //],
      [
        'gaji_pokok.required' => 'Masukkan Gaji Pokok Karyawan!',
      ]
    );

    $gaji_pokok = $request->input('gaji_pokok');
    $gaji_pokok = empty($gaji_pokok) ? 0 : str_replace(",", "", $gaji_pokok); // Convert to numeric value or set to 0 if empty

    $lembur = $request->input('lembur');
    $lembur = empty($lembur) ? 0 : str_replace(",", "", $lembur);

    $bonus = $request->input('bonus');
    $bonus = empty($bonus) ? 0 : str_replace(",", "", $bonus);

    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? 0 : str_replace(",", "", $bonus_luar);

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);

    $jumlah_lembur = $request->input('jumlah_lembur') ?? 0;
    $jumlah_bonus = $request->input('jumlah_bonus');
    $jumlah_bonus_luar = $request->input('jumlah_bonus_luar');

    $total_lembur = $lembur * $jumlah_lembur;
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    $total_bonus = ($bonus * $jumlah_bonus_luar) + ($bonus_luar * $jumlah_bonus);
    $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $potongan = $request->input('potongan');
    $potongan = empty($potongan) ? 0 : str_replace(",", "", $potongan);

    $total = $gaji_pokok + $total_lembur + $total_bonus + $tunjangan - $potongan;
    $total = empty($total) ? 0 : str_replace(",", "", $total);


    $save = Gaji::create([
      'id_transaksi' => $id_transaksi,
      'user_id' => $request->input('user_id'),
      'gaji_pokok' => $gaji_pokok,
      'lembur' => $lembur,
      'bonus' => $bonus,
      'bonus_luar' => $bonus_luar,
      'operasional' => $operasional,
      'tunjangan' => $tunjangan,
      'jumlah_lembur' => $jumlah_lembur,
      'jumlah_bonus' => $jumlah_bonus,
      'jumlah_bonus_luar' => $jumlah_bonus_luar,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
    ]);

    // Redirect with success or error message
    if ($save) {
      // Get the ID of the newly created data
      $gajiId = $save->id;

      // Redirect to the detail page for the newly created data with SweetAlert notification
      return Redirect::route(
        'account.gaji.detail',
        ['id' => $gajiId]
      )->with(['success' => 'Data Berhasil Disimpan!']);
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with(['error' => 'Data Gagal Disimpan!']);
    }
  }

  public function edit($id)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::join('gaji', 'users.id', '=', 'gaji.user_id')
        ->where('users.company', $user->company)
        ->get(['users.*']);
    } else {
      $users = User::where('id', $gaji->user_id)->get();
    }

    return view('account.gaji.edit', compact('gaji', 'users')); // Sesuaikan path template dengan benar
  }

  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id);
    //$id_transaksi = $this->generateRandomId(5);

    $this->validate(
      $request,
      //[
      //  'gaji_pokok' => 'required',
      //],
      [
        'gaji_pokok.required' => 'Masukkan Gaji Pokok Karyawan!',
      ]
    );

    $gaji_pokok = $request->input('gaji_pokok');
    $gaji_pokok = empty($gaji_pokok) ? 0 : str_replace(",", "", $gaji_pokok); // Convert to numeric value or set to 0 if empty

    $lembur = $request->input('lembur');
    $lembur = empty($lembur) ? 0 : str_replace(",", "", $lembur);

    $bonus = $request->input('bonus');
    $bonus = empty($bonus) ? 0 : str_replace(",", "", $bonus);

    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? 0 : str_replace(",", "", $bonus_luar);

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);

    $jumlah_lembur = $request->input('jumlah_lembur');
    $jumlah_bonus = $request->input('jumlah_bonus');

    $total_lembur = $lembur * $jumlah_lembur;
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    $total_bonus = $bonus + $bonus_luar * $jumlah_bonus;
    $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $potongan = $request->input('potongan');
    $potongan = empty($potongan) ? 0 : str_replace(",", "", $potongan);

    $total = $gaji_pokok + $total_lembur + $total_bonus + $tunjangan - $potongan;
    $total = empty($total) ? 0 : str_replace(",", "", $total);

    $existingUserId = $gaji->user_id;

    $gaji->update([
      //'id_transaksi' => $id_transaksi,
      'user_id' => $existingUserId,
      'gaji_pokok' => $gaji_pokok,
      'lembur' => $lembur,
      'bonus' => $bonus,
      'bonus_luar' => $bonus_luar,
      'operasional' => $operasional,
      'tunjangan' => $tunjangan,
      'jumlah_lembur' => $jumlah_lembur,
      'jumlah_bonus' => $jumlah_bonus,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
    ]);

    // Redirect with success or error message
    if ($gaji) {
      return redirect()->route(
        'account.gaji.detail',
        ['id' => $id]
      )->with(
        ['success' => 'Data Berhasil Disimpan!']
      );
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with(['error' => 'Data Gagal Disimpan!']);
    }
  }

  public function detail($id)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

    if ($user->level == 'manager' || $user->level == 'staff') {
      $users = User::join('gaji', 'users.id', '=', 'gaji.user_id')
        ->where('users.company', $user->company)
        ->get(['users.*']);
    } else {
      $users = User::where('id', $gaji->user_id)->get();
    }
    return view('account.gaji.detail', compact('gaji', 'users')); // Sesuaikan path template dengan benar
  }


  public function destroy($id)
  {
    try {
      $gaji = Gaji::find($id);

      if ($gaji) {
        $gaji->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
      } else {
        return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
      }
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
    }
  }
}
