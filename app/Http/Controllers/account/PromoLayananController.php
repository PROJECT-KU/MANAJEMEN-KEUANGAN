<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Layanan;
use App\PromoLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Promo untuk angkatan layanan apa pun — satu layar untuk semua layanan.
 *
 * Menggantikan layar promo Clinik Scopus yang lama, yang hanya melayani satu
 * layanan dan menyuguhkan tujuh isian untuk satu keputusan.
 *
 * Yang benar-benar diketik admin di sini tinggal empat hal: nama promo,
 * besar potongan, periodenya, dan angkatan mana saja yang kena. Sisanya
 * dihitung — termasuk keadaan promonya, yang TIDAK disimpan di kolom mana
 * pun supaya tidak pernah bisa tertinggal dari tanggal sebenarnya.
 */
class PromoLayananController extends Controller
{
    private const PER_HALAMAN = 10;

    /**
     * Medan yang ikut terbawa tombol unduh dan kepala kolom pengurut.
     *
     * Satu daftar, bukan tiga salinan di markah. Di layar Data Pelanggan dulu
     * tautan unduhnya cuma membawa tiga dari lima medan saringan, jadi
     * menekan ubin ringkasan lalu mengunduh memulangkan SELURUH baris —
     * tanpa galat, dan kepala berkasnya ikut berbohong.
     */
    private const MEDAN_SARINGAN = ['cari', 'keadaan', 'layanan'];

    /**
     * Kolom yang boleh dipakai mengurutkan.
     *
     * Daftar TERTUTUP: nilainya datang dari alamat, dan nama kolom sembarang
     * akan sampai ke orderBy apa adanya.
     */
    private const BOLEH_URUT = [
        'mulai' => 'Tanggal mulai',
        'selesai' => 'Tanggal selesai',
        'nama' => 'Nama promo',
        'dibuat' => 'Terbaru dibuat',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
     * Hak akses diperiksa DI SINI, bukan oleh middleware peran.
     *
     * Grup rute `account/` tidak memasang middleware peran sama sekali — jadi
     * pengendali yang lupa memeriksanya terbuka untuk siapa pun yang punya
     * akun, termasuk pelanggan. Sudah pernah terjadi di layar lain.
     */
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
            ->with('error', 'Anda tidak punya akses ke promo layanan.');
    }

    // -------------------------------------------------------------- daftar

    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $cari = trim((string) $request->query('cari'));

        $keadaan = array_key_exists((string) $request->query('keadaan'), self::KEADAAN)
            ? (string) $request->query('keadaan')
            : null;

        $layanan = array_key_exists((string) $request->query('layanan'), Layanan::katalog())
            ? (string) $request->query('layanan')
            : null;

        $urut = array_key_exists((string) $request->query('urut'), self::BOLEH_URUT)
            ? (string) $request->query('urut')
            : 'mulai';

        $arah = $request->query('arah') === 'naik' ? 'asc' : 'desc';

        /*
         * Dua kueri, bukan satu.
         *
         * Ubin ringkasan dihitung dari kueri DASAR — kata kunci dan layanan
         * saja. Kalau ubinnya ikut menyaring keadaan, angkanya berubah tiap
         * kali ubinnya ditekan: menekan "Berjalan" membuat ubin "Belum mulai"
         * jadi 0, dan jalan kembalinya hilang dari layar.
         */
        $kueriDasar = PromoLayanan::query()->cari($cari);

        if ($layanan !== null) {
            $kueriDasar->whereHas('angkatan', fn ($q) => $q->where('layanan', $layanan));
        }

        $ringkas = $this->ringkasan(clone $kueriDasar);

        $kueri = clone $kueriDasar;

        /*
         * Saringan keadaan dikerjakan lewat DAFTAR ID, bukan syarat SQL.
         *
         * Dua dari enam keadaan tidak bisa dinyatakan di SQL sama sekali
         * (lihat keadaanSemua). Separuh di SQL separuh di PHP berarti
         * lencana di baris dan saringan di menu bisa tidak sepakat; disaring
         * di PHP SESUDAH halamannya diambil, penomoran halamannya langsung
         * salah — halaman pertama berisi 10 baris lalu tersaring jadi 3, dan
         * jumlah totalnya ikut berbohong.
         *
         * Lewat whereIn, penyaringannya tetap dikerjakan basis data dan
         * penomoran halamannya tetap benar.
         */
        if ($keadaan !== null) {
            $kueri->whereIn('id', $this->idBerkeadaan($kueriDasar, $keadaan));
        }

        // 'dibuat' bukan nama kolom; ia created_at. Dipetakan di sini supaya
        // nama kolom asli tidak pernah muncul di alamat halaman.
        $kolomUrut = $urut === 'dibuat' ? 'created_at' : $urut;

        $daftar = $kueri
            ->with('penginput')
            ->withCount('angkatan')
            ->orderBy($kolomUrut, $arah)
            // Pengurut kedua yang pasti unik: tanpa itu, dua promo bertanggal
            // sama bisa bertukar tempat antar halaman dan satu di antaranya
            // tidak pernah terlihat.
            ->orderBy('id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('account.promo_layanan.index', [
            'daftar' => $daftar,
            'ringkas' => $ringkas,
            'cari' => $cari,
            'keadaan' => $keadaan,
            'layanan' => $layanan,
            'urut' => $urut,
            'arah' => $arah,
            'bolehUrut' => self::BOLEH_URUT,
            'daftarKeadaan' => self::KEADAAN,
            'katalog' => Layanan::katalog(),
            'saringan' => $request->only(self::MEDAN_SARINGAN),
            'adaSaringan' => $cari !== '' || $keadaan !== null || $layanan !== null,
            'bolehUbah' => $this->bolehMengubah(),
        ]);
    }

    /** Keadaan yang bisa disaring, beserta namanya di layar. */
    private const KEADAAN = [
        'berjalan' => 'Sedang berjalan',
        'hampir-habis' => 'Hampir habis (≤ 7 hari)',
        'belum-mulai' => 'Belum mulai',
        'kuota-habis' => 'Kuota habis',
        'selesai' => 'Sudah selesai',
        'nonaktif' => 'Dimatikan',
    ];

    /**
     * Keadaan tiap promo, DIHITUNG DI PHP lewat accessor-nya.
     *
     * Bukan di SQL, dan itu disengaja. Dua dari enam keadaan tidak bisa
     * dinyatakan sebagai syarat SQL sama sekali: "kuota habis" menuntut
     * menghitung pendaftaran di tiga tabel lain, dan "hampir habis" menuntut
     * keduanya sekaligus. Separuh di SQL separuh di PHP berarti ubin
     * ringkasannya dan lencana di barisnya bisa menyebut keadaan yang
     * berbeda untuk promo yang sama — dan tidak ada galat yang memberi tahu.
     *
     * Boleh dikerjakan begini karena tabelnya memang kecil: promo dihitung
     * puluhan, bukan ribuan, dan DampakPromo sudah mengambil seluruh angka
     * pemakaian dalam tiga kueri untuk SEMUA promo sekaligus. Kalau suatu
     * saat promonya mencapai ribuan, yang harus diubah bagian ini — bukan
     * dengan memecahnya ke SQL, melainkan dengan menyimpan hitungannya.
     *
     * @return list<array{keadaan: string, hampir: bool}>
     */
    private function keadaanSemua($kueri): array
    {
        return (clone $kueri)->reorder()
            ->get(['id', 'aktif', 'mulai', 'selesai', 'kuota'])
            ->map(fn (PromoLayanan $p) => [
                'keadaan' => $p->keadaan,
                // Dihitung di sini juga, dari model yang SUDAH dimuat.
                // Dicari ulang lewat find() per promo, ubin ringkasannya
                // sendiri jadi satu kueri per baris.
                'hampir' => $p->sisa_hari !== null && $p->sisa_hari <= 7,
            ])
            ->all();
    }

    /**
     * Angka ubin ringkasan, dari keadaan yang sama persis dengan lencananya.
     */
    private function ringkasan($kueri): array
    {
        $jumlah = array_fill_keys(array_keys(self::KEADAAN), 0);
        $jumlah['semua'] = 0;

        /*
         * "Hampir habis" BAGIAN DARI "sedang berjalan", bukan keadaan yang
         * meniadakannya: promo yang tinggal tiga hari tetap sedang berjalan.
         * Karena itu ia dihitung di samping, dan jumlah seluruh ubin lain
         * tetap menjumlah ke totalnya.
         */
        foreach ($this->keadaanSemua($kueri) as $satu) {
            $jumlah['semua']++;

            if (isset($jumlah[$satu['keadaan']])) {
                $jumlah[$satu['keadaan']]++;
            }

            if ($satu['keadaan'] === 'berjalan' && $satu['hampir']) {
                $jumlah['hampir-habis']++;
            }
        }

        return $jumlah;
    }

    /**
     * Id promo yang keadaannya cocok.
     *
     * @return array<int, string>
     */
    private function idBerkeadaan($kueriDasar, string $keadaan): array
    {
        $cocok = [];

        $daftar = (clone $kueriDasar)->reorder()
            ->get(['id', 'aktif', 'mulai', 'selesai', 'kuota']);

        foreach ($daftar as $p) {
            $hampir = $p->sisa_hari !== null && $p->sisa_hari <= 7;

            $sesuai = $keadaan === 'hampir-habis'
                ? ($p->keadaan === 'berjalan' && $hampir)
                : $p->keadaan === $keadaan;

            if ($sesuai) {
                $cocok[] = (string) $p->getKey();
            }
        }

        return $cocok;
    }

    /**
     * Daftar promo untuk diunduh: saringan dan urutannya SAMA dengan layarnya.
     *
     * Tanpa halaman. Berkas berisi 10 dari 60 promo tidak boleh terbaca
     * seperti daftar yang lengkap, dan itulah yang terjadi kalau paginator
     * layarnya dipakai ulang begitu saja.
     */
    private function untukEkspor(Request $request)
    {
        $cari = trim((string) $request->query('cari'));

        $keadaan = array_key_exists((string) $request->query('keadaan'), self::KEADAAN)
            ? (string) $request->query('keadaan') : null;

        $layanan = array_key_exists((string) $request->query('layanan'), Layanan::katalog())
            ? (string) $request->query('layanan') : null;

        $urut = array_key_exists((string) $request->query('urut'), self::BOLEH_URUT)
            ? (string) $request->query('urut') : 'mulai';

        $arah = $request->query('arah') === 'naik' ? 'asc' : 'desc';

        $dasar = PromoLayanan::query()->cari($cari);

        if ($layanan !== null) {
            $dasar->whereHas('angkatan', fn ($q) => $q->where('layanan', $layanan));
        }

        $kueri = clone $dasar;

        if ($keadaan !== null) {
            $kueri->whereIn('id', $this->idBerkeadaan($dasar, $keadaan));
        }

        return $kueri->with('penginput')->withCount('angkatan')
            ->orderBy($urut === 'dibuat' ? 'created_at' : $urut, $arah)
            ->orderBy('id')
            ->get();
    }

    /**
     * Saringan yang sedang dipakai, dalam kalimat.
     *
     * Dicetak di kepala berkas unduhan. Berkas berisi 29 dari 101 baris tidak
     * boleh terbaca seperti daftar yang lengkap, dan satu-satunya cara
     * menyatakannya adalah menyebutkan saringannya.
     */
    private function ringkasanSaringan(Request $request): string
    {
        $bagian = [];

        if (($cari = trim((string) $request->query('cari'))) !== '') {
            $bagian[] = 'kata kunci "' . $cari . '"';
        }

        if (array_key_exists((string) $request->query('keadaan'), self::KEADAAN)) {
            $bagian[] = 'keadaan ' . mb_strtolower(self::KEADAAN[$request->query('keadaan')]);
        }

        if (array_key_exists((string) $request->query('layanan'), Layanan::katalog())) {
            $bagian[] = 'layanan ' . Layanan::katalog()[$request->query('layanan')]['nama'];
        }

        return $bagian === [] ? 'Seluruh promo, tanpa saringan.' : 'Disaring: ' . implode(', ', $bagian) . '.';
    }

    // -------------------------------------------------------------- unduhan

    public function eksporExcel(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PromoLayananExport($this->untukEkspor($request)),
            'promo-layanan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function cetakPdf(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $html = view('account.promo_layanan.cetak-pdf', [
            'daftar' => $this->untukEkspor($request),
            'saringan' => $this->ringkasanSaringan($request),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $pengaturan = $dompdf->getOptions();
        $pengaturan->setIsPhpEnabled(true);
        $pengaturan->setIsRemoteEnabled(false);
        $dompdf->setOptions($pengaturan);
        $dompdf->loadHtml($html);
        // Mendatar: sebelas kolom tidak muat tegak tanpa dimampatkan.
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="promo-layanan-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    // -------------------------------------------------------------- rincian

    /**
     * Rincian satu promo: hasilnya, angkatannya, dan jejak perubahannya.
     *
     * Terpisah dari borang ubah, dan itu disengaja. Sebelumnya menekan nama
     * promo langsung membuka borangnya — jadi MELIHAT promo berarti membuka
     * layar yang setiap isiannya bisa tersenggol.
     */
    public function detail(string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $promo = PromoLayanan::with(['penginput', 'angkatan'])->findOrFail($id);

        return view('account.promo_layanan.detail', [
            'terlewat' => $this->angkatanTerlewat($promo),
            'promo' => $promo,
            'dampak' => \App\Support\DampakPromo::untuk((string) $promo->getKey()),
            'perAngkatan' => \App\Support\DampakPromo::perAngkatan($promo),
            /*
             * Jejaknya DINOMORI, bukan dipotong di 50.
             *
             * Dibatasi limit, yang ke-51 hilang tanpa satu pun tanda — dan
             * justru jejak lama yang dicari saat ada yang bertanya "sejak
             * kapan harganya segini?". Nama halamannya sendiri ('jejak')
             * supaya tidak bentrok dengan penomoran lain di layar ini nanti.
             */
            'jejak' => $promo->jejak()->paginate(15, ['*'], 'jejak')->withQueryString(),
            'katalog' => Layanan::katalog(),
            'bolehUbah' => $this->bolehMengubah(),
        ]);
    }

    // ------------------------------------------- dipasang dari sisi angkatan

    /**
     * Memasang atau melepas promo pada satu angkatan, dari layar angkatannya.
     *
     * Sebelumnya hubungannya cuma satu arah: promo menunjuk angkatan, dan
     * satu-satunya tempat mengaturnya borang promo. Jadi admin yang sedang
     * membuka sebuah angkatan dan ingin memberinya promo harus mengingat nama
     * promonya, pindah layar, mencari angkatan itu di antara 241 baris, lalu
     * mencentangnya.
     *
     * Dua arah, satu tabel sambungan yang sama — jadi tidak ada keadaan yang
     * bisa berbeda tergantung dari layar mana diaturnya.
     */
    public function tempel(Request $request, string $kategoriId)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $angkatan = KategoriLayanan::findOrFail($kategoriId);

        $isian = $request->validate([
            'promo_id' => ['required', 'string', 'exists:promo_layanan,id'],
            'lepas' => ['nullable', 'boolean'],
        ], [], ['promo_id' => 'promo']);

        $promo = PromoLayanan::findOrFail($isian['promo_id']);

        if (! empty($isian['lepas'])) {
            $promo->angkatan()->detach($angkatan->getKey());
            $pesan = 'Promo "' . $promo->nama . '" dilepas dari angkatan ini.';
        } else {
            // syncWithoutDetaching, bukan attach: menekan tombolnya dua kali
            // akan ditolak kunci unik pivotnya dengan galat yang menyebut nama
            // constraint, bukan apa yang salah.
            $promo->angkatan()->syncWithoutDetaching([$angkatan->getKey()]);
            $pesan = 'Promo "' . $promo->nama . '" dipasang di angkatan ini.';
        }

        /*
         * Dicatat di jejak PROMO-nya, bukan jejak angkatan: yang berubah
         * cakupan promo, dan di situlah orang mencarinya kalau harga sebuah
         * angkatan tiba-tiba berbeda.
         */
        \App\PromoJejak::catat(
            $promo,
            'diubah',
            (empty($isian['lepas']) ? 'dipasang di' : 'dilepas dari') . ' angkatan "' . $angkatan->nama . '"'
        );

        \App\Support\PromoAngkatan::lupakan();

        return back()->with('success', $pesan);
    }

    /**
     * Angkatan yang MESTINYA kena promo ini tetapi belum dicentang.
     *
     * Promo menempel pada angkatan yang dicentang satu per satu — itu memang
     * yang diminta, sebab tidak semua angkatan dapat promo. Akibat wajarnya:
     * angkatan yang dibuat SESUDAH promonya diam-diam tetap harga normal, dan
     * tidak ada apa pun yang memberitahu siapa pun.
     *
     * Yang dianggap "mestinya": layanan yang sama dengan angkatan yang sudah
     * dicentang, dibuat sesudah promonya, belum dicentang, dan tanggal
     * mulainya masih di depan — angkatan yang sudah lewat tidak ada gunanya
     * ditawarkan promo.
     *
     * @return \Illuminate\Support\Collection<int, KategoriLayanan>
     */
    private function angkatanTerlewat(PromoLayanan $promo)
    {
        $layanan = $promo->angkatan->pluck('layanan')->unique()->filter()->values();

        // Promo yang belum menunjuk apa pun tidak punya "layanan yang sama"
        // untuk dibandingkan; peringatannya sudah ditangani keadaan kosong.
        if ($layanan->isEmpty() || $promo->keadaan === 'selesai') {
            return collect();
        }

        return KategoriLayanan::query()
            ->whereIn('layanan', $layanan)
            ->whereNotIn('id', $promo->angkatan->pluck('id'))
            ->where('created_at', '>', $promo->created_at)
            ->whereDate('mulai', '>=', now()->toDateString())
            ->orderByDesc('created_at')
            ->get(['id', 'layanan', 'nama', 'nama_ke', 'mulai']);
    }

    // ------------------------------------------------------------- gandakan

    /**
     * Menyalin promo beserta angkatan yang dicentangnya.
     *
     * Promo tahunan — ulang tahun, akhir tahun — isinya hampir sama persis
     * tiap kali; tanpa ini seluruhnya diketik ulang, termasuk mencentang
     * belasan angkatan satu per satu.
     *
     * Salinannya LAHIR MATI dan tanpa tanggal yang disalin. Tanggal lama
     * berarti promo yang sudah lewat, dan salinan yang langsung menyala
     * dengan tanggal tahun lalu adalah dua kekeliruan sekaligus yang tidak
     * akan terlihat sampai ada yang memeriksa harganya.
     */
    public function gandakan(string $id)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $asal = PromoLayanan::with('angkatan:id')->findOrFail($id);

        $salinan = DB::transaction(function () use ($asal) {
            $baru = PromoLayanan::create([
                'nama' => \Illuminate\Support\Str::limit($asal->nama . ' (salinan)', 120, ''),
                'tipe' => $asal->tipe,
                'persen' => $asal->persen,
                'nominal' => $asal->nominal,
                'batas_potongan' => $asal->batas_potongan,
                'kuota' => $asal->kuota,
                'mulai' => now()->toDateString(),
                'selesai' => now()->addDays(14)->toDateString(),
                'aktif' => false,
                'catatan' => $asal->catatan,
            ]);

            $baru->angkatan()->sync($asal->angkatan->pluck('id')->all());

            return $baru;
        });

        \App\PromoJejak::catat($salinan, 'digandakan', 'disalin dari "' . $asal->nama . '"');

        \App\Support\PromoAngkatan::lupakan();

        return redirect()->route('account.promo-layanan.edit', $salinan->id)
            ->with('success', 'Salinan dibuat dan masih dimatikan — periksa tanggalnya dulu, lalu nyalakan.');
    }

    // --------------------------------------------------------------- borang

    public function create()
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        return view('account.promo_layanan.form', $this->bekalBorang(new PromoLayanan([
            'tipe' => PromoLayanan::PERSEN,
            'aktif' => true,
            'mulai' => now()->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
        ]), []));
    }

    public function edit(string $id)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $promo = PromoLayanan::with('angkatan:id')->findOrFail($id);

        return view('account.promo_layanan.form', $this->bekalBorang(
            $promo, $promo->angkatan->pluck('id')->all()
        ));
    }

    /**
     * Semua yang dibutuhkan borangnya, dirakit sekali.
     *
     * @param  array<int, string>  $terpilih
     */
    private function bekalBorang(PromoLayanan $promo, array $terpilih): array
    {
        return [
            'promo' => $promo,
            'isEdit' => $promo->exists,
            'terpilih' => $terpilih,
            'kelompok' => $this->angkatanPerLayanan($terpilih, $promo),
            'tipe' => PromoLayanan::TIPE,

            /*
             * Daftar layanan untuk kotak centang syarat riwayat. Dibaca dari
             * katalog yang sama dengan jalur pendaftaran, bukan ditulis ulang
             * di borangnya — layanan berikutnya muncul sendiri di sini.
             */
            'layananSyarat' => collect(\App\Support\PendaftaranSemuaLayanan::semuaSumber())
                ->map(fn ($s, $kunci) => [
                    'kunci' => $kunci,
                    'nama' => $s['nama'] ?? str_replace('_', ' ', $kunci),
                    'ikon' => $s['ikon'] ?? 'fa-tag',
                ])
                ->values()->all(),

            'syaratTerpilih' => (array) old('syarat_layanan', $promo->syarat_layanan ?? []),
        ];
    }

    /**
     * Angkatan untuk dicentang, dikelompokkan per layanan.
     *
     * SELURUH angkatan dikirim ke halaman — 241 baris saat ini — dan
     * penyaringannya dikerjakan di peramban. Dengan titik akhir pencarian
     * sendiri, tiap ketikan jadi satu permintaan dan pilihan yang sudah
     * dicentang hilang begitu hasilnya ditukar; itu kekeliruan yang sudah
     * pernah terjadi di layar lain dan tidak terlihat dari curl sama sekali.
     *
     * Yang sudah lewat tetap dikirim, ditandai, dan disembunyikan secara
     * bawaan oleh saringannya — bukan dibuang di sini. Dibuang, angkatan
     * lewat yang SUDAH tercentang pada promo lama lenyap dari borangnya, dan
     * menyimpan promo itu akan diam-diam melepaskan centangnya.
     *
     * @param  array<int, string>  $terpilih
     * @return array<string, array<string, mixed>>
     */
    private function angkatanPerLayanan(array $terpilih, ?PromoLayanan $promoIni = null): array
    {
        $katalog = Layanan::katalog();
        $hariIni = now()->startOfDay();

        $semua = KategoriLayanan::query()
            ->orderByDesc('mulai')->orderBy('nama')
            ->get(['id', 'layanan', 'varian', 'nama', 'nama_ke', 'mulai', 'status', 'biaya', 'total_biaya']);

        /*
         * Angkatan yang SUDAH kena promo lain ditandai.
         *
         * Mencentangnya tetap boleh — dua promo pada satu angkatan sah, dan
         * yang menang dipilih menurut besar potongannya. Tetapi admin yang
         * tidak diberi tahu akan mengira promonya pasti berlaku, lalu heran
         * kenapa harganya bukan yang ia setel.
         *
         * Dimuat berkelompok: satu kueri untuk seluruh daftar, bukan satu per
         * baris.
         */
        \App\Support\PromoAngkatan::muat($semua);

        $kelompok = [];

        foreach ($semua as $a) {
            $kunci = (string) $a->layanan;

            // Layanan yang sudah dihapus dari katalog tetap punya angkatan di
            // basis data. Dilewati, angkatannya hilang dari borang tanpa ada
            // yang tahu; jadi ia tetap tampil dengan nama kuncinya.
            $kelompok[$kunci] ??= [
                'nama' => $katalog[$kunci]['nama'] ?? str_replace('_', ' ', $kunci),
                'ikon' => $katalog[$kunci]['ikon'] ?? 'fa-tag',
                'warna' => $katalog[$kunci]['warna'] ?? 'mis-ungu',
                'baris' => [],
            ];

            $harga = (int) ($a->total_biaya ?: $a->biaya);

            /*
             * Tanggalnya diurai sendiri. `kategori_layanan.mulai` kolom
             * datetime tetapi modelnya TIDAK punya $casts sama sekali — jadi
             * yang keluar untaian, dan memanggil ->locale() padanya 500 tanpa
             * petunjuk apa pun. Menambahkan cast di model itu akan mengubah
             * belasan layar lain sekaligus, jadi diurai di sini saja.
             */
            $mulai = $a->mulai ? \Carbon\Carbon::parse($a->mulai) : null;

            // Promo lain yang sedang berjalan di angkatan ini; promo yang
            // sedang disunting sendiri tidak dihitung sebagai "promo lain".
            $lain = \App\Support\PromoAngkatan::untuk($a);

            if ($promoIni !== null && $lain !== null && $lain->getKey() === $promoIni->getKey()) {
                $lain = null;
            }

            $kelompok[$kunci]['baris'][] = [
                'id' => (string) $a->id,
                'promo_lain' => $lain?->nama,
                'nama' => trim((string) ($a->nama ?: 'Angkatan tanpa nama')),
                'ke' => trim((string) $a->nama_ke),
                'varian' => $katalog[$kunci]['varian'][$a->varian] ?? null,
                'mulai' => $mulai?->locale('id')->translatedFormat('j M Y'),
                'harga' => $harga,
                'lewat' => $mulai !== null && $mulai->lt($hariIni),
                'dipilih' => in_array((string) $a->id, $terpilih, true),
            ];
        }

        return $kelompok;
    }

    // ------------------------------------------------------------- simpanan

    public function store(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $data = $this->periksa($request);

        $promo = DB::transaction(function () use ($data) {
            $promo = PromoLayanan::create($data['promo']);
            $promo->angkatan()->sync($data['angkatan']);

            return $promo;
        });

        \App\Support\PromoAngkatan::lupakan();

        return redirect()->route('account.promo-layanan.index')
            ->with('success', 'Promo "' . $promo->nama . '" sudah disimpan.');
    }

    public function update(Request $request, string $id)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $promo = PromoLayanan::findOrFail($id);
        $data = $this->periksa($request);

        DB::transaction(function () use ($promo, $data) {
            $promo->update($data['promo']);

            /*
             * sync(), bukan attach(): centang yang DILEPAS harus benar-benar
             * lepas. Dengan attach saja, angkatan yang tadi dicentang lalu
             * dibatalkan tetap kena promo, dan layarnya tetap menampilkan
             * promonya aktif di sana — tanpa cara apa pun untuk mencabutnya
             * dari borang.
             */
            $promo->angkatan()->sync($data['angkatan']);
        });

        \App\Support\PromoAngkatan::lupakan();

        return redirect()->route('account.promo-layanan.index')
            ->with('success', 'Promo "' . $promo->nama . '" sudah diperbarui.');
    }

    /**
     * Memeriksa kiriman borang, lalu memulangkannya dalam bentuk siap simpan.
     *
     * @return array{promo: array<string, mixed>, angkatan: array<int, string>}
     */
    private function periksa(Request $request): array
    {
        /*
         * Titik ribuan dibuang SEBELUM divalidasi, bukan sesudah.
         *
         * Kotak rupiahnya memformat dirinya sambil diketik, jadi yang sampai
         * ke sini "75.000". Aturan `integer` menolaknya, dan pesan yang
         * dilihat admin berbunyi "potongan rupiah harus berupa angka" pada
         * kotak yang jelas-jelas berisi angka — tidak ada petunjuk sama
         * sekali bahwa titiknya yang jadi soal.
         *
         * Yang dibuang semua yang BUKAN angka, bukan hanya tanda titiknya:
         * koma, spasi, dan "Rp" yang ikut tertempel saat orang menyalin dari
         * tempat lain sama-sama harus hilang.
         *
         * Kotak kosong tetap null, BUKAN 0: keduanya berbeda artinya bagi
         * `nullable`, dan 0 akan ditolak `min:1` dengan galat pada medan yang
         * memang boleh dikosongkan.
         */
        foreach (['nominal', 'batas_potongan', 'kuota'] as $medan) {
            if (! $request->exists($medan)) {
                continue;
            }

            $bersih = preg_replace('/\D+/', '', (string) $request->input($medan));

            $request->merge([$medan => $bersih === '' ? null : (int) $bersih]);
        }

        $isian = $request->validate([
            'nama' => ['required', 'string', 'max:120'],

            /*
             * Kode BOLEH KOSONG, dan kosong itu yang membedakan dua jenis
             * promo: tanpa kode berarti memotong dengan sendirinya seperti
             * dulu, berkode berarti harganya baru turun sesudah pendaftarnya
             * mengetikkan kodenya.
             *
             * Hurufnya dibatasi ke huruf, angka, dan tanda hubung. Kode
             * berisi spasi atau tanda baca akan diketik berbeda-beda oleh
             * orang yang membacanya dari poster, lalu ditolak tanpa ia tahu
             * sebabnya.
             */
            'kode' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9\-]+$/'],

            /*
             * Syarat riwayat: layanan yang HARUS pernah diikuti sampai lunas.
             *
             * Kuncinya diperiksa terhadap katalog, bukan diterima mentah.
             * Kunci karangan tersimpan berarti syarat yang tidak akan pernah
             * terpenuhi siapa pun — promonya diam-diam mati untuk semua
             * orang, dan tidak ada satu galat pun yang menyebut sebabnya.
             */
            'syarat_layanan' => ['nullable', 'array'],
            'syarat_layanan.*' => [
                'string',
                Rule::in(array_keys(\App\Support\PendaftaranSemuaLayanan::semuaSumber())),
            ],

            'tipe' => ['required', Rule::in(array_keys(PromoLayanan::TIPE))],

            /*
             * Nilainya diperiksa menurut tipenya, bukan dua-duanya wajib.
             * Diwajibkan dua-duanya, admin yang memilih "potong persen" tetap
             * ditolak karena kotak rupiah kosong — padahal kotak itu memang
             * disembunyikan skrip saat tipenya persen.
             */
            'persen' => ['nullable', 'required_if:tipe,persen', 'integer', 'min:1', 'max:100'],
            'nominal' => ['nullable', 'required_if:tipe,rupiah', 'integer', 'min:1', 'max:1000000000'],
            'batas_potongan' => ['nullable', 'integer', 'min:1', 'max:1000000000'],

            /*
             * Kuota boleh kosong (tanpa batas) tetapi TIDAK boleh nol: nol
             * berarti promo yang habis sejak detik pertama, dan itu hampir
             * selalu salah ketik, bukan maksud.
             */
            'kuota' => ['nullable', 'integer', 'min:1', 'max:1000000'],

            'mulai' => ['required', 'date'],

            /*
             * after_or_equal, bukan after: promo sehari — mulai dan selesai di
             * tanggal yang sama — masuk akal dan sudah pernah diminta.
             */
            'selesai' => ['required', 'date', 'after_or_equal:mulai'],

            'aktif' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:500'],

            'angkatan' => ['nullable', 'array'],
            'angkatan.*' => ['string', 'exists:kategori_layanan,id'],
        ], [], [
            'nama' => 'nama promo',
            'kode' => 'kode promo',
            'syarat_layanan' => 'syarat pernah ikut layanan',
            'persen' => 'potongan persen',
            'nominal' => 'potongan rupiah',
            'batas_potongan' => 'batas potongan',
            'kuota' => 'kuota pendaftaran',
            'mulai' => 'tanggal mulai',
            'selesai' => 'tanggal selesai',
            'angkatan' => 'angkatan',
        ]);

        $persenan = $isian['tipe'] === PromoLayanan::PERSEN;

        return [
            'promo' => [
                'nama' => $isian['nama'],

                // Dirapikan SEKALI di modelnya, supaya yang disimpan dan yang
                // dicari jalur umum memakai bentuk yang sama persis.
                'kode' => PromoLayanan::rapikanKode($isian['kode'] ?? null) ?: null,

                // Larik kosong disimpan NULL, bukan "[]": keduanya berarti
                // tanpa syarat, dan dua bentuk untuk satu arti membuat layar
                // yang membacanya harus memeriksa keduanya.
                'syarat_layanan' => array_values(array_unique($isian['syarat_layanan'] ?? []))
                    ?: null,

                'tipe' => $isian['tipe'],

                /*
                 * Nilai yang tidak dipakai DIKOSONGKAN, bukan dibiarkan.
                 *
                 * Promo yang diubah dari rupiah ke persen akan menyimpan
                 * keduanya kalau angka lamanya dibiarkan, dan kartu
                 * ringkasannya jadi menyebut dua potongan berbeda untuk satu
                 * promo. Yang menentukan tetap `tipe`, tapi angka yang masih
                 * tertinggal di kolom sebelahnya terbaca seperti kekeliruan
                 * hitungan.
                 */
                'persen' => $persenan ? (int) $isian['persen'] : null,
                'nominal' => $persenan ? null : (int) $isian['nominal'],
                'batas_potongan' => $persenan && ! empty($isian['batas_potongan'])
                    ? (int) $isian['batas_potongan']
                    : null,

                'kuota' => ! empty($isian['kuota']) ? (int) $isian['kuota'] : null,

                'mulai' => $isian['mulai'],
                'selesai' => $isian['selesai'],
                /*
                 * Tidak dikirim berarti MENYALA, sama dengan bawaan kolomnya.
                 *
                 * Dibuat jatuh ke mati, promo yang dikirim tanpa medan itu
                 * tersimpan nonaktif — dan admin yang baru membuat promo
                 * melihat lencana "Dimatikan" pada sesuatu yang baru saja ia
                 * buat untuk dijalankan. Mematikannya tetap bisa, lewat medan
                 * tersembunyi bernilai 0 yang dikirim borangnya: kotak centang
                 * yang tidak dicentang TIDAK terkirim sama sekali.
                 */
                'aktif' => (bool) ($isian['aktif'] ?? true),
                'catatan' => trim((string) ($isian['catatan'] ?? '')) ?: null,
            ],

            // array_unique: kiriman yang memuat id yang sama dua kali akan
            // ditolak kunci unik pivotnya dengan galat yang menyebut nama
            // constraint, bukan apa yang salah.
            'angkatan' => array_values(array_unique($isian['angkatan'] ?? [])),
        ];
    }

    // ------------------------------------------------------------- tindakan

    /**
     * Menyalakan atau mematikan promo dari layar daftarnya.
     *
     * Terpisah dari update supaya mematikan promo yang sedang berjalan tidak
     * perlu membuka borang dan menekan Simpan — keadaan yang biasanya
     * mendesak, dan borang penuh adalah tempat paling mudah untuk tersenggol
     * mengubah hal lain.
     */
    public function saklar(Request $request, string $id)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $promo = PromoLayanan::findOrFail($id);
        $promo->update(['aktif' => ! $promo->aktif]);

        \App\Support\PromoAngkatan::lupakan();

        return back()->with(
            'success',
            'Promo "' . $promo->nama . '" sekarang ' . ($promo->aktif ? 'menyala.' : 'dimatikan.')
        );
    }

    /**
     * Menyalakan atau mematikan beberapa promo sekaligus.
     *
     * TIDAK termasuk menghapus, dan itu disengaja: penghapusan massal berarti
     * satu salah klik menghilangkan belasan promo beserta cakupannya, dan
     * belum ada tong sampah untuk promo. Aturan yang sama sudah berlaku di
     * aksi massal angkatan dan pelanggan.
     */
    public function massal(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $isian = $request->validate([
            'id' => ['required', 'array', 'min:1'],
            'id.*' => ['string', 'exists:promo_layanan,id'],
            'tindakan' => ['required', Rule::in(['nyalakan', 'matikan'])],
        ], [], ['id' => 'promo']);

        $nyala = $isian['tindakan'] === 'nyalakan';

        /*
         * Disimpan satu per satu lewat model, BUKAN satu `update()` massal.
         *
         * Pembaruan massal melewati kait model, jadi tidak ada satu baris pun
         * jejak yang tertulis — dan mematikan dua belas promo sekaligus
         * adalah justru perubahan yang paling perlu dicatat.
         */
        $berubah = 0;

        foreach (PromoLayanan::whereIn('id', $isian['id'])->get() as $promo) {
            if ((bool) $promo->aktif === $nyala) {
                continue;
            }

            $promo->update(['aktif' => $nyala]);
            $berubah++;
        }

        \App\Support\PromoAngkatan::lupakan();

        return back()->with('success', $berubah === 0
            ? 'Tidak ada yang berubah — semuanya sudah ' . ($nyala ? 'menyala.' : 'dimatikan.')
            : $berubah . ' promo ' . ($nyala ? 'dinyalakan.' : 'dimatikan.'));
    }

    public function destroy(string $id)
    {
        if (! $this->bolehMengubah()) {
            return $this->tolak();
        }

        $promo = PromoLayanan::findOrFail($id);
        $nama = $promo->nama;

        /*
         * Dihapus tanpa pemeriksaan tambahan, dan itu aman di sini: yang
         * menggantung pada promo cuma barisnya di tabel sambungan, yang ikut
         * terhapus lewat kunci asing. Harga pendaftaran yang sudah terjadi
         * TIDAK ikut berubah — nominalnya sudah tersimpan di baris
         * pendaftarannya masing-masing, bukan dihitung ulang dari promonya.
         */
        $promo->delete();

        \App\Support\PromoAngkatan::lupakan();

        return redirect()->route('account.promo-layanan.index')
            ->with('success', 'Promo "' . $nama . '" sudah dihapus.');
    }
}
