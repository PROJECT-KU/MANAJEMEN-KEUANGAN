<?php

namespace App\Http\Controllers\account;

use App\User;
use App\RefrensiPaper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RefrensiPaperController extends Controller
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
        // Ambil tanggal dari input atau gunakan default
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Ambil data dengan paginasi
        $datas = DB::table('refrensi_paper')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('account.refrensi_paper.index', compact('datas', 'startDate', 'endDate'));
    }

    // <!--================== END ==================-->

    // <!--================== FILTER & SEARCH ==================-->
    public function filter(Request $request)
    {
        $user = Auth::user();
        // Ambil tanggal dari input atau gunakan default
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Ambil data dengan paginasi
        $datas = RefrensiPaper::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('account.refrensi_paper.index', compact('datas', 'startDate', 'endDate'));
    }

    public function search(Request $request)
    {
        // Ambil input pencarian dan tanggal
        $search = $request->get('q');
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Query data menggunakan Eloquent dengan filter
        $datas = RefrensiPaper::query()
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('nama_author', 'LIKE', "%$search%")
                        ->orWhere('nama_journal', 'LIKE', "%$search%")
                        ->orWhere('quartile_journal', 'LIKE', "%$search%");
                }
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Tambahkan parameter pencarian ke paginasi
        $datas->appends($request->only(['q', 'tanggal_awal', 'tanggal_akhir']));

        // Jika data tidak ditemukan
        if ($datas->isEmpty()) {
            return redirect()->route('account.refrensi-paper.index')
                ->with('error', 'Data Refrensi Paper Tidak Ditemukan.');
        }

        // Kembalikan ke view
        return view('account.refrensi_paper.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        return view('account.refrensi_paper.create');
    }

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $save = RefrensiPaper::create([
            'token'                         => $token,
            'subjek_area_journal'           => $request->input('kata_kunci_tags'),
            'nama_journal'                  => $request->input('nama_journal'),
            'quartile_journal'              => $request->input('quartile_journal'),
            'nama_author'                   => $request->input('nama_author'),
            'judul_paper'                   => $request->input('judul_paper'),
            'doi'                           => $request->input('doi'),
            'apc'                           => $request->input('apc'),
            'type'                          => $request->input('type'),
            'abstrak'                       => $request->input('abstrak'),
        ]);

        if ($save) {
            return redirect()->route('account.refrensi-paper.index')->with('success', 'Data Refrensi Paper Berhasil Disimpan!');
        } else {
            return redirect()->route('account.refrensi-paper.index')->with('error', 'Data Refrensi Paper Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== EDIT DATA ==================-->
    public function edit(Request $request, $id)
    {
        $datas = RefrensiPaper::findOrFail($id);
        return view('account.refrensi_paper.edit', compact('datas'));
    }

    public function update(Request $request, $id)
    {
        $datas = RefrensiPaper::findOrFail($id);

        $datas->update([
            'subjek_area_journal'       => $request->input('kata_kunci_tags') ?? $datas->kata_kunci_tags,
            'nama_journal'              => $request->input('nama_journal') ?? $datas->nama_journal,
            'quartile_journal'          => $request->input('quartile_journal') ?? $datas->quartile_journal,
            'nama_author'               => $request->input('nama_author') ?? $datas->subjek_area_journal,
            'judul_paper'               => $request->input('judul_paper') ?? $datas->judul_paper,
            'doi'                       => $request->input('doi') ?? $datas->doi,
            'apc'                       => $request->input('apc') ?? $datas->apc,
            'type'                      => $request->input('type') ?? $datas->type,
            'abstrak'                   => $request->input('abstrak') ?? $datas->abstrak,
        ]);

        if ($datas) {
            return redirect()->route('account.refrensi-paper.index')->with('success', 'Data Refrensi Paper Berhasil Disimpan!');
        } else {
            return redirect()->route('account.refrensi-paper.index')->with('error', 'Data Refrensi Paper Gagal Disimpan!');
        }
    }

    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $data = RefrensiPaper::findOrFail($id);

        // Hapus data dari database
        $data->delete();

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus beserta file terkait.',
        ]);
    }
    // <!--================== END ==================-->
}
