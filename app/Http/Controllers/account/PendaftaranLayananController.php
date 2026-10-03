<?php

namespace App\Http\Controllers\account;

use App\Actions\Pendaftaran\HapusPendaftaran;
use App\Actions\Pendaftaran\UbahDataPendaftaran;
use App\Actions\Pendaftaran\UbahStatusPendaftaran;
use App\Exports\PendaftaranLayananExport;
use App\Http\Controllers\Controller;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PesananPelanggan;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Pendaftar seluruh layanan dalam satu daftar.
 *
 * Yang digantikan layar ini bukan satu layar, melainkan kebiasaan membuka
 * lima: pendaftar Scopus Camp, Analisis Bibliometrik, Scopus Kafe, Clinik
 * Scopus, dan Webinar Eksklusif masing-masing punya layarnya sendiri dengan
 * kotak pencariannya sendiri. Pertanyaan yang paling sering diajukan panitia —
 * "siapa saja yang belum bayar?" dan "orang ini mendaftar apa saja?" — tidak
 * bisa dijawab di satu pun dari kelimanya.
 *
 * Kelima layar lama DIBUANG, dan seluruh kemampuannya pindah ke sini:
 * melihat rincian, membetulkan data, memindahkan angkatan, memindahkan
 * status beserta email pemberitahuannya, dan menghapus beserta pengembalian
 * kuota serta pembersihan berkasnya.
 *
 * Tiga dari kelima layar itu juga TIDAK punya penjaga akses sama sekali —
 * terukur, pengguna berperan 'user' mendapat 200 di Scopus Camp, Scopus
 * Kafe, dan daftar Clinik Scopus, lalu bisa menyunting dan menghapus
 * pendaftaran orang lain. Satu lagi, Analisis Bibliometrik, galat 500 untuk
 * SEMUA orang termasuk administrator: `compact()` di pengendalinya memanggil
 * variabel yang tidak pernah didefinisikan. Jadi penyatuan ini sekaligus
 * menutup tiga lubang akses dan menghidupkan kembali satu layar yang mati.
 *
 * Aturan yang berbeda per layanan — medan yang boleh disunting, kosakata
 * status, surat yang terkirim, kuota — tinggal sebagai DATA di katalog
 * PendaftaranSemuaLayanan dan dikerjakan tiga tindakan di
 * App\Actions\Pendaftaran, bukan sebagai percabangan di pengendali ini.
 */
class PendaftaranLayananController extends Controller
{
    private const PER_HALAMAN = 20;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hanya orang dalam.
     *
     * Grup rute account/ hanya bermiddleware auth + terverifikasi — tidak ada
     * middleware peran sama sekali di sana — jadi tanpa penjagaan ini layar
     * ini terbuka untuk pelanggan mana pun yang punya akun. Dan isinya nama,
     * email, nomor telepon, serta nominal pembayaran 187 orang.
     */
    private function bolehMelihat(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    private function tolak()
    {
        return redirect()->route('account.dashboard.index')
            ->with('error', 'Anda tidak punya akses ke data pendaftar layanan.');
    }

    /**
     * Menghapus pendaftaran: ADMINISTRATOR saja.
     *
     * Aturan terketat di antara kelima layar lama, dan disengaja dipakai
     * untuk semuanya. Clinik Scopus memang sudah menuntut administrator;
     * Scopus Camp, Bibliometrik, dan Scopus Kafe tidak menuntut apa pun —
     * terukur, pengguna berperan 'user' mendapat 200 di ketiganya dan bisa
     * menghapus pendaftaran orang lain. Penghapusannya tidak bisa diurungkan
     * dan belum ada tong sampah, jadi yang dipakai aturan terketatnya.
     */
    private function bolehMenghapus(): bool
    {
        return (bool) Auth::user()?->adalahAdministrator();
    }

    /** Nama orang yang bertindak, untuk jejak di kolom catatan. */
    private function siapa(): string
    {
        return Auth::user()->full_name ?: ('pengguna #' . Auth::id());
    }

    /**
     * Halaman rincian satu pendaftaran.
     *
     * Satu halaman untuk kelima layanan, dengan bagian borang yang berbeda
     * per layanan — bukan lima halaman. Kuncinya SELALU dicocokkan ke katalog
     * tertutup lebih dulu: `$layanan` datang dari alamat halaman, dan
     * memakainya mentah berarti membiarkan nama kelas mana pun dipanggil.
     */
    public function rincian(string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $katalog = Pendaftaran::katalog();

        if (! array_key_exists($layanan, $katalog)) {
            abort(404);
        }

        $pendaftaran = Pendaftaran::temukan($layanan, $id);

        if ($pendaftaran === null) {
            abort(404);
        }

        // Barisnya dibaca juga lewat kueri gabungan supaya rincian memakai
        // keterangan yang SAMA dengan daftarnya — keadaan, bukti, sesi, dan
        // tautannya dirakit sekali di satu tempat.
        $baris = Pendaftaran::kueri()->where('layanan', $layanan)->where('id', $id)->first();

        /*
         * Angkatan yang boleh dipilih DIBATASI layanannya.
         *
         * Tanpa penyaring itu, borang Scopus Camp menawarkan angkatan
         * Bibliometrik — dan memindahkan pendaftaran ke angkatan layanan lain
         * membuat kuota keduanya salah tanpa ada yang menolak.
         */
        $angkatan = Pendaftaran::berangkatan($layanan)
            ? \App\KategoriLayanan::where('layanan', $layanan)
                ->orderByDesc('mulai')
                ->get(['id', 'nama', 'mulai', 'total_kuota', 'sisa_kuota'])
            : collect();

        return view('account.pendaftaran_layanan.rincian', [
            'layanan' => $layanan,
            'info' => $katalog[$layanan],
            'pendaftaran' => $pendaftaran,
            'baris' => $baris,
            'angkatan' => $angkatan,
            'pilihanStatus' => Pendaftaran::pilihanStatus($layanan),
            'medan' => UbahDataPendaftaran::medan($layanan),
            'bolehMenghapus' => $this->bolehMenghapus(),
        ]);
    }

    /** Menyimpan suntingan data satu pendaftaran. */
    public function ubahData(Request $request, string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        if (! array_key_exists($layanan, Pendaftaran::katalog())) {
            abort(404);
        }

        /*
         * Divalidasi di sini, bukan di dalam tindakannya: pesan galat harus
         * kembali ke borangnya beserta isian yang sudah diketik, dan itu
         * urusan lapisan HTTP.
         */
        $aturan = [];
        $medan = UbahDataPendaftaran::medan($layanan);

        foreach (['nama', 'nama_pemesan'] as $k) {
            if (isset($medan[$k])) {
                $aturan[$k] = ['required', 'string', 'max:255'];
            }
        }

        foreach (['email', 'email_pemesan'] as $k) {
            if (isset($medan[$k])) {
                // 'email' saja, bukan 'email:rfc,dns': alamat pendaftar yang
                // sudah ada memuat domain yang kadang tidak bisa dicari dari
                // peladen ini, dan menolaknya membuat baris lama tidak bisa
                // disunting sama sekali.
                $aturan[$k] = ['required', 'email', 'max:255'];
            }
        }

        foreach (['telp', 'telp_pemesan'] as $k) {
            if (isset($medan[$k])) {
                $aturan[$k] = ['required', 'string', 'max:30'];
            }
        }

        if (isset($medan['kategori_id'])) {
            $aturan['kategori_id'] = ['required', 'string', 'exists:kategori_layanan,id'];
        }

        if (isset($medan['jumlah_pendaftar'])) {
            $aturan['jumlah_pendaftar'] = ['required', 'integer', 'min:1', 'max:99'];
        }

        $request->validate($aturan);

        $hasil = (new UbahDataPendaftaran)->jalankan($layanan, $id, $request->all());

        if (! $hasil['berhasil']) {
            return back()->withInput()->with('error', $hasil['pesan']);
        }

        return redirect()
            ->route('account.pendaftaran-layanan.rincian', [$layanan, $id])
            ->with('sukses', $hasil['pesan']);
    }

    /** Memindahkan status satu pendaftaran. */
    public function ubahStatus(Request $request, string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        if (! array_key_exists($layanan, Pendaftaran::katalog())) {
            abort(404);
        }

        $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(Pendaftaran::pilihanStatus($layanan)))],
        ]);

        $hasil = (new UbahStatusPendaftaran)->jalankan(
            $layanan, $id, (string) $request->input('status'), $this->siapa()
        );

        if (! $hasil['berhasil']) {
            return back()->with('info', $hasil['pesan']);
        }

        /*
         * Layarnya mengatakan suratnya terkirim atau tidak.
         *
         * Pengirimannya sengaja dibungkus try/catch supaya peladen surat yang
         * bermasalah tidak menggagalkan perubahan status yang sudah tersimpan
         * — konsekuensinya kegagalannya hanya muncul di log, dan tanpa
         * kalimat ini panitia akan mengira pendaftarnya sudah diberi tahu.
         */
        $adaSurat = Pendaftaran::suratUntuk($layanan, (string) $request->input('status')) !== null;

        if (! $adaSurat) {
            return back()->with('sukses', $hasil['pesan']);
        }

        if ($hasil['surat']) {
            return back()->with('sukses', $hasil['pesan'] . ' Pemberitahuannya sudah dikirim ke emailnya.');
        }

        // Statusnya TETAP tersimpan; yang gagal cuma suratnya. Kalimatnya
        // menyebut keduanya supaya panitia tidak mengulang perubahan status
        // yang sudah berhasil, melainkan menghubungi pendaftarnya sendiri.
        return back()->with('error', $hasil['pesan']
            . ' Statusnya tersimpan, TETAPI emailnya gagal dikirim — beri tahu pendaftarnya sendiri.');
    }

    /** Menghapus satu pendaftaran beserta ekornya. */
    public function hapus(string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        if (! $this->bolehMenghapus()) {
            return back()->with('error',
                'Hanya administrator yang boleh menghapus pendaftaran. '
                . 'Ubah statusnya jadi dibatalkan kalau yang Anda maksud membatalkannya.');
        }

        if (! array_key_exists($layanan, Pendaftaran::katalog())) {
            abort(404);
        }

        $hasil = (new HapusPendaftaran)->jalankan($layanan, $id);

        if (! $hasil['berhasil']) {
            return back()->with('error', $hasil['pesan']);
        }

        return redirect()
            ->route('account.pendaftaran-layanan.index')
            ->with('sukses', $hasil['pesan']);
    }

    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $pilihan = $this->bacaPilihan($request);

        /*
         * Angka ubin dihitung dari SELURUH baris pada lingkup layanan yang
         * dipilih — bukan dari halaman yang sedang tampil, dan bukan pula
         * dari hasil yang sudah tersaring statusnya.
         *
         * Saringan layanan ikut dihormati karena ia memilih LINGKUP ("saya
         * sedang mengurus Scopus Camp"), sementara ubinnya memilah status di
         * dalam lingkup itu. Kalau ubinnya mengabaikan saringan layanan,
         * angkanya bercerita tentang kumpulan yang berbeda dari daftar di
         * bawahnya — dan itu jenis selisih yang membuat orang berhenti
         * mempercayai angkanya.
         */
        $ringkasan = $this->ringkasan($pilihan['layanan'], $pilihan['angkatan']);

        $kueri = $this->saring(Pendaftaran::kueri(), $pilihan);

        $urutSql = Pendaftaran::URUTAN[$pilihan['urut']];

        $baris = $kueri
            ->orderByRaw($urutSql . ' ' . $pilihan['arah'])
            // Pengurut kedua yang pasti unik. Tanpa ini, 91 baris
            // Bibliometrik yang statusnya sama persis boleh diurutkan ulang
            // sesuka basis data antar permintaan — dan baris yang sama bisa
            // muncul di dua halaman sekaligus, atau tidak muncul sama sekali.
            ->orderBy('p.id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('account.pendaftaran_layanan.index', [
            'baris' => $baris,
            'ringkasan' => $ringkasan,
            'katalog' => Pendaftaran::katalog(),
            'keadaan' => Pendaftaran::KEADAAN,
        ] + $pilihan);
    }

    /**
     * Pilihan dari alamat halaman, sudah dibersihkan.
     *
     * Dibaca di satu tempat karena dipakai tiga jalur: daftar, unduhan PDF,
     * dan unduhan lembar kerja. Dulu di layar lain hal ini ditulis ulang per
     * jalur, dan akibatnya berkas unduhan membawa saringan yang berbeda dari
     * yang terlihat di layar.
     *
     * @return array<string, mixed>
     */
    private function bacaPilihan(Request $request): array
    {
        $cari = trim((string) $request->input('cari'));

        // Daftar putih, bukan nilai mentah: tanpa ini siapa pun bisa
        // menyuntikkan nama kolom ke orderByRaw.
        $layanan = array_key_exists((string) $request->input('layanan'), Pendaftaran::katalog())
            ? (string) $request->input('layanan')
            : '';

        $keadaanDipilih = (string) $request->input('keadaan');

        // 'lain' bukan salah tulis: ia keadaan yang sah, yaitu nilai status
        // yang belum terdaftar di katalog. Lihat KEADAAN di kelas penyatunya.
        if ($keadaanDipilih !== 'lain' && ! array_key_exists($keadaanDipilih, Pendaftaran::KEADAAN)) {
            $keadaanDipilih = '';
        }

        /*
         * 'hilang' TIDAK termasuk, walau layarnya memang menandai bukti yang
         * berkasnya tidak ada di cakram (72 dari 183).
         *
         * Keberadaan berkas adalah pemeriksaan cakram, bukan kolom basis data,
         * jadi ia tidak bisa jadi `where` — dan menerima nilainya di sini akan
         * memberi saringan yang diam-diam tidak mengerjakan apa pun. Saringan
         * yang tidak bekerja lebih buruk daripada saringan yang tidak ada:
         * orangnya menyimpulkan tidak ada bukti yang hilang.
         */
        $bukti = in_array($request->input('bukti'), ['ada', 'belum'], true)
            ? (string) $request->input('bukti')
            : '';

        /*
         * Saringan angkatan, dipakai tautan dari layar Angkatan Layanan.
         *
         * Tautan di sana dulu mengirim `kategori` ke layar pendaftar lama,
         * dan layar itu TIDAK PERNAH membacanya — jadi menekan "lihat
         * pendaftar" pada satu angkatan menampilkan seluruh pendaftar
         * layanan itu, dan tidak ada yang tahu saringannya tidak bekerja.
         * Di sini ia benar-benar menyaring.
         */
        $angkatan = trim((string) $request->input('angkatan'));

        $urut = array_key_exists((string) $request->input('urut'), Pendaftaran::URUTAN)
            ? (string) $request->input('urut')
            : 'waktu';

        $arah = $request->input('arah') === 'naik' ? 'asc' : 'desc';

        return [
            'cari' => $cari,
            'layanan' => $layanan,
            'angkatan' => $angkatan,
            'keadaanDipilih' => $keadaanDipilih,
            'bukti' => $bukti,
            'urut' => $urut,
            'arah' => $arah,
            /*
             * SATU penanda "sedang menyaring", bukan syarat yang diulang di
             * tiap tempat yang membutuhkannya. Tanpa itu, keadaan kosong bisa
             * berbunyi "belum ada pendaftar" padahal ada 187 dan hanya
             * saringannya yang mengecualikan semuanya.
             */
            'adaSaringan' => $cari !== '' || $layanan !== '' || $keadaanDipilih !== ''
                || $bukti !== '' || $angkatan !== '',
        ];
    }

    /**
     * Saringan yang sama dipakai daftar dan kedua unduhan; ditulis sekali.
     *
     * @param  \Illuminate\Database\Query\Builder  $kueri
     */
    private function saring($kueri, array $p)
    {
        return $kueri
            ->when($p['layanan'] !== '', fn ($q) => $q->where('layanan', $p['layanan']))
            ->when($p['angkatan'] !== '', fn ($q) => $q->where('angkatan_id', $p['angkatan']))
            ->when($p['keadaanDipilih'] !== '', function ($q) use ($p) {
                if ($p['keadaanDipilih'] === 'lain') {
                    // Keranjang sisa, dan ia perlu ada: kolom statusnya varchar
                    // bebas, jadi layanan mana pun bisa menulis nilai baru kapan
                    // saja tanpa migrasi. Tanpa saringan ini, baris bernilai baru
                    // tidak bisa ditemukan lewat saringan mana pun.
                    return $q->whereNotIn('status', Pendaftaran::statusDikenal());
                }

                return $q->whereIn('status', Pendaftaran::KEADAAN[$p['keadaanDipilih']]['nilai']);
            })
            ->when($p['bukti'] === 'ada', fn ($q) => $q->whereNotNull('bukti')->where('bukti', '<>', ''))
            ->when($p['bukti'] === 'belum', fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('bukti')->orWhere('bukti', '');
            }))
            ->when($p['cari'] !== '', function ($q) use ($p) {
                $cari = $p['cari'];

                $q->where(function ($sub) use ($cari) {
                    foreach (['nomor', 'nama_orang', 'email', 'affiliasi'] as $kolom) {
                        $sub->orWhere($kolom, 'LIKE', '%' . $cari . '%');
                    }

                    /*
                     * Nomor telepon dicocokkan tanpa tanda baca, di KEDUA sisi.
                     *
                     * Terukur pada data yang ada: nomor tersimpan berbentuk
                     * '0822-2090-6000' di empat layanan sekaligus, sementara
                     * yang disalin orang dari WhatsApp berbentuk
                     * '082220906000' — tanpa pembersihan ini pencariannya
                     * mengembalikan NOL hasil. Bentuk kode negara ikut
                     * diterima: satu baris webinar tersimpan sebagai
                     * '17868678952' tanpa nol depan.
                     */
                    $angka = preg_replace('/\D+/', '', $cari) ?? '';

                    if ($angka === '') {
                        // LIKE '%%' akan mencocokkan semuanya; tanpa angka,
                        // pencarian nomor tidak ada gunanya.
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
            });
    }

    /**
     * Kolom telepon tanpa tanda baca, sebagai ungkapan SQL.
     *
     * Tanda yang dibuang sama dengan yang dipakai layar Data Pelanggan, supaya
     * dua layar tidak punya dua pengertian berbeda tentang "nomor yang sama".
     */
    private function telpAngka(): string
    {
        $bersih = 'telp';

        foreach (['-', ' ', '(', ')', '+', '.'] as $tanda) {
            $bersih = "REPLACE({$bersih}, '{$tanda}', '')";
        }

        return $bersih;
    }

    /**
     * Angka untuk ubin ringkasan dan jumlah uangnya.
     *
     * SATU kueri berkelompok untuk seluruh keadaan, bukan satu `count()` per
     * ubin: lima ubin berarti lima kali membaca gabungan kelima tabel.
     * Dikelompokkan menurut status mentah lalu dijumlahkan per keadaan di PHP,
     * sehingga menambah keadaan baru tidak menambah satu kueri pun.
     *
     * @return array<string, int>
     */
    private function ringkasan(string $layanan, string $angkatan = ''): array
    {
        $mentah = Pendaftaran::kueri()
            ->when($layanan !== '', fn ($q) => $q->where('layanan', $layanan))
            ->when($angkatan !== '', fn ($q) => $q->where('angkatan_id', $angkatan))
            ->selectRaw('status, count(*) as baris')
            ->selectRaw('SUM(CAST(jumlah AS UNSIGNED)) as orang')
            ->selectRaw('SUM(CAST(total AS DECIMAL(15,2))) as uang')
            ->selectRaw("SUM(CASE WHEN bukti IS NOT NULL AND bukti <> '' THEN 1 ELSE 0 END) as berbukti")
            ->groupBy('status')
            ->get();

        $hitung = array_fill_keys(array_merge(array_keys(Pendaftaran::KEADAAN), ['lain']), 0);

        $ringkasan = [
            'semua' => 0,
            'orang' => 0,
            'uang_lunas' => 0,
            // Yang paling menuntut tindakan: sudah mengunggah bukti transfer
            // tetapi statusnya masih menunggu. Terukur lima baris di tiga
            // layanan — dan sebelum ada layar ini, menemukannya menuntut
            // membuka tiga layar lalu menyaring masing-masing.
            'perlu_diperiksa' => 0,
        ];

        foreach ($mentah as $r) {
            $keadaan = Pendaftaran::keadaanDari($r->status);

            $hitung[$keadaan] += (int) $r->baris;
            $ringkasan['semua'] += (int) $r->baris;
            $ringkasan['orang'] += (int) $r->orang;

            if ($keadaan === 'lunas') {
                $ringkasan['uang_lunas'] += (int) $r->uang;
            }

            if ($keadaan === 'menunggu') {
                $ringkasan['perlu_diperiksa'] += (int) $r->berbukti;
            }
        }

        return $ringkasan + $hitung;
    }

    /**
     * Ringkasan saringan yang sedang dipakai, untuk dicetak di berkas unduhan.
     *
     * Berkas berisi 29 dari 187 baris tidak boleh terbaca seperti daftar yang
     * lengkap — dan berkas unduhan justru yang paling sering diteruskan ke
     * orang lain, terlepas dari layar tempat ia diunduh.
     *
     * @return array<string, string>
     */
    private function ringkasanSaringan(array $p): array
    {
        $katalog = Pendaftaran::katalog();

        return array_filter([
            'Kata kunci' => $p['cari'] !== '' ? $p['cari'] : null,
            'Layanan' => $p['layanan'] !== '' ? $katalog[$p['layanan']]['nama'] : null,
            'Angkatan' => $p['angkatan'] !== ''
                ? (Pendaftaran::namaAngkatan()[$p['angkatan']] ?? $p['angkatan'])
                : null,
            'Keadaan' => $p['keadaanDipilih'] !== ''
                ? ($p['keadaanDipilih'] === 'lain'
                    ? 'Status belum dikenali'
                    : Pendaftaran::KEADAAN[$p['keadaanDipilih']]['label'])
                : null,
            'Bukti bayar' => match ($p['bukti']) {
                'ada' => 'Sudah diunggah',
                'belum' => 'Belum diunggah',
                default => null,
            },
            'Urutan' => $p['urut'] . ' (' . ($p['arah'] === 'asc' ? 'menaik' : 'menurun') . ')',
        ]);
    }

    /**
     * Seluruh baris yang cocok saringan, tanpa penomoran halaman.
     *
     * Dipakai kedua unduhan. Yang diunduh orang hampir selalu yang sedang
     * dilihatnya, jadi saringannya ikut — bukan seluruh tabel.
     */
    private function untukEkspor(array $p)
    {
        return $this->saring(Pendaftaran::kueri(), $p)
            ->orderByRaw(Pendaftaran::URUTAN[$p['urut']] . ' ' . $p['arah'])
            ->orderBy('p.id')
            ->get();
    }

    /** Daftar pendaftar sebagai PDF, untuk dibaca dan dilampirkan. */
    public function eksporPdf(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $p = $this->bacaPilihan($request);

        $html = view('account.pendaftaran_layanan.ekspor-pdf', [
            'baris' => $this->untukEkspor($p),
            'saringan' => $this->ringkasanSaringan($p),
            'katalog' => Pendaftaran::katalog(),
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml($html);
        // Mendatar: sembilan kolom tidak muat di lebar potret, dan memaksanya
        // membuat nama serta email terpotong di tengah kata.
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        /*
         * Lewat response(), bukan $dompdf->stream(): stream memanggil header()
         * dan echo sendiri, sehingga kepalanya lewat dari lapisan respons
         * Laravel dan tidak bisa diuji.
         */
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pendaftar-layanan-'
                . now()->format('Y-m-d-Hi') . '.pdf"',
        ]);
    }

    /** Daftar pendaftar sebagai lembar kerja, untuk diolah lebih lanjut. */
    public function eksporExcel(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $p = $this->bacaPilihan($request);

        return Excel::download(
            new PendaftaranLayananExport(
                $this->untukEkspor($p),
                Pendaftaran::katalog(),
                $this->ringkasanSaringan($p),
            ),
            'pendaftar-layanan-' . now()->format('Y-m-d-Hi') . '.xlsx'
        );
    }
}
