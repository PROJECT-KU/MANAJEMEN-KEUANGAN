<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User; // Make sure to import the User model if not imported already
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
  // ...

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $user = User::find($id);

    // Jika data pengguna tidak ditemukan, redirect atau tampilkan pesan kesalahan
    if (!$user) {
      return redirect()->route('account.profil.index')->with('error', 'User not found.');
    }

    // Jika user adalah 'manager' dan pengguna memiliki perusahaan yang sama, atau jika user bukan 'manager' dan ID pengguna sesuai dengan ID user saat ini
    if (
      Auth::check() && Auth::user()->level == 'manager' && Auth::user()->company == $user->company ||
      Auth::check() && Auth::user()->id == $user->id
    ) {
      return view('account.profil.index', compact('user'));
    } else {
      return redirect()->route('account.profil.index')->with('error', 'Access denied.');
    }
  }

  public function update(Request $request, $id)
  {
    // Validate the request data
    $request->validate([
      'full_name' => 'required',
      'company' => 'required',
      'username' => 'required',
      'telp' => 'required',
      'nik' => 'required',
      'norek' => 'required',
      'bank' => 'required',
    ], [
      'full_name.required'   => 'Masukkan Nama Lengkap!',
      'company.required'  => 'Masukkan Nama Tempat Anda Bekerja!',
      'username.required'          => 'Masukkan Username Anda!',
      'telp.required'          => 'Masukkan No Telp Anda!',
      'nik.required'          => 'Masukkan NIK Anda!',
      'norek.required'          => 'Masukkan Nomor Rekening Anda!',
      'bank.required'          => 'Masukkan BANK Anda!',
    ]);

    // Find the user by ID
    $user = User::findOrFail($id);

    // Update user data
    $user->full_name = $request->input('full_name');
    $user->company = $request->input('company');
    $user->username = $request->input('username');
    $user->telp = $request->input('telp');
    $user->nik = $request->input('nik');
    $user->norek = $request->input('norek');
    $user->bank = $request->input('bank');

    // Save the updated user data
    $user->save();

    return redirect()->route('account.profil.show', $user->id)->with('success', 'Data Diri Berhasil diperbarui!');
  }
}
