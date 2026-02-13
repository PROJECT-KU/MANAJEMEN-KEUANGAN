<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Clinikscopus;
use App\ClinikScopusPemesanan;
use App\ClinikScopusPromo;
use App\ClinikScopusPromoSesi;
use App\ClinikScopusTestimoni;
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

        $datas->each(function ($item) {
            $this->autoUpdateStatus($item);
        });

        return view(
            'account.clinik_scopus_riwayat_pemesanan.index',
            compact('datas')
        );
    }
    // <!--================== END ==================-->

    // <!--================== DETAIL DATA ==================-->
    public function detail($id)
    {
        $user = Auth::user();

        // Ambil pemesanan
        $datas = ClinikScopusPemesanan::findOrFail($id);

        // Ambil testimoni yang terkait, jika ada
        $datasTesti = ClinikScopusTestimoni::where('clinikscopus_pemesanan_id', $datas->id)->first();

        // Update status otomatis
        $this->autoUpdateStatus($datas);

        return view(
            'account.clinik_scopus_riwayat_pemesanan.detail',
            compact('datas', 'datasTesti')
        );
    }
    // <!--================== END ==================-->

    private function autoUpdateStatus(ClinikScopusPemesanan $pemesanan)
    {
        $now = now('Asia/Jakarta');

        /**
         * =====================================
         * 1️⃣ AUTO COMPLETED (PAID)
         * =====================================
         */
        if ($pemesanan->status === 'paid' && $pemesanan->tanggal_booking) {

            $end = null;

            // cek jika jam_sesi bundling (dipisah koma)
            if (strpos($pemesanan->jam_sesi, ',') !== false) {
                $sessions = explode(',', $pemesanan->jam_sesi);
                $lastSession = trim(end($sessions));

                preg_match('/(\d{1,2}[.:]\d{2})\s*-\s*(\d{1,2}[.:]\d{2})/', $lastSession, $match);
                $end = $match[2] ?? null;
            } else {
                // reguler
                preg_match('/(\d{1,2}[.:]\d{2})\s*-\s*(\d{1,2}[.:]\d{2})/', $pemesanan->jam_sesi, $match);
                $end = $match[2] ?? null;
            }

            if ($end) {
                $endTime = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    Carbon::parse($pemesanan->tanggal_booking)->format('Y-m-d')
                        . ' '
                        . str_replace('.', ':', $end),
                    'Asia/Jakarta'
                );

                if ($now->greaterThanOrEqualTo($endTime)) {
                    $pemesanan->update([
                        'status' => 'completed'
                    ]);
                    return;
                }
            }
        }

        /**
         * =====================================
         * 2️⃣ AUTO CANCELED (PENDING)
         * =====================================
         */
        if ($pemesanan->status === 'pending' && $pemesanan->tanggal_booking) {

            $tanggalBooking = Carbon::parse($pemesanan->tanggal_booking)
                ->startOfDay()
                ->timezone('Asia/Jakarta');

            // lewat 1 hari penuh
            if ($now->greaterThanOrEqualTo($tanggalBooking->addDay())) {
                $pemesanan->update([
                    'status' => 'canceled'
                ]);
            }
        }
    }
    // <!--================== END ==================-->

    // <!--================== UPDATE STATUS ==================-->
    public function updateStatus(Request $request, $id)
    {
        // 🔒 Proteksi role
        if (Auth::user()->level !== 'manager') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:pending,paid,completed,canceled'
        ]);

        $pemesanan = ClinikScopusPemesanan::findOrFail($id);
        $pemesanan->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status booking berhasil diperbarui');
    }
    // <!--================== END ==================-->
}
