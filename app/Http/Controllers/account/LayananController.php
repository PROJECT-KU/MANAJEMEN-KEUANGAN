<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Menambah dan mengubah layanan jasa.
 *
 * Katalognya dulu konstanta di kode, jadi layanan baru — misalnya sharing
 * session eksklusif yang berbayar — menunggu rilis. Sekarang admin
 * menambahnya sendiri, dan seluruh layar yang membaca katalog itu (tarif,
 * angkatan) langsung ikut tanpa disentuh.
 */
class LayananController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Menambah layanan mengubah apa yang dijual; administrator saja. */
    private function boleh(): bool
    {
        return (bool) Auth::user()?->adalahAdministrator();
    }

    public function store(Request $request)
    {
        if (! $this->boleh()) {
            return back()->with('error', 'Hanya administrator yang boleh menambah layanan.');
        }

        $data = $this->tanpaYangKosong($this->periksa($request));

        $layanan = new Layanan();
        // Kode dibuat sekali dari namanya, lalu tidak pernah berubah: nilai
        // inilah yang tersimpan di kolom `layanan` pada tarif dan angkatan.
        $layanan->kode = Layanan::kodeDari($data['nama']);
        $layanan->urutan = ((int) Layanan::max('urutan')) + 10;
        $layanan->fill($data);
        $layanan->setVarianDari($this->uraikanVarian($request->input('varian'), []));
        $layanan->save();

        return redirect()->route('account.Clinik-Scopus-Biaya-Persesi.index')
            ->with('success', 'Layanan ' . $layanan->nama . ' ditambahkan. Setel tarifnya sekarang.');
    }

    public function update(Request $request, Layanan $layanan)
    {
        if (! $this->boleh()) {
            return back()->with('error', 'Hanya administrator yang boleh mengubah layanan.');
        }

        $data = $this->tanpaYangKosong($this->periksa($request, $layanan));

        $baru = $this->uraikanVarian($request->input('varian'), $layanan->varian_peta);

        /*
         * Varian yang sudah dipakai tarif atau angkatan tidak boleh hilang.
         * Hilang, tarif dan angkatan yang menunjuknya jadi menggantung: tidak
         * terbaca oleh pencari tarif mana pun, dan tidak ada galat yang
         * memberitahu.
         */
        $terpakai = $layanan->varianTerpakai();
        $hilang = array_diff($terpakai, array_keys($baru ?? []));

        if ($hilang !== []) {
            $nama = array_map(fn ($k) => ($layanan->varian_peta[$k] ?? $k), $hilang);

            return back()->withInput()->withErrors([
                'varian' => 'Varian ' . implode(', ', $nama) . ' sudah dipakai tarif atau angkatan, '
                    . 'jadi belum bisa dibuang.',
            ]);
        }

        $layanan->fill($data);
        $layanan->setVarianDari($baru);
        $layanan->save();

        return redirect()->route('account.Clinik-Scopus-Biaya-Persesi.index')
            ->with('success', 'Layanan ' . $layanan->nama . ' diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        if (! $this->boleh()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh menghapus layanan.',
            ], 403);
        }

        /*
         * Diperiksa lebih dulu, bukan dicoba lalu ditangkap: tabel tarif dan
         * angkatan menyimpan KODE layanan, bukan kunci asing, jadi basis data
         * tidak akan menolak apa pun. Yang terjadi kalau dibiarkan: tarif dan
         * angkatan menggantung tanpa nama, diam-diam.
         */
        $tarif = $layanan->jumlah_tarif;
        $angkatan = $layanan->jumlah_angkatan;

        if ($tarif > 0 || $angkatan > 0) {
            $sebab = [];

            if ($tarif > 0) {
                $sebab[] = $tarif . ' baris tarif';
            }

            if ($angkatan > 0) {
                $sebab[] = $angkatan . ' angkatan';
            }

            return response()->json([
                'success' => false,
                'message' => 'Layanan ini masih punya ' . implode(' dan ', $sebab)
                    . '. Nonaktifkan saja kalau sudah tidak dijual — datanya tetap utuh.',
            ], 409);
        }

        $layanan->delete();

        return response()->json(['success' => true, 'message' => 'Layanan dihapus.']);
    }

    /** @return array<string, mixed> */
    private function periksa(Request $request, ?Layanan $layanan = null): array
    {
        return $request->validate([
            'nama' => [
                'required', 'string', 'max:120',
                Rule::unique('layanan', 'nama')->ignore($layanan?->getKey()),
            ],
            'satuan' => ['required', 'string', 'max:60'],
            // Ikon dan warna dari daftar tertutup: nama Font Awesome 6 tidak
            // merender apa pun TANPA galat, dan nilai dari basis data lolos
            // dari uji yang memindai berkas tampilan.
            'ikon' => ['required', Rule::in(array_keys(Layanan::IKON))],
            'warna' => ['required', Rule::in(array_keys(Layanan::WARNA))],
            'aktif' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'nama.required' => 'Isi dulu nama layanannya.',
            'nama.unique' => 'Sudah ada layanan dengan nama itu.',
            'satuan.required' => 'Isi dulu satuannya, misalnya "per peserta".',
            'ikon.in' => 'Pilih ikon dari daftar yang tersedia.',
        ]) + ['aktif' => $request->boolean('aktif')];
    }

    /**
     * Membuang kunci yang memang tidak dikirim.
     *
     * validate() tidak memuat kunci yang absen, tetapi `urutan` boleh kosong
     * dan dibiarkan apa adanya — bukan dinolkan, yang akan melempar layanan itu
     * ke urutan paling depan tanpa diminta.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function tanpaYangKosong(array $data): array
    {
        if (($data['urutan'] ?? null) === null) {
            unset($data['urutan']);
        }

        return $data;
    }

    /**
     * Mengubah daftar varian yang diketik jadi pasangan kode => nama.
     *
     * Kode varian yang SUDAH ADA dipertahankan selama namanya masih sama,
     * karena kode itulah yang tersimpan di tarif dan angkatan. Nama yang baru
     * dapat kode dari namanya sendiri.
     *
     * @param  array<string, string>  $lama
     * @return array<string, string>|null
     */
    private function uraikanVarian(?string $teks, array $lama): ?array
    {
        $baris = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $teks) ?: []),
            fn ($b) => $b !== ''
        ));

        if ($baris === []) {
            return null;
        }

        $petaLama = array_flip($lama);   // nama => kode
        $hasil = [];

        foreach ($baris as $nama) {
            $kode = $petaLama[$nama] ?? Str::slug($nama, '_');

            if ($kode === '') {
                continue;
            }

            $hasil[$kode] = $nama;
        }

        return $hasil === [] ? null : $hasil;
    }
}
