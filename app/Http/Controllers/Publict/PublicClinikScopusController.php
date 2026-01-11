<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Clinikscopus;
use App\User;
use App\AnalisisBibliometrik;
use App\Mail\AnalisisBibliometrikMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PublicClinikScopusController extends Controller
{

    // <!--================== MENAMPILKAN DAFTAR TRAINER ==================-->
    public function index(Request $request)
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Nonaktifkan event yang sudah lewat
        DB::table('clinikscopus')
            ->whereDate('tanggal', '<', $today)
            ->where('status', 'active')
            ->update([
                'status' => 'non active'
            ]);

        $categories = DB::table('clinikscopus')
            ->join('users', 'users.id', '=', 'clinikscopus.user_id')
            ->where('clinikscopus.status', 'active')
            ->whereDate('clinikscopus.tanggal', '=', $today)
            ->whereRaw(
                "TIMESTAMP(clinikscopus.tanggal, '00:01:00') <= ?",
                [$now]
            )
            ->select(
                'clinikscopus.*',
                'users.full_name as full_name',
                'users.jobdesk as jobdesk'
            )
            ->orderBy('clinikscopus.tanggal', 'ASC')
            ->paginate(6);

        return view('public.clinik_scopus.index', compact('categories'));
    }
    // <!--================== END ==================-->

    // <!--================== MENAMPILKAN SESI TRAINER ==================-->
    public function sesi(Request $request, $id)
    {
        // Ambil data klinik
        $clinik = Clinikscopus::with(['biayaPersesi'])
            ->join('users', 'users.id', '=', 'clinikscopus.user_id')
            ->where('clinikscopus.id', $id)
            ->where('clinikscopus.status', 'active')
            ->select(
                'clinikscopus.*',
                'users.full_name',
                'users.jobdesk'
            )
            ->first();

        if (!$clinik) {
            abort(404);
        }

        /**
         * =====================================================
         * PROMO BERDASARKAN TABEL clinikscopus_promo_sesi
         * =====================================================
         */
        $promo = DB::table('clinikscopus_promo as p')
            ->where('p.status', 'active')
            ->whereDate('p.tanggal_mulai_promo', '<=', now())
            ->whereDate('p.tanggal_selesai_promo', '>=', now())
            ->whereExists(function ($q) use ($id) {
                $q->select(DB::raw(1))
                    ->from('clinikscopus_promo_sesi as ps')
                    ->whereColumn('ps.promo_id', 'p.id')
                    ->where('ps.clinikscopus_id', $id);
            })
            ->orderBy('p.created_at', 'desc')
            ->get()
            ->map(function ($p) use ($clinik, $id) {

                $p->sesi_bundling = DB::table('clinikscopus_promo_sesi')
                    ->where('promo_id', $p->id)
                    ->where('clinikscopus_id', $id)
                    ->orderBy('sesi_key')
                    ->get()
                    ->map(function ($s) use ($clinik) {

                        $column = $s->sesi_key == 1 ? 'sesi' : 'sesi' . $s->sesi_key;
                        $s->jam = $clinik->$column ?? null;

                        return $s;
                    });

                return $p; // ✅ INI YANG WAJIB
            });

        // Spesialis
        $spesialis = [];
        if (!empty($clinik->spesialis)) {
            $spesialis = array_map('trim', explode(',', $clinik->spesialis));
        }

        return view(
            'public.clinik_scopus.formsesi',
            compact('clinik', 'spesialis', 'promo')
        );
    }
    // <!--================== END ==================-->

    // <!--================== CEK KODE DISKON ==================-->
    public function cekDiskon(Request $request)
    {
        $kode = strtoupper($request->kode ?? '');
        $harga = $request->harga ?? 0;
        $clinik_id = $request->clinik_id ?? null;

        $now = now(); // waktu sekarang (tanggal + jam)

        // Ambil promo aktif dengan kode diskon
        $promo = DB::table('clinikscopus_promo')
            ->where('kode_diskon', $kode)
            ->where('status', 'active')
            ->where('tanggal_mulai_promo', '<=', $now)
            ->where('tanggal_selesai_promo', '>=', $now)
            ->whereIn('tipe_diskon', ['persentase', 'nominal']) // hanya kode diskon
            ->when($clinik_id, function ($query, $clinik_id) {
                $query->whereExists(function ($q) use ($clinik_id) {
                    $q->select(DB::raw(1))
                        ->from('clinikscopus_promo_sesi as ps')
                        ->whereColumn('ps.promo_id', 'clinikscopus_promo.id')
                        ->where('ps.clinikscopus_id', $clinik_id);
                });
            })
            ->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak valid atau sudah kadaluarsa'
            ]);
        }

        // Hitung nominal diskon
        $potongan = $promo->tipe_diskon === 'persentase'
            ? floor($harga * ($promo->nominal_diskon / 100))
            : $promo->nominal_diskon;

        $totalBaru = max($harga - $potongan, 0);

        return response()->json([
            'success' => true,
            'potongan' => $potongan,
            'totalBaru' => $totalBaru,
            'message' => "Diskon '{$kode}' berhasil diterapkan!"
        ]);
    }
    // <!--================== END ==================-->

}
