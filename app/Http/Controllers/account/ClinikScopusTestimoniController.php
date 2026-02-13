<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\ClinikScopusPemesanan;
use App\ClinikScopusTestimoni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClinikScopusTestimoniController extends Controller
{
    /**
     * Simpan testimoni (trainer atau aplikasi)
     */
    public function store(Request $request)
    {
        $request->validate([
            // testimoni trainer
            'pemesanan_id' => 'required|exists:clinikscopus_pemesanan,id',
            'rating'       => 'nullable|integer|min:1|max:5',
            'komentar'     => 'nullable|string|max:1000',

            // testimoni aplikasi
            'rating_aplikasi'   => 'nullable|integer|min:1|max:5',
            'komentar_aplikasi' => 'nullable|string|max:1000',

            'is_anonymous' => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        // Ambil pemesanan
        $pemesanan = ClinikScopusPemesanan::findOrFail($request->pemesanan_id);

        // hanya customer
        if ($userId !== $pemesanan->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Cegah duplikasi testimoni untuk pemesanan yang sama
        $existsTestimoni = ClinikScopusTestimoni::where(
            'clinikscopus_pemesanan_id',
            $pemesanan->id
        )->exists();

        if ($existsTestimoni) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan testimoni untuk pemesanan ini sebelumnya'
            ], 409);
        }

        // Simpan testimoni
        $data = [
            'id'                => (string) Str::uuid(),
            'clinikscopus_id'   => $pemesanan->clinikscopus_id,
            'clinikscopus_pemesanan_id' => $pemesanan->id,
            'trainer_id'        => $pemesanan->trainer_id,
            'customer_id'       => $userId,
            'id_transaksi'      => $pemesanan->id_transaksi,
            'kode_booking'      => $pemesanan->kode_booking,
            'sesi'              => $pemesanan->sesi,
            'jam_sesi'          => $pemesanan->jam_sesi,
            'rating'            => $request->rating,
            'komentar'          => $request->komentar,
            'rating_aplikasi'   => $request->rating_aplikasi,
            'komentar_aplikasi' => $request->komentar_aplikasi,
            'is_anonymous'      => $request->is_anonymous ?? false,
            'status'            => 'published',
        ];

        ClinikScopusTestimoni::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas feedback Anda'
        ]);
    }
}
