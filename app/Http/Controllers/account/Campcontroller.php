<?php

namespace App\Http\Controllers\account;

use App\User;
use App\Camp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Response;
use PDF;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;
use App\Models\Employee;

class CampController extends Controller
{
    /**
     * PenyewaanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generateRandomId($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $id;
    }

    public function index(Request $request)
    {
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

        $camp = DB::table('camp')
            ->select('camp.id', 'camp.id_transaksi', 'camp.title', 'camp.camp_ke', 'camp.uang_masuk', 'camp.lain_lain', 'camp.total_uang_masuk', 'camp.gaji_trainer', 'camp.gaji_team', 'camp.team_cabang', 'camp.booknote', 'camp.grammarly', 'camp.tiket_trainer', 'camp.tiket_team', 'camp.hotel', 'camp.marketing', 'camp.konsumsi_tambahan', 'camp.lainnya', 'camp.total', 'camp.keuntungan', 'camp.tanggal', 'camp.tanggal_akhir', 'camp.status', 'camp.note', 'camp.persentase_keuntungan')
            ->leftJoin('users', 'camp.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
            ->orderBy('camp.created_at', 'DESC')
            ->paginate(10);


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        // <!-- Calculate total gaji -->
        $totalCamp = $camp->sum('total');

        return view('account.camp.index', compact('camp', 'maintenances', 'startDate', 'endDate', 'totalCamp'));
    }

    public function filter(Request $request)
    {
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

        $camp = DB::table('camp')
            ->select('camp.id', 'camp.id_transaksi', 'camp.title', 'camp.camp_ke', 'camp.uang_masuk', 'camp.lain_lain', 'camp.total_uang_masuk', 'camp.gaji_trainer', 'camp.gaji_team', 'camp.team_cabang', 'camp.booknote', 'camp.grammarly', 'camp.tiket_trainer', 'camp.tiket_team', 'camp.hotel', 'camp.marketing', 'camp.konsumsi_tambahan', 'camp.lainnya', 'camp.total', 'camp.keuntungan', 'camp.tanggal', 'camp.tanggal_akhir', 'camp.status', 'camp.note', 'camp.persentase_keuntungan')
            ->leftJoin('users', 'camp.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
            ->whereBetween('camp.tanggal', [$currentMonth, $nextMonth])
            ->orderBy('camp.created_at', 'DESC')
            ->paginate(10);


        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        // <!-- Calculate total gaji -->
        $totalCamp = $camp->sum('total');

        return view('account.camp.index', compact('camp', 'maintenances', 'startDate', 'endDate', 'totalCamp'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $user = Auth::user();

        $camp = DB::table('camp')
            ->select('camp.id', 'camp.id_transaksi', 'camp.title', 'camp.camp_ke', 'camp.uang_masuk', 'camp.lain_lain', 'camp.total_uang_masuk', 'camp.gaji_trainer', 'camp.gaji_team', 'camp.team_cabang', 'camp.booknote', 'camp.grammarly', 'camp.tiket_trainer', 'camp.tiket_team', 'camp.hotel', 'camp.marketing', 'camp.konsumsi_tambahan', 'camp.lainnya', 'camp.total', 'camp.keuntungan', 'camp.tanggal', 'camp.tanggal_akhir', 'camp.status', 'camp.note', 'camp.persentase_keuntungan')
            ->leftJoin('users', 'camp.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
            ->where(function ($query) use ($search) {
                $query->where('camp.title', 'LIKE', '%' . $search . '%')
                    ->orWhere('camp.camp_ke', 'LIKE', '%' . $search . '%')
                    ->orWhere(DB::raw("DATE_FORMAT(camp.tanggal, '%Y-%m-%d')"), '=', date('Y-m-d', strtotime($search)))
                    ->orWhere(DB::raw("DATE_FORMAT(camp.tanggal_akhir, '%Y-%m-%d')"), '=', date('Y-m-d', strtotime($search)));
            })
            ->orderBy('camp.created_at', 'DESC')
            ->paginate(10);
        $camp->appends(['q' => $search]);

        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();

        $totalCamp = $camp->sum('total');

        $startDate = $request->get('start_date'); // Example, replace with your actual start_date input field
        $endDate = $request->get('end_date');

        if ($camp->isEmpty()) {
            return redirect()->route('account.camp.index')->with('error', 'Data Camp tidak ditemukan.');
        }
        return view('account.camp.index', compact('camp', 'maintenances', 'startDate', 'endDate', 'totalCamp'));
    }

    public function create()
    {
        $user = Auth::user();

        $users = User::where('company', $user->company)
            ->select('id', 'full_name', 'nik', 'norek', 'bank', 'telp')
            ->get();
        return view('account.camp.create', compact('users'));
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;

        $id_transaksi = $this->generateRandomId(5);

        $title = $request->input('title');
        $camp_ke = $request->input('camp_ke');

        $uang_masuk = $request->input('uang_masuk');
        $uang_masuk = empty($uang_masuk) ? 0 : str_replace(",", "", $uang_masuk); // Convert to numeric value or set to 0 if empty

        $lain_lain = $request->input('lain_lain');
        $lain_lain = empty($lain_lain) ? 0 : str_replace(",", "", $lain_lain);

        // <!-- gaji trainer -->
        $gaji_trainer = $request->input('gaji_trainer');
        $gaji_trainer = empty($gaji_trainer) ? 0 : str_replace(",", "", $gaji_trainer);

        $gaji_trainer1 = $request->input('gaji_trainer1');
        $gaji_trainer1 = empty($gaji_trainer1) ? 0 : str_replace(",", "", $gaji_trainer1);

        $gaji_trainer2 = $request->input('gaji_trainer2');
        $gaji_trainer2 = empty($gaji_trainer2) ? 0 : str_replace(",", "", $gaji_trainer2);

        $gaji_trainer3 = $request->input('gaji_trainer3');
        $gaji_trainer3 = empty($gaji_trainer3) ? 0 : str_replace(",", "", $gaji_trainer3);

        $gaji_trainer4 = $request->input('gaji_trainer4');
        $gaji_trainer4 = empty($gaji_trainer4) ? 0 : str_replace(",", "", $gaji_trainer4);

        $gaji_trainer5 = $request->input('gaji_trainer5');
        $gaji_trainer5 = empty($gaji_trainer5) ? 0 : str_replace(",", "", $gaji_trainer5);

        $gaji_trainer6 = $request->input('gaji_trainer6');
        $gaji_trainer6 = empty($gaji_trainer6) ? 0 : str_replace(",", "", $gaji_trainer6);
        // <!-- end -->

        // <!-- gaji team -->
        $gaji_team = $request->input('gaji_team');
        $gaji_team = empty($gaji_team) ? 0 : str_replace(",", "", $gaji_team);

        $gaji_team1 = $request->input('gaji_team1');
        $gaji_team1 = empty($gaji_team1) ? 0 : str_replace(",", "", $gaji_team1);

        $gaji_team2 = $request->input('gaji_team2');
        $gaji_team2 = empty($gaji_team2) ? 0 : str_replace(",", "", $gaji_team2);

        $gaji_team3 = $request->input('gaji_team3');
        $gaji_team3 = empty($gaji_team3) ? 0 : str_replace(",", "", $gaji_team3);

        $gaji_team4 = $request->input('gaji_team4');
        $gaji_team4 = empty($gaji_team4) ? 0 : str_replace(",", "", $gaji_team4);

        $gaji_team5 = $request->input('gaji_team5');
        $gaji_team5 = empty($gaji_team5) ? 0 : str_replace(",", "", $gaji_team5);

        $gaji_team6 = $request->input('gaji_team6');
        $gaji_team6 = empty($gaji_team6) ? 0 : str_replace(",", "", $gaji_team6);

        $gaji_team7 = $request->input('gaji_team7');
        $gaji_team7 = empty($gaji_team7) ? 0 : str_replace(",", "", $gaji_team7);

        $gaji_team8 = $request->input('gaji_team8');
        $gaji_team8 = empty($gaji_team8) ? 0 : str_replace(",", "", $gaji_team8);

        $gaji_team9 = $request->input('gaji_team9');
        $gaji_team9 = empty($gaji_team9) ? 0 : str_replace(",", "", $gaji_team9);

        $gaji_team10 = $request->input('gaji_team10');
        $gaji_team10 = empty($gaji_team10) ? 0 : str_replace(",", "", $gaji_team10);
        // <!-- end -->

        $team_cabang = $request->input('team_cabang');
        $team_cabang = empty($team_cabang) ? 0 : str_replace(",", "", $team_cabang);

        $booknote = $request->input('booknote');
        $booknote = empty($booknote) ? 0 : str_replace(",", "", $booknote);

        $grammarly = $request->input('grammarly');
        $grammarly = empty($grammarly) ? 0 : str_replace(",", "", $grammarly);

        // <!-- tiket trainer berangkat -->
        $tiket_trainer = $request->input('tiket_trainer');
        $tiket_trainer = empty($tiket_trainer) ? 0 : str_replace(",", "", $tiket_trainer);
        $tiket_trainer1 = $request->input('tiket_trainer1');
        $tiket_trainer1 = empty($tiket_trainer1) ? 0 : str_replace(",", "", $tiket_trainer1);
        $tiket_trainer2 = $request->input('tiket_trainer2');
        $tiket_trainer2 = empty($tiket_trainer2) ? 0 : str_replace(",", "", $tiket_trainer2);
        $tiket_trainer3 = $request->input('tiket_trainer3');
        $tiket_trainer3 = empty($tiket_trainer3) ? 0 : str_replace(",", "", $tiket_trainer3);
        $tiket_trainer4 = $request->input('tiket_trainer4');
        $tiket_trainer4 = empty($tiket_trainer4) ? 0 : str_replace(",", "", $tiket_trainer4);
        $tiket_trainer5 = $request->input('tiket_trainer5');
        $tiket_trainer5 = empty($tiket_trainer5) ? 0 : str_replace(",", "", $tiket_trainer5);
        $tiket_trainer6 = $request->input('tiket_trainer6');
        $tiket_trainer6 = empty($tiket_trainer6) ? 0 : str_replace(",", "", $tiket_trainer6);
        $tiket_trainer7 = $request->input('tiket_trainer7');
        $tiket_trainer7 = empty($tiket_trainer7) ? 0 : str_replace(",", "", $tiket_trainer7);
        // <!-- end -->

        // <!-- tiket trainer pulang -->
        $tiket_trainer_pulang = $request->input('tiket_trainer_pulang');
        $tiket_trainer_pulang = empty($tiket_trainer_pulang) ? 0 : str_replace(",", "", $tiket_trainer_pulang);
        $tiket_trainer_pulang1 = $request->input('tiket_trainer_pulang1');
        $tiket_trainer_pulang1 = empty($tiket_trainer_pulang1) ? 0 : str_replace(",", "", $tiket_trainer_pulang1);
        $tiket_trainer_pulang2 = $request->input('tiket_trainer_pulang2');
        $tiket_trainer_pulang2 = empty($tiket_trainer_pulang2) ? 0 : str_replace(",", "", $tiket_trainer_pulang2);
        $tiket_trainer_pulang3 = $request->input('tiket_trainer_pulang3');
        $tiket_trainer_pulang3 = empty($tiket_trainer_pulang3) ? 0 : str_replace(",", "", $tiket_trainer_pulang3);
        $tiket_trainer_pulang4 = $request->input('tiket_trainer_pulang4');
        $tiket_trainer_pulang4 = empty($tiket_trainer_pulang4) ? 0 : str_replace(",", "", $tiket_trainer_pulang4);
        $tiket_trainer_pulang5 = $request->input('tiket_trainer_pulang5');
        $tiket_trainer_pulang5 = empty($tiket_trainer_pulang5) ? 0 : str_replace(",", "", $tiket_trainer_pulang5);
        $tiket_trainer_pulang6 = $request->input('tiket_trainer_pulang6');
        $tiket_trainer_pulang6 = empty($tiket_trainer_pulang6) ? 0 : str_replace(",", "", $tiket_trainer_pulang6);
        $tiket_trainer_pulang7 = $request->input('tiket_trainer_pulang7');
        $tiket_trainer_pulang7 = empty($tiket_trainer_pulang7) ? 0 : str_replace(",", "", $tiket_trainer_pulang7);
        // <!-- end -->

        // <!-- tiket team berangkat -->
        $tiket_team = $request->input('tiket_team');
        $tiket_team = empty($tiket_team) ? 0 : str_replace(",", "", $tiket_team);
        $tiket_team1 = $request->input('tiket_team1');
        $tiket_team1 = empty($tiket_team1) ? 0 : str_replace(",", "", $tiket_team1);
        $tiket_team2 = $request->input('tiket_team2');
        $tiket_team2 = empty($tiket_team2) ? 0 : str_replace(",", "", $tiket_team2);
        $tiket_team3 = $request->input('tiket_team3');
        $tiket_team3 = empty($tiket_team3) ? 0 : str_replace(",", "", $tiket_team3);
        $tiket_team4 = $request->input('tiket_team4');
        $tiket_team4 = empty($tiket_team4) ? 0 : str_replace(",", "", $tiket_team4);
        $tiket_team5 = $request->input('tiket_team5');
        $tiket_team5 = empty($tiket_team5) ? 0 : str_replace(",", "", $tiket_team5);
        $tiket_team6 = $request->input('tiket_team6');
        $tiket_team6 = empty($tiket_team6) ? 0 : str_replace(",", "", $tiket_team6);
        $tiket_team7 = $request->input('tiket_team7');
        $tiket_team7 = empty($tiket_team7) ? 0 : str_replace(",", "", $tiket_team7);
        // <!-- end -->

        // <!-- tiket team pulang -->
        $tiket_team_pulang = $request->input('tiket_team_pulang');
        $tiket_team_pulang = empty($tiket_team_pulang) ? 0 : str_replace(",", "", $tiket_team_pulang);
        $tiket_team_pulang1 = $request->input('tiket_team_pulang1');
        $tiket_team_pulang1 = empty($tiket_team_pulang1) ? 0 : str_replace(",", "", $tiket_team_pulang1);
        $tiket_team_pulang2 = $request->input('tiket_team_pulang2');
        $tiket_team_pulang2 = empty($tiket_team_pulang2) ? 0 : str_replace(",", "", $tiket_team_pulang2);
        $tiket_team_pulang3 = $request->input('tiket_team_pulang3');
        $tiket_team_pulang3 = empty($tiket_team_pulang3) ? 0 : str_replace(",", "", $tiket_team_pulang3);
        $tiket_team_pulang4 = $request->input('tiket_team_pulang4');
        $tiket_team_pulang4 = empty($tiket_team_pulang4) ? 0 : str_replace(",", "", $tiket_team_pulang4);
        $tiket_team_pulang5 = $request->input('tiket_team_pulang5');
        $tiket_team_pulang5 = empty($tiket_team_pulang5) ? 0 : str_replace(",", "", $tiket_team_pulang5);
        $tiket_team_pulang6 = $request->input('tiket_team_pulang6');
        $tiket_team_pulang6 = empty($tiket_team_pulang6) ? 0 : str_replace(",", "", $tiket_team_pulang6);
        $tiket_team_pulang7 = $request->input('tiket_team_pulang7');
        $tiket_team_pulang7 = empty($tiket_team_pulang7) ? 0 : str_replace(",", "", $tiket_team_pulang7);
        // <!-- end -->

        $hotel = $request->input('hotel');
        $hotel = empty($hotel) ? 0 : str_replace(",", "", $hotel);

        $konsumsi_tambahan = $request->input('konsumsi_tambahan');
        $konsumsi_tambahan = empty($konsumsi_tambahan) ? 0 : str_replace(",", "", $konsumsi_tambahan);

        $lainnya = $request->input('lainnya');
        $lainnya = empty($lainnya) ? 0 : str_replace(",", "", $lainnya);

        $total = $gaji_trainer + $gaji_trainer1 + $gaji_trainer2 + $gaji_trainer3 + $gaji_trainer4 + $gaji_trainer5 + $gaji_trainer6 +
            $gaji_team + $gaji_team1 + $gaji_team2 + $gaji_team3 + $gaji_team4 + $gaji_team5 + $gaji_team6 + $gaji_team7 + $gaji_team8 + $gaji_team9 + $gaji_team10 +
            $team_cabang + $booknote + $grammarly +
            $tiket_trainer + $tiket_trainer1 + $tiket_trainer2 + $tiket_trainer3 + $tiket_trainer4 + $tiket_trainer5 + $tiket_trainer6 + $tiket_trainer7 +
            $tiket_trainer_pulang + $tiket_trainer_pulang1 + $tiket_trainer_pulang2 + $tiket_trainer_pulang3 + $tiket_trainer_pulang4 + $tiket_trainer_pulang5 + $tiket_trainer_pulang6 + $tiket_trainer_pulang7 +
            $tiket_team + $tiket_team1 + $tiket_team2 + $tiket_team3 + $tiket_team4 + $tiket_team5 + $tiket_team6 + $tiket_team7 +
            $tiket_team_pulang + $tiket_team_pulang1 + $tiket_team_pulang2 + $tiket_team_pulang3 + $tiket_team_pulang4 + $tiket_team_pulang5 + $tiket_team_pulang6 + $tiket_team_pulang7 +
            $hotel + $konsumsi_tambahan + $lainnya;
        $total = empty($total) ? 0 : str_replace(",", "", $total);

        $total_gaji_trainer = $gaji_trainer + $gaji_trainer1 + $gaji_trainer2 + $gaji_trainer3 + $gaji_trainer4 + $gaji_trainer5 + $gaji_trainer6;
        $total_gaji_trainer = empty($total_gaji_trainer) ? 0 : str_replace(",", "", $total_gaji_trainer);

        $total_gaji_team = $gaji_team + $gaji_team1 + $gaji_team2 + $gaji_team3 + $gaji_team4 + $gaji_team5 + $gaji_team6 + $gaji_team7 + $gaji_team8 + $gaji_team9 + $gaji_team10;
        $total_gaji_team = empty($total_gaji_team) ? 0 : str_replace(",", "", $total_gaji_team);

        $total_uang_masuk = $uang_masuk + $lain_lain;
        $total_uang_masuk = empty($total_uang_masuk) ? 0 : str_replace(",", "", $total_uang_masuk);

        $total_tiket_trainer_berangkat = $tiket_trainer + $tiket_trainer1 + $tiket_trainer2 + $tiket_trainer3 + $tiket_trainer4 + $tiket_trainer5 + $tiket_trainer6 + $tiket_trainer7;
        $total_tiket_trainer_berangkat = empty($total_tiket_trainer_berangkat) ? 0 : str_replace(",", "", $total_tiket_trainer_berangkat);

        $total_tiket_trainer_pulang = $tiket_trainer_pulang + $tiket_trainer_pulang1 + $tiket_trainer_pulang2 + $tiket_trainer_pulang3 + $tiket_trainer_pulang4 + $tiket_trainer_pulang5 + $tiket_trainer_pulang6 + $tiket_trainer_pulang7;
        $total_tiket_trainer_pulang = empty($total_tiket_trainer_pulang) ? 0 : str_replace(",", "", $total_tiket_trainer_pulang);

        $total_tiket_team_berangkat = $tiket_team + $tiket_team1 + $tiket_team2 + $tiket_team3 + $tiket_team4 + $tiket_team5 + $tiket_team6 + $tiket_team7;
        $total_tiket_team_berangkat = empty($total_tiket_team_berangkat) ? 0 : str_replace(",", "", $total_tiket_team_berangkat);

        $total_tiket_team_pulang = $tiket_team_pulang + $tiket_team_pulang1 + $tiket_team_pulang2 + $tiket_team_pulang3 + $tiket_team_pulang4 + $tiket_team_pulang5 + $tiket_team_pulang6 + $tiket_team_pulang7;
        $total_tiket_team_pulang = empty($total_tiket_team_pulang) ? 0 : str_replace(",", "", $total_tiket_team_pulang);

        $keuntungan = $total_uang_masuk - $total;
        $keuntungan = empty($keuntungan) ? 0 : str_replace(",", "", $keuntungan);

        $persentase_keuntungan = ($keuntungan / $total_uang_masuk) * 100;

        $marketing = $total_uang_masuk * 0.10; // Ubah persentase ke dalam desimal (0.10 untuk 10%)
        $marketing = empty($marketing) ? 0 : str_replace(",", "", number_format($marketing));

        $save = Camp::create([
            'id_transaksi' => $id_transaksi,
            'user_id' => Auth::user()->id,
            'title' => $title,
            'camp_ke' => $camp_ke,
            'uang_masuk' => $uang_masuk,
            'lain_lain' => $lain_lain,

            // <!-- gaji trainer -->
            'gaji_trainer' => $gaji_trainer,
            'gaji_trainer1' => $gaji_trainer1,
            'gaji_trainer2' => $gaji_trainer2,
            'gaji_trainer3' => $gaji_trainer3,
            'gaji_trainer4' => $gaji_trainer4,
            'gaji_trainer5' => $gaji_trainer5,
            'gaji_trainer6' => $gaji_trainer6,
            'gaji_trainer_nama' => $request->input('gaji_trainer_nama'),
            'gaji_trainer_nama1' => $request->input('gaji_trainer_nama1'),
            'gaji_trainer_nama2' => $request->input('gaji_trainer_nama2'),
            'gaji_trainer_nama3' => $request->input('gaji_trainer_nama3'),
            'gaji_trainer_nama4' => $request->input('gaji_trainer_nama4'),
            'gaji_trainer_nama5' => $request->input('gaji_trainer_nama5'),
            'gaji_trainer_nama6' => $request->input('gaji_trainer_nama6'),
            'total_gaji_trainer' => $total_gaji_trainer,
            // <!-- end -->

            // <!-- gaji team -->
            'gaji_team' => $gaji_team,
            'gaji_team1' => $gaji_team1,
            'gaji_team2' => $gaji_team2,
            'gaji_team3' => $gaji_team3,
            'gaji_team4' => $gaji_team4,
            'gaji_team5' => $gaji_team5,
            'gaji_team6' => $gaji_team6,
            'gaji_team7' => $gaji_team7,
            'gaji_team8' => $gaji_team8,
            'gaji_team9' => $gaji_team9,
            'gaji_team10' => $gaji_team10,
            'gaji_team_nama' => $request->input('gaji_team_nama'),
            'gaji_team_nama1' => $request->input('gaji_team_nama1'),
            'gaji_team_nama2' => $request->input('gaji_team_nama2'),
            'gaji_team_nama3' => $request->input('gaji_team_nama3'),
            'gaji_team_nama4' => $request->input('gaji_team_nama4'),
            'gaji_team_nama5' => $request->input('gaji_team_nama5'),
            'gaji_team_nama6' => $request->input('gaji_team_nama6'),
            'gaji_team_nama7' => $request->input('gaji_team_nama7'),
            'gaji_team_nama8' => $request->input('gaji_team_nama8'),
            'gaji_team_nama9' => $request->input('gaji_team_nama9'),
            'gaji_team_nama10' => $request->input('gaji_team_nama10'),
            'total_gaji_team' => $total_gaji_team,
            // <!-- end -->

            'team_cabang' => $team_cabang,
            'booknote' => $booknote,
            'grammarly' => $grammarly,
            'peserta' => $request->input('peserta'),

            // <!-- tiket trainer berangkat -->
            'tiket_trainer' => $tiket_trainer,
            'tiket_trainer1' => $tiket_trainer1,
            'tiket_trainer2' => $tiket_trainer2,
            'tiket_trainer3' => $tiket_trainer3,
            'tiket_trainer4' => $tiket_trainer4,
            'tiket_trainer5' => $tiket_trainer5,
            'tiket_trainer6' => $tiket_trainer6,
            'tiket_trainer7' => $tiket_trainer7,
            'tiket_trainer_nama' => $request->input('tiket_trainer_nama'),
            'tiket_trainer_nama1' => $request->input('tiket_trainer_nama1'),
            'tiket_trainer_nama2' => $request->input('tiket_trainer_nama2'),
            'tiket_trainer_nama3' => $request->input('tiket_trainer_nama3'),
            'tiket_trainer_nama4' => $request->input('tiket_trainer_nama4'),
            'tiket_trainer_nama5' => $request->input('tiket_trainer_nama5'),
            'tiket_trainer_nama6' => $request->input('tiket_trainer_nama6'),
            'tiket_trainer_nama7' => $request->input('tiket_trainer_nama7'),
            // <!-- end -->

            'total_tiket_trainer_berangkat' => $total_tiket_trainer_berangkat,

            // <!-- tiket trainer pulang -->
            'tiket_trainer_pulang' => $tiket_trainer_pulang,
            'tiket_trainer_pulang1' => $tiket_trainer_pulang1,
            'tiket_trainer_pulang2' => $tiket_trainer_pulang2,
            'tiket_trainer_pulang3' => $tiket_trainer_pulang3,
            'tiket_trainer_pulang4' => $tiket_trainer_pulang4,
            'tiket_trainer_pulang5' => $tiket_trainer_pulang5,
            'tiket_trainer_pulang6' => $tiket_trainer_pulang6,
            'tiket_trainer_pulang7' => $tiket_trainer_pulang7,
            'tiket_trainer_pulang_nama' => $request->input('tiket_trainer_pulang_nama'),
            'tiket_trainer_pulang_nama1' => $request->input('tiket_trainer_pulang_nama1'),
            'tiket_trainer_pulang_nama2' => $request->input('tiket_trainer_pulang_nama2'),
            'tiket_trainer_pulang_nama3' => $request->input('tiket_trainer_pulang_nama3'),
            'tiket_trainer_pulang_nama4' => $request->input('tiket_trainer_pulang_nama4'),
            'tiket_trainer_pulang_nama5' => $request->input('tiket_trainer_pulang_nama5'),
            'tiket_trainer_pulang_nama6' => $request->input('tiket_trainer_pulang_nama6'),
            'tiket_trainer_pulang_nama7' => $request->input('tiket_trainer_pulang_nama7'),
            // <!-- end -->

            'total_tiket_trainer_pulang' => $total_tiket_trainer_pulang,

            // <!-- tiket team berangkat -->
            'tiket_team' => $tiket_team,
            'tiket_team1' => $tiket_team1,
            'tiket_team2' => $tiket_team2,
            'tiket_team3' => $tiket_team3,
            'tiket_team4' => $tiket_team4,
            'tiket_team5' => $tiket_team5,
            'tiket_team6' => $tiket_team6,
            'tiket_team7' => $tiket_team7,
            'tiket_team_nama' => $request->input('tiket_team_nama'),
            'tiket_team_nama1' => $request->input('tiket_team_nama1'),
            'tiket_team_nama2' => $request->input('tiket_team_nama2'),
            'tiket_team_nama3' => $request->input('tiket_team_nama3'),
            'tiket_team_nama4' => $request->input('tiket_team_nama4'),
            'tiket_team_nama5' => $request->input('tiket_team_nama5'),
            'tiket_team_nama6' => $request->input('tiket_team_nama6'),
            'tiket_team_nama7' => $request->input('tiket_team_nama7'),
            // <!-- end -->

            'total_tiket_team_berangkat' => $total_tiket_team_berangkat,

            // <!-- tiket team pulang -->
            'tiket_team_pulang' => $tiket_team_pulang,
            'tiket_team_pulang1' => $tiket_team_pulang1,
            'tiket_team_pulang2' => $tiket_team_pulang2,
            'tiket_team_pulang3' => $tiket_team_pulang3,
            'tiket_team_pulang4' => $tiket_team_pulang4,
            'tiket_team_pulang5' => $tiket_team_pulang5,
            'tiket_team_pulang6' => $tiket_team_pulang6,
            'tiket_team_pulang7' => $tiket_team_pulang7,
            'tiket_team_pulang_nama' => $request->input('tiket_team_pulang_nama'),
            'tiket_team_pulang_nama1' => $request->input('tiket_team_pulang_nama1'),
            'tiket_team_pulang_nama2' => $request->input('tiket_team_pulang_nama2'),
            'tiket_team_pulang_nama3' => $request->input('tiket_team_pulang_nama3'),
            'tiket_team_pulang_nama4' => $request->input('tiket_team_pulang_nama4'),
            'tiket_team_pulang_nama5' => $request->input('tiket_team_pulang_nama5'),
            'tiket_team_pulang_nama6' => $request->input('tiket_team_pulang_nama6'),
            'tiket_team_pulang_nama7' => $request->input('tiket_team_pulang_nama7'),
            // <!-- end -->

            'total_tiket_team_pulang' => $total_tiket_team_pulang,
            'hotel' => $hotel,
            'konsumsi_tambahan' => $konsumsi_tambahan,
            'lainnya' => $lainnya,
            'tanggal' => $request->input('tanggal'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
            'status' => $request->input('status'),
            'note' => $request->input('note'),
            'total' => $total,
            'total_uang_masuk' => $total_uang_masuk,
            'keuntungan' => $keuntungan,
            'persentase_keuntungan' => $persentase_keuntungan,
            'marketing' => $marketing,
        ]);

        // Redirect with success or error message
        if ($save) {
            // Get the ID of the newly created data
            $campId = $save->id;

            // Redirect to the detail page for the newly created data with SweetAlert notification

            // Redirect with an error message if data creation fails
            return redirect()->route('account.camp.index')->with('success', 'Data Laporan Camp Berhasil Disimpan!');
        }
    }

    public function detail($id)
    {
        $user = Auth::user();
        $camp = Camp::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital


        $users = User::join('camp', 'users.id', '=', 'camp.user_id')
            ->where('users.company', $user->company)
            ->get(['users.*']);

        return view('account.camp.detail', compact('camp', 'users')); // Sesuaikan path template dengan benar
    }

    public function destroy($id)
    {
        try {
            $camp = Camp::find($id);

            if ($camp) {
                $camp->delete();
                return response()->json(['status' => 'success', 'message' => 'Data Berhasil Dihapus!']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan!'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi Kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $camp = Camp::findOrFail($id); // Pastikan 'Gaji' menggunakan huruf kapital

        $users = User::join('camp', 'users.id', '=', 'camp.user_id')
            ->where('users.company', $user->company)
            ->get(['users.*']);

        return view('account.camp.edit', compact('camp', 'users')); // Sesuaikan path template dengan benar
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $camp = Camp::findOrFail($id);

        $title = $request->input('title');
        $camp_ke = $request->input('camp_ke');

        $uang_masuk = $request->input('uang_masuk');
        $uang_masuk = empty($uang_masuk) ? 0 : str_replace(",", "", $uang_masuk); // Convert to numeric value or set to 0 if empty

        $lain_lain = $request->input('lain_lain');
        $lain_lain = empty($lain_lain) ? 0 : str_replace(",", "", $lain_lain);

        // <!-- gaji trainer -->
        $gaji_trainer = $request->input('gaji_trainer');
        $gaji_trainer = empty($gaji_trainer) ? 0 : str_replace(",", "", $gaji_trainer);

        $gaji_trainer1 = $request->input('gaji_trainer1');
        $gaji_trainer1 = empty($gaji_trainer1) ? 0 : str_replace(",", "", $gaji_trainer1);

        $gaji_trainer2 = $request->input('gaji_trainer2');
        $gaji_trainer2 = empty($gaji_trainer2) ? 0 : str_replace(",", "", $gaji_trainer2);

        $gaji_trainer3 = $request->input('gaji_trainer3');
        $gaji_trainer3 = empty($gaji_trainer3) ? 0 : str_replace(",", "", $gaji_trainer3);

        $gaji_trainer4 = $request->input('gaji_trainer4');
        $gaji_trainer4 = empty($gaji_trainer4) ? 0 : str_replace(",", "", $gaji_trainer4);

        $gaji_trainer5 = $request->input('gaji_trainer5');
        $gaji_trainer5 = empty($gaji_trainer5) ? 0 : str_replace(",", "", $gaji_trainer5);

        $gaji_trainer6 = $request->input('gaji_trainer6');
        $gaji_trainer6 = empty($gaji_trainer6) ? 0 : str_replace(",", "", $gaji_trainer6);
        // <!-- end -->

        // <!-- gaji team -->
        $gaji_team = $request->input('gaji_team');
        $gaji_team = empty($gaji_team) ? 0 : str_replace(",", "", $gaji_team);

        $gaji_team1 = $request->input('gaji_team1');
        $gaji_team1 = empty($gaji_team1) ? 0 : str_replace(",", "", $gaji_team1);

        $gaji_team2 = $request->input('gaji_team2');
        $gaji_team2 = empty($gaji_team2) ? 0 : str_replace(",", "", $gaji_team2);

        $gaji_team3 = $request->input('gaji_team3');
        $gaji_team3 = empty($gaji_team3) ? 0 : str_replace(",", "", $gaji_team3);

        $gaji_team4 = $request->input('gaji_team4');
        $gaji_team4 = empty($gaji_team4) ? 0 : str_replace(",", "", $gaji_team4);

        $gaji_team5 = $request->input('gaji_team5');
        $gaji_team5 = empty($gaji_team5) ? 0 : str_replace(",", "", $gaji_team5);

        $gaji_team6 = $request->input('gaji_team6');
        $gaji_team6 = empty($gaji_team6) ? 0 : str_replace(",", "", $gaji_team6);

        $gaji_team7 = $request->input('gaji_team7');
        $gaji_team7 = empty($gaji_team7) ? 0 : str_replace(",", "", $gaji_team7);

        $gaji_team8 = $request->input('gaji_team8');
        $gaji_team8 = empty($gaji_team8) ? 0 : str_replace(",", "", $gaji_team8);

        $gaji_team9 = $request->input('gaji_team9');
        $gaji_team9 = empty($gaji_team9) ? 0 : str_replace(",", "", $gaji_team9);

        $gaji_team10 = $request->input('gaji_team10');
        $gaji_team10 = empty($gaji_team10) ? 0 : str_replace(",", "", $gaji_team10);
        // <!-- end -->

        $team_cabang = $request->input('team_cabang');
        $team_cabang = empty($team_cabang) ? 0 : str_replace(",", "", $team_cabang);

        $booknote = $request->input('booknote');
        $booknote = empty($booknote) ? 0 : str_replace(",", "", $booknote);

        $grammarly = $request->input('grammarly');
        $grammarly = empty($grammarly) ? 0 : str_replace(",", "", $grammarly);

        // <!-- tiket trainer berangkat -->
        $tiket_trainer = $request->input('tiket_trainer');
        $tiket_trainer = empty($tiket_trainer) ? 0 : str_replace(",", "", $tiket_trainer);
        $tiket_trainer1 = $request->input('tiket_trainer1');
        $tiket_trainer1 = empty($tiket_trainer1) ? 0 : str_replace(",", "", $tiket_trainer1);
        $tiket_trainer2 = $request->input('tiket_trainer2');
        $tiket_trainer2 = empty($tiket_trainer2) ? 0 : str_replace(",", "", $tiket_trainer2);
        $tiket_trainer3 = $request->input('tiket_trainer3');
        $tiket_trainer3 = empty($tiket_trainer3) ? 0 : str_replace(",", "", $tiket_trainer3);
        $tiket_trainer4 = $request->input('tiket_trainer4');
        $tiket_trainer4 = empty($tiket_trainer4) ? 0 : str_replace(",", "", $tiket_trainer4);
        $tiket_trainer5 = $request->input('tiket_trainer5');
        $tiket_trainer5 = empty($tiket_trainer5) ? 0 : str_replace(",", "", $tiket_trainer5);
        $tiket_trainer6 = $request->input('tiket_trainer6');
        $tiket_trainer6 = empty($tiket_trainer6) ? 0 : str_replace(",", "", $tiket_trainer6);
        $tiket_trainer7 = $request->input('tiket_trainer7');
        $tiket_trainer7 = empty($tiket_trainer7) ? 0 : str_replace(",", "", $tiket_trainer7);
        // <!-- end -->

        // <!-- tiket trainer pulang -->
        $tiket_trainer_pulang = $request->input('tiket_trainer_pulang');
        $tiket_trainer_pulang = empty($tiket_trainer_pulang) ? 0 : str_replace(",", "", $tiket_trainer_pulang);
        $tiket_trainer_pulang1 = $request->input('tiket_trainer_pulang1');
        $tiket_trainer_pulang1 = empty($tiket_trainer_pulang1) ? 0 : str_replace(",", "", $tiket_trainer_pulang1);
        $tiket_trainer_pulang2 = $request->input('tiket_trainer_pulang2');
        $tiket_trainer_pulang2 = empty($tiket_trainer_pulang2) ? 0 : str_replace(",", "", $tiket_trainer_pulang2);
        $tiket_trainer_pulang3 = $request->input('tiket_trainer_pulang3');
        $tiket_trainer_pulang3 = empty($tiket_trainer_pulang3) ? 0 : str_replace(",", "", $tiket_trainer_pulang3);
        $tiket_trainer_pulang4 = $request->input('tiket_trainer_pulang4');
        $tiket_trainer_pulang4 = empty($tiket_trainer_pulang4) ? 0 : str_replace(",", "", $tiket_trainer_pulang4);
        $tiket_trainer_pulang5 = $request->input('tiket_trainer_pulang5');
        $tiket_trainer_pulang5 = empty($tiket_trainer_pulang5) ? 0 : str_replace(",", "", $tiket_trainer_pulang5);
        $tiket_trainer_pulang6 = $request->input('tiket_trainer_pulang6');
        $tiket_trainer_pulang6 = empty($tiket_trainer_pulang6) ? 0 : str_replace(",", "", $tiket_trainer_pulang6);
        $tiket_trainer_pulang7 = $request->input('tiket_trainer_pulang7');
        $tiket_trainer_pulang7 = empty($tiket_trainer_pulang7) ? 0 : str_replace(",", "", $tiket_trainer_pulang7);
        // <!-- end -->

        // <!-- tiket team berangkat -->
        $tiket_team = $request->input('tiket_team');
        $tiket_team = empty($tiket_team) ? 0 : str_replace(",", "", $tiket_team);
        $tiket_team1 = $request->input('tiket_team1');
        $tiket_team1 = empty($tiket_team1) ? 0 : str_replace(",", "", $tiket_team1);
        $tiket_team2 = $request->input('tiket_team2');
        $tiket_team2 = empty($tiket_team2) ? 0 : str_replace(",", "", $tiket_team2);
        $tiket_team3 = $request->input('tiket_team3');
        $tiket_team3 = empty($tiket_team3) ? 0 : str_replace(",", "", $tiket_team3);
        $tiket_team4 = $request->input('tiket_team4');
        $tiket_team4 = empty($tiket_team4) ? 0 : str_replace(",", "", $tiket_team4);
        $tiket_team5 = $request->input('tiket_team5');
        $tiket_team5 = empty($tiket_team5) ? 0 : str_replace(",", "", $tiket_team5);
        $tiket_team6 = $request->input('tiket_team6');
        $tiket_team6 = empty($tiket_team6) ? 0 : str_replace(",", "", $tiket_team6);
        $tiket_team7 = $request->input('tiket_team7');
        $tiket_team7 = empty($tiket_team7) ? 0 : str_replace(",", "", $tiket_team7);
        // <!-- end -->

        // <!-- tiket team pulang -->
        $tiket_team_pulang = $request->input('tiket_team_pulang');
        $tiket_team_pulang = empty($tiket_team_pulang) ? 0 : str_replace(",", "", $tiket_team_pulang);
        $tiket_team_pulang1 = $request->input('tiket_team_pulang1');
        $tiket_team_pulang1 = empty($tiket_team_pulang1) ? 0 : str_replace(",", "", $tiket_team_pulang1);
        $tiket_team_pulang2 = $request->input('tiket_team_pulang2');
        $tiket_team_pulang2 = empty($tiket_team_pulang2) ? 0 : str_replace(",", "", $tiket_team_pulang2);
        $tiket_team_pulang3 = $request->input('tiket_team_pulang3');
        $tiket_team_pulang3 = empty($tiket_team_pulang3) ? 0 : str_replace(",", "", $tiket_team_pulang3);
        $tiket_team_pulang4 = $request->input('tiket_team_pulang4');
        $tiket_team_pulang4 = empty($tiket_team_pulang4) ? 0 : str_replace(",", "", $tiket_team_pulang4);
        $tiket_team_pulang5 = $request->input('tiket_team_pulang5');
        $tiket_team_pulang5 = empty($tiket_team_pulang5) ? 0 : str_replace(",", "", $tiket_team_pulang5);
        $tiket_team_pulang6 = $request->input('tiket_team_pulang6');
        $tiket_team_pulang6 = empty($tiket_team_pulang6) ? 0 : str_replace(",", "", $tiket_team_pulang6);
        $tiket_team_pulang7 = $request->input('tiket_team_pulang7');
        $tiket_team_pulang7 = empty($tiket_team_pulang7) ? 0 : str_replace(",", "", $tiket_team_pulang7);
        // <!-- end -->

        $hotel = $request->input('hotel');
        $hotel = empty($hotel) ? 0 : str_replace(",", "", $hotel);

        $konsumsi_tambahan = $request->input('konsumsi_tambahan');
        $konsumsi_tambahan = empty($konsumsi_tambahan) ? 0 : str_replace(",", "", $konsumsi_tambahan);

        $lainnya = $request->input('lainnya');
        $lainnya = empty($lainnya) ? 0 : str_replace(",", "", $lainnya);

        $total = $gaji_trainer + $gaji_trainer1 + $gaji_trainer2 + $gaji_trainer3 + $gaji_trainer4 + $gaji_trainer5 + $gaji_trainer6 +
            $gaji_team + $gaji_team1 + $gaji_team2 + $gaji_team3 + $gaji_team4 + $gaji_team5 + $gaji_team6 + $gaji_team7 + $gaji_team8 + $gaji_team9 + $gaji_team10 +
            $team_cabang + $booknote + $grammarly +
            $tiket_trainer + $tiket_trainer1 + $tiket_trainer2 + $tiket_trainer3 + $tiket_trainer4 + $tiket_trainer5 + $tiket_trainer6 + $tiket_trainer7 +
            $tiket_trainer_pulang + $tiket_trainer_pulang1 + $tiket_trainer_pulang2 + $tiket_trainer_pulang3 + $tiket_trainer_pulang4 + $tiket_trainer_pulang5 + $tiket_trainer_pulang6 + $tiket_trainer_pulang7 +
            $tiket_team + $tiket_team1 + $tiket_team2 + $tiket_team3 + $tiket_team4 + $tiket_team5 + $tiket_team6 + $tiket_team7 +
            $tiket_team_pulang + $tiket_team_pulang1 + $tiket_team_pulang2 + $tiket_team_pulang3 + $tiket_team_pulang4 + $tiket_team_pulang5 + $tiket_team_pulang6 + $tiket_team_pulang7 +
            $hotel + $konsumsi_tambahan + $lainnya;
        $total = empty($total) ? 0 : str_replace(",", "", $total);

        $total_gaji_trainer = $gaji_trainer + $gaji_trainer1 + $gaji_trainer2 + $gaji_trainer3 + $gaji_trainer4 + $gaji_trainer5 + $gaji_trainer6;
        $total_gaji_trainer = empty($total_gaji_trainer) ? 0 : str_replace(",", "", $total_gaji_trainer);

        $total_gaji_team = $gaji_team + $gaji_team1 + $gaji_team2 + $gaji_team3 + $gaji_team4 + $gaji_team5 + $gaji_team6 + $gaji_team7 + $gaji_team8 + $gaji_team9 + $gaji_team10;
        $total_gaji_team = empty($total_gaji_team) ? 0 : str_replace(",", "", $total_gaji_team);

        $total_uang_masuk = $uang_masuk + $lain_lain;
        $total_uang_masuk = empty($total_uang_masuk) ? 0 : str_replace(",", "", $total_uang_masuk);

        $total_tiket_trainer_berangkat = $tiket_trainer + $tiket_trainer1 + $tiket_trainer2 + $tiket_trainer3 + $tiket_trainer4 + $tiket_trainer5 + $tiket_trainer6 + $tiket_trainer7;
        $total_tiket_trainer_berangkat = empty($total_tiket_trainer_berangkat) ? 0 : str_replace(",", "", $total_tiket_trainer_berangkat);

        $total_tiket_trainer_pulang = $tiket_trainer_pulang + $tiket_trainer_pulang1 + $tiket_trainer_pulang2 + $tiket_trainer_pulang3 + $tiket_trainer_pulang4 + $tiket_trainer_pulang5 + $tiket_trainer_pulang6 + $tiket_trainer_pulang7;
        $total_tiket_trainer_pulang = empty($total_tiket_trainer_pulang) ? 0 : str_replace(",", "", $total_tiket_trainer_pulang);

        $total_tiket_team_berangkat = $tiket_team + $tiket_team1 + $tiket_team2 + $tiket_team3 + $tiket_team4 + $tiket_team5 + $tiket_team6 + $tiket_team7;
        $total_tiket_team_berangkat = empty($total_tiket_team_berangkat) ? 0 : str_replace(",", "", $total_tiket_team_berangkat);

        $total_tiket_team_pulang = $tiket_team_pulang + $tiket_team_pulang1 + $tiket_team_pulang2 + $tiket_team_pulang3 + $tiket_team_pulang4 + $tiket_team_pulang5 + $tiket_team_pulang6 + $tiket_team_pulang7;
        $total_tiket_team_pulang = empty($total_tiket_team_pulang) ? 0 : str_replace(",", "", $total_tiket_team_pulang);

        $keuntungan = $total_uang_masuk - $total;
        $keuntungan = empty($keuntungan) ? 0 : str_replace(",", "", $keuntungan);

        $persentase_keuntungan = ($keuntungan / $total_uang_masuk) * 100;

        $marketing = $total_uang_masuk * 0.10; // Ubah persentase ke dalam desimal (0.10 untuk 10%)
        $marketing = empty($marketing) ? 0 : str_replace(",", "", number_format($marketing));

        $existingUserId = $camp->user_id;

        $camp->update([
            'user_id' => Auth::user()->id,
            'title' => $title,
            'camp_ke' => $camp_ke,
            'uang_masuk' => $uang_masuk,
            'lain_lain' => $lain_lain,

            // <!-- gaji trainer -->
            'gaji_trainer' => $gaji_trainer,
            'gaji_trainer1' => $gaji_trainer1,
            'gaji_trainer2' => $gaji_trainer2,
            'gaji_trainer3' => $gaji_trainer3,
            'gaji_trainer4' => $gaji_trainer4,
            'gaji_trainer5' => $gaji_trainer5,
            'gaji_trainer6' => $gaji_trainer6,
            'gaji_trainer_nama' => $request->input('gaji_trainer_nama'),
            'gaji_trainer_nama1' => $request->input('gaji_trainer_nama1'),
            'gaji_trainer_nama2' => $request->input('gaji_trainer_nama2'),
            'gaji_trainer_nama3' => $request->input('gaji_trainer_nama3'),
            'gaji_trainer_nama4' => $request->input('gaji_trainer_nama4'),
            'gaji_trainer_nama5' => $request->input('gaji_trainer_nama5'),
            'gaji_trainer_nama6' => $request->input('gaji_trainer_nama6'),
            'total_gaji_trainer' => $total_gaji_trainer,
            // <!-- end -->

            // <!-- gaji team -->
            'gaji_team' => $gaji_team,
            'gaji_team1' => $gaji_team1,
            'gaji_team2' => $gaji_team2,
            'gaji_team3' => $gaji_team3,
            'gaji_team4' => $gaji_team4,
            'gaji_team5' => $gaji_team5,
            'gaji_team6' => $gaji_team6,
            'gaji_team7' => $gaji_team7,
            'gaji_team8' => $gaji_team8,
            'gaji_team9' => $gaji_team9,
            'gaji_team10' => $gaji_team10,
            'gaji_team_nama' => $request->input('gaji_team_nama'),
            'gaji_team_nama1' => $request->input('gaji_team_nama1'),
            'gaji_team_nama2' => $request->input('gaji_team_nama2'),
            'gaji_team_nama3' => $request->input('gaji_team_nama3'),
            'gaji_team_nama4' => $request->input('gaji_team_nama4'),
            'gaji_team_nama5' => $request->input('gaji_team_nama5'),
            'gaji_team_nama6' => $request->input('gaji_team_nama6'),
            'gaji_team_nama7' => $request->input('gaji_team_nama7'),
            'gaji_team_nama8' => $request->input('gaji_team_nama8'),
            'gaji_team_nama9' => $request->input('gaji_team_nama9'),
            'gaji_team_nama10' => $request->input('gaji_team_nama10'),
            'total_gaji_team' => $total_gaji_team,
            // <!-- end -->

            'team_cabang' => $team_cabang,
            'booknote' => $booknote,
            'grammarly' => $grammarly,
            'peserta' => $request->input('peserta'),

            // <!-- tiket trainer berangkat -->
            'tiket_trainer' => $tiket_trainer,
            'tiket_trainer1' => $tiket_trainer1,
            'tiket_trainer2' => $tiket_trainer2,
            'tiket_trainer3' => $tiket_trainer3,
            'tiket_trainer4' => $tiket_trainer4,
            'tiket_trainer5' => $tiket_trainer5,
            'tiket_trainer6' => $tiket_trainer6,
            'tiket_trainer7' => $tiket_trainer7,
            'tiket_trainer_nama' => $request->input('tiket_trainer_nama'),
            'tiket_trainer_nama1' => $request->input('tiket_trainer_nama1'),
            'tiket_trainer_nama2' => $request->input('tiket_trainer_nama2'),
            'tiket_trainer_nama3' => $request->input('tiket_trainer_nama3'),
            'tiket_trainer_nama4' => $request->input('tiket_trainer_nama4'),
            'tiket_trainer_nama5' => $request->input('tiket_trainer_nama5'),
            'tiket_trainer_nama6' => $request->input('tiket_trainer_nama6'),
            'tiket_trainer_nama7' => $request->input('tiket_trainer_nama7'),
            // <!-- end -->

            'total_tiket_trainer_berangkat' => $total_tiket_trainer_berangkat,

            // <!-- tiket trainer pulang -->
            'tiket_trainer_pulang' => $tiket_trainer_pulang,
            'tiket_trainer_pulang1' => $tiket_trainer_pulang1,
            'tiket_trainer_pulang2' => $tiket_trainer_pulang2,
            'tiket_trainer_pulang3' => $tiket_trainer_pulang3,
            'tiket_trainer_pulang4' => $tiket_trainer_pulang4,
            'tiket_trainer_pulang5' => $tiket_trainer_pulang5,
            'tiket_trainer_pulang6' => $tiket_trainer_pulang6,
            'tiket_trainer_pulang7' => $tiket_trainer_pulang7,
            'tiket_trainer_pulang_nama' => $request->input('tiket_trainer_pulang_nama'),
            'tiket_trainer_pulang_nama1' => $request->input('tiket_trainer_pulang_nama1'),
            'tiket_trainer_pulang_nama2' => $request->input('tiket_trainer_pulang_nama2'),
            'tiket_trainer_pulang_nama3' => $request->input('tiket_trainer_pulang_nama3'),
            'tiket_trainer_pulang_nama4' => $request->input('tiket_trainer_pulang_nama4'),
            'tiket_trainer_pulang_nama5' => $request->input('tiket_trainer_pulang_nama5'),
            'tiket_trainer_pulang_nama6' => $request->input('tiket_trainer_pulang_nama6'),
            'tiket_trainer_pulang_nama7' => $request->input('tiket_trainer_pulang_nama7'),
            // <!-- end -->

            'total_tiket_trainer_pulang' => $total_tiket_trainer_pulang,

            // <!-- tiket team berangkat -->
            'tiket_team' => $tiket_team,
            'tiket_team1' => $tiket_team1,
            'tiket_team2' => $tiket_team2,
            'tiket_team3' => $tiket_team3,
            'tiket_team4' => $tiket_team4,
            'tiket_team5' => $tiket_team5,
            'tiket_team6' => $tiket_team6,
            'tiket_team7' => $tiket_team7,
            'tiket_team_nama' => $request->input('tiket_team_nama'),
            'tiket_team_nama1' => $request->input('tiket_team_nama1'),
            'tiket_team_nama2' => $request->input('tiket_team_nama2'),
            'tiket_team_nama3' => $request->input('tiket_team_nama3'),
            'tiket_team_nama4' => $request->input('tiket_team_nama4'),
            'tiket_team_nama5' => $request->input('tiket_team_nama5'),
            'tiket_team_nama6' => $request->input('tiket_team_nama6'),
            'tiket_team_nama7' => $request->input('tiket_team_nama7'),
            // <!-- end -->

            'total_tiket_team_berangkat' => $total_tiket_team_berangkat,

            // <!-- tiket team pulang -->
            'tiket_team_pulang' => $tiket_team_pulang,
            'tiket_team_pulang1' => $tiket_team_pulang1,
            'tiket_team_pulang2' => $tiket_team_pulang2,
            'tiket_team_pulang3' => $tiket_team_pulang3,
            'tiket_team_pulang4' => $tiket_team_pulang4,
            'tiket_team_pulang5' => $tiket_team_pulang5,
            'tiket_team_pulang6' => $tiket_team_pulang6,
            'tiket_team_pulang7' => $tiket_team_pulang7,
            'tiket_team_pulang_nama' => $request->input('tiket_team_pulang_nama'),
            'tiket_team_pulang_nama1' => $request->input('tiket_team_pulang_nama1'),
            'tiket_team_pulang_nama2' => $request->input('tiket_team_pulang_nama2'),
            'tiket_team_pulang_nama3' => $request->input('tiket_team_pulang_nama3'),
            'tiket_team_pulang_nama4' => $request->input('tiket_team_pulang_nama4'),
            'tiket_team_pulang_nama5' => $request->input('tiket_team_pulang_nama5'),
            'tiket_team_pulang_nama6' => $request->input('tiket_team_pulang_nama6'),
            'tiket_team_pulang_nama7' => $request->input('tiket_team_pulang_nama7'),
            // <!-- end -->

            'total_tiket_team_pulang' => $total_tiket_team_pulang,
            'hotel' => $hotel,
            'konsumsi_tambahan' => $konsumsi_tambahan,
            'lainnya' => $lainnya,
            'tanggal' => $request->input('tanggal'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
            'status' => $request->input('status'),
            'note' => $request->input('note'),
            'total' => $total,
            'total_uang_masuk' => $total_uang_masuk,
            'keuntungan' => $keuntungan,
            'persentase_keuntungan' => $persentase_keuntungan,
            'marketing' => $marketing,
        ]);

        // Redirect with success or error message
        if ($camp) {
            // Get the ID of the newly created data

            // Redirect to the detail page for the newly created data with SweetAlert notification

            // Redirect with an error message if data creation fails
            return redirect()->route('account.camp.index')->with('success', 'Data Laporan Camp Berhasil Disimpan!');
        }
    }

    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');

        if (!$startDate || !$endDate) {
            // Jika tanggal_awal atau tanggal_akhir tidak ada dalam request, gunakan rentang bulan ini
            $currentMonth = date('Y-m-01 00:00:00');
            $nextMonth = date('Y-m-01 00:00:00', strtotime('+1 month'));
        } else {
            // Jika tanggal_awal dan tanggal_akhir ada dalam request, gunakan rentang tersebut
            $currentMonth = date('Y-m-d 00:00:00', strtotime($startDate));
            $nextMonth = date('Y-m-d 00:00:00', strtotime($endDate));
        }

        $camp = DB::table('camp')
            ->select('camp.id', 'camp.id_transaksi', 'camp.title', 'camp.camp_ke', 'camp.uang_masuk', 'camp.lain_lain', 'camp.total_uang_masuk', 'camp.gaji_trainer', 'camp.gaji_team', 'camp.team_cabang', 'camp.booknote', 'camp.grammarly', 'camp.tiket_trainer', 'camp.tiket_team', 'camp.hotel', 'camp.marketing', 'camp.konsumsi_tambahan', 'camp.lainnya', 'camp.total', 'camp.keuntungan', 'camp.tanggal', 'camp.tanggal_akhir', 'camp.status', 'camp.note', 'camp.persentase_keuntungan')
            ->leftJoin('users', 'camp.user_id', '=', 'users.id')
            ->where('users.company', $user->company)
            ->whereBetween('camp.tanggal', [$currentMonth, $nextMonth])
            ->orderBy('camp.created_at', 'DESC')
            ->get();

        // Additional data retrieval for 'maintenance'
        $maintenances = DB::table('maintenance')
            ->orderBy('created_at', 'DESC')
            ->get();


        // Calculate total gaji
        $totalCamp = $camp->sum('total');
        $terbilang = Terbilang::make($totalCamp, ' rupiah');
        $users = User::all(); // Get all users

        // total gaji yang status terbayar
        $campTerbayar = $camp->where('status', 'terbayar');
        $totalCampTerbayar = $campTerbayar->sum('total');
        $terbilangterbayar = Terbilang::make($totalCampTerbayar, ' rupiah');

        $html = view('account.camp.pdf', compact('camp', 'totalCamp', 'user', 'terbilang', 'startDate', 'endDate', 'totalCampTerbayar', 'terbilangterbayar'))->render();

        // Instantiate Dompdf with the default configuration
        $dompdf = new Dompdf();

        // Load the HTML content into Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the PDF
        $dompdf->render();

        // Get the output as a string
        $output = $dompdf->output();

        // Set the response headers
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan-Camp_' . date('d-m-Y') . '.pdf"',
        ];
        return Response::make($dompdf->output(), 200, $headers);
    }

    public function SlipCamp($id)
    {
        $user = Auth::user();
        $camp = Camp::findOrFail($id);

        // Calculate total gaji
        $totalCamp = $camp->total;
        $terbilang = Terbilang::make($totalCamp, ' rupiah');

        // Fetch the associated employee information
        $employee = User::find($camp->user_id); // Assuming user_id corresponds to the employee's ID
        $userWithNorekBank = User::find($employee->id);

        // Get the HTML content of the view
        $userLogoPath = public_path('images/' . $user->logo_company);

        if (!file_exists($userLogoPath)) {
            // Handle the case where the image file does not exist.
            return response('Image not found', 404);
        }

        $html = view('account.camp.slipcamp', compact('camp', 'totalCamp', 'user', 'terbilang', 'employee', 'userWithNorekBank', 'userLogoPath'))->render();

        // Instantiate Dompdf with the default configuration
        $dompdf = new Dompdf();

        // Load the HTML content into Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait'); // Change 'potrait' to 'portrait'

        // Render the PDF
        $dompdf->render();

        // Set the response headers
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan-Per-Camp_' . date('d-m-Y') . '.pdf"',
        ];

        // Output the generated PDF to the browser
        return Response::make($dompdf->output(), 200, $headers);
    }
}
