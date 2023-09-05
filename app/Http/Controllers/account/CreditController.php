<?php

namespace App\Http\Controllers\account;

use App\CategoriesCredit;
use App\Credit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * CreditController constructor.
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
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description',  'credit.gambar', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->orderBy('credit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'credit.gambar', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('credit.user_id', $user->id)
                ->orderBy('credit.created_at', 'DESC')
                ->paginate(10);
        }

        // Mengubah format tanggal menjadi "dd-mm-yyyy h:i"
        foreach ($credit as $item) {
            $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
        }

        return view('account.credit.index', compact('credit'));
        //$credit = DB::table('credit')
        //    ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        //    ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        //    ->where('credit.user_id', Auth::user()->id)
        //    ->orderBy('credit.created_at', 'DESC')
        //    ->paginate(10);

        //foreach ($credit as $item) {
        //    $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
        //}

        //return view('account.credit.index', compact('credit'));
    }


    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        if ($user->level == 'manager' || $user->level == 'staff') {
            // Jika user adalah 'manager' atau 'staff', ambil semua data transaksi yang memiliki perusahaan yang sama dengan user
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->where(function ($query) use ($search) {
                    $query->where('credit.description', 'LIKE', '%' . $search . '%')
                        ->orWhere(
                            'categories_credit.name',
                            'LIKE',
                            '%' . $search . '%'
                        )
                        ->orWhere('credit.nominal', 'LIKE', '%' . $search . '%')
                        ->orWhere('credit.credit_date', 'LIKE', '%' . $search . '%');
                })
                ->orderBy('credit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->where('credit.user_id', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where(
                        'credit.description',
                        'LIKE',
                        '%' . $search . '%'
                    )
                        ->orWhere('categories_credit.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('credit.nominal', 'LIKE', '%' . $search . '%')
                        ->orWhere('credit.credit_date', 'LIKE', '%' . $search . '%');
                })
                ->orderBy('credit.created_at', 'DESC')
                ->paginate(10);
        }

        foreach ($credit as $item) {
            $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
        }
        return view('account.credit.index', compact('credit'));
        //$search = $request->get('q');
        //$credit = DB::table('credit')
        //    ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'categories_credit.id as id_category', 'categories_credit.name')
        //    ->join('categories_credit', 'credit.category_id', '=', 'categories_credit.id', 'LEFT')
        //    ->where('credit.user_id', Auth::user()->id)
        //    ->where(function ($query) use ($search) {
        //        $query->where('credit.description', 'LIKE', '%' . $search . '%')
        //            ->orWhere('categories_credit.name', 'LIKE', '%' . $search . '%')
        //            ->orWhere('credit.nominal', 'LIKE', '%' . $search . '%')
        //            ->orWhere('credit.credit_date', 'LIKE', '%' . $search . '%');
        //    })
        //    ->orderBy('credit.created_at', 'DESC')
        //    ->paginate(10);
        //foreach ($credit as $item) {
        //    $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
        //}
        //return view('account.credit.index', compact('credit'));
    }


    public function create()
    {

        $user = Auth::user();

        // Get all categories for users who are managers and staff in the same company
        if ($user->level == 'manager' || $user->level == 'staff') {
            $categories = CategoriesCredit::join('users', 'categories_credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->get(['categories_credit.*']);

            return view('account.credit.create', compact('categories'));
        } else {
            $categories = CategoriesCredit::where('user_id', Auth::user()->id)
                ->get();
            return view('account.credit.create', compact('categories'));
        }
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->get();
        //return view('account.credit.create', compact('categories'));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->level == 'manager' || $user->level == 'staff') {
            $this->validate(
                $request,
                [
                    'nominal'       => 'required',
                    'credit_date'    => 'required',
                    'category_id'   => 'required',
                    'description'   => 'required'
                ],
                //set message validation
                [
                    'nominal.required' => 'Masukkan Nominal Debit / Uang Keluar!',
                    'credit_date.required' => 'Silahkan Pilih Tanggal!',
                    'category_id.required' => 'Silahkan Pilih Kategori!',
                    'description.required' => 'Masukkan Keterangan!',
                ]
            );

            //save image to path
            $imagePath = null;

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
            }
            //end


            //Eloquent simpan data
            $save = Credit::create([
                'user_id'       => Auth::user()->id,
                'credit_date'   => $request->input('credit_date'),
                'category_id'   => $request->input('category_id'),
                'nominal'       => str_replace(",", "", $request->input('nominal')),
                'description'   => $request->input('description'),
                'gambar' => $imagePath, // Store the image path
            ]);
            //cek apakah data berhasil disimpan
            if ($save) {
                //redirect dengan pesan sukses
                return redirect()->route('account.credit.index')->with(['success' => 'Data Berhasil Disimpan!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('account.credit.index')->with(['error' => 'Data Gagal Disimpan!']);
            }
        } else {
            $this->validate(
                $request,
                [
                    'nominal'       => 'required',
                    'credit_date'    => 'required',
                    'category_id'   => 'required',
                    'description'   => 'required'
                ],
                //set message validation
                [
                    'nominal.required' => 'Masukkan Nominal Debit / Uang Keluar!',
                    'credit_date.required' => 'Silahkan Pilih Tanggal!',
                    'category_id.required' => 'Silahkan Pilih Kategori!',
                    'description.required' => 'Masukkan Keterangan!',
                ]
            );
            //save image to path
            $imagePath = null;

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
            }
            //end

            //Eloquent simpan data
            $save = Credit::create([
                'user_id'       => Auth::user()->id,
                'credit_date'   => $request->input('credit_date'),
                'category_id'   => $request->input('category_id'),
                'nominal'       => str_replace(",", "", $request->input('nominal')),
                'description'   => $request->input('description'),
                'gambar' => $imagePath, // Store the image path
            ]);
            //cek apakah data berhasil disimpan
            if ($save) {
                //redirect dengan pesan sukses
                return redirect()->route('account.credit.index')->with(['success' => 'Data Berhasil Disimpan!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('account.credit.index')->with(['error' => 'Data Gagal Disimpan!']);
            }
        }
    }


    public function edit(Request $request, Credit $credit)
    {

        $user = Auth::user();

        // Get all categories for users who are managers and staff in the same company
        if ($user->level == 'manager' || $user->level == 'staff') {
            $categories = CategoriesCredit::join('users', 'categories_credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->get(['categories_credit.*']);

            return  view('account.credit.edit', compact('credit', 'categories'));
        } else {
            $categories = CategoriesCredit::where('user_id', Auth::user()->id)
                ->get();
            return  view('account.credit.edit', compact('credit', 'categories'));
        }
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->get();
        //return  view('account.credit.edit', compact('credit', 'categories'));
    }


    public function update(Request $request, Credit $credit)
    {
        $user = Auth::user();
        if ($user->level == 'manager' || $user->level == 'staff') {
            // Cek apakah user memiliki hak akses untuk mengedit data transaksi
            if ($credit->user_id == $user->id) {
                $this->validate(
                    $request,
                    [
                        'nominal'       => 'required',
                        'credit_date'    => 'required',
                        'category_id'   => 'required',
                        'description'   => 'required'
                    ],
                    //set message validation
                    [
                        'nominal.required' => 'Masukkan Nominal Debit / Uang Keluar!',
                        'credit_date.required' => 'Silahkan Pilih Tanggal!',
                        'category_id.required' => 'Silahkan Pilih Kategori!',
                        'description.required' => 'Masukkan Keterangan!',
                    ]
                );

                //save image to path
                $imagePath = null;

                if ($request->hasFile('gambar')) {
                    $image = $request->file('gambar');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                    $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
                } else {
                    $imagePath = $credit->gambar;
                }
                //end

                //Eloquent simpan data
                $update = Credit::whereId($credit->id)->update([
                    'user_id'       => Auth::user()->id,
                    'category_id'   => $request->input('category_id'),
                    'credit_date'    => $request->input('credit_date'),
                    'nominal'       => str_replace(",", "", $request->input('nominal')),
                    'description'   => $request->input('description'),
                    'gambar' => $imagePath, // Store the image path
                ]);
                //cek apakah data berhasil disimpan
                if ($update) {
                    //redirect dengan pesan sukses
                    return redirect()->route('account.credit.index')->with(['success' => 'Data Berhasil Diupdate!']);
                } else {
                    //redirect dengan pesan error
                    return redirect()->route('account.credit.index')->with(['error' => 'Data Gagal Diupdate!']);
                }
            }
        } else {
            $this->validate(
                $request,
                [
                    'nominal'       => 'required',
                    'credit_date'    => 'required',
                    'category_id'   => 'required',
                    'description'   => 'required'
                ],
                //set message validation
                [
                    'nominal.required' => 'Masukkan Nominal Debit / Uang Keluar!',
                    'credit_date.required' => 'Silahkan Pilih Tanggal!',
                    'category_id.required' => 'Silahkan Pilih Kategori!',
                    'description.required' => 'Masukkan Keterangan!',
                ]
            );

            //save image to path
            $imagePath = null;

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
            } else {
                $imagePath = $credit->gambar;
            }
            //end

            //Eloquent simpan data
            $update = Credit::whereId($credit->id)->update([
                'user_id'       => Auth::user()->id,
                'category_id'   => $request->input('category_id'),
                'credit_date'    => $request->input('credit_date'),
                'nominal'       => str_replace(",", "", $request->input('nominal')),
                'description'   => $request->input('description'),
                'gambar' => $imagePath, // Store the image path
            ]);
            //cek apakah data berhasil disimpan
            if ($update) {
                //redirect dengan pesan sukses
                return redirect()->route('account.credit.index')->with(['success' => 'Data Berhasil Diupdate!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('account.credit.index')->with(['error' => 'Data Gagal Diupdate!']);
            }
        }
    }


    public function destroy($id)
    {
        $delete = Credit::find($id)->delete($id);

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
