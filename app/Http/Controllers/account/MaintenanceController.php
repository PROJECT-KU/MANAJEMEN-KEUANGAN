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
                'note' => 'required',
            ],
            [
                'title.required' => 'Masukkan Judul Maintenance!',
                'note.required' => 'Masukkan Pesan Maintenance!',
            ]
        );

        $maintenance = Maintenance::create([
            'title' => $request->input('title'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'note' => $request->input('note'),
            'status' => $request->input('status'),
        ]);

        // if ($maintenance && strtotime($request->input('end_date')) <= strtotime(now())) {
        //     $maintenance->update(['status' => 'non-aktif']);
        // }


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
        return view('errors.page-maintenance');
    }

    public function destroy($id)
    {
        $delete = Maintenance::find($id)->delete($id);

        if ($delete) {
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'error'
            ]);
        }
    }
}
