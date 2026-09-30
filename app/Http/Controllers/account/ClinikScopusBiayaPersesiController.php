<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Tarif per sesi Clinik Scopus.
 *
 * Layar ini bukan daftar pilihan melainkan SATU pengaturan: berapa harga satu
 * sesi sekarang, dan berapa persen PPN-nya. Baris lamanya disimpan sebagai
 * riwayat karena sesi yang sudah dipesan menunjuk ke barisnya.
 *
 * Bentuk lamanya CRUD biasa — daftar, tambah, ubah, hapus, lengkap dengan menu
 * status yang bisa disetel bebas. Bentuk itu membiarkan dua tarif berstatus
 * berlaku sekaligus, dan tidak ada yang memberi tahu tarif mana yang sebenarnya
 * dipakai saat pelanggan memesan.
 */
class ClinikScopusBiayaPersesiController extends Controller
{
    private const PER_HALAMAN = 8;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Hanya orang dalam yang boleh melihat tarif. */
    private function bolehMelihat(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    /** Mengubah tarif menyentuh harga yang ditagihkan; administrator saja. */
    private function bolehMengubah(): bool
    {
        return (bool) Auth::user()?->adalahAdministrator();
    }

    private function tolak(string $pesan)
    {
        return redirect()->route('account.dashboard.index')->with('error', $pesan);
    }

    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak('Anda tidak punya akses ke tarif Clinik Scopus.');
        }

        $berlaku = ClinikScopusBiayaPersesi::berlaku();

        /*
         * Riwayat TIDAK memuat tarif yang sedang berlaku: ia sudah tampil utuh
         * di kartu paling atas, dan mengulangnya di daftar membuat orang
         * mengira ada dua tarif.
         */
        $riwayat = ClinikScopusBiayaPersesi::query()
            ->when($berlaku, fn ($q) => $q->where('id', '!=', $berlaku->getKey()))
            ->withCount('clinikScopus')
            ->orderByDesc('updated_at')
            ->paginate(self::PER_HALAMAN);

        return view('account.clinik_scopus_biaya_persesi.index', [
            'berlaku' => $berlaku,
            'riwayat' => $riwayat,
            'bolehUbah' => $this->bolehMengubah(),
            'jumlahTarif' => ClinikScopusBiayaPersesi::count(),
            'sesiMemakai' => $berlaku ? $berlaku->dipakai_sesi : 0,
        ]);
    }

    /**
     * Menyetel tarif yang berlaku.
     *
     * Satu pintu untuk dua keadaan yang dulu jadi dua halaman terpisah:
     *
     * - tanpa id  -> tarif BARU, dan langsung diberlakukan
     * - dengan id -> memperbaiki tarif yang sedang berlaku
     *
     * Dibedakan dari niatnya, bukan dari tombol yang ditekan: menaikkan harga
     * itu peristiwa yang layak dicatat sebagai baris baru, sementara
     * membetulkan salah ketik tidak boleh meninggalkan jejak seolah harganya
     * pernah berubah.
     */
    public function simpan(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return back()->with('error', 'Hanya administrator yang boleh mengubah tarif.');
        }

        $data = $request->validate([
            'biaya_persesi' => ['required', 'string'],
            'ppn' => ['nullable', 'integer', 'min:0', 'max:100'],
            'perbaiki' => ['nullable', 'uuid'],
        ], [
            'biaya_persesi.required' => 'Isi dulu tarif per sesinya.',
            'ppn.max' => 'PPN tidak masuk akal kalau lebih dari 100 persen.',
        ]);

        // Isian tarif diketik berformat "Rp 125.000" oleh pemolesnya di layar.
        $tarif = (int) preg_replace('/\D+/', '', $data['biaya_persesi']);

        if ($tarif < 1) {
            return back()
                ->withInput()
                ->withErrors(['biaya_persesi' => 'Tarifnya harus lebih dari nol.']);
        }

        $ppn = $request->filled('ppn') ? (int) $data['ppn'] : null;

        if (! empty($data['perbaiki'])) {
            $lama = ClinikScopusBiayaPersesi::find($data['perbaiki']);

            if ($lama) {
                $lama->update(['biaya_persesi' => $tarif, 'ppn' => $ppn]);
                $lama->jadikanBerlaku();

                return redirect()
                    ->route('account.Clinik-Scopus-Biaya-Persesi.index')
                    ->with('success', 'Tarif diperbarui jadi ' . $lama->tarif_terbaca . ' per sesi.');
            }
        }

        $baru = ClinikScopusBiayaPersesi::create([
            'biaya_persesi' => $tarif,
            'ppn' => $ppn,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        $baru->jadikanBerlaku();

        return redirect()
            ->route('account.Clinik-Scopus-Biaya-Persesi.index')
            ->with('success', 'Tarif baru ' . $baru->tarif_terbaca . ' per sesi mulai berlaku.');
    }

    /** Memberlakukan lagi tarif lama, tanpa mengetik ulang angkanya. */
    public function berlakukan(ClinikScopusBiayaPersesi $tarif)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh mengubah tarif.',
            ], 403);
        }

        $tarif->jadikanBerlaku();

        return response()->json([
            'success' => true,
            'message' => 'Tarif ' . $tarif->tarif_terbaca . ' per sesi kembali berlaku.',
        ]);
    }

    /**
     * Menghapus satu baris riwayat tarif.
     *
     * Diperiksa lebih dulu, bukan dicoba lalu ditangkap: clinikscopus.
     * biaya_persesi_id berkunci asing ON DELETE NO ACTION, jadi menghapus tarif
     * yang masih dipakai sesi mana pun ditolak MySQL dengan galat 1451 — dan
     * galat itu, kalau sampai ke layar, hanya menyebut nama constraint-nya.
     */
    public function destroy(ClinikScopusBiayaPersesi $tarif)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh menghapus tarif.',
            ], 403);
        }

        if ($tarif->berlaku) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif ini sedang berlaku. Setel tarif lain dulu sebagai penggantinya.',
            ], 409);
        }

        $dipakai = $tarif->dipakai_sesi;

        if ($dipakai > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif ini masih jadi acuan harga ' . $dipakai . ' sesi. '
                    . 'Menghapusnya akan memutus riwayat harga pesanan yang sudah terjadi.',
            ], 409);
        }

        try {
            $tarif->delete();
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tarif ini masih tertaut ke data lain, jadi belum bisa dihapus.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tarif lama dihapus dari riwayat.',
        ]);
    }
}
