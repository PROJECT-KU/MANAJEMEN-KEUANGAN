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

class ClinikScopusTrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== TAMPILAN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');
        // $nama = $request->get('nama');
        $sesi = $request->get('sesi');
        $sesi2 = $request->get('sesi2');
        $sesi3 = $request->get('sesi3');
        $sesi4 = $request->get('sesi4');
        $sesi5 = $request->get('sesi5');
        $sesi6 = $request->get('sesi6');
        $sesi7 = $request->get('sesi7');

        $perPage = $request->get('per_page', 10);

        $data = DB::table('clinikscopus')->get();

        return view('account.clinik_scopus.index', compact(
            'data',
            'status',
            // 'nama',
            'sesi',
            'sesi2',
            'sesi3',
            'sesi4',
            'sesi5',
            'sesi6',
            'sesi7',
        ));
    }
    // <!--================== END ==================-->

    // <!--================== create DATA ==================-->
    public function create(Request $request)
    {
        return view('account.clinik_scopus.create');
    }
    // <!--================== END ==================-->
    public function store(Request $request)
    {
        $user = Auth::user();
        return view('account.user.index', compact('users'));

        $this->validate(
            $request,
            [
                'user_id' => 'users.id',
                'sesi' => 'required',
                'sesi2' => 'required',
                'sesi3' => 'required',
                'sesi4' => 'required',
                'sesi5' => 'required',
                'sesi6' => 'required',
                'sesi7' => 'required',
                'spesialis' => 'required',
                'status' => 'required',
                'tanggal' => 'required',
            ],
            [
                'user_id' => 'users.id',
                'sesi.required' => 'Masukkan sesi!',
                'sesi2.required' => 'Masukkan sesi!',
                'sesi3.required' => 'Masukkan sesi!',
                'sesi4.required' => 'Masukkan sesi!',
                'sesi5.required' => 'Masukkan sesi!',
                'sesi6.required' => 'Masukkan sesi!',
                'sesi7.required' => 'Masukkan sesi!',
                'spesialis.required' => 'Masukan spesialis',
                'status.required' => 'Masukan status',
                'tanggal.required' => 'Masukan tanggal',
            ]
        );

        //menyinpan image di path
        $imagePath = null;

        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
        }
        //end


        $save = Clinikscopus::create([
            'nama' => $request->input('nama'),
            'sesi' => $request->input('sesi'),
            'sesi2' => $request->input('sesi2'),
            'sesi3' => $request->input('sesi3'),
            'sesi4' => $request->input('sesi4'),
            'sesi5' => $request->input('sesi5'),
            'sesi6' => $request->input('sesi6'),
            'sesi7' => $request->input('sesi7'),
            'spesialis' => $request->input('spesialis'),
            'status' => $request->input('status'),
            'tanggal' => $request->input('tanggal'),
            'foto' => $imagePath ?? null,
        ]);

        // Redirect with success or error message
        if ($save) {
            return redirect()->route('account.clinikscopus.index')->with('success', 'Data Trainer Berhasil Disimpan!');
        } else {
            return redirect()->route('account.clinikscopus.index')->with('error', 'Data Trainer Gagal Disimpan!');
        }
    }


    // <!--================== EDIT DATA ==================-->
    public function edit($id)
    {
        $datas = Clinikscopus::findOrFail($id);
        return view('account.clinik_scopus.edit', compact('datas'));
    }
    public function update(Request $request, $id)
    {
        $datas = Clinikscopus::findOrFail($id);

        // Validasi (opsional tapi disarankan)
        $request->validate([
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Default pakai foto lama
        $imagePath = $datas->foto;

        // Jika upload foto baru
        if ($request->hasFile('foto')) {

            // 🔥 HAPUS FOTO LAMA (JIKA ADA)
            if ($datas->foto && file_exists(public_path('images/' . $datas->foto))) {
                unlink(public_path('images/' . $datas->foto));
            }

            // SIMPAN FOTO BARU
            $image = $request->file('foto');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            // Set path foto baru
            $imagePath = $imageName;
        }

        // Update data
        $datas->update([
            'nama'       => $request->nama,
            'sesi'       => $request->sesi,
            'sesi2'       => $request->sesi2,
            'sesi3'       => $request->sesi3,
            'sesi4'       => $request->sesi4,
            'sesi5'       => $request->sesi5,
            'sesi6'       => $request->sesi6,
            'sesi7'       => $request->sesi7,
            'spesialis'  => $request->spesialis,
            'status'     => $request->status,
            'tanggal'    => $request->tanggal,
            'foto'       => $imagePath,
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
