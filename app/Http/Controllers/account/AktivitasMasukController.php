<?php

namespace App\Http\Controllers\account;

use App\AktivitasMasuk;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menampilkan jejak percobaan masuk. Hanya untuk peran yang memegang
 * tanggung jawab pengawasan.
 */
class AktivitasMasukController extends Controller
{
    private const PERAN_BOLEH = ['manager', 'ceo', 'admin'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $pengguna = Auth::user();

        if (! in_array($pengguna->level, self::PERAN_BOLEH, true)) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke jejak aktivitas masuk.');
        }

        $cari = trim((string) $request->input('q'));
        $status = $request->input('status');

        $aktivitas = AktivitasMasuk::query()
            ->with('user:id,full_name,level')
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('identitas', 'LIKE', "%{$cari}%")
                        ->orWhere('ip', 'LIKE', "%{$cari}%");
                });
            })
            ->when($status === 'berhasil', fn ($q) => $q->where('berhasil', true))
            ->when($status === 'gagal', fn ($q) => $q->where('berhasil', false))
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->only('q', 'status'));

        $ringkasan = [
            'gagal_24_jam' => AktivitasMasuk::where('berhasil', false)
                ->where('created_at', '>=', now()->subDay())->count(),
            'berhasil_24_jam' => AktivitasMasuk::where('berhasil', true)
                ->where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('account.aktivitas_masuk.index', compact('aktivitas', 'cari', 'status', 'ringkasan'));
    }
}
