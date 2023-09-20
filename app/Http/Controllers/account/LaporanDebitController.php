<?php

namespace App\Http\Controllers\account;

use App\Debit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use Dompdf\Dompdf;
use App\User;

class LaporanDebitController extends Controller
{
    /**
     * LaporanDebitController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('account.laporan_debit.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function check(Request $request)
    {
        $user = Auth::user();
        //set validasi required
        $this->validate(
            $request,
            [
                'tanggal_awal'     => 'required',
                'tanggal_akhir'    => 'required',
            ],
            //set message validation
            [
                'tanggal_awal.required'  => 'Silahkan Pilih Tanggal Awal!',
                'tanggal_akhir.required' => 'Silahkan Pilih Tanggal Akhir!',
            ]
        );

        $tanggal_awal  = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        if ($user->level == 'manager' || $user->level == 'staff') {
            $debit = Debit::select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
                ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->whereDate('debit.debit_date', '>=', $tanggal_awal)
                ->whereDate('debit.debit_date', '<=', $tanggal_akhir)
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->paginate(10)
                ->appends(request()->except('page'));
        } else {
            $debit = Debit::select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
                ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
                ->whereDate('debit.debit_date', '>=', $tanggal_awal)
                ->whereDate('debit.debit_date', '<=', $tanggal_akhir)
                ->where('debit.user_id', $user->id)
                ->paginate(10)
                ->appends(request()->except('page'));
        }

        $totalDebit = $debit->sum('nominal');
        return view('account.laporan_debit.index', compact('debit', 'tanggal_awal', 'tanggal_akhir', 'totalDebit'));
    }
    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        // Fetch data based on the given date range
        $tanggal_awal  = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        if ($user->level == 'manager' || $user->level == 'staff') {
            $debit = Debit::select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
                ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->whereDate('debit.debit_date', '>=', $tanggal_awal)
                ->whereDate('debit.debit_date', '<=', $tanggal_akhir)
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->paginate(10)
                ->appends(request()->except('page'));
        } else {
            $debit = Debit::select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
                ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
                ->whereDate('debit.debit_date', '>=', $tanggal_awal)
                ->whereDate('debit.debit_date', '<=', $tanggal_akhir)
                ->where('debit.user_id', $user->id)
                ->paginate(10)
                ->appends(request()->except('page'));
        }

        $totalDebit = $debit->sum('nominal');
        $users = User::all(); // Get all users
        // Get the Blade view content as HTML
        $html = view('account.laporan_debit.pdf', compact('debit', 'tanggal_awal', 'tanggal_akhir', 'user', 'totalDebit'))->render();

        // Generate PDF using the HTML content
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Set the PDF filename
        $fileName = 'laporan_debit_' . date('d-m-Y') . '.pdf';

        // Output the generated PDF to the browser
        return $dompdf->stream($fileName);
    }
}
