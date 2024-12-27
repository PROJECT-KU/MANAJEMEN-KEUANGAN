<?php

namespace App\Http\Controllers\account;

use App\User;
use App\PerjalananDinas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PerjalananDinasController extends Controller
{
  /**
   * PenyewaanController constructor.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  function generateRandomToken($length)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
    $token = '';

    for ($i = 0; $i < $length; $i++) {
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

  public function index(Request $request)
  {
    $user = Auth::user();
    $startDate = $request->input('tanggal_awal');
    $endDate = $request->input('tanggal_akhir');
    $status = $request->input('status');

    if (!$startDate || !$endDate) {
      $currentMonth = date('Y-m-01 00:00:00');
      $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
    } else {
      $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
      $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
    }

    // <!-- DATA AJUKAN -->
    $Ajukan = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->orderBy('perjalanan_dinas.created_at', 'DESC');

    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Ajukan->where('perjalanan_dinas.status', 'ajukan')
        ->where('users.company', $user->company);
    } else {
      $Ajukan->where(function ($query) use ($user) {
        $query->where('perjalanan_dinas.user_id', $user->id)
          ->whereIn('perjalanan_dinas.status', ['draft', 'ajukan']);
      });
    }

    $DatasAjukan = $Ajukan->paginate(10)->appends([
      'tanggal_awal' => $startDate,
      'tanggal_akhir' => $endDate,
      'status' => request('status', 'ajukan')
    ]);
    // Count the number of ajukan
    $countAjukan = DB::table('perjalanan_dinas')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id') // Join the users table
      ->where('perjalanan_dinas.status', 'ajukan')
      ->when($user->level == 'manager' || $user->level == 'ceo', function ($query) use ($user) {
        return $query->where('users.company', $user->company);
      }, function ($query) use ($user) {
        return $query->where('perjalanan_dinas.user_id', $user->id);
      })
      ->count();
    // <!-- END -->

    // <!-- DATA DITERIMA -->
    $Diterima = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('perjalanan_dinas.status', 'diterima')
      ->orderBy('perjalanan_dinas.created_at', 'DESC');

    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Diterima->where('users.company', $user->company);
    } else {
      $Diterima->where('perjalanan_dinas.user_id', $user->id);
    }

    $DatasDiterima = $Diterima->paginate(10)->appends([
      'tanggal_awal' => $startDate,
      'tanggal_akhir' => $endDate,
      'status' => 'diterima'
    ]);
    // <!-- END -->

    // <!-- DATA DITOLAK -->
    $Ditolak = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('perjalanan_dinas.status', 'ditolak')
      ->orderBy('perjalanan_dinas.created_at', 'DESC');

    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Ditolak->where('users.company', $user->company);
    } else {
      $Ditolak->where('perjalanan_dinas.user_id', $user->id);
    }

    $DatasDitolak = $Ditolak->paginate(10)->appends([
      'tanggal_awal' => $startDate,
      'tanggal_akhir' => $endDate,
      'status' => 'ditolak'
    ]);
    // <!-- END -->

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('account.perjalanan_dinas.index', compact('DatasAjukan', 'DatasDiterima', 'DatasDitolak', 'maintenances', 'startDate', 'endDate', 'countAjukan'));
  }

  public function filter(Request $request)
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

    if ($user->level == 'manager' || $user->level == 'ceo') {
      $perjalanan_dinas = DB::table('perjalanan_dinas')
        ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('perjalanan_dinas.created_at', [$currentMonth, $nextMonth])
        ->orderBy('perjalanan_dinas.created_at', 'DESC')
        ->paginate(10);
    } else {
      $perjalanan_dinas = DB::table('perjalanan_dinas')
        ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
        ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
        ->where('perjalanan_dinas.user_id', $user->id)
        ->whereBetween('perjalanan_dinas.created_at', [$currentMonth, $nextMonth])
        ->orderBy('perjalanan_dinas.created_at', 'DESC')
        ->paginate(10);
    }


    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    return view('account.camp.index', compact('camp', 'maintenances', 'startDate', 'endDate'));
  }

  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    // <!-- DATA DIAJUKAN -->
    $Ajukan = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where(function ($Ajukan) use ($search) {
        $Ajukan->where('perjalanan_dinas.id_transaksi', 'LIKE', '%' . $search . '%')
          ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('perjalanan_dinas.tempat', 'LIKE', '%' . $search . '%');
      })
      ->orderBy('perjalanan_dinas.created_at', 'DESC');
    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Ajukan->where('users.company', $user->company);
    } else {
      $Ajukan->where('perjalanan_dinas.user_id', $user->id);
    }
    $Ajukan->where('perjalanan_dinas.status', 'ajukan');
    $DatasAjukan = $Ajukan->paginate(10);
    // <!-- END -->

    // <!-- DATA DITERIMA -->
    $Diterima = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where(function ($Diterima) use ($search) {
        $Diterima->where('perjalanan_dinas.id_transaksi', 'LIKE', '%' . $search . '%')
          ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('perjalanan_dinas.tempat', 'LIKE', '%' . $search . '%');
      })
      ->orderBy('perjalanan_dinas.created_at', 'DESC');
    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Diterima->where('users.company', $user->company);
    } else {
      $Diterima->where('perjalanan_dinas.user_id', $user->id);
    }
    $Diterima->where('perjalanan_dinas.status', 'diterima');
    $DatasDiterima = $Diterima->paginate(10);
    // <!-- END -->

    // <!-- DATA DITOLAK -->
    $Ditolak = DB::table('perjalanan_dinas')
      ->select('perjalanan_dinas.id', 'perjalanan_dinas.user_id', 'perjalanan_dinas.token', 'perjalanan_dinas.id_transaksi', 'perjalanan_dinas.status', 'perjalanan_dinas.tempat', 'perjalanan_dinas.camp', 'perjalanan_dinas.tanggal_mulai', 'perjalanan_dinas.tanggal_akhir', 'perjalanan_dinas.total_uang_masuk', 'perjalanan_dinas.total_uang_keluar', 'perjalanan_dinas.sisa_saldo', 'users.id as user_id', 'users.full_name as full_name', 'users.telp as telp')
      ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where(function ($Ditolak) use ($search) {
        $Ditolak->where('perjalanan_dinas.id_transaksi', 'LIKE', '%' . $search . '%')
          ->orWhere('users.full_name', 'LIKE', '%' . $search . '%')
          ->orWhere('perjalanan_dinas.tempat', 'LIKE', '%' . $search . '%');
      })
      ->orderBy('perjalanan_dinas.created_at', 'DESC');
    if ($user->level == 'manager' || $user->level == 'ceo') {
      $Ditolak->where('users.company', $user->company);
    } else {
      $Ditolak->where('perjalanan_dinas.user_id', $user->id);
    }
    $Ditolak->where('perjalanan_dinas.status', 'ditolak');
    $DatasDitolak = $Ditolak->paginate(10);
    // <!-- END -->

    $maintenances = DB::table('maintenance')
      ->orderBy('created_at', 'DESC')
      ->get();

    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');

    // Jika data tidak ditemukan
    if ($DatasAjukan->isEmpty()) {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Camp tidak ditemukan.');
    }

    // Mengembalikan tampilan
    return view('account.perjalanan_dinas.index', compact('DatasAjukan', 'DatasDiterima', 'DatasDitolak', 'maintenances', 'startDate', 'endDate'));
  }


  public function create()
  {
    $user = Auth::user();

    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();

    // dd($datas);
    return view('account.perjalanan_dinas.create', compact('datas'));
  }

  public function addcreate($id)
  {
    $user = Auth::user();
    $perjalanandinas = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.addcreate', compact('perjalanandinas'));
  }

  public function store(Request $request)
  {
    $user = Auth::user();

    $id_transaksi = $this->generateRandomId(5);
    $token = $this->generateRandomToken(30);

    // <!-- INPUT 1 -->
    $uang_masuk = $request->input('uang_masuk');
    $uang_masuk = empty($uang_masuk) ? null : str_replace(",", "", $uang_masuk);
    $uang_keluar = $request->input('uang_keluar');
    $uang_keluar = empty($uang_keluar) ? null : str_replace(",", "", $uang_keluar);
    $imagePath = null;
    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '_gambar.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 2 -->
    $uang_keluar2 = $request->input('uang_keluar2');
    $uang_keluar2 = empty($uang_keluar2) ? null : str_replace(",", "", $uang_keluar2);
    $imagePath2 = null;
    if ($request->hasFile('gambar2')) {
      $image2 = $request->file('gambar2');
      $imageName2 = time() . '_gambar2.' . $image2->getClientOriginalExtension();
      $imagePath2 = $imageName2; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image2->move(public_path('images'), $imageName2); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 3 -->
    $uang_keluar3 = $request->input('uang_keluar3');
    $uang_keluar3 = empty($uang_keluar3) ? null : str_replace(",", "", $uang_keluar3);
    $imagePath3 = null;
    if ($request->hasFile('gambar3')) {
      $image3 = $request->file('gambar3');
      $imageName3 = time() . '_gambar3.' . $image3->getClientOriginalExtension();
      $imagePath3 = $imageName3; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image3->move(public_path('images'), $imageName3); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 4 -->
    $uang_keluar4 = $request->input('uang_keluar4');
    $uang_keluar4 = empty($uang_keluar4) ? null : str_replace(",", "", $uang_keluar4);
    $imagePath4 = null;
    if ($request->hasFile('gambar4')) {
      $image4 = $request->file('gambar4');
      $imageName4 = time() . '_gambar4.' . $image4->getClientOriginalExtension();
      $imagePath4 = $imageName4; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image4->move(public_path('images'), $imageName4); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 5 -->
    $uang_keluar5 = $request->input('uang_keluar5');
    $uang_keluar5 = empty($uang_keluar5) ? null : str_replace(",", "", $uang_keluar5);
    $imagePath5 = null;
    if ($request->hasFile('gambar5')) {
      $image5 = $request->file('gambar5');
      $imageName5 = time() . '_gambar5.' . $image5->getClientOriginalExtension();
      $imagePath5 = $imageName5; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image5->move(public_path('images'), $imageName5); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 6 -->
    $uang_keluar6 = $request->input('uang_keluar6');
    $uang_keluar6 = empty($uang_keluar6) ? null : str_replace(",", "", $uang_keluar6);
    $imagePath6 = null;
    if ($request->hasFile('gambar6')) {
      $image6 = $request->file('gambar6');
      $imageName6 = time() . '_gambar6.' . $image6->getClientOriginalExtension();
      $imagePath6 = $imageName6; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image6->move(public_path('images'), $imageName6); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 7 -->
    $uang_keluar7 = $request->input('uang_keluar7');
    $uang_keluar7 = empty($uang_keluar7) ? null : str_replace(",", "", $uang_keluar7);
    $imagePath7 = null;
    if ($request->hasFile('gambar7')) {
      $image7 = $request->file('gambar7');
      $imageName7 = time() . '_gambar7.' . $image7->getClientOriginalExtension();
      $imagePath7 = $imageName7; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image7->move(public_path('images'), $imageName7); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 8 -->
    $uang_keluar8 = $request->input('uang_keluar8');
    $uang_keluar8 = empty($uang_keluar8) ? null : str_replace(",", "", $uang_keluar8);
    $imagePath8 = null;
    if ($request->hasFile('gambar8')) {
      $image8 = $request->file('gambar8');
      $imageName8 = time() . '_gambar8.' . $image8->getClientOriginalExtension();
      $imagePath8 = $imageName8; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image8->move(public_path('images'), $imageName8); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 9 -->
    $uang_keluar9 = $request->input('uang_keluar9');
    $uang_keluar9 = empty($uang_keluar9) ? null : str_replace(",", "", $uang_keluar9);
    $imagePath9 = null;
    if ($request->hasFile('gambar9')) {
      $image9 = $request->file('gambar9');
      $imageName9 = time() . '_gambar9.' . $image9->getClientOriginalExtension();
      $imagePath9 = $imageName9; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image9->move(public_path('images'), $imageName9); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 10 -->
    $uang_keluar10 = $request->input('uang_keluar10');
    $uang_keluar10 = empty($uang_keluar10) ? null : str_replace(",", "", $uang_keluar10);
    $imagePath10 = null;
    if ($request->hasFile('gambar10')) {
      $image10 = $request->file('gambar10');
      $imageName10 = time() . '_gambar10.' . $image10->getClientOriginalExtension();
      $imagePath10 = $imageName10; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image10->move(public_path('images'), $imageName10); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 11 -->
    $uang_masuk11 = $request->input('uang_masuk11');
    $uang_masuk11 = empty($uang_masuk11) ? null : str_replace(",", "", $uang_masuk11);
    $uang_keluar11 = $request->input('uang_keluar11');
    $uang_keluar11 = empty($uang_keluar11) ? null : str_replace(",", "", $uang_keluar11);
    $imagePath11 = null;
    if ($request->hasFile('gambar11')) {
      $image11 = $request->file('gambar11');
      $imageName11 = time() . '_gambar11.' . $image11->getClientOriginalExtension();
      $imagePath11 = $imageName11; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image11->move(public_path('images'), $imageName11); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 12 -->
    $uang_keluar12 = $request->input('uang_keluar12');
    $uang_keluar12 = empty($uang_keluar12) ? null : str_replace(",", "", $uang_keluar12);
    $imagePath12 = null;
    if ($request->hasFile('gambar12')) {
      $image12 = $request->file('gambar12');
      $imageName12 = time() . '_gambar12.' . $image12->getClientOriginalExtension();
      $imagePath12 = $imageName12; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image12->move(public_path('images'), $imageName12); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 13 -->
    $uang_keluar13 = $request->input('uang_keluar13');
    $uang_keluar13 = empty($uang_keluar13) ? null : str_replace(",", "", $uang_keluar13);
    $imagePath13 = null;
    if ($request->hasFile('gambar13')) {
      $image13 = $request->file('gambar13');
      $imageName13 = time() . '_gambar13.' . $image13->getClientOriginalExtension();
      $imagePath13 = $imageName13; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image13->move(public_path('images'), $imageName13); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 14 -->
    $uang_keluar14 = $request->input('uang_keluar14');
    $uang_keluar14 = empty($uang_keluar14) ? null : str_replace(",", "", $uang_keluar14);
    $imagePath14 = null;
    if ($request->hasFile('gambar14')) {
      $image14 = $request->file('gambar14');
      $imageName14 = time() . '_gambar14.' . $image14->getClientOriginalExtension();
      $imagePath14 = $imageName14; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image14->move(public_path('images'), $imageName14); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 15 -->
    $uang_keluar15 = $request->input('uang_keluar15');
    $uang_keluar15 = empty($uang_keluar15) ? null : str_replace(",", "", $uang_keluar15);
    $imagePath15 = null;
    if ($request->hasFile('gambar15')) {
      $image15 = $request->file('gambar15');
      $imageName15 = time() . '_gambar15.' . $image15->getClientOriginalExtension();
      $imagePath15 = $imageName15; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image15->move(public_path('images'), $imageName15); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 16 -->
    $uang_keluar16 = $request->input('uang_keluar16');
    $uang_keluar16 = empty($uang_keluar16) ? null : str_replace(",", "", $uang_keluar16);
    $imagePath16 = null;
    if ($request->hasFile('gambar16')) {
      $image16 = $request->file('gambar16');
      $imageName16 = time() . '_gambar16.' . $image16->getClientOriginalExtension();
      $imagePath16 = $imageName16; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image16->move(public_path('images'), $imageName16); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 17 -->
    $uang_keluar17 = $request->input('uang_keluar17');
    $uang_keluar17 = empty($uang_keluar17) ? null : str_replace(",", "", $uang_keluar17);
    $imagePath17 = null;
    if ($request->hasFile('gambar17')) {
      $image17 = $request->file('gambar17');
      $imageName17 = time() . '_gambar17.' . $image17->getClientOriginalExtension();
      $imagePath17 = $imageName17; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image17->move(public_path('images'), $imageName17); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 18 -->
    $uang_keluar18 = $request->input('uang_keluar18');
    $uang_keluar18 = empty($uang_keluar18) ? null : str_replace(",", "", $uang_keluar18);
    $imagePath18 = null;
    if ($request->hasFile('gambar18')) {
      $image18 = $request->file('gambar18');
      $imageName18 = time() . '_gambar18.' . $image18->getClientOriginalExtension();
      $imagePath18 = $imageName18; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image18->move(public_path('images'), $imageName18); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 19 -->
    $uang_keluar19 = $request->input('uang_keluar19');
    $uang_keluar19 = empty($uang_keluar19) ? null : str_replace(",", "", $uang_keluar19);
    $imagePath19 = null;
    if ($request->hasFile('gambar19')) {
      $image19 = $request->file('gambar19');
      $imageName19 = time() . '_gambar19.' . $image19->getClientOriginalExtension();
      $imagePath19 = $imageName19; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image19->move(public_path('images'), $imageName19); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 20 -->
    $uang_keluar20 = $request->input('uang_keluar20');
    $uang_keluar20 = empty($uang_keluar20) ? null : str_replace(",", "", $uang_keluar20);
    $imagePath20 = null;
    if ($request->hasFile('gambar20')) {
      $image20 = $request->file('gambar20');
      $imageName20 = time() . '_gambar20.' . $image20->getClientOriginalExtension();
      $imagePath20 = $imageName20; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image20->move(public_path('images'), $imageName20); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- TOTAL UANG MASUK -->
    $total_uang_masuk = $uang_masuk + $uang_masuk11;
    // <!-- END -->

    // <!-- TOAL UANG KELUAR -->
    $total_uang_keluar = $uang_keluar + $uang_keluar2 + $uang_keluar3 + $uang_keluar4 + $uang_keluar5 + $uang_keluar6 + $uang_keluar7 + $uang_keluar8 + $uang_keluar9 + $uang_keluar10 + $uang_keluar11 + $uang_keluar12 + $uang_keluar13 + $uang_keluar14 + $uang_keluar15 + $uang_keluar16 + $uang_keluar17 + $uang_keluar18 + $uang_keluar19 + $uang_keluar20;
    // <!-- END -->

    // <!-- SISA SALDO -->
    $sisa_saldo = $total_uang_masuk - $total_uang_keluar;
    // <!-- END -->

    $save = PerjalananDinas::create([
      'id_transaksi'            => $id_transaksi,
      'token'                   => $token,
      'user_id'                 => $request->input('user_id'),
      'tempat'                  => $request->input('tempat'),
      'camp'                    => $request->input('camp'),
      'tanggal_mulai'           => $request->input('tanggal_mulai'),
      'tanggal_akhir'           => $request->input('tanggal_akhir'),
      'status'                  => $request->input('status'),

      // <!-- INPUT 1 -->
      'tanggal'                  => $request->input('tanggal'),
      'uang_masuk'               => $uang_masuk,
      'uang_keluar'              => $uang_keluar,
      'keterangan'               => $request->input('keterangan'),
      'gambar'                   => $imagePath,
      // <!-- END -->

      // <!-- INPUT 2 -->
      'uang_keluar2'              => $uang_keluar2,
      'keterangan2'               => $request->input('keterangan2'),
      'gambar2'                   => $imagePath2,
      // <!-- END -->

      // <!-- INPUT 3 -->
      'uang_keluar3'              => $uang_keluar3,
      'keterangan3'               => $request->input('keterangan3'),
      'gambar3'                   => $imagePath3,
      // <!-- END -->

      // <!-- INPUT 4 -->
      'uang_keluar4'              => $uang_keluar4,
      'keterangan4'               => $request->input('keterangan4'),
      'gambar4'                   => $imagePath4,
      // <!-- END -->

      // <!-- INPUT 5 -->
      'uang_keluar5'              => $uang_keluar5,
      'keterangan5'               => $request->input('keterangan5'),
      'gambar5'                   => $imagePath5,
      // <!-- END -->

      // <!-- INPUT 6 -->
      'uang_keluar6'              => $uang_keluar6,
      'keterangan6'               => $request->input('keterangan6'),
      'gambar6'                   => $imagePath6,
      // <!-- END -->

      // <!-- INPUT 7 -->
      'uang_keluar7'              => $uang_keluar7,
      'keterangan7'               => $request->input('keterangan7'),
      'gambar7'                   => $imagePath7,
      // <!-- END -->

      // <!-- INPUT 8 -->
      'uang_keluar8'              => $uang_keluar8,
      'keterangan8'               => $request->input('keterangan8'),
      'gambar8'                   => $imagePath8,
      // <!-- END -->

      // <!-- INPUT 9 -->
      'uang_keluar9'              => $uang_keluar9,
      'keterangan9'               => $request->input('keterangan9'),
      'gambar9'                   => $imagePath9,
      // <!-- END -->

      // <!-- INPUT 10 -->
      'uang_keluar10'              => $uang_keluar10,
      'keterangan10'               => $request->input('keterangan10'),
      'gambar10'                   => $imagePath10,
      // <!-- END -->

      // <!-- INPUT 11 -->
      'tanggal11'                  => $request->input('tanggal11'),
      'uang_masuk11'               => $uang_masuk11,
      'uang_keluar11'              => $uang_keluar11,
      'keterangan11'               => $request->input('keterangan11'),
      'gambar11'                   => $imagePath11,
      // <!-- END -->

      // <!-- INPUT 12 -->
      'uang_keluar12'              => $uang_keluar12,
      'keterangan12'               => $request->input('keterangan12'),
      'gambar12'                   => $imagePath12,
      // <!-- END -->

      // <!-- INPUT 13 -->
      'uang_keluar13'              => $uang_keluar13,
      'keterangan13'               => $request->input('keterangan13'),
      'gambar13'                   => $imagePath13,
      // <!-- END -->

      // <!-- INPUT 14 -->
      'uang_keluar14'              => $uang_keluar14,
      'keterangan14'               => $request->input('keterangan14'),
      'gambar14'                   => $imagePath14,
      // <!-- END -->

      // <!-- INPUT 15 -->
      'uang_keluar15'              => $uang_keluar15,
      'keterangan15'               => $request->input('keterangan15'),
      'gambar15'                   => $imagePath15,
      // <!-- END -->

      // <!-- INPUT 16 -->
      'uang_keluar16'              => $uang_keluar16,
      'keterangan16'               => $request->input('keterangan16'),
      'gambar16'                   => $imagePath16,
      // <!-- END -->

      // <!-- INPUT 17 -->
      'uang_keluar17'              => $uang_keluar17,
      'keterangan17'               => $request->input('keterangan17'),
      'gambar17'                   => $imagePath17,
      // <!-- END -->

      // <!-- INPUT 18 -->
      'uang_keluar18'              => $uang_keluar18,
      'keterangan18'               => $request->input('keterangan18'),
      'gambar18'                   => $imagePath18,
      // <!-- END -->

      // <!-- INPUT 19 -->
      'uang_keluar19'              => $uang_keluar19,
      'keterangan19'               => $request->input('keterangan19'),
      'gambar19'                   => $imagePath19,
      // <!-- END -->

      // <!-- INPUT 20 -->
      'uang_keluar20'              => $uang_keluar20,
      'keterangan20'               => $request->input('keterangan20'),
      'gambar20'                   => $imagePath20,
      // <!-- END -->

      'total_uang_masuk'           => $total_uang_masuk,
      'total_uang_keluar'          => $total_uang_keluar,
      'sisa_saldo'                 => $sisa_saldo,

    ]);

    // Redirect with success or error message
    if ($save) {
      if ($request->input('action') === 'save_add') {
        return redirect()->route('account.PerjalananDinas.addcreate', ['id' => $save])->with('next', 'Data Perjalanan Dinas Berhasil Disimpan!');
      } else {
        return redirect()->route('account.PerjalananDinas.index')->with('success', 'Data Perjalanan Dinas Berhasil Disimpan!');
      }
    } else {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Perjalanan Dinas Gagal Disimpan!');
    }
  }

  public function addstore(Request $request, $id)
  {
    $user = Auth::user();
    $perjalanandinas = PerjalananDinas::findOrFail($id);
    // <!-- INPUT 21 -->
    $uang_masuk21 = $request->input('uang_masuk21');
    $uang_masuk21 = empty($uang_masuk21) ? null : str_replace(",", "", $uang_masuk21);
    $uang_keluar21 = $request->input('uang_keluar21');
    $uang_keluar21 = empty($uang_keluar21) ? null : str_replace(",", "", $uang_keluar21);
    $imagePath21 = null;
    if ($request->hasFile('gambar21')) {
      $image21 = $request->file('gambar21');
      $imageName21 = time() . '_gambar21.' . $image21->getClientOriginalExtension();
      $imagePath21 = $imageName21; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image21->move(public_path('images'), $imageName21); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 22 -->
    $uang_keluar22 = $request->input('uang_keluar22');
    $uang_keluar22 = empty($uang_keluar22) ? null : str_replace(",", "", $uang_keluar22);
    $imagePath22 = null;
    if ($request->hasFile('gambar22')) {
      $image22 = $request->file('gambar22');
      $imageName22 = time() . '_gambar22.' . $image22->getClientOriginalExtension();
      $imagePath22 = $imageName22; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image22->move(public_path('images'), $imageName22); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 23 -->
    $uang_keluar23 = $request->input('uang_keluar23');
    $uang_keluar23 = empty($uang_keluar23) ? null : str_replace(",", "", $uang_keluar23);
    $imagePath23 = null;
    if ($request->hasFile('gambar23')) {
      $image23 = $request->file('gambar23');
      $imageName23 = time() . '_gambar23.' . $image23->getClientOriginalExtension();
      $imagePath23 = $imageName23; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image23->move(public_path('images'), $imageName23); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 24 -->
    $uang_keluar24 = $request->input('uang_keluar24');
    $uang_keluar24 = empty($uang_keluar24) ? null : str_replace(",", "", $uang_keluar24);
    $imagePath24 = null;
    if ($request->hasFile('gambar24')) {
      $image24 = $request->file('gambar24');
      $imageName24 = time() . '_gambar24.' . $image24->getClientOriginalExtension();
      $imagePath24 = $imageName24; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image24->move(public_path('images'), $imageName24); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 25 -->
    $uang_keluar25 = $request->input('uang_keluar25');
    $uang_keluar25 = empty($uang_keluar25) ? null : str_replace(",", "", $uang_keluar25);
    $imagePath25 = null;
    if ($request->hasFile('gambar25')) {
      $image25 = $request->file('gambar25');
      $imageName25 = time() . '_gambar25.' . $image25->getClientOriginalExtension();
      $imagePath25 = $imageName25; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image25->move(public_path('images'), $imageName25); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 26 -->
    $uang_keluar26 = $request->input('uang_keluar26');
    $uang_keluar26 = empty($uang_keluar26) ? null : str_replace(",", "", $uang_keluar26);
    $imagePath26 = null;
    if ($request->hasFile('gambar26')) {
      $image26 = $request->file('gambar26');
      $imageName26 = time() . '_gambar26.' . $image26->getClientOriginalExtension();
      $imagePath26 = $imageName26; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image26->move(public_path('images'), $imageName26); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 27 -->
    $uang_keluar27 = $request->input('uang_keluar27');
    $uang_keluar27 = empty($uang_keluar27) ? null : str_replace(",", "", $uang_keluar27);
    $imagePath27 = null;
    if ($request->hasFile('gambar27')) {
      $image27 = $request->file('gambar27');
      $imageName27 = time() . '_gambar27.' . $image27->getClientOriginalExtension();
      $imagePath27 = $imageName27; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image27->move(public_path('images'), $imageName27); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 28 -->
    $uang_keluar28 = $request->input('uang_keluar28');
    $uang_keluar28 = empty($uang_keluar28) ? null : str_replace(",", "", $uang_keluar28);
    $imagePath28 = null;
    if ($request->hasFile('gambar28')) {
      $image28 = $request->file('gambar28');
      $imageName28 = time() . '_gambar28.' . $image28->getClientOriginalExtension();
      $imagePath28 = $imageName28; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image28->move(public_path('images'), $imageName28); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 29 -->
    $uang_keluar29 = $request->input('uang_keluar29');
    $uang_keluar29 = empty($uang_keluar29) ? null : str_replace(",", "", $uang_keluar29);
    $imagePath29 = null;
    if ($request->hasFile('gambar29')) {
      $image29 = $request->file('gambar29');
      $imageName29 = time() . '_gambar29.' . $image29->getClientOriginalExtension();
      $imagePath29 = $imageName29; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image29->move(public_path('images'), $imageName29); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 30 -->
    $uang_keluar30 = $request->input('uang_keluar30');
    $uang_keluar30 = empty($uang_keluar30) ? null : str_replace(",", "", $uang_keluar30);
    $imagePath30 = null;
    if ($request->hasFile('gambar30')) {
      $image30 = $request->file('gambar30');
      $imageName30 = time() . '_gambar30.' . $image30->getClientOriginalExtension();
      $imagePath30 = $imageName30; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image30->move(public_path('images'), $imageName30); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 31 -->
    $uang_masuk31 = $request->input('uang_masuk31');
    $uang_masuk31 = empty($uang_masuk31) ? null : str_replace(",", "", $uang_masuk31);
    $uang_keluar31 = $request->input('uang_keluar31');
    $uang_keluar31 = empty($uang_keluar31) ? null : str_replace(",", "", $uang_keluar31);
    $imagePath31 = null;
    if ($request->hasFile('gambar31')) {
      $image31 = $request->file('gambar31');
      $imageName31 = time() . '_gambar31.' . $image31->getClientOriginalExtension();
      $imagePath31 = $imageName31; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image31->move(public_path('images'), $imageName31); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 32 -->
    $uang_keluar32 = $request->input('uang_keluar32');
    $uang_keluar32 = empty($uang_keluar32) ? null : str_replace(",", "", $uang_keluar32);
    $imagePath32 = null;
    if ($request->hasFile('gambar32')) {
      $image32 = $request->file('gambar32');
      $imageName32 = time() . '_gambar32.' . $image32->getClientOriginalExtension();
      $imagePath32 = $imageName32; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image32->move(public_path('images'), $imageName32); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 33 -->
    $uang_keluar33 = $request->input('uang_keluar33');
    $uang_keluar33 = empty($uang_keluar33) ? null : str_replace(",", "", $uang_keluar33);
    $imagePath33 = null;
    if ($request->hasFile('gambar33')) {
      $image33 = $request->file('gambar33');
      $imageName33 = time() . '_gambar33.' . $image33->getClientOriginalExtension();
      $imagePath33 = $imageName33; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image33->move(public_path('images'), $imageName33); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 34 -->
    $uang_keluar34 = $request->input('uang_keluar34');
    $uang_keluar34 = empty($uang_keluar34) ? null : str_replace(",", "", $uang_keluar34);
    $imagePath34 = null;
    if ($request->hasFile('gambar34')) {
      $image34 = $request->file('gambar34');
      $imageName34 = time() . '_gambar34.' . $image34->getClientOriginalExtension();
      $imagePath34 = $imageName34; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image34->move(public_path('images'), $imageName34); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 35 -->
    $uang_keluar35 = $request->input('uang_keluar35');
    $uang_keluar35 = empty($uang_keluar35) ? null : str_replace(",", "", $uang_keluar35);
    $imagePath35 = null;
    if ($request->hasFile('gambar35')) {
      $image35 = $request->file('gambar35');
      $imageName35 = time() . '_gambar35.' . $image35->getClientOriginalExtension();
      $imagePath35 = $imageName35; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image35->move(public_path('images'), $imageName35); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 36 -->
    $uang_masuk36 = $request->input('uang_masuk36');
    $uang_masuk36 = empty($uang_masuk36) ? null : str_replace(",", "", $uang_masuk36);
    $uang_keluar36 = $request->input('uang_keluar36');
    $uang_keluar36 = empty($uang_keluar36) ? null : str_replace(",", "", $uang_keluar36);
    $imagePath36 = null;
    if ($request->hasFile('gambar36')) {
      $image36 = $request->file('gambar36');
      $imageName36 = time() . '_gambar36.' . $image36->getClientOriginalExtension();
      $imagePath36 = $imageName36; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image36->move(public_path('images'), $imageName36); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 37 -->
    $uang_keluar37 = $request->input('uang_keluar37');
    $uang_keluar37 = empty($uang_keluar37) ? null : str_replace(",", "", $uang_keluar37);
    $imagePath37 = null;
    if ($request->hasFile('gambar37')) {
      $image37 = $request->file('gambar37');
      $imageName37 = time() . '_gambar37.' . $image37->getClientOriginalExtension();
      $imagePath37 = $imageName37; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image37->move(public_path('images'), $imageName37); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 38 -->
    $uang_keluar38 = $request->input('uang_keluar38');
    $uang_keluar38 = empty($uang_keluar38) ? null : str_replace(",", "", $uang_keluar38);
    $imagePath38 = null;
    if ($request->hasFile('gambar38')) {
      $image38 = $request->file('gambar38');
      $imageName38 = time() . '_gambar38.' . $image38->getClientOriginalExtension();
      $imagePath38 = $imageName38; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image38->move(public_path('images'), $imageName38); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 39 -->
    $uang_keluar39 = $request->input('uang_keluar39');
    $uang_keluar39 = empty($uang_keluar39) ? null : str_replace(",", "", $uang_keluar39);
    $imagePath39 = null;
    if ($request->hasFile('gambar39')) {
      $image39 = $request->file('gambar39');
      $imageName39 = time() . '_gambar39.' . $image39->getClientOriginalExtension();
      $imagePath39 = $imageName39; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image39->move(public_path('images'), $imageName39); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 40 -->
    $uang_keluar40 = $request->input('uang_keluar40');
    $uang_keluar40 = empty($uang_keluar40) ? null : str_replace(",", "", $uang_keluar40);
    $imagePath40 = null;
    if ($request->hasFile('gambar40')) {
      $image40 = $request->file('gambar40');
      $imageName40 = time() . '_gambar40.' . $image40->getClientOriginalExtension();
      $imagePath40 = $imageName40; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image40->move(public_path('images'), $imageName40); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- TOTAL UANG MASUK -->
    $total_uang_masuk = $uang_masuk21 + $uang_masuk31 + $uang_masuk36;
    // <!-- END -->

    // <!-- TOTAL UANG KELUAR -->
    $total_uang_keluar = $uang_keluar21 + $uang_keluar22 + $uang_keluar23 + $uang_keluar24 + $uang_keluar25 + $uang_keluar26 + $uang_keluar27 + $uang_keluar28 + $uang_keluar29 + $uang_keluar30 + $uang_keluar31 + $uang_keluar32 + $uang_keluar33 + $uang_keluar34 + $uang_keluar35 + $uang_keluar36 + $uang_keluar37 + $uang_keluar38 + $uang_keluar39 + $uang_keluar40;
    // <!-- END -->

    // <!-- SISA SALDO -->
    $sisa_saldo = $total_uang_masuk - $total_uang_keluar;
    // <!-- END -->

    $perjalanandinas->update([
      // <!-- INPUT 21 -->
      'tanggal21'                  => $request->input('tanggal21'),
      'uang_masuk21'               => $uang_masuk21,
      'uang_keluar21'              => $uang_keluar21,
      'keterangan21'               => $request->input('keterangan21'),
      'gambar21'                   => $imagePath21,
      // <!-- END -->

      // <!-- INPUT 22 -->
      'uang_keluar22'              => $uang_keluar22,
      'keterangan22'               => $request->input('keterangan22'),
      'gambar22'                   => $imagePath22,
      // <!-- END -->

      // <!-- INPUT 23 -->
      'uang_keluar23'              => $uang_keluar23,
      'keterangan23'               => $request->input('keterangan23'),
      'gambar23'                   => $imagePath23,
      // <!-- END -->

      // <!-- INPUT 24 -->
      'uang_keluar24'              => $uang_keluar24,
      'keterangan24'               => $request->input('keterangan24'),
      'gambar24'                   => $imagePath24,
      // <!-- END -->

      // <!-- INPUT 25 -->
      'uang_keluar25'              => $uang_keluar25,
      'keterangan25'               => $request->input('keterangan25'),
      'gambar25'                   => $imagePath25,
      // <!-- END -->

      // <!-- INPUT 26 -->
      'uang_keluar26'              => $uang_keluar26,
      'keterangan26'               => $request->input('keterangan26'),
      'gambar26'                   => $imagePath26,
      // <!-- END -->

      // <!-- INPUT 27 -->
      'uang_keluar27'              => $uang_keluar27,
      'keterangan27'               => $request->input('keterangan27'),
      'gambar27'                   => $imagePath27,
      // <!-- END -->

      // <!-- INPUT 28 -->
      'uang_keluar28'              => $uang_keluar28,
      'keterangan28'               => $request->input('keterangan28'),
      'gambar28'                   => $imagePath28,
      // <!-- END -->

      // <!-- INPUT 29 -->
      'uang_keluar29'              => $uang_keluar29,
      'keterangan29'               => $request->input('keterangan29'),
      'gambar29'                   => $imagePath29,
      // <!-- END -->

      // <!-- INPUT 30 -->
      'uang_keluar30'              => $uang_keluar30,
      'keterangan30'               => $request->input('keterangan30'),
      'gambar30'                   => $imagePath30,
      // <!-- END -->

      // <!-- INPUT 31 -->
      'tanggal31'                  => $request->input('tanggal31'),
      'uang_masuk31'               => $uang_masuk31,
      'uang_keluar31'              => $uang_keluar31,
      'keterangan31'               => $request->input('keterangan31'),
      'gambar31'                   => $imagePath31,
      // <!-- END -->

      // <!-- INPUT 32 -->
      'uang_keluar32'              => $uang_keluar32,
      'keterangan32'               => $request->input('keterangan32'),
      'gambar32'                   => $imagePath32,
      // <!-- END -->

      // <!-- INPUT 33 -->
      'uang_keluar33'              => $uang_keluar33,
      'keterangan33'               => $request->input('keterangan33'),
      'gambar33'                   => $imagePath33,
      // <!-- END -->

      // <!-- INPUT 34 -->
      'uang_keluar34'              => $uang_keluar34,
      'keterangan34'               => $request->input('keterangan34'),
      'gambar34'                   => $imagePath34,
      // <!-- END -->

      // <!-- INPUT 35 -->
      'uang_keluar35'              => $uang_keluar35,
      'keterangan35'               => $request->input('keterangan35'),
      'gambar35'                   => $imagePath35,
      // <!-- END -->

      // <!-- INPUT 36 -->
      'tanggal36'                  => $request->input('tanggal36'),
      'uang_masuk36'               => $uang_masuk36,
      'uang_keluar36'              => $uang_keluar36,
      'keterangan36'               => $request->input('keterangan36'),
      'gambar36'                   => $imagePath36,
      // <!-- END -->

      // <!-- INPUT 37 -->
      'uang_keluar37'              => $uang_keluar37,
      'keterangan37'               => $request->input('keterangan37'),
      'gambar37'                   => $imagePath37,
      // <!-- END -->

      // <!-- INPUT 38 -->
      'uang_keluar38'              => $uang_keluar38,
      'keterangan38'               => $request->input('keterangan38'),
      'gambar38'                   => $imagePath38,
      // <!-- END -->

      // <!-- INPUT 39 -->
      'uang_keluar39'              => $uang_keluar39,
      'keterangan39'               => $request->input('keterangan39'),
      'gambar39'                   => $imagePath39,
      // <!-- END -->

      // <!-- INPUT 40 -->
      'uang_keluar40'              => $uang_keluar40,
      'keterangan40'               => $request->input('keterangan40'),
      'gambar40'                   => $imagePath40,
      // <!-- END -->

      'total_uang_masuk'           => $request->input('totalmasukpage1') + $total_uang_masuk,
      'total_uang_keluar'          => $request->input('totalkeluarpage1') + $total_uang_keluar,
      'sisa_saldo'                 => $request->input('sisasaldopage1') + $sisa_saldo,
    ]);

    // Redirect with success or error message
    if ($perjalanandinas) {
      return redirect()->route('account.PerjalananDinas.index')->with('success', 'Data Perjalanan Dinas Berhasil Disimpan!');
    } else {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Perjalanan Dinas Gagal Disimpan!');
    }
  }

  public function DetailAjukan($id)
  {
    $user = Auth::user();
    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();
    $DatasAjukan = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.detailajukan', compact('DatasAjukan', 'datas'));
  }

  public function DetailDiterima($id)
  {
    $user = Auth::user();
    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();
    $DatasDiterima = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.detailditerima', compact('DatasDiterima', 'datas'));
  }

  public function DetailDitolak($id)
  {
    $user = Auth::user();
    $datas = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();
    $DatasDitolak = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.detailditolak', compact('DatasDitolak', 'datas'));
  }

  public function Edit($id)
  {
    $user = Auth::user();

    $data = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();
    $DatasEdit = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.edit', compact('data', 'DatasEdit'));
  }

  public function AddEdit($id)
  {
    $user = Auth::user();

    $data = DB::table('users')
      ->select(
        'users.id',
        'users.full_name'
      )
      ->leftJoin('perjalanan_dinas', 'perjalanan_dinas.user_id', '=', 'users.id')
      ->where('users.company', $user->company)
      ->groupBy('users.id', 'users.full_name')
      ->orderBy('users.created_at', 'DESC')
      ->get();
    $DatasEdit = PerjalananDinas::findOrFail($id);
    return view('account.perjalanan_dinas.addedit', compact('data', 'DatasEdit'));
  }

  public function UpdateEdit(Request $request, $id)
  {
    $user = Auth::user();
    $DatasEdit = PerjalananDinas::findOrFail($id);
    // <!-- INPUT 1 -->
    $uang_masuk = $request->input('uang_masuk');
    $uang_masuk = empty($uang_masuk) ? null : str_replace(",", "", $uang_masuk);
    $uang_keluar = $request->input('uang_keluar');
    $uang_keluar = empty($uang_keluar) ? null : str_replace(",", "", $uang_keluar);
    $imagePath = $DatasEdit->gambar;
    if ($request->hasFile('gambar')) {
      $image = $request->file('gambar');
      $imageName = time() . '_gambar.' . $image->getClientOriginalExtension();
      $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 2 -->
    $uang_keluar2 = $request->input('uang_keluar2');
    $uang_keluar2 = empty($uang_keluar2) ? null : str_replace(",", "", $uang_keluar2);
    $imagePath2 = $DatasEdit->gambar2;
    if ($request->hasFile('gambar2')) {
      $image2 = $request->file('gambar2');
      $imageName2 = time() . '_gambar2.' . $image2->getClientOriginalExtension();
      $imagePath2 = $imageName2; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image2->move(public_path('images'), $imageName2); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 3 -->
    $uang_keluar3 = $request->input('uang_keluar3');
    $uang_keluar3 = empty($uang_keluar3) ? null : str_replace(",", "", $uang_keluar3);
    $imagePath3 = $DatasEdit->gambar3;
    if ($request->hasFile('gambar3')) {
      $image3 = $request->file('gambar3');
      $imageName3 = time() . '_gambar3.' . $image3->getClientOriginalExtension();
      $imagePath3 = $imageName3; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image3->move(public_path('images'), $imageName3); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 4 -->
    $uang_keluar4 = $request->input('uang_keluar4');
    $uang_keluar4 = empty($uang_keluar4) ? null : str_replace(",", "", $uang_keluar4);
    $imagePath4 = $DatasEdit->gambar4;
    if ($request->hasFile('gambar4')) {
      $image4 = $request->file('gambar4');
      $imageName4 = time() . '_gambar4.' . $image4->getClientOriginalExtension();
      $imagePath4 = $imageName4; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image4->move(public_path('images'), $imageName4); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 5 -->
    $uang_keluar5 = $request->input('uang_keluar5');
    $uang_keluar5 = empty($uang_keluar5) ? null : str_replace(",", "", $uang_keluar5);
    $imagePath5 = $DatasEdit->gambar5;
    if ($request->hasFile('gambar5')) {
      $image5 = $request->file('gambar5');
      $imageName5 = time() . '_gambar5.' . $image5->getClientOriginalExtension();
      $imagePath5 = $imageName5; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image5->move(public_path('images'), $imageName5); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 6 -->
    $uang_keluar6 = $request->input('uang_keluar6');
    $uang_keluar6 = empty($uang_keluar6) ? null : str_replace(",", "", $uang_keluar6);
    $imagePath6 = $DatasEdit->gambar6;
    if ($request->hasFile('gambar6')) {
      $image6 = $request->file('gambar6');
      $imageName6 = time() . '_gambar6.' . $image6->getClientOriginalExtension();
      $imagePath6 = $imageName6; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image6->move(public_path('images'), $imageName6); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 7 -->
    $uang_keluar7 = $request->input('uang_keluar7');
    $uang_keluar7 = empty($uang_keluar7) ? null : str_replace(",", "", $uang_keluar7);
    $imagePath7 = $DatasEdit->gambar7;
    if ($request->hasFile('gambar7')) {
      $image7 = $request->file('gambar7');
      $imageName7 = time() . '_gambar7.' . $image7->getClientOriginalExtension();
      $imagePath7 = $imageName7; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image7->move(public_path('images'), $imageName7); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 8 -->
    $uang_keluar8 = $request->input('uang_keluar8');
    $uang_keluar8 = empty($uang_keluar8) ? null : str_replace(",", "", $uang_keluar8);
    $imagePath8 = $DatasEdit->gambar8;
    if ($request->hasFile('gambar8')) {
      $image8 = $request->file('gambar8');
      $imageName8 = time() . '_gambar8.' . $image8->getClientOriginalExtension();
      $imagePath8 = $imageName8; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image8->move(public_path('images'), $imageName8); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 9 -->
    $uang_keluar9 = $request->input('uang_keluar9');
    $uang_keluar9 = empty($uang_keluar9) ? null : str_replace(",", "", $uang_keluar9);
    $imagePath9 = $DatasEdit->gambar9;
    if ($request->hasFile('gambar9')) {
      $image9 = $request->file('gambar9');
      $imageName9 = time() . '_gambar9.' . $image9->getClientOriginalExtension();
      $imagePath9 = $imageName9; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image9->move(public_path('images'), $imageName9); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 10 -->
    $uang_keluar10 = $request->input('uang_keluar10');
    $uang_keluar10 = empty($uang_keluar10) ? null : str_replace(",", "", $uang_keluar10);
    $imagePath10 = $DatasEdit->gambar10;
    if ($request->hasFile('gambar10')) {
      $image10 = $request->file('gambar10');
      $imageName10 = time() . '_gambar10.' . $image10->getClientOriginalExtension();
      $imagePath10 = $imageName10; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image10->move(public_path('images'), $imageName10); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 11 -->
    $uang_masuk11 = $request->input('uang_masuk11');
    $uang_masuk11 = empty($uang_masuk11) ? null : str_replace(",", "", $uang_masuk11);
    $uang_keluar11 = $request->input('uang_keluar11');
    $uang_keluar11 = empty($uang_keluar11) ? null : str_replace(",", "", $uang_keluar11);
    $imagePath11 = $DatasEdit->gambar11;
    if ($request->hasFile('gambar11')) {
      $image11 = $request->file('gambar11');
      $imageName11 = time() . '_gambar11.' . $image11->getClientOriginalExtension();
      $imagePath11 = $imageName11; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image11->move(public_path('images'), $imageName11); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 12 -->
    $uang_keluar12 = $request->input('uang_keluar12');
    $uang_keluar12 = empty($uang_keluar12) ? null : str_replace(",", "", $uang_keluar12);
    $imagePath12 = $DatasEdit->gambar12;
    if ($request->hasFile('gambar12')) {
      $image12 = $request->file('gambar12');
      $imageName12 = time() . '_gambar12.' . $image12->getClientOriginalExtension();
      $imagePath12 = $imageName12; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image12->move(public_path('images'), $imageName12); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 13 -->
    $uang_keluar13 = $request->input('uang_keluar13');
    $uang_keluar13 = empty($uang_keluar13) ? null : str_replace(",", "", $uang_keluar13);
    $imagePath13 = $DatasEdit->gambar13;
    if ($request->hasFile('gambar13')) {
      $image13 = $request->file('gambar13');
      $imageName13 = time() . '_gambar13.' . $image13->getClientOriginalExtension();
      $imagePath13 = $imageName13; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image13->move(public_path('images'), $imageName13); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 14 -->
    $uang_keluar14 = $request->input('uang_keluar14');
    $uang_keluar14 = empty($uang_keluar14) ? null : str_replace(",", "", $uang_keluar14);
    $imagePath14 = $DatasEdit->gambar14;
    if ($request->hasFile('gambar14')) {
      $image14 = $request->file('gambar14');
      $imageName14 = time() . '_gambar14.' . $image14->getClientOriginalExtension();
      $imagePath14 = $imageName14; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image14->move(public_path('images'), $imageName14); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 15 -->
    $uang_keluar15 = $request->input('uang_keluar15');
    $uang_keluar15 = empty($uang_keluar15) ? null : str_replace(",", "", $uang_keluar15);
    $imagePath15 = $DatasEdit->gambar15;
    if ($request->hasFile('gambar15')) {
      $image15 = $request->file('gambar15');
      $imageName15 = time() . '_gambar15.' . $image15->getClientOriginalExtension();
      $imagePath15 = $imageName15; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image15->move(public_path('images'), $imageName15); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 16 -->
    $uang_keluar16 = $request->input('uang_keluar16');
    $uang_keluar16 = empty($uang_keluar16) ? null : str_replace(",", "", $uang_keluar16);
    $imagePath16 = $DatasEdit->gambar16;
    if ($request->hasFile('gambar16')) {
      $image16 = $request->file('gambar16');
      $imageName16 = time() . '_gambar16.' . $image16->getClientOriginalExtension();
      $imagePath16 = $imageName16; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image16->move(public_path('images'), $imageName16); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 17 -->
    $uang_keluar17 = $request->input('uang_keluar17');
    $uang_keluar17 = empty($uang_keluar17) ? null : str_replace(",", "", $uang_keluar17);
    $imagePath17 = $DatasEdit->gambar17;
    if ($request->hasFile('gambar17')) {
      $image17 = $request->file('gambar17');
      $imageName17 = time() . '_gambar17.' . $image17->getClientOriginalExtension();
      $imagePath17 = $imageName17; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image17->move(public_path('images'), $imageName17); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 18 -->
    $uang_keluar18 = $request->input('uang_keluar18');
    $uang_keluar18 = empty($uang_keluar18) ? null : str_replace(",", "", $uang_keluar18);
    $imagePath18 = $DatasEdit->gambar18;
    if ($request->hasFile('gambar18')) {
      $image18 = $request->file('gambar18');
      $imageName18 = time() . '_gambar18.' . $image18->getClientOriginalExtension();
      $imagePath18 = $imageName18; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image18->move(public_path('images'), $imageName18); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 19 -->
    $uang_keluar19 = $request->input('uang_keluar19');
    $uang_keluar19 = empty($uang_keluar19) ? null : str_replace(",", "", $uang_keluar19);
    $imagePath19 = $DatasEdit->gambar19;
    if ($request->hasFile('gambar19')) {
      $image19 = $request->file('gambar19');
      $imageName19 = time() . '_gambar19.' . $image19->getClientOriginalExtension();
      $imagePath19 = $imageName19; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image19->move(public_path('images'), $imageName19); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 20 -->
    $uang_keluar20 = $request->input('uang_keluar20');
    $uang_keluar20 = empty($uang_keluar20) ? null : str_replace(",", "", $uang_keluar20);
    $imagePath20 = $DatasEdit->gambar20;
    if ($request->hasFile('gambar20')) {
      $image20 = $request->file('gambar20');
      $imageName20 = time() . '_gambar20.' . $image20->getClientOriginalExtension();
      $imagePath20 = $imageName20; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image20->move(public_path('images'), $imageName20); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- TOTAL UANG MASUK -->
    $total_uang_masuk = $uang_masuk + $uang_masuk11;
    // <!-- END -->

    // <!-- TOAL UANG KELUAR -->
    $total_uang_keluar = $uang_keluar + $uang_keluar2 + $uang_keluar3 + $uang_keluar4 + $uang_keluar5 + $uang_keluar6 + $uang_keluar7 + $uang_keluar8 + $uang_keluar9 + $uang_keluar10 + $uang_keluar11 + $uang_keluar12 + $uang_keluar13 + $uang_keluar14 + $uang_keluar15 + $uang_keluar16 + $uang_keluar17 + $uang_keluar18 + $uang_keluar19 + $uang_keluar20;
    // <!-- END -->

    // <!-- SISA SALDO -->
    $sisa_saldo = $total_uang_masuk - $total_uang_keluar;
    // <!-- END -->

    $DatasEdit->update([
      'user_id'                 => $request->input('user_id'),
      'tempat'                  => $request->input('tempat'),
      'camp'                    => $request->input('camp'),
      'tanggal_mulai'           => $request->input('tanggal_mulai'),
      'tanggal_akhir'           => $request->input('tanggal_akhir'),
      'status'                  => $request->input('status'),

      // <!-- INPUT 1 -->
      'tanggal'                  => $request->input('tanggal'),
      'uang_masuk'               => $uang_masuk,
      'uang_keluar'              => $uang_keluar,
      'keterangan'               => $request->input('keterangan'),
      'gambar'                   => $imagePath,
      // <!-- END -->

      // <!-- INPUT 2 -->
      'uang_keluar2'              => $uang_keluar2,
      'keterangan2'               => $request->input('keterangan2'),
      'gambar2'                   => $imagePath2,
      // <!-- END -->

      // <!-- INPUT 3 -->
      'uang_keluar3'              => $uang_keluar3,
      'keterangan3'               => $request->input('keterangan3'),
      'gambar3'                   => $imagePath3,
      // <!-- END -->

      // <!-- INPUT 4 -->
      'uang_keluar4'              => $uang_keluar4,
      'keterangan4'               => $request->input('keterangan4'),
      'gambar4'                   => $imagePath4,
      // <!-- END -->

      // <!-- INPUT 5 -->
      'uang_keluar5'              => $uang_keluar5,
      'keterangan5'               => $request->input('keterangan5'),
      'gambar5'                   => $imagePath5,
      // <!-- END -->

      // <!-- INPUT 6 -->
      'uang_keluar6'              => $uang_keluar6,
      'keterangan6'               => $request->input('keterangan6'),
      'gambar6'                   => $imagePath6,
      // <!-- END -->

      // <!-- INPUT 7 -->
      'uang_keluar7'              => $uang_keluar7,
      'keterangan7'               => $request->input('keterangan7'),
      'gambar7'                   => $imagePath7,
      // <!-- END -->

      // <!-- INPUT 8 -->
      'uang_keluar8'              => $uang_keluar8,
      'keterangan8'               => $request->input('keterangan8'),
      'gambar8'                   => $imagePath8,
      // <!-- END -->

      // <!-- INPUT 9 -->
      'uang_keluar9'              => $uang_keluar9,
      'keterangan9'               => $request->input('keterangan9'),
      'gambar9'                   => $imagePath9,
      // <!-- END -->

      // <!-- INPUT 10 -->
      'uang_keluar10'              => $uang_keluar10,
      'keterangan10'               => $request->input('keterangan10'),
      'gambar10'                   => $imagePath10,
      // <!-- END -->

      // <!-- INPUT 11 -->
      'tanggal11'                  => $request->input('tanggal11'),
      'uang_masuk11'               => $uang_masuk11,
      'uang_keluar11'              => $uang_keluar11,
      'keterangan11'               => $request->input('keterangan11'),
      'gambar11'                   => $imagePath11,
      // <!-- END -->

      // <!-- INPUT 12 -->
      'uang_keluar12'              => $uang_keluar12,
      'keterangan12'               => $request->input('keterangan12'),
      'gambar12'                   => $imagePath12,
      // <!-- END -->

      // <!-- INPUT 13 -->
      'uang_keluar13'              => $uang_keluar13,
      'keterangan13'               => $request->input('keterangan13'),
      'gambar13'                   => $imagePath13,
      // <!-- END -->

      // <!-- INPUT 14 -->
      'uang_keluar14'              => $uang_keluar14,
      'keterangan14'               => $request->input('keterangan14'),
      'gambar14'                   => $imagePath14,
      // <!-- END -->

      // <!-- INPUT 15 -->
      'uang_keluar15'              => $uang_keluar15,
      'keterangan15'               => $request->input('keterangan15'),
      'gambar15'                   => $imagePath15,
      // <!-- END -->

      // <!-- INPUT 16 -->
      'uang_keluar16'              => $uang_keluar16,
      'keterangan16'               => $request->input('keterangan16'),
      'gambar16'                   => $imagePath16,
      // <!-- END -->

      // <!-- INPUT 17 -->
      'uang_keluar17'              => $uang_keluar17,
      'keterangan17'               => $request->input('keterangan17'),
      'gambar17'                   => $imagePath17,
      // <!-- END -->

      // <!-- INPUT 18 -->
      'uang_keluar18'              => $uang_keluar18,
      'keterangan18'               => $request->input('keterangan18'),
      'gambar18'                   => $imagePath18,
      // <!-- END -->

      // <!-- INPUT 19 -->
      'uang_keluar19'              => $uang_keluar19,
      'keterangan19'               => $request->input('keterangan19'),
      'gambar19'                   => $imagePath19,
      // <!-- END -->

      // <!-- INPUT 20 -->
      'uang_keluar20'              => $uang_keluar20,
      'keterangan20'               => $request->input('keterangan20'),
      'gambar20'                   => $imagePath20,
      // <!-- END -->

      'total_uang_masuk'           => $total_uang_masuk,
      'total_uang_keluar'          => $total_uang_keluar,
      'sisa_saldo'                 => $sisa_saldo,
    ]);

    // Redirect with success or error message
    if ($DatasEdit) {
      if ($request->input('action') === 'save_add') {
        return redirect()->route('account.PerjalananDinas.AddEdit', ['id' => $DatasEdit])->with('next', 'Data Perjalanan Dinas Berhasil Disimpan!');
      } else {
        return redirect()->route('account.PerjalananDinas.index')->with('success', 'Data Perjalanan Dinas Berhasil Disimpan!');
      }
    } else {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Perjalanan Dinas Gagal Disimpan!');
    }
  }

  public function UpdateAddEdit(Request $request, $id)
  {
    $user = Auth::user();
    $DatasEdit = PerjalananDinas::findOrFail($id);
    // <!-- INPUT 21 -->
    $uang_masuk21 = $request->input('uang_masuk21');
    $uang_masuk21 = empty($uang_masuk21) ? null : str_replace(",", "", $uang_masuk21);
    $uang_keluar21 = $request->input('uang_keluar21');
    $uang_keluar21 = empty($uang_keluar21) ? null : str_replace(",", "", $uang_keluar21);
    $imagePath21 = $DatasEdit->gambar21;
    if ($request->hasFile('gambar21')) {
      $image21 = $request->file('gambar21');
      $imageName21 = time() . '_gambar21.' . $image21->getClientOriginalExtension();
      $imagePath21 = $imageName21; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image21->move(public_path('images'), $imageName21); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 22 -->
    $uang_keluar22 = $request->input('uang_keluar22');
    $uang_keluar22 = empty($uang_keluar22) ? null : str_replace(",", "", $uang_keluar22);
    $imagePath22 = $DatasEdit->gambar22;
    if ($request->hasFile('gambar22')) {
      $image22 = $request->file('gambar22');
      $imageName22 = time() . '_gambar22.' . $image22->getClientOriginalExtension();
      $imagePath22 = $imageName22; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image22->move(public_path('images'), $imageName22); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 23 -->
    $uang_keluar23 = $request->input('uang_keluar23');
    $uang_keluar23 = empty($uang_keluar23) ? null : str_replace(",", "", $uang_keluar23);
    $imagePath23 = $DatasEdit->gambar23;
    if ($request->hasFile('gambar23')) {
      $image23 = $request->file('gambar23');
      $imageName23 = time() . '_gambar23.' . $image23->getClientOriginalExtension();
      $imagePath23 = $imageName23; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image23->move(public_path('images'), $imageName23); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 24 -->
    $uang_keluar24 = $request->input('uang_keluar24');
    $uang_keluar24 = empty($uang_keluar24) ? null : str_replace(",", "", $uang_keluar24);
    $imagePath24 = $DatasEdit->gambar24;
    if ($request->hasFile('gambar24')) {
      $image24 = $request->file('gambar24');
      $imageName24 = time() . '_gambar24.' . $image24->getClientOriginalExtension();
      $imagePath24 = $imageName24; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image24->move(public_path('images'), $imageName24); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 25 -->
    $uang_keluar25 = $request->input('uang_keluar25');
    $uang_keluar25 = empty($uang_keluar25) ? null : str_replace(",", "", $uang_keluar25);
    $imagePath25 = $DatasEdit->gambar25;
    if ($request->hasFile('gambar25')) {
      $image25 = $request->file('gambar25');
      $imageName25 = time() . '_gambar25.' . $image25->getClientOriginalExtension();
      $imagePath25 = $imageName25; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image25->move(public_path('images'), $imageName25); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 26 -->
    $uang_keluar26 = $request->input('uang_keluar26');
    $uang_keluar26 = empty($uang_keluar26) ? null : str_replace(",", "", $uang_keluar26);
    $imagePath26 = $DatasEdit->gambar26;
    if ($request->hasFile('gambar26')) {
      $image26 = $request->file('gambar26');
      $imageName26 = time() . '_gambar26.' . $image26->getClientOriginalExtension();
      $imagePath26 = $imageName26; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image26->move(public_path('images'), $imageName26); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 27 -->
    $uang_keluar27 = $request->input('uang_keluar27');
    $uang_keluar27 = empty($uang_keluar27) ? null : str_replace(",", "", $uang_keluar27);
    $imagePath27 = $DatasEdit->gambar27;
    if ($request->hasFile('gambar27')) {
      $image27 = $request->file('gambar27');
      $imageName27 = time() . '_gambar27.' . $image27->getClientOriginalExtension();
      $imagePath27 = $imageName27; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image27->move(public_path('images'), $imageName27); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 28 -->
    $uang_keluar28 = $request->input('uang_keluar28');
    $uang_keluar28 = empty($uang_keluar28) ? null : str_replace(",", "", $uang_keluar28);
    $imagePath28 = $DatasEdit->gambar28;
    if ($request->hasFile('gambar28')) {
      $image28 = $request->file('gambar28');
      $imageName28 = time() . '_gambar28.' . $image28->getClientOriginalExtension();
      $imagePath28 = $imageName28; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image28->move(public_path('images'), $imageName28); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 29 -->
    $uang_keluar29 = $request->input('uang_keluar29');
    $uang_keluar29 = empty($uang_keluar29) ? null : str_replace(",", "", $uang_keluar29);
    $imagePath29 = $DatasEdit->gambar29;
    if ($request->hasFile('gambar29')) {
      $image29 = $request->file('gambar29');
      $imageName29 = time() . '_gambar29.' . $image29->getClientOriginalExtension();
      $imagePath29 = $imageName29; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image29->move(public_path('images'), $imageName29); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 30 -->
    $uang_keluar30 = $request->input('uang_keluar30');
    $uang_keluar30 = empty($uang_keluar30) ? null : str_replace(",", "", $uang_keluar30);
    $imagePath30 = $DatasEdit->gambar30;
    if ($request->hasFile('gambar30')) {
      $image30 = $request->file('gambar30');
      $imageName30 = time() . '_gambar30.' . $image30->getClientOriginalExtension();
      $imagePath30 = $imageName30; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image30->move(public_path('images'), $imageName30); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 31 -->
    $uang_masuk31 = $request->input('uang_masuk31');
    $uang_masuk31 = empty($uang_masuk31) ? null : str_replace(",", "", $uang_masuk31);
    $uang_keluar31 = $request->input('uang_keluar31');
    $uang_keluar31 = empty($uang_keluar31) ? null : str_replace(",", "", $uang_keluar31);
    $imagePath31 = $DatasEdit->gambar31;
    if ($request->hasFile('gambar31')) {
      $image31 = $request->file('gambar31');
      $imageName31 = time() . '_gambar31.' . $image31->getClientOriginalExtension();
      $imagePath31 = $imageName31; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image31->move(public_path('images'), $imageName31); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 32 -->
    $uang_keluar32 = $request->input('uang_keluar32');
    $uang_keluar32 = empty($uang_keluar32) ? null : str_replace(",", "", $uang_keluar32);
    $imagePath32 = $DatasEdit->gambar32;
    if ($request->hasFile('gambar32')) {
      $image32 = $request->file('gambar32');
      $imageName32 = time() . '_gambar32.' . $image32->getClientOriginalExtension();
      $imagePath32 = $imageName32; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image32->move(public_path('images'), $imageName32); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 33 -->
    $uang_keluar33 = $request->input('uang_keluar33');
    $uang_keluar33 = empty($uang_keluar33) ? null : str_replace(",", "", $uang_keluar33);
    $imagePath33 = $DatasEdit->gambar33;
    if ($request->hasFile('gambar33')) {
      $image33 = $request->file('gambar33');
      $imageName33 = time() . '_gambar33.' . $image33->getClientOriginalExtension();
      $imagePath33 = $imageName33; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image33->move(public_path('images'), $imageName33); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 34 -->
    $uang_keluar34 = $request->input('uang_keluar34');
    $uang_keluar34 = empty($uang_keluar34) ? null : str_replace(",", "", $uang_keluar34);
    $imagePath34 = $DatasEdit->gambar34;
    if ($request->hasFile('gambar34')) {
      $image34 = $request->file('gambar34');
      $imageName34 = time() . '_gambar34.' . $image34->getClientOriginalExtension();
      $imagePath34 = $imageName34; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image34->move(public_path('images'), $imageName34); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 35 -->
    $uang_keluar35 = $request->input('uang_keluar35');
    $uang_keluar35 = empty($uang_keluar35) ? null : str_replace(",", "", $uang_keluar35);
    $imagePath35 = $DatasEdit->gambar35;
    if ($request->hasFile('gambar35')) {
      $image35 = $request->file('gambar35');
      $imageName35 = time() . '_gambar35.' . $image35->getClientOriginalExtension();
      $imagePath35 = $imageName35; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image35->move(public_path('images'), $imageName35); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 36 -->
    $uang_masuk36 = $request->input('uang_masuk36');
    $uang_masuk36 = empty($uang_masuk36) ? null : str_replace(",", "", $uang_masuk36);
    $uang_keluar36 = $request->input('uang_keluar36');
    $uang_keluar36 = empty($uang_keluar36) ? null : str_replace(",", "", $uang_keluar36);
    $imagePath36 = $DatasEdit->gambar36;
    if ($request->hasFile('gambar36')) {
      $image36 = $request->file('gambar36');
      $imageName36 = time() . '_gambar36.' . $image36->getClientOriginalExtension();
      $imagePath36 = $imageName36; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image36->move(public_path('images'), $imageName36); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 37 -->
    $uang_keluar37 = $request->input('uang_keluar37');
    $uang_keluar37 = empty($uang_keluar37) ? null : str_replace(",", "", $uang_keluar37);
    $imagePath37 = $DatasEdit->gambar37;
    if ($request->hasFile('gambar37')) {
      $image37 = $request->file('gambar37');
      $imageName37 = time() . '_gambar37.' . $image37->getClientOriginalExtension();
      $imagePath37 = $imageName37; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image37->move(public_path('images'), $imageName37); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 38 -->
    $uang_keluar38 = $request->input('uang_keluar38');
    $uang_keluar38 = empty($uang_keluar38) ? null : str_replace(",", "", $uang_keluar38);
    $imagePath38 = $DatasEdit->gambar38;
    if ($request->hasFile('gambar38')) {
      $image38 = $request->file('gambar38');
      $imageName38 = time() . '_gambar38.' . $image38->getClientOriginalExtension();
      $imagePath38 = $imageName38; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image38->move(public_path('images'), $imageName38); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 39 -->
    $uang_keluar39 = $request->input('uang_keluar39');
    $uang_keluar39 = empty($uang_keluar39) ? null : str_replace(",", "", $uang_keluar39);
    $imagePath39 = $DatasEdit->gambar39;
    if ($request->hasFile('gambar39')) {
      $image39 = $request->file('gambar39');
      $imageName39 = time() . '_gambar39.' . $image39->getClientOriginalExtension();
      $imagePath39 = $imageName39; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image39->move(public_path('images'), $imageName39); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- INPUT 40 -->
    $uang_keluar40 = $request->input('uang_keluar40');
    $uang_keluar40 = empty($uang_keluar40) ? null : str_replace(",", "", $uang_keluar40);
    $imagePath40 = $DatasEdit->gambar40;
    if ($request->hasFile('gambar40')) {
      $image40 = $request->file('gambar40');
      $imageName40 = time() . '_gambar40.' . $image40->getClientOriginalExtension();
      $imagePath40 = $imageName40; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
      $image40->move(public_path('images'), $imageName40); // Pindahkan gambar ke direktori public/images
    }
    // <!-- END -->

    // <!-- TOTAL UANG MASUK -->
    $total_uang_masuk = $uang_masuk21 + $uang_masuk31 + $uang_masuk36;
    // <!-- END -->

    // <!-- TOTAL UANG KELUAR -->
    $total_uang_keluar = $uang_keluar21 + $uang_keluar22 + $uang_keluar23 + $uang_keluar24 + $uang_keluar25 + $uang_keluar26 + $uang_keluar27 + $uang_keluar28 + $uang_keluar29 + $uang_keluar30 + $uang_keluar31 + $uang_keluar32 + $uang_keluar33 + $uang_keluar34 + $uang_keluar35 + $uang_keluar36 + $uang_keluar37 + $uang_keluar38 + $uang_keluar39 + $uang_keluar40;
    // <!-- END -->

    // <!-- SISA SALDO -->
    $sisa_saldo = $total_uang_masuk - $total_uang_keluar;
    // <!-- END -->

    $DatasEdit->update([
      // <!-- INPUT 21 -->
      'tanggal21'                  => $request->input('tanggal21'),
      'uang_masuk21'               => $uang_masuk21,
      'uang_keluar21'              => $uang_keluar21,
      'keterangan21'               => $request->input('keterangan21'),
      'gambar21'                   => $imagePath21,
      // <!-- END -->

      // <!-- INPUT 22 -->
      'uang_keluar22'              => $uang_keluar22,
      'keterangan22'               => $request->input('keterangan22'),
      'gambar22'                   => $imagePath22,
      // <!-- END -->

      // <!-- INPUT 23 -->
      'uang_keluar23'              => $uang_keluar23,
      'keterangan23'               => $request->input('keterangan23'),
      'gambar23'                   => $imagePath23,
      // <!-- END -->

      // <!-- INPUT 24 -->
      'uang_keluar24'              => $uang_keluar24,
      'keterangan24'               => $request->input('keterangan24'),
      'gambar24'                   => $imagePath24,
      // <!-- END -->

      // <!-- INPUT 25 -->
      'uang_keluar25'              => $uang_keluar25,
      'keterangan25'               => $request->input('keterangan25'),
      'gambar25'                   => $imagePath25,
      // <!-- END -->

      // <!-- INPUT 26 -->
      'uang_keluar26'              => $uang_keluar26,
      'keterangan26'               => $request->input('keterangan26'),
      'gambar26'                   => $imagePath26,
      // <!-- END -->

      // <!-- INPUT 27 -->
      'uang_keluar27'              => $uang_keluar27,
      'keterangan27'               => $request->input('keterangan27'),
      'gambar27'                   => $imagePath27,
      // <!-- END -->

      // <!-- INPUT 28 -->
      'uang_keluar28'              => $uang_keluar28,
      'keterangan28'               => $request->input('keterangan28'),
      'gambar28'                   => $imagePath28,
      // <!-- END -->

      // <!-- INPUT 29 -->
      'uang_keluar29'              => $uang_keluar29,
      'keterangan29'               => $request->input('keterangan29'),
      'gambar29'                   => $imagePath29,
      // <!-- END -->

      // <!-- INPUT 30 -->
      'uang_keluar30'              => $uang_keluar30,
      'keterangan30'               => $request->input('keterangan30'),
      'gambar30'                   => $imagePath30,
      // <!-- END -->

      // <!-- INPUT 31 -->
      'tanggal31'                  => $request->input('tanggal31'),
      'uang_masuk31'               => $uang_masuk31,
      'uang_keluar31'              => $uang_keluar31,
      'keterangan31'               => $request->input('keterangan31'),
      'gambar31'                   => $imagePath31,
      // <!-- END -->

      // <!-- INPUT 32 -->
      'uang_keluar32'              => $uang_keluar32,
      'keterangan32'               => $request->input('keterangan32'),
      'gambar32'                   => $imagePath32,
      // <!-- END -->

      // <!-- INPUT 33 -->
      'uang_keluar33'              => $uang_keluar33,
      'keterangan33'               => $request->input('keterangan33'),
      'gambar33'                   => $imagePath33,
      // <!-- END -->

      // <!-- INPUT 34 -->
      'uang_keluar34'              => $uang_keluar34,
      'keterangan34'               => $request->input('keterangan34'),
      'gambar34'                   => $imagePath34,
      // <!-- END -->

      // <!-- INPUT 35 -->
      'uang_keluar35'              => $uang_keluar35,
      'keterangan35'               => $request->input('keterangan35'),
      'gambar35'                   => $imagePath35,
      // <!-- END -->

      // <!-- INPUT 36 -->
      'tanggal36'                  => $request->input('tanggal36'),
      'uang_masuk36'               => $uang_masuk36,
      'uang_keluar36'              => $uang_keluar36,
      'keterangan36'               => $request->input('keterangan36'),
      'gambar36'                   => $imagePath36,
      // <!-- END -->

      // <!-- INPUT 37 -->
      'uang_keluar37'              => $uang_keluar37,
      'keterangan37'               => $request->input('keterangan37'),
      'gambar37'                   => $imagePath37,
      // <!-- END -->

      // <!-- INPUT 38 -->
      'uang_keluar38'              => $uang_keluar38,
      'keterangan38'               => $request->input('keterangan38'),
      'gambar38'                   => $imagePath38,
      // <!-- END -->

      // <!-- INPUT 39 -->
      'uang_keluar39'              => $uang_keluar39,
      'keterangan39'               => $request->input('keterangan39'),
      'gambar39'                   => $imagePath39,
      // <!-- END -->

      // <!-- INPUT 40 -->
      'uang_keluar40'              => $uang_keluar40,
      'keterangan40'               => $request->input('keterangan40'),
      'gambar40'                   => $imagePath40,
      // <!-- END -->

      'total_uang_masuk'           => $request->input('totalmasukpage1') + $total_uang_masuk,
      'total_uang_keluar'          => $request->input('totalkeluarpage1') + $total_uang_keluar,
      'sisa_saldo'                 => $request->input('sisasaldopage1') + $sisa_saldo,
    ]);

    // Redirect with success or error message
    if ($DatasEdit) {
      return redirect()->route('account.PerjalananDinas.index')->with('success', 'Data Perjalanan Dinas Berhasil Disimpan!');
    } else {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Perjalanan Dinas Gagal Disimpan!');
    }
  }

  public function PengajuanManager(Request $request, $id)
  {
    $user = Auth::user();
    $DatasEdit = PerjalananDinas::findOrFail($id);

    $DatasEdit->update([
      'user_id'                 => $request->input('user_id'),
      'deskripsi'               => $request->input('deskripsi'),
      'status'                  => $request->input('status'),
    ]);

    // Redirect with success or error message
    if ($DatasEdit) {
      if ($request->input('action') === 'save_add') {
        return redirect()->route('account.PerjalananDinas.AddEdit', ['id' => $DatasEdit])->with('next', 'Data Perjalanan Dinas Berhasil Disimpan!');
      } else {
        return redirect()->route('account.PerjalananDinas.index')->with('success', 'Data Perjalanan Dinas Berhasil Disimpan!');
      }
    } else {
      return redirect()->route('account.PerjalananDinas.index')->with('error', 'Data Perjalanan Dinas Gagal Disimpan!');
    }
  }

  public function destroy($id)
  {
    try {
      $perjalanan_dinas = PerjalananDinas::find($id);

      if ($perjalanan_dinas) {
        $perjalanan_dinas->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
      } else {
        return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
      }
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
    }
  }
}
