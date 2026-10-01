<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Exports\AngkatanLayananExport;
use App\Http\Controllers\Controller;
use App\AngkatanJejak;
use App\KategoriLayanan;
use App\Layanan;
use App\Support\PerakitDeskripsi;
use App\Support\TeksDariHtml;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
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

    /**
     * Kolom yang boleh dipakai mengurutkan, beserta namanya di layar.
     *
     * Daftar TERTUTUP: nilainya datang dari alamat, dan nama kolom sembarang
     * akan sampai ke orderBy apa adanya. Satu konstanta, bukan dua salinan —
     * layar dan unduhan pernah berselisih karena masing-masing punya
     * daftarnya sendiri.
     */
    private const BOLEH_URUT = [
        'mulai' => 'Tanggal mulai',
        'nama' => 'Nama angkatan',
        'sisa_kuota' => 'Sisa kuota',
        'status' => 'Status',
        'layanan' => 'Layanan',
        'biaya' => 'Biaya',
    ];

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

        // Daftar tertutup: nilainya datang dari alamat, dan scopePerlu()
        // mengabaikan yang tidak dikenal — tetapi menyimpannya di sini membuat
        // menu di layar ikut memilih yang benar.
        $perlu = array_key_exists((string) $request->query('perlu'), KategoriLayanan::PERLU)
            ? $request->query('perlu')
            : null;

        $periode = array_key_exists((string) $request->query('periode'), KategoriLayanan::PERIODE)
            ? $request->query('periode')
            : null;

        /*
         * Dua kueri, bukan satu: yang dasar memakai layanan dan kata kunci
         * saja, dan ubin ringkasan dihitung dari situ.
         *
         * Kalau ubinnya ikut menyaring status, angkanya berubah tiap kali
         * ubinnya ditekan — menekan "Aktif" membuat ubin "Draf" jadi 0, dan
         * jalan kembalinya hilang dari layar.
         */
        $kueriDasar = KategoriLayanan::query();

        if ($layanan && array_key_exists($layanan, Layanan::katalog())) {
            $kueriDasar->where('layanan', $layanan);
        }

        if ($cari !== '') {
            $kueriDasar->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nama_ke', 'like', "%{$cari}%")
                    ->orWhere('lokasi', 'like', "%{$cari}%");
            });
        }

        $kueriDasar->periode($periode);

        $kueri = clone $kueriDasar;

        if ($status) {
            $kueri->where('status', $status);
        }

        $kueri->perlu($perlu);

        /*
         * Pengurutan. Daftarnya semula selalu tanggal mulai terbaru, jadi
         * mencari angkatan terlama atau yang paling penuh berarti menggulung
         * seluruh enam halaman.
         *
         * Kolomnya dibatasi daftar tertutup: nilainya datang dari alamat, dan
         * nama kolom sembarang akan sampai ke orderBy apa adanya.
         */
        $bolehUrut = self::BOLEH_URUT;

        $urut = array_key_exists((string) $request->query('urut'), $bolehUrut)
            ? $request->query('urut')
            : 'mulai';

        $arah = $request->query('arah') === 'naik' ? 'asc' : 'desc';

        $angkatan = $kueri
            /*
             * sisa_kuota dan biaya sama-sama kolom TEKS; tanpa dicetak jadi
             * bilangan, "9" berdiri di atas "10" dan "Rp 999.000" di atas
             * "Rp 5.500.000" karena keduanya diurutkan sebagai huruf.
             */
            ->when(in_array($urut, ['sisa_kuota', 'biaya'], true),
                fn ($q) => $q->orderByRaw('CAST(' . $urut . ' AS UNSIGNED) ' . $arah),
                fn ($q) => $q->orderBy($urut, $arah))
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Dipakai lencana kuota, tautan pendaftar, dan penjaga kuota.
        KategoriLayanan::hitungPendaftar();

        /*
         * Jumlah per layanan dihitung sekali dengan satu kueri, bukan satu
         * kueri per lencana: lencananya sebanyak layanan yang ada, dan
         * jumlahnya akan bertambah.
         */
        $jumlah = KategoriLayanan::select('layanan', DB::raw('count(*) as n'))
            ->groupBy('layanan')->pluck('n', 'layanan');

        /*
         * Ringkasan per status, satu kueri berkelompok. Lencana layanan
         * menyebut totalnya — "Scopus Camp 48" tidak memberi tahu berapa yang
         * sedang berjalan, dan itu yang paling sering ditanya.
         */
        $perStatus = (clone $kueriDasar)->reorder()
            ->select('status', DB::raw('count(*) as n'))
            ->groupBy('status')->pluck('n', 'status');

        /*
         * Jumlah tiap keadaan yang perlu ditindaklanjuti, dihitung dari kueri
         * DASAR — tanpa saringan status maupun saringan ini sendiri. Kalau
         * ikut tersaring, menekan "Draf kadaluwarsa" membuat angka dua lainnya
         * jadi 0 dan jalan kembalinya hilang dari layar.
         */
        $jumlahPerlu = [];

        foreach (array_keys(KategoriLayanan::PERLU) as $jenis) {
            $jumlahPerlu[$jenis] = (clone $kueriDasar)->reorder()->perlu($jenis)->count();
        }

        // Baris UNIK, bukan jumlah ketiganya: satu angkatan bisa kena dua
        // keadaan sekaligus, dan menjumlahkannya membuat ubinnya menulis 47
        // dari 60 baris padahal yang benar jauh lebih sedikit.
        $totalPerlu = (clone $kueriDasar)->reorder()->perluApaPun()->count();

        return view('account.kategori_layanan.index', [
            'angkatan' => $angkatan,
            'jumlah' => $jumlah,
            'perStatus' => $perStatus,
            'layanan' => $layanan,
            'status' => $status,
            'cari' => $cari,
            'urut' => $urut,
            'arah' => $arah === 'asc' ? 'naik' : 'turun',
            // Kepala kolom pengurut memakai asc/desc; menu ponsel memakai
            // naik/turun. Keduanya dikirim daripada disulih di dalam Blade.
            'arahKode' => $arah,
            'bolehUrut' => $bolehUrut,
            'perlu' => $perlu,
            'periode' => $periode,
            'jumlahPerlu' => $jumlahPerlu,
            'totalPerlu' => $totalPerlu,
            'adaSaringan' => $cari !== '' || (bool) $status || (bool) $layanan
                || (bool) $perlu || (bool) $periode,
            'bolehUbah' => $this->bolehMengubah(),
            'katalog' => Layanan::katalog(),
        ]);
    }

    // ------------------------------------------------------------- ekspor

    /**
     * Daftar angkatan yang SEDANG disaring dan diurutkan di layar.
     *
     * Batasnya tidak dipotong per halaman — mengunduh sepuluh dari lima puluh
     * delapan tidak ada gunanya.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function untukEkspor(Request $request)
    {
        $layanan = $request->query('layanan');
        $status = $request->query('status');
        $cari = trim((string) $request->query('cari'));

        // Unduhan membawa saringan yang SEDANG dipakai, termasuk yang ini:
        // yang diunduh orang hampir selalu yang sedang dilihatnya.
        $perlu = $request->query('perlu');
        $periode = $request->query('periode');

        $kueri = KategoriLayanan::query()
            ->perlu($perlu)
            ->periode($periode)
            ->when($layanan && array_key_exists($layanan, Layanan::katalog()),
                fn ($q) => $q->where('layanan', $layanan))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($cari !== '', fn ($q) => $q->where(function ($w) use ($cari) {
                $w->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nama_ke', 'like', "%{$cari}%")
                    ->orWhere('lokasi', 'like', "%{$cari}%");
            }));

        /*
         * Daftar kolomnya harus SAMA dengan yang di layar. Sebelumnya di sini
         * cuma empat — tanpa 'layanan' dan 'biaya' — jadi mengurutkan daftar
         * menurut Biaya lalu menekan Unduh menghasilkan berkas yang urutannya
         * diam-diam kembali ke tanggal, dan tidak ada yang memberi tahu.
         */
        $urut = array_key_exists((string) $request->query('urut'), self::BOLEH_URUT)
            ? $request->query('urut')
            : 'mulai';

        $arah = $request->query('arah') === 'naik' ? 'asc' : 'desc';

        return $kueri
            ->when(in_array($urut, ['sisa_kuota', 'biaya'], true),
                fn ($q) => $q->orderByRaw('CAST(' . $urut . ' AS UNSIGNED) ' . $arah),
                fn ($q) => $q->orderBy($urut, $arah))
            ->get();
    }

    /**
     * Mengunduh daftar angkatan sebagai PDF.
     *
     * Layar kategori yang digantikan layar ini SUDAH punya unduhan PDF dan
     * Excel; menyatukannya tanpa keduanya berarti diam-diam mencabut
     * kemampuan yang sudah dipakai orang.
     */
    public function cetakPdf(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $angkatan = $this->untukEkspor($request);
        KategoriLayanan::hitungPendaftar();

        $html = view('account.kategori_layanan.cetak-pdf', [
            'angkatan' => $angkatan,
            'saringan' => $this->ringkasanSaringan($request),
        ])->render();

        $dompdf = new Dompdf();
        $pengaturan = $dompdf->getOptions();
        $pengaturan->setIsPhpEnabled(true);
        $pengaturan->setIsRemoteEnabled(false);
        $dompdf->setOptions($pengaturan);
        $dompdf->loadHtml($html);
        // Mendatar: sembilan kolom tidak muat tegak tanpa dimampatkan.
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $nama = 'angkatan-layanan-' . now()->format('Ymd-His') . '.pdf';

        // response(), bukan $dompdf->stream(): stream() memanggil header() dan
        // echo sendiri sehingga kepalanya lewat dari lapisan respons Laravel.
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    /** Mengunduh daftar angkatan sebagai Excel. */
    public function cetakExcel(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $angkatan = $this->untukEkspor($request);
        KategoriLayanan::hitungPendaftar();

        return Excel::download(
            new AngkatanLayananExport($angkatan),
            'angkatan-layanan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    /**
     * Kalimat yang menerangkan saringan yang sedang dipakai.
     *
     * Ditulis di berkasnya supaya yang menerimanya tahu ia sedang melihat
     * sebagian, bukan seluruhnya — daftar tersaring yang tampak lengkap itu
     * jenis kesalahan yang mahal.
     */
    private function ringkasanSaringan(Request $request): string
    {
        $bagian = [];

        if ($l = $request->query('layanan')) {
            $bagian[] = 'layanan ' . (Layanan::katalog()[$l]['nama'] ?? $l);
        }

        if ($s = $request->query('status')) {
            $bagian[] = 'status ' . (['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'][$s] ?? $s);
        }

        if ($c = trim((string) $request->query('cari'))) {
            $bagian[] = 'kata kunci "' . $c . '"';
        }

        // Rentang waktu ikut disebut dengan alasan yang sama seperti "perlu
        // dicek" di bawah: berkas berisi angkatan bulan depan saja tidak boleh
        // berkepala "tanpa saringan".
        if ($p = $request->query('periode')) {
            $bagian[] = 'periode ' . lcfirst(KategoriLayanan::PERIODE[$p] ?? $p);
        }

        /*
         * Saringan "perlu dicek" IKUT disebut.
         *
         * Tanpa ini, berkas yang dicetak sambil menyaring draf kadaluwarsa
         * tetap berkepala "Seluruh angkatan, tanpa saringan." — pernyataan
         * yang salah di dokumen yang mungkin diarsipkan orang, dan yang
         * membacanya tidak punya cara tahu bahwa isinya sudah dipersempit.
         */
        if ($p = $request->query('perlu')) {
            $bagian[] = 'hanya yang ' . mb_strtolower(KategoriLayanan::PERLU[$p] ?? $p);
        }

        return $bagian === [] ? 'Seluruh angkatan, tanpa saringan.' : 'Disaring: ' . implode(', ', $bagian) . '.';
    }

    // -------------------------------------------------------------- borang

    public function create(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $layanan = $request->query('layanan', 'scopus_camp');

        return view('account.kategori_layanan.form', [
            'angkatan' => new KategoriLayanan([
                'layanan' => $layanan,
                // Nomor berikutnya disarankan, bukan dipaksakan: admin
                // mengingat-ingat nomor terakhir kalau tidak, dan angka yang
                // dilompati baru ketahuan berbulan-bulan kemudian.
                'nama_ke' => $this->nomorAwal($layanan),
            ]),
            'sunting' => false,
            'baruDigandakan' => false,
            'katalog' => Layanan::katalog(),
            'tarifPer' => $this->tarifPerLayanan(),
            'sampulLazim' => KategoriLayanan::sampulLazim(),
            'nomorPerLokasi' => KategoriLayanan::nomorBerikutnyaPerLokasi(),
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
            'baruDigandakan' => request()->boolean('digandakan'),
            'katalog' => Layanan::katalog(),
            'tarifPer' => $this->tarifPerLayanan(),
            'sampulLazim' => KategoriLayanan::sampulLazim(),
            'nomorPerLokasi' => KategoriLayanan::nomorBerikutnyaPerLokasi(),
        ]);
    }

    /**
     * Harga dan fasilitas tiap layanan, dikirim ke layar supaya borangnya bisa
     * mengisi dirinya sendiri tanpa menunggu peladen.
     *
     * @return array<string, array<string, mixed>>
     */
    /**
     * Nomor angkatan berikutnya untuk satu layanan.
     *
     * Diambil dari nomor TERBESAR yang pernah dipakai, bukan dari jumlah
     * barisnya: angkatan yang dihapus akan membuat hitungan baris memberi
     * nomor yang sudah terpakai.
     */
    /**
     * Nomor yang disodorkan saat borang tambah baru dibuka.
     *
     * Dipakai kunci berlokasi KOSONG, sebab lokasinya memang belum diketik.
     * Untuk Scopus Camp tidak ada angkatan tanpa lokasi, jadi hasilnya 0 dan
     * nomornya baru terisi begitu lokasinya diketik; untuk Bibliometrik yang
     * angkatannya memang tidak berlokasi, deretnya tetap berjalan.
     */
    private function nomorAwal(string $layanan): string
    {
        return (string) (KategoriLayanan::nomorBerikutnyaPerLokasi()[$layanan . '|'] ?? 0);
    }

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

        // Sesudah barisnya ada: foldernya ditentukan layanannya.
        $berkas = [];

        if ($sampul = $this->simpanSampul($request, $angkatan, $data)) {
            $berkas['gambar'] = $sampul;
        }

        if ($foto = $this->simpanFotoPemateri($request, $angkatan)) {
            $berkas['pemateri_foto'] = $foto;
        }

        if ($berkas !== []) {
            $angkatan->forceFill($berkas)->save();
        }

        return redirect()->route('account.kategori-layanan.index', ['layanan' => $angkatan->layanan])
            ->with('success', 'Angkatan ' . $angkatan->nama . ' tersimpan.');
    }

    public function update(Request $request, KategoriLayanan $angkatan)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $data = $this->periksa($request, $angkatan);

        if ($sampul = $this->simpanSampul($request, $angkatan, $data)) {
            $data['gambar'] = $sampul;
        }

        if ($foto = $this->simpanFotoPemateri($request, $angkatan)) {
            $data['pemateri_foto'] = $foto;
        }

        $angkatan->update($data);

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
            /*
             * Acara daring. Semuanya boleh kosong — layanan luring tidak
             * memakainya sama sekali, dan memaksanya wajib berarti borang
             * Scopus Camp tidak bisa disimpan.
             */
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'platform' => ['nullable', 'string', 'max:60'],
            'pemateri' => ['nullable', 'string', 'max:120'],
            'pemateri_jabatan' => ['nullable', 'string', 'max:120'],
            'pemateri_foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'total_kuota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'sisa_kuota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            // Harga, harga promo, dan kode promo TIDAK lagi diketik di sini.
            // Harga memotret tarif induk; promo jadi fiturnya sendiri nanti.
            'ikuti_tarif' => ['nullable', 'boolean'],
            'group_wa' => ['nullable', 'string', 'max:255'],
            'desc' => ['nullable', 'string', 'max:20000'],
            'status' => ['required', Rule::in(['active', 'non active', 'draft'])],
            // Sampul: jenisnya dibatasi daftar tertutup. 4 MB cukup untuk
            // flyer; di atas itu halaman publiknya lambat dimuat pengunjung.
            'gambar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            /*
             * Sampul yang diwarisi dari angkatan lain di lokasi yang sama.
             *
             * Isinya jalur berkas yang datang dari peramban, jadi dibatasi
             * daftar tertutup berisi jalur yang MEMANG sudah dipakai angkatan
             * yang ada. Tanpa itu, siapa pun yang bisa mengirim borang ini
             * boleh menunjuk berkas mana saja di dalam public/.
             */
            'sampul_warisan' => ['nullable', 'string', Rule::in(
                array_column(KategoriLayanan::sampulLazim(), 'jalur')
            )],
        ], [
            'nama.required' => 'Isi dulu nama angkatannya.',
            'mulai.required' => 'Isi dulu tanggal mulainya.',
            'selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'jam_selesai.after' => 'Jam selesai harus sesudah jam mulai.',
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
            'group_wa', 'desc', 'sampul_warisan',
            'jam_mulai', 'jam_selesai', 'platform', 'pemateri', 'pemateri_jabatan',
        ], null), $data);

        // Berkasnya diurus simpanFoto(), bukan disimpan apa adanya: yang ada
        // di sini objek unggahan, dan menyimpannya ke kolom akan menulis
        // "Illuminate\Http\UploadedFile" ke basis data.
        unset($data['pemateri_foto']);

        /*
         * Deskripsi yang ditempel dari ChatGPT atau Word ikut membawa HTML-nya.
         * Halaman publik menampilkannya dengan {{ }}, jadi yang terbaca
         * pengunjung adalah tag-nya — empat angkatan Bibliometrik sudah
         * terlanjur begitu sebelum ini dipasang.
         *
         * Teks yang memang sudah datar dikembalikan utuh, jadi baris kosong
         * yang sengaja diketik admin tidak ikut dirapatkan.
         */
        $data['desc'] = TeksDariHtml::ubah($data['desc']) ?: null;

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
         * Kuota tidak boleh disetel di bawah jumlah yang SUDAH mendaftar.
         * Dibiarkan, angka sisanya jadi tidak berarti dan angkatan yang
         * sebenarnya penuh terlihat masih longgar — atau sebaliknya.
         */
        if ($angkatan !== null && $data['total_kuota'] !== null) {
            $pendaftar = $angkatan->jumlah_pendaftar;

            if ((int) $data['total_kuota'] < $pendaftar) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'total_kuota' => 'Sudah ada ' . $pendaftar . ' orang yang mendaftar, '
                        . 'jadi kuotanya tidak bisa kurang dari itu.',
                ]);
            }

            /*
             * Sisa kuota yang diketik juga harus masuk akal terhadap jumlah
             * orang yang sudah terdaftar. Tanpa ini, total 25 dengan 25 orang
             * terdaftar masih boleh diberi sisa 10, dan halaman publik akan
             * menerima sepuluh pendaftar lagi di atas kuota.
             */
            if ($data['sisa_kuota'] !== null
                && (int) $data['sisa_kuota'] + $pendaftar > (int) $data['total_kuota']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'sisa_kuota' => 'Dengan ' . $pendaftar . ' orang yang sudah mendaftar, '
                        . 'sisanya paling banyak ' . max(0, (int) $data['total_kuota'] - $pendaftar) . '.',
                ]);
            }
        }

        // Sisa tidak boleh melebihi totalnya; kalau tidak, angkatan bisa
        // menerima lebih banyak orang daripada yang disediakan.
        if ($data['total_kuota'] !== null && $data['sisa_kuota'] !== null
            && (int) $data['sisa_kuota'] > (int) $data['total_kuota']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sisa_kuota' => 'Sisa kuota tidak boleh lebih besar daripada total kuotanya.',
            ]);
        }

        /*
         * Nomor angkatan tidak boleh kembar dengan angkatan lain di layanan
         * dan lokasi yang sama. Nomor itu yang dipakai orang menyebutnya
         * ("Camp ke-188"), jadi dua yang bernomor sama membuat percakapan
         * admin dengan peserta jadi ambigu.
         *
         * Yang diperiksa hanya nomor yang BERUBAH. Memeriksa semuanya berarti
         * tujuh pasang kembar yang sudah terlanjur ada tidak bisa disunting
         * sama sekali — termasuk untuk memperbaiki nomornya sendiri. Yang
         * lama ditandai lencana "Perlu dicek" supaya tetap kelihatan.
         */
        $nomorBaru = trim((string) $data['nama_ke']);

        if ($nomorBaru !== '' && $nomorBaru !== trim((string) $angkatan?->nama_ke)) {
            $kembar = KategoriLayanan::query()
                ->where('layanan', $data['layanan'])
                ->whereRaw("coalesce(lokasi, '') = ?", [(string) $data['lokasi']])
                ->where('nama_ke', $nomorBaru)
                ->when($angkatan !== null, fn ($q) => $q->whereKeyNot($angkatan->getKey()))
                ->exists();

            if ($kembar) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nama_ke' => 'Angkatan ke-' . $nomorBaru . ' sudah ada'
                        . ($data['lokasi'] ? ' di ' . $data['lokasi'] : '')
                        . '. Pakai nomor lain.',
                ]);
            }
        }

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
    /**
     * Menyimpan sampul angkatan dan mengembalikan nama berkasnya.
     *
     * Halaman publik membaca sampul lewat basename(), jadi yang menentukan
     * FOLDER tempat berkasnya diletakkan — bukan awalan yang tersimpan. Nama
     * berkasnya UUID, bukan nama asli: nama unggahan bisa memuat spasi atau
     * apostrof, dan apostrof di nama berkas ditolak firewall hosting sebelum
     * PHP sempat jalan.
     */
    /**
     * Sampul yang diwarisi dari angkatan lain di layanan + lokasi yang sama.
     *
     * Berkasnya TIDAK disalin: empat puluh satu angkatan Yogyakarta memang
     * sudah menunjuk satu berkas yang sama, dan itu yang membuat mengganti
     * flyer cukup sekali untuk semuanya.
     */
    private function sampulWarisan(KategoriLayanan $angkatan, array $data, ?string $warisan): ?string
    {
        if (! $warisan) {
            return null;
        }

        // Angkatan yang sudah punya sampul tidak ditimpa diam-diam.
        if ($angkatan->gambar) {
            return null;
        }

        /*
         * Layanan dan lokasinya diambil dari $data, BUKAN dari modelnya.
         *
         * Saat menyunting, update() baru dijalankan sesudah ini — jadi model
         * yang dibaca di sini masih memegang lokasi LAMA. Admin yang mengubah
         * lokasi jadi Yogyakarta lalu menyimpan akan ditolak usulannya, dan
         * sebabnya tidak kelihatan dari mana pun.
         *
         * Pasangannya diperiksa ulang, bukan cuma jalurnya. Validator hanya
         * memastikan jalur itu ADA di daftar; tanpa pemeriksaan ini, borang
         * yang dikirim dengan layanan "bibliometrik" dan jalur flyer Scopus
         * Camp tetap diterima — dan sampulnya lalu dicari di folder
         * bibliometrik/, tempat berkas itu tidak ada.
         */
        $kunci = $data['layanan'] . '|' . mb_strtolower(trim((string) ($data['lokasi'] ?? '')));
        $lazim = KategoriLayanan::sampulLazim()[$kunci] ?? null;

        return $lazim && $lazim['jalur'] === $warisan ? $warisan : null;
    }

    private function simpanSampul(Request $request, KategoriLayanan $angkatan, array $data = []): ?string
    {
        if (! $request->hasFile('gambar')) {
            return $this->sampulWarisan($angkatan, $data, $data['sampul_warisan'] ?? null);
        }

        $berkas = $request->file('gambar');
        $folder = $angkatan->folderSampul();
        $tujuan = public_path($folder);

        if (! is_dir($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        $nama = (string) Str::uuid() . '.' . strtolower($berkas->getClientOriginalExtension());
        $berkas->move($tujuan, $nama);

        /*
         * Sampul lama dihapus SESUDAH yang baru tersimpan, dan hanya kalau
         * tidak ada angkatan lain yang memakainya — beberapa angkatan berbagi
         * satu flyer, dan menghapusnya akan mengosongkan gambar mereka juga.
         */
        if ($angkatan->gambar) {
            $lama = basename($angkatan->gambar);

            $dipakaiLain = KategoriLayanan::where('id', '!=', $angkatan->getKey())
                ->where('gambar', 'like', '%' . $lama)
                ->exists();

            $berkasLama = public_path($folder . '/' . $lama);

            if (! $dipakaiLain && is_file($berkasLama)) {
                @unlink($berkasLama);
            }
        }

        return $folder . '/' . $nama;
    }

    /**
     * Menyimpan foto pemateri dan mengembalikan jalur simpannya.
     *
     * Ditaruh di FOLDER yang sama dengan sampul layanan itu, bukan di folder
     * tersendiri: halaman publik membaca keduanya lewat basename() dari folder
     * yang ditentukan folderSampul(), dan dua folder berarti dua aturan.
     *
     * Nama berkasnya UUID berawalan "pemateri-", bukan nama asli. Nama
     * unggahan bisa memuat apostrof, dan apostrof di nama berkas ditolak
     * firewall hosting sebelum PHP sempat jalan.
     */
    private function simpanFotoPemateri(Request $request, KategoriLayanan $angkatan): ?string
    {
        if (! $request->hasFile('pemateri_foto')) {
            return null;
        }

        $berkas = $request->file('pemateri_foto');
        $folder = $angkatan->folderSampul();
        $tujuan = public_path($folder);

        if (! is_dir($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        $nama = 'pemateri-' . Str::uuid() . '.' . strtolower($berkas->getClientOriginalExtension());
        $berkas->move($tujuan, $nama);

        /*
         * Foto lama dihapus SESUDAH yang baru tersimpan, dan hanya kalau tidak
         * ada angkatan lain yang memakainya — satu pemateri biasa mengisi
         * beberapa sesi, dan fotonya memang dipakai bersama.
         */
        if ($angkatan->pemateri_foto) {
            $lama = basename($angkatan->pemateri_foto);

            $dipakaiLain = KategoriLayanan::where('id', '!=', $angkatan->getKey())
                ->where('pemateri_foto', 'like', '%' . $lama)
                ->exists();

            $berkasLama = public_path($folder . '/' . $lama);

            if (! $dipakaiLain && is_file($berkasLama)) {
                @unlink($berkasLama);
            }
        }

        return $folder . '/' . $nama;
    }

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

    /**
     * Menggandakan satu angkatan jadi rancangan baru.
     *
     * Ada 41 angkatan Yogyakarta yang isinya nyaris sama persis — nama,
     * lokasi, kuota, deskripsi — hanya tanggal dan nomornya berbeda, dan
     * semuanya diketik ulang satu per satu.
     *
     * Yang TIDAK ikut disalin: tanggal (acaranya belum ditentukan), sisa kuota
     * (belum ada yang mendaftar), dan statusnya — salinannya selalu draf,
     * supaya tidak ada angkatan yang terbit hanya karena tombol tertekan.
     */
    public function gandakan(KategoriLayanan $angkatan)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $salinan = $angkatan->replicate([
            'mulai', 'selesai', 'sisa_kuota', 'status', 'token',
            'created_at', 'updated_at',
        ]);

        $salinan->token = Str::random(30);
        $salinan->status = 'draft';
        $salinan->sisa_kuota = $angkatan->total_kuota;

        /*
         * Nomor BEBAS berikutnya, bukan sekadar +1. Menggandakan ke-187
         * menghasilkan 188, dan 188 sudah dipakai dua angkatan Yogyakarta —
         * jadi menaikkan asal satu justru menambah kembar ketiga.
         *
         * Kalau nomornya bukan angka, dibiarkan apa adanya daripada mengarang.
         */
        $salinan->nama_ke = KategoriLayanan::nomorBebas(
            $angkatan->layanan, $angkatan->lokasi, (string) $angkatan->nama_ke
        );

        // Tanggalnya sengaja dikosongkan, tetapi kolomnya wajib isi — diisi
        // hari ini supaya borangnya terbuka, dan admin tinggal menggantinya.
        $salinan->mulai = now();
        $salinan->selesai = null;

        $salinan->save();

        /*
         * Penanda ?digandakan=1 dibawa ke borangnya supaya tanggalnya bisa
         * ditandai di layar. Pesan sukses saja tidak cukup: toast-nya hilang
         * beberapa detik kemudian, sementara tanggal hari ini tetap duduk di
         * isiannya dan terlihat seperti tanggal yang memang disengaja.
         */
        return redirect()->route('account.kategori-layanan.edit', [$salinan, 'digandakan' => 1])
            ->with('success', 'Angkatan digandakan jadi rancangan. '
                . 'Ganti tanggalnya, lalu ubah statusnya kalau sudah siap.');
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

    // ------------------------------------------------------ tindakan massal

    /**
     * Mengubah status beberapa angkatan sekaligus.
     *
     * Dipakai juga oleh lencana "Lewat" di daftar, yang mengirim satu id:
     * lencana itu memberi tahu ada angkatan yang masih aktif padahal
     * tanggalnya lewat, dan jalan keluarnya pantas ada di tempat yang sama —
     * bukan lewat buka-sunting-simpan.
     */
    public function massal(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak boleh mengubah angkatan.',
            ], 403);
        }

        $data = $request->validate([
            'id' => ['required', 'array', 'min:1', 'max:200'],
            'id.*' => ['uuid'],
            'status' => ['required', Rule::in(['active', 'non active', 'draft'])],
        ], [
            'id.required' => 'Pilih dulu angkatan yang mau diubah.',
            'id.max' => 'Maksimal 200 angkatan sekali ubah.',
        ]);

        $jumlah = KategoriLayanan::whereIn('id', $data['id'])->update(['status' => $data['status']]);

        $sebutan = ['active' => 'Aktif', 'non active' => 'Nonaktif', 'draft' => 'Draf'][$data['status']];

        return response()->json([
            'success' => true,
            'message' => $jumlah . ' angkatan diubah jadi ' . $sebutan . '.',
        ]);
    }

    // ------------------------------------------------------------- detail

    /**
     * Tampilan baca-saja satu angkatan.
     *
     * Sebelum ini, membaca deskripsi utuh satu angkatan harus lewat borang
     * SUNTING — membaca melalui layar yang bisa mengubah, dan satu salah tekan
     * sudah cukup untuk menyimpan sesuatu yang tidak dimaksud.
     */
    public function detail(KategoriLayanan $angkatan)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        KategoriLayanan::hitungPendaftar();

        return view('account.kategori_layanan.detail', [
            'angkatan' => $angkatan,
            'tarif' => $angkatan->tarif(),
            'bolehUbah' => $this->bolehMengubah(),
            // Dua belas terakhir: yang ditanyakan orang selalu "siapa yang
            // baru saja mengubah ini", bukan riwayat setahun penuh.
            'jejak' => $angkatan->jejak()->limit(12)->get(),
        ]);
    }

    // ----------------------------------------------------------- penghapusan

    /**
     * Menghapus banyak angkatan sekaligus.
     *
     * Dipisah dari massal() yang mengubah status, bukan ditumpangkan sebagai
     * salah satu nilainya: menghapus tidak bisa dibatalkan, dan satu endpoint
     * yang menerima "active" dan "hapus" sebagai nilai setara membuat salah
     * ketik berakibat jauh lebih mahal daripada yang dimaksud.
     */
    public function massalHapus(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak boleh menghapus angkatan.',
            ], 403);
        }

        $data = $request->validate([
            'id' => ['required', 'array', 'min:1', 'max:200'],
            'id.*' => ['uuid'],
        ], [
            'id.required' => 'Pilih dulu angkatan yang mau dihapus.',
            'id.max' => 'Maksimal 200 angkatan sekali hapus.',
        ]);

        /*
         * Yang punya pendaftar DILEWATI, bukan membatalkan seluruh tindakan.
         * Membatalkan semuanya berarti satu angkatan berpendaftar di tengah
         * pilihan membuat sembilan belas lainnya ikut gagal tanpa alasan yang
         * kelihatan — dan orangnya harus menebak yang mana.
         */
        $berpendaftar = collect(KategoriLayanan::TABEL_PENDAFTARAN)
            ->flatMap(fn ($tabel) => DB::table($tabel)
                ->whereIn('kategori_id', $data['id'])->distinct()->pluck('kategori_id'))
            ->unique()->values()->all();

        $bolehHapus = array_values(array_diff($data['id'], $berpendaftar));
        $dihapus = 0;

        if ($bolehHapus !== []) {
            try {
                /*
                 * Dihapus satu per satu lewat model, BUKAN satu delete massal:
                 * penghapusan massal tidak membangkitkan kait model, jadi
                 * tidak ada satu pun jejak yang tertulis — dan dua ratus
                 * angkatan hilang tanpa jalan kembali.
                 */
                KategoriLayanan::whereIn('id', $bolehHapus)->get()
                    ->each(function ($a) use (&$dihapus) {
                        $a->delete();
                        $dihapus++;
                    });
            } catch (QueryException $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Sebagian angkatan masih tertaut ke data lain.',
                ], 409);
            }
        }

        $pesan = $dihapus . ' angkatan dihapus.';

        if ($berpendaftar !== []) {
            $pesan .= ' ' . count($berpendaftar) . ' dilewati karena sudah punya pendaftar.';
        }

        return response()->json([
            'success' => $dihapus > 0,
            'message' => $dihapus > 0 ? $pesan : 'Tidak ada yang bisa dihapus; semuanya sudah punya pendaftar.',
        ]);
    }

    /**
     * Mengembalikan angkatan yang baru saja dihapus.
     *
     * Barisnya dimasukkan kembali dari salinan di jejak, dengan id yang SAMA —
     * pendaftaran lama menunjuk id itu, dan memulihkannya dengan id baru
     * berarti pesertanya tetap kehilangan angkatannya.
     */
    public function pulihkan(AngkatanJejak $jejak)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        if ($jejak->aksi !== 'dihapus' || ! is_array($jejak->data)) {
            return back()->with('error', 'Jejak ini bukan penghapusan, jadi tidak ada yang bisa dipulihkan.');
        }

        if (KategoriLayanan::whereKey($jejak->kategori_id)->exists()) {
            return redirect()->route('account.kategori-layanan.index')
                ->with('error', 'Angkatan itu sudah ada lagi; tidak jadi dipulihkan.');
        }

        $angkatan = new KategoriLayanan;
        $angkatan->forceFill($jejak->data);
        $angkatan->save();

        AngkatanJejak::catat($angkatan, 'dipulihkan', 'dikembalikan dari keranjang');

        return redirect()->route('account.kategori-layanan.index')
            ->with('success', 'Angkatan "' . $angkatan->nama . '" dikembalikan.');
    }

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
        $terpakai = $angkatan->jumlah_pendaftaran;

        if ($terpakai > 0) {
            $orang = $angkatan->jumlah_pendaftar;

            return response()->json([
                'success' => false,
                'message' => 'Angkatan ini sudah punya ' . $orang . ' peserta'
                    . ($orang !== $terpakai ? ' (' . $terpakai . ' pendaftaran)' : '')
                    . ', jadi tidak bisa dihapus.',
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

        /*
         * Alamat untuk mengurungkan ikut dikirim. Menghapus angkatan yang
         * salah adalah kesalahan yang paling mahal di layar ini — 60 baris
         * yang namanya nyaris sama persis, dan sebelum ini tidak ada jalan
         * kembali sama sekali.
         */
        $jejak = AngkatanJejak::where('kategori_id', $angkatan->getKey())
            ->where('aksi', 'dihapus')->latest('created_at')->first();

        return response()->json([
            'success' => true,
            'message' => 'Angkatan dihapus.',
            'pulihkan' => $jejak ? route('account.kategori-layanan.pulihkan', $jejak) : null,
        ]);
    }
}
