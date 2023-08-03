<?php

namespace App\Http\Controllers\account;

use App\TambahBarang;
use App\Penyewaan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class PenyewaanController extends Controller
{
  /**
   * PenyewaanController constructor.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      // Jika user adalah 'manager' atau 'staff', ambil semua data transaksi yang memiliki perusahaan yang sama dengan user
      $penyewaan = Penyewaan::select('penyewaan.*', 'tambah_barang.nama_barang')
        ->join('tambah_barang', 'penyewaan.tambah_barang_id', '=', 'tambah_barang.id')
        ->join('users', 'penyewaan.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('penyewaan.created_at', 'DESC')
        ->paginate(10);
    } else {
      // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
      $penyewaan = Penyewaan::select('penyewaan.*', 'tambah_barang.nama_barang')
        ->join('tambah_barang', 'penyewaan.tambah_barang_id', '=', 'tambah_barang.id')
        ->where('penyewaan.user_id', $user->id)
        ->orderBy('penyewaan.created_at', 'DESC')
        ->paginate(10);
    }

    return view('account.penyewaan.index', compact('penyewaan'));
  }

  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    $penyewaan = Penyewaan::select('penyewaan.*', 'tambah_barang.nama_barang')
      ->join('tambah_barang', 'penyewaan.tambah_barang_id', '=', 'tambah_barang.id')
      ->join('users', 'penyewaan.user_id', '=', 'users.id')
      ->where(function ($query) use ($search) {
        $query->where('tambah_barang.nama_barang', 'LIKE', '%' . $search . '%')
          ->orWhere('penyewaan.nama', 'LIKE', '%' . $search . '%')
          ->orWhere('penyewaan.telp', 'LIKE', '%' . $search . '%')
          ->orWhere('penyewaan.alamat', 'LIKE', '%' . $search . '%')
          ->orWhere('penyewaan.identitas', 'LIKE', '%' . $search . '%');
      });

    if ($user->level == 'manager' || $user->level == 'staff') {
      // If the user is 'manager' or 'staff', filter by company
      $penyewaan->where('users.company', $user->company);
    } else {
      // If the user is not 'manager' or 'staff', filter by user_id
      $penyewaan->where('penyewaan.user_id', $user->id);
    }

    $penyewaan = $penyewaan->orderBy('penyewaan.created_at', 'DESC')
      ->paginate(10);

    return view('account.penyewaan.index', compact('penyewaan'));
  }



  public function create()
  {
    $user = Auth::user();
    if ($user->level == 'manager' || $user->level == 'staff') {
      $tambahBarang = TambahBarang::join('users', 'tambah_barang.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->get(['tambah_barang.*']);

      return view('account.penyewaan.create', compact('tambahBarang'));
    } else {
      $tambahBarang = TambahBarang::where('user_id', Auth::user()->id)
        ->get();
      return view('account.penyewaan.create', compact('tambahBarang'));
    }
  }
  public function store(Request $request)
  {
    $user = Auth::user();
    $this->validate(
      $request,
      [
        'tambah_barang_id' => 'required',
        'nama' => 'required',
        'email' => 'required',
        'telp' => 'required',
        'alamat' => 'required',
        'identitas' => 'required',
        'jumlah' => 'required|min:1', // Ensure jumlah is a positive number
        'tanggal' => 'required',
        'lama_peminjaman' => 'required|min:1',
      ],
      [
        'nama.required' => 'Masukkan Nama Peminjam!',
        'email.required' => 'Masukan Email Peminjam!',
        'telp.required' => 'Masukan Telp Peminjam!',
        'alamat.required' => 'Masukkan Alamat Peminjam!',
        'identitas.required' => 'Masukkan Identitas Peminjam!',
        'jumlah.required' => 'Masukkan Jumlah kendaaraan yang Dipinjam!',
        'jumlah.min' => 'Jumlah kendaaraan harus lebih dari 0!',
        'tanggal.required' => 'Masukkan Tanggal Peminjam!',
        'lama_peminjaman.min' => 'Lama Peminjaman kendaaraan harus lebih dari 0!',
        'lama_peminjaman.required' => 'Masukkan Lama Peminjaman kendaaraan!',
      ]
    );

    $tambahBarang = TambahBarang::find($request->input('tambah_barang_id'));
    if (!$tambahBarang) {
      return redirect()->back()->with(['error' => 'Data Kendaraan Tidak Ditemukan!']);
    }

    if ($tambahBarang->stok < $request->input('jumlah')) {
      return redirect()->back()->with(['error' => 'Stok kendaraan tidak mencukupi!']);
    }

    // Calculate subtotal based on harga_barang * jumlah
    $subtotal = $tambahBarang->harga_barang * $request->input('jumlah') * $request->input('lama_peminjaman');

    // Update stok of the selected kendaraan
    $tambahBarang->stok -= $request->input('jumlah');
    $tambahBarang->save();
    $diskon = $request->input('diskon');
    $diskon = empty($diskon) ? 0 : str_replace(",", "", $diskon); // Convert to numeric value or set to 0 if empty

    // Buat data transaksi baru
    $save = Penyewaan::create([
      'user_id' => Auth::user()->id,
      'tambah_barang_id' => $request->input('tambah_barang_id'),
      'nama' => $request->input('nama'),
      'email' => $request->input('email'),
      'telp' => $request->input('telp'),
      'alamat' => $request->input('alamat'),
      'identitas' => $request->input('identitas'),
      'jumlah' => $request->input('jumlah'),
      'lama_peminjaman' => $request->input('lama_peminjaman'),
      'tanggal' => $request->input('tanggal'),
      'subtotal' => $subtotal, // Set the calculated subtotal
      'total' => $subtotal - $diskon, // Calculate the total by subtracting diskon from subtotal
      'diskon' => $diskon,
    ]);

    // Redirect with success or error message
    if ($save) {
      // Get the ID of the newly created data
      $penyewaanId = $save->id;

      // Redirect to the detail page for the newly created data with SweetAlert notification
      return Redirect::route(
        'account.penyewaan.detail',
        ['id' => $penyewaanId]
      )->with('success', 'Data Berhasil Disimpan!');
    } else {
      // Redirect with an error message if data creation fails
      return redirect()->route('account.penyewaan.index')->with('error', 'Data Gagal Disimpan!');
    }
  }

  public function detail($id)
  {
    $penyewaan = Penyewaan::findOrFail($id);
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      $tambahBarang = TambahBarang::join('users', 'tambah_barang.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->get(['tambah_barang.*']);

      return view('account.penyewaan.detail', compact('penyewaan', 'tambahBarang'));
    } else {
      $tambahBarang = TambahBarang::where('user_id', Auth::user()->id)
        ->get();
      return view('account.penyewaan.detail', compact('penyewaan', 'tambahBarang'));
    }
  }

  public function edit($id)
  {
    $user = Auth::user();
    $penyewaan = Penyewaan::findOrFail($id);

    if ($user->level == 'manager' || $user->level == 'staff') {
      $tambahBarang = TambahBarang::join('users', 'tambah_barang.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->get(['tambah_barang.*']);
    } else {
      $tambahBarang = TambahBarang::where('user_id', Auth::user()->id)->get();
    }

    return view('account.penyewaan.edit', compact('penyewaan', 'tambahBarang'));
  }

  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $penyewaan = Penyewaan::findOrFail($id);

    $this->validate(
      $request,
      [
        'tambah_barang_id' => 'required',
        'nama' => 'required',
        'email' => 'required',
        'telp' => 'required',
        'alamat' => 'required',
        'identitas' => 'required',
        'jumlah' => 'required|min:1', // Ensure jumlah is a positive number
        'tanggal' => 'required',
        'lama_peminjaman' => 'required|min:1',
      ],
      [
        'nama.required' => 'Masukkan Nama Peminjam!',
        'email.required' => 'Masukan Email Peminjam!',
        'telp.required' => 'Masukan Telp Peminjam!',
        'alamat.required' => 'Masukkan Alamat Peminjam!',
        'identitas.required' => 'Masukkan Identitas Peminjam!',
        'jumlah.required' => 'Masukkan Jumlah kendaaraan yang Dipinjam!',
        'jumlah.min' => 'Jumlah kendaaraan harus lebih dari 0!',
        'tanggal.required' => 'Masukkan Tanggal Peminjam!',
        'jumlah.min' => 'Lama Peminjaman kendaaraan harus lebih dari 0!',
        'jumlah.required' => 'Masukkan Lama Peminjaman kendaaraan!',
      ]
    );

    $tambahBarang = TambahBarang::find($request->input('tambah_barang_id'));
    if (!$tambahBarang) {
      return redirect()->back()->with(['error' => 'Data Kendaraan Tidak Ditemukan!']);
    }

    if ($tambahBarang->stok < $request->input('jumlah')) {
      return redirect()->back()->with(['error' => 'Stok kendaraan tidak mencukupi!']);
    }

    // Calculate subtotal based on harga_barang * jumlah
    $subtotal = $tambahBarang->harga_barang * $request->input('jumlah') * $request->input('lama_peminjaman');

    // Update stok of the selected kendaraan
    $tambahBarang->stok -= $request->input('jumlah');
    $tambahBarang->save();
    $diskon = $request->input('diskon');
    $diskon = empty($diskon) ? 0 : str_replace(",", "", $diskon); // Convert to numeric value or set to 0 if empty

    // Update data transaksi
    $penyewaan->update([
      'tambah_barang_id' => $request->input('tambah_barang_id'),
      'nama' => $request->input('nama'),
      'email' => $request->input('email'),
      'telp' => $request->input('telp'),
      'alamat' => $request->input('alamat'),
      'identitas' => $request->input('identitas'),
      'jumlah' => $request->input('jumlah'),
      'lama_peminjaman' => $request->input('lama_peminjaman'),
      'tanggal' => $request->input('tanggal'),
      'subtotal' => $subtotal, // Set the calculated subtotal
      'total' => $subtotal - $diskon, // Calculate the total by subtracting diskon from subtotal
      'diskon' => $diskon,
    ]);

    // Redirect with success or error message
    if ($penyewaan) {
      // Redirect to the detail page for the updated data with SweetAlert notification
      return Redirect::route('account.penyewaan.detail', ['id' => $id])->with('success', 'Data Berhasil Diupdate!');
    } else {
      // Redirect with an error message if data update fails
      return redirect()->route('account.penyewaan.index')->with('error', 'Data Gagal Diupdate!');
    }
  }

  public function destroy($id)
  {
    $penyewaan = Penyewaan::find($id);
    if ($penyewaan) {
      $penyewaan->delete();
      return response()->json(['status' => 'success']);
    } else {
      return response()->json(['status' => 'error']);
    }
  }
}
