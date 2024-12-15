<?php

namespace App\Http\Controllers\Publict;

use App\User;
use App\Paperisasi;
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

class PublicPaperisasiController extends Controller
{
    // <!--================== TAMPILAN DATA ==================-->
    public function public(Request $request)
    {
        // Ambil tanggal dari input atau gunakan default
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Ambil data dengan paginasi
        $datas = Paperisasi::whereBetween('tanggal_masuk_paper', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('public.paperisasi.public', compact('datas', 'startDate', 'endDate'));
    }

    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function publicsearch(Request $request)
    {
        // Ambil input 'q' dari form pencarian
        $searchQuery = $request->input('q');

        // Jika ada input pencarian, filter data berdasarkan id_paper
        if ($searchQuery) {
            $datas = Paperisasi::where('id_paper', $searchQuery)->get();
        } else {
            // Jika tidak ada input, kosongkan data
            $datas = collect(); // Koleksi kosong
        }

        // Kembalikan data ke view
        return view('public.paperisasi.public', compact('datas', 'searchQuery'));
    }
    // <!--================== END ==================-->
}
