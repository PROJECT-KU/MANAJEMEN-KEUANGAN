<?php

namespace App\Http\Controllers\account;

use App\Credit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use Dompdf\Dompdf;

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
    public function index()
    {
        return view('account.laporan_credit.index');
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
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->whereDate('credit.credit_date', '>=', $tanggal_awal)
                ->whereDate('credit.credit_date', '<=', $tanggal_akhir)
                ->where('users.company', $user->company)
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

        return view('account.laporan_credit.index', compact('credit', 'tanggal_awal', 'tanggal_akhir'));
    }

    public function downloadPdf(Request $request)
    {
        // Fetch data based on the given date range
        $tanggal_awal  = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        $credit = Credit::select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        ->whereDate('credit.credit_date', '>=', $tanggal_awal)
            ->whereDate('credit.credit_date', '<=', $tanggal_akhir)
            ->get();

        // Get the Blade view content as HTML
        $html = view('account.laporan_credit.pdf', compact('credit', 'tanggal_awal', 'tanggal_akhir'))->render();

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
