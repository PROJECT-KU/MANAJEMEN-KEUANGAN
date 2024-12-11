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
