<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\Support\PesananPelanggan;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Data pelanggan — orang luar yang memakai layanan jasa.
 *
 * Pelanggan dikenali dari PERAN-nya, bukan jabatannya. Sebelumnya layar ini
 * menyaring where('level','user'); sesudah peran dipisah dari jabatan, yang
 * benar adalah peran = user.
 */
class CustomerController extends Controller
{
    /** Satu tempat untuk jumlah baris per halaman, dipakai daftar dan pencarian. */
    private const PER_HALAMAN = 12;

    /**
     * Kolom yang boleh dipakai mengurutkan, beserta kolom basis datanya.
     *
     * Daftar putih, bukan nilai mentah dari kiriman: tanpa ini siapa pun bisa
     * mengurutkan lewat kolom apa pun — termasuk kolom yang tidak boleh
     * dilihatnya, seperti sidik kata sandi.
     */
    private const URUTAN = [
        'nama' => 'full_name',
        'bergabung' => 'created_at',
        'status' => 'status',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Hanya orang dalam yang boleh melihat data pelanggan. */
    private function bolehMelihat(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    /**
     * Daftar pelanggan.
     *
     * Penyaringnya sengaja tinggal tiga: kata kunci, status akun, dan status
     * verifikasi email. Yang lama menyediakan enam parameter (email persis,
     * tanggal mulai, tanggal akhir, jumlah per halaman) yang TIDAK punya
     * satu pun kendali di layarnya — jadi tidak pernah bisa dipakai siapa pun
     * kecuali dengan mengetik sendiri di bilah alamat.
     */
    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke data pelanggan.');
        }

        $cari = trim((string) $request->input('cari'));
        $status = $request->input('status');
        $verifikasi = $request->input('verifikasi');

        $urut = array_key_exists((string) $request->input('urut'), self::URUTAN)
            ? (string) $request->input('urut')
            : 'bergabung';
        $arah = $request->input('arah') === 'naik' ? 'asc' : 'desc';

        $dasar = User::query()->where('peran', User::PERAN_PELANGGAN);

        // Ringkasan dihitung dari seluruh pelanggan, bukan dari halaman yang
        // sedang tampil — angka yang berubah tiap ganti halaman menyesatkan.
        $ringkasan = [
            'total' => (clone $dasar)->count(),
            'aktif' => (clone $dasar)->where('status', 'active')->count(),
            'terverifikasi' => (clone $dasar)->whereNotNull('email_verified_at')->count(),
            'baru' => (clone $dasar)->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $pelanggan = $this->saring($dasar, $cari, $status, $verifikasi)
            ->orderBy(self::URUTAN[$urut], $arah)
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Jumlah pesanan dan tanggal terakhir untuk SELURUH halaman sekaligus,
        // bukan per baris — dua belas baris akan jadi puluhan kueri.
        $pesanan = PesananPelanggan::ringkas($pelanggan->getCollection());

        return view('account.customer.index', compact(
            'pelanggan', 'ringkasan', 'cari', 'status', 'verifikasi', 'urut', 'arah', 'pesanan'
        ));
    }

    /** Saringan yang sama dipakai daftar dan ekspor; ditulis sekali di sini. */
    private function saring($kueri, string $cari, $status, $verifikasi)
    {
        return $kueri
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    foreach (['full_name', 'username', 'email', 'telp'] as $kolom) {
                        $sub->orWhere($kolom, 'LIKE', '%' . $cari . '%');
                    }
                });
            })
            ->when($status === 'aktif', fn ($q) => $q->where('status', 'active'))
            ->when($status === 'nonaktif', fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('status')->orWhere('status', '!=', 'active');
            }))
            ->when($verifikasi === 'sudah', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($verifikasi === 'belum', fn ($q) => $q->whereNull('email_verified_at'));
    }

    /**
     * Halaman satu pelanggan.
     *
     * Dicari lewat uuid, bukan id berurut: id yang berurut membuat siapa pun
     * yang punya satu tautan bisa menebak tautan pelanggan lain hanya dengan
     * menambah satu.
     */
    public function edit(User $pelanggan)
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke data pelanggan.');
        }

        abort_unless($pelanggan->adalahPelanggan(), 404);

        return view('account.customer.edit', [
            'user' => $pelanggan,
            'pesanan' => PesananPelanggan::untuk($pelanggan),
            // Jejak perubahan memakai tabel yang sama dengan riwayat keamanan
            // profil; polanya sudah ada, jadi tidak perlu tabel baru.
            'jejak' => \App\AktivitasMasuk::where('user_id', $pelanggan->getKey())
                ->latest('id')->take(15)->get(),
        ]);
    }

    /**
     * Mengunduh daftar pelanggan sebagai PDF.
     *
     * Memakai saringan yang sedang dipakai di layar, bukan seluruh tabel: yang
     * diunduh orang hampir selalu yang sedang dilihatnya. Batasnya tidak
     * dipotong per halaman — mengunduh satu halaman dari sembilan tidak ada
     * gunanya.
     */
    public function ekspor(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke data pelanggan.');
        }

        $cari = trim((string) $request->input('cari'));
        $status = $request->input('status');
        $verifikasi = $request->input('verifikasi');

        $pelanggan = $this->saring(User::query()->where('peran', User::PERAN_PELANGGAN), $cari, $status, $verifikasi)
            ->orderBy('full_name')
            ->get();

        $html = view('account.customer.ekspor-pdf', [
            'pelanggan' => $pelanggan,
            'pesanan' => PesananPelanggan::ringkas($pelanggan),
            'saringan' => array_filter([
                'Kata kunci' => $cari !== '' ? $cari : null,
                'Status akun' => $status ? ($status === 'aktif' ? 'Aktif' : 'Nonaktif') : null,
                'Email' => $verifikasi ? ($verifikasi === 'sudah' ? 'Sudah diverifikasi' : 'Belum diverifikasi') : null,
            ]),
        ])->render();

        $dompdf = new Dompdf();
        $pengaturan = $dompdf->getOptions();
        $pengaturan->setIsPhpEnabled(true);
        $pengaturan->setIsRemoteEnabled(false);
        $dompdf->setOptions($pengaturan);
        $dompdf->loadHtml($html);
        // Mendatar: delapan kolom tidak muat tegak tanpa dimampatkan.
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $nama = 'data-pelanggan-' . now()->format('Ymd-His') . '.pdf';

        // response(), bukan $dompdf->stream(): stream() memanggil header() dan
        // echo sendiri sehingga kepalanya lewat dari lapisan respons Laravel.
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    /** Menghapus pelanggan; hanya administrator. */
    public function destroy(User $pelanggan)
    {
        if (! Auth::user()?->adalahAdministrator()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh menghapus pelanggan.',
            ], 403);
        }

        abort_unless($pelanggan->adalahPelanggan(), 404);

        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus.',
        ]);
    }
}
