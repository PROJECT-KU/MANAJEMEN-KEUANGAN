<?php

namespace App\Http\Controllers\account;

use App\ScopusCamp;
use App\CategoriesScopusCamp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScopusCampMail;
use App\Mail\ScopusCampUpdateDiterimaMail;
use App\Mail\ScopusCampUpdateDitolakMail;

class ScopusCampController extends Controller
{
    /**
     * PenyewaanController constructor.
     */
    function generateRandomToken($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
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

        $scopuscamp = DB::table('scopuscamp')
            ->select('scopuscamp.id', 'scopuscamp.token', 'scopuscamp.id_transaksi', 'scopuscamp.email', 'scopuscamp.nama', 'scopuscamp.judul', 'scopuscamp.telp', 'scopuscamp.afiliasi', 'scopuscamp.pembayaran', 'scopuscamp.gambar', 'scopuscamp.status', 'scopuscamp.camp', 'scopuscamp.mulai', 'scopuscamp.selesai', 'scopuscamp.tempat', 'scopuscamp.created_at')
            ->orderBy('scopuscamp.created_at', 'DESC')
            ->paginate(10);


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.scopuscamp.index', compact('scopuscamp', 'maintenances', 'startDate', 'endDate'));
    }

    public function edit($id, $token)
    {
        $user = Auth::user();
        $scopuscamp = ScopusCamp::findOrFail($id);

        return view('account.scopuscamp.edit', compact('scopuscamp')); // Sesuaikan path template dengan benar
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $scopuscamp = ScopusCamp::findOrFail($id);

        //save image to path
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName;
            $image->move(public_path('scopuscamp'), $imageName); // Store the image
        } else {
            // If no new image uploaded, keep using the old image path
            $imagePath = $scopuscamp->gambar;
        }
        //end

        $scopuscamp->update([
            'email'                     => $request->input('email'),
            'nama'                      => $request->input('nama'),
            'judul'                     => $request->input('judul'),
            'telp'                      => $request->input('telp'),
            'afiliasi'                  => $request->input('afiliasi'),
            'pembayaran'                => $request->input('pembayaran'),
            'categories_scopuscamp_id'  => $request->input('categories_scopuscamp_id'),
            'camp'                     => $request->input('camp'),
            'mulai'                     => $request->input('mulai'),
            'selesai'                   => $request->input('selesai'),
            'tempat'                    => $request->input('tempat'),
            'status'                    => $request->input('status'),
            'note' => !empty($request->input('note')) ? strip_tags($request->input('note')) : null,
            'gambar'                    => $imagePath,
        ]);

        if ($scopuscamp) {
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');
            if ($scopuscamp->status == 'Pendaftaran Diterima') {
                Mail::to($emailTo)->send(new ScopusCampUpdateDiterimaMail($scopuscamp, $appName));
            } else {
                Mail::to($emailTo)->send(new ScopusCampUpdateDitolakMail($scopuscamp, $appName));
            }
            return redirect()->route('account.scopuscamp.index')->with('success', 'Data Presensi Karyawan Berhasil Disimpan!');
        } else {
            return redirect()->route('account.scopuscamp.index')->with('error', 'Data Presensi Karyawan Gagal Disimpan!');
        }
    }

    public function form(Request $request)
    {
        // Fetch categories from the database
        // $categories_scopuscamp = CategoriesScopusCamp::where('status', 'AKTIF')->get(['categories_scopuscamp.*']);

        // Pass the categories to the view
        // return view('account.scopuscamp.form', ['categories_scopuscamp' => $categories_scopuscamp]);
        return view('account.scopuscamp.form');
    }

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $id_transaksi = $this->generateRandomId(5);

        // menyimpan gambar di path
        $imagePath = null;
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image->move(public_path('scopuscamp'), $imageName); // Pindahkan gambar ke direktori public/images
        }
        //end

        $status = $request->input('status') ?? 'Dalam Proses Pengecekan';

        // Membuat entri baru untuk model ScopusCamp dengan data yang diberikan
        $save = ScopusCamp::create([
            'token'                     => $token,
            'id_transaksi'              => $id_transaksi,
            'email'                     => $request->input('email'),
            'nama'                      => $request->input('nama'),
            'judul'                     => $request->input('judul'),
            'telp'                      => $request->input('telp'),
            'afiliasi'                  => $request->input('afiliasi'),
            'pembayaran'                => $request->input('pembayaran'),
            'categories_scopuscamp_id'  => $request->input('categories_scopuscamp_id'),
            'camp'                     => $request->input('camp'),
            'mulai'                     => $request->input('mulai'),
            'selesai'                   => $request->input('selesai'),
            'tempat'                    => $request->input('tempat'),
            'status'                    => $status,
            'gambar'                    => $imagePath ?? null,
        ]);

        if ($save) {
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');
            // Kurangi jumlah kuota di kategori yang sesuai
            // $kategori = CategoriesScopusCamp::find($request->input('categories_scopuscamp_id'));
            // if ($kategori) {
            //     $kategori->decrement('kuota');
            // }

            Mail::to($emailTo)->send(new ScopusCampMail($save, $appName));
            // Redirect ke rute testimoni dengan ID peserta
            return redirect()->route('account.scopuscamp.form')->with('success', 'Pendaftaran Scopus Camp Terkirim!');
        } else {
            // Redirect dengan pesan kesalahan jika pembuatan data gagal
            return redirect()->route('account.scopuscamp.form')->with('error', 'Pendaftaran Scopus Camp Gagal Terkirim!');
        }
    }

    public function destroy($id)
    {
        try {
            $scopuscamp = ScopusCamp::find($id);

            if ($scopuscamp) {
                $scopuscamp->delete();
                return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
