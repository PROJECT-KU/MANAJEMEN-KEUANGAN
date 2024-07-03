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

class ArtikelController extends Controller
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

    // <!--================== PUBLIC ==================-->
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

        // Join tabel categories_artikel
        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token') // Sertakan kolom token di sini
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get();


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.public', compact('artikel', 'maintenances', 'startDate', 'endDate', 'categories_artikel'));
    }

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

        // Mengambil artikel berdasarkan kategori
        $articlesQuery = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where('categories_artikel.id', $categories_artikel_id) // Menyaring artikel berdasarkan kategori yang diberikan
            ->where('artikel.status', 'publish') // Hanya memperhitungkan artikel dengan status 'publish'
            ->orderBy('artikel.created_at', 'DESC');

        // Hitung jumlah artikel yang memenuhi kriteria
        $totalArticles = $articlesQuery->count();

        // Pagination
        $articles = $articlesQuery->paginate(9); // Ubah nilai per halaman menjadi 9

        // Mengambil kategori artikel
        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel')) // Sertakan kolom token di sini
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori') // Include all non-aggregated columns from categories_artikel in GROUP BY
            ->get();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.publickategori', compact('articles', 'maintenances', 'startDate', 'endDate', 'categories_artikel', 'totalArticles'));
    }

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

        // Join tabel categories_artikel
        $categories_artikel = DB::table('categories_artikel')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->leftJoin('artikel', 'categories_artikel.id', '=', 'artikel.categories_artikel_id')
            ->select('categories_artikel.id', 'categories_artikel.kategori', 'categories_artikel.token', DB::raw('COUNT(artikel.id) as jumlah_artikel')) // Sertakan kolom token di sini
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->groupBy('categories_artikel.id', 'categories_artikel.user_id', 'categories_artikel.token', 'categories_artikel.kategori') // Include all non-aggregated columns from categories_artikel in GROUP BY
            ->get();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $artikel->dilihat += 1;
        $artikel->save();

        return view('account.artikel.blogsingle', compact('artikel', 'datas', 'maintenances', 'startDate', 'endDate', 'categories_artikel', 'artikel_komentar'));
    }

    public function createkomentar()
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i:s');

        $artikel_komentar = DB::table('artikel_komentar')
            ->select('artikel_komentar.id', 'artikel_komentar.categories_artikel_id', 'artikel_komentar.artikel_id', 'artikel_komentar.reply', 'artikel_komentar.token', 'artikel_komentar.nama', 'artikel_komentar.email', 'artikel_komentar.link_website', 'artikel_komentar.komentar', 'artikel_komentar.created_at')
            ->join('artikel', 'artikel_komentar.artikel_id', '=', 'artikel.id')
            ->orderBy('artikel_komentar.created_at', 'DESC')
            ->get();

        $users = DB::table('users')
            ->select('id', 'full_name', 'gambar')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.create', compact('users', 'currentTime', 'categories_artikel'));
    }

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
        return view('account.artikel.contact');
    }
    // <!--================== END ==================-->

    // <!--================== ADMIN ==================-->
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

        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->orderBy('artikel.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.index', compact('artikel', 'maintenances', 'startDate', 'endDate'));
    }

    public function create()
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i:s');

        $categories_artikel = DB::table('categories_artikel')
            ->select('categories_artikel.id', 'categories_artikel.kategori')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get();

        $users = DB::table('users')
            ->select('id', 'full_name', 'gambar')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.create', compact('users', 'currentTime', 'categories_artikel'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $token = $this->generateRandomToken(30);

        //save image to path
        $imagePath_depan = null;

        if ($request->hasFile('gambar_depan')) {
            $image_depan = $request->file('gambar_depan');
            $imageName_depan = time() . '_GambarDepan.' . $image_depan->getClientOriginalExtension();
            $imagePath_depan = $imageName_depan; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image_depan->move(public_path('images'), $imageName_depan); // Pindahkan gambar ke direktori public/images
        }
        //end

        //save image to path
        $imagePath_cover = null;

        if ($request->hasFile('gambar_cover')) {
            $image_cover = $request->file('gambar_cover');
            $imageName_cover = time() . '_GambarCover.' . $image_cover->getClientOriginalExtension();
            $imagePath_cover = $imageName_cover; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image_cover->move(public_path('images'), $imageName_cover); // Pindahkan gambar ke direktori public/images
        }
        //end


        $save = Artikel::create([
            'user_id'                        => $request->input('user_id'),
            'categories_artikel_id'          => $request->input('categories_artikel_id'),
            'token'                          => $token,
            'judul'                          => $request->input('judul'),
            'kata_kunci'                     => $request->input('kata_kunci_tags'),
            'judul'                          => $request->input('judul'),
            'gambar_depan'                   => $imagePath_depan, // Store the image path
            'gambar_cover'                   => $imagePath_cover, // Store the image path
            'isi'                            => $request->input('isi'),
            'status'                         => $request->input('status'),
        ]);


        if ($save) {
            return redirect()->route('account.Artikel.index')->with('success', 'Data Artikel Berhasil Disimpan!');
        } else {
            return redirect()->route('account.Artikel.index')->with('error', 'Data Artikel Gagal Disimpan!');
        }
    }

    public function edit($id, $token)
    {
        $user = Auth::user();
        $artikel = Artikel::findOrFail($id);


        $categories_artikel = DB::table('categories_artikel')
            ->select('categories_artikel.id', 'categories_artikel.kategori')
            ->join('users', 'categories_artikel.user_id', '=', 'users.id')
            ->orderBy('categories_artikel.created_at', 'DESC')
            ->get();

        $users = DB::table('users')
            ->select('id', 'full_name', 'gambar')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.edit', compact('users', 'artikel', 'categories_artikel')); // Sesuaikan path template dengan benar
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $artikel = Artikel::findOrFail($id);

        // Save image to path
        $imagePath_depan = $artikel->gambar_depan;
        if ($request->hasFile('gambar_depan')) {
            $image_depan = $request->file('gambar_depan');
            $imageName_depan = time() . '_GambarDepan.' . $image_depan->getClientOriginalExtension();
            $imagePath_depan = $imageName_depan; // Store the new image path
            $image_depan->move(public_path('images'), $imageName_depan); // Move the new image to the public/images directory
        }

        // Save image to path
        $imagePath_cover = $artikel->gambar_cover;
        if ($request->hasFile('gambar_cover')) {
            $image_cover = $request->file('gambar_cover');
            $imageName_cover = time() . '_GambarCover.' . $image_cover->getClientOriginalExtension();
            $imagePath_cover = $imageName_cover; // Store the new image path
            $image_cover->move(public_path('images'), $imageName_cover); // Move the new image to the public/images directory
        }

        $artikel->update([
            'user_id'                        => $request->input('user_id'),
            'categories_artikel_id'          => $request->input('categories_artikel_id'),
            'judul'                          => $request->input('judul'),
            'kata_kunci'                     => $request->input('kata_kunci_tags'),
            'judul'                          => $request->input('judul'),
            'gambar_depan'                   => $imagePath_depan, // Store the image path
            'gambar_cover'                   => $imagePath_cover, // Store the image path
            'isi'                            => $request->input('isi'),
            'status'                         => $request->input('status'),
        ]);

        if ($artikel) {
            return redirect()->route('account.Artikel.index')->with('success', 'Data Artikel Berhasil Disimpan!');
        } else {
            return redirect()->route('account.Artikel.index')->with('error', 'Data Artikel Gagal Disimpan!');
        }
    }

    // <!--================== HANDLE CKEDITOR ==================-->
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {

            $file = $request->file('upload');
            $name = $file->getClientOriginalName();
            $name = Str::slug($name);
            $img  = Image::make($file);
            $img->stream();
            $name = str_replace('png', '', $name) . '.png';

            Storage::disk('images')->put('posts/' . $name, $img);

            return response()->json([
                'url' => "/images/posts/$name"
            ]);
        }
    }
    // <!--================== END ==================-->

    public function destroy($id)
    {
        try {
            $artikel = Artikel::find($id);

            if ($artikel) {
                $artikel->delete();
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

        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->where(function ($query) use ($search) {
                $query->where('artikel.judul', 'LIKE', '%' . $search . '%')
                    ->orWhere('categories_artikel.kategori', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.full_name', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('artikel.created_at', 'DESC')
            ->paginate(10);

        $artikel->appends(['q' => $search, 'start_date' => $startDate, 'end_date' => $endDate]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($artikel->isEmpty()) {
            return redirect()->route('account.Artikel.index')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }
        return view('account.artikel.index', compact('artikel', 'maintenances', 'startDate', 'endDate'));
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

        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->whereBetween('artikel.created_at', [$currentMonth, $nextMonth])
            ->orderBy('artikel.created_at', 'DESC')
            ->paginate(10);


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.artikel.index', compact('artikel', 'maintenances', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->
}
