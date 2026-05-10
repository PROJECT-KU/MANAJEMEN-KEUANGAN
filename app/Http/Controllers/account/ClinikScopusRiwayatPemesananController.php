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
use Illuminate\Support\Facades\File;

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

        $datas = $query->latest()->paginate(9);

        // Karena $datas sekarang adalah LengthAwarePaginator, gunakan getCollection() untuk looping autoUpdate
        $datas->getCollection()->each(function ($item) {
            $this->autoUpdateStatus($item);
        });

        return view(
            'account.clinik_scopus_riwayat_pemesanan.index',
            compact('datas')
        );
    }
    // <!--================== END ==================-->

    // <!--================== SEARCH ==================-->
    public function search(Request $request)
    {
        $search = strtolower($request->get('q'));
        $user = Auth::user();

        $query = ClinikScopusPemesanan::with(['customer', 'trainer']);

        // ==========================================================
        // 🔐 ROLE FILTER (Disamakan persis dengan function index)
        // ==========================================================
        if ($user->level === 'user' && $user->jenis === 'perorangan') {
            $query->where('customer_id', $user->id);
        } elseif ($user->level === 'manager') {
            // tampilkan semua
        } elseif ($user->level === 'karyawan') {
            $query->where('trainer_id', $user->id)
                ->whereHas('trainer', function ($q) use ($user) {
                    $q->where('company', $user->company);
                });
        }

        // ===============================
        // 🔍 LOGIKA PENCARIAN
        // ===============================
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_booking', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('sesi', 'LIKE', "%{$search}%")
                    ->orWhere('jam_sesi', 'LIKE', "%{$search}%")
                    ->orWhere('kendala', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('full_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('trainer', function ($sub) use ($search) {
                        $sub->where('full_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereRaw("DATE_FORMAT(tanggal_booking, '%d %M %Y') LIKE ?", ["%{$search}%"]);
            });
        }
        $datas = $query->latest()->paginate(10);
        $datas->each(function ($item) {
            $this->autoUpdateStatus($item);
        });
        return view('account.clinik_scopus_riwayat_pemesanan.index', compact('datas'));
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

    // <!--================== DELETE DATA ==================-->
    public function destroy($id)
    {
        /**
         * ========================================================
         * 🛡️ GATEKEEPER: CEK OTORISASI
         * ========================================================
         * Hanya user dengan level 'manager' yang memiliki "kunci".
         * Jika bukan manager, hentikan proses dan kirim error 403.
         */
        if (Auth::user()->level !== 'manager') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Anda tidak memiliki akses.'
            ], 403);
        }

        /**
         * ========================================================
         * 📦 MULAI TRANSAKSI DATABASE (SAFETY FIRST)
         * ========================================================
         * Kita bungkus dalam try-catch. Jika di tengah jalan ada 
         * proses yang gagal, semua akan dibatalkan (rollback) 
         * agar database tetap bersih dan tidak ada data yang cacat.
         */
        DB::beginTransaction();
        try {
            // Cari data yang mau dieksekusi, jika tidak ada langsung lempar error (findOrFail)
            $pemesanan = ClinikScopusPemesanan::findOrFail($id);

            /**
             * ========================================================
             * 🖼️ EKSEKUSI 1: HAPUS BUKTI PEMBAYARAN (FILE FISIK)
             * ========================================================
             * Jangan sampai penyimpanan server penuh dengan file usang.
             * Kita pastikan file benar-benar ada di folder, lalu hapus! 🔥
             */
            if ($pemesanan->gambar) {
                $filePath = public_path('ClinikScopusPemesanan/' . $pemesanan->gambar);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }

            /**
             * ========================================================
             * 💬 EKSEKUSI 2: BERSIHKAN DATA RELASI (TESTIMONI)
             * ========================================================
             * Mencegah "Orphan Data" (data yatim piatu).
             * Hapus semua testimoni yang menempel pada ID pesanan ini.
             */
            ClinikScopusTestimoni::where('clinikscopus_pemesanan_id', $pemesanan->id)->delete();

            /**
             * ========================================================
             * 🗑️ EKSEKUSI 3: HAPUS DATA INDUK (PEMESANAN)
             * ========================================================
             * Setelah semua "buntut" dan "sampah" dibersihkan, 
             * saatnya menghapus data utamanya. Sayonara! 👋
             */
            $pemesanan->delete();

            /**
             * ========================================================
             * 🚀 MISI SUKSES: SIMPAN PERMANEN & BERI KABAR BAIK
             * ========================================================
             * Semua proses berjalan mulus. Lakukan Commit ke DB,
             * lalu kirim JSON berisi pesan sukses ke SweetAlert (AJAX).
             */
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data dan gambar berhasil dihapus secara permanen!'
            ]);
        } catch (\Throwable $e) {
            /**
             * ========================================================
             * 🚨 SYSTEM FAILURE: BATALKAN SEMUA & TANGKAP ERROR
             * ========================================================
             * Terjadi kebocoran/error! Tarik mundur semua proses DB (Rollback).
             * Kirim kode 500 dan pesan error agar mudah di-debug.
             */
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
    // <!--================== END ==================-->
}
