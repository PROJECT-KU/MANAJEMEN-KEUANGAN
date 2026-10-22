<?php

namespace App\Http\Controllers\account;

use App\ArtikelKomentar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Moderasi komentar blog.
 *
 * Layar ini TIDAK PERNAH ADA. Pengendalinya sudah ditulis sejak lama tetapi
 * berkasnya bernama "ArtikelKomentarController.php.php" — berakhiran ganda,
 * jadi kelasnya tidak pernah bisa dimuat dan nol rute menunjuknya. Kuerinya
 * pun salah: ia menggabung tabel bernama `artikel_id` yang tidak ada, lalu
 * memulangkan view daftar artikel, bukan daftar komentar.
 *
 * Akibatnya, sampai 22 Okt 2026 komentar yang masuk tidak bisa dihapus dari
 * layar mana pun di aplikasi ini. Terukur: satu-satunya komentar yang ada
 * di basis data berjudul "Call Center Agoda Indonesia".
 */
class ArtikelKomentarController extends Controller
{
    /** Status yang boleh disetel dari layar ini. */
    private const STATUS = ['tertahan', 'terbit', 'sampah'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
     * Grup rute account/ TIDAK memakai middleware peran — tiap pengendali
     * memeriksa sendiri. Yang lupa memanggil boleh() terbuka untuk pelanggan
     * biasa yang kebetulan menebak alamatnya.
     */
    private function boleh(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    private function tolak()
    {
        return redirect()->route('account.dashboard.index')
            ->with('error', 'Anda tidak punya akses ke moderasi komentar.');
    }

    public function index(Request $request)
    {
        if (! $this->boleh()) {
            return $this->tolak();
        }

        $status = $request->input('status', 'tertahan');

        if (! in_array($status, self::STATUS, true)) {
            $status = 'tertahan';
        }

        $cari = trim((string) $request->input('cari'));

        $kueri = DB::table('artikel_komentar')
            ->select(
                'artikel_komentar.id',
                'artikel_komentar.nama',
                'artikel_komentar.email',
                'artikel_komentar.link_website',
                'artikel_komentar.komentar',
                'artikel_komentar.status',
                'artikel_komentar.reply',
                'artikel_komentar.created_at',
                'artikel_komentar.artikel_id',
                'artikel.judul as judul_artikel',
                'users.full_name as nama_akun',
            )
            // leftJoin, bukan join: komentar TAMU tidak punya user_id, dan
            // join biasa akan membuangnya diam-diam dari layar moderasi —
            // justru komentar yang paling perlu dilihat.
            ->leftJoin('artikel', 'artikel_komentar.artikel_id', '=', 'artikel.id')
            ->leftJoin('users', 'artikel_komentar.user_id', '=', 'users.id')
            ->where('artikel_komentar.status', $status)
            ->orderBy('artikel_komentar.created_at', 'DESC');

        if ($cari !== '') {
            $pola = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari) . '%';

            $kueri->where(function ($q) use ($pola) {
                $q->where('artikel_komentar.nama', 'like', $pola)
                    ->orWhere('artikel_komentar.email', 'like', $pola)
                    ->orWhere('artikel_komentar.komentar', 'like', $pola);
            });
        }

        $komentar = $kueri->paginate(20)->withQueryString();

        // Jumlah per status untuk tab di atas daftar; dihitung dari SELURUH
        // tabel, bukan dari halaman yang sedang dibuka.
        $jumlah = DB::table('artikel_komentar')
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('account.artikel_komentar.index', compact('komentar', 'status', 'cari', 'jumlah'));
    }

    /**
     * Mengganti status satu komentar.
     *
     * `status` disetel lewat forceFill, BUKAN lewat fillable: kolom itu
     * sengaja tidak ada di $fillable model supaya tidak pernah bisa ikut
     * terisi dari kiriman borang publik.
     */
    public function status(Request $request, $id)
    {
        if (! $this->boleh()) {
            return $this->tolak();
        }

        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUS)],
        ]);

        $komentar = ArtikelKomentar::findOrFail($id);
        $komentar->forceFill(['status' => $data['status']])->save();

        $kata = [
            'terbit' => 'diterbitkan',
            'tertahan' => 'ditahan lagi',
            'sampah' => 'ditandai sampah',
        ][$data['status']];

        return redirect()->back()->with('success', 'Komentar ' . $kata . '.');
    }

    public function destroy($id)
    {
        if (! $this->boleh()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak punya akses.'], 403);
        }

        $komentar = ArtikelKomentar::findOrFail($id);

        /*
         * Balasannya ikut dihapus. Dibiarkan, balasan itu jadi yatim:
         * tampilan publik mencarinya lewat kolom `reply` yang menunjuk
         * komentar yang sudah tidak ada, jadi ia tidak pernah tergambar lagi
         * tetapi tetap menumpuk di tabel selamanya.
         */
        $ikut = ArtikelKomentar::where('reply', $komentar->id)->count();

        ArtikelKomentar::where('reply', $komentar->id)->delete();
        $komentar->delete();

        // JSON, mengikuti pola hapus layar galeri: tombolnya memakai
        // pembungkus konfirmasi bersama window.misKonfirmasi lalu membuang
        // barisnya sendiri tanpa memuat ulang halaman.
        return response()->json([
            'success' => true,
            'message' => $ikut > 0
                ? 'Komentar dihapus beserta ' . $ikut . ' balasannya.'
                : 'Komentar dihapus.',
        ]);
    }
}
