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
        $status = $request->get('status');
        $nama = $request->get('nama');
        $sesi = $request->get('sesi');
        $start = $request->get('start');
        $end = $request->get('end');
        $perPage = $request->get('per_page', 10);

        $datatrainer = DB::table('clinikscopus')
            ->when($status, function ($q, $status) {
                if ($status === 'aktif') {
                    $q->where('status', 'aktif');
                } elseif ($status === 'non aktif') {
                    $q->whereNull('status')->orWhere('status', 'non aktif');
                }
            })
            ->when($nama, function ($q, $nama) {
                if ($nama === 'nama') {
                    $q->whereNotNull('nama');
                } elseif ($nama === 'nama') {
                    $q->whereNull('nama');
                }
            })
            ->when($sesi, fn($q, $sesi) => $q->where('sesi', $sesi))
            ->when($start, fn($q, $start) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q, $end) => $q->whereDate('created_at', '<=', $end))
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage)
            ->appends($request->all());

        $data = DB::table('clinikscopus')->get();

        return view('account.clinik_scopus.index', compact(
            'datatrainer',
            'data',
            'status',
            'nama',
            'sesi',
            'start',
            'end',
            'perPage'
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

        $this->validate(
            $request,
            [
                'nama' => 'required',
                'sesi' => 'required',
                'spesialis' => 'required',
                'status' => 'required',
                'tanggal' => 'required',
            ],
            [
                'nama.required' => 'Masukkan Nama Trainer!',
                'sesi.required' => 'Masukkan sesi!',
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
}
