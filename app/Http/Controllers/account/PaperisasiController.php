<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Paperisasi;
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

class PaperisasiController extends Controller
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

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        // Ambil tanggal dari input atau gunakan default
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Ambil data dengan paginasi
        $datas = DB::table('paperisasi')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('account.paperisasi.index', compact('datas', 'startDate', 'endDate'));
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
        $datas = Paperisasi::whereBetween('tanggal_masuk_paper', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view('account.paperisasi.index', compact('datas', 'startDate', 'endDate'));
    }

    public function search(Request $request)
    {
        // Ambil input pencarian dan tanggal
        $search = $request->get('q');
        $startDate = $request->input('tanggal_awal', date('Y-m-01 00:00:00'));
        $endDate = $request->input('tanggal_akhir', date('Y-m-t 23:59:59'));

        // Query data menggunakan Eloquent dengan filter
        $datas = Paperisasi::query()
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('id_paper', 'LIKE', "%$search%")
                        ->orWhere('co_author', 'LIKE', "%$search%")
                        ->orWhere('jurnal_target', 'LIKE', "%$search%")
                        ->orWhere('q_jurnal', 'LIKE', "%$search%")
                        ->orWhere('status_paper', 'LIKE', "%$search%");
                }
            })
            ->whereBetween('tanggal_masuk_paper', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Tambahkan parameter pencarian ke paginasi
        $datas->appends($request->only(['q', 'tanggal_awal', 'tanggal_akhir']));

        // Jika data tidak ditemukan
        if ($datas->isEmpty()) {
            return redirect()->route('account.paperisasi.index')
                ->with('error', 'Data Paper Tidak Ditemukan.');
        }

        // Kembalikan ke view
        return view('account.paperisasi.index', compact('datas', 'startDate', 'endDate'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {
        return view('account.paperisasi.create');
    }

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $id_paper = $this->generateRandomId(5);

        // Maksimal ukuran file dalam byte (5 MB)
        $maxFileSize = 2 * 1024 * 1024;

        // Handle File Anatomy Upload
        if ($request->hasFile('file_anatomi')) {
            $fileAnatomy = $request->file('file_anatomi');
            if ($fileAnatomy->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy pertama melebihi 2 MB.');
            }
            $anatomyName = time() . '_' . Str::uuid() . '_anatomy.' . $fileAnatomy->getClientOriginalExtension();
            $anatomyPathFirst = 'paperisasi/' . $anatomyName;
            $fileAnatomy->move(public_path('paperisasi'), $anatomyName);
        }

        if ($request->hasFile('file_anatomi_second')) {
            $fileAnatomySecond = $request->file('file_anatomi_second');
            if ($fileAnatomySecond->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy kedua melebihi 2 MB.');
            }
            $anatomyNameSecond = time() . '_' . Str::uuid() . '_anatomy_second.' . $fileAnatomySecond->getClientOriginalExtension();
            $anatomyPathSecond = 'paperisasi/' . $anatomyNameSecond;
            $fileAnatomySecond->move(public_path('paperisasi'), $anatomyNameSecond);
        }

        if ($request->hasFile('file_anatomi_third')) {
            $fileAnatomyThird = $request->file('file_anatomi_third');
            if ($fileAnatomyThird->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy ketiga melebihi 2 MB.');
            }
            $anatomyNameThird = time() . '_' . Str::uuid() . '_anatomy_third.' . $fileAnatomyThird->getClientOriginalExtension();
            $anatomyPathThird = 'paperisasi/' . $anatomyNameThird;
            $fileAnatomyThird->move(public_path('paperisasi'), $anatomyNameThird);
        }

        // Handle File Paper Upload
        if ($request->hasFile('file_paper')) {
            $filePaper = $request->file('file_paper');
            if ($filePaper->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper pertama melebihi 2 MB.');
            }
            $paperName = time() . '_' . Str::uuid() . '_paper.' . $filePaper->getClientOriginalExtension();
            $paperPath = 'paperisasi/' . $paperName;
            $filePaper->move(public_path('paperisasi'), $paperName);
        }

        if ($request->hasFile('file_paper_second')) {
            $filePaperSecond = $request->file('file_paper_second');
            if ($filePaperSecond->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper kedua melebihi 2 MB.');
            }
            $paperNameSecond = time() . '_' . Str::uuid() . '_paper_second.' . $filePaperSecond->getClientOriginalExtension();
            $paperPathSecond = 'paperisasi/' . $paperNameSecond;
            $filePaperSecond->move(public_path('paperisasi'), $paperNameSecond);
        }

        if ($request->hasFile('file_paper_third')) {
            $filePaperThird = $request->file('file_paper_third');
            if ($filePaperThird->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper ketiga melebihi 2 MB.');
            }
            $paperNameThird = time() . '_' . Str::uuid() . '_paper_third.' . $filePaperThird->getClientOriginalExtension();
            $paperPathThird = 'paperisasi/' . $paperNameThird;
            $filePaperThird->move(public_path('paperisasi'), $paperNameThird);
        }

        $save = Paperisasi::create([
            // DETAIL PAPER
            'token'                         => $token,
            'id_paper'                      => $id_paper,
            'tanggal_masuk_paper'           => $request->input('tanggal_masuk_paper'),
            'judul_paper'                   => $request->input('judul_paper'),
            'first_author'                     => $request->input('first_author'),
            'affiliasi_first_author'           => $request->input('affiliasi_first_author'),
            'co_author'                     => $request->input('co_author'),
            'affiliasi_co_author'           => $request->input('affiliasi_co_author'),
            'jurnal_target'                 => $request->input('jurnal_target'),
            'q_jurnal'                      => $request->input('q_jurnal'),
            'estimasi_terbit'               => $request->input('estimasi_terbit'),
            'apc_jurnal'                    => $request->input('apc_jurnal'),
            'status_paper'                  => $request->input('status_paper'),


            // PROGRES ANATOMI 
            'progres_anatomi_tanggal'       => $request->input('progres_anatomi_tanggal'),
            'progres_anatomi_status'        => $request->input('progres_anatomi_status'),
            'progres_anatomi_estimasi'      => $request->input('progres_anatomi_estimasi'),
            'progres_anatomi_keterangan'    => $request->input('progres_anatomi_keterangan'),
            'file_anatomi'                  => $anatomyPathFirst ?? null,

            'progres_anatomi_tanggal_second'       => $request->input('progres_anatomi_tanggal_second'),
            'progres_anatomi_status_second'        => $request->input('progres_anatomi_status_second'),
            'progres_anatomi_estimasi_second'      => $request->input('progres_anatomi_estimasi_second'),
            'progres_anatomi_keterangan_second'    => $request->input('progres_anatomi_keterangan_second'),
            'file_anatomi_second'                  => $anatomyPathSecond ?? null,

            'progres_anatomi_tanggal_third'       => $request->input('progres_anatomi_tanggal_third'),
            'progres_anatomi_status_third'        => $request->input('progres_anatomi_status_third'),
            'progres_anatomi_estimasi_third'      => $request->input('progres_anatomi_estimasi_third'),
            'progres_anatomi_keterangan_third'    => $request->input('progres_anatomi_keterangan_third'),
            'file_anatomi_third'                  => $anatomyPathThird ?? null,

            // PROGRES PAPER
            'progres_paper_tanggal'         => $request->input('progres_paper_tanggal'),
            'progres_paper_status'          => $request->input('progres_paper_status'),
            'progres_paper_estimasi'        => $request->input('progres_paper_estimasi'),
            'progres_paper_keterangan'      => $request->input('progres_paper_keterangan'),
            'file_paper'                    => $paperPath ?? null,

            'progres_paper_tanggal_second'         => $request->input('progres_paper_tanggal_second'),
            'progres_paper_status_second'          => $request->input('progres_paper_status_second'),
            'progres_paper_estimasi_second'        => $request->input('progres_paper_estimasi_second'),
            'progres_paper_keterangan_second'      => $request->input('progres_paper_keterangan_second'),
            'file_paper_second'                    => $paperPathSecond ?? null,

            'progres_paper_tanggal_third'         => $request->input('progres_paper_tanggal_third'),
            'progres_paper_status_third'          => $request->input('progres_paper_status_third'),
            'progres_paper_estimasi_third'        => $request->input('progres_paper_estimasi_third'),
            'progres_paper_keterangan_third'      => $request->input('progres_paper_keterangan_third'),
            'file_paper_third'                    => $paperPathThird ?? null,
        ]);

        if ($save) {
            return redirect()->route('account.paperisasi.index')->with('success', 'Data Paperisasi Berhasil Disimpan!');
        } else {
            return redirect()->route('account.paperisasi.index')->with('error', 'Data Paperisasi Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== EDIT DATA ==================-->
    public function edit(Request $request, $id)
    {
        $datas = Paperisasi::findOrFail($id);
        return view('account.paperisasi.edit', compact('datas'));
    }

    public function update(Request $request, $id)
    {
        $datas = Paperisasi::findOrFail($id);

        $maxFileSize = 2 * 1024 * 1024;

        // Handle File Anatomy Upload
        if ($request->hasFile('file_anatomi')) {
            $fileAnatomy = $request->file('file_anatomi');
            if ($fileAnatomy->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy kedua melebihi 2 MB.');
            }
            $anatomyName = time() . '_' . Str::uuid() . '_anatomy.' . $fileAnatomy->getClientOriginalExtension();
            $anatomyPath = 'paperisasi/' . $anatomyName;

            // Check and delete the old file if it exists
            if ($datas->file_anatomi && file_exists(public_path($datas->file_anatomi))) {
                unlink(public_path($datas->file_anatomi));
            }

            $fileAnatomy->move(public_path('paperisasi'), $anatomyName);
            $datas->file_anatomi = $anatomyPath; // Update the file path in the database
        } else {
            $anatomyPath = $datas->file_anatomi;
        }

        if ($request->hasFile('file_anatomi_second')) {
            $fileAnatomySecond = $request->file('file_anatomi_second');
            if ($fileAnatomySecond->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy kedua melebihi 2 MB.');
            }
            $anatomyNameSecond = time() . '_' . Str::uuid() . '_anatomy_second.' . $fileAnatomySecond->getClientOriginalExtension();
            $anatomyPathSecond = 'paperisasi/' . $anatomyNameSecond;

            // Check and delete the old file if it exists
            if ($datas->file_anatomi_second && file_exists(public_path($datas->file_anatomi_second))) {
                unlink(public_path($datas->file_anatomi_second));
            }
            $fileAnatomySecond->move(public_path('paperisasi'), $anatomyNameSecond);
            $datas->file_anatomi_second = $anatomyPathSecond; // Update the file path in the database
        } else {
            $anatomyPathSecond = $datas->file_anatomi_second;
        }

        if ($request->hasFile('file_anatomi_third')) {
            $fileAnatomyThird = $request->file('file_anatomi_third');
            if ($fileAnatomyThird->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file anatomy ketiga melebihi 2 MB.');
            }
            $anatomyNameThird = time() . '_' . Str::uuid() . '_anatomy_third.' . $fileAnatomyThird->getClientOriginalExtension();
            $anatomyPathThird = 'paperisasi/' . $anatomyNameThird;

            // Check and delete the old file if it exists
            if ($datas->file_anatomi_third && file_exists(public_path($datas->file_anatomi_third))) {
                unlink(public_path($datas->file_anatomi_third));
            }
            $fileAnatomyThird->move(public_path('paperisasi'), $anatomyNameThird);
            $datas->file_anatomi_third = $anatomyPathThird; // Update the file path in the database
        } else {
            $anatomyPathThird = $datas->file_anatomi_third;
        }

        // Handle File Paper Upload
        if ($request->hasFile('file_paper')) {
            $filePaper = $request->file('file_paper');
            if ($filePaper->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper kedua melebihi 2 MB.');
            }
            $paperName = time() . '_' . Str::uuid() . '_paper.' . $filePaper->getClientOriginalExtension();
            $paperPath = 'paperisasi/' . $paperName;

            // Check and delete the old file if it exists
            if ($datas->file_paper && file_exists(public_path($datas->file_paper))) {
                unlink(public_path($datas->file_paper));
            }

            $filePaper->move(public_path('paperisasi'), $paperName);
            $datas->file_paper = $paperPath; // Update the file path in the database
        } else {
            $paperPath = $datas->file_paper;
        }

        if ($request->hasFile('file_paper_second')) {
            $filePaperSecond = $request->file('file_paper_second');
            if ($filePaperSecond->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper kedua melebihi 2 MB.');
            }
            $paperNameSecond = time() . '_' . Str::uuid() . '_paper_second.' . $filePaperSecond->getClientOriginalExtension();
            $paperPathSecond = 'paperisasi/' . $paperNameSecond;

            // Check and delete the old file if it exists
            if ($datas->file_paper_second && file_exists(public_path($datas->file_paper_second))) {
                unlink(public_path($datas->file_paper_second));
            }
            $filePaperSecond->move(public_path('paperisasi'), $paperNameSecond);
            $datas->file_paper_second = $paperPathSecond; // Update the file path in the database
        } else {
            $paperPathSecond = $datas->file_paper_second;
        }

        if ($request->hasFile('file_paper_third')) {
            $filePaperThird = $request->file('file_paper_third');
            if ($filePaperThird->getSize() > $maxFileSize) {
                return redirect()->back()->with('error', 'Ukuran file paper ketiga melebihi 2 MB.');
            }
            $paperNameThird = time() . '_' . Str::uuid() . '_paper_third.' . $filePaperThird->getClientOriginalExtension();
            $paperPathThird = 'paperisasi/' . $paperNameThird;

            // Check and delete the old file if it exists
            if ($datas->file_paper_third && file_exists(public_path($datas->file_paper_third))) {
                unlink(public_path($datas->file_paper_third));
            }
            $filePaperThird->move(public_path('paperisasi'), $paperNameThird);
            $datas->file_paper_third = $paperPathThird; // Update the file path in the database
        } else {
            $paperPathThird = $datas->file_paper_third;
        }


        $datas->update([
            // DETAIL PAPER
            'tanggal_masuk_paper'           => $request->input('tanggal_masuk_paper'),
            'judul_paper'                   => $request->input('judul_paper'),
            'first_author'                  => $request->input('first_author'),
            'affiliasi_first_author'        => $request->input('affiliasi_first_author'),
            'co_author'                     => $request->input('co_author'),
            'affiliasi_co_author'           => $request->input('affiliasi_co_author'),
            'jurnal_target'                 => $request->input('jurnal_target'),
            'q_jurnal'                      => $request->input('q_jurnal'),
            'estimasi_terbit'               => $request->input('estimasi_terbit'),
            'apc_jurnal'                    => $request->input('apc_jurnal'),
            'status_paper'                  => $request->input('status_paper'),

            // PROGRES ANATOMI 
            'progres_anatomi_tanggal'       => $request->input('progres_anatomi_tanggal'),
            'progres_anatomi_status'        => $request->input('progres_anatomi_status'),
            'progres_anatomi_estimasi'      => $request->input('progres_anatomi_estimasi'),
            'progres_anatomi_keterangan'    => $request->input('progres_anatomi_keterangan'),
            'file_anatomi'                  => $anatomyPath ?? null,

            'progres_anatomi_tanggal_second'       => $request->input('progres_anatomi_tanggal_second'),
            'progres_anatomi_status_second'        => $request->input('progres_anatomi_status_second'),
            'progres_anatomi_estimasi_second'      => $request->input('progres_anatomi_estimasi_second'),
            'progres_anatomi_keterangan_second'    => $request->input('progres_anatomi_keterangan_second'),
            'file_anatomi_second'                  => $anatomyPathSecond ?? null,

            'progres_anatomi_tanggal_third'       => $request->input('progres_anatomi_tanggal_third'),
            'progres_anatomi_status_third'        => $request->input('progres_anatomi_status_third'),
            'progres_anatomi_estimasi_third'      => $request->input('progres_anatomi_estimasi_third'),
            'progres_anatomi_keterangan_third'    => $request->input('progres_anatomi_keterangan_third'),
            'file_anatomi_third'                  => $anatomyPathThird ?? null,

            // PROGRES PAPER
            'progres_paper_tanggal'         => $request->input('progres_paper_tanggal'),
            'progres_paper_status'          => $request->input('progres_paper_status'),
            'progres_paper_estimasi'        => $request->input('progres_paper_estimasi'),
            'progres_paper_keterangan'      => $request->input('progres_paper_keterangan'),
            'file_paper'                    => $paperPath ?? null,

            'progres_paper_tanggal_second'         => $request->input('progres_paper_tanggal_second'),
            'progres_paper_status_second'          => $request->input('progres_paper_status_second'),
            'progres_paper_estimasi_second'        => $request->input('progres_paper_estimasi_second'),
            'progres_paper_keterangan_second'      => $request->input('progres_paper_keterangan_second'),
            'file_paper_second'                    => $paperPathSecond ?? null,

            'progres_paper_tanggal_third'         => $request->input('progres_paper_tanggal_third'),
            'progres_paper_status_third'          => $request->input('progres_paper_status_third'),
            'progres_paper_estimasi_third'        => $request->input('progres_paper_estimasi_third'),
            'progres_paper_keterangan_third'      => $request->input('progres_paper_keterangan_third'),
            'file_paper_third'                    => $paperPathThird ?? null,
        ]);

        if ($datas) {
            return redirect()->route('account.paperisasi.index')->with('success', 'Data Paperisasi Berhasil Disimpan!');
        } else {
            return redirect()->route('account.paperisasi.index')->with('error', 'Data Paperisasi Gagal Disimpan!');
        }
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy(Request $request, $id)
    {
        // Temukan data berdasarkan ID
        $data = Paperisasi::findOrFail($id);

        // Array file yang akan dicek dan dihapus
        $files = [
            $data->file_anatomi,
            $data->file_anatomi_second,
            $data->file_anatomi_third,
            $data->file_paper,
            $data->file_paper_second,
            $data->file_paper_third,
        ];

        // Iterasi untuk menghapus file
        foreach ($files as $filePath) {
            if ($filePath && file_exists(public_path($filePath))) {
                unlink(public_path($filePath));
            }
        }

        // Hapus data dari database
        $data->delete();

        // Return JSON response
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
            ->select('meme.id', 'meme.token', 'meme.name', 'meme.tanggal', 'meme.sesi', 'meme.waktu_mulai', 'meme.waktu_selesai', 'meme.kuota', 'meme.biaya', 'meme.deskripsi', 'meme.lokasi', 'meme.status', 'meme.gambar', 'meme.created_at', 'meme.updated_at')
            ->orderBy('meme.created_at', 'DESC')
            ->paginate(10);

        return view('account.data_meme.public.index', compact('meme'));
    }
    // <!--================== END ==================-->
}
