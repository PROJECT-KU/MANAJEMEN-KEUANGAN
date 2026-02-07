<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\ClinikScopusPemesanan;
use App\ClinikScopusTestimoni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClinikScopusTestimoniController extends Controller
{
    /**
     * =============================
     * SIMPAN TESTIMONI TRAINER
     * =============================
     */
    public function storeTrainer(Request $request)
    {
        $request->validate([
            'pemesanan_id' => 'required|exists:clinikscopus_pemesanan,id',
            'rating'       => 'required|integer|min:1|max:5',
            'komentar'     => 'nullable|string|max:1000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $pemesanan = ClinikScopusPemesanan::findOrFail($request->pemesanan_id);

        //  hanya customer yang boleh memberi testimoni
        if ($userId !== $pemesanan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        //  hanya setelah selesai
        if ($pemesanan->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Testimoni hanya bisa diberikan setelah sesi selesai'
            ], 422);
        }

        //  cegah testimoni ganda
        $exists = ClinikScopusTestimoni::where('tipe_testimoni', 'trainer')
            ->where('customer_id', $userId)
            ->where('id_transaksi', $pemesanan->id_transaksi)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Testimoni sudah pernah diberikan'
            ], 409);
        }

        ClinikScopusTestimoni::create([
            'id'             => (string) Str::uuid(),
            'tipe_testimoni' => 'trainer',
            'clinikscopus_id' => $pemesanan->clinikscopus_id,
            'trainer_id'     => $pemesanan->trainer_id,
            'customer_id'    => $userId,
            'id_transaksi'   => $pemesanan->id_transaksi,
            'kode_booking'   => $pemesanan->kode_booking,
            'sesi'           => $pemesanan->sesi,
            'jam_sesi'       => $pemesanan->jam_sesi,
            'rating'         => $request->rating,
            'komentar'       => $request->komentar,
            'is_anonymous'   => $request->is_anonymous ?? false,
            'status'         => 'published',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas testimoni Anda'
        ]);
    }

    /**
     * =============================
     * SIMPAN TESTIMONI APLIKASI (WEB)
     * =============================
     */
    public function storeAplikasi(Request $request)
    {
        $request->validate([
            'rating_aplikasi'   => 'required|integer|min:1|max:5',
            'komentar_aplikasi' => 'nullable|string|max:1000',
            'is_anonymous'      => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        //  hanya 1 testimoni aplikasi per user
        $exists = ClinikScopusTestimoni::where('tipe_testimoni', 'aplikasi')
            ->where('customer_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan testimoni aplikasi'
            ], 409);
        }

        ClinikScopusTestimoni::create([
            'id'                 => (string) Str::uuid(),
            'tipe_testimoni'     => 'aplikasi',
            'customer_id'        => $userId,
            'rating_aplikasi'    => $request->rating_aplikasi,
            'komentar_aplikasi'  => $request->komentar_aplikasi,
            'platform'           => 'web',
            'is_anonymous'       => $request->is_anonymous ?? false,
            'status'             => 'published',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas feedback aplikasi Anda'
        ]);
    }
}
