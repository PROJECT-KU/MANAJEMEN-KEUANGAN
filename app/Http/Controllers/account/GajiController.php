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
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    } else if ($user->level == 'karyawan') {
      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    } else {
      $gaji = Gaji::select('gaji.*', 'users.name as full_name')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    }

    return view('account.gaji.index', compact('gaji'));
  }

  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      $gaji = DB::table('gaji')
      ->select(/* ... your columns ... */)
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->where(function ($query) use ($search) {
          $query->where('gaji.id_transaksi', 'LIKE', '%' . $search . '%')
            ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.norek', 'LIKE', '%' . $search . '%')
            ->orWhere(DB::raw("CAST(REPLACE(gaji.total, 'Rp', '') AS DECIMAL(10, 2))"), '=', str_replace(['Rp', '.', ','], '', $search))
            ->orWhere(DB::raw("DATE_FORMAT(gaji.tanggal, '%Y-%m-%d')"), '=', date('Y-m-d', strtotime($search)));
        })
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
      $gaji->appends(['q' => $search]);
    }

    if ($gaji->isEmpty()) {
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji Karyawan tidak ditemukan.');
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

    //lembur
    $lembur = $request->input('lembur');
    $lembur = empty($lembur) ? 0 : str_replace(",", "", $lembur);

    $lembur1 = $request->input('lembur1');
    $lembur1 = empty($lembur1) ? 0 : str_replace(",", "", $lembur1);

    $lembur2 = $request->input('lembur2');
    $lembur2 = empty($lembur2) ? 0 : str_replace(",", "", $lembur2);

    $lembur3 = $request->input('lembur3');
    $lembur3 = empty($lembur3) ? 0 : str_replace(",", "", $lembur3);

    $lembur4 = $request->input('lembur4');
    $lembur4 = empty($lembur4) ? 0 : str_replace(",", "", $lembur4);

    $lembur5 = $request->input('lembur5');
    $lembur5 = empty($lembur5) ? 0 : str_replace(",", "", $lembur5);

    $lembur6 = $request->input('lembur6');
    $lembur6 = empty($lembur6) ? 0 : str_replace(",", "", $lembur6);

    $lembur7 = $request->input('lembur7');
    $lembur7 = empty($lembur7) ? 0 : str_replace(",", "", $lembur7);

    $lembur8 = $request->input('lembur8');
    $lembur8 = empty($lembur8) ? 0 : str_replace(",", "", $lembur8);

    $lembur9 = $request->input('lembur9');
    $lembur9 = empty($lembur9) ? 0 : str_replace(",", "", $lembur9);

    $lembur10 = $request->input('lembur10');
    $lembur10 = empty($lembur10) ? 0 : str_replace(",", "", $lembur10);
    //end

    //fee bonus dalam kota
    $bonus = $request->input('bonus');
    $bonus = empty($bonus) ? 0 : str_replace(",", "", $bonus);

    $bonus1 = $request->input('bonus1');
    $bonus1 = empty($bonus1) ? 0 : str_replace(",", "", $bonus1);

    $bonus2 = $request->input('bonus2');
    $bonus2 = empty($bonus2) ? 0 : str_replace(",", "", $bonus2);

    $bonus3 = $request->input('bonus3');
    $bonus3 = empty($bonus3) ? 0 : str_replace(",", "", $bonus3);

    $bonus4 = $request->input('bonus4');
    $bonus4 = empty($bonus4) ? 0 : str_replace(",", "", $bonus4);

    $bonus5 = $request->input('bonus5');
    $bonus5 = empty($bonus5) ? 0 : str_replace(",", "", $bonus5);

    $bonus6 = $request->input('bonus6');
    $bonus6 = empty($bonus6) ? 0 : str_replace(",", "", $bonus6);

    $bonus7 = $request->input('bonus7');
    $bonus7 = empty($bonus7) ? 0 : str_replace(",", "", $bonus7);

    $bonus8 = $request->input('bonus8');
    $bonus8 = empty($bonus8) ? 0 : str_replace(",", "", $bonus8);

    $bonus9 = $request->input('bonus9');
    $bonus9 = empty($bonus9) ? 0 : str_replace(",", "", $bonus9);

    $bonus10 = $request->input('bonus10');
    $bonus10 = empty($bonus10) ? 0 : str_replace(",", "", $bonus10);
    //end fee bonus dalam kota

    //fee bonus luar kota
    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? 0 : str_replace(",", "", $bonus_luar);

    $bonus_luar1 = $request->input('bonus_luar1');
    $bonus_luar1 = empty($bonus_luar1) ? 0 : str_replace(",", "", $bonus_luar1);

    $bonus_luar2 = $request->input('bonus_luar2');
    $bonus_luar2 = empty($bonus_luar2) ? 0 : str_replace(",", "", $bonus_luar2);

    $bonus_luar3 = $request->input('bonus_luar3');
    $bonus_luar3 = empty($bonus_luar3) ? 0 : str_replace(",", "", $bonus_luar3);

    $bonus_luar4 = $request->input('bonus_luar4');
    $bonus_luar4 = empty($bonus_luar4) ? 0 : str_replace(",", "", $bonus_luar4);

    $bonus_luar5 = $request->input('bonus_luar5');
    $bonus_luar5 = empty($bonus_luar5) ? 0 : str_replace(",", "", $bonus_luar5);

    $bonus_luar6 = $request->input('bonus_luar6');
    $bonus_luar6 = empty($bonus_luar6) ? 0 : str_replace(",", "", $bonus_luar6);

    $bonus_luar7 = $request->input('bonus_luar7');
    $bonus_luar7 = empty($bonus_luar7) ? 0 : str_replace(",", "", $bonus_luar7);

    $bonus_luar8 = $request->input('bonus_luar8');
    $bonus_luar8 = empty($bonus_luar8) ? 0 : str_replace(",", "", $bonus_luar8);

    $bonus_luar9 = $request->input('bonus_luar9');
    $bonus_luar9 = empty($bonus_luar9) ? 0 : str_replace(",", "", $bonus_luar9);

    $bonus_luar10 = $request->input('bonus_luar10');
    $bonus_luar10 = empty($bonus_luar10) ? 0 : str_replace(",", "", $bonus_luar10);
    //end fee bonus luar kota

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);

    //jumlah lembur
    $jumlah_lembur = $request->input('jumlah_lembur') ?? 0;
    $jumlah_lembur1 = $request->input('jumlah_lembur1') ?? 0;
    $jumlah_lembur2 = $request->input('jumlah_lembur2') ?? 0;
    $jumlah_lembur3 = $request->input('jumlah_lembur3') ?? 0;
    $jumlah_lembur4 = $request->input('jumlah_lembur4') ?? 0;
    $jumlah_lembur5 = $request->input('jumlah_lembur5') ?? 0;
    $jumlah_lembur6 = $request->input('jumlah_lembur6') ?? 0;
    $jumlah_lembur7 = $request->input('jumlah_lembur7') ?? 0;
    $jumlah_lembur8 = $request->input('jumlah_lembur8') ?? 0;
    $jumlah_lembur9 = $request->input('jumlah_lembur9') ?? 0;
    $jumlah_lembur10 = $request->input('jumlah_lembur10') ?? 0;
    //end jumlah lembur

    //jumlah bonus dalam kota
    $jumlah_bonus = $request->input('jumlah_bonus') ?? 0;
    $jumlah_bonus1 = $request->input('jumlah_bonus1') ?? 0;
    $jumlah_bonus2 = $request->input('jumlah_bonus2') ?? 0;
    $jumlah_bonus3 = $request->input('jumlah_bonus3') ?? 0;
    $jumlah_bonus4 = $request->input('jumlah_bonus4') ?? 0;
    $jumlah_bonus5 = $request->input('jumlah_bonus5') ?? 0;
    $jumlah_bonus6 = $request->input('jumlah_bonus6') ?? 0;
    $jumlah_bonus7 = $request->input('jumlah_bonus7') ?? 0;
    $jumlah_bonus8 = $request->input('jumlah_bonus8') ?? 0;
    $jumlah_bonus9 = $request->input('jumlah_bonus9') ?? 0;
    $jumlah_bonus10 = $request->input('jumlah_bonus10') ?? 0;
    //end jumlah bonus dalam kota

    //jumlah bonus luar kota
    $jumlah_bonus_luar = $request->input('jumlah_bonus_luar') ?? 0;
    $jumlah_bonus_luar1 = $request->input('jumlah_bonus_luar1') ?? 0;
    $jumlah_bonus_luar2 = $request->input('jumlah_bonus_luar2') ?? 0;
    $jumlah_bonus_luar3 = $request->input('jumlah_bonus_luar3') ?? 0;
    $jumlah_bonus_luar4 = $request->input('jumlah_bonus_luar4') ?? 0;
    $jumlah_bonus_luar5 = $request->input('jumlah_bonus_luar5') ?? 0;
    $jumlah_bonus_luar6 = $request->input('jumlah_bonus_luar6') ?? 0;
    $jumlah_bonus_luar7 = $request->input('jumlah_bonus_luar7') ?? 0;
    $jumlah_bonus_luar8 = $request->input('jumlah_bonus_luar8') ?? 0;
    $jumlah_bonus_luar9 = $request->input('jumlah_bonus_luar9') ?? 0;
    $jumlah_bonus_luar10 = $request->input('jumlah_bonus_luar10') ?? 0;
    //end jumlah bonus luar kota

    $total_lembur = ($lembur * $jumlah_lembur) + ($lembur1 * $jumlah_lembur1) + ($lembur2 * $jumlah_lembur2) + ($lembur3 * $jumlah_lembur3) + ($lembur4 * $jumlah_lembur4) + ($lembur5 * $jumlah_lembur5) + ($lembur6 * $jumlah_lembur6) +
    ($lembur7 * $jumlah_lembur7) + ($lembur8 * $jumlah_lembur8) + ($lembur9 * $jumlah_lembur9) + ($lembur10 * $jumlah_lembur10);
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    $total_bonus =
    ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) +
    ($bonus8 * $jumlah_bonus8) + ($bonus9 * $jumlah_bonus9) + ($bonus10 * $jumlah_bonus10) +
    ($bonus_luar * $jumlah_bonus_luar) + ($bonus_luar1 * $jumlah_bonus_luar1) + ($bonus_luar2 * $jumlah_bonus_luar2) + ($bonus_luar3 * $jumlah_bonus_luar3) + ($bonus_luar4 * $jumlah_bonus_luar4) + ($bonus_luar5 * $jumlah_bonus_luar5) +
    ($bonus_luar6 * $jumlah_bonus_luar6) + ($bonus_luar7 * $jumlah_bonus_luar7) + ($bonus_luar8 * $jumlah_bonus_luar8) + ($bonus_luar9 * $jumlah_bonus_luar9) + ($bonus_luar10 * $jumlah_bonus_luar10);
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
      'lembur1' => $lembur1,
      'lembur2' => $lembur2,
      'lembur3' => $lembur3,
      'lembur4' => $lembur4,
      'lembur5' => $lembur5,
      'lembur6' => $lembur6,
      'lembur7' => $lembur7,
      'lembur8' => $lembur8,
      'lembur9' => $lembur9,
      'lembur10' => $lembur10,
      'jumlah_lembur' => $jumlah_lembur,
      'jumlah_lembur1' => $jumlah_lembur1,
      'jumlah_lembur2' => $jumlah_lembur2,
      'jumlah_lembur3' => $jumlah_lembur3,
      'jumlah_lembur4' => $jumlah_lembur4,
      'jumlah_lembur5' => $jumlah_lembur5,
      'jumlah_lembur6' => $jumlah_lembur6,
      'jumlah_lembur7' => $jumlah_lembur7,
      'jumlah_lembur8' => $jumlah_lembur8,
      'jumlah_lembur9' => $jumlah_lembur9,
      'jumlah_lembur10' => $jumlah_lembur10,
      'bonus' => $bonus,
      'bonus1' => $bonus1,
      'bonus2' => $bonus2,
      'bonus3' => $bonus3,
      'bonus4' => $bonus4,
      'bonus5' => $bonus5,
      'bonus6' => $bonus6,
      'bonus7' => $bonus7,
      'bonus8' => $bonus8,
      'bonus9' => $bonus9,
      'bonus10' => $bonus10,
      'bonus_luar' => $bonus_luar,
      'bonus_luar1' => $bonus_luar1,
      'bonus_luar2' => $bonus_luar2,
      'bonus_luar3' => $bonus_luar3,
      'bonus_luar4' => $bonus_luar4,
      'bonus_luar5' => $bonus_luar5,
      'bonus_luar6' => $bonus_luar6,
      'bonus_luar7' => $bonus_luar7,
      'bonus_luar8' => $bonus_luar8,
      'bonus_luar9' => $bonus_luar9,
      'bonus_luar10' => $bonus_luar10,
      'operasional' => $operasional,
      'tunjangan' => $tunjangan,
      'jumlah_bonus' => $jumlah_bonus,
      'jumlah_bonus1' => $jumlah_bonus1,
      'jumlah_bonus2' => $jumlah_bonus2,
      'jumlah_bonus3' => $jumlah_bonus3,
      'jumlah_bonus4' => $jumlah_bonus4,
      'jumlah_bonus5' => $jumlah_bonus5,
      'jumlah_bonus6' => $jumlah_bonus6,
      'jumlah_bonus7' => $jumlah_bonus7,
      'jumlah_bonus8' => $jumlah_bonus8,
      'jumlah_bonus9' => $jumlah_bonus9,
      'jumlah_bonus10' => $jumlah_bonus10,
      'jumlah_bonus_luar' => $jumlah_bonus_luar,
      'jumlah_bonus_luar1' => $jumlah_bonus_luar1,
      'jumlah_bonus_luar2' => $jumlah_bonus_luar2,
      'jumlah_bonus_luar3' => $jumlah_bonus_luar3,
      'jumlah_bonus_luar4' => $jumlah_bonus_luar4,
      'jumlah_bonus_luar5' => $jumlah_bonus_luar5,
      'jumlah_bonus_luar6' => $jumlah_bonus_luar6,
      'jumlah_bonus_luar7' => $jumlah_bonus_luar7,
      'jumlah_bonus_luar8' => $jumlah_bonus_luar8,
      'jumlah_bonus_luar9' => $jumlah_bonus_luar9,
      'jumlah_bonus_luar10' => $jumlah_bonus_luar10,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
      'status' => $request->input('status'),
      'note' => $request->input('note'),
    ]);

    // Redirect with success or error message
    if ($save) {
      // Get the ID of the newly created data
      $gajiId = $save->id;

      // Redirect to the detail page for the newly created data with SweetAlert notification
      return Redirect::route(
        'account.gaji.detail',
        ['id' => $gajiId]
      )->with('success', 'Data Gaji Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji Karyawan Gagal Disimpan!');
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

     //lembur
    $lembur = $request->input('lembur');
    $lembur = empty($lembur) ? 0 : str_replace(",", "", $lembur);

    $lembur1 = $request->input('lembur1');
    $lembur1 = empty($lembur1) ? 0 : str_replace(",", "", $lembur1);

    $lembur2 = $request->input('lembur2');
    $lembur2 = empty($lembur2) ? 0 : str_replace(",", "", $lembur2);

    $lembur3 = $request->input('lembur3');
    $lembur3 = empty($lembur3) ? 0 : str_replace(",", "", $lembur3);

    $lembur4 = $request->input('lembur4');
    $lembur4 = empty($lembur4) ? 0 : str_replace(",", "", $lembur4);

    $lembur5 = $request->input('lembur5');
    $lembur5 = empty($lembur5) ? 0 : str_replace(",", "", $lembur5);

    $lembur6 = $request->input('lembur6');
    $lembur6 = empty($lembur6) ? 0 : str_replace(",", "", $lembur6);

    $lembur7 = $request->input('lembur7');
    $lembur7 = empty($lembur7) ? 0 : str_replace(",", "", $lembur7);

    $lembur8 = $request->input('lembur8');
    $lembur8 = empty($lembur8) ? 0 : str_replace(",", "", $lembur8);

    $lembur9 = $request->input('lembur9');
    $lembur9 = empty($lembur9) ? 0 : str_replace(",", "", $lembur9);

    $lembur10 = $request->input('lembur10');
    $lembur10 = empty($lembur10) ? 0 : str_replace(",", "", $lembur10);
    //end

    //fee bonus dalam kota
    $bonus = $request->input('bonus');
    $bonus = empty($bonus) ? 0 : str_replace(",", "", $bonus);

    $bonus1 = $request->input('bonus1');
    $bonus1 = empty($bonus1) ? 0 : str_replace(",", "", $bonus1);

    $bonus2 = $request->input('bonus2');
    $bonus2 = empty($bonus2) ? 0 : str_replace(",", "", $bonus2);

    $bonus3 = $request->input('bonus3');
    $bonus3 = empty($bonus3) ? 0 : str_replace(",", "", $bonus3);

    $bonus4 = $request->input('bonus4');
    $bonus4 = empty($bonus4) ? 0 : str_replace(",", "", $bonus4);

    $bonus5 = $request->input('bonus5');
    $bonus5 = empty($bonus5) ? 0 : str_replace(",", "", $bonus5);

    $bonus6 = $request->input('bonus6');
    $bonus6 = empty($bonus6) ? 0 : str_replace(",", "", $bonus6);

    $bonus7 = $request->input('bonus7');
    $bonus7 = empty($bonus7) ? 0 : str_replace(",", "", $bonus7);

    $bonus8 = $request->input('bonus8');
    $bonus8 = empty($bonus8) ? 0 : str_replace(",", "", $bonus8);

    $bonus9 = $request->input('bonus9');
    $bonus9 = empty($bonus9) ? 0 : str_replace(",", "", $bonus9);

    $bonus10 = $request->input('bonus10');
    $bonus10 = empty($bonus10) ? 0 : str_replace(",", "", $bonus10);
    //end fee bonus dalam kota

    //fee bonus luar kota
    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? 0 : str_replace(",", "", $bonus_luar);

    $bonus_luar1 = $request->input('bonus_luar1');
    $bonus_luar1 = empty($bonus_luar1) ? 0 : str_replace(",", "", $bonus_luar1);

    $bonus_luar2 = $request->input('bonus_luar2');
    $bonus_luar2 = empty($bonus_luar2) ? 0 : str_replace(",", "", $bonus_luar2);

    $bonus_luar3 = $request->input('bonus_luar3');
    $bonus_luar3 = empty($bonus_luar3) ? 0 : str_replace(",", "", $bonus_luar3);

    $bonus_luar4 = $request->input('bonus_luar4');
    $bonus_luar4 = empty($bonus_luar4) ? 0 : str_replace(",", "", $bonus_luar4);

    $bonus_luar5 = $request->input('bonus_luar5');
    $bonus_luar5 = empty($bonus_luar5) ? 0 : str_replace(",", "", $bonus_luar5);

    $bonus_luar6 = $request->input('bonus_luar6');
    $bonus_luar6 = empty($bonus_luar6) ? 0 : str_replace(",", "", $bonus_luar6);

    $bonus_luar7 = $request->input('bonus_luar7');
    $bonus_luar7 = empty($bonus_luar7) ? 0 : str_replace(",", "", $bonus_luar7);

    $bonus_luar8 = $request->input('bonus_luar8');
    $bonus_luar8 = empty($bonus_luar8) ? 0 : str_replace(",", "", $bonus_luar8);

    $bonus_luar9 = $request->input('bonus_luar9');
    $bonus_luar9 = empty($bonus_luar9) ? 0 : str_replace(",", "", $bonus_luar9);

    $bonus_luar10 = $request->input('bonus_luar10');
    $bonus_luar10 = empty($bonus_luar10) ? 0 : str_replace(",", "", $bonus_luar10);
    //end fee bonus luar kota

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);

    //jumlah lembur
    $jumlah_lembur = $request->input('jumlah_lembur') ?? 0;
    $jumlah_lembur1 = $request->input('jumlah_lembur1') ?? 0;
    $jumlah_lembur2 = $request->input('jumlah_lembur2') ?? 0;
    $jumlah_lembur3 = $request->input('jumlah_lembur3') ?? 0;
    $jumlah_lembur4 = $request->input('jumlah_lembur4') ?? 0;
    $jumlah_lembur5 = $request->input('jumlah_lembur5') ?? 0;
    $jumlah_lembur6 = $request->input('jumlah_lembur6') ?? 0;
    $jumlah_lembur7 = $request->input('jumlah_lembur7') ?? 0;
    $jumlah_lembur8 = $request->input('jumlah_lembur8') ?? 0;
    $jumlah_lembur9 = $request->input('jumlah_lembur9') ?? 0;
    $jumlah_lembur10 = $request->input('jumlah_lembur10') ?? 0;
    //end jumlah lembur

    //jumlah bonus dalam kota
    $jumlah_bonus = $request->input('jumlah_bonus') ?? 0;
    $jumlah_bonus1 = $request->input('jumlah_bonus1') ?? 0;
    $jumlah_bonus2 = $request->input('jumlah_bonus2') ?? 0;
    $jumlah_bonus3 = $request->input('jumlah_bonus3') ?? 0;
    $jumlah_bonus4 = $request->input('jumlah_bonus4') ?? 0;
    $jumlah_bonus5 = $request->input('jumlah_bonus5') ?? 0;
    $jumlah_bonus6 = $request->input('jumlah_bonus6') ?? 0;
    $jumlah_bonus7 = $request->input('jumlah_bonus7') ?? 0;
    $jumlah_bonus8 = $request->input('jumlah_bonus8') ?? 0;
    $jumlah_bonus9 = $request->input('jumlah_bonus9') ?? 0;
    $jumlah_bonus10 = $request->input('jumlah_bonus10') ?? 0;
    //end jumlah bonus dalam kota

    //jumlah bonus luar kota
    $jumlah_bonus_luar = $request->input('jumlah_bonus_luar') ?? 0;
    $jumlah_bonus_luar1 = $request->input('jumlah_bonus_luar1') ?? 0;
    $jumlah_bonus_luar2 = $request->input('jumlah_bonus_luar2') ?? 0;
    $jumlah_bonus_luar3 = $request->input('jumlah_bonus_luar3') ?? 0;
    $jumlah_bonus_luar4 = $request->input('jumlah_bonus_luar4') ?? 0;
    $jumlah_bonus_luar5 = $request->input('jumlah_bonus_luar5') ?? 0;
    $jumlah_bonus_luar6 = $request->input('jumlah_bonus_luar6') ?? 0;
    $jumlah_bonus_luar7 = $request->input('jumlah_bonus_luar7') ?? 0;
    $jumlah_bonus_luar8 = $request->input('jumlah_bonus_luar8') ?? 0;
    $jumlah_bonus_luar9 = $request->input('jumlah_bonus_luar9') ?? 0;
    $jumlah_bonus_luar10 = $request->input('jumlah_bonus_luar10') ?? 0;
    //end jumlah bonus luar kota

    $total_lembur = ($lembur * $jumlah_lembur) + ($lembur1 * $jumlah_lembur1) + ($lembur2 * $jumlah_lembur2) + ($lembur3 * $jumlah_lembur3) + ($lembur4 * $jumlah_lembur4) + ($lembur5 * $jumlah_lembur5) + ($lembur6 * $jumlah_lembur6) + ($lembur7 * $jumlah_lembur7) + ($lembur8 * $jumlah_lembur8) + ($lembur9 * $jumlah_lembur9) + ($lembur10 * $jumlah_lembur10);
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    $total_bonus =
    ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) +
    ($bonus8 * $jumlah_bonus8) + ($bonus9 * $jumlah_bonus9) + ($bonus10 * $jumlah_bonus10) +
    ($bonus_luar * $jumlah_bonus_luar) + ($bonus_luar1 * $jumlah_bonus_luar1) + ($bonus_luar2 * $jumlah_bonus_luar2) + ($bonus_luar3 * $jumlah_bonus_luar3) + ($bonus_luar4 * $jumlah_bonus_luar4) + ($bonus_luar5 * $jumlah_bonus_luar5) +
    ($bonus_luar6 * $jumlah_bonus_luar6) + ($bonus_luar7 * $jumlah_bonus_luar7) + ($bonus_luar8 * $jumlah_bonus_luar8) + ($bonus_luar9 * $jumlah_bonus_luar9) + ($bonus_luar10 * $jumlah_bonus_luar10);
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
      'lembur1' => $lembur1,
      'lembur2' => $lembur2,
      'lembur3' => $lembur3,
      'lembur4' => $lembur4,
      'lembur5' => $lembur5,
      'lembur6' => $lembur6,
      'lembur7' => $lembur7,
      'lembur8' => $lembur8,
      'lembur9' => $lembur9,
      'lembur10' => $lembur10,
      'jumlah_lembur' => $jumlah_lembur,
      'jumlah_lembur1' => $jumlah_lembur1,
      'jumlah_lembur2' => $jumlah_lembur2,
      'jumlah_lembur3' => $jumlah_lembur3,
      'jumlah_lembur4' => $jumlah_lembur4,
      'jumlah_lembur5' => $jumlah_lembur5,
      'jumlah_lembur6' => $jumlah_lembur6,
      'jumlah_lembur7' => $jumlah_lembur7,
      'jumlah_lembur8' => $jumlah_lembur8,
      'jumlah_lembur9' => $jumlah_lembur9,
      'jumlah_lembur10' => $jumlah_lembur10,
      'bonus' => $bonus,
      'bonus1' => $bonus1,
      'bonus2' => $bonus2,
      'bonus3' => $bonus3,
      'bonus4' => $bonus4,
      'bonus5' => $bonus5,
      'bonus6' => $bonus6,
      'bonus7' => $bonus7,
      'bonus8' => $bonus8,
      'bonus9' => $bonus9,
      'bonus10' => $bonus10,
      'bonus_luar' => $bonus_luar,
      'bonus_luar1' => $bonus_luar1,
      'bonus_luar2' => $bonus_luar2,
      'bonus_luar3' => $bonus_luar3,
      'bonus_luar4' => $bonus_luar4,
      'bonus_luar5' => $bonus_luar5,
      'bonus_luar6' => $bonus_luar6,
      'bonus_luar7' => $bonus_luar7,
      'bonus_luar8' => $bonus_luar8,
      'bonus_luar9' => $bonus_luar9,
      'bonus_luar10' => $bonus_luar10,
      'operasional' => $operasional,
      'tunjangan' => $tunjangan,
      'jumlah_bonus' => $jumlah_bonus,
      'jumlah_bonus1' => $jumlah_bonus1,
      'jumlah_bonus2' => $jumlah_bonus2,
      'jumlah_bonus3' => $jumlah_bonus3,
      'jumlah_bonus4' => $jumlah_bonus4,
      'jumlah_bonus5' => $jumlah_bonus5,
      'jumlah_bonus6' => $jumlah_bonus6,
      'jumlah_bonus7' => $jumlah_bonus7,
      'jumlah_bonus8' => $jumlah_bonus8,
      'jumlah_bonus9' => $jumlah_bonus9,
      'jumlah_bonus10' => $jumlah_bonus10,
      'jumlah_bonus_luar' => $jumlah_bonus_luar,
      'jumlah_bonus_luar1' => $jumlah_bonus_luar1,
      'jumlah_bonus_luar2' => $jumlah_bonus_luar2,
      'jumlah_bonus_luar3' => $jumlah_bonus_luar3,
      'jumlah_bonus_luar4' => $jumlah_bonus_luar4,
      'jumlah_bonus_luar5' => $jumlah_bonus_luar5,
      'jumlah_bonus_luar6' => $jumlah_bonus_luar6,
      'jumlah_bonus_luar7' => $jumlah_bonus_luar7,
      'jumlah_bonus_luar8' => $jumlah_bonus_luar8,
      'jumlah_bonus_luar9' => $jumlah_bonus_luar9,
      'jumlah_bonus_luar10' => $jumlah_bonus_luar10,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
      'status' => $request->input('status'),
      'note' => $request->input('note'),
    ]);

    // Redirect with success or error message
    if ($gaji) {
      return redirect()->route(
        'account.gaji.detail',
        ['id' => $id]
      )->with(
        ['success' => 'Data Gaji Karyawan Berhasil Diperbarui!']
      );
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji Karyawan Gagal Diperbarui!');
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

  public function downloadPdf()
  {
    $user = Auth::user();
    $currentMonth = date('Y-m-01 00:00:00');
    $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));

    if ($user->level == 'manager' || $user->level == 'staff') {
      $gaji = DB::table('gaji')
      ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
      ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    } else if ($user->level == 'karyawan') {
      $gaji = DB::table('gaji')
      ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
      ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
      ->where('gaji.user_id', $user->id)  // Display only the salary data for the logged-in user
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    } else {
      $gaji = Gaji::select('gaji.*', 'users.name as full_name')
      ->join('users', 'gaji.user_id', '=', 'users.id')
      ->where('gaji.user_id', $user->id)
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    }

    // Calculate total gaji
    $totalGaji = $gaji->sum('total');

    $users = User::all(); // Get all users

    // Get the HTML content of the view
    $html = view('account.gaji.pdf', compact('gaji', 'totalGaji'))->render();

    // Instantiate Dompdf with the default configuration
    $dompdf = new Dompdf();

    // Load the HTML content into Dompdf
    $dompdf->loadHtml($html);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the PDF
    $dompdf->render();

    // Set the PDF filename
    $fileName = 'laporan_gaji_karyawan_' . date('d-m-Y') . '.pdf';

    // Output the generated PDF to the browser
    return $dompdf->stream($fileName);
  }
}
