<?php

namespace App\Http\Controllers\account;

use App\AktivitasMasuk;
use App\Exports\PelangganExport;
use App\Http\Controllers\Controller;
use App\Support\PesananPelanggan;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
        // Bukan nama kolom: jumlah pesanan tidak ada di tabel users, ia
        // dirakit PesananPelanggan dari empat tabel layanan. Ditangani
        // tersendiri di urutkanPesanan().
        'pesanan' => null,
    ];

    /**
     * Tabel yang menghalangi penghapusan pelanggan, beserta kolom dan sebutannya.
     *
     * Semuanya berkunci asing ON DELETE NO ACTION ke users, jadi menghapus
     * pelanggan yang punya salah satunya membuat MySQL menolak dengan galat
     * 1451 — dan tanpa pemeriksaan ini penolakan itu sampai ke layar sebagai
     * galat 500 tanpa keterangan.
     */
    private const PENGHALANG_HAPUS = [
        ['clinikscopus_pemesanan', 'customer_id', 'pesanan Clinik Scopus'],
        ['clinik_scopus_testimoni', 'customer_id', 'testimoni Clinik Scopus'],
        ['clinikscopus', 'user_id', 'sesi Clinik Scopus'],
        ['artikel', 'user_id', 'artikel'],
        ['artikel_komentar', 'user_id', 'komentar artikel'],
        ['todolist', 'user_id', 'catatan tugas'],
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

        // Dua saringan pintasan yang dipasang ubin ringkasan.
        $punyaPesanan = $request->input('pesanan') === 'ada';
        $baru = $request->input('baru') === '30';

        $dasar = User::query()->where('peran', User::PERAN_PELANGGAN);

        /*
         * Jumlah pesanan seluruh pelanggan dihitung SEKALI di sini.
         *
         * Dulu dua kali: sekali untuk ubin ringkasan dan sekali lagi untuk
         * baris yang tampil, masing-masing membaca keempat tabel layanan —
         * delapan pembacaan tabel per muat halaman padahal empat sudah cukup.
         * Petanya berkunci id, jadi tampilan tinggal mencarinya per baris.
         *
         * Ubin ketiga dulu "Email terverifikasi", dan itu angka mati: terukur
         * 65 aktif dan 65 terverifikasi, NOL yang berbeda ke salah satu arah,
         * sebab verifyEmail() menyetel keduanya sekaligus.
         */
        $semua = (clone $dasar)->get();
        $pesanan = PesananPelanggan::ringkas($semua);

        // Angkanya dari SELURUH pelanggan, bukan dari halaman yang sedang
        // tampil — angka yang berubah tiap ganti halaman menyesatkan.
        $ringkasan = [
            'total' => $semua->count(),
            'aktif' => $semua->where('status', 'active')->count(),
            'memesan' => count($pesanan),
            'baru' => $semua->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $disaring = $this->saring($dasar, $cari, $status, $verifikasi, $punyaPesanan, $baru, $pesanan);

        if ($urut === 'pesanan') {
            $pelanggan = $this->urutkanPesanan($disaring, $arah, $request, $pesanan);
        } else {
            $pelanggan = $disaring
                ->orderBy(self::URUTAN[$urut], $arah)
                ->paginate(self::PER_HALAMAN)
                ->withQueryString();
        }

        return view('account.customer.index', compact(
            'pelanggan', 'ringkasan', 'cari', 'status', 'verifikasi', 'urut', 'arah', 'pesanan'
        ) + ['punyaPesanan' => $punyaPesanan, 'baru' => $baru]);
    }

    /**
     * Mengurutkan berdasarkan jumlah pesanan.
     *
     * Tidak bisa jadi orderBy: jumlahnya tidak ada di tabel users, melainkan
     * dirakit dari empat tabel layanan yang penautannya bertingkat (nomor
     * telepon, lalu email, lalu nama) dan hanya bisa diselesaikan sesudah
     * seluruh akun dilihat.
     *
     * Jadi seluruh pelanggan yang cocok saringan dimuat, diurutkan, lalu
     * dipenggal sendiri jadi halaman. Harganya: satu halaman menarik seluruh
     * baris yang cocok, bukan dua belas. Pada ratusan pelanggan itu murah, dan
     * hanya terjadi saat kolom ini yang dipakai mengurutkan — lima urutan
     * lainnya tetap lewat SQL seperti biasa.
     */
    private function urutkanPesanan($kueri, string $arah, Request $request, array $ringkas): LengthAwarePaginator
    {
        $semua = $kueri->orderBy('full_name')->get();

        $terurut = $semua->sortBy(
            fn ($orang) => $ringkas[$orang->id]['jumlah'] ?? 0,
            SORT_REGULAR,
            $arah === 'desc'
        )->values();

        $halaman = max(1, (int) $request->input('page', 1));

        return new LengthAwarePaginator(
            $terurut->slice(($halaman - 1) * self::PER_HALAMAN, self::PER_HALAMAN)->values(),
            $terurut->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Kolom telp tanpa tanda baca, sebagai pecahan SQL.
     *
     * Diperiksa pada data yang ada: hanya "-" (250 kali) dan "+" (6 kali) yang
     * muncul. Spasi, kurung, dan titik ikut dibuang juga supaya bentuk yang
     * belum pernah masuk tidak lolos begitu saja nanti.
     */
    private function telpAngka(): string
    {
        $bersih = 'telp';

        foreach (['-', ' ', '(', ')', '+', '.'] as $tanda) {
            $bersih = "REPLACE({$bersih}, '{$tanda}', '')";
        }

        return $bersih;
    }

    /** Saringan yang sama dipakai daftar dan ekspor; ditulis sekali di sini. */
    private function saring($kueri, string $cari, $status, $verifikasi, bool $punyaPesanan = false, bool $baru = false, array $pesanan = [])
    {
        return $kueri
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    foreach (['full_name', 'username', 'email'] as $kolom) {
                        $sub->orWhere($kolom, 'LIKE', '%' . $cari . '%');
                    }

                    /*
                     * Nomor telepon dicocokkan tanpa tanda baca, di KEDUA sisi.
                     *
                     * Terukur: 93 dari 101 nomor pelanggan bertanda hubung,
                     * sehingga mencari "085842761933" atas nomor tersimpan
                     * "0858-4276-1933" mengembalikan NOL hasil — dan yang
                     * disalin orang dari WhatsApp memang tidak bertanda hubung.
                     * Bentuk kode negara ikut diterima: "6285842761933" dan
                     * "+6285842761933" menunjuk orang yang sama dengan "0858...".
                     */
                    $angka = preg_replace('/\D+/', '', $cari) ?? '';

                    if ($angka === '') {
                        // Tanpa angka sama sekali, pencarian nomor tidak ada
                        // gunanya — dan LIKE '%%' akan mencocokkan semuanya.
                        $sub->orWhere('telp', 'LIKE', '%' . $cari . '%');

                        return;
                    }

                    $bentuk = array_unique(array_filter([
                        $angka,
                        PesananPelanggan::nomorBaku($angka),
                    ]));

                    foreach ($bentuk as $b) {
                        $sub->orWhereRaw($this->telpAngka() . ' LIKE ?', ['%' . $b . '%']);
                    }
                });
            })
            ->when($status === 'aktif', fn ($q) => $q->where('status', 'active'))
            ->when($status === 'nonaktif', fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('status')->orWhere('status', '!=', 'active');
            }))
            ->when($verifikasi === 'sudah', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($verifikasi === 'belum', fn ($q) => $q->whereNull('email_verified_at'))
            // Pintasan dari ubin ringkasan. Daftar id-nya datang dari peta yang
            // sudah dihitung, jadi tidak ada kueri tambahan.
            ->when($punyaPesanan, fn ($q) => $q->whereIn('id', array_keys($pesanan) ?: [0]))
            ->when($baru, fn ($q) => $q->where('created_at', '>=', now()->subDays(30)));
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
            /*
             * Karyawan boleh MELIHAT data pelanggan, tetapi hanya administrator
             * yang boleh mengubahnya — PenggunaController yang menerima
             * kirimannya menolak sisanya dengan 403. Tanpa penanda ini layarnya
             * tetap menyuguhkan borang yang tampak bisa diisi, dan penolakannya
             * baru datang sesudah orang mengetik.
             */
            'bolehUbah' => (bool) Auth::user()?->adalahAdministrator(),
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

        $pelanggan = $this->terurutUntukEkspor($request, $cari, $status, $verifikasi);

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

    /**
     * Daftar untuk diunduh: saringan DAN urutan yang sedang dipakai di layar.
     *
     * Saringannya memang sudah ikut sejak awal, tetapi urutannya dulu selalu
     * dipaksa menurut nama. Akibatnya orang yang mengurutkan daftarnya menurut
     * jumlah pesanan lalu menekan Unduh mendapat berkas yang urutannya berbeda
     * dari yang baru saja dilihatnya — dan mengira unduhannya salah isi.
     */
    private function terurutUntukEkspor(Request $request, string $cari, $status, $verifikasi)
    {
        $punyaPesanan = $request->input('pesanan') === 'ada';
        $baru = $request->input('baru') === '30';

        $dasar = User::query()->where('peran', User::PERAN_PELANGGAN);
        $pesanan = PesananPelanggan::ringkas((clone $dasar)->get());

        $kueri = $this->saring($dasar, $cari, $status, $verifikasi, $punyaPesanan, $baru, $pesanan);

        // Tanpa urut di alamatnya, berkasnya tetap menurut nama seperti dulu —
        // itu urutan yang paling masuk akal untuk daftar yang dibaca orang.
        if (! array_key_exists((string) $request->input('urut'), self::URUTAN)) {
            return $kueri->orderBy('full_name')->get();
        }

        $urut = (string) $request->input('urut');
        $arah = $request->input('arah') === 'naik' ? 'asc' : 'desc';

        if ($urut !== 'pesanan') {
            return $kueri->orderBy(self::URUTAN[$urut], $arah)->get();
        }

        return $kueri->orderBy('full_name')->get()
            ->sortBy(fn ($o) => $pesanan[$o->id]['jumlah'] ?? 0, SORT_REGULAR, $arah === 'desc')
            ->values();
    }

    /**
     * Mengubah status banyak pelanggan sekaligus.
     *
     * Hanya mengaktifkan dan menonaktifkan — TIDAK menghapus. Penghapusan
     * massal berarti satu salah klik menghilangkan puluhan akun untuk
     * selamanya, dan di sini tidak ada tong sampah yang bisa mengembalikannya.
     */
    public function massal(Request $request)
    {
        if (! Auth::user()?->adalahAdministrator()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh mengubah banyak pelanggan sekaligus.',
            ], 403);
        }

        $data = $request->validate([
            'aksi' => ['required', 'in:aktifkan,nonaktifkan'],
            'uuid' => ['required', 'array', 'min:1', 'max:200'],
            'uuid.*' => ['uuid'],
        ], [
            'uuid.required' => 'Pilih dulu pelanggan yang ingin diubah.',
            'uuid.max' => 'Paling banyak 200 pelanggan sekali jalan.',
        ]);

        $aktif = $data['aksi'] === 'aktifkan';

        /*
         * Dibatasi peran pelanggan juga, bukan hanya oleh daftar uuid-nya.
         *
         * uuid yang dikirim datang dari peramban; tanpa batas ini, satu uuid
         * karyawan yang diselipkan ke kiriman bisa ikut dinonaktifkan dari
         * layar yang seharusnya cuma menyentuh pelanggan.
         */
        $jumlah = User::whereIn('uuid', $data['uuid'])
            ->where('peran', User::PERAN_PELANGGAN)
            ->update(['status' => $aktif ? 'active' : 'non active']);

        return response()->json([
            'success' => true,
            'jumlah' => $jumlah,
            'message' => $jumlah . ' pelanggan ' . ($aktif ? 'diaktifkan.' : 'dinonaktifkan.'),
        ]);
    }

    /**
     * Menghapus pelanggan; hanya administrator.
     *
     * Penghapusannya PERMANEN — tabel users tidak punya deleted_at, jadi tidak
     * ada tong sampah untuk mengembalikannya.
     *
     * Sebelum ini, menghapus pelanggan yang punya jejak di layanan membuat
     * MySQL menolak dengan galat 1451 yang tidak ditangkap siapa pun; yang
     * sampai ke layar galat 500, dan toast-nya berbunyi "Tidak bisa
     * menghubungi peladen" — padahal peladennya terhubung dan justru
     * menjalankan tugasnya. Terukur, 2 dari 102 pelanggan berada dalam keadaan
     * itu, dan angkanya naik seiring pesanan bertambah.
     */
    public function destroy(User $pelanggan)
    {
        if (! Auth::user()?->adalahAdministrator()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh menghapus pelanggan.',
            ], 403);
        }

        abort_unless($pelanggan->adalahPelanggan(), 404);

        /*
         * Diperiksa lebih dulu, bukan dicoba lalu ditangkap.
         *
         * Galat basis data hanya menyebut nama kunci asingnya — "a foreign key
         * constraint fails (rsc.clinik_scopus_testimoni ...)" — dan itu tidak
         * berarti apa-apa bagi yang membacanya. Diperiksa di sini, jawabannya
         * bisa menyebut APA yang menghalangi dan APA yang sebaiknya dilakukan.
         */
        $penghalang = $this->penghalangHapus($pelanggan);

        if ($penghalang !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan ini tidak bisa dihapus karena masih punya '
                    . $this->rangkai($penghalang) . '. Nonaktifkan akunnya saja '
                    . 'kalau tidak ingin dipakai lagi — riwayat layanannya tetap utuh.',
            ], 409);
        }

        try {
            DB::transaction(function () use ($pelanggan) {
                // Riwayat masuk tidak punya kunci asing ke users, jadi ia TIDAK
                // ikut terhapus sendiri dan akan tertinggal sebagai baris yatim
                // yang menunjuk akun yang sudah tidak ada. Dihapus di sini
                // supaya janji "riwayatnya ikut terhapus" di dialog konfirmasi
                // benar-benar ditepati.
                AktivitasMasuk::where('user_id', $pelanggan->getKey())->delete();

                $pelanggan->delete();
            });
        } catch (QueryException $e) {
            // Jaring terakhir: kunci asing baru bisa ditambahkan kapan saja
            // tanpa daftar di atas ikut diperbarui.
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Pelanggan ini masih tertaut ke data lain, jadi belum bisa dihapus. '
                    . 'Nonaktifkan akunnya saja.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus.',
        ]);
    }

    /**
     * Sebutan apa saja yang menghalangi penghapusan pelanggan ini.
     *
     * @return array<int, string>
     */
    private function penghalangHapus(User $pelanggan): array
    {
        $ada = [];

        foreach (self::PENGHALANG_HAPUS as [$tabel, $kolom, $sebutan]) {
            $jumlah = DB::table($tabel)->where($kolom, $pelanggan->getKey())->count();

            if ($jumlah > 0) {
                $ada[] = $jumlah . ' ' . $sebutan;
            }
        }

        return $ada;
    }

    /** "a", "a dan b", atau "a, b, dan c" — bukan daftar berkoma yang kaku. */
    private function rangkai(array $bagian): string
    {
        if (count($bagian) === 1) {
            return $bagian[0];
        }

        $akhir = array_pop($bagian);

        return implode(', ', $bagian) . ' dan ' . $akhir;
    }

    /**
     * Mengunduh daftar pelanggan sebagai lembar kerja.
     *
     * Berdampingan dengan unduhan PDF, bukan menggantikannya: PDF untuk dibaca
     * dan dilampirkan, lembar kerja untuk diolah. Saringannya sama persis
     * dengan yang sedang dipakai di layar, seperti ekspor PDF.
     */
    public function eksporExcel(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke data pelanggan.');
        }

        $pelanggan = $this->terurutUntukEkspor(
            $request,
            trim((string) $request->input('cari')),
            $request->input('status'),
            $request->input('verifikasi')
        );

        return Excel::download(
            new PelangganExport($pelanggan, PesananPelanggan::ringkas($pelanggan)),
            'data-pelanggan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
