<?php

namespace App\Http\Controllers\account;

use App\PendaftaranScopusKafe;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PendaftaranScopusKafeController extends Controller
{
    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        // Tentukan rentang tanggal
        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 23:59:59', strtotime($endDate)); // Akhiri pada akhir hari
        }

        $datas = DB::table('pendaftaran_scopus_kafe')
            ->whereBetween('pendaftaran_scopus_kafe.tanggal_pemesanan', [$currentMonth, $nextMonth])
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);

        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== FILTER & SEARCH ==================-->
    public function filter(Request $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        // Tentukan rentang tanggal
        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 23:59:59', strtotime($endDate)); // Akhiri pada akhir hari
        }

        // Ambil data meme untuk paginasi
        $datas = DB::table('pendaftaran_scopus_kafe')
            ->whereBetween('pendaftaran_scopus_kafe.tanggal_pemesanan', [$currentMonth, $nextMonth])
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);

        // Kembalikan view dengan semua data yang diperlukan
        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');

        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
        }

        $datas = DB::table('pendaftaran_scopus_kafe')
            ->where(function ($query) use ($search) {
                $query->orWhere('pendaftaran_scopus_kafe.nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.id_pemesanan', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.total_keseluruhan_pembayaran', 'LIKE', '%' . $search . '%')
                    ->orWhere('pendaftaran_scopus_kafe.status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('pendaftaran_scopus_kafe.created_at', 'DESC')
            ->paginate(10);
        $datas->appends(['q' => $search]);

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($datas->isEmpty()) {
            return redirect()->route('account.pendaftaran_scopus_kafe.index')->with('error', 'Data Pendaftaran tidak ditemukan.');
        }
        return view('account.pendaftaran_scopus_kafe.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->
}
