<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\Debit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitController extends Controller
{
  /**
   * DebitController constructor.
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
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->leftJoin('users', 'debit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);
    } else {
      // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->where('debit.user_id', $user->id)
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);
    }

    // Mengubah format tanggal menjadi "dd-mm-yyyy h:i"
    foreach ($debit as $item) {
      $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
    }

    $maintenances = DB::table('maintenance')
    ->orderBy('created_at', 'DESC')
    ->get();

    return view('account.pesanan.index', compact('debit', 'maintenances'));
  }


  public function search(Request $request)
  {
    $search = $request->get('q');
    $user = Auth::user();

    if ($user->level == 'manager' || $user->level == 'staff') {
      // Jika user adalah 'manager' atau 'staff', ambil semua data transaksi yang memiliki perusahaan yang sama dengan user
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->leftJoin('users', 'debit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->where(function ($query) use ($search) {
          $query->where('debit.description', 'LIKE', '%' . $search . '%')
            ->orWhere(
              'categories_debit.name',
              'LIKE',
              '%' . $search . '%'
            )
            ->orWhere('debit.nominal', 'LIKE', '%' . $search . '%')
            ->orWhere('debit.debit_date', 'LIKE', '%' . $search . '%');
        })
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);
    } else {
      // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->where('debit.user_id', $user->id)
        ->where(function ($query) use ($search) {
          $query->where(
            'debit.description',
            'LIKE',
            '%' . $search . '%'
          )
            ->orWhere('categories_debit.name', 'LIKE', '%' . $search . '%')
            ->orWhere('debit.nominal', 'LIKE', '%' . $search . '%')
            ->orWhere('debit.debit_date', 'LIKE', '%' . $search . '%');
        })
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);
    }

    foreach ($debit as $item) {
      $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
    }
    return view('account.pesanan.index', compact('debit'));
  }


  public function create()
  {
    $user = Auth::user();
    if ($user->level == 'manager' || $user->level == 'staff') {
      $categories = CategoriesDebit::join('users', 'categories_debit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->get(['categories_debit.*']);

      return view('account.pesanan.create', compact('categories'));
    } else {
      $categories = CategoriesDebit::where('user_id', Auth::user()->id)
        ->get();
      return view('account.pesanan.create', compact('categories'));
    }
  }
  public function store(Request $request)
  {
    // Pastikan hanya user dengan role 'manager' atau 'staff' yang bisa melakukan create
    $user = Auth::user();
    if ($user->level == 'manager' || $user->level == 'staff') {
      // Lakukan validasi data yang diinputkan
      $this->validate(
        $request,
        [
          'nominal'       => 'required',
          'debit_date'    => 'required',
          'category_id'   => 'required',
          'description'   => 'required'
        ],
        [
          'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
          'debit_date.required' => 'Silahkan Pilih Tanggal!',
          'category_id.required' => 'Silahkan Pilih Kategori!',
          'description.required' => 'Masukkan Keterangan!',
        ]
      );

      // Buat data transaksi baru
      $save = Debit::create([
        'user_id'       => Auth::user()->id,
        'debit_date'   => $request->input('debit_date'),
        'category_id'   => $request->input('category_id'),
        'nominal'       => str_replace(",", "", $request->input('nominal')),
        'description'   => $request->input('description'),
      ]);

      // Redirect dengan pesan sukses
      if ($save) {
        //redirect dengan pesan sukses
        return redirect()->route('account.pesanan.index')->with(['success' => 'Data Berhasil Disimpan!']);
      } else {
        //redirect dengan pesan error
        return redirect()->route('account.pesanan.index')->with(['error' => 'Data Gagal Disimpan!']);
      }
    } else {
      // Redirect dengan pesan error jika bukan manager atau staff
      $this->validate(
        $request,
        [
          'nominal'       => 'required',
          'debit_date'    => 'required',
          'category_id'   => 'required',
          'description'   => 'required'
        ],
        [
          'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
          'debit_date.required' => 'Silahkan Pilih Tanggal!',
          'category_id.required' => 'Silahkan Pilih Kategori!',
          'description.required' => 'Masukkan Keterangan!',
        ]
      );

      // Buat data transaksi baru
      $save = Debit::create([
        'user_id'       => Auth::user()->id,
        'debit_date'   => $request->input('debit_date'),
        'category_id'   => $request->input('category_id'),
        'nominal'       => str_replace(",", "", $request->input('nominal')),
        'description'   => $request->input('description'),
      ]);

      // Redirect dengan pesan sukses
      if ($save) {
        //redirect dengan pesan sukses
        return redirect()->route('account.pesanan.index')->with(['success' => 'Data Berhasil Disimpan!']);
      } else {
        //redirect dengan pesan error
        return redirect()->route('account.pesanan.index')->with(['error' => 'Data Gagal Disimpan!']);
      }
    }
  }


  public function edit(Request $request, Debit $debit)
  {
    $user = Auth::user();

    // Get all categories for users who are managers and staff in the same company
    if ($user->level == 'manager' || $user->level == 'staff') {
      $categories = CategoriesDebit::join('users', 'categories_debit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->get(['categories_debit.*']);

      return  view('account.pesanan.edit', compact('debit', 'categories'));
    } else {
      $categories = CategoriesDebit::where('user_id', Auth::user()->id)
        ->get();
      return  view('account.pesanan.edit', compact('debit', 'categories'));
    }
    //$categories = CategoriesDebit::where('user_id', Auth::user()->id)
    //    ->get();
    //return  view('account.debit.edit', compact('debit', 'categories'));
  }


  public function update(Request $request, Debit $debit)
  {
    // Pastikan hanya user dengan role 'manager' atau 'staff' yang bisa melakukan edit
    $user = Auth::user();
    if ($user->level == 'manager' || $user->level == 'staff') {
      // Cek apakah user memiliki hak akses untuk mengedit data transaksi
      if ($debit->user_id == $user->id) {
        // Lakukan validasi data yang diubah
        $this->validate(
          $request,
          [
            'nominal'       => 'required',
            'debit_date'    => 'required',
            'category_id'   => 'required',
            'description'   => 'required'
          ],
          [
            'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
            'debit_date.required' => 'Silahkan Pilih Tanggal!',
            'category_id.required' => 'Silahkan Pilih Kategori!',
            'description.required' => 'Masukkan Keterangan!',
          ]
        );

        // Perbarui data transaksi
        $debit->update([
          'debit_date'   => $request->input('debit_date'),
          'category_id'  => $request->input('category_id'),
          'nominal'      => str_replace(",", "", $request->input('nominal')),
          'description'  => $request->input('description'),
        ]);

        // Redirect dengan pesan sukses
        if ($debit) {
          //redirect dengan pesan sukses
          return redirect()->route('account.pesanan.index')->with(['success' => 'Data Berhasil Diupdate!']);
        } else {
          //redirect dengan pesan error
          return redirect()->route('account.pesanan.index')->with(['error' => 'Data Gagal Diupdate!']);
        }
      }
    } else {
      $this->validate(
        $request,
        [
          'nominal'       => 'required',
          'debit_date'    => 'required',
          'category_id'   => 'required',
          'description'   => 'required'
        ],
        [
          'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
          'debit_date.required' => 'Silahkan Pilih Tanggal!',
          'category_id.required' => 'Silahkan Pilih Kategori!',
          'description.required' => 'Masukkan Keterangan!',
        ]
      );

      // Perbarui data transaksi
      $debit->update([
        'debit_date'   => $request->input('debit_date'),
        'category_id'  => $request->input('category_id'),
        'nominal'      => str_replace(",", "", $request->input('nominal')),
        'description'  => $request->input('description'),
      ]);

      // Redirect dengan pesan sukses
      if ($debit) {
        //redirect dengan pesan sukses
        return redirect()->route('account.pesanan.index')->with(['success' => 'Data Berhasil Diupdate!']);
      } else {
        //redirect dengan pesan error
        return redirect()->route('account.pesanan.index')->with(['error' => 'Data Gagal Diupdate!']);
      }
    }
  }
  public function destroy($id)
  {
    $delete = Debit::find($id)->delete($id);

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
