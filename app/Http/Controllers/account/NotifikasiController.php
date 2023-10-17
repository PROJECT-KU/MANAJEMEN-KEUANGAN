<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\User;
use App\Debit;
use App\Credit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NotifikasiController extends Controller
{
    public function showNotifications()
    {
        // Ambil semua transaksi masuk
        $incomingTransactions = Credit::where('status', 'pending')->get();

        // Ambil semua transaksi keluar
        $outgoingTransactions = Debit::where('status', 'pending')->get();

        // Cek apakah ada transaksi masuk atau keluar
        if ($incomingTransactions->count() > 0 || $outgoingTransactions->count() > 0) {
            // Jika ada, tampilkan notifikasi ke manager
            // Misalnya, Anda bisa menyimpan notifikasi ini dalam database notifikasi
            // atau mengirim notifikasi ke manager melalui email atau pesan dalam aplikasi.
            // Di sini kami hanya akan mengembalikan data notifikasi dalam bentuk array.
            $notifications = [
                'incoming' => $incomingTransactions->count(),
                'outgoing' => $outgoingTransactions->count(),
            ];

            return view('account.notifications.index', compact('notifications'));
        } else {
            // Jika tidak ada transaksi yang perlu dinotifikasi, kembalikan halaman kosong atau sesuai kebutuhan Anda.
            return view('account.notifications.empty');
        }
    }
}
