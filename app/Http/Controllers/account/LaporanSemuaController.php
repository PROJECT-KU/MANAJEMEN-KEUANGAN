<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\Debit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanSemuaController extends Controller
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
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
        ->leftJoin('users', 'debit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
        ->leftJoin('users', 'credit.user_id', '=', 'users.id')
        ->where('users.company', $user->company)
        ->orderBy('credit.created_at', 'DESC')
        ->paginate(10);
    } else {
      $debit = DB::table('debit')
        ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
        ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
        ->where('debit.user_id', Auth::user()->id)
        ->orderBy('debit.created_at', 'DESC')
        ->paginate(10);

      $credit = DB::table('credit')
        ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        ->where('credit.user_id', Auth::user()->id)
        ->orderBy('credit.created_at', 'DESC')
        ->paginate(10);
    }
    // Mengubah format tanggal menjadi "dd-mm-yyyy h:i" untuk debit
    foreach ($debit as $item) {
      $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
    }

    // Mengubah format tanggal menjadi "dd-mm-yyyy h:i" untuk kredit
    foreach ($credit as $item) {
      $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
    }

    return view('account.laporan_semua.index', compact('debit', 'credit'));
  }

  /**
   * @param Request $request
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function search(Request $request)
  {
    $search = $request->get('q');

    $debit = DB::table('debit')
      ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
      ->join('categories_debit', 'debit.category_id', '=', 'categories_debit.id', 'LEFT')
      ->where('debit.user_id', Auth::user()->id)
      ->where(function ($query) use ($search) {
        $query->where('debit.description', 'LIKE', '%' . $search . '%')
          ->orWhere('categories_debit.name', 'LIKE', '%' . $search . '%')
          ->orWhere('debit.nominal', 'LIKE', '%' . $search . '%')
          ->orWhere('debit.debit_date', 'LIKE', '%' . $search . '%');
      })
      ->orderBy('debit.created_at', 'DESC')
      ->paginate(10);

    foreach ($debit as $item) {
      $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
    }

    $credit = DB::table('credit')
      ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
      ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
      ->where('credit.user_id', Auth::user()->id)
      ->where(function ($query) use ($search) {
        $query->where('credit.description', 'LIKE', '%' . $search . '%')
          ->orWhere('categories_credit.name', 'LIKE', '%' . $search . '%')
          ->orWhere('credit.nominal', 'LIKE', '%' . $search . '%')
          ->orWhere('credit.credit_date', 'LIKE', '%' . $search . '%');
      })
      ->orderBy('credit.created_at', 'DESC')
      ->paginate(10);

    foreach ($credit as $item) {
      $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
    }

    return view('account.laporan_semua.index', compact('debit', 'credit'));
  }
  public function create()
  {
    $categories = CategoriesDebit::where('user_id', Auth::user()->id)
      ->get();
    return view('account.debit.create', compact('categories'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //set validasi required
    $this->validate(
      $request,
      [
        'nominal'       => 'required',
        'debit_date'    => 'required',
        'category_id'   => 'required',
        'description'   => 'required'
      ],
      //set message validation
      [
        'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
        'debit_date.required' => 'Silahkan Pilih Tanggal!',
        'category_id.required' => 'Silahkan Pilih Kategori!',
        'description.required' => 'Masukkan Keterangan!',
      ]
    );

    //Eloquent simpan data
    $save = Debit::create([
      'user_id'       => Auth::user()->id,
      'debit_date'   => $request->input('debit_date'),
      'category_id'   => $request->input('category_id'),
      'nominal'       => str_replace(",", "", $request->input('nominal')),
      'description'   => $request->input('description'),
    ]);
    //cek apakah data berhasil disimpan
    if ($save) {
      //redirect dengan pesan sukses
      return redirect()->route('account.debit.index')->with(['success' => 'Data Berhasil Disimpan!']);
    } else {
      //redirect dengan pesan error
      return redirect()->route('account.debit.index')->with(['error' => 'Data Gagal Disimpan!']);
    }
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit(Request $request, Debit $debit)
  {
    $categories = CategoriesDebit::where('user_id', Auth::user()->id)
      ->get();
    return  view('account.debit.edit', compact('debit', 'categories'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Debit $debit)
  {
    //set validasi required
    $this->validate(
      $request,
      [
        'nominal'       => 'required',
        'debit_date'    => 'required',
        'category_id'   => 'required',
        'description'   => 'required'
      ],
      //set message validation
      [
        'nominal.required' => 'Masukkan Nominal Debit / Uang Masuk!',
        'debit_date.required' => 'Silahkan Pilih Tanggal!',
        'category_id.required' => 'Silahkan Pilih Kategori!',
        'description.required' => 'Masukkan Keterangan!',
      ]
    );

    //Eloquent simpan data
    $update = Debit::whereId($debit->id)->update([
      'user_id'       => Auth::user()->id,
      'category_id'   => $request->input('category_id'),
      'debit_date'    => $request->input('debit_date'),
      'nominal'       => str_replace(",", "", $request->input('nominal')),
      'description'   => $request->input('description'),
    ]);
    //cek apakah data berhasil disimpan
    if ($update) {
      //redirect dengan pesan sukses
      return redirect()->route('account.debit.index')->with(['success' => 'Data Berhasil Diupdate!']);
    } else {
      //redirect dengan pesan error
      return redirect()->route('account.debit.index')->with(['error' => 'Data Gagal Diupdate!']);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
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
