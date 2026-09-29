<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $dasar = User::query()->where('peran', User::PERAN_PELANGGAN);

        // Ringkasan dihitung dari seluruh pelanggan, bukan dari halaman yang
        // sedang tampil — angka yang berubah tiap ganti halaman menyesatkan.
        $ringkasan = [
            'total' => (clone $dasar)->count(),
            'aktif' => (clone $dasar)->where('status', 'active')->count(),
            'terverifikasi' => (clone $dasar)->whereNotNull('email_verified_at')->count(),
            'baru' => (clone $dasar)->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $pelanggan = $dasar
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
            ->when($verifikasi === 'belum', fn ($q) => $q->whereNull('email_verified_at'))
            ->latest('created_at')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('account.customer.index', compact('pelanggan', 'ringkasan', 'cari', 'status', 'verifikasi'));
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

        return view('account.customer.edit', ['user' => $pelanggan]);
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
