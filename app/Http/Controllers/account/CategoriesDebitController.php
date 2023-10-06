<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CategoriesDebitController extends Controller
{
    /**
     * CategoriesDebitController constructor.
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

        if ($user->level == 'staff' || $user->level == 'manager') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->join('users', 'categories_debit.user_id', '=', 'users.id')
                ->whereIn('users.level', ['staff', 'manager'])
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        } elseif ($user->level == 'karyawan') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->where('categories_debit.user_id', $user->id)
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        } else {
            $categories = [];
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.categories_debit.index', compact('categories', 'maintenances'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        if ($user->level == 'staff') {
            // Search for categories based on the user's company and name
            $categories = CategoriesDebit::join('users', 'categories_debit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->where('categories_debit.name', 'LIKE', '%' . $search . '%')
                ->where('categories_debit.kode', 'LIKE', '%' . $search . '%')
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // If not a manager or staff, search for categories based on user_id and name
            $categories = CategoriesDebit::where('user_id', $user->id)
                ->where('name', 'LIKE', '%' . $search . '%')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }

        return view('account.categories_debit.index', compact('categories'));
    }
    public function create()
    {
        return view('account.categories_debit.create');
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
                'name'  => 'required'
            ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));

        // Ambil nilai terakhir dari kolom 'kode'
        $lastCode = CategoriesDebit::max('kode');

        $prefix = '';

        if (Auth::user()->level == "manager") {
            $prefix = 'DM';
        } elseif (Auth::user()->level == "admin") {
            $prefix = 'DA';
        } elseif (Auth::user()->level == "karyawan") {
            $prefix = 'DK';
        } else {
            $prefix = 'DU';
        }

        $existingCategoriesCount = CategoriesDebit::where('kode', 'like', $prefix . '%')->count();

        if (empty($lastCode) || $existingCategoriesCount == 0) {
            // If no existing categories or first category for the level, start with '002'
            $newCode = $prefix . '002';
        } else {
            // If there are existing categories, get the highest code and increment it
            $lastCategory = CategoriesDebit::where('kode', 'like', $prefix . '%')->orderBy('kode', 'desc')->first();
            $lastCode = $lastCategory->kode;
            $newCode = sprintf($prefix . '%03d', intval(substr($lastCode, 2)) + 1);
        }


        //Eloquent simpan data
        $save = CategoriesDebit::create([
            'user_id'       => Auth::user()->id,
            'kode'           => $newCode,
            'name'          => $name
        ]);
        //cek apakah data berhasil disimpan
        if ($save) {
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_debit.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('account.categories_debit.index')->with(['error' => 'Data Gagal Disimpan!']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, CategoriesDebit $categoriesDebit)
    {
        return view('account.categories_debit.edit', compact('categoriesDebit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CategoriesDebit $categoriesDebit)
    {
        //set validasi required
        $this->validate(
            $request,
            [
                'name'  => 'required'
            ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));
        //Eloquent simpan data
        $update = CategoriesDebit::whereId($categoriesDebit->id)->update([
            'user_id'       => Auth::user()->id,
            'name'          => $name
        ]);
        //cek apakah data berhasil disimpan
        if ($update) {
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_debit.index')->with(['success' => 'Data Berhasil Diupdate!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('account.categories_debit.index')->with(['error' => 'Data Gagal Diupdate!']);
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

        $delete = CategoriesDebit::find($id)->delete($id);

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
