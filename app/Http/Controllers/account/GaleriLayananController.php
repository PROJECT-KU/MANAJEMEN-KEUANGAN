<?php

namespace App\Http\Controllers\account;

use App\GaleriLayanan;
use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Layanan;
use App\Services\Gambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Galeri foto per layanan.
 *
 * Yang diunggah di sini langsung diubah jadi WebP dan disimpan di storage;
 * berkas aslinya dihapus. Seluruh aturan itu ada di App\Services\Gambar, dan
 * pengendali ini tidak pernah menyentuh berkas sendiri — satu pintu, supaya
 * tidak ada jalan yang lupa mengonversi.
 */
class GaleriLayananController extends Controller
{
    public function __construct(private Gambar $gambar)
    {
        $this->middleware('auth');
    }

    private function boleh(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    private function tolak()
    {
        return redirect()->route('account.dashboard.index')
            ->with('error', 'Anda tidak punya akses ke galeri layanan.');
    }

    public function index(Request $request)
    {
        if (! $this->boleh()) {
            return $this->tolak();
        }

        $katalog = Layanan::katalog();

        /*
         * Webinar Eksklusif jadi bawaannya. Layar ini dibuat untuk layanan itu
         * lebih dulu — itu yang mendesak — dan membuka layar galeri pada
         * layanan yang belum punya foto satu pun cuma memperlihatkan keadaan
         * kosong.
         */
        $layanan = array_key_exists((string) $request->query('layanan'), $katalog)
            ? $request->query('layanan')
            : (array_key_exists('webinar_eksklusif', $katalog) ? 'webinar_eksklusif' : array_key_first($katalog));

        $foto = GaleriLayanan::layanan($layanan)->get();

        return view('account.galeri_layanan.index', [
            // Layar berterus terang soal HEIC, bukan membiarkan orang mencoba
            // lalu kehilangan fotonya tanpa penjelasan.
            'bisaHeic' => $this->gambar->bisaHeic(),
            'layanan' => $layanan,
            'katalog' => $katalog,
            'foto' => $foto,
            // Untuk menempelkan foto ke satu sesi tertentu. Hanya angkatan
            // layanan ini, dan yang terbaru lebih dulu — itu yang baru saja
            // selesai dan dokumentasinya sedang diunggah.
            'angkatan' => KategoriLayanan::where('layanan', $layanan)
                ->orderByDesc('mulai')->limit(50)->get(),
            'jumlahHilang' => $foto->filter->berkas_hilang->count(),
        ]);
    }

    /**
     * Mengunggah beberapa foto sekaligus.
     *
     * Berkas yang gagal dibaca DILEWATI, bukan membatalkan seluruh unggahan:
     * memilih dua belas foto lalu kehilangan semuanya karena satu di antaranya
     * rusak adalah cara tercepat membuat orang berhenti memakai layar ini.
     */
    public function store(Request $request)
    {
        if (! $this->boleh()) {
            return $this->tolak();
        }

        $data = $request->validate([
            'layanan' => ['required', Rule::in(array_keys(Layanan::katalog()))],
            'kategori_id' => ['nullable', 'uuid'],
            'berkas' => ['required', 'array', 'min:1', 'max:20'],
            /*
             * 'file', BUKAN 'image'. Aturan 'image' memakai getimagesize(),
             * dan getimagesize() tidak mengenal HEIC sama sekali — foto iPhone
             * akan ditolak sebelum sempat dikonversi.
             *
             * Penjagaannya dipindah, bukan dihilangkan: ekstensinya dibatasi
             * daftar tertutup di bawah, dan isinya tetap harus terbaca sebagai
             * gambar oleh App\Services\Gambar — kalau tidak, berkasnya
             * dilewati dan tidak ada baris yang dibuat.
             */
            'berkas.*' => ['file', 'mimes:jpeg,jpg,png,webp,heic,heif', 'max:30720'],
            'keterangan' => ['nullable', 'string', 'max:160'],
        ], [
            'berkas.required' => 'Pilih dulu fotonya.',
            'berkas.max' => 'Maksimal 20 foto sekali unggah.',
            'berkas.*.mimes' => 'Yang bisa diunggah: JPG, PNG, WebP, atau HEIC.',
            // 30 MB: foto HEIC dari iPhone terbaru bisa 10-15 MB sebelum
            // dikonversi, dan batas 8 MB menolaknya tanpa alasan yang masuk
            // akal bagi pengunggahnya.
            'berkas.*.max' => 'Ada foto yang lebih besar dari 30 MB.',
        ]);

        // Angkatan yang ditunjuk harus memang milik layanan yang sama; kalau
        // tidak, fotonya tidak akan pernah muncul di mana pun.
        $kategoriId = null;

        if (! empty($data['kategori_id'])) {
            $kategoriId = KategoriLayanan::whereKey($data['kategori_id'])
                ->where('layanan', $data['layanan'])->value('id');
        }

        $masuk = 0;
        $gagal = 0;

        foreach ($request->file('berkas') as $berkas) {
            $jalur = $this->gambar->simpan($berkas, GaleriLayanan::folder($data['layanan']));

            if ($jalur === null) {
                $gagal++;

                continue;
            }

            GaleriLayanan::create([
                'layanan' => $data['layanan'],
                'kategori_id' => $kategoriId,
                'berkas' => $jalur,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $masuk++;
        }

        $pesan = $masuk . ' foto masuk galeri dan sudah diubah jadi WebP.';

        if ($gagal > 0) {
            $pesan .= ' ' . $gagal . ' dilewati karena berkasnya tidak terbaca';

            // Sebabnya disebut kalau memang itu. "Tidak terbaca" saja membuat
            // orang mengunggah ulang berkas yang sama berkali-kali.
            $pesan .= $this->gambar->bisaHeic()
                ? '.'
                : ' — peladen ini belum bisa membaca HEIC, jadi ubah dulu ke JPG.';
        }

        return redirect()->route('account.galeri-layanan.index', ['layanan' => $data['layanan']])
            ->with($masuk > 0 ? 'success' : 'error', $pesan);
    }

    public function update(Request $request, GaleriLayanan $galeri)
    {
        if (! $this->boleh()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        $data = $request->validate([
            'keterangan' => ['nullable', 'string', 'max:160'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $galeri->fill(array_filter($data, fn ($v) => $v !== null))->save();

        return response()->json(['success' => true, 'message' => 'Foto diperbarui.']);
    }

    public function destroy(GaleriLayanan $galeri)
    {
        if (! $this->boleh()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        /*
         * Berkasnya dibuang SESUDAH barisnya terhapus, dan hanya kalau tidak
         * ada baris lain yang menunjuk berkas yang sama — satu foto boleh
         * dipakai dua layanan, dan menghapusnya akan mengosongkan keduanya.
         */
        $berkas = $galeri->berkas;
        $galeri->delete();

        if (! GaleriLayanan::where('berkas', $berkas)->exists()) {
            $this->gambar->buang($berkas);
        }

        return response()->json(['success' => true, 'message' => 'Foto dihapus.']);
    }
}
