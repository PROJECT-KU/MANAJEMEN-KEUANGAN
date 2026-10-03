<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Layar panitia untuk pendaftaran Webinar Eksklusif.
 *
 * SEBELUM layar ini ada, satu-satunya tempat yang menulis status 'paid'
 * adalah pemberitahuan balik dari DOKU. Selama kredensial DOKU belum diisi,
 * SEMUA pembayaran jatuh ke transfer manual — dan tidak ada satu pun jalur
 * yang bisa menandainya lunas. Akibatnya tiap pendaftaran pasti kedaluwarsa
 * dan kursinya dilepas walau uangnya sudah dikirim; terukur di basis data
 * lokal, 3 dari 3 pendaftaran berstatus expired dan tidak satu pun pernah
 * paid.
 *
 * Layar ini yang menutup lubang itu.
 */
class WebinarEksklusifPendaftarController extends Controller
{
    public function index(Request $request)
    {
        $sesi = KategoriLayanan::where('layanan', 'webinar_eksklusif')
            ->orderByDesc('mulai')
            ->get(['id', 'nama', 'mulai', 'total_kuota', 'sisa_kuota']);

        $kueri = WebinarEksklusifPendaftaran::query()
            ->with(['angkatan:id,nama,mulai', 'pesertaLain'])
            ->latest();

        if ($request->filled('cari')) {
            $cari = trim($request->input('cari'));

            /*
             * Dicari juga ke NOMOR PENDAFTARAN: itu yang disebut orang saat
             * menghubungi panitia lewat WhatsApp, dan itu pula yang tertulis
             * paling besar di surat buktinya.
             */
            $kueri->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('email', 'like', "%{$cari}%")
                    ->orWhere('telp', 'like', "%{$cari}%")
                    ->orWhere('id_transaksi', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('status')) {
            $kueri->where('status', $request->input('status'));
        }

        if ($request->filled('sesi')) {
            $kueri->where('kategori_id', $request->input('sesi'));
        }

        $pendaftaran = $kueri->paginate(20)->withQueryString();

        return view('account.webinar_eksklusif_pendaftar.index', [
            'pendaftaran' => $pendaftaran,
            'sesi' => $sesi,
            'ringkasan' => $this->ringkasan($request),
        ]);
    }

    /**
     * Menandai satu pendaftaran LUNAS.
     *
     * Kuotanya TIDAK dipotong lagi di sini: sudah dipotong saat orangnya
     * menekan "Daftar". Memotongnya sekali lagi akan menghilangkan kursi
     * yang sebenarnya masih ada.
     */
    public function lunasi(Request $request, string $id)
    {
        $pendaftaran = WebinarEksklusifPendaftaran::findOrFail($id);

        if ($pendaftaran->status === 'paid') {
            return back()->with('info', 'Pendaftaran itu memang sudah tercatat lunas.');
        }

        DB::transaction(function () use ($pendaftaran) {
            $segar = WebinarEksklusifPendaftaran::whereKey($pendaftaran->getKey())
                ->lockForUpdate()->first();

            if ($segar === null || $segar->status === 'paid') {
                return;
            }

            /*
             * Kursinya DIAMBIL KEMBALI kalau pendaftarannya sempat
             * kedaluwarsa. Panitia sering baru sempat memeriksa buktinya
             * sesudah batas waktunya lewat, dan tanpa ini orang yang sudah
             * membayar justru kehilangan tempatnya.
             */
            if ($segar->status === 'expired') {
                $sesi = KategoriLayanan::whereKey($segar->kategori_id)->lockForUpdate()->first();

                if ($sesi !== null && $sesi->total_kuota !== null) {
                    $sesi->forceFill([
                        'sisa_kuota' => (string) max(0, (int) $sesi->sisa_kuota - (int) $segar->jumlah_pendaftar),
                    ])->save();
                }
            }

            $segar->forceFill([
                'status' => 'paid',
                'bayar_status' => 'manual',
                'bayar_pada' => now(),
                // Dicatat SIAPA yang menandai: kalau belakangan ada selisih
                // uang, yang bisa ditanya adalah orangnya, bukan sistemnya.
                'note' => trim(($segar->note ? $segar->note . ' | ' : '')
                    . 'Dilunasi manual oleh ' . (auth()->user()->full_name ?? 'pengguna #' . auth()->id())
                    . ' pada ' . now()->format('d M Y H:i')),
            ])->save();
        });

        return back()->with('sukses', 'Pendaftaran ' . $pendaftaran->id_transaksi . ' ditandai lunas.');
    }

    /** Membatalkan pendaftaran dan mengembalikan kursinya. */
    public function batalkan(Request $request, string $id)
    {
        $pendaftaran = WebinarEksklusifPendaftaran::findOrFail($id);

        if (in_array($pendaftaran->status, ['cancel', 'expired'], true)) {
            return back()->with('info', 'Pendaftaran itu memang sudah tidak aktif.');
        }

        DB::transaction(function () use ($pendaftaran) {
            $segar = WebinarEksklusifPendaftaran::whereKey($pendaftaran->getKey())
                ->lockForUpdate()->first();

            if ($segar === null || in_array($segar->status, ['cancel', 'expired'], true)) {
                return;
            }

            $sesi = KategoriLayanan::whereKey($segar->kategori_id)->lockForUpdate()->first();

            if ($sesi !== null && $sesi->total_kuota !== null) {
                $sesi->forceFill([
                    'sisa_kuota' => (string) min(
                        (int) $sesi->total_kuota,
                        (int) $sesi->sisa_kuota + (int) $segar->jumlah_pendaftar
                    ),
                ])->save();
            }

            $segar->forceFill([
                'status' => 'cancel',
                'note' => trim(($segar->note ? $segar->note . ' | ' : '')
                    . 'Dibatalkan oleh ' . (auth()->user()->full_name ?? 'pengguna #' . auth()->id())
                    . ' pada ' . now()->format('d M Y H:i')),
            ])->save();
        });

        return back()->with('sukses', 'Pendaftaran ' . $pendaftaran->id_transaksi . ' dibatalkan, kursinya dikembalikan.');
    }

    /**
     * Angka ringkas di kepala layar.
     *
     * @return array<string,int>
     */
    private function ringkasan(Request $request): array
    {
        $dasar = fn () => WebinarEksklusifPendaftaran::query()
            ->when($request->filled('sesi'), fn ($q) => $q->where('kategori_id', $request->input('sesi')));

        return [
            'menunggu' => (clone $dasar())->where('status', 'pending')->count(),
            'lunas' => (clone $dasar())->where('status', 'paid')->count(),
            'kedaluwarsa' => (clone $dasar())->where('status', 'expired')->count(),
            'peserta_lunas' => (int) (clone $dasar())->where('status', 'paid')->sum('jumlah_pendaftar'),
            'uang_masuk' => (int) (clone $dasar())->where('status', 'paid')->sum('total_pembayaran'),
        ];
    }
}
