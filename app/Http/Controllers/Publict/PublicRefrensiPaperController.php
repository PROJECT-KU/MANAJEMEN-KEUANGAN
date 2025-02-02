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

    // <!--================== FILTER & SEARCH ==================-->
    public function searchpublic(Request $request)
    {
        // Ambil input pencarian dan tanggal
        $search = $request->get('q');
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Query data menggunakan Eloquent dengan filter
        $datas = RefrensiPaper::query()
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('nama_author', 'LIKE', "%$search%")
                        ->orWhere('nama_journal', 'LIKE', "%$search%")
                        ->orWhere('quartile_journal', 'LIKE', "%$search%")
                        ->orWhere('subjek_area_journal', 'LIKE', "%$search%")
                        ->orWhere('judul_paper', 'LIKE', "%$search%")
                        ->orWhere('type', 'LIKE', "%$search%");
                }
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(16);

        // Tambahkan parameter pencarian ke paginasi
        $datas->appends($request->only(['q', 'tanggal_awal', 'tanggal_akhir']));

        // Jika data tidak ditemukan
        if ($datas->isEmpty()) {
            return redirect()->route('public.refrensi-paper.PublicRefrensiPaper')
                ->with('error', 'Data Refrensi Paper Tidak Ditemukan.');
        }

        // Kembalikan ke view
        return view('public.refrensi_paper.public', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->
}
