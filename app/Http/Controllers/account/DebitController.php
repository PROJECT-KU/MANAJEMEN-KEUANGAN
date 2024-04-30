<?php

namespace App\Http\Controllers\account;

use App\CategoriesDebit;
use App\Debit;
use App\User;
use App\Gaji;
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
            $debit = DB::table('debit')
                ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.gambar', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
                ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->orderBy('debit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
            $debit = DB::table('debit')
                ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.gambar', 'debit.description', 'categories_debit.id as id_category', 'categories_debit.name')
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

        return view('account.debit.index', compact('debit', 'maintenances', 'totalGaji', 'gaji'));
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
                $debit = DB::table('debit')
                    ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'debit.gambar', 'categories_debit.id as id_category', 'categories_debit.name')
                    ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
                    ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                    ->where('users.company', $user->company)
                    ->where('users.level', 'manager')
                    ->where(function ($query) use ($search) {
                        $query->where('debit.description', 'LIKE', '%' . $search . '%')
                            ->orWhere('categories_debit.name', 'LIKE', '%' . $search . '%')
                            ->orWhere(DB::raw("CAST(REPLACE(debit.nominal, 'Rp', '') AS DECIMAL(10, 2))"), '=', str_replace(['Rp', '.', ','], '', $search))
                            ->orWhere(DB::raw("DATE_FORMAT(debit.debit_date, '%Y-%m-%d')"), '=', date('Y-m-d', strtotime($search)));
                    })
                    ->orderBy('debit.created_at', 'DESC')
                    ->paginate(10);
            } else {
                // If there is no matching manager, return an error
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }
        } else {
            $debit = DB::table('debit')
                ->select('debit.id', 'debit.category_id', 'debit.user_id', 'debit.nominal', 'debit.debit_date', 'debit.description', 'debit.gambar', 'categories_debit.id as id_category', 'categories_debit.name')
                ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
                ->where('debit.user_id', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where(
                        'debit.description',
                        'LIKE',
                        '%' . $search . '%'
                    )
                        ->orWhere('categories_debit.name', 'LIKE', '%' . $search . '%')
                        ->orWhere(DB::raw("CAST(REPLACE(debit.nominal, 'Rp', '') AS DECIMAL(10, 2))"), '=', str_replace(['Rp', '.', ','], '', $search))
                        ->orWhere(DB::raw("DATE_FORMAT(debit.debit_date, '%Y-%m-%d')"), '=', date('Y-m-d', strtotime($search)));
                })
                ->orderBy('debit.created_at', 'DESC')
                ->paginate(10);
        }

        if ($debit->isEmpty()) {
            // If there are no results, return an error
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        foreach ($debit as $item) {
            $item->debit_date = date('d-m-Y H:i', strtotime($item->debit_date));
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('account.debit.index', compact('debit', 'maintenances'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->level == 'staff' || $user->level == 'manager') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->join('users', 'categories_debit.user_id', '=', 'users.id')
                ->whereIn('users.level', ['staff', 'manager'])
                ->orderBy('categories_debit.created_at', 'DESC')
                ->get();
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->where('categories_debit.user_id', $user->id)
                ->orderBy('categories_debit.created_at', 'DESC')
                ->get();
        } else {
            $categories = [];
        }
        return view('account.debit.create', compact('categories'));
    }
    public function store(Request $request)
    {
        // Pastikan hanya user dengan role 'manager' atau 'staff' yang bisa melakukan create
        $user = Auth::user();
        $id_transaksi = $this->generateRandomId(5);

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

            //save image to path
            $imagePath = null;

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
            }
            //end

            // Buat data transaksi baru
            $save = Debit::create([
                'user_id'       => Auth::user()->id,
                'id_transaksi' => $id_transaksi,
                'debit_date'   => $request->input('debit_date'),
                'category_id'   => $request->input('category_id'),
                'nominal'       => str_replace(",", "", $request->input('nominal')),
                'description'   => $request->input('description'),
                'gambar' => $imagePath, // Store the image path
            ]);

            // Redirect dengan pesan sukses
            if ($save) {
                //redirect dengan pesan sukses
                return redirect()->route('account.debit.index')->with(['success' => 'Data Berhasil Disimpan!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('account.debit.index')->with(['error' => 'Data Gagal Disimpan!']);
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

            //save image to path
            $imagePath = null;

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
                $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
            }
            //end

            // Buat data transaksi baru
            $save = Debit::create([
                'user_id'       => Auth::user()->id,
                'debit_date'   => $request->input('debit_date'),
                'category_id'   => $request->input('category_id'),
                'nominal'       => str_replace(",", "", $request->input('nominal')),
                'description'   => $request->input('description'),
                'gambar' => $imagePath, // Store the image path
            ]);

            // Redirect dengan pesan sukses
            if ($save) {
                //redirect dengan pesan sukses
                return redirect()->route('account.debit.index')->with(['success' => 'Data Berhasil Disimpan!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('account.debit.index')->with(['error' => 'Data Gagal Disimpan!']);
            }
        }
    }


    public function edit(Request $request, Debit $debit)
    {
        $user = Auth::user();

        if ($user->level == 'staff' || $user->level == 'manager') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->join('users', 'categories_debit.user_id', '=', 'users.id')
                ->whereIn('users.level', ['staff', 'manager'])
                ->orderBy('categories_debit.created_at', 'DESC')
                ->get();
        } elseif ($user->level == 'karyawan' || $user->level == 'trainer') {
            $categories = DB::table('categories_debit')
                ->select('categories_debit.id', 'categories_debit.kode', 'categories_debit.name')
                ->where('categories_debit.user_id', $user->id)
                ->orderBy('categories_debit.created_at', 'DESC')
                ->get();
        } else {
            $categories = [];
        }
        return  view('account.debit.edit', compact('debit', 'categories'));
    }


    public function update(Request $request, Debit $debit)
    {
        $user = Auth::user();

        // Validasi data yang diubah
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

        // Simpan gambar ke path
        $imagePath = null;

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $imageName; // Sesuaikan dengan path yang telah didefinisikan di konfigurasi
            $image->move(public_path('images'), $imageName); // Pindahkan gambar ke direktori public/images
        } else {
            $imagePath = $debit->gambar;
        }
        // Akhir

        // Perbarui data transaksi
        $debit->update([
            'debit_date'   => $request->input('debit_date'),
            'category_id'  => $request->input('category_id'),
            'nominal'      => str_replace(",", "", $request->input('nominal')),
            'description'  => $request->input('description'),
            'gambar'       => $imagePath, // Simpan path gambar
        ]);

        // Redirect dengan pesan sukses
        if ($debit) {
            //redirect dengan pesan sukses
            return redirect()->route('account.debit.index')->with(['success' => 'Data Berhasil Diupdate!']);
        } else {
            //redirect dengan pesan error
            return redirect()->route('account.debit.index')->with(['error' => 'Data Gagal Diupdate!']);
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
