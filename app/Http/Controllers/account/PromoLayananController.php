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

        $kueri = (clone $kueriDasar)->keadaan($keadaan);

        // 'dibuat' bukan nama kolom; ia created_at. Dipetakan di sini supaya
        // nama kolom asli tidak pernah muncul di alamat halaman.
        $kolomUrut = $urut === 'dibuat' ? 'created_at' : $urut;

        $daftar = $kueri
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
        'belum-mulai' => 'Belum mulai',
        'selesai' => 'Sudah selesai',
        'nonaktif' => 'Dimatikan',
    ];

    /**
     * Empat angka ubin ringkasan, dalam SATU kueri.
     *
     * Dihitung terpisah, layarnya menjalankan empat `count(*)` yang
     * bentuknya sama persis — dan jumlahnya tumbuh tiap kali satu keadaan
     * ditambahkan.
     */
    private function ringkasan($kueri): array
    {
        $hari = now()->toDateString();

        /*
         * Alias hitungannya DIBERI AWALAN, dan hasilnya dibaca lewat toBase().
         *
         * Alias `selesai` bertabrakan dengan nama kolom `selesai`. Dibaca
         * lewat Eloquent, cast 'selesai' => 'date' menang atas alias itu:
         * yang kembali objek Carbon, bukan angka, dan halamannya 500 dengan
         * "Object of class Illuminate\Support\Carbon could not be converted
         * to int" — pesan yang tidak menyebut tabrakan nama sama sekali.
         *
         * toBase() memulangkan baris mentah tanpa cast apa pun, jadi tabrakan
         * seperti ini tidak bisa terjadi lagi walau nanti ada yang menambah
         * alias bernama sama dengan kolomnya.
         */
        $baris = $kueri->toBase()->selectRaw(
            'sum(case when aktif = 1 and mulai <= ? and selesai >= ? then 1 else 0 end) as j_berjalan,'
            . ' sum(case when aktif = 1 and mulai > ? then 1 else 0 end) as j_belum,'
            . ' sum(case when aktif = 1 and selesai < ? then 1 else 0 end) as j_selesai,'
            . ' sum(case when aktif = 0 then 1 else 0 end) as j_nonaktif',
            [$hari, $hari, $hari, $hari]
        )->first();

        return [
            'berjalan' => (int) ($baris->j_berjalan ?? 0),
            'belum-mulai' => (int) ($baris->j_belum ?? 0),
            'selesai' => (int) ($baris->j_selesai ?? 0),
            'nonaktif' => (int) ($baris->j_nonaktif ?? 0),
        ];
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
            'kelompok' => $this->angkatanPerLayanan($terpilih),
            'tipe' => PromoLayanan::TIPE,
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
    private function angkatanPerLayanan(array $terpilih): array
    {
        $katalog = Layanan::katalog();
        $hariIni = now()->startOfDay();

        $semua = KategoriLayanan::query()
            ->orderByDesc('mulai')->orderBy('nama')
            ->get(['id', 'layanan', 'varian', 'nama', 'nama_ke', 'mulai', 'status', 'biaya', 'total_biaya']);

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

            $kelompok[$kunci]['baris'][] = [
                'id' => (string) $a->id,
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
        foreach (['nominal', 'batas_potongan'] as $medan) {
            if (! $request->exists($medan)) {
                continue;
            }

            $bersih = preg_replace('/\D+/', '', (string) $request->input($medan));

            $request->merge([$medan => $bersih === '' ? null : (int) $bersih]);
        }

        $isian = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
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
            'persen' => 'potongan persen',
            'nominal' => 'potongan rupiah',
            'batas_potongan' => 'batas potongan',
            'mulai' => 'tanggal mulai',
            'selesai' => 'tanggal selesai',
            'angkatan' => 'angkatan',
        ]);

        $persenan = $isian['tipe'] === PromoLayanan::PERSEN;

        return [
            'promo' => [
                'nama' => $isian['nama'],
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
