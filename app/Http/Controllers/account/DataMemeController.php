<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Meme;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use App\Mail\GajiSuccessMail;
use App\Exports\GajiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class DataMemeController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    function generateRandomToken($length)
    {
        $firstCharacter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$&-_?';
        $token = '';
        $token .= $firstCharacter[rand(0, strlen($firstCharacter) - 1)];
        for ($i = 1; $i < $length; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $token;
    }

    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();

        $meme = DB::table('meme')
            ->select('meme.id', 'meme.token', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at', 'meme.updated_at')
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(10);

        return view('account.data_meme.index', compact('meme'));
    }
    // <!--================== END ==================-->

    // <!--================== FILTER & SEARCH ==================-->
    public function filter(Request $request)
    {
        $user = Auth::user();
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
        $meme = DB::table('meme')
            ->select('meme.id', 'meme.token', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at', 'meme.updated_at')
            ->whereBetween('meme.created_at', [$currentMonth, $nextMonth])
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(10);

        // Kembalikan view dengan semua data yang diperlukan
        return view('account.data_meme.index', compact('meme', 'startDate', 'endDate'));
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

        $meme = DB::table('meme')
            ->select('meme.id', 'meme.token', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at', 'meme.updated_at')
            ->where('users.company', $user->company)
            ->where(function ($query) use ($search) {
                $query->orWhere('meme.sesi', 'LIKE', '%' . $search . '%')
                    ->orWhere('meme.status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(10);
        $meme->appends(['q' => $search]);

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($meme->isEmpty()) {
            return redirect()->route('account.data_meme.index')->with('error', 'Data Meme tidak ditemukan.');
        }
        return view('account.data_meme.index', compact('meme', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        return view('account.data_meme.create');
    }

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $filegambar = $request->file('gambar');
            $gambarName = time() . '.' . $filegambar->getClientOriginalExtension();
            $gambarPath = $gambarName;
            $filegambar->move(public_path('assets/img/meme'), $gambarName);
        }

        $biaya = $request->input('biaya');
        $biaya = empty($biaya) ? 0 : str_replace(",", "", $biaya);

        $save = Meme::create([
            'token'         => $token,
            'sesi'          => $request->input('sesi'),
            'waktu_mulai'   => $request->input('waktu_mulai'),
            'waktu_selesai' => $request->input('waktu_selesai'),
            'kuota'         => $request->input('kuota'),
            'biaya'         => $biaya ?? 0,
            'deskripsi'     => $request->input('deskripsi'),
            'lokasi'        => $request->input('lokasi'),
            'status'        => $request->input('status'),
            'gambar'        => $gambarPath ?? null,
        ]);

        if ($save) {
            return redirect()->route('account.meme.index')->with('success', 'Data Meme Berhasil Disimpan!');
        } else {
            return redirect()->route('account.meme.index')->with('erro', 'Data Meme Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== EDIT DATA ==================-->
    public function edit(Request $request, $id)
    {
        $datameme = Meme::findOrFail($id);
        return view('account.data_meme.edit', compact('datameme'));
    }

    public function update(Request $request, $id)
    {
        $datameme = Meme::findOrFail($id);
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $filegambar = $request->file('gambar');
            $gambarName = time() . '.' . $filegambar->getClientOriginalExtension();
            $gambarPath = $gambarName;
            $filegambar->move(public_path('assets/img/meme'), $gambarName);

            // Delete old image
            if ($datameme->gambar != null) {
                $oldImage = public_path('assets/img/meme/' . $datameme->gambar);
                if (File::exists($oldImage)) {
                    File::delete($oldImage);
                }
            }
        }

        $biaya = $request->input('biaya');
        $biaya = empty($biaya) ? 0 : str_replace(",", "", $biaya);

        $datameme->update([
            'sesi'          => $request->input('sesi'),
            'waktu_mulai'   => $request->input('waktu_mulai'),
            'waktu_selesai' => $request->input('waktu_selesai'),
            'kuota'         => $request->input('kuota'),
            'biaya'         => $biaya ?? 0,
            'deskripsi'     => $request->input('deskripsi'),
            'lokasi'        => $request->input('lokasi'),
            'status'        => $request->input('status'),
            'gambar'        => $gambarPath ?? $datameme->gambar,
        ]);

        if ($datameme) {
            return redirect()->route('account.meme.index')->with('success', 'Data Meme Berhasil Disimpan!');
        } else {
            return redirect()->route('account.meme.index')->with('error', 'Data Meme Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy(Request $request, $id)
    {
        $meme = Meme::findOrFail($id);

        if ($meme->gambar && file_exists(public_path('assets/img/meme/' . $meme->gambar))) {
            unlink(public_path('assets/img/meme/' . $meme->gambar));
        }

        $meme->delete();

        // Return a JSON response with a status and message
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus beserta file terkait.',
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== PUBLIC INDEX ==================-->
    public function publicindex(Request $request)
    {

        $meme = DB::table('meme')
            ->select('meme.id', 'meme.token', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at', 'meme.updated_at')
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(10);

        return view('account.data_meme.public.index', compact('meme'));
    }
    // <!--================== END ==================-->
}
