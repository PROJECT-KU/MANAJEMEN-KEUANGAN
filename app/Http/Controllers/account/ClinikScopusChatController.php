<?php


namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\ClinikScopusChat;
use App\ClinikScopusPemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Events\ClinikScopusChatEvent;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;

class ClinikScopusChatController extends Controller
{

    // <!--================== MENAMPILKAN CHAT ==================-->
    public function index($pemesananId)
    {
        $user = Auth::user();
        $pemesanan = ClinikScopusPemesanan::findOrFail($pemesananId);

        if (!in_array($user->id, [$pemesanan->customer_id, $pemesanan->trainer_id])) {
            return redirect()
                ->route('account.Clinik-Scopus-Riwayat-Pemesanan.index') // sesuaikan tujuan
                ->with('alert', [
                    'type' => 'error',
                    'title' => 'Akses Ditolak',
                    'message' => 'Anda tidak memiliki izin mengakses sesi konsultasi ini.'
                ]);
        }

        //  AUTO COMPLETE CHECK
        $this->autoCompleteIfEnded($pemesanan);

        // Ambil jam_sesi utuh
        $jam_sesi = $pemesanan->jam_sesi;

        // Optional: ambil lastChatId
        $lastChatId = ClinikScopusChat::where('pemesanan_id', $pemesanan->id)->max('id') ?? 0;

        return view('account.clinik_scopus_chat.index', compact(
            'pemesanan',
            'jam_sesi',
            'lastChatId'
        ));
    }
    // <!--================== END ==================-->

    // <!--================== LOAD CHAT ==================-->
    public function load(Request $request, $pemesananId)
    {
        $pemesanan = ClinikScopusPemesanan::findOrFail($pemesananId);
        $userId = Auth::id();

        // proteksi akses
        if (!in_array($userId, [$pemesanan->customer_id, $pemesanan->trainer_id])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $lastId = $request->query('last_id', 0);

        $chats = ClinikScopusChat::where('pemesanan_id', $pemesananId)
            ->where('id', '>', $lastId)
            ->with('sender')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'chats' => $chats
        ]);
    }

    // <!--================== END ==================-->

    // <!--================== KIRIM CHAT ==================-->
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048'
        ]);

        $pemesanan = ClinikScopusPemesanan::findOrFail($request->pemesanan_id);
        if (!$request->message && !$request->hasFile('images')) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan atau gambar harus diisi'
            ], 422);
        }

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {

                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('ClinikScopusChat'), $filename);

                $imagePaths[] = $filename;
            }
        }

        $userId = Auth::id();

        if (!in_array($userId, [$pemesanan->customer_id, $pemesanan->trainer_id])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $receiver = $userId == $pemesanan->customer_id ? $pemesanan->trainer_id : $pemesanan->customer_id;

        $chat = ClinikScopusChat::create([
            'pemesanan_id' => $pemesanan->id,
            'sender_id' => $userId,
            'receiver_id' => $receiver,
            'message' => $request->message,
            'images' => $imagePaths ? json_encode($imagePaths) : null
        ]);

        $chat->load('sender');
        return response()->json(['success' => true, 'chat' => $chat]);
    }
    // <!--================== END ==================-->

    // <!--================== STATUS AUTO BERUBAH ==================-->
    private function autoCompleteIfEnded(ClinikScopusPemesanan $pemesanan)
    {
        if ($pemesanan->status === 'completed') return;
        if (!$pemesanan->tanggal_booking) return;
        if (!$pemesanan->jam_sesi) return;

        $jam_sesi = $pemesanan->jam_sesi;

        $end = null;

        // cek jika ada bundling (dipisah koma)
        if (strpos($jam_sesi, ',') !== false) {
            $sessions = explode(',', $jam_sesi);
            $lastSession = trim(end($sessions));
            preg_match('/(\d{1,2}[.:]\d{2})\s*-\s*(\d{1,2}[.:]\d{2})/', $lastSession, $match);
            $end = $match[2] ?? null;
        } else {
            // reguler
            preg_match('/(\d{1,2}[.:]\d{2})\s*-\s*(\d{1,2}[.:]\d{2})/', $jam_sesi, $match);
            $end = $match[2] ?? null;
        }

        if (!$end) return;

        $endTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            Carbon::parse($pemesanan->tanggal_booking)->format('Y-m-d') . ' ' . str_replace('.', ':', $end),
            'Asia/Jakarta'
        );

        if (now('Asia/Jakarta')->greaterThanOrEqualTo($endTime)) {
            $pemesanan->update(['status' => 'completed']);
        }
    }
    // <!--================== END ==================-->

    // <!--================== DATA DI DB AUTO KEDELETE SESUAI DENGAN PEMESANAN ID ==================-->
    public function clearChat(ClinikScopusPemesanan $pemesanan)
    {
        $userId = Auth::id();

        //  Proteksi akses
        if (!in_array($userId, [$pemesanan->customer_id, $pemesanan->trainer_id])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Ambil semua chat sebelum dihapus
        $chats = ClinikScopusChat::where('pemesanan_id', $pemesanan->id)->get();

        foreach ($chats as $chat) {
            if (is_array($chat->images)) {
                foreach ($chat->images as $img) {
                    $filePath = public_path('ClinikScopusChat/' . $img);
                    if (file_exists($filePath)) {
                        @unlink($filePath); // hapus file, @ untuk mencegah error jika tidak ada
                    }
                }
            }
        }

        //  DELETE SEMUA CHAT BERDASARKAN PEMESANAN
        ClinikScopusChat::where('pemesanan_id', $pemesanan->id)->delete();

        return response()->json([
            'success' => true
        ]);
    }
    // <!--================== END ==================-->
}
