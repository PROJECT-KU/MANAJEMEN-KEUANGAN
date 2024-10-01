<?php

namespace App\Http\Controllers\account;

use App\Karir;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\KarirCreateMail;
use App\Mail\KarirUpdateMail;

class KarirController extends Controller
{
    /**
     * PenyewaanController constructor.
     */
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

        $karir = DB::table('karir')
            ->select('karir.id', 'karir.token', 'karir.nama', 'karir.telp', 'karir.email', 'karir.cv', 'karir.lamaran', 'karir.lainnya', 'karir.pendidikan', 'karir.posisi', 'karir.desc', 'karir.status', 'karir.tanggal_interview', 'karir.lokasi_interview', 'karir.created_at', 'karir.updated_at')
            ->orderBy('karir.created_at', 'DESC')
            ->paginate(10);


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('karir.list', compact('karir', 'maintenances', 'startDate', 'endDate'));
    }

    public function detail(Request $request, $id, $token)
    {
        $karir = DB::table('karir')
            ->select('karir.id', 'karir.token',  'karir.nama', 'karir.telp', 'karir.email', 'karir.cv', 'karir.lamaran', 'karir.lainnya', 'karir.pendidikan', 'karir.posisi', 'karir.desc', 'karir.status', 'karir.tanggal_interview', 'karir.lokasi_interview', 'karir.created_at', 'karir.updated_at')
            ->where('karir.id', $id)
            ->first();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('karir.detail', compact('karir', 'maintenances'));
    }

    public function edit(Request $request, $id, $token)
    {
        $karir = DB::table('karir')
            ->select('karir.id', 'karir.token',  'karir.nama', 'karir.telp', 'karir.email', 'karir.cv', 'karir.lamaran', 'karir.lainnya', 'karir.pendidikan', 'karir.posisi', 'karir.desc', 'karir.status', 'karir.tanggal_interview', 'karir.lokasi_interview', 'karir.created_at', 'karir.updated_at')
            ->where('karir.id', $id)
            ->first();

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('karir.edit', compact('karir', 'maintenances'));
    }

    public function update(Request $request, $id)
    {
        $karir = Karir::findOrFail($id);

        // PATH UNTUK MENYIMPAN CV
        $cvPath = $karir->cv; // Default value is the existing value in the database
        if ($request->hasFile('cv')) {
            // Only update if a new file is uploaded
            $filecv = $request->file('cv');
            $cvName = time() . '_cv.' . $filecv->getClientOriginalExtension();
            $cvPath = $cvName;
            $filecv->move(public_path('karir'), $cvName);

            // If there was an existing file, delete it
            if ($karir->cv) {
                unlink(public_path('karir/' . $karir->cv));
            }
        }
        // END

        // PATH UNTUK MENYIMPAN LAMARAN
        $lamaranPath = $karir->lamaran; // Default value is the existing value in the database
        if ($request->hasFile('lamaran')) {
            // Only update if a new file is uploaded
            $filelamaran = $request->file('lamaran');
            $lamaranName = time() . '_lamaran.' . $filelamaran->getClientOriginalExtension();
            $lamaranPath = $lamaranName;
            $filelamaran->move(public_path('karir'), $lamaranName);

            // If there was an existing file, delete it
            if ($karir->lamaran) {
                unlink(public_path('karir/' . $karir->lamaran));
            }
        }
        // END

        // PATH UNTUK MENYIMPAN LAINNYA
        $lainnyaPath = $karir->lainnya; // Default value is the existing value in the database
        if ($request->hasFile('lainnya')) {
            // Only update if a new file is uploaded
            $filelainnya = $request->file('lainnya');
            $lainnyaName = time() . '_lainnya.' . $filelainnya->getClientOriginalExtension();
            $lainnyaPath = $lainnyaName;
            $filelainnya->move(public_path('karir'), $lainnyaName);

            // If there was an existing file, delete it
            if ($karir->lainnya) {
                unlink(public_path('karir/' . $karir->lainnya));
            }
        }
        // END

        $karir->update([
            'nama'              => $request->input('nama'),
            'telp'              => $request->input('telp'),
            'email'             => $request->input('email'),
            'tanggal'           => $request->input('tanggal'),
            'cv'                => $cvPath,
            'lamaran'           => $lamaranPath,
            'lainnya'           => $lainnyaPath,
            'pendidikan'        => $request->input('pendidikan'),
            'posisi'            => $request->input('posisi'),
            'status'            => $request->input('status'),
            'tanggal_interview' => $request->input('tanggal_interview'),
            'lokasi_interview'  => $request->input('lokasi_interview'),
            'desc'              => $request->input('desc'),
        ]);

        // Redirect with success or error message
        if ($karir) {
            // Send email or perform other actions if needed
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');
            $isStatus = $request->input('status') == 'Interview';
            if ($isStatus) {
                Mail::to($emailTo)->send(new KarirUpdateMail($karir, $appName, $isStatus));
            }
            return redirect()->route('karir.list')->with('success', 'Data Karir Berhasil Disimpan!');
        } else {
            // Redirect with an error message if data update fails
            return redirect()->route('karir.list')->with('error', 'Gagal Menyimpan Data Karir!');
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

        $karir = DB::table('karir')
            ->select('karir.id', 'karir.token',  'karir.nama', 'karir.telp', 'karir.email', 'karir.cv', 'karir.lamaran', 'karir.lainnya', 'karir.pendidikan', 'karir.posisi', 'karir.desc', 'karir.status', 'karir.tanggal_interview', 'karir.lokasi_interview', 'karir.created_at', 'karir.updated_at')
            ->where(function ($query) use ($search) {
                $query->where('karir.nama', 'LIKE', '%' . $search . '%')
                    ->orWhere('karir.posisi', 'LIKE', '%' . $search . '%')
                    ->orWhere('karir.status', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('karir.created_at', 'DESC')
            ->paginate(10);
        $karir->appends(['q' => $search]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        $karirExist = Karir::where('status', '<>', null)
            ->whereBetween('created_at', [$currentMonth, $nextMonth])
            ->exists();

        if ($karir->isEmpty()) {
            return redirect()->route('gaji.list')->with('error', 'Data Karir tidak ditemukan.');
        }
        return view('karir.list', compact('karir', 'maintenances', 'startDate', 'endDate', 'karirExist'));
    }

    // <!-- DELETE DATA -->
    public function destroy(Request $request, $id)
    {
        $karirData = Karir::findOrFail($id);

        // Delete the associated CV if it exists
        if ($karirData->cv && file_exists(public_path('karir/' . $karirData->cv))) {
            unlink(public_path('karir/' . $karirData->cv));
        }

        // Delete the associated Lamaran if it exists
        if ($karirData->lamaran && file_exists(public_path('karir/' . $karirData->lamaran))) {
            unlink(public_path('karir/' . $karirData->lamaran));
        }

        // Delete the associated Lainnya if it exists
        if ($karirData->lainnya && file_exists(public_path('karir/' . $karirData->lainnya))) {
            unlink(public_path('karir/' . $karirData->lainnya));
        }

        // Delete the record from the database
        $karirData->delete();

        // Return a JSON response with a status and message
        return response()->json(['statusdatadeleted' => 'success', 'message' => 'Data berhasil dihapus.']);
    }
    // <!-- END -->

    // <!--================== END ==================-->

    // <!--================== PUBLIC ==================-->
    public function index(Request $request)
    {

        return view('karir.index');
    }

    public function store(Request $request)
    {
        $token = $this->generateRandomToken(30);

        // PATH UNTUK MENYIMPAN CV
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $filecv = $request->file('cv');
            $cvName = time() . '_cv.' . $filecv->getClientOriginalExtension();
            $cvPath = $cvName;
            $filecv->move(public_path('karir'), $cvName);
        }
        // END

        // PATH UNTUK MENYIMPAN LAMARAN
        $lamaranPath = null;
        if ($request->hasFile('lamaran')) {
            $filelamaran = $request->file('lamaran');
            $lamaranName = time() . '_lamaran.' . $filelamaran->getClientOriginalExtension();
            $lamaranPath = $lamaranName;
            $filelamaran->move(public_path('karir'), $lamaranName);
        }
        // END

        // PATH UNTUK MENYIMPAN LAINNYA
        $lainnyaPath = null;
        if ($request->hasFile('lainnya')) {
            $filelainnya = $request->file('lainnya');
            $lainnyaName = time() . '_lainnya.' . $filelainnya->getClientOriginalExtension();
            $lainnyaPath = $lainnyaName;
            $filelainnya->move(public_path('karir'), $lainnyaName);
        }
        // END

        $save = Karir::create([
            'token'         => $token,
            'nama'          => $request->input('nama'),
            'telp'          => $request->input('telp'),
            'email'         => $request->input('email'),
            'tanggal'       => $request->input('tanggal'),
            'cv'            => $cvPath ?? null,
            'lamaran'       => $lamaranPath ?? null,
            'lainnya'       => $lainnyaPath ?? null,
            'pendidikan'    => $request->input('pendidikan'),
            'posisi'        => $request->input('posisi'),
            'desc'          => $request->input('desc'),
        ]);

        // Redirect with success or error message
        if ($save) {
            // dd($save);
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $request->input('email');

            Mail::to($emailTo)->send(new KarirCreateMail($save, $appName));

            return redirect()->route('karir.index')->with('success', 'Pendaftaran Recru Berhasil Disimpan!');
        } else {
            // Redirect with an error message if data creation fails
            return redirect()->route('karir.index')->with('error', 'Gagal menyimpan data laporan camp.');
        }
    }
    // <!--================== END ==================-->
}
