<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\User;
use App\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MaintenanceController extends Controller
{

    public function index()
    {
        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return view('account.maintenance.index', compact('maintenances'));
    }

    public function maintenance()
    {
        $maintenance = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return view('account.maintenance.blank', compact('maintenance'));
    }

    public function create()
    {
        return view('account.maintenance.create');
    }

    public function store(Request $request)
    {
        // Mengambil user yang saat ini terautentikasi
        $user = Auth::user();

        $this->validate(
            $request,
            [
                'title' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'note' => 'required',
                'gambar' => 'required', // Validasi gambar (opsional)
            ],
            [
                'title.required' => 'Masukkan Judul Maintenance!',
                'start_date.required' => 'Masukkan Tanggal Awal Maintenance!',
                'end_date.required' => 'Masukkan Tanggal Berakhirnya Maintenance!',
                'note.required' => 'Masukkan Pesan Maintenance!',
                'gambar.required' => 'Masukkan Gambar Maintenance.',
            ]
        );

        // Menyimpan gambar di path (jika ada)
        $imagePath = null;

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'images/' . $imageName; // Sesuaikan dengan path yang sesuai di konfigurasi
            $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
        }

        $maintenance = Maintenance::create([
            'title' => $request->input('title'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'note' => $request->input('note'),
            'gambar' => $imagePath,
        ]);

        if ($maintenance && strtotime($request->input('end_date')) <= strtotime(now())) {
            $maintenance->update(['status' => 'non-aktif']);
        }


        // Redirect dengan pesan sukses atau gagal
        if ($maintenance) {
            return redirect()->route('account.maintenance.index')->with('success', 'Data Maintenance Berhasil Disimpan!');
        } else {
            return redirect()->route('account.maintenance.index')->with('error', 'Data Maintenance Gagal Disimpan!');
        }
    }

    public function edit($id)
    {

        $maintenance = Maintenance::findOrFail($id);
        $status = $maintenance->status;
        return view('account.maintenance.edit', compact('maintenance', 'status'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        // $request->validate([
        //     'full_name' => 'required',
        //     'company' => '',
        //     'email' => 'required|email',
        //     'username' => 'required',
        //     'level' => 'required',
        //     'jenis' => 'required',
        //     'telp' => 'required',
        // ]);

        // Find the user by ID
        $maintenance = Maintenance::findOrFail($id);

        //save image to path
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName;
            $image->move(public_path('images'), $imageName); // Store the image
        } else {
            // If no new image uploaded, keep using the old image path
            $imagePath = $maintenance->gambar;
        }
        //end

        $title = $request->input('title');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $note = $request->input('note');
        $status = $request->input('status');

        // Update user data
        $maintenance->update([
            'title' => $title,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'note' => $note,
            'status' => $status,
            'gambar' => $imagePath, // Store the image path
        ]);

        // Save the updated user data
        if ($maintenance) {
            return redirect()->route('account.maintenance.index')->with('success', 'Data Maintenance Berhasil Disimpan!');
        } else {
            return redirect()->route('account.maintenance.index')->with('error', 'Data Maintenance Gagal Disimpan!');
        }
    }

    public function page()
    {
        return view('errors.page-maintenance', compact('maintenance'));
    }
}
