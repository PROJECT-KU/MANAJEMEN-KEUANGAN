<?php

namespace App\Http\Controllers\account;

use App\CategoriesArtikel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriesArtikelController extends Controller
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

        // Menggabungkan tabel 'categories_artikel' dengan tabel 'artikel' untuk menghitung jumlah artikel per kategori
        $categories_artikel = DB::table('categories_artikel')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori') // Include all non-aggregated columns from categories_artikel in GROUP BY
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.kategori_artikel.index', compact('categories_artikel', 'maintenances', 'startDate', 'endDate'));
    }



    public function create()
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i:s');

        return view('account.kategori_artikel.create', compact('users', 'currentTime'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateRandomToken(30);

        $save = CategoriesArtikel::create([
            'user_id'           => Auth::user()->id,
            'token'             => $token,
            'kategori'          => $request->input('kategori'),
        ]);


        if ($save) {
            return redirect()->route('account.Kategori-Artikel.index')->with('success', 'Data Kategori Artikel Berhasil Disimpan!');
        } else {
            return redirect()->route('account.Kategori-Artikel.index')->with('error', 'Data Kategori Artikel Gagal Disimpan!');
        }
    }

    public function edit($id, $token)
    {
        $user = Auth::user();
        $categories_artikel = CategoriesArtikel::findOrFail($id);

        return view('account.kategori_artikel.edit', compact('categories_artikel')); // Sesuaikan path template dengan benar
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $categories_artikel = CategoriesArtikel::findOrFail($id);

        $categories_artikel->update([
            'kategori'          => $request->input('kategori'),
        ]);

        if ($categories_artikel) {
            return redirect()->route('account.Kategori-Artikel.index')->with('success', 'Data Kategori Artikel Berhasil Disimpan!');
        } else {
            return redirect()->route('account.Kategori-Artikel.index')->with('error', 'Data Kategori Artikel Gagal Disimpan!');
        }
    }
    public function destroy($id)
    {
        try {
            $categories_artikel = CategoriesArtikel::find($id);

            if ($categories_artikel) {
                $categories_artikel->delete();
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

        $categories_artikel = DB::table('categories_artikel')
            ->select('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->where(function ($query) use ($search) {
                $query->where('categories_artikel.kategori', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->paginate(10);
        $categories_artikel->appends(['q' => $search]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($categories_artikel->isEmpty()) {
            return redirect()->route('account.Kategori-Artikel.index')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }
        return view('account.kategori_artikel.index', compact('categories_artikel', 'maintenances', 'startDate', 'endDate'));
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

        $categories_artikel = DB::table('categories_artikel')
            ->select('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->whereBetween('categories_artikel.created_at', [$currentMonth, $nextMonth])
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.kategori_artikel.index', compact('categories_artikel', 'maintenances', 'startDate', 'endDate'));
    }
}
