<?php

namespace App\Http\Controllers\account;

use App\Debit;
use App\User;
use App\Presensi;
use App\Gaji;
use App\Artikel;
use App\Todolist;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\CategoriesDebit;

class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $categories = [];

        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
            $uang_masuk_bulan_ini  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_keluar_bulan_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_masuk_bulan_lalu  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->subMonths())
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_keluar_bulan_lalu = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->subMonths())
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_masuk_selama_ini  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_keluar_selama_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_keluar_hari_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('credit_date', Carbon::today())
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_masuk_hari_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('debit_date', Carbon::today())
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_masuk_tahun_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();

            $uang_keluar_tahun_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->first();
        } else {
            $uang_masuk_bulan_ini  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->where('debit.user_id', $user->id)
                ->first();

            $uang_keluar_bulan_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)
                ->where('credit.user_id', $user->id)
                ->first();

            $uang_masuk_bulan_lalu = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->subMonths())
                ->where('debit.user_id', $user->id)
                ->first();

            $uang_keluar_bulan_lalu = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->subMonths())
                ->where('credit.user_id', $user->id)
                ->first();

            $uang_masuk_selama_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->where('debit.user_id', $user->id)
                ->first();

            $uang_keluar_selama_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->where('credit.user_id', $user->id)
                ->first();

            $uang_keluar_hari_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('credit_date', Carbon::today())
                ->where('credit.user_id', $user->id)
                ->first();

            $uang_masuk_hari_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('debit_date', Carbon::today())
                ->where('debit.user_id', $user->id)
                ->first();

            $uang_masuk_tahun_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->where('debit.user_id', $user->id)
                ->first();

            $uang_masuk_tahun_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', '=', now()->year) // Filter tahun ini
                ->whereMonth('debit_date', '=', 5) // Filter bulan Mei
                ->where('debit.user_id', $user->id)
                ->first();


            $uang_keluar_tahun_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->where('credit.user_id', $user->id)
                ->first();
        }

        //statistik pemasukan perkategori
        if (
            $user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo'
        ) {
            $debit = DB::table('debit')
                ->select('categories_debit.name', DB::raw('SUM(debit.nominal) as total_nominal'))
                ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('debit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->groupBy('categories_debit.name')
                ->orderBy('debit.created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
            $debit = DB::table('debit')
                ->select('categories_debit.name', DB::raw('SUM(debit.nominal) as total_nominal'))
                ->leftJoin('categories_debit', 'debit.category_id', '=', 'categories_debit.id')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->where('debit.user_id', $user->id)
                ->groupBy('categories_debit.name')
                ->orderBy('debit.created_at', 'DESC')
                ->paginate(10);
        }
        //end

        //statistik pengeluaran perkategori
        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {
            // Jika user adalah 'manager' atau 'staff', ambil semua data transaksi yang memiliki perusahaan yang sama dengan user
            $credit = DB::table('credit')
                ->select('categories_credit.name', DB::raw('SUM(credit.nominal) as total_nominal'))
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)
                ->where(function ($query) use ($user) {
                    $query->where('users.company', $user->company)
                        ->orWhere('credit.user_id', $user->id);
                })
                ->where(function ($query) {
                    $query->where('users.level', 'manager')
                        ->orWhere('users.level', 'staff');
                })
                ->orderBy('credit.created_at', 'DESC')
                ->groupBy('categories_credit.name')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager' atau 'staff', ambil hanya data transaksi miliknya sendiri
            $credit = DB::table('credit')
                ->select('categories_credit.name', DB::raw('SUM(credit.nominal) as total_nominal'))
                ->leftJoin('categories_credit', 'credit.category_id', '=', 'categories_credit.id')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)
                ->where('credit.user_id', $user->id)
                ->orderBy('credit.created_at', 'DESC')
                ->groupBy('categories_credit.name')
                ->paginate(10);
        }
        //end

        // user baru
        if ($user->level == 'manager') {
            // Jika user adalah 'manager', ambil semua data pengguna staff yang memiliki perusahaan yang sama dengan user
            $users = DB::table('users')
                ->where('company', $user->company)
                ->whereIn('level', ['staff', 'karyawan', 'trainer'])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        } else {
            // Jika user bukan 'manager', ambil hanya data pengguna itu sendiri
            $users = DB::table('users')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }
        // end

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        //saldo bulan ini
        $saldo_bulan_ini = $uang_masuk_bulan_ini->nominal - $uang_keluar_bulan_ini->nominal;

        //saldo bulan lalu
        $saldo_bulan_lalu     = $uang_masuk_bulan_lalu->nominal - $uang_keluar_bulan_lalu->nominal;

        //saldo selama ini
        $saldo_selama_ini = $uang_masuk_selama_ini->nominal - $uang_keluar_selama_ini->nominal;

        //total pemasukan
        $total_pemasukan = $uang_masuk_selama_ini->nominal;

        //total pengeluaran
        $total_pengeluaran = $uang_keluar_selama_ini->nominal;

        //pengeluaran bulan ini
        $pengeluaran_bulan_ini = $uang_keluar_bulan_ini->nominal;

        //pengeluaran hari ini
        $pengeluaran_hari_ini = $uang_keluar_hari_ini->nominal;

        //uang masuk hari ini
        $Pemasukan_hari_ini = $uang_masuk_hari_ini->nominal;

        //uang masuk bulan ini
        $pemasukan_bulan_ini = $uang_masuk_bulan_ini->nominal;

        //pemasukan tahun ini
        $pemasukan_tahun_ini = $uang_masuk_tahun_ini->nominal;

        //pengeluaran tahun ini
        $pengeluaran_tahun_ini = $uang_keluar_tahun_ini->nominal;


        // Ambil data karyawan terbaru
        $latestUsers = User::orderBy('created_at', 'desc')->limit(6)->get();

        // <!--================== TOTAL GAJI ==================-->
        $user = Auth::user();
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
        }

        $totalGaji = 0;
        if ($user->level == 'manager' || $user->level == 'staff' || $user->level == 'ceo') {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->join('users', 'gaji.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->where('gaji.status', 'terbayar')
                ->first()->total_gaji ?? 0;

            $gaji = DB::table('gaji')
                ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
                ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(20);
        } else if ($user->level == 'karyawan' || $user->level == 'trainer') {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->where('user_id', $user->id)
                ->where('status', 'terbayar')
                ->first()->total_gaji ?? 0;

            $gaji = DB::table('gaji')
                ->select('gaji.id', 'gaji.id_transaksi', 'gaji.token', 'gaji.gaji_pokok', 'gaji.lembur', 'gaji.bonus', 'gaji.tunjangan', 'gaji.tanggal', 'gaji.pph', 'gaji.total', 'gaji.status', 'users.id as user_id', 'users.full_name as full_name', 'users.nik as nik', 'users.norek as norek', 'users.bank as bank')
                ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
                ->where('gaji.user_id', $user->id)  // Display only the salary data for the logged-in user
                ->whereBetween('gaji.tanggal', [$currentMonth, $nextMonth])
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(10);
        } else {

            $totalGaji = DB::table('gaji')
                ->selectRaw('SUM(total) as total_gaji')
                ->where('user_id', $user->id)
                ->whereBetween('tanggal', [$currentMonth, $nextMonth])
                ->first()->total_gaji ?? 0;

            $gaji = Gaji::select('gaji.*', 'users.name as full_name')
                ->join('users', 'gaji.user_id', '=', 'users.id')
                ->where('gaji.user_id', $user->id)
                ->orderBy('gaji.created_at', 'DESC')
                ->paginate(10);
        }

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $presensiExist = Presensi::where('status', '<>', null)
            ->whereBetween('created_at', [$currentMonth, $nextMonth])
            ->exists();

        //  <!--================== END ==================-->

        // <!--================== DATA ARTIKEL ==================-->
        $artikel = DB::table('artikel')
            ->select('artikel.id', 'artikel.user_id', 'artikel.categories_artikel_id', 'artikel.token', 'artikel.judul', 'artikel.kata_kunci', 'artikel.gambar_depan', 'artikel.gambar_cover', 'artikel.isi', 'artikel.dilihat', 'artikel.status', 'artikel.created_at', 'users.id as user_id', 'users.full_name as full_name', 'users.gambar as gambar', 'categories_artikel.kategori')
            ->leftJoin('users', 'artikel.user_id', '=', 'users.id')
            ->leftJoin('categories_artikel', 'artikel.categories_artikel_id', '=', 'categories_artikel.id')
            ->orderBy('artikel.created_at', 'DESC')
            ->paginate(6);
        // <!--================== END ==================-->

        // <!--================== PERJALANAN DINAS ==================-->
        $Ajukan = DB::table('perjalanan_dinas')
            ->select(
                'perjalanan_dinas.id',
                'perjalanan_dinas.user_id',
                'perjalanan_dinas.token',
                'perjalanan_dinas.id_transaksi',
                'perjalanan_dinas.status',
                'perjalanan_dinas.tempat',
                'perjalanan_dinas.camp',
                'perjalanan_dinas.tanggal_mulai',
                'perjalanan_dinas.tanggal_akhir',
                'perjalanan_dinas.total_uang_masuk',
                'perjalanan_dinas.total_uang_keluar',
                'perjalanan_dinas.sisa_saldo',
                'users.id as user_id',
                'users.full_name as full_name',
                'users.telp as telp'
            )
            ->leftJoin('users', 'perjalanan_dinas.user_id', '=', 'users.id')
            ->orderBy('perjalanan_dinas.created_at', 'DESC');

        if ($user->level == 'manager' || $user->level == 'ceo') {
            $Ajukan->where('perjalanan_dinas.status', 'ajukan')
                ->where('users.company', $user->company);
        } else {
            $Ajukan->where(function ($query) use ($user) {
                $query->where('perjalanan_dinas.user_id', $user->id)
                    ->whereIn('perjalanan_dinas.status', ['draft', 'ajukan']);
            });
        }

        $Ajukan = $Ajukan->get();

        $hasAjukan = false;
        if ($user->level == 'manager' && $Ajukan->where('status', 'ajukan')->isNotEmpty()) {
            $hasAjukan = true;
        }
        // <!--================== END ==================-->

        // <!--================== TOTAL KARYAWAN ==================-->
        // Hitung total karyawan
        $totalKaryawan = DB::table('users')
            ->where('company', $user->company)
            ->count();

        // Hitung total karyawan aktif
        $totalKaryawanAktif = DB::table('users')
            ->where('company', $user->company)
            ->where('status', 'active') // Karyawan yang statusnya 'active'
            ->count();

        // Hitung total karyawan nonaktif
        $totalKaryawanNonAktif = DB::table('users')
            ->where('company', $user->company)
            ->where('status', 'nonactive') // Karyawan yang statusnya 'nonactive'
            ->count();
        // <!--================== END ==================-->

        // <!--================== MENAMPILKAN TOTAL GAJI PERBULAN SELAMA 1 TAHUN DENGAN CHART ==================-->
        if ($user->level == 'manager') {
            $currentYear = Carbon::now()->year;

            // Fetch total salary for each month for all users in the same company
            $salaries = DB::table('gaji')
                ->selectRaw('MONTH(gaji.tanggal) as month, SUM(gaji.total) as total_gaji')
                ->leftJoin('users', 'gaji.user_id', '=', 'users.id')
                ->where('users.company', $user->company) // Filter by company for managers
                ->where('gaji.status', 'terbayar')
                ->whereYear('gaji.tanggal', $currentYear)
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get();

            // Initialize salary data array with 0 for all months
            $salaryData = array_fill(1, 12, 0);
            $totalGaji = 0;

            // Map fetched salary data to corresponding month and calculate total
            foreach ($salaries as $salary) {
                $salaryData[$salary->month] = $salary->total_gaji;
                $totalGaji += $salary->total_gaji;
            }
        } else {
            $currentYear = Carbon::now()->year;

            // Fetch total salary for the specific user (non-manager)
            $salaries = DB::table('gaji')
                ->selectRaw('MONTH(gaji.tanggal) as month, SUM(gaji.total) as total_gaji')
                ->where('gaji.user_id', $user->id) // Filter by user's ID for non-managers
                ->where('gaji.status', 'terbayar')
                ->whereYear('gaji.tanggal', $currentYear)
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get();

            // Initialize salary data array with 0 for all months
            $salaryData = array_fill(1, 12, 0);
            $totalGaji = 0;

            // Map fetched salary data to corresponding month and calculate total
            foreach ($salaries as $salary) {
                $salaryData[$salary->month] = $salary->total_gaji;
                $totalGaji += $salary->total_gaji;
            }
        }
        // <!--================== END ==================-->

        return view('account.dashboard.index', compact('salaryData', 'currentYear',  'totalKaryawan', 'totalKaryawanAktif', 'totalKaryawanNonAktif', 'saldo_selama_ini', 'saldo_bulan_ini', 'saldo_bulan_lalu', 'pengeluaran_bulan_ini', 'pengeluaran_hari_ini', 'Pemasukan_hari_ini', 'pemasukan_bulan_ini', 'pemasukan_tahun_ini', 'pengeluaran_tahun_ini', 'total_pemasukan', 'total_pengeluaran', 'debit', 'credit', 'latestUsers', 'users', 'maintenances', 'totalGaji', 'gaji', 'artikel', 'Ajukan', 'hasAjukan'));
    }
}
