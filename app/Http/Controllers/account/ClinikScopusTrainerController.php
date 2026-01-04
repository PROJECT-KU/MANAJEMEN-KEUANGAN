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

class ClinikScopusTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $user = Auth::user();

        $data = DB::table('clinikscopus')
            ->select(
                'clinikscopus.*',
                'users.full_name',
                'users.nik',
                'users.norek',
                'users.bank'
            )
            ->leftJoin('users', 'clinikscopus.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
            ->orderBy('clinikscopus.created_at', 'DESC')
            ->paginate(20);

        return view('account.clinik_scopus.index', compact('data'));
    }

    // <!--================== END ==================-->


    // <!--================== create DATA ==================-->
    public function create(Request $request)
    {
        $auth = Auth::user();

        $datas = DB::table('users')
            ->select(
                'id',
                'full_name',
                'nik',
                'norek',
                'bank',
                'telp',
                'email'
            )
            ->where('company', $auth->company)
            ->orderBy('full_name', 'ASC')
            ->get();

        return view('account.clinik_scopus.create', compact('datas'));
    }

    // <!--================== END ==================-->

    // <!--================== FILTER ==================-->
    public function filter(Request $request)
    {
        $user = Auth::user();

        $query = DB::table('clinikscopus')
            ->leftJoin('users', 'clinikscopus.user_id', '=', 'users.id')
            ->select(
                'clinikscopus.*',
                'users.full_name'
            );

        // 🔐 Filter company
        if (in_array($user->level, ['manager', 'staff', 'ceo']) && $user->company) {
            $query->where('users.company', $user->company);
        }

        // 📅 FILTER TANGGAL (PAKAI KOLOM YANG BENAR)
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('clinikscopus.tanggal', [
                $request->tanggal_awal,
                $request->tanggal_akhir
            ]);
        }

        // 🔍 SEARCH
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('users.full_name', 'like', "%{$search}%")
                    ->orWhere('clinikscopus.spesialis', 'like', "%{$search}%")
                    ->orWhere('clinikscopus.status', 'like', "%{$search}%");
            });
        }

        $data = $query
            ->orderBy('clinikscopus.tanggal', 'desc')
            ->paginate(10)
            ->appends($request->all());

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

        // 🔍 SEARCH (LOWERCASE SAFE)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(users.full_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(clinikscopus.spesialis) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(clinikscopus.status) LIKE ?', ["%{$search}%"]);
            });
        }

        $data = $query
            ->orderBy('clinikscopus.tanggal', 'DESC') // 🔴 GANTI created_at
            ->paginate(10);

        return view('account.clinik_scopus.index', compact('data'));
    }



    // <!--================== END ==================-->

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'user_id'   => 'required|exists:users,id',
                'sesi'      => 'required',
                'sesi2'     => 'required',
                'sesi3'     => 'required',
                'sesi4'     => 'required',
                'sesi5'     => 'required',
                'sesi6'     => 'required',
                'sesi7'     => 'required',
                'spesialis' => 'required',
                'status'    => 'required',
                'tanggal'   => 'required|date',
            ],
            [
                'user_id.required' => 'Nama karyawan wajib dipilih',
                'user_id.exists'   => 'Nama karyawan tidak valid',

                'sesi.required'    => 'Masukkan sesi!',
                'sesi2.required'   => 'Masukkan sesi!',
                'sesi3.required'   => 'Masukkan sesi!',
                'sesi4.required'   => 'Masukkan sesi!',
                'sesi5.required'   => 'Masukkan sesi!',
                'sesi6.required'   => 'Masukkan sesi!',
                'sesi7.required'   => 'Masukkan sesi!',

                'spesialis.required' => 'Masukkan spesialis',
                'status.required'    => 'Masukkan status',
                'tanggal.required'   => 'Masukkan tanggal',
                'tanggal.date'       => 'Format tanggal tidak valid',
            ]
        );

        // ================= SIMPAN FOTO =================
        $imagePath = null;
        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imagePath = $imageName;
        }

        // ================= SIMPAN DATA =================
        $save = Clinikscopus::create([
            'user_id'   => $request->user_id,
            'sesi'      => $request->sesi,
            'sesi2'     => $request->sesi2,
            'sesi3'     => $request->sesi3,
            'sesi4'     => $request->sesi4,
            'sesi5'     => $request->sesi5,
            'sesi6'     => $request->sesi6,
            'sesi7'     => $request->sesi7,
            'spesialis' => $request->spesialis,
            'status'    => $request->status,
            'tanggal'   => $request->tanggal,
            'foto'      => $imagePath,
        ]);

        return redirect()
            ->route('account.clinikscopus.index')
            ->with(
                $save ? 'success' : 'error',
                $save ? 'Data Trainer Berhasil Disimpan!' : 'Data Trainer Gagal Disimpan!'
            );
    }


    // <!--================== EDIT DATA ==================-->
    public function edit($id)
    {
        $datas = Clinikscopus::findOrFail($id);

        $users = DB::table('users')
            ->select('id', 'full_name', 'nik', 'norek', 'bank', 'email')
            ->orderBy('full_name', 'ASC')
            ->get();

        return view(
            'account.clinik_scopus.edit',
            compact('datas', 'users')
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
            if ($datas->foto && file_exists(public_path('images/' . $datas->foto))) {
                unlink(public_path('images/' . $datas->foto));
            }

            $image = $request->file('foto');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imagePath = $imageName;
        }

        // UPDATE DATA
        $datas->update([
            'user_id'   => $request->user_id, // 🔥 BENAR
            'sesi'      => $request->sesi,
            'sesi2'     => $request->sesi2,
            'sesi3'     => $request->sesi3,
            'sesi4'     => $request->sesi4,
            'sesi5'     => $request->sesi5,
            'sesi6'     => $request->sesi6,
            'sesi7'     => $request->sesi7,
            'spesialis' => $request->spesialis,
            'status'    => $request->status,
            'tanggal'   => $request->tanggal,
            'foto'      => $imagePath,
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
