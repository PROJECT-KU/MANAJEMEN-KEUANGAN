<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\Debit;
use App\Gaji;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Dompdf\Dompdf;
use App\User;

class LaporanSemuaController extends Controller
{
  /**
   * DebitController constructor.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
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

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.id_transaksi', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->leftJoin('users', 'debit.user_id', '=', 'users.id')
        ->where(function ($query) use ($user) {
          $query->where('users.company', $user->company)
            ->orWhere('debit.user_id', $user->id);
        })
        ->where(function ($query) {
          $query->where('users.level', 'manager')
            ->orWhere('users.level', 'staff');
        })
        ->whereBetween('debit.debit_date', [$currentMonth, $nextMonth])
        ->orderBy('debit.created_at', 'DESC')
        ->get();

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.id_transaksi', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
        ->leftJoin('users', 'credit.user_id', '=', 'users.id')
        ->where(function ($query) use ($user) {
          $query->where('users.company', $user->company)
            ->orWhere('credit.user_id', $user->id);
        })
        ->where(function ($query) {
          $query->where('users.level', 'manager')
            ->orWhere('users.level', 'staff');
        })
        ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
        ->orderBy('credit.created_at', 'DESC')
        ->get();

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    } else {
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.id_transaksi', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
        ->where('debit.user_id', Auth::user()->id)
        ->whereBetween('debit.debit_date', [$currentMonth, $nextMonth])
        ->orderBy('debit.created_at', 'DESC')
        ->get();

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.id_transaksi', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        ->where('credit.user_id', Auth::user()->id)
        ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
        ->orderBy('credit.created_at', 'DESC')
        ->get();

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', Auth::user()->id)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    }

    // Mengubah format tanggal menjadi "dd-mm-yyyy h:i" untuk debit
    foreach ($debit as $item) {
      $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
    }

    // Mengubah format tanggal menjadi "dd-mm-yyyy h:i" untuk kredit
    foreach ($credit as $item) {
      $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
    }

    // Calculate total debit
    $totalDebit = $debit->sum('nominal');

    // Calculate total credit
    $totalCredit = $credit->sum('nominal');

    // Calculate total gaji
    $totalGaji = $gaji->sum('total');

    return view('account.laporan_semua.index', compact('debit', 'credit', 'gaji', 'totalDebit', 'totalCredit', 'totalGaji', 'startDate', 'endDate'));
  }

  public function downloadPdf(Request $request)
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

    if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.id_transaksi', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->leftJoin('users', 'debit.user_id', '=', 'users.id')
        ->where(function ($query) use ($user) {
          $query->where('users.company', $user->company)
            ->orWhere('debit.user_id', $user->id);
        })
        ->where(function ($query) {
          $query->where('users.level', 'manager')
            ->orWhere('users.level', 'staff');
        })
        ->whereBetween('debit.debit_date', [$currentMonth, $nextMonth])
        ->orderBy('debit.created_at', 'DESC')
        ->get();

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.id_transaksi', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
        ->leftJoin('users', 'credit.user_id', '=', 'users.id')
        ->where(function ($query) use ($user) {
          $query->where('users.company', $user->company)
            ->orWhere('credit.user_id', $user->id);
        })
        ->where(function ($query) {
          $query->where('users.level', 'manager')
            ->orWhere('users.level', 'staff');
        })
        ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
        ->orderBy('credit.created_at', 'DESC')
        ->get();

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    } else {
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.id_transaksi', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
        ->where('debit.user_id', Auth::user()->id)
        ->whereBetween('debit.debit_date', [$currentMonth, $nextMonth])
        ->orderBy('debit.created_at', 'DESC')
        ->get();

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.id_transaksi', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        ->where('credit.user_id', Auth::user()->id)
        ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
        ->orderBy('credit.created_at', 'DESC')
        ->get();

      $gaji = DB::table('gaji')
        ->select('gaji.id', 'gaji.id_transaksi', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
        ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
        ->where('gaji.user_id', Auth::user()->id)
        ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
        ->orderBy('gaji.created_at', 'DESC')
        ->get();
    }

    // Calculate total debit
    $totalDebit = $debit->sum('nominal');

    // Calculate total credit
    $totalCredit = $credit->sum('nominal');

    // Calculate total gaji
    $totalGaji = $gaji->sum('total');

    $users = User::all(); // Get all users

    // Get the HTML content of the view
    $html = view('account.laporan_semua.pdf', compact('debit', 'credit', 'user', 'gaji', 'totalDebit', 'totalCredit', 'totalGaji', 'startDate', 'endDate'))->render();

    // Instantiate Dompdf with the default configuration
    $dompdf = new Dompdf();

    // Load the HTML content into Dompdf
    $dompdf->loadHtml($html);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the PDF
    $dompdf->render();

    // Set the PDF filename
    $fileName = 'laporan_transaksi_semua_' . date('d-m-Y') . '.pdf';

    // Output the generated PDF to the browser
    return $dompdf->stream($fileName);
  }
}
