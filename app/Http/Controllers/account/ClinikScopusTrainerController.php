<?php

namespace App\Http\Controllers\account;

use App\Clinikscopus;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClinikScopusTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 🔥 AUTO UPDATE STATUS JIKA TANGGAL SUDAH LEWAT
        DB::table('clinikscopus')
            ->whereDate('tanggal_offline', '<', $today)
            ->where('status', 'active')
            ->update([
                'status' => 'non active'
            ]);

        // 🔽 AMBIL DATA SETELAH UPDATE
        $query = DB::table('clinikscopus')
            ->select(
                'clinikscopus.*',
                'users.full_name'
            )
            ->leftJoin('users', 'clinikscopus.user_id', '=', 'users.id')
            ->where('users.company', $user->company);

        // 🚀 LOGIKA PENGURUTAN CUSTOM
        $data = $query
            ->orderByRaw("CASE 
            /* 1. Prioritaskan data milik user yang sedang login */
            WHEN clinikscopus.user_id = ? THEN 1 
            /* 2. Prioritaskan data yang statusnya Active */
            WHEN clinikscopus.status = 'active' THEN 2 
            /* 3. Selain itu (Non Active) taruh di bawah */
            ELSE 3 
        END ASC", [$user->id])
            ->orderBy('clinikscopus.tanggal_online', 'DESC')
            ->paginate(12);

        return view('account.clinik_scopus.index', compact('data'));
    }
    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function search(Request $request)
    {
        $search = strtolower($request->get('q'));
        $user = Auth::user();

        $query = DB::table('clinikscopus')
            ->leftJoin('users', 'clinikscopus.user_id', '=', 'users.id')
            ->select('clinikscopus.*', 'users.full_name');

        // 🔐 Role filter
        if ($user->level === 'manager' && $user->company) {
            $query->where('users.company', $user->company);
        } elseif ($user->level === 'staff') {
            $query->where('clinikscopus.user_id', $user->id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(users.full_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(clinikscopus.spesialis) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(clinikscopus.status) LIKE ?', ["%{$search}%"])
                    // Format Search Tanggal (Contoh: "21 April 2026")
                    ->orWhereRaw("DATE_FORMAT(clinikscopus.tanggal_online, '%d %M %Y') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(clinikscopus.tanggal_offline, '%d %M %Y') LIKE ?", ["%{$search}%"])
                    // Sesi 1 - 9
                    ->orWhere(function ($sub) use ($search) {
                        for ($i = 1; $i <= 9; $i++) {
                            $field = $i == 1 ? 'sesi' : 'sesi' . $i;
                            $sub->orWhereRaw("LOWER(clinikscopus.$field) LIKE ?", ["%{$search}%"]);
                        }
                    });
            });
        }

        $data = $query
            ->orderByRaw("CASE WHEN clinikscopus.status = 'active' THEN 1 ELSE 2 END ASC")
            ->orderBy('clinikscopus.tanggal_online', 'DESC')
            ->paginate(10);

        return view('account.clinik_scopus.index', compact('data'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create(Request $request)
    {
        $auth = Auth::user();

        $datas = DB::table('users')
            ->select('id', 'full_name')
            ->where('company', $auth->company)
            ->orderBy('full_name', 'ASC')
            ->get();

        // 🔹 Ambil biaya persesi yang ACTIVE
        $biayaPersesiAktif = \App\ClinikScopusBiayaPersesi::where('status', 'active')->first();

        return view(
            'account.clinik_scopus.create',
            compact('datas', 'biayaPersesiAktif')
        );
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'user_id'   => 'required|exists:users,id',
                'spesialis' => 'required',
                'status'    => 'required',
                'tanggal_online' => 'required|date',
                'tanggal_offline' => 'required|date',
                'foto'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // 2 MB
            ],
            [
                'user_id.required'   => 'Nama karyawan wajib dipilih',
                'user_id.exists'     => 'Nama karyawan tidak valid',

                'spesialis.required' => 'Masukkan spesialis',
                'status.required'    => 'Masukkan status',

                'tanggal_online.required'   => 'Masukkan tanggal online',
                'tanggal_online.date'       => 'Format tanggal online tidak valid',
                'tanggal_offline.required'   => 'Masukkan tanggal offline',
                'tanggal_offline.date'       => 'Format tanggal offline tidak valid',

                // Pesan khusus foto
                'foto.image' => 'File harus berupa gambar',
                'foto.mimes' => 'Format foto harus JPG, JPEG, atau PNG',
                'foto.max'   => 'Ukuran foto maksimal 2 MB',
            ]
        );

        // simpan gambar
        $imagePath = null;

        if ($request->hasFile('foto')) {
            $image = $request->file('foto');

            // Generate UUID filename
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();

            // Move to public/ClinikScopusTrainer
            $image->move(public_path('ClinikScopusTrainer'), $imageName);

            // Save filename (or path if needed)
            $imagePath = $imageName;
        }

        $save = Clinikscopus::create([
            'user_id'           => $request->user_id,
            'sesi'              => $request->sesi,
            'sesi2'             => $request->sesi2,
            'sesi3'             => $request->sesi3,
            'sesi4'             => $request->sesi4,
            'sesi5'             => $request->sesi5,
            'sesi6'             => $request->sesi6,
            'sesi7'             => $request->sesi7,
            'sesi8'             => $request->sesi8,
            'sesi9'             => $request->sesi9,
            'spesialis'         => $request->spesialis,
            'status'            => $request->status,
            'tanggal_online'    => $request->tanggal_online,
            'tanggal_offline'   => $request->tanggal_offline,
            'biaya_persesi_id'  => $request->biaya_persesi_id,
            'foto'              => $imagePath,
        ]);

        return redirect()
            ->route('account.clinikscopus.index')
            ->with(
                $save ? 'success' : 'error',
                $save ? 'Data Trainer Berhasil Disimpan!' : 'Data Trainer Gagal Disimpan!'
            );
    }
    // <!--================== END ==================-->

    // <!--================== EDIT DATA ==================-->
    public function edit($id)
    {
        $datas = Clinikscopus::findOrFail($id);

        $users = DB::table('users')
            ->select('id', 'full_name')
            ->orderBy('full_name', 'ASC')
            ->get();

        // 🔹 Ambil biaya persesi yang ACTIVE
        $biayaPersesiAktif = \App\ClinikScopusBiayaPersesi::where('status', 'active')->first();

        return view(
            'account.clinik_scopus.edit',
            compact('datas', 'users', 'biayaPersesiAktif')
        );
    }
    public function update(Request $request, $id)
    {
        $datas = Clinikscopus::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'foto'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // FOTO
        $imagePath = $datas->foto;

        if ($request->hasFile('foto')) {

            // Hapus foto lama jika ada
            if ($datas->foto && file_exists(public_path('ClinikScopusTrainer/' . $datas->foto))) {
                unlink(public_path('ClinikScopusTrainer/' . $datas->foto));
            }

            $image = $request->file('foto');

            // Nama file UUID
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();

            // Simpan ke public/ClinikScopusTrainer
            $image->move(public_path('ClinikScopusTrainer'), $imageName);

            $imagePath = $imageName;
        }

        // UPDATE DATA
        $datas->update([
            'user_id'           => $request->user_id,
            'sesi'              => $request->sesi,
            'sesi2'             => $request->sesi2,
            'sesi3'             => $request->sesi3,
            'sesi4'             => $request->sesi4,
            'sesi5'             => $request->sesi5,
            'sesi6'             => $request->sesi6,
            'sesi7'             => $request->sesi7,
            'sesi8'             => $request->sesi8,
            'sesi9'             => $request->sesi9,
            'spesialis'         => $request->spesialis,
            'status'            => $request->status,
            'tanggal_online'    => $request->tanggal_online,
            'tanggal_offline'   => $request->tanggal_offline,
            'biaya_persesi_id' => $request->biaya_persesi_id,
            'foto'              => $imagePath,
        ]);

        return redirect()
            ->route('account.clinikscopus.index')
            ->with('success', 'Data Trainer Berhasil Diperbarui!');
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        try {
            $data = Clinikscopus::find($id);

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan!'
                ], 404);
            }

            // 🔥 HAPUS FOTO (JIKA ADA)
            if ($data->foto && file_exists(public_path('images/' . $data->foto))) {
                unlink(public_path('images/' . $data->foto));
            }

            // Hapus data dari database
            $data->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data dan gambar berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // <!--================== END ==================-->

}
