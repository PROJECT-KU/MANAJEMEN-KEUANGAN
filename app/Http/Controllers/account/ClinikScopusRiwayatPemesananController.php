<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Clinikscopus;
use App\ClinikScopusPemesanan;
use App\ClinikScopusPromo;
use App\ClinikScopusPromoSesi;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClinikScopusRiwayatPemesananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // <!--================== MENAMPILKAN DATA ==================-->
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ClinikScopusPemesanan::with(['customer', 'trainer']);

        // ===============================
        // USER - PERORANGAN
        // ===============================
        if ($user->level === 'user' && $user->jenis === 'perorangan') {
            $query->where('customer_id', $user->id);
        }

        // ===============================
        // MANAGER
        // ===============================
        elseif ($user->level === 'manager') {
            // tampilkan semua
            // (jika nanti mau filter company, bisa pakai whereHas)
        }

        // ===============================
        // KARYAWAN
        // ===============================
        elseif ($user->level === 'karyawan') {
            $query->where('trainer_id', $user->id)
                ->whereHas('trainer', function ($q) use ($user) {
                    $q->where('company', $user->company);
                });
        }

        $datas = $query->latest()->get();

        return view(
            'account.clinik_scopus_riwayat_pemesanan.index',
            compact('datas')
        );
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE DATA ==================-->
    public function detail($id)
    {
        $user = Auth::user();
        $datas = ClinikScopusPemesanan::findOrFail($id);

        return view(
            'account.clinik_scopus_riwayat_pemesanan.detail',
            compact('datas')
        );
    }
    // <!--================== END ==================-->
}
