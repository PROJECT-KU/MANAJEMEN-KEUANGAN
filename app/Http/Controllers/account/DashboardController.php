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
    /**
     * Dasbor.
     *
     * Seluruh perhitungan pindah ke komponen Livewire App\Livewire\Akun\Dasbor
     * supaya datanya dihitung saat dipakai saja dan tampilannya satu untuk
     * semua perangkat.
     */
    public function index(Request $request)
    {
        return view('account.dashboard.index');
    }
}
