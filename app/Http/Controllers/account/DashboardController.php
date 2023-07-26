<?php

namespace App\Http\Controllers\account;

use App\Debit;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $user = Auth::user();
        if ($user->level == 'manager' || $user->level == 'staff') {
            $uang_masuk_bulan_ini  = DB::table('debit')
            ->selectRaw('sum(nominal) as nominal')
            ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_keluar_bulan_ini = DB::table('credit')
            ->selectRaw('sum(nominal) as nominal')
            ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)

                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_masuk_bulan_lalu  = DB::table('debit')
            ->selectRaw('sum(nominal) as nominal')
            ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->subMonths())

                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_keluar_bulan_lalu = DB::table('credit')
            ->selectRaw('sum(nominal) as nominal')
            ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->subMonths())

                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_masuk_selama_ini  = DB::table('debit')
            ->selectRaw('sum(nominal) as nominal')

            ->leftJoin('users', 'debit.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
                ->first();

            $uang_keluar_selama_ini = DB::table('credit')
            ->selectRaw('sum(nominal) as nominal')

            ->leftJoin('users', 'credit.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
                ->first();

            $uang_keluar_hari_ini = DB::table('credit')
            ->selectRaw('sum(nominal) as nominal')
            ->whereDate('credit_date', Carbon::today())

                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_masuk_hari_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('debit_date', Carbon::today())

                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_masuk_tahun_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)

                ->leftJoin('users', 'debit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();

            $uang_keluar_tahun_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)

                ->leftJoin('users', 'credit.user_id', '=', 'users.id')
                ->where('users.company', $user->company)
                ->first();
        } else {
            $uang_masuk_bulan_ini  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->month)
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_keluar_bulan_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->month)
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_masuk_bulan_lalu  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->whereMonth('debit_date', Carbon::now()->subMonths())
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_keluar_bulan_lalu = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->whereMonth('credit_date', Carbon::now()->subMonths())
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_masuk_selama_ini  = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_keluar_selama_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_keluar_hari_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('credit_date', Carbon::today())
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_masuk_hari_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereDate('debit_date', Carbon::today())
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_masuk_tahun_ini = DB::table('debit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('debit_date', Carbon::now()->year)
                ->where('user_id', Auth::user()->id)
                ->first();

            $uang_keluar_tahun_ini = DB::table('credit')
                ->selectRaw('sum(nominal) as nominal')
                ->whereYear('credit_date', Carbon::now()->year)
                ->where('user_id', Auth::user()->id)
                ->first();
        }





        //saldo bulan ini
        $saldo_bulan_ini = $uang_masuk_bulan_ini->nominal - $uang_keluar_bulan_ini->nominal;

        //saldo bulan lalu
        $saldo_bulan_lalu     = $uang_masuk_bulan_lalu->nominal - $uang_keluar_bulan_lalu->nominal;

        //saldo selama ini
        $saldo_selama_ini = $uang_masuk_selama_ini->nominal - $uang_keluar_selama_ini->nominal;

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


        /**
         * chart
         */

        return view('account.dashboard.index', compact('saldo_selama_ini', 'saldo_bulan_ini', 'saldo_bulan_lalu', 'pengeluaran_bulan_ini', 'pengeluaran_hari_ini', 'Pemasukan_hari_ini', 'pemasukan_bulan_ini', 'pemasukan_tahun_ini', 'pengeluaran_tahun_ini'));
    }

}
