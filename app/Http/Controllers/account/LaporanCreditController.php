<?php

namespace App\Http\Controllers\account;

use App\Credit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Support\Carbon;

class LaporanCreditController extends Controller
{
    /**
     * LaporanCreditController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->paginate(10)
                ->appends(request()->except('page'));
        } else {
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
                ->where('credit.user_id', $user->id)
                ->paginate(10)
                ->appends(request()->except('page'));
        }

        $totalCredit = $credit->sum('nominal');
        return view('account.laporan_credit.index', compact('credit', 'startDate', 'endDate', 'totalCredit'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function check(Request $request)
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
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->paginate(10)
                ->appends(request()->except('page'));
        } else {
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->whereBetween('credit.credit_date', [$currentMonth, $nextMonth])
                ->where('credit.user_id', $user->id)
                ->paginate(10)
                ->appends(request()->except('page'));
        }

        $totalCredit = $credit->sum('nominal');
        return view('account.laporan_credit.index', compact('credit', 'startDate', 'endDate', 'totalCredit'));
    }

    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        // Fetch data based on the given date range
        $tanggal_awal  = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->whereDate('credit.credit_date', '>=', $tanggal_awal)
                ->whereDate('credit.credit_date', '<=', $tanggal_akhir)
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->paginate(10)
                ->appends(request()->except('page'));
        } else {
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->whereDate('credit.credit_date', '>=', $tanggal_awal)
                ->whereDate('credit.credit_date', '<=', $tanggal_akhir)
                ->where('credit.user_id', $user->id)
                ->paginate(10)
                ->appends(request()->except('page'));
        }

        $totalCredit = $credit->sum('nominal');
        $users = User::all(); // Get all users
        // Get the Blade view content as HTML
        $html = view('account.laporan_credit.pdf', compact('credit', 'tanggal_awal', 'tanggal_akhir', 'user', 'totalCredit'))->render();

        // Generate PDF using the HTML content
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Set the PDF filename
        $fileName = 'laporan_credit_' . date('d-m-Y') . '.pdf';

        // Output the generated PDF to the browser
        return $dompdf->stream($fileName);
    }
}
