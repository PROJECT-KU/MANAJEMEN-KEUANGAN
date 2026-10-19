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
    /**
     * Daftar tulisan di /blog.
     *
     * Dua hal dibetulkan 19 Okt 2026.
     *
     * PERTAMA, saringan status. Kueri ini dulu mengambil SELURUH baris
     * artikel apa pun statusnya; yang menyaring 'publish' cuma tampilannya,
     * di dalam @php per kategori. Artinya draf yang belum selesai ikut
     * terkirim ke peramban — tersembunyi dari mata, tetapi ada utuh di
     * sumber halaman, termasuk isinya. Siapa pun yang menekan "lihat sumber"
     * membacanya. Sekarang disaring di kueri, jadi yang belum terbit tidak
     * pernah meninggalkan peladen.
     *
     * KEDUA, dua tulisan hilang. Tampilannya mengelompokkan per kategori lalu
     * take(3) tiap kelompok — kategori Journal punya lima, jadi dua di
     * antaranya tidak pernah muncul di /blog sama sekali. Halaman barunya
     * memajang semuanya.
     *
     * Perhitungan $currentMonth/$nextMonth yang lama dibuang: keduanya
     * dihitung lalu tidak pernah dipakai di kueri mana pun.
     */
    public function public(Request $request)
    {
        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC')
            ->get();

        /*
         * Kategori yang KOSONG tidak ditawarkan.
         *
         * Keripik kategori di halaman barunya mengantar orang ke halaman
         * kategori; yang tidak punya tulisan terbit mengantarnya ke halaman
         * kosong, dan halaman kosong terbaca sebagai situs yang rusak.
         */
        $jumlahPerKategori = $artikel->groupBy('categories_artikel_id')->map->count();

        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token')
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get()
            ->filter(fn ($k) => ($jumlahPerKategori[$k->id] ?? 0) > 0)
            ->values();

        return view('public.article.public', compact('artikel', 'categories_artikel', 'jumlahPerKategori'));
    }

    // MENAMPILKAN ARTIKEL BERDASARKAN KATEGORI
    public function publickategori(Request $request, $categories_artikel_id)
    {
        $articlesQuery = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('categories_artikel.id', $categories_artikel_id)
            ->where('artikel.status', 'publish')
            ->orderBy('artikel.created_at', 'DESC');

        $totalArticles = $articlesQuery->count();

        $articles = $articlesQuery->paginate(9);

        /*
         * Hitungannya CUMA yang terbit.
         *
         * COUNT(artikel.id) tanpa syarat status membuat keripik di sini
         * menyebut angka yang berbeda dari keripik yang sama di /blog —
         * "Journal 7" di satu halaman, "Journal 5" di halaman berikutnya,
         * untuk daftar yang sama persis. Yang melihatnya menyimpulkan ada
         * tulisan yang hilang.
         *
         * Kategori yang kosong ikut disaring, dengan alasan yang sama
         * seperti di /blog: tidak ada keripik yang mengantar ke halaman
         * tanpa isi.
         */
        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', function ($gabung) {
                $gabung->on('categories_artikel.id', '=', 'artikel.categories_artikel_id')
                    ->where('artikel.status', '=', 'publish');
            })
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel'))
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori')
            ->having('jumlah_artikel', '>', 0)
            ->get();

        // Kategori yang sedang dibuka: untuk judul halaman, <title>, dan
        // penanda keripik mana yang sedang aktif.
        $kategoriIni = $categories_artikel->firstWhere('id', $categories_artikel_id)
            ?? DB::table('categories_artikel')->where('id', $categories_artikel_id)->first();

        return view('public.article.publickategori', compact('articles', 'categories_artikel', 'totalArticles', 'kategoriIni'));
    }

    // MENAMPILKAN DATA ARTIKEL 
    public function blogsingle($id)
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

        $artikel->dilihat += 1;
        $artikel->save();

        return view('public.article.blogsingle', compact('artikel', 'datas', 'categories_artikel', 'artikel_komentar'));
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
