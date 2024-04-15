<?php

namespace App\Http\Controllers\account;

use App\CategoriesCredit;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CategoriesCreditController extends Controller
{
    /**
     * CategoriesCreditController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->level == 'staff' || $user->level == 'manager' || $user->level == 'ceo') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->join('users', 'categories_credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('categories_credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->orderBy('categories_credit.created_at', 'DESC')
                ->paginate(10);
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->where('categories_credit.user_id', $user->id)
                ->orderBy('categories_credit.created_at', 'DESC')
                ->paginate(10);
        } else {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->where('categories_credit.user_id', $user->id)
                ->orderBy('categories_credit.created_at', 'DESC')
                ->paginate(10);
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        return view('account.categories_credit.index', compact('categories', 'maintenances'));
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->orderBy('created_at', 'DESC')
        //    ->paginate(10);
        //return view('account.categories_credit.index', compact('categories'));
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q');

        if ($user->level == 'manager' || $user->level == 'ceo') {
            $categories = CategoriesCredit::where('user_id', $user->id)
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
                $categories = CategoriesCredit::where('user_id', $manager->id)
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
            $categories = CategoriesCredit::where('user_id', $user->id)
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

        return view('account.categories_credit.index', compact('categories', 'maintenances'));
    }

    public function create()
    {
        return view('account.categories_credit.create');
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

        // untuk membuat kode kategori otomatis
        // $lastCode = CategoriesCredit::max('kode');

        // $prefix = '';

        // if (Auth::user()->level == "manager") {
        //     $prefix = 'CM';
        // } elseif (Auth::user()->level == "admin") {
        //     $prefix = 'CA';
        // } elseif (Auth::user()->level == "karyawan") {
        //     $prefix = 'CK';
        // } else {
        //     $prefix = 'CU';
        // }

        // $existingCategoriesCount = CategoriesCredit::where('kode', 'like', $prefix . '%')->count();

        // if (empty($lastCode) || $existingCategoriesCount == 0) {
        //     $newCode = $prefix . '002';
        // } else {
        //     $lastCategory = CategoriesCredit::where('kode', 'like', $prefix . '%')->orderBy('kode', 'desc')->first();
        //     $lastCode = $lastCategory->kode;
        //     $newCode = sprintf($prefix . '%03d', intval(substr($lastCode, 2)) + 1);
        // }

        $name = strtoupper($request->input('name'));
        $kode = strtoupper($request->input('kode'));

        //Eloquent simpan data
        $save = CategoriesCredit::create([
            'user_id'       => Auth::user()->id,
            'kode'           => $kode,
            'name'          => $name
        ]);
        //cek apakah data berhasil disimpan
        if ($save) {
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_credit.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('account.categories_credit.index')->with(['error' => 'Data Gagal Disimpan!']);
        }
    }


    public function edit(Request $request, CategoriesCredit $categoriesCredit)
    {
        return view('account.categories_credit.edit', compact('categoriesCredit'));
    }


    public function update(Request $request, CategoriesCredit $categoriesCredit)
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
        $update = CategoriesCredit::whereId($categoriesCredit->id)->update([
            'user_id'       => Auth::user()->id,
            'name'          => $name,
            'kode'          => $kode
        ]);
        //cek apakah data berhasil disimpan
        if ($update) {
            //redirect dengan pesan sukses
            return redirect()->route('account.categories_credit.index')->with(['success' => 'Data Berhasil Diupdate!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('account.categories_credit.index')->with(['error' => 'Data Gagal Diupdate!']);
        }
    }

    public function destroy($id)
    {
        $delete = CategoriesCredit::find($id)->delete($id);

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
