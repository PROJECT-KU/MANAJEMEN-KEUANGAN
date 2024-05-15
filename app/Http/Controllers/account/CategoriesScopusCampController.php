<?php

namespace App\Http\Controllers\account;

use App\CategoriesScopusCamp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriesScopusCampController extends Controller
{

    function generateRandomToken($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

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

        $categories_scopuscamp = DB::table('categories_scopuscamp')
            ->select('categories_scopuscamp.id', 'categories_scopuscamp.token', 'categories_scopuscamp.camp', 'categories_scopuscamp.tempat', 'categories_scopuscamp.mulai', 'categories_scopuscamp.selesai', 'categories_scopuscamp.kuota', 'categories_scopuscamp.status')
            ->orderBy('categories_scopuscamp.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.kategori_scopuscamp.index', compact('categories_scopuscamp', 'maintenances', 'startDate', 'endDate'));
    }

    public function create()
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i:s');

        return view('account.kategori_scopuscamp.create', compact('users', 'currentTime'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateRandomToken(30);

        $save = CategoriesScopusCamp::create([
            'token'             => $token,
            'camp'              => $request->input('camp'),
            'tempat'            => $request->input('tempat'),
            'mulai'             => $request->input('mulai'),
            'selesai'           => $request->input('selesai'),
            'kuota'             => $request->input('kuota'),
        ]);


        if ($save) {
            return redirect()->route('account.kategori.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('account.kategori.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
        }
    }

    public function edit($id, $token)
    {
        $user = Auth::user();
        $categories_scopuscamp = CategoriesScopusCamp::findOrFail($id);

        return view('account.kategori_scopuscamp.edit', compact('categories_scopuscamp')); // Sesuaikan path template dengan benar
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $categories_scopuscamp = CategoriesScopusCamp::findOrFail($id);

        $categories_scopuscamp->update([
            'camp'              => $request->input('camp'),
            'tempat'            => $request->input('tempat'),
            'mulai'             => $request->input('mulai'),
            'selesai'           => $request->input('selesai'),
            'kuota'             => $request->input('kuota'),
            'status'            => $request->input('status'),
        ]);

        if ($categories_scopuscamp) {
            return redirect()->route('account.kategori.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
        } else {
            return redirect()->route('account.kategori.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
        }
    }

    public function destroy($id)
    {
        try {
            $categories_scopuscamp = CategoriesScopusCamp::find($id);

            if ($categories_scopuscamp) {
                $categories_scopuscamp->delete();
                return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
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

        $categories_scopuscamp = DB::table('categories_scopuscamp')
            ->select('categories_scopuscamp.id', 'categories_scopuscamp.token',  'categories_scopuscamp.camp', 'categories_scopuscamp.tempat', 'categories_scopuscamp.mulai', 'categories_scopuscamp.selesai', 'categories_scopuscamp.kuota', 'categories_scopuscamp.status')
            ->where(function ($query) use ($search) {
                $query->where('categories_scopuscamp.camp', 'LIKE', '%' . $search . '%')
                    ->orWhere('categories_scopuscamp.tempat', 'LIKE', '%' . $search . '%')
                    ->orWhere('categories_scopuscamp.status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('categories_scopuscamp.created_at', 'DESC')
            ->paginate(10);
        $categories_scopuscamp->appends(['q' => $search]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($categories_scopuscamp->isEmpty()) {
            return redirect()->route('account.kategori.index')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }
        return view('account.kategori_scopuscamp.index', compact('categories_scopuscamp', 'maintenances', 'startDate', 'endDate'));
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

        $categories_scopuscamp = DB::table('categories_scopuscamp')
            ->select('categories_scopuscamp.id', 'categories_scopuscamp.token',  'categories_scopuscamp.camp', 'categories_scopuscamp.tempat', 'categories_scopuscamp.mulai', 'categories_scopuscamp.selesai', 'categories_scopuscamp.kuota', 'categories_scopuscamp.status')
            ->whereBetween('categories_scopuscamp.mulai', [$currentMonth, $nextMonth])
            ->orderBy('categories_scopuscamp.mulai', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.kategori_scopuscamp.index', compact('categories_scopuscamp', 'maintenances', 'startDate', 'endDate'));
    }
}
