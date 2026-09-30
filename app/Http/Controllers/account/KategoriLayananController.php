<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Layanan;
use App\Support\PerakitDeskripsi;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Angkatan (kategori) seluruh layanan jasa — satu layar untuk semuanya.
 *
 * Sebelumnya tiap layanan punya layarnya sendiri dengan borang yang isinya
 * sama persis, dan deskripsi angkatan diketik ulang dari nol setiap kali
 * meskipun yang berganti cuma nomor, tanggal, dan harga.
 *
 * Di sini harga, fasilitas, kegiatan, dan kontak datang dari tarif induk, dan
 * deskripsinya dirakit. Yang benar-benar diketik admin tinggal nama, nomor,
 * tanggal, dan kuota.
 */
class KategoriLayananController extends Controller
{
    private const PER_HALAMAN = 10;

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function bolehMelihat(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    private function bolehMengubah(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    private function tolak()
    {
        return redirect()->route('account.dashboard.index')
            ->with('error', 'Anda tidak punya akses ke angkatan layanan.');
    }

    // ------------------------------------------------------------- daftar

    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $layanan = $request->query('layanan');
        $status = $request->query('status');
        $cari = trim((string) $request->query('cari'));

        $kueri = KategoriLayanan::query();

        if ($layanan && array_key_exists($layanan, Layanan::katalog())) {
            $kueri->where('layanan', $layanan);
        }

        if ($status) {
            $kueri->where('status', $status);
        }

        if ($cari !== '') {
            $kueri->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nama_ke', 'like', "%{$cari}%")
                    ->orWhere('lokasi', 'like', "%{$cari}%");
            });
        }

        $angkatan = $kueri->orderByDesc('mulai')->paginate(self::PER_HALAMAN)->withQueryString();

        /*
         * Jumlah per layanan dihitung sekali dengan satu kueri, bukan satu
         * kueri per lencana: lencananya sebanyak layanan yang ada, dan
         * jumlahnya akan bertambah.
         */
        $jumlah = KategoriLayanan::select('layanan', DB::raw('count(*) as n'))
            ->groupBy('layanan')->pluck('n', 'layanan');

        return view('account.kategori_layanan.index', [
            'angkatan' => $angkatan,
            'jumlah' => $jumlah,
            'layanan' => $layanan,
            'status' => $status,
            'cari' => $cari,
            'bolehUbah' => $this->bolehMengubah(),
            'katalog' => Layanan::katalog(),
        ]);
    }

    // -------------------------------------------------------------- borang

    public function create(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        return view('account.kategori_layanan.form', [
            'angkatan' => new KategoriLayanan(['layanan' => $request->query('layanan', 'scopus_camp')]),
            'sunting' => false,
            'katalog' => Layanan::katalog(),
            'tarifPer' => $this->tarifPerLayanan(),
        ]);
    }

    public function edit(KategoriLayanan $angkatan)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        return view('account.kategori_layanan.form', [
            'angkatan' => $angkatan,
            'sunting' => true,
            'katalog' => Layanan::katalog(),
            'tarifPer' => $this->tarifPerLayanan(),
        ]);
    }

    /**
     * Harga dan fasilitas tiap layanan, dikirim ke layar supaya borangnya bisa
     * mengisi dirinya sendiri tanpa menunggu peladen.
     *
     * @return array<string, array<string, mixed>>
     */
    private function tarifPerLayanan(): array
    {
        $hasil = [];
        $berlaku = ClinikScopusBiayaPersesi::semuaYangBerlaku();

        foreach (Layanan::katalog() as $kunci => $tentang) {
            $varian = $tentang['varian'] ?: [null => null];

            foreach ($varian as $kodeVarian => $namaVarian) {
                $tarif = $berlaku[$kunci . '|' . ($kodeVarian ?: '')] ?? null;

                $hasil[$kunci . '|' . ($kodeVarian ?: '')] = [
                    'biaya' => $tarif ? (int) $tarif->biaya_persesi : null,
                    'adaCetakan' => (bool) $tarif?->ada_cetakan,
                    'fasilitas' => $tarif?->daftar_fasilitas ?? [],
                ];
            }
        }

        return $hasil;
    }

    // ------------------------------------------------------------ simpanan

    public function store(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $data = $this->periksa($request);

        $data['token'] = Str::random(30);

        $angkatan = KategoriLayanan::create($data);

        return redirect()->route('account.kategori-layanan.index', ['layanan' => $angkatan->layanan])
            ->with('success', 'Angkatan ' . $angkatan->nama . ' tersimpan.');
    }

    public function update(Request $request, KategoriLayanan $angkatan)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $angkatan->update($this->periksa($request, $angkatan));

        return redirect()->route('account.kategori-layanan.index', ['layanan' => $angkatan->layanan])
            ->with('success', 'Angkatan ' . $angkatan->nama . ' diperbarui.');
    }

    /** @return array<string, mixed> */
    private function periksa(Request $request, ?KategoriLayanan $angkatan = null): array
    {
        $data = $request->validate([
            'layanan' => ['required', Rule::in(array_keys(Layanan::katalog()))],
            'varian' => ['nullable', 'string', 'max:40'],
            'nama' => ['required', 'string', 'max:255'],
            'nama_ke' => ['nullable', 'string', 'max:20'],
            'mulai' => ['required', 'date'],
            'selesai' => ['nullable', 'date', 'after_or_equal:mulai'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'total_kuota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'sisa_kuota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            // Harga, harga promo, dan kode promo TIDAK lagi diketik di sini.
            // Harga memotret tarif induk; promo jadi fiturnya sendiri nanti.
            'ikuti_tarif' => ['nullable', 'boolean'],
            'group_wa' => ['nullable', 'string', 'max:255'],
            'desc' => ['nullable', 'string', 'max:20000'],
            'status' => ['required', Rule::in(['active', 'non active', 'draft'])],
        ], [
            'nama.required' => 'Isi dulu nama angkatannya.',
            'mulai.required' => 'Isi dulu tanggal mulainya.',
            'selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        /*
         * validate() TIDAK memuat kunci yang tidak dikirim peramban, dan
         * borang tambah memang tidak punya medan sisa_kuota. Dibaca langsung,
         * $data['sisa_kuota'] melempar "Undefined array key" — galat yang
         * hanya muncul saat menambah, tidak saat menyunting, jadi gampang
         * lolos dari pemeriksaan manual.
         *
         * Semua medan pilihan dinolkan di satu tempat supaya sisanya boleh
         * dibaca apa adanya.
         */
        $data = array_merge(array_fill_keys([
            'varian', 'nama_ke', 'selesai', 'lokasi', 'total_kuota', 'sisa_kuota',
            'group_wa', 'desc',
        ], null), $data);

        $tentang = Layanan::katalog()[$data['layanan']];
        $varian = $data['varian'] ?: null;

        if ($varian !== null && ! array_key_exists($varian, $tentang['varian'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'varian' => 'Varian itu tidak ada pada layanan tersebut.',
            ]);
        }

        if ($varian === null && $tentang['varian'] !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'varian' => 'Layanan ini harus punya varian.',
            ]);
        }

        $data['varian'] = $varian;

        $this->hargakan($data, $request, $angkatan, $data['layanan'], $varian);

        /*
         * Sisa kuota mengikuti total HANYA saat angkatannya baru. Pada
         * angkatan yang sudah berjalan, sisanya sudah berkurang karena ada
         * yang mendaftar; menimpanya akan membuka kuota yang sebenarnya habis.
         */
        if ($angkatan === null && $data['sisa_kuota'] === null) {
            $data['sisa_kuota'] = $data['total_kuota'];
        }

        return $data;
    }

    /**
     * Menentukan harga angkatan tanpa satu pun isian harga.
     *
     * Angkatan BARU memotret tarif yang berlaku saat itu. Angkatan yang SUDAH
     * ADA mempertahankan harganya sendiri — peserta yang sudah mendaftar
     * membayar harga yang dijanjikan saat itu, dan menyunting tanggal atau
     * kuota tidak boleh diam-diam menaikkannya. Dari 48 angkatan Scopus Camp
     * yang ada, 25 di antaranya berharga 4,5jt sementara tarif sekarang 5,5jt.
     *
     * Satu-satunya jalan mengubah harga angkatan lama adalah mencentang
     * "ikuti tarif sekarang", dan centang itu hanya muncul kalau harganya
     * memang sudah berbeda.
     *
     * Harga promo dan kode promo TIDAK disentuh sama sekali di sini. Keduanya
     * akan jadi fiturnya sendiri; sampai itu ada, nilai yang sudah tersimpan
     * dibiarkan utuh — 12 angkatan sudah punya promo, dan menghapusnya lewat
     * penyuntingan biasa berarti kehilangan data tanpa ada yang meminta.
     *
     * @param  array<string, mixed>  $data
     */
    private function hargakan(array &$data, Request $request, ?KategoriLayanan $angkatan, string $layanan, ?string $varian): void
    {
        $tarif = ClinikScopusBiayaPersesi::berlaku($layanan, $varian);

        if ($angkatan === null) {
            $harga = $tarif ? (string) (int) $tarif->biaya_persesi : null;

            $data['biaya'] = $harga;
            // Tanpa promo, total sama dengan biayanya — bukan kosong, karena
            // itulah angka yang dipakai halaman publik dan laporan.
            $data['total_biaya'] = $harga;

            return;
        }

        if ($request->boolean('ikuti_tarif') && $tarif) {
            $harga = (string) (int) $tarif->biaya_persesi;

            $data['biaya'] = $harga;

            // Promo yang lama dihitung dari harga lama, jadi ikut disetarakan;
            // kalau tidak, "promo" bisa jadi lebih mahal daripada harganya.
            $data['total_biaya'] = $harga;
            $data['kode_diskon'] = null;

            return;
        }

        // Tidak disebut sama sekali = tidak diubah. Dibiarkan ada di $data
        // sebagai null, update() akan mengosongkannya.
        unset($data['biaya'], $data['total_biaya'], $data['kode_diskon']);
    }

    // -------------------------------------------------------------- perakit

    /**
     * Merakit deskripsi dari isian yang SEDANG diketik, belum disimpan.
     *
     * Dirakit di peladen, bukan di peramban, supaya aturannya cuma ada satu.
     * Disalin ke JavaScript, format tanggal dan aturan baris kosongnya pasti
     * berselisih dengan yang dipakai saat menyimpan.
     */
    public function rakit(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        $sementara = new KategoriLayanan($request->only([
            'layanan', 'varian', 'nama', 'nama_ke', 'mulai', 'selesai',
            'lokasi', 'total_kuota', 'sisa_kuota', 'group_wa', 'kode_diskon',
        ]));

        foreach (['biaya', 'total_biaya'] as $k) {
            $sementara->$k = $request->filled($k)
                ? (string) (int) preg_replace('/\D+/', '', (string) $request->input($k))
                : null;
        }

        $teks = PerakitDeskripsi::rakit($sementara);

        return response()->json([
            'success' => $teks !== '',
            'deskripsi' => $teks,
            'message' => $teks !== ''
                ? 'Deskripsi dirakit dari cetakan layanan.'
                : 'Layanan ini belum punya cetakan deskripsi. Isi dulu di Tarif layanan.',
        ]);
    }

    // ----------------------------------------------------------- penghapusan

    public function destroy(KategoriLayanan $angkatan)
    {
        if (! $this->bolehMengubah()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        /*
         * Diperiksa lebih dulu, bukan dicoba lalu ditangkap: tabel pendaftaran
         * berkunci asing ke sini, dan MySQL menolaknya dengan galat 1451 yang
         * hanya menyebut nama constraint-nya.
         */
        $terpakai = DB::table('analisis_bibliometrik')->where('kategori_id', $angkatan->getKey())->count()
            + DB::table('scopus_camp_pendaftaran')->where('kategori_id', $angkatan->getKey())->count();

        if ($terpakai > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Angkatan ini sudah punya ' . $terpakai . ' pendaftar, jadi tidak bisa dihapus.',
            ], 409);
        }

        try {
            $angkatan->delete();
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Angkatan ini masih tertaut ke data lain.',
            ], 409);
        }

        return response()->json(['success' => true, 'message' => 'Angkatan dihapus.']);
    }
}
