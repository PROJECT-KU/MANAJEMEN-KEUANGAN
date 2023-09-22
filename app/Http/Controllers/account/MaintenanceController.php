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
    // Dalam MaintenanceController.php
    public function maintenance($id)
    {
        $maintenance = Maintenance::findOrFail($id);

        return view('account.maintenance.index', compact('maintenance'));
    }

    public function update(Request $request, $id)
    {
        // Find the user by ID
        $maintenance = Maintenance::findOrFail($id);

        // Validate the request data (uncomment and modify as needed)
        // $request->validate([
        //     'full_name' => 'required',
        //     'company' => '',
        //     'email' => 'required|email',
        //     'username' => 'required',
        //     'level' => 'required',
        //     'jenis' => 'required',
        //     'telp' => 'required',
        // ]);



        // Update user data
        $maintenance->update([
            'note' => $request->input('note'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        // Redirect with success message
        return redirect()->route('account.maintenance.index', $maintenance->id)->with('success', 'Data Maintenance berhasil diperbarui!');
    }
}
