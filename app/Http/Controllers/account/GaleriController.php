<?php

namespace App\Http\Controllers\account;

use App\Galeri;
use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Layanan;
use App\Services\Gambar;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
class GaleriController extends Controller
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
         * Saringan layanan, dan bawaannya SEMUA. Galerinya kini dipakai
         * bersama lintas layanan, jadi membuka layar ini pada satu layanan
         * saja menyembunyikan sebagian besar isinya tanpa ada yang
         * memberitahu.
         */
        $layanan = array_key_exists((string) $request->query('layanan'), $katalog)
            ? $request->query('layanan')
            : null;

        $foto = Galeri::layanan($layanan)->get();

        return view('account.galeri.index', [
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
            // Larik, bukan satu nilai: satu foto boleh dipakai beberapa
            // layanan sekaligus.
            'layanan' => ['nullable', 'array'],
            'layanan.*' => [Rule::in(array_keys(Layanan::katalog()))],
            'semua_layanan' => ['nullable', 'boolean'],
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
            'layanan.*.in' => 'Ada layanan yang tidak dikenal.',
            'berkas.max' => 'Maksimal 20 foto sekali unggah.',
            'berkas.*.mimes' => 'Yang bisa diunggah: JPG, PNG, WebP, atau HEIC.',
            // 30 MB: foto HEIC dari iPhone terbaru bisa 10-15 MB sebelum
            // dikonversi, dan batas 8 MB menolaknya tanpa alasan yang masuk
            // akal bagi pengunggahnya.
            'berkas.*.max' => 'Ada foto yang lebih besar dari 30 MB.',
        ]);

        $semua = (bool) ($data['semua_layanan'] ?? false);
        $pilihan = $semua ? [] : array_values($data['layanan'] ?? []);

        /*
         * Harus terpilih SESUATU. Tanpa penjagaan ini, foto yang diunggah
         * tanpa mencentang apa pun tersimpan rapi tetapi tidak pernah muncul
         * di halaman mana pun — dan yang mengunggahnya tidak punya petunjuk
         * kenapa.
         */
        if (! $semua && $pilihan === []) {
            return back()->withInput()
                ->with('error', 'Pilih dulu layanannya, atau centang "Semua layanan".');
        }

        /*
         * Angkatan yang ditunjuk harus memang milik salah satu layanan yang
         * dipilih; kalau tidak, fotonya tidak akan pernah muncul di mana pun.
         * Saat "semua layanan" dicentang, angkatan mana pun boleh.
         */
        $kategoriId = null;

        if (! empty($data['kategori_id'])) {
            $kategoriId = KategoriLayanan::whereKey($data['kategori_id'])
                ->when(! $semua, fn ($q) => $q->whereIn('layanan', $pilihan))
                ->value('id');
        }

        $masuk = 0;
        $gagal = 0;

        foreach ($request->file('berkas') as $berkas) {
            $jalur = $this->gambar->simpan($berkas, Galeri::FOLDER);

            if ($jalur === null) {
                $gagal++;

                continue;
            }

            $foto = Galeri::create([
                'berkas' => $jalur,
                'keterangan' => $data['keterangan'] ?? null,
                'semua_layanan' => $semua,
                'kategori_id' => $kategoriId,
            ]);

            $foto->setLayanan($pilihan);

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

        return redirect()->route('account.galeri.index')
            ->with($masuk > 0 ? 'success' : 'error', $pesan);
    }

    public function update(Request $request, Galeri $galeri)
    {
        if (! $this->boleh()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        $data = $request->validate([
            'keterangan' => ['nullable', 'string', 'max:160'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['nullable', 'boolean'],
            'semua_layanan' => ['nullable', 'boolean'],
            'layanan' => ['nullable', 'array'],
            'layanan.*' => [Rule::in(array_keys(Layanan::katalog()))],
        ]);

        $galeri->fill(array_filter(
            Arr::only($data, ['keterangan', 'urutan', 'aktif', 'semua_layanan']),
            fn ($v) => $v !== null
        ))->save();

        // Daftar layanannya hanya disentuh kalau memang dikirim; isian lain
        // dikirim sendiri-sendiri saat ditinggalkan, dan menimpanya dengan
        // larik kosong akan mencabut seluruh layanan foto itu.
        if ($request->has('layanan')) {
            $galeri->setLayanan($data['layanan'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto diperbarui.',
            'sebut' => $galeri->fresh()->sebut_layanan,
        ]);
    }

    public function destroy(Galeri $galeri)
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

        if (! Galeri::where('berkas', $berkas)->exists()) {
            $this->gambar->buang($berkas);
        }

        return response()->json(['success' => true, 'message' => 'Foto dihapus.']);
    }
}
