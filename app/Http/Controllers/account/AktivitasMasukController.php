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
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        $aktivitas = $this->kueri($request)
            ->with('user:id,full_name,level')
            ->paginate(20)
            ->appends($request->only('q', 'status', 'dari', 'sampai'));

        $ringkasan = [
            'gagal_24_jam' => AktivitasMasuk::where('berhasil', false)
                ->where('created_at', '>=', now()->subDay())->count(),
            'berhasil_24_jam' => AktivitasMasuk::where('berhasil', true)
                ->where('created_at', '>=', now()->subDay())->count(),
        ];

        return view('account.aktivitas_masuk.index', compact('aktivitas', 'cari', 'status', 'dari', 'sampai', 'ringkasan'));
    }

    /** Unduh hasil saringan sebagai CSV, untuk keperluan penelusuran. */
    public function ekspor(Request $request)
    {
        $pengguna = Auth::user();

        if (! in_array($pengguna->level, self::PERAN_BOLEH, true)) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke jejak aktivitas masuk.');
        }

        $nama = 'aktivitas-masuk-' . now()->format('Ymd-His') . '.csv';
        $kueri = $this->kueri($request)->with('user:id,full_name');

        return response()->streamDownload(function () use ($kueri) {
            $keluaran = fopen('php://output', 'w');
            // BOM supaya Excel membaca huruf beraksen dengan benar
            fwrite($keluaran, "\xEF\xBB\xBF");
            fputcsv($keluaran, ['Waktu', 'Identitas', 'Akun', 'Status', 'Alasan', 'IP', 'Peramban']);

            $kueri->chunk(500, function ($baris) use ($keluaran) {
                foreach ($baris as $a) {
                    fputcsv($keluaran, [
                        optional($a->created_at)->format('d/m/Y H:i:s'),
                        $a->identitas,
                        optional($a->user)->full_name,
                        $a->berhasil ? 'Berhasil' : 'Gagal',
                        $a->alasan,
                        $a->ip,
                        $a->peramban,
                    ]);
                }
            });

            fclose($keluaran);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Kueri bersama untuk tampilan tabel dan ekspor. */
    private function kueri(Request $request)
    {
        $cari = trim((string) $request->input('q'));
        $status = $request->input('status');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        return AktivitasMasuk::query()
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('identitas', 'LIKE', "%{$cari}%")
                        ->orWhere('ip', 'LIKE', "%{$cari}%");
                });
            })
            ->when($status === 'berhasil', fn ($q) => $q->where('berhasil', true))
            ->when($status === 'gagal', fn ($q) => $q->where('berhasil', false))
            ->when($dari, fn ($q) => $q->whereDate('created_at', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('created_at', '<=', $sampai))
            ->orderByDesc('id');
    }
}
