<?php

namespace App\Http\Controllers\account;

use App\Artikel;
use App\CategoriesArtikel;
use App\ArtikelKomentar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArtikelKomentarController extends Controller
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

        $komentar = DB::table('artikel_komentar')
            ->select('artikel_komentar.id', 'artikel_komentar.token', 'artikel_komentar.user_id', 'artikel_komentar.categories_artikel_id', 'artikel_komentar.artikel_id', 'artikel_komentar.reply', 'artikel_komentar.nama', 'artikel_komentar.email', 'artikel_komentar.link_website', 'artikel_komentar.komentar', 'artikel_komentar.created_at', 'artikel_komentar.updated_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar')
            ->leftJoin('users', 'artikel_komentar.user_id', '=', 'users.id')
            ->leftJoin('artikel_id', 'artikel_komentar.artikel_id', '=', 'artikel_id.id')
            ->whereBetween('artikel_komentar.created_at', [$currentMonth, $nextMonth])
            ->orderBy('artikel_komentar.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.index', compact('komentar', 'maintenances', 'startDate', 'endDate'));
    }
}
