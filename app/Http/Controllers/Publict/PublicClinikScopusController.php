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
        $today = Carbon::today();

        // 🔴 Nonaktifkan event yang sudah lewat
        DB::table('clinikscopus')
            ->whereDate('tanggal_offline', '<', $today)
            ->where('status', 'active')
            ->update([
                'status' => 'non active'
            ]);

        // 🟢 Ambil event yang sedang berlangsung
        $categories = DB::table('clinikscopus')
            ->join('users', 'users.id', '=', 'clinikscopus.user_id')
            ->where('clinikscopus.status', 'active')
            ->whereDate('clinikscopus.tanggal_online', '<=', $today)
            ->whereDate('clinikscopus.tanggal_offline', '>=', $today)
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

        // ============================
        // HITUNG SESI PENUH
        // ============================
        $totalHari = count($rangeTanggal);

        $sesiPenuh = collect();

        foreach (range(1, 9) as $sesi) {

            $jumlahBooking = collect($sesiTerpakai)
                ->where('sesi', 'Sesi ' . $sesi)
                ->count();

            if ($jumlahBooking >= $totalHari) {
                $sesiPenuh->push('Sesi ' . $sesi);
            }
        }

        return view(
            'public.clinik_scopus.formsesi',
            compact('clinik', 'spesialis', 'promo', 'sesiTerpakai', 'rangeTanggal', 'sesiPenuh')
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
                'tipe_promo' => 'required|in:reguler,promo',
            ]);

            $result = DB::transaction(function () use ($request) {

                $tanggal = Carbon::parse($request->booking)->format('Y-m-d');

                $bentrok = DB::table('clinikscopus_pemesanan')
                    ->where('clinikscopus_id', $request->klinik_id)
                    ->whereDate('tanggal_booking', $tanggal)
                    ->where(function ($q) use ($request) {

                        if ($request->tipe_promo === 'reguler') {
                            $q->where('tipe_promo', 'reguler')
                                ->where('sesi', $request->sesi);
                        }

                        if ($request->tipe_promo === 'promo') {
                            $q->where('tipe_promo', 'promo');
                        }
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($bentrok) {
                    return [
                        'status' => 409,
                        'type'   => 'bentrok',
                        'title'  => 'Sesi Tidak Tersedia',
                        'message' => 'Maaf, sesi ini baru saja dibooking peserta lain.'
                    ];
                }

                $clinik = Clinikscopus::select('id', 'user_id')->findOrFail($request->klinik_id);

                ClinikScopusPemesanan::create([
                    'clinikscopus_id'  => $clinik->id,
                    'trainer_id'       => $clinik->user_id,
                    'customer_id'      => auth()->id(),
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
                    'tanggal_booking'  => Carbon::parse($request->booking),
                    'status'           => 'pending',
                    'tanggal'          => now(),
                    'ip_address'       => $request->ip(),
                    'browser'          => $request->userAgent(),
                ]);

                return [
                    'status' => 200,
                    'success' => true,
                    'title'  => 'Berhasil 🎉',
                    'message' => 'Pemesanan berhasil dibuat. Silakan lanjutkan pembayaran.'
                ];
            });

            return response()->json($result, $result['status']);
        } catch (\Throwable $e) {

            \Log::error('ERROR STORE PEMESANAN', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'title'   => 'Server Error',
                'message' => 'Terjadi kesalahan server.'
            ], 500);
        }
    }
    // <!--================== END ==================-->
}
