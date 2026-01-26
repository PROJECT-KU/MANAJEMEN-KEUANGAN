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
use App\ClinikScopusPemesanan;
use App\AnalisisBibliometrik;
use App\Mail\AnalisisBibliometrikMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Carbon\CarbonPeriod;

class PublicClinikScopusController extends Controller
{

    // <!--================== MENAMPILKAN DAFTAR TRAINER ==================-->
    public function index(Request $request)
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Nonaktifkan event yang sudah lewat
        DB::table('clinikscopus')
            ->whereDate('tanggal_offline', '<', $today)
            ->where('status', 'active')
            ->update([
                'status' => 'non active'
            ]);

        $categories = DB::table('clinikscopus')
            ->join('users', 'users.id', '=', 'clinikscopus.user_id')
            ->where('clinikscopus.status', 'active')
            ->whereDate('clinikscopus.tanggal_online', '=', $today)
            ->whereRaw(
                "TIMESTAMP(clinikscopus.tanggal_online, '00:01:00') <= ?",
                [$now]
            )
            ->select(
                'clinikscopus.*',
                'users.full_name as full_name',
                'users.jobdesk as jobdesk'
            )
            ->orderBy('clinikscopus.tanggal_online', 'ASC')
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

        // ambil data klinik tanggal periode online - offline trainer
        $tanggalOnline  = Carbon::parse($clinik->tanggal_online)->startOfDay();
        $tanggalOffline = Carbon::parse($clinik->tanggal_offline)->startOfDay();

        $rangeTanggal = [];

        $period = CarbonPeriod::create($tanggalOnline, $tanggalOffline);

        foreach ($period as $date) {
            $rangeTanggal[] = $date; // SIMPAN OBJECT
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

        $sesiTerpakai = ClinikScopusPemesanan::where('clinikscopus_id', $clinik->id)
            ->whereIn('status', ['pending', 'paid'])
            ->get()
            ->map(function ($item) {
                return [
                    'sesi'    => $item->sesi, // contoh: "Sesi 1"
                    'tanggal' => Carbon::parse($item->tanggal_booking)->format('Y-m-d'),
                    'tipe_promo' => $item->tipe_promo
                ];
            });

        return view(
            'public.clinik_scopus.formsesi',
            compact('clinik', 'spesialis', 'promo', 'sesiTerpakai', 'rangeTanggal')
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

    // <!--================== PEMESANAN ==================-->
    public function store(Request $request)
    {
        try {
            $request->validate([
                'klinik_id' => 'required|exists:clinikscopus,id',
                'nama'      => 'required|min:3',
                'email'     => 'required|email',
                'whatsapp'  => 'required|min:8',
                'total'     => 'required|numeric|min:1',
                'booking'   => 'required|date',

            ]);

            // Ambil klinik + trainer
            $clinik = Clinikscopus::select('id', 'user_id')->findOrFail($request->klinik_id);

            $pemesanan = ClinikScopusPemesanan::create([
                'clinikscopus_id'  => $clinik->id,
                'user_id'          => $clinik->user_id, // ✅ TRAINER
                'id_transaksi'     => 'BOOK-' . now()->format('dmYHis') . '-' . strtoupper(Str::random(5)),
                'kode_booking'     => $request->kode_booking,
                'sesi'             => $request->sesi,
                'jam_sesi'         => $request->jam_sesi,
                'nama_pemesan'     => $request->nama,
                'afiliasi_pemesan' => $request->afiliasi,
                'email_pemesan'    => $request->email,
                'telp_pemesan'     => $request->whatsapp,
                'kendala'          => $request->kendala,
                'desc_kendala'     => $request->kendala_desc,
                'harga_persesi'    => $request->harga,
                'diskon'           => $request->diskon ?? 0,
                'ppn'              => $request->ppn ?? 0,
                'kode_unik'        => $request->kode_unik ?? 0,
                'kode_diskon'      => $request->kode_diskon,
                'tipe_promo'       => $request->tipe_promo,
                'total_pembayaran' => $request->total,
                'tanggal_booking'  => Carbon::parse($request->booking)->format('Y-m-d H:i:s'),
                'status'           => 'pending',
                'tanggal'          => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan berhasil dibuat',
            ]);
        } catch (\Throwable $e) {
            \Log::error('ERROR STORE PEMESANAN', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }
    // <!--================== END ==================-->
}
