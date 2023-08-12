<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->level == 'manager') {
            // Jika user adalah 'manager', ambil semua data pengguna staff yang memiliki perusahaan yang sama dengan user
            $users = DB::table('users')
            ->where('company', $user->company)
                ->whereIn('level', ['staff', 'karyawan'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager', ambil hanya data pengguna itu sendiri
            $users = DB::table('users')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }

        return view('account.pengguna.index', compact('users'));
        //$users = User::orderBy('created_at', 'DESC')->paginate(10);

        //return view('account.pengguna.index', compact('users'));
    }

    // ... Other methods ...
    // ... Other methods ...

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('account.pengguna.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'company' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'), // Check email uniqueness in the 'users' table
            ],
            'username' => [
                'required',
                Rule::unique('users', 'username'), // Check username uniqueness in the 'users' table
            ],
            'password' => 'required',
            'level' => 'required',
            'jenis' => 'required',
            'telp' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.pengguna.create')
                ->withErrors($validator)
                ->withInput();
        }


        // Create a new user instance
        $user = new User();
        $user->full_name = $request->input('full_name');
        $user->company = $request->input('company');
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->password = bcrypt($request->input('password'));
        $user->level = $request->input('level');
        $user->jenis = $request->input('jenis');
        $user->telp = $request->input('telp');
        $user->notif = $request->input('notif');
        $user->tenggat = $request->input('tenggat');
        $user->title = $request->input('title');
        $user->email_verified_at = $request->input('email_verified_at') ? now() : null;

        if ($request->input('status')) {
            $user->status = 'on';
        } else {
            $user->status = 'off';
        }

        // Save the new user
        $user->save();

        // Redirect with success message
        return redirect()->route('account.pengguna.index')->with('success', 'Data pengguna berhasil ditambahkan!');
    }
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('account.pengguna.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        $user = User::findOrFail($id);

        return view('account.pengguna.detail', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'full_name' => 'required',
            'company' => '',
            'email' => 'required|email',
            'username' => 'required',
            'level' => 'required',
            'jenis' => 'required',
            'telp' => 'required',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update user data
        $user->full_name = $request->input('full_name');
        $user->company = $request->input('company');
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->level = $request->input('level');
        $user->jenis = $request->input('jenis');
        $user->telp = $request->input('telp');
        $user->notif = $request->input('notif');
        $user->tenggat = $request->input('tenggat');
        $user->title = $request->input('title');
        $user->email_verified_at = $request->input('email_verified_at') ? now() : null;

        if ($request->input('status')) {
            $user->status = 'on';
        } else {
            $user->status = 'off';
        }

        // Check if password is being updated
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        // Save the updated user data
        $user->save();

        // Redirect with success message
        return redirect()->route('account.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui!');
    }
    public function destroy($id)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return redirect()->route('account.pengguna.index')->with('error', 'Pengguna tidak ditemukan');
        }

        // Delete the user
        $user->delete();

        // Redirect with success message
        return redirect()->route('account.pengguna.index')->with('success', 'Data pengguna berhasil dihapus!');
    }
}
