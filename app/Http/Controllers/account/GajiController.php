<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Gaji;
use App\Presensi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use App\Mail\GajiSuccessMail;
use App\Exports\GajiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GajiController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  function generateRandomToken($length)
  {
    // Karakter huruf untuk awal (3 huruf pertama)
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    // Semua karakter yang boleh (setelah 3 huruf awal)
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';

    $token = '';

    // Tambah 3 huruf pertama
    for ($i = 0; $i < 3 && $i < $length; $i++) {
      $token .= $letters[rand(0, strlen($letters) - 1)];
    }

    // Tambah sisanya (kalau panjang > 3)
    for ($i = 3; $i < $length; $i++) {
      $token .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $token;
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

  // <!--================== TAMPILAN DATA ==================-->
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

    $totalGaji = 0;
    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {

      // ===== TOTAL GAJI 3 PERIODE =====

      // Bulan ini
      $totalBulanIni = DB::table('gaji')
        ->selectRaw('SUM(total) as total')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [
          \Carbon\Carbon::now()->startOfMonth(),
          \Carbon\Carbon::now()->endOfMonth()
        ])
        ->value('total') ?? 0;

      // 1 bulan sebelumnya
      $totalBulanLalu = DB::table('gaji')
        ->selectRaw('SUM(total) as total')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [
          \Carbon\Carbon::now()->subMonth()->startOfMonth(),
          \Carbon\Carbon::now()->subMonth()->endOfMonth()
        ])
        ->value('total') ?? 0;

      // 2 bulan sebelumnya
      $totalDuaBulanLalu = DB::table('gaji')
        ->selectRaw('SUM(total) as total')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [
          \Carbon\Carbon::now()->subMonths(2)->startOfMonth(),
          \Carbon\Carbon::now()->subMonths(2)->endOfMonth()
        ])
        ->value('total') ?? 0;

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.gaji_pokok_ethes_digital', 'gaji.total_gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(20);
    } else if ($user->level == 'karyawan' || $user->level == 'trainer') {

      $totalGaji = DB::table('gaji')
        ->selectRaw('SUM(total) as total_gaji')
        ->where('user_id', $user->id)
        ->where('status', 'terbayar')
        ->whereBetween('tanggal', [$currentMonth, $nextMonth])
        ->first()->total_gaji ?? 0;

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.gaji_pokok_ethes_digital', 'gaji.total_gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)  // Display only the salary data for the logged-in user
        // ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    } else {

      $totalGaji = DB::table('gaji')
        ->selectRaw('SUM(total) as total_gaji')
        ->where('user_id', $user->id)
        ->whereBetween('tanggal', [$currentMonth, $nextMonth])
        ->first()->total_gaji ?? 0;

      $gaji = Gaji::select('gaji.*', 'users.name as full_name')
        ->join('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', $user->id)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->paginate(10);
    }

    $presensiExist = Presensi::where('status', '<>', null)
      ->whereBetween('created_at', [$currentMonth, $nextMonth])
      ->exists();


    return view('account.gaji.index', compact('gaji', 'startDate', 'endDate', 'totalGaji', 'presensiExist', 'totalBulanIni', 'totalBulanLalu', 'totalDuaBulanLalu'));
  }
  // <!--================== END ==================-->

  // <!--================== FILTER ==================-->
  public function filterGaji(Request $request)
  {
    $user       = Auth::user();
    $startDate  = $request->input('tanggal_awal');
    $endDate    = $request->input('tanggal_akhir');
    $search     = $request->input('q');

    // 1) Bentuk base query (tanpa paginate)
    $baseQuery = DB::table('gaji')
      ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
      ->select([
        'gaji.id',
        'gaji.id_transaksi',
        'gaji.token',
        'gaji.gaji_pokok',
        'gaji.gaji_pokok_ethes_digital',
        'gaji.total_gaji_pokok',
        'gaji.lembur',
        'gaji.bonus',
        'gaji.tunjangan',
        'gaji.tanggal',
        'gaji.pph',
        'gaji.total',
        'gaji.status',
        'users.id as user_id',
        'users.full_name',
        'users.nik',
        'users.norek',
        'users.bank',
      ]);

    // 2) Terapkan filter tanggal (default bulan ini)
    $from = $startDate
      ? Carbon::parse($startDate)->startOfDay()
      : Carbon::now()->startOfMonth();
    $to = $endDate
      ? Carbon::parse($endDate)->endOfDay()
      : Carbon::now()->endOfMonth();

    $baseQuery->whereBetween('gaji.tanggal', [$from, $to]);

    // 3) Filter berdasarkan level user
    if (in_array($user->level, ['manager', 'staff', 'ceo'])) {
      $baseQuery->where('users.company', $user->company);
    } else {
      $baseQuery->where('gaji.user_id', $user->id);
    }

    // 4) Filter pencarian (jika ada)
    if ($search) {
      $baseQuery->where(function ($q) use ($search) {
        $q->where('gaji.id_transaksi', 'like', "%{$search}%")
          ->orWhere('users.full_name', 'like', "%{$search}%")
          ->orWhere('users.norek', 'like', "%{$search}%");
      });
    }

    // 5) Hitung total Gaji tanpa paginate
    $totalGaji = (clone $baseQuery)->sum('gaji.total');

    // 6) Ambil data ter‑paginate (halaman)
    $gaji = (clone $baseQuery)
      ->orderBy('gaji.created_at', 'desc')
      ->paginate(10)
      ->appends([
        'tanggal_awal'  => $startDate,
        'tanggal_akhir' => $endDate,
        'q'             => $search,
      ]);

    // 7) Data pendukung lain
    $presensiExist = false;
    if (in_array($user->level, ['manager', 'staff', 'ceo'])) {
      $presensiExist = Presensi::whereNotNull('status')
        ->whereBetween('created_at', [$from, $to])
        ->exists();
    }

    return view('account.gaji.index', compact(
      'gaji',
      'startDate',
      'endDate',
      'totalGaji',
      'presensiExist'
    ));
  }
  // <!--================== END ==================-->

  // <!--================== SEARCH ==================-->
  public function searchGaji(Request $request)
  {
    $user = Auth::user();
    $search = $request->get('q');

    // Ambil input tanggal dari request
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');

    // Jika tanggal tidak diisi, pakai default dari masa lalu hingga sekarang
    $currentMonth = $startDate ? date('Y-m-d 00:00:00', strtotime($startDate)) : '2000-01-01 00:00:00';
    $nextMonth = $endDate ? date('Y-m-d 23:59:59', strtotime($endDate)) : now()->format('Y-m-d 23:59:59');

    // Query awal
    $gajiQuery = DB::table('gaji')
      ->select(
        'gaji.id',
        'gaji.id_transaksi',
        'gaji.token',
        'gaji.gaji_pokok',
        'gaji.gaji_pokok_ethes_digital',
        'gaji.total_gaji_pokok',
        'gaji.lembur',
        'gaji.bonus',
        'gaji.tunjangan',
        'gaji.tanggal',
        'gaji.pph',
        'gaji.total',
        'gaji.status',
        'users.id as user_id',
        'users.full_name as full_name',
        'users.nik as nik',
        'users.norek as norek',
        'users.bank as bank'
      )
      ->leftJoin('users', 'gaji.user_id', '=', 'users.id');

    // Filter sesuai role user
    if ($user->level === 'manager') {
      $gajiQuery->where('users.company', $user->company);
    } else {
      $gajiQuery->where('gaji.user_id', $user->id);
    }

    // Pencarian berdasarkan kata kunci
    if ($search) {
      $gajiQuery->where(function ($query) use ($search) {
        $query->where('gaji.id_transaksi', 'LIKE', '%' . $search . '%')
          ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('users.norek', 'LIKE', '%' . $search . '%')
          ->orWhere(DB::raw("CAST(REPLACE(gaji.total, 'Rp', '') AS DECIMAL(10, 2))"), '=', str_replace(['Rp', '.', ','], '', $search))
          ->orWhere(DB::raw("DATE_FORMAT(gaji.tanggal, '%d %M %Y')"), 'LIKE', '%' . $search . '%');
      });
    }

    // Duplikasi query untuk menghitung total (tanpa pagination)
    $totalGajiQuery = clone $gajiQuery;

    // Hitung total gaji dari semua hasil (bukan hanya paginasi)
    $totalGaji = $totalGajiQuery
      ->whereBetween('gaji.created_at', [$currentMonth, $nextMonth])
      ->sum('total');

    // Ambil data gaji dengan pagination
    $gaji = $gajiQuery
      ->whereBetween('gaji.created_at', [$currentMonth, $nextMonth])
      ->orderBy('gaji.created_at', 'DESC')
      ->paginate(10);

    // Simpan filter ke pagination
    $gaji->appends([
      'q' => $search,
      'tanggal_awal' => $startDate,
      'tanggal_akhir' => $endDate
    ]);

    $presensiExist = false;
    if ($user->level === 'manager') {
      $presensiExist = Presensi::where('status', '<>', null)
        ->whereBetween('created_at', [$currentMonth, $nextMonth])
        ->exists();
    }

    // Jika kosong, redirect dengan error
    if ($gaji->isEmpty()) {
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji tidak ditemukan.');
    }

    return view('account.gaji.index', compact(
      'gaji',
      'startDate',
      'endDate',
      'totalGaji',
      'presensiExist'
    ));
  }
  // <!--================== END ==================-->

  // <!--================== CREATE DATA ==================-->
  public function create()
  {
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'admin') {
      // $users = User::where('company', $user->company)
      //   ->select('id', 'full_name', 'nik', 'norek', 'bank', 'telp')
      //   ->get();
      // $presensi = DB::table('presensi')
      //   ->get();

      // $datas = [
      //   'users' => $users,
      //   'presensi' => $presensi,
      // ];

      $datas = DB::table('users')
        ->select(
          'users.id',
          'users.full_name',
          'users.nik',
          'users.norek',
          'users.bank',
          'users.telp',
          'users.email',
          DB::raw('SUM(presensi.alpha) as alpha'),
          DB::raw('SUM(presensi.hadir) as hadir'),
          DB::raw('SUM(presensi.camp_jogja) as camp_jogja'),
          DB::raw('SUM(presensi.camp_luar_kota) as camp_luar_kota'),
          DB::raw('SUM(presensi.perjalanan_jawa) as perjalanan_jawa'),
          DB::raw('SUM(presensi.perjalanan_luar_jawa) as perjalanan_luar_jawa'),
          DB::raw('SUM(presensi.remote) as remote'),
          DB::raw('SUM(presensi.izin) as izin')
        )
        ->leftJoin('presensi', 'presensi.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('presensi.created_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->groupBy('users.id', 'users.full_name', 'users.nik', 'users.norek', 'users.bank', 'users.telp', 'users.email')
        ->orderBy('users.created_at', 'DESC')
        ->get();

      // dd($datas);
      return view('account.gaji.create', compact('datas'));
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
    $token = $this->generateRandomToken(30);

    $this->validate(
      $request,
      [
        'gaji_pokok' => 'required',
        'tanggal' => 'required',
        'status' => 'required',
      ],
      [
        'gaji_pokok.required' => 'Masukkan Gaji Pokok Karyawan!',
        'tanggal.required' => 'Pilih Tanggal Pembayaran Gaji Karyawan!',
        'status.required' => 'Pilih Status Pembayaran Gaji Karyawan!',
      ]
    );

    $gaji_pokok = $request->input('gaji_pokok');
    $gaji_pokok_ethes_digital = $request->input('gaji_pokok_ethes_digital');
    $gaji_pokok = empty($gaji_pokok) ? 0 : str_replace(",", "", $gaji_pokok); // Convert to numeric value or set to 0 if empty
    $gaji_pokok_ethes_digital = empty($gaji_pokok_ethes_digital) ? 0 : str_replace(",", "", $gaji_pokok_ethes_digital);
    $total_gaji_pokok = $gaji_pokok +  $gaji_pokok_ethes_digital;

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
    $bonus = empty($bonus) ? null : str_replace(",", "", $bonus);

    $bonus1 = $request->input('bonus1');
    $bonus1 = empty($bonus1) ? null : str_replace(",", "", $bonus1);

    $bonus2 = $request->input('bonus2');
    $bonus2 = empty($bonus2) ? null : str_replace(",", "", $bonus2);

    $bonus3 = $request->input('bonus3');
    $bonus3 = empty($bonus3) ? null : str_replace(",", "", $bonus3);

    $bonus4 = $request->input('bonus4');
    $bonus4 = empty($bonus4) ? null : str_replace(",", "", $bonus4);

    $bonus5 = $request->input('bonus5');
    $bonus5 = empty($bonus5) ? null : str_replace(",", "", $bonus5);

    $bonus6 = $request->input('bonus6');
    $bonus6 = empty($bonus6) ? null : str_replace(",", "", $bonus6);

    $bonus7 = $request->input('bonus7');
    $bonus7 = empty($bonus7) ? null : str_replace(",", "", $bonus7);

    $bonus8 = $request->input('bonus8');
    $bonus8 = empty($bonus8) ? null : str_replace(",", "", $bonus8);

    $bonus9 = $request->input('bonus9');
    $bonus9 = empty($bonus9) ? null : str_replace(",", "", $bonus9);

    $bonus10 = $request->input('bonus10');
    $bonus10 = empty($bonus10) ? null : str_replace(",", "", $bonus10);
    //end fee bonus dalam kota

    //fee bonus luar kota
    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? null : str_replace(",", "", $bonus_luar);

    $bonus_luar1 = $request->input('bonus_luar1');
    $bonus_luar1 = empty($bonus_luar1) ? null : str_replace(",", "", $bonus_luar1);

    $bonus_luar2 = $request->input('bonus_luar2');
    $bonus_luar2 = empty($bonus_luar2) ? null : str_replace(",", "", $bonus_luar2);

    $bonus_luar3 = $request->input('bonus_luar3');
    $bonus_luar3 = empty($bonus_luar3) ? null : str_replace(",", "", $bonus_luar3);

    $bonus_luar4 = $request->input('bonus_luar4');
    $bonus_luar4 = empty($bonus_luar4) ? null : str_replace(",", "", $bonus_luar4);

    $bonus_luar5 = $request->input('bonus_luar5');
    $bonus_luar5 = empty($bonus_luar5) ? null : str_replace(",", "", $bonus_luar5);

    $bonus_luar6 = $request->input('bonus_luar6');
    $bonus_luar6 = empty($bonus_luar6) ? null : str_replace(",", "", $bonus_luar6);

    $bonus_luar7 = $request->input('bonus_luar7');
    $bonus_luar7 = empty($bonus_luar7) ? null : str_replace(",", "", $bonus_luar7);

    $bonus_luar8 = $request->input('bonus_luar8');
    $bonus_luar8 = empty($bonus_luar8) ? null : str_replace(",", "", $bonus_luar8);

    $bonus_luar9 = $request->input('bonus_luar9');
    $bonus_luar9 = empty($bonus_luar9) ? null : str_replace(",", "", $bonus_luar9);

    $bonus_luar10 = $request->input('bonus_luar10');
    $bonus_luar10 = empty($bonus_luar10) ? null : str_replace(",", "", $bonus_luar10);
    //end fee bonus luar kota

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $webinar = $request->input('webinar');
    $webinar = empty($webinar) ? 0 : str_replace(",", "", $webinar);
    $kinerja = $request->input('kinerja');
    $kinerja = empty($kinerja) ? 0 : str_replace(",", "", $kinerja);
    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);
    $tunjangan_bpjs = $request->input('tunjangan_bpjs');
    $tunjangan_bpjs = empty($tunjangan_bpjs) ? 0 : str_replace(",", "", $tunjangan_bpjs);
    $tunjangan_thr = $request->input('tunjangan_thr');
    $tunjangan_thr = empty($tunjangan_thr) ? 0 : str_replace(",", "", $tunjangan_thr);
    $tunjangan_pulsa = $request->input('tunjangan_pulsa');
    $tunjangan_pulsa = empty($tunjangan_pulsa) ? 0 : str_replace(",", "", $tunjangan_pulsa);

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
    $jumlah_bonus = $request->input('jumlah_bonus') ?? null;
    $jumlah_bonus1 = $request->input('jumlah_bonus1') ?? null;
    $jumlah_bonus2 = $request->input('jumlah_bonus2') ?? null;
    $jumlah_bonus3 = $request->input('jumlah_bonus3') ?? null;
    $jumlah_bonus4 = $request->input('jumlah_bonus4') ?? null;
    $jumlah_bonus5 = $request->input('jumlah_bonus5') ?? null;
    $jumlah_bonus6 = $request->input('jumlah_bonus6') ?? null;
    $jumlah_bonus7 = $request->input('jumlah_bonus7') ?? null;
    $jumlah_bonus8 = $request->input('jumlah_bonus8') ?? null;
    $jumlah_bonus9 = $request->input('jumlah_bonus9') ?? null;
    $jumlah_bonus10 = $request->input('jumlah_bonus10') ?? null;
    $alpha = $request->input('alpha') ?? null;
    //end jumlah bonus dalam kota

    //jumlah bonus luar kota
    $jumlah_bonus_luar = $request->input('jumlah_bonus_luar') ?? null;
    $jumlah_bonus_luar1 = $request->input('jumlah_bonus_luar1') ?? null;
    $jumlah_bonus_luar2 = $request->input('jumlah_bonus_luar2') ?? null;
    $jumlah_bonus_luar3 = $request->input('jumlah_bonus_luar3') ?? null;
    $jumlah_bonus_luar4 = $request->input('jumlah_bonus_luar4') ?? null;
    $jumlah_bonus_luar5 = $request->input('jumlah_bonus_luar5') ?? null;
    $jumlah_bonus_luar6 = $request->input('jumlah_bonus_luar6') ?? null;
    $jumlah_bonus_luar7 = $request->input('jumlah_bonus_luar7') ?? null;
    $jumlah_bonus_luar8 = $request->input('jumlah_bonus_luar8') ?? null;
    $jumlah_bonus_luar9 = $request->input('jumlah_bonus_luar9') ?? null;
    $jumlah_bonus_luar10 = $request->input('jumlah_bonus_luar10') ?? null;
    //end jumlah bonus luar kota

    $total_lembur = ($lembur * $jumlah_lembur) + ($lembur1 * $jumlah_lembur1) + ($lembur2 * $jumlah_lembur2) + ($lembur3 * $jumlah_lembur3) + ($lembur4 * $jumlah_lembur4) + ($lembur5 * $jumlah_lembur5) + ($lembur6 * $jumlah_lembur6) +
      ($lembur7 * $jumlah_lembur7) + ($lembur8 * $jumlah_lembur8) + ($lembur9 * $jumlah_lembur9) + ($lembur10 * $jumlah_lembur10);
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    // $total_bonus =
    //   ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) +
    //   ($bonus8 * $jumlah_bonus8) + ($bonus9 * $jumlah_bonus9) + ($bonus10 * $jumlah_bonus10) +
    //   ($bonus_luar * $jumlah_bonus_luar) + ($bonus_luar1 * $jumlah_bonus_luar1) + ($bonus_luar2 * $jumlah_bonus_luar2) + ($bonus_luar3 * $jumlah_bonus_luar3) + ($bonus_luar4 * $jumlah_bonus_luar4) + ($bonus_luar5 * $jumlah_bonus_luar5) +
    //   ($bonus_luar6 * $jumlah_bonus_luar6) + ($bonus_luar7 * $jumlah_bonus_luar7) + ($bonus_luar8 * $jumlah_bonus_luar8) + ($bonus_luar9 * $jumlah_bonus_luar9) + ($bonus_luar10 * $jumlah_bonus_luar10);
    // $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $total_bonus =
      ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) + $webinar + $kinerja;
    $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $potongan = $request->input('potongan');
    $potongan = empty($potongan) ? 0 : str_replace(",", "", $potongan);

    $pph = $request->input('pph');
    $pph = empty($pph) ? 0 : str_replace(",", "", $pph);

    // <!-- POTONGAN JIKA ALPHA -->
    $subalpha = $jumlah_bonus5 * 0.005;
    $subhasil = $total_gaji_pokok * $subalpha;
    $totalalpha = $total_gaji_pokok - $subhasil;
    // <!-- END -->

    // <!-- TOTAL -->
    $subtotal = $totalalpha + $total_lembur + $total_bonus + $tunjangan + $tunjangan_bpjs + $tunjangan_thr + $tunjangan_pulsa - $potongan - $pph;
    $total = $subtotal;
    $total = empty($total) ? 0 : str_replace(",", "", $total);
    // <!-- END -->

    //menyinpan image di path
    $imagePath = null;

    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
    }
    //end


    $save = Gaji::create([
      'id_transaksi' => $id_transaksi,
      'token'             => $token,
      'user_id' => $request->input('user_id'),
      'gaji_pokok' => $gaji_pokok,
      'gaji_pokok_ethes_digital' => $gaji_pokok_ethes_digital,
      'total_gaji_pokok' => $total_gaji_pokok,
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
      'webinar' => $webinar,
      'kinerja' => $kinerja,
      'tunjangan' => $tunjangan,
      'tunjangan_bpjs' => $tunjangan_bpjs,
      'tunjangan_thr' => $tunjangan_thr,
      'tunjangan_pulsa' => $tunjangan_pulsa,
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
      'alpha' => $alpha,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'pph' => $pph,
      'alpha' => $subhasil,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
      'status' => $request->input('status'),
      'note' => $request->input('note'),
      'gambar' => $imagePath ?? null,
      'email' => $request->input('email'),
    ]);

    // Redirect with success or error message
    if ($save) {
      $user = User::findOrFail($request->input('user_id'));
      $appName = 'Rumah Scopus Foundation';
      $isTerbayar = $request->input('status') == 'terbayar';
      if ($isTerbayar) {
        Mail::to($request->email)->send(new GajiSuccessMail($user, $save, $appName, $isTerbayar));
      }

      return redirect()->route('account.gaji.index')->with('success', 'Data Gaji Karyawan Berhasil Disimpan!');
      // $gajiId = $save->id;
      // return Redirect::route(
      //   'account.gaji.detail',
      //   ['id' => $gajiId]
      // )->with('success', 'Data Gaji Karyawan Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji Karyawan Gagal Disimpan!');
    }
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE DATA ==================-->
  public function edit($id, $token)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

    $users = User::join('gaji', 'users.id', '=', 'gaji.user_id')
      ->where('users.company', $user->company)
      ->get(['users.*']);
    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name',
        'users.nik',
        'users.norek',
        'users.bank',
        'users.telp',
        'users.email',
        DB::raw('SUM(presensi.alpha) as alpha'),
        DB::raw('SUM(presensi.hadir) as hadir'),
        DB::raw('SUM(presensi.camp_jogja) as camp_jogja'),
        DB::raw('SUM(presensi.camp_luar_kota) as camp_luar_kota'),
        DB::raw('SUM(presensi.perjalanan_jawa) as perjalanan_jawa'),
        DB::raw('SUM(presensi.perjalanan_luar_jawa) as perjalanan_luar_jawa'),
        DB::raw('SUM(presensi.remote) as remote'),
        DB::raw('SUM(presensi.izin) as izin')
      )
      ->leftJoin('presensi', 'presensi.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->whereBetween('presensi.created_at', [now()->startOfMonth(), now()->endOfMonth()])
      ->groupBy('users.id', 'users.full_name', 'users.nik', 'users.norek', 'users.bank', 'users.telp', 'users.email')
      ->orderBy('users.created_at', 'DESC')
      ->get();

    return view('account.gaji.edit', compact('gaji', 'users', 'datas')); // Sesuaikan path template dengan benar
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
    $gaji_pokok_ethes_digital = $request->input('gaji_pokok_ethes_digital');
    $gaji_pokok = empty($gaji_pokok) ? 0 : str_replace(",", "", $gaji_pokok); // Convert to numeric value or set to 0 if empty
    $gaji_pokok_ethes_digital = empty($gaji_pokok_ethes_digital) ? 0 : str_replace(",", "", $gaji_pokok_ethes_digital);
    $total_gaji_pokok = $gaji_pokok +  $gaji_pokok_ethes_digital;

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
    $bonus = empty($bonus) ? null : str_replace(",", "", $bonus);

    $bonus1 = $request->input('bonus1');
    $bonus1 = empty($bonus1) ? null : str_replace(",", "", $bonus1);

    $bonus2 = $request->input('bonus2');
    $bonus2 = empty($bonus2) ? null : str_replace(",", "", $bonus2);

    $bonus3 = $request->input('bonus3');
    $bonus3 = empty($bonus3) ? null : str_replace(",", "", $bonus3);

    $bonus4 = $request->input('bonus4');
    $bonus4 = empty($bonus4) ? null : str_replace(",", "", $bonus4);

    $bonus5 = $request->input('bonus5');
    $bonus5 = empty($bonus5) ? null : str_replace(",", "", $bonus5);

    $bonus6 = $request->input('bonus6');
    $bonus6 = empty($bonus6) ? null : str_replace(",", "", $bonus6);

    $bonus7 = $request->input('bonus7');
    $bonus7 = empty($bonus7) ? null : str_replace(",", "", $bonus7);

    $bonus8 = $request->input('bonus8');
    $bonus8 = empty($bonus8) ? null : str_replace(",", "", $bonus8);

    $bonus9 = $request->input('bonus9');
    $bonus9 = empty($bonus9) ? null : str_replace(",", "", $bonus9);

    $bonus10 = $request->input('bonus10');
    $bonus10 = empty($bonus10) ? null : str_replace(",", "", $bonus10);
    //end fee bonus dalam kota

    //fee bonus luar kota
    $bonus_luar = $request->input('bonus_luar');
    $bonus_luar = empty($bonus_luar) ? null : str_replace(",", "", $bonus_luar);

    $bonus_luar1 = $request->input('bonus_luar1');
    $bonus_luar1 = empty($bonus_luar1) ? null : str_replace(",", "", $bonus_luar1);

    $bonus_luar2 = $request->input('bonus_luar2');
    $bonus_luar2 = empty($bonus_luar2) ? null : str_replace(",", "", $bonus_luar2);

    $bonus_luar3 = $request->input('bonus_luar3');
    $bonus_luar3 = empty($bonus_luar3) ? null : str_replace(",", "", $bonus_luar3);

    $bonus_luar4 = $request->input('bonus_luar4');
    $bonus_luar4 = empty($bonus_luar4) ? null : str_replace(",", "", $bonus_luar4);

    $bonus_luar5 = $request->input('bonus_luar5');
    $bonus_luar5 = empty($bonus_luar5) ? null : str_replace(",", "", $bonus_luar5);

    $bonus_luar6 = $request->input('bonus_luar6');
    $bonus_luar6 = empty($bonus_luar6) ? null : str_replace(",", "", $bonus_luar6);

    $bonus_luar7 = $request->input('bonus_luar7');
    $bonus_luar7 = empty($bonus_luar7) ? null : str_replace(",", "", $bonus_luar7);

    $bonus_luar8 = $request->input('bonus_luar8');
    $bonus_luar8 = empty($bonus_luar8) ? null : str_replace(",", "", $bonus_luar8);

    $bonus_luar9 = $request->input('bonus_luar9');
    $bonus_luar9 = empty($bonus_luar9) ? null : str_replace(",", "", $bonus_luar9);

    $bonus_luar10 = $request->input('bonus_luar10');
    $bonus_luar10 = empty($bonus_luar10) ? null : str_replace(",", "", $bonus_luar10);
    //end fee bonus luar kota

    $operasional = $request->input('operasional');
    $operasional = empty($operasional) ? 0 : str_replace(",", "", $operasional);

    $webinar = $request->input('webinar');
    $webinar = empty($webinar) ? 0 : str_replace(",", "", $webinar);
    $kinerja = $request->input('kinerja');
    $kinerja = empty($kinerja) ? 0 : str_replace(",", "", $kinerja);
    $tunjangan = $request->input('tunjangan');
    $tunjangan = empty($tunjangan) ? 0 : str_replace(",", "", $tunjangan);
    $tunjangan_bpjs = $request->input('tunjangan_bpjs');
    $tunjangan_bpjs = empty($tunjangan_bpjs) ? 0 : str_replace(",", "", $tunjangan_bpjs);
    $tunjangan_thr = $request->input('tunjangan_thr');
    $tunjangan_thr = empty($tunjangan_thr) ? 0 : str_replace(",", "", $tunjangan_thr);
    $tunjangan_pulsa = $request->input('tunjangan_pulsa');
    $tunjangan_pulsa = empty($tunjangan_pulsa) ? 0 : str_replace(",", "", $tunjangan_pulsa);


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
    $jumlah_bonus = $request->input('jumlah_bonus') ?? null;
    $jumlah_bonus1 = $request->input('jumlah_bonus1') ?? null;
    $jumlah_bonus2 = $request->input('jumlah_bonus2') ?? null;
    $jumlah_bonus3 = $request->input('jumlah_bonus3') ?? null;
    $jumlah_bonus4 = $request->input('jumlah_bonus4') ?? null;
    $jumlah_bonus5 = $request->input('jumlah_bonus5') ?? null;
    $jumlah_bonus6 = $request->input('jumlah_bonus6') ?? null;
    $jumlah_bonus7 = $request->input('jumlah_bonus7') ?? null;
    $jumlah_bonus8 = $request->input('jumlah_bonus8') ?? null;
    $jumlah_bonus9 = $request->input('jumlah_bonus9') ?? null;
    $jumlah_bonus10 = $request->input('jumlah_bonus10') ?? null;
    $alpha = $request->input('alpha') ?? null;
    //end jumlah bonus dalam kota

    //jumlah bonus luar kota
    $jumlah_bonus_luar = $request->input('jumlah_bonus_luar') ?? null;
    $jumlah_bonus_luar1 = $request->input('jumlah_bonus_luar1') ?? null;
    $jumlah_bonus_luar2 = $request->input('jumlah_bonus_luar2') ?? null;
    $jumlah_bonus_luar3 = $request->input('jumlah_bonus_luar3') ?? null;
    $jumlah_bonus_luar4 = $request->input('jumlah_bonus_luar4') ?? null;
    $jumlah_bonus_luar5 = $request->input('jumlah_bonus_luar5') ?? null;
    $jumlah_bonus_luar6 = $request->input('jumlah_bonus_luar6') ?? null;
    $jumlah_bonus_luar7 = $request->input('jumlah_bonus_luar7') ?? null;
    $jumlah_bonus_luar8 = $request->input('jumlah_bonus_luar8') ?? null;
    $jumlah_bonus_luar9 = $request->input('jumlah_bonus_luar9') ?? null;
    $jumlah_bonus_luar10 = $request->input('jumlah_bonus_luar10') ?? null;
    //end jumlah bonus luar kota

    $total_lembur = ($lembur * $jumlah_lembur) + ($lembur1 * $jumlah_lembur1) + ($lembur2 * $jumlah_lembur2) + ($lembur3 * $jumlah_lembur3) + ($lembur4 * $jumlah_lembur4) + ($lembur5 * $jumlah_lembur5) + ($lembur6 * $jumlah_lembur6) + ($lembur7 * $jumlah_lembur7) + ($lembur8 * $jumlah_lembur8) + ($lembur9 * $jumlah_lembur9) + ($lembur10 * $jumlah_lembur10);
    $total_lembur = empty($total_lembur) ? 0 : str_replace(",", "", $total_lembur);

    // $total_bonus =
    //   ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) +
    //   ($bonus8 * $jumlah_bonus8) + ($bonus9 * $jumlah_bonus9) + ($bonus10 * $jumlah_bonus10) +
    //   ($bonus_luar * $jumlah_bonus_luar) + ($bonus_luar1 * $jumlah_bonus_luar1) + ($bonus_luar2 * $jumlah_bonus_luar2) + ($bonus_luar3 * $jumlah_bonus_luar3) + ($bonus_luar4 * $jumlah_bonus_luar4) + ($bonus_luar5 * $jumlah_bonus_luar5) +
    //   ($bonus_luar6 * $jumlah_bonus_luar6) + ($bonus_luar7 * $jumlah_bonus_luar7) + ($bonus_luar8 * $jumlah_bonus_luar8) + ($bonus_luar9 * $jumlah_bonus_luar9) + ($bonus_luar10 * $jumlah_bonus_luar10);
    // $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $total_bonus =
      ($bonus * $jumlah_bonus) + ($bonus1 * $jumlah_bonus1) + ($bonus2 * $jumlah_bonus2) + ($bonus3 * $jumlah_bonus3) + ($bonus4 * $jumlah_bonus4) + ($bonus5 * $jumlah_bonus5) + ($bonus6 * $jumlah_bonus6) + ($bonus7 * $jumlah_bonus7) + $webinar + $kinerja;
    $total_bonus = empty($total_bonus) ? 0 : str_replace(",", "", $total_bonus);

    $potongan = $request->input('potongan');
    $potongan = empty($potongan) ? 0 : str_replace(",", "", $potongan);

    $pph = $request->input('pph');
    $pph = empty($pph) ? 0 : str_replace(",", "", $pph);

    // <!-- POTONGAN JIKA ALPHA -->
    $subalpha = $jumlah_bonus5 * 0.005;
    $subhasil = $total_gaji_pokok * $subalpha;
    $totalalpha = $total_gaji_pokok - $subhasil;
    // <!-- END -->

    // <!-- TOTAL -->
    $subtotal = $totalalpha + $total_lembur + $total_bonus + $tunjangan + $tunjangan_bpjs + $tunjangan_thr + $tunjangan_pulsa - $potongan - $pph;
    $total = $subtotal;
    $total = empty($total) ? 0 : str_replace(",", "", $total);
    // <!-- END -->

    $existingUserId = $gaji->user_id;


    //save image to path
    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '.' . $image->getClientOriginalExtension();
      $imagePath = $imageName;
      $image->move(public_path('images'), $imageName); // Store the image
    } else {
      // If no new image uploaded, keep using the old image path
      $imagePath = $gaji->gambar;
    }
    //end

    $gaji->update([
      //'id_transaksi' => $id_transaksi,
      'user_id' => $existingUserId,
      'gaji_pokok' => $gaji_pokok,
      'gaji_pokok_ethes_digital' => $gaji_pokok_ethes_digital,
      'total_gaji_pokok' => $total_gaji_pokok,
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
      'webinar' => $webinar,
      'kinerja' => $kinerja,
      'tunjangan' => $tunjangan,
      'tunjangan_bpjs' => $tunjangan_bpjs,
      'tunjangan_thr' => $tunjangan_thr,
      'tunjangan_pulsa' => $tunjangan_pulsa,
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
      'alpha' => $alpha,
      'tanggal' => $request->input('tanggal'),
      'potongan' => $potongan,
      'pph' => $pph,
      'total_lembur' => $total_lembur,
      'total_bonus' => $total_bonus,
      'total' => $total,
      'alpha' => $subhasil,
      'status' => $request->input('status'),
      'note' => $request->input('note'),
      'gambar' => $imagePath, // Store the image path
      'email' => $request->input('email'),
    ]);

    // Redirect with success or error message
    if ($gaji) {
      $user = User::findOrFail($existingUserId);
      $appName = 'Rumah Scopus Foundation';
      $isTerbayar = $request->input('status') == 'terbayar';
      if ($isTerbayar) {
        Mail::to($request->email)->send(new GajiSuccessMail($user, $gaji, $appName, $isTerbayar));
      }

      return redirect()->route('account.gaji.index')->with('success', 'Data Gaji Karyawan Berhasil Diperbarui!');
      // return redirect()->route(
      //   'account.gaji.detail',
      //   ['id' => $id]
      // )->with(
      //   ['success' => 'Data Gaji Karyawan Berhasil Diperbarui!']
      // );
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.gaji.index')->with('error', 'Data Gaji Karyawan Gagal Diperbarui!');
    }
  }
  // <!--================== END ==================-->

  // <!--================== DETAIL DATA ==================-->
  public function detail($id, $token)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

    $users = User::join('gaji', 'users.id', '=', 'gaji.user_id')
      ->where('users.company', $user->company)
      ->get(['users.*']);
    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name',
        'users.nik',
        'users.norek',
        'users.bank',
        'users.telp',
        'users.email',
        DB::raw('SUM(presensi.alpha) as alpha'),
        DB::raw('SUM(presensi.hadir) as hadir'),
        DB::raw('SUM(presensi.camp_jogja) as camp_jogja'),
        DB::raw('SUM(presensi.camp_luar_kota) as camp_luar_kota'),
        DB::raw('SUM(presensi.perjalanan_jawa) as perjalanan_jawa'),
        DB::raw('SUM(presensi.perjalanan_luar_jawa) as perjalanan_luar_jawa'),
        DB::raw('SUM(presensi.remote) as remote'),
        DB::raw('SUM(presensi.izin) as izin')
      )
      ->leftJoin('presensi', 'presensi.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->whereBetween('presensi.created_at', [now()->startOfMonth(), now()->endOfMonth()])
      ->groupBy('users.id', 'users.full_name', 'users.nik', 'users.norek', 'users.bank', 'users.telp', 'users.email')
      ->orderBy('users.created_at', 'DESC')
      ->get();

    return view('account.gaji.detail', compact('gaji', 'users', 'datas')); // Pass 'user' to the view
  }
  // <!--================== END ==================-->

  // <!--================== DELETE DATA ==================-->
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
  // <!--================== END ==================-->

  // <!--================== DOWNLOAD ==================-->
  public function downloadExcel(Request $request)
  {
    $user = Auth::user();
    $search = $request->input('q');
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');

    // Default tanggal: bulan ini jika tidak diisi
    if (!$startDate && !$endDate && !$search) {
      $currentMonth = now()->startOfMonth()->format('Y-m-d 00:00:00');
      $nextMonth = now()->endOfMonth()->format('Y-m-d 23:59:59');
    } else {
      $currentMonth = $startDate ? date('Y-m-d 00:00:00', strtotime($startDate)) : '2000-01-01 00:00:00';
      $nextMonth = $endDate ? date('Y-m-d 23:59:59', strtotime($endDate)) : now()->format('Y-m-d 23:59:59');
    }

    $gajiQuery = DB::table('gaji')
      ->select(
        'gaji.id',
        'gaji.id_transaksi',
        'gaji.token',
        'gaji.gaji_pokok',
        'gaji.gaji_pokok_ethes_digital',
        'gaji.total_gaji_pokok',
        'gaji.lembur',
        'gaji.bonus',
        'gaji.tunjangan',
        'gaji.tanggal',
        'gaji.pph',
        'gaji.total',
        'gaji.status',
        'users.id as user_id',
        'users.full_name as full_name',
        'users.nik as nik',
        'users.norek as norek',
        'users.bank as bank'
      )
      ->leftJoin('users', 'gaji.user_id', '=', 'users.id');

    // Filter role
    if (in_array($user->level, ['manager', 'staff', 'ceo'])) {
      $gajiQuery->where('users.company', $user->company);
    } else {
      $gajiQuery->where('gaji.user_id', $user->id);
    }

    // Filter pencarian jika ada
    if (!empty($search)) {
      $gajiQuery->where(function ($q) use ($search) {
        $q->where('gaji.id_transaksi', 'LIKE', "%{$search}%")
          ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('users.norek', 'LIKE', '%' . $search . '%')
          ->orWhere(DB::raw("CAST(REPLACE(gaji.total, 'Rp', '') AS DECIMAL(10, 2))"), '=', str_replace(['Rp', '.', ','], '', $search))
          ->orWhere(DB::raw("DATE_FORMAT(gaji.tanggal, '%d %M %Y')"), 'LIKE', '%' . $search . '%');
      });
    }

    // Filter tanggal
    $gaji = $gajiQuery
      ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
      ->orderBy('gaji.tanggal', 'DESC')
      ->get();

    // Export Excel
    $excelFileName = 'List-Gaji-Karyawan_' . date('d-m-Y') . '.xlsx';
    return Excel::download(new GajiExport($gaji), $excelFileName);
  }
  // <!--================== END ==================-->

  // <!--================== SLIP GAJI ==================-->
  public function SlipGaji($id)
  {
    $user = Auth::user();
    $gaji = Gaji::findOrFail($id);

    // Calculate total gaji
    $totalGaji = $gaji->total;
    $terbilang = Terbilang::make($totalGaji, ' rupiah');

    // Fetch the associated employee information
    $employee = User::find($gaji->user_id); // Assuming user_id corresponds to the employee's ID
    $userWithNorekBank = User::find($employee->id);

    // Get the HTML content of the view
    $userLogoPath = public_path('images/' . $user->logo_company);

    if (!file_exists($userLogoPath)) {
      // Handle the case where the image file does not exist.
      return response('Image not found', 404);
    }

    $html = view('account.gaji.slipgaji', compact('gaji', 'totalGaji', 'user', 'terbilang', 'employee', 'userWithNorekBank', 'userLogoPath'))->render();

    // Instantiate Dompdf with the default configuration
    $dompdf = new Dompdf();

    // Load the HTML content into Dompdf
    $dompdf->loadHtml($html);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait'); // Change 'potrait' to 'portrait'

    // Render the PDF
    $dompdf->render();

    // Set the response headers
    $headers = [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="Slip-Gaji-Karyawan_' . date('d-m-Y') . '.pdf"',
    ];

    // Output the generated PDF to the browser
    return Response::make($dompdf->output(), 200, $headers);
  }
  // <!--================== END ==================-->

  // <!--================== UPDATE STATUS TERBAYAR ==================-->
  public function updateStatusToTerbayar($gajiId)
  {
    $gaji = Gaji::find($gajiId);

    if ($gaji) {
      $gaji->update(['status' => 'terbayar']);

      // Dispatch the event
      event(new GajiStatusUpdated($gaji));

      return redirect()->route('your.success.route')->with('success', 'Status updated successfully.');
    } else {
      return redirect()->route('your.error.route')->with('error', 'Gaji not found.');
    }
  }
  // <!--================== END ==================-->
}
