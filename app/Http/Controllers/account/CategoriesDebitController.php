<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\User;
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

    public function index()
    {
        $user = Auth::user();

        if ($user->level == 'staff' || $user->level == 'manager' || $user->level == 'ceo') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->join('users', 'categories_debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('categories_debit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->where('categories_debit.user_id', $user->id)
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        } else {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->where('categories_debit.user_id', $user->id)
                ->orderBy('categories_debit.created_at', 'DESC')
                ->paginate(10);
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.categories_debit.index', compact('categories', 'maintenances'));
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q');

        if ($user->level == 'manager' || $user->level == 'ceo') {
            $categories = CategoriesDebit::where('user_id', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('kode', 'LIKE', '%' . $search . '%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            if ($categories->isEmpty()) {
                // Jika tidak ada hasil, tampilkan error
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }
        } else if ($user->level == 'staff') {
            $manager = User::where('level', 'manager')
                ->where('company', $user->company)
                ->first();

            if ($manager) {
                $categories = CategoriesDebit::where('user_id', $manager->id)
                    ->where(function ($query) use ($search) {
                        $query->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('kode', 'LIKE', '%' . $search . '%');
                    })
                    ->orderBy('created_at', 'DESC')
                    ->paginate(10);

                if ($categories->isEmpty()) {
                    // Jika tidak ada hasil, tampilkan pesan error
                    return redirect()->back()->with('error', 'Data tidak ditemukan.');
                }
            } else {
                // Jika tidak ada manajer yang sesuai, tampilkan error
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }
        } else {
            $categories = CategoriesDebit::where('user_id', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('kode', 'LIKE', '%' . $search . '%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            if ($categories->isEmpty()) {
                // Jika tidak ada hasil, tampilkan error
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.categories_debit.index', compact('categories', 'maintenances'));
    }

    public function create()
    {
        return view('account.categories_debit.create');
    }

    public function store(Request $request)
    {
        //set validasi required
        $this->validate(
            $request,
            [
                'name'  => 'required',
                'kode'  => 'required'
            ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
                'kode.required' => 'Masukkan Kode Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));
        $kode = strtoupper($request->input('kode'));


        // untuk membuat kode kategori otomatis
        // $lastCode = CategoriesDebit::max('kode');

        // $prefix = '';

        // if (Auth::user()->level == "manager") {
        //     $prefix = 'DM';
        // } elseif (Auth::user()->level == "admin") {
        //     $prefix = 'DA';
        // } elseif (Auth::user()->level == "karyawan") {
        //     $prefix = 'DK';
        // } else {
        //     $prefix = 'DU';
        // }

        // $existingCategoriesCount = CategoriesDebit::where('kode', 'like', $prefix . '%')->count();

        // if (empty($lastCode) || $existingCategoriesCount == 0) {
        //     $newCode = $prefix . '002';
        // } else {
        //     $lastCategory = CategoriesDebit::where('kode', 'like', $prefix . '%')->orderBy('kode', 'desc')->first();
        //     $lastCode = $lastCategory->kode;
        //     $newCode = sprintf($prefix . '%03d', intval(substr($lastCode, 2)) + 1);
        // }


        //Eloquent simpan data
        $save = CategoriesDebit::create([
            'user_id'       => Auth::user()->id,
            'kode'           => $kode,
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

    public function edit(Request $request, CategoriesDebit $categoriesDebit)
    {
        return view('account.categories_debit.edit', compact('categoriesDebit'));
    }

    public function update(Request $request, CategoriesDebit $categoriesDebit)
    {
        //set validasi required
        $this->validate(
            $request,
            [
                'name'  => 'required',
                'kode'  => 'required'
            ],
            //set message validation
            [
                'name.required' => 'Masukkan Nama Kategori !',
                'kode.required' => 'Masukkan Kode Kategori !',
            ]
        );

        $name = strtoupper($request->input('name'));
        $kode = strtoupper($request->input('kode'));
        //Eloquent simpan data
        $update = CategoriesDebit::whereId($categoriesDebit->id)->update([
            'user_id'       => Auth::user()->id,
            'name'          => $name,
            'kode'          => $kode
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
