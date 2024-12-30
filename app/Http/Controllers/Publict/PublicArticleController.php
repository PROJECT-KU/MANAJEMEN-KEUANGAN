<?php

namespace App\Http\Controllers\Publict;

use App\Artikel;
use App\CategoriesArtikel;
use App\ArtikelKomentar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicArticleController extends Controller
{

    function generateRandomToken($length)
    {
        // First character must be a letter
        $firstCharacter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // Remaining characters can include numbers and symbols
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';

        // Ensure the first character is a letter
        $token .= $firstCharacter[rand(0, strlen($firstCharacter) - 1)];

        // Generate the rest of the token
        for ($i = 1; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    // <!--================== PUBLIC ARTIKEL ==================-->

    // HALAMAN AWAL
    public function public(Request $request)
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

        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->orderBy('artikel.created_at', 'DESC')
            ->get();

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token') // Sertakan kolom token di sini
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get();


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('public.article.public', compact('artikel', 'maintenances', 'startDate', 'endDate', 'categories_artikel'));
    }

    // MENAMPILKAN ARTIKEL BERDASARKAN KATEGORI
    public function publickategori(Request $request, $categories_artikel_id, $token)
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

        $articlesQuery = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('categories_artikel.id', $categories_artikel_id)
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC');

        $totalArticles = $articlesQuery->count();

        $articles = $articlesQuery->paginate(9);

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->get();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('public.article.publickategori', compact('articles', 'maintenances', 'startDate', 'endDate', 'categories_artikel', 'totalArticles'));
    }

    // MENAMPILKAN DATA ARTIKEL 
    public function blogsingle($id, $token)
    {
        $user = Auth::user();
        $artikel = Artikel::findOrFail($id);

        $datas = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC')
            ->get();

        $artikel_komentar = DB::table('artikel_komentar')
            ->select('artikel_komentar.id', 'artikel_komentar.categories_artikel_id', 'artikel_komentar.artikel_id', 'artikel_komentar.reply', 'artikel_komentar.token', 'artikel_komentar.nama', 'artikel_komentar.email', 'artikel_komentar.link_website', 'artikel_komentar.komentar', 'artikel_komentar.created_at')
            ->join('artikel', 'artikel_komentar.artikel_id', '=', 'artikel.id')
            ->orderBy('artikel_komentar.created_at', 'DESC')
            ->get();

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->get();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $artikel->dilihat += 1;
        $artikel->save();

        return view('public.article.blogsingle', compact('artikel', 'datas', 'maintenances', 'categories_artikel', 'artikel_komentar'));
    }

    // MENAMBAH DATA KOMENTAR
    public function storekomentar(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateRandomToken(30);

        $save = ArtikelKomentar::create([
            'user_id'                        => $request->input('user_id'),
            'categories_artikel_id'          => $request->input('categories_artikel_id'),
            'artikel_id'                     => $request->input('artikel_id'),
            'reply'                          => $request->input('reply'),
            'token'                          => $token,
            'nama'                           => $request->input('nama'),
            'email'                          => $request->input('email'),
            'link_website'                   => $request->input('link_website'),
            'komentar'                       => $request->input('komentar'),
        ]);


        if ($save) {
            return redirect()->back()->with('success', 'Komentar Anda Berhasil Disimpan!');
        } else {
            return redirect()->back()->with('error', 'Komentar Anda Gagal Disimpan!');
        }
    }

    public function contact()
    {
        return view('public.article.contact');
    }
    // <!--================== END ==================-->

}
