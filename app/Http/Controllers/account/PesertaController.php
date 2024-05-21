<?php

namespace App\Http\Controllers\account;

use App\Peserta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesertaController extends Controller
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

    public function list(Request $request)
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

        $peserta = DB::table('peserta')
            ->select('peserta.id', 'peserta.email', 'peserta.nama', 'peserta.afiliasi', 'peserta.judul', 'peserta.jurnal', 'peserta.token', 'peserta.refrensi', 'peserta.digital_writing', 'peserta.mendeley', 'peserta.persentase_penyelesaian', 'peserta.submit', 'peserta.target', 'peserta.scopus_camp', 'peserta.materi', 'peserta.makanan', 'peserta.pelayanan', 'peserta.tempat', 'peserta.terfavorit', 'peserta.terbaik', 'peserta.terlucu', 'peserta.kritik')
            ->orderBy('peserta.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.peserta.index', compact('peserta', 'maintenances', 'startDate', 'endDate'));
    }

    public function detail($id, $token)
    {
        $peserta = Peserta::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

        return view('account.peserta.detail', compact('peserta')); // Sesuaikan path template dengan benar
    }

    public function destroy($id)
    {
        try {
            $peserta = Peserta::find($id);

            if ($peserta) {
                $peserta->delete();
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

        $peserta = DB::table('peserta')
            ->select('peserta.id', 'peserta.email', 'peserta.nama', 'peserta.afiliasi', 'peserta.judul', 'peserta.jurnal', 'peserta.token',  'peserta.refrensi', 'peserta.digital_writing', 'peserta.mendeley', 'peserta.persentase_penyelesaian', 'peserta.submit', 'peserta.target', 'peserta.scopus_camp', 'peserta.materi', 'peserta.makanan', 'peserta.pelayanan', 'peserta.tempat', 'peserta.terfavorit', 'peserta.terbaik', 'peserta.terlucu', 'peserta.kritik')
            ->where(function ($query) use ($search) {
                $query->where('peserta.nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('peserta.afiliasi', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('peserta.created_at', 'DESC')
            ->paginate(10);
        $peserta->appends(['q' => $search]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($peserta->isEmpty()) {
            return redirect()->route('account.peserta.list')->with('error', 'Data Laporan Peserta tidak ditemukan.');
        }
        return view('account.peserta.index', compact('peserta', 'maintenances', 'startDate', 'endDate'));
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

        $peserta = DB::table('peserta')
            ->select('peserta.id', 'peserta.email', 'peserta.nama', 'peserta.afiliasi', 'peserta.judul', 'peserta.jurnal', 'peserta.token',  'peserta.refrensi', 'peserta.digital_writing', 'peserta.mendeley', 'peserta.persentase_penyelesaian', 'peserta.submit', 'peserta.target', 'peserta.scopus_camp', 'peserta.materi', 'peserta.makanan', 'peserta.pelayanan', 'peserta.tempat', 'peserta.terfavorit', 'peserta.terbaik', 'peserta.terlucu', 'peserta.kritik')
            ->whereBetween('peserta.created_at', [$currentMonth, $nextMonth])
            ->orderBy('peserta.created_at', 'DESC')
            ->paginate(10);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.peserta.index', compact('peserta', 'maintenances', 'startDate', 'endDate'));
    }

    public function index(Request $request)
    {

        return view('account.peserta.form');
    }

    public function testimoni(Request $request, $id, $token_update)
    {
        // Retrieve the Peserta data based on the provided ID
        $peserta = Peserta::findOrFail($id);

        // Check if the provided token already exists in the database
        $existingPeserta = Peserta::where('token_update', $token_update)->first();

        // Check if the token_update is already filled and is not the same as the current peserta
        if ($existingPeserta && $existingPeserta->token_update !== null) {
            return redirect()->route('account.peserta.form')->with('error', 'Duplicate Token');
        }

        // Pass the Peserta data to the view
        return view('account.peserta.testimoni', compact('peserta'));
    }


    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);
        $save = Peserta::create([
            'email'                     => $request->input('email'),
            'nama'                      => $request->input('nama'),
            'afiliasi'                  => $request->input('afiliasi'),
            'judul'                     => $request->input('judul'),
            'jurnal'                    => $request->input('jurnal'),
            'camp'                      => $request->input('camp'),
            'refrensi'                  => $request->input('refrensi'),
            'digital_writing'           => $request->input('digital_writing'),
            'mendeley'                  => $request->input('mendeley'),
            'persentase_penyelesaian'   => $request->input('persentase_penyelesaian'),
            'submit'                    => $request->input('submit'),
            'target'                    => $request->input('target'),
            'token'                     => $token,
        ]);

        // Redirect with success or error message
        $pesertaId = $save->id;
        if ($save) {
            // Redirect to testimoni route with peserta ID
            return redirect()->route('account.peserta.testimoni', ['id' => $pesertaId, 'token' => $token]);
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('account.peserta.form')->with('error', 'Lembar Kerja Scopus Camp Gagal Disimpan!');
        }
    }

    public function update(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);
        $token_update = $this->generateRandomToken(100);
        $peserta->update([
            'scopus_camp'       => $request->input('scopus_camp'),
            'materi'            => $request->input('materi'),
            'makanan'           => $request->input('makanan'),
            'pelayanan'         => $request->input('pelayanan'),
            'tempat'            => $request->input('tempat'),
            'terfavorit'        => $request->input('terfavorit'),
            'terbaik'           => $request->input('terbaik'),
            'terlucu'           => $request->input('terlucu'),
            'kritik'            => $request->input('kritik'),
            'token_update'      => $token_update,
        ]);

        // Redirect with success or error message
        if ($peserta) {
            return redirect()->route('account.peserta.form')->with('success', 'Lembar Kerja & Evaluasi Scopus Camp Berhasil Disimpan!');
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('account.peserta.form')->with('error', 'Lembar Kerja & Evaluasi Scopus Camp Gagal Disimpan!');
        }
    }
}
