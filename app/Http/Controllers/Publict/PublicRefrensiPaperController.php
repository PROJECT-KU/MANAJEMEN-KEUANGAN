<?php

namespace App\Http\Controllers\Publict;

use App\User;
use App\RefrensiPaper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublicRefrensiPaperController extends Controller
{

    // <!--================== TAMPILAN DATA ==================-->
    public function PublicRefrensiPaper(Request $request)
    {
        // Ambil tanggal dari input atau gunakan default
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Ambil data dengan paginasi
        $datas = DB::table('refrensi_paper')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(16);

        return view('public.refrensi_paper.public', compact('datas', 'startDate', 'endDate'));
    }

    public function Selengkapnya(Request $request, $id)
    {
        $datas = RefrensiPaper::findOrFail($id);
        return view('public.refrensi_paper.selengkapnya', compact('datas'));
    }

    // <!--================== END ==================-->
}
