<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Clinikscopus;
use App\User;
use App\ClinikscopusPromoSesi;
use App\ClinikScopusBiayaPersesi;
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
        $sesi_key = $request->sesi_key ?? null; // 🔹 sesi yang dipilih user

        $now = now();

        // Ambil promo aktif dengan kode diskon
        $promo = DB::table('clinikscopus_promo')
            ->where('kode_diskon', $kode)
            ->where('status', 'active')
            ->where('tanggal_mulai_promo', '<=', $now)
            ->where('tanggal_selesai_promo', '>=', $now)
            ->whereIn('tipe_diskon', ['persentase', 'nominal'])
            ->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak valid atau sudah kadaluarsa'
            ]);
        }

        // 🔹 Cek apakah promo punya pembatasan sesi
        $totalSesiTerdaftar = DB::table('clinikscopus_promo_sesi')
            ->where('promo_id', $promo->id)
            ->count();

        // 🔹 Cek apakah sesi yang dipilih ADA di promo_sesi
        $adaDiPromoSesi = DB::table('clinikscopus_promo_sesi')
            ->where('promo_id', $promo->id)
            ->where('sesi_key', $sesi_key)
            ->exists();

        // ===== LOGIKA SESUAI PERMINTAAN ANDA =====

        // ❌ Jika promo MEMILIKI pembatasan sesi,
        // tapi sesi ini TIDAK termasuk → TOLAK
        if ($totalSesiTerdaftar > 0 && !$adaDiPromoSesi) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak berlaku untuk sesi ini'
            ]);
        }

        // ✅ BOLEH PAKAI DISKON (semua kondisi lain)
        if ($promo->tipe_diskon === 'persentase') {
            // Jika persentase → langsung ambil nominal_diskon apa adanya
            $potongan = $promo->nominal_diskon;
        } else {
            $potongan = $promo->nominal_diskon;
        }

        $totalBaru = max($harga - $potongan, 0);

        return response()->json([
            'success' => true,
            'potongan' => $potongan, // tetap angka (untuk hitungan)
            'potongan_rupiah' => number_format($potongan, 0, ',', '.'),
            'totalBaru' => $totalBaru,
            'message' => "Diskon '{$kode}' berhasil diterapkan!"
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== CEK PPN ==================-->
    public function cekPpn()
    {
        $biaya = ClinikScopusBiayaPersesi::select('ppn')->first();

        return response()->json([
            'ppn' => $biaya ? (int) $biaya->ppn : 0,
            'source' => 'clinikscopus_biaya_persesi'
        ]);
    }
    // <!--================== END ==================-->

}
