<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
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

class ClinikScopusBiayaPersesiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {
        // Ambil semua data dari tabel clinikscopus_biaya_persesi
        $biayaPersesi = ClinikscopusBiayaPersesi::all();

        // Kirim ke view
        return view('account.clinik_scopus_biaya_persesi.index', compact('biayaPersesi'));
    }
    // <!--================== END ==================-->

    // <!--================== CREATE DATA ==================-->
    public function create()
    {

        return view('account.clinik_scopus_biaya_persesi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'biaya_persesi' => 'required',
            'status' => 'required|in:active,non active',
        ]);

        $biayaPersesi = str_replace(['Rp', '.', ' '], '', $request->biaya_persesi);
        $biayaPersesi = (int)$biayaPersesi;

        ClinikScopusBiayaPersesi::create([
            'biaya_persesi' => $biayaPersesi,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('account.Clinik-Scopus-Biaya-Persesi.index')
            ->with('success', 'Data berhasil disimpan!');
    }

    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function edit($id)
    {
        $biayaPersesi = ClinikScopusBiayaPersesi::findOrFail($id);

        return view(
            'account.clinik_scopus_biaya_persesi.edit',
            compact('biayaPersesi')
        );
    }


    public function update(Request $request, $id)
    {

        $datas = ClinikScopusBiayaPersesi::findOrFail($id);
        $biayaPersesi = str_replace(['Rp', '.', ' '], '', $request->biaya_persesi);
        $biayaPersesi = (int)$biayaPersesi;


        $datas->update([
            'biaya_persesi' => $biayaPersesi,
            'status' => $request->status,
        ]);

        return redirect()->route('account.Clinik-Scopus-Biaya-Persesi.index')->with('success', 'Biaya Persesi berhasil diperbarui');
    }
    // <!--================== END ==================-->

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        try {
            $biayaPersesi = ClinikScopusBiayaPersesi::findOrFail($id);
            $biayaPersesi->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data!'
            ], 500);
        }
    }
    // <!--================== END ==================-->
}
