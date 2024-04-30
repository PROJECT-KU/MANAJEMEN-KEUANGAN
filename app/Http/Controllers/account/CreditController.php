<?php

namespace App\Http\Controllers\account;

use App\CategoriesCredit;
use App\Credit;
use App\User;
use App\Gaji;
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

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    public function index()
    {

        $user = Auth::user();

        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
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

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $totalGaji = 0;
        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->join('users', 'gaji.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first()->total_gaji ?? 0;

            $gaji = DB::table('gaji')
                ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status',  'gaji.gambar', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
                ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(20);
        } else if ($user->level == 'karyawan' || $user->level == 'trainer') {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->where('user_id', $user->id)
                ->where('status', 'terbayar')
                ->first()->total_gaji ?? 0;

            $gaji = DB::table('gaji')
                ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status',  'gaji.gambar', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
                ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
                ->where('gaji.user_id', $user->id)
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(10);
        } else {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->where('user_id', $user->id)
                ->first()->total_gaji ?? 0;

            $gaji = Gaji::select('gaji.*', 'users.name as full_name')
                ->join('users', 'gaji.user_id', '=', 'users.id')
                ->where('gaji.user_id', $user->id)
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(10);
        }

        return view('account.credit.index', compact('credit', 'maintenances', 'totalGaji', 'gaji'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        if ($user->level == 'staff' || $user->level == 'ceo') {
            $manager = User::where('level', 'manager')
                ->where('company', $user->company)
                ->first();

            if ($manager) {
                $credit = DB::table('credit')
                    ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'credit.gambar', 'categories_credit.id as id_category', 'categories_credit.name')
                    ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                    ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                    ->where('users.company', $user->company)
                    ->where('users.level', 'manager')
                    ->where(function ($query) use ($search) {
                        $query->where('credit.description', 'LIKE', '%' . $search . '%')
                            ->orWhere('categories_credit.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('credit.nominal', 'LIKE', '%' . $search . '%')
                            ->orWhere('credit.credit_date', 'LIKE', '%' . $search . '%');
                    })
                    ->orderBy('credit.created_at', 'DESC')
                    ->paginate(10);
            } else {
                // If there is no matching manager, return an error
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }
        } else {
            $credit = DB::table('credit')
                ->select('credit.id', 'credit.category_id', 'credit.user_id', 'credit.nominal', 'credit.credit_date', 'credit.description', 'credit.gambar', 'categories_credit.id as id_category', 'categories_credit.name')
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

        if ($credit->isEmpty()) {
            // If there are no results, return an error
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        foreach ($credit as $item) {
            $item->credit_date = date('d-m-Y H:i', strtotime($item->credit_date));
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.credit.index', compact('credit', 'maintenances'));
    }

    public function create()
    {

        $user = Auth::user();

        // Get all categories for users who are managers and staff in the same company
        if ($user->level == 'staff' || $user->level == 'manager') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->join('users', 'categories_credit.user_id', '=', 'users.id')
                ->whereIn('users.level', ['staff', 'manager'])
                ->orderBy('categories_credit.created_at', 'DESC')
                ->get();
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->where('categories_credit.user_id', $user->id)
                ->orderBy('categories_credit.created_at', 'DESC')
                ->get();
        } else {
            $categories = [];
        }
        return view('account.credit.create', compact('categories'));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $id_transaksi = $this->generateRandomId(5);

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
                'id_transaksi' => $id_transaksi,
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
        if ($user->level == 'staff' || $user->level == 'manager') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->join('users', 'categories_credit.user_id', '=', 'users.id')
                ->whereIn('users.level', ['staff', 'manager'])
                ->orderBy('categories_credit.created_at', 'DESC')
                ->get();
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_credit')
                ->select('categories_credit.id', 'categories_credit.kode', 'categories_credit.name')
                ->where('categories_credit.user_id', $user->id)
                ->orderBy('categories_credit.created_at', 'DESC')
                ->get();
        } else {
            $categories = [];
        }
        //$categories = CategoriesCredit::where('user_id', Auth::user()->id)
        //    ->get();
        return  view('account.credit.edit', compact('credit', 'categories'));
    }


    public function update(Request $request, Credit $credit)
    {
        $user = Auth::user();

        $this->validate(
            $request,
            [
                'nominal'       => 'required',
                'credit_date'    => 'required',
                'category_id'   => 'required',
                'description'   => 'required'
            ],
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
