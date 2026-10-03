<?php

namespace App\Http\Controllers\account;

use App\Actions\Pendaftaran\BuatPendaftaran;
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

    /**
     * Berapa hari sebuah pendaftaran boleh menunggu sebelum disebut
     * menggantung.
     *
     * Tujuh hari, dan angkanya bukan selera: Webinar Eksklusif melepas
     * kursinya sendiri sesudah 24 jam, tetapi empat layanan lain TIDAK punya
     * kedaluwarsa sama sekali — pendaftaran yang transfernya tidak pernah
     * datang akan menunggu selamanya tanpa ada yang menengok. Terukur di
     * basis data: 5 pendaftaran menunggu lebih dari 7 hari, 3 lebih dari 30
     * hari, dan 2 lebih dari 90 hari. Satu minggu batas yang masih masuk akal
     * untuk "mestinya sudah ditagih".
     */
    private const HARI_MENGGANTUNG = 7;

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
     * Borang mendaftarkan orang dari sisi panitia.
     *
     * Untuk yang mendaftar lewat WhatsApp, datang langsung, atau membayar di
     * tempat — sebelum ini tidak ada jalurnya sama sekali, sehingga daftar
     * pendaftar tidak pernah lengkap dan kuota angkatan tidak mencerminkan
     * kursi yang sebenarnya terpakai.
     */
    public function baru(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $katalog = Pendaftaran::katalogBisaDibuat();

        $terpilih = $request->input('layanan');
        $terpilih = array_key_exists((string) $terpilih, $katalog) ? (string) $terpilih : null;

        /*
         * Angkatan SELURUH layanan diambil sekali, lalu dikelompokkan — bukan
         * satu kueri per layanan saat orangnya berganti pilihan. Dengan empat
         * layanan itu berarti empat kueri untuk tabel yang isinya 59 baris,
         * dan jumlahnya tumbuh seiring katalognya bertambah.
         *
         * Yang sudah lewat tidak ditawarkan: mendaftarkan orang ke angkatan
         * yang acaranya kemarin bukan sesuatu yang perlu dimudahkan.
         */
        $angkatan = \App\KategoriLayanan::query()
            ->whereIn('layanan', array_keys($katalog))
            ->where(function ($q) {
                $q->whereNull('mulai')->orWhere('mulai', '>=', now()->subDay()->toDateString());
            })
            ->orderBy('mulai')
            // nama_ke ikut dibaca: lima angkatan Scopus Camp yang akan datang
            // bernama SAMA PERSIS, jadi tanpa nomor dan tanggalnya pilihan
            // di borang tidak bisa dibedakan satu pun.
            ->get(['id', 'layanan', 'varian', 'nama', 'nama_ke', 'mulai', 'biaya', 'total_biaya', 'total_kuota', 'sisa_kuota'])
            ->groupBy('layanan');

        return view('account.pendaftaran_layanan.baru', [
            'katalog' => $katalog,
            'terpilih' => $terpilih,
            'angkatan' => $angkatan,
            'alumni' => $this->persenAlumni(array_keys($katalog)),
            'caraBayar' => Pendaftaran::caraBayarPilihan(),
        ]);
    }

    /**
     * Potongan alumni tiap tarif aktif, berkunci "layanan|varian".
     *
     * Satu kueri untuk seluruh layanan, bukan satu per layanan — dan
     * dikunci varian pula, sebab satu layanan bisa punya beberapa tarif
     * dengan harga berbeda: Scopus Camp punya jawa dan luar_jawa. Memakai
     * tarif mana pun yang kebetulan ketemu duluan berarti menjanjikan
     * potongan milik varian lain.
     *
     * @param  array<int, string>  $layanan
     * @return array<string, int>
     */
    private function persenAlumni(array $layanan): array
    {
        return \App\ClinikScopusBiayaPersesi::query()
            ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
            ->whereIn('layanan', $layanan)
            ->whereNotNull('diskon_alumni_persen')
            ->where('diskon_alumni_persen', '>', 0)
            ->get(['layanan', 'varian', 'diskon_alumni_persen'])
            ->mapWithKeys(fn ($t) => [
                $t->layanan . '|' . ($t->varian ?? '') => (int) $t->diskon_alumni_persen,
            ])
            ->all();
    }

    /** Menyimpan pendaftaran yang dibuat panitia. */
    public function simpan(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $katalog = Pendaftaran::katalogBisaDibuat();

        $aturan = [
            'layanan' => ['required', Rule::in(array_keys($katalog))],
            'nama' => ['required', 'string', 'min:3', 'max:255'],
            // 'email' saja, bukan 'email:rfc,dns': sebagian domain pendaftar
            // tidak bisa dicari dari peladen ini, dan menolaknya membuat orang
            // yang sah tidak bisa didaftarkan sama sekali.
            'email' => ['required', 'email', 'max:255'],
            'telp' => ['required', 'string', 'max:30'],
            'affiliasi' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:1000'],
            'potongan' => ['nullable', 'string', 'max:20'],
            'kode_potongan' => ['nullable', 'string', 'max:40'],
            'alumni' => ['nullable', 'boolean'],
            /*
             * Dibatasi ke yang boleh dipilih panitia. 'doku' sengaja TIDAK
             * termasuk: nilainya ditulis jalur pendaftaran umum saat
             * tagihannya dibuat, dan menerimanya dari borang ini berarti baris
             * bertanda dibayar daring tanpa tagihan yang pernah ada.
             */
            'cara_bayar' => ['nullable', Rule::in(array_keys(Pendaftaran::caraBayarPilihan()))],
            'uang_diterima' => ['nullable', 'boolean'],
        ];

        $layanan = (string) $request->input('layanan');

        if (array_key_exists($layanan, $katalog) && $katalog[$layanan]['berangkatan']) {
            $aturan['kategori_id'] = ['required', 'string', 'exists:kategori_layanan,id'];
        } else {
            // Layanan tanpa angkatan tidak punya harga yang bisa diambil
            // sendiri; nominalnya memang harus diketik.
            $aturan['total'] = ['required', 'string'];
        }

        $request->validate($aturan, [], [
            'kategori_id' => 'angkatan',
            'telp' => 'nomor WhatsApp',
            'total' => 'total bayar',
            'cara_bayar' => 'cara bayar',
        ]);

        $hasil = (new BuatPendaftaran)->jalankan($layanan, $request->all(), $this->siapa());

        if (! $hasil['berhasil']) {
            return back()->withInput()->with('error', $hasil['pesan']);
        }

        /*
         * Diantar ke halaman RINCIAN barisnya, bukan kembali ke daftar.
         *
         * Yang hampir selalu dikerjakan sesudah mendaftarkan orang adalah
         * memeriksa nomor dan kode uniknya untuk dikirim ke orangnya — dan
         * keduanya baru dibuat sistem, jadi panitia belum pernah melihatnya.
         */
        return redirect()
            ->route('account.pendaftaran-layanan.rincian', [$layanan, $hasil['model']->getKey()])
            ->with('sukses', $hasil['pesan']);
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
            // Tab yang terbuka saat halaman dibuka. Datang dari session supaya
            // galat validasi dan penyimpanan yang berhasil keduanya mendarat
            // di tab yang bersangkutan.
            'tabAktif' => session('tab', 'ringkasan'),
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
         *
         * Semuanya 'sometimes', dan itu yang membuat borang bertab bisa
         * disimpan satu tab saja. Tanpa itu, menyimpan tab "Angkatan & bayar"
         * ditolak karena nama dan email tidak ikut terkirim — padahal keduanya
         * ada di tab lain dan tidak sedang diubah. Yang dijaga tetap sama:
         * medan yang DIKIRIM tidak boleh dikosongkan atau diisi sembarang.
         */
        $aturan = [];
        $medan = UbahDataPendaftaran::medan($layanan);

        foreach (['nama', 'nama_pemesan'] as $k) {
            if (isset($medan[$k])) {
                $aturan[$k] = ['sometimes', 'required', 'string', 'max:255'];
            }
        }

        foreach (['email', 'email_pemesan'] as $k) {
            if (isset($medan[$k])) {
                // 'email' saja, bukan 'email:rfc,dns': alamat pendaftar yang
                // sudah ada memuat domain yang kadang tidak bisa dicari dari
                // peladen ini, dan menolaknya membuat baris lama tidak bisa
                // disunting sama sekali.
                $aturan[$k] = ['sometimes', 'required', 'email', 'max:255'];
            }
        }

        foreach (['telp', 'telp_pemesan'] as $k) {
            if (isset($medan[$k])) {
                $aturan[$k] = ['sometimes', 'required', 'string', 'max:30'];
            }
        }

        if (isset($medan['kategori_id'])) {
            $aturan['kategori_id'] = ['sometimes', 'required', 'string', 'exists:kategori_layanan,id'];
        }

        if (isset($medan['jumlah_pendaftar'])) {
            $aturan['jumlah_pendaftar'] = ['sometimes', 'required', 'integer', 'min:1', 'max:99'];
        }

        $request->validate($aturan);

        $hasil = (new UbahDataPendaftaran)->jalankan($layanan, $id, $request->all());

        if (! $hasil['berhasil']) {
            return back()->withInput()
                ->with('error', $hasil['pesan'])
                ->with('tab', $this->tabDari(array_keys($request->all())));
        }

        return redirect()
            ->route('account.pendaftaran-layanan.rincian', [$layanan, $id])
            ->with('sukses', $hasil['pesan'])
            // Dikembalikan ke tab yang baru disimpan, bukan ke tab pertama:
            // orang yang membetulkan nominal ingin melihat hasilnya, bukan
            // mencari tabnya lagi.
            ->with('tab', $this->tabDari(array_keys($request->all())));
    }

    /**
     * Tab mana yang memuat medan-medan yang baru dikirim.
     *
     * Dipakai dua arah: mengembalikan orang ke tab yang baru ia simpan, dan
     * MEMBUKA tab tempat galat validasinya. Galat yang dilaporkan di tab
     * pertama sementara isiannya ada di tab keempat praktis tidak bisa
     * ditemukan — orangnya melihat pesan merah tanpa tahu isian mana.
     *
     * @param  array<int, string>  $medanKiriman
     */
    private function tabDari(array $medanKiriman): string
    {
        $peta = [
            'bayar' => ['kategori_id', 'jumlah_pendaftar', 'ppn', 'kode_unik',
                'kode_diskon', 'nominal_diskon', 'total_pembayaran',
                'total_keseluruhan_pembayaran'],
            'sesi' => ['tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai',
                'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran',
                'subtotal_pembayaran', 'sesi_kedua', 'sesi_ketiga',
                'tanggal_reschedule', 'group_wa'],
            'diri' => ['nama', 'nama_pemesan', 'email', 'email_pemesan', 'telp',
                'telp_pemesan', 'affiliasi', 'afiliasi_pemesan', 'note',
                'kendala', 'desc_kendala'],
        ];

        foreach ($peta as $tab => $daftar) {
            foreach ($medanKiriman as $k) {
                if (in_array($k, $daftar, true)) {
                    return $tab;
                }
            }
        }

        return 'diri';
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
            'pilihanAngkatan' => $this->pilihanAngkatan(),
        ] + $pilihan);
    }

    /**
     * Angkatan yang bisa dipilih di kartu saringan, dikelompokkan per layanan.
     *
     * HANYA yang benar-benar punya pendaftar — terukur 39 dari 59. Menawarkan
     * angkatan yang kosong berarti menyediakan pilihan yang pasti
     * mengembalikan nol baris, dan orang yang menekannya menyimpulkan
     * saringannya rusak.
     *
     * Satu kueri untuk id yang terpakai, lalu dipetakan dari keterangan
     * angkatan yang sudah dibaca sekali per permintaan — bukan satu kueri per
     * angkatan.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function pilihanAngkatan(): array
    {
        $terpakai = Pendaftaran::kueri()
            ->whereNotNull('angkatan_id')
            ->distinct()
            ->pluck('angkatan_id')
            ->all();

        $lengkap = Pendaftaran::angkatanLengkap();
        $katalog = Pendaftaran::katalog();
        $keluar = [];

        foreach ($terpakai as $id) {
            $a = $lengkap[$id] ?? null;

            if ($a === null || ! isset($katalog[$a['layanan']])) {
                continue;
            }

            $keluar[$a['layanan']][] = [
                'id' => $id,
                'ringkas' => $a['ringkas'],
                'nomor' => $a['nomor'],
                'mulai' => $a['mulai'],
            ];
        }

        // Terbaru di atas: angkatan yang sedang berjalan jauh lebih sering
        // dicari daripada yang sudah lewat bertahun-tahun.
        foreach ($keluar as $layanan => $daftar) {
            usort($daftar, fn ($x, $y) => strcmp((string) $y['mulai'], (string) $x['mulai']));
            $keluar[$layanan] = $daftar;
        }

        return $keluar;
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
         * Rentang tanggal pendaftaran.
         *
         * DIKEMBALIKAN, bukan fitur baru: ketiga layar pendaftaran yang
         * dibuang punya kendali `tanggal_awal` dan `tanggal_akhir` yang
         * benar-benar bekerja di markahnya — Scopus Kafe bahkan membuka pada
         * bulan berjalan. Penyatuannya diam-diam mencabutnya, dan itu persis
         * yang dilarang aturan "menyatukan dua layar tidak boleh mencabut
         * kemampuannya".
         *
         * Dibaca lewat Carbon supaya "2026-13-45" tidak sampai ke SQL sebagai
         * untaian yang membuat kuerinya sunyi tanpa hasil.
         */
        $dari = $this->tanggal($request->input('dari'));
        $sampai = $this->tanggal($request->input('sampai'));

        /*
         * Saringan cara bayar. Daftar putihnya SELURUH katalog, bukan hanya
         * yang boleh dipilih panitia: baris berbayar DOKU tidak bisa dibuat
         * dari layar ini, tetapi tetap harus bisa dicari dari sini.
         */
        $caraBayar = array_key_exists((string) $request->input('bayar'), Pendaftaran::CARA_BAYAR)
            ? (string) $request->input('bayar')
            : '';

        /*
         * Pendaftaran yang menunggu terlalu lama. Nilainya cuma ada/tidak,
         * jadi apa pun selain '1' dianggap tidak dipakai.
         */
        $menggantung = $request->input('lama') === '1';

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
            'dari' => $dari,
            'sampai' => $sampai,
            'menggantung' => $menggantung,
            'keadaanDipilih' => $keadaanDipilih,
            'bukti' => $bukti,
            'caraBayar' => $caraBayar,
            'urut' => $urut,
            'arah' => $arah,
            /*
             * SATU penanda "sedang menyaring", bukan syarat yang diulang di
             * tiap tempat yang membutuhkannya. Tanpa itu, keadaan kosong bisa
             * berbunyi "belum ada pendaftar" padahal ada 187 dan hanya
             * saringannya yang mengecualikan semuanya.
             */
            'adaSaringan' => $cari !== '' || $layanan !== '' || $keadaanDipilih !== ''
                || $bukti !== '' || $angkatan !== '' || $dari !== '' || $sampai !== ''
                || $caraBayar !== '' || $menggantung,
        ];
    }

    /**
     * Satu tanggal kiriman, dibakukan jadi Y-m-d — atau untaian kosong.
     *
     * Dibaca lewat Carbon, bukan diteruskan apa adanya: tanggal yang tidak
     * masuk akal seperti "2026-13-45" diterima SQL sebagai untaian dan
     * kuerinya mengembalikan nol baris tanpa galat, jadi orangnya menyimpulkan
     * datanya yang tidak ada.
     */
    private function tanggal($nilai): string
    {
        $teks = trim((string) $nilai);

        if ($teks === '') {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $teks)->toDateString();
        } catch (\Throwable $e) {
            return '';
        }
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
            /*
             * Batas akhirnya memakai `<` pada HARI BERIKUTNYA, bukan `<=` pada
             * harinya: kolom waktunya bertimestamp, jadi `<= '2026-10-03'`
             * berarti `<= 2026-10-03 00:00:00` dan membuang seluruh
             * pendaftaran yang terjadi pada hari yang dipilih orangnya.
             */
            ->when($p['dari'] !== '', fn ($q) => $q->where('waktu', '>=', $p['dari'] . ' 00:00:00'))
            ->when($p['sampai'] !== '', fn ($q) => $q->where(
                'waktu', '<', \Illuminate\Support\Carbon::parse($p['sampai'])->addDay()->toDateString() . ' 00:00:00'
            ))
            ->when($p['caraBayar'] !== '', fn ($q) => $q->where('cara_bayar', $p['caraBayar']))
            /*
             * Yang membayar di tempat DIKECUALIKAN dari penanda menggantung.
             *
             * Penanda ini mencari pendaftar yang sudah lama menunggu tanpa
             * kabar supaya ditagih. Pembayar tunai tidak sedang ditunggu
             * transfernya — ia menyerahkan uangnya saat datang — jadi
             * memasukkannya berarti daftar tagihan yang isinya orang yang
             * tidak perlu ditagih, dan daftar seperti itu berhenti dibaca.
             */
            ->when($p['menggantung'], fn ($q) => $q
                ->whereIn('status', Pendaftaran::KEADAAN['menunggu']['nilai'])
                ->where('cara_bayar', '<>', 'tunai')
                ->where('waktu', '<', now()->subDays(self::HARI_MENGGANTUNG)->toDateTimeString()))
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
                     * Nama dan NOMOR angkatannya ikut dicari.
                     *
                     * Orang mencari lewat apa yang mereka ingat, dan untuk
                     * merekap yang diingat biasanya "Yogyakarta" atau "202" —
                     * bukan nomor pendaftaran seseorang. Sebelum ini keduanya
                     * mengembalikan nol hasil, sebab kueri gabungannya cuma
                     * membawa id angkatannya.
                     *
                     * Dicocokkan di PHP atas keterangan angkatan yang sudah
                     * dibaca sekali per permintaan, lalu diserahkan sebagai
                     * daftar id — jadi tidak ada join maupun kueri tambahan.
                     */
                    $idAngkatan = [];

                    foreach (Pendaftaran::angkatanLengkap() as $id => $a) {
                        $cocokNama = stripos($a['nama'], $cari) !== false;
                        // Nomornya dicocokkan PERSIS, bukan sebagian: dengan
                        // LIKE, mencari "2" akan menarik angkatan ke-2, ke-20,
                        // ke-200, dan ke-202 sekaligus.
                        $cocokNomor = $a['nomor'] !== null && $a['nomor'] === $cari;

                        if ($cocokNama || $cocokNomor) {
                            $idAngkatan[] = $id;
                        }
                    }

                    if ($idAngkatan !== []) {
                        $sub->orWhereIn('angkatan_id', $idAngkatan);
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

        /*
         * Pendaftaran yang menunggu terlalu lama, dihitung terpisah.
         *
         * Tidak bisa ikut kueri berkelompok di atas: syaratnya menyangkut
         * WAKTU tiap baris, bukan statusnya saja, jadi pengelompokan menurut
         * status tidak bisa menjawabnya. Satu kueri tambahan — halamannya jadi
         * empat kueri, dan jumlahnya tidak tumbuh seiring data.
         */
        $ringkasan['menggantung'] = (int) Pendaftaran::kueri()
            ->when($layanan !== '', fn ($q) => $q->where('layanan', $layanan))
            ->when($angkatan !== '', fn ($q) => $q->where('angkatan_id', $angkatan))
            ->whereIn('status', Pendaftaran::KEADAAN['menunggu']['nilai'])
            ->where('cara_bayar', '<>', 'tunai')
            ->where('waktu', '<', now()->subDays(self::HARI_MENGGANTUNG)->toDateTimeString())
            ->count();

        $ringkasan['hari_menggantung'] = self::HARI_MENGGANTUNG;

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
            'Cara bayar' => $p['caraBayar'] !== ''
                ? Pendaftaran::CARA_BAYAR[$p['caraBayar']]['label']
                : null,
            /*
             * Rentang tanggalnya WAJIB tercetak di berkas unduhan. Berkas
             * berisi pendaftaran satu bulan yang tidak menyebut bulannya
             * terbaca persis seperti daftar yang lengkap — dan berkas unduhan
             * justru yang paling sering diteruskan ke orang lain.
             */
            'Tanggal daftar' => $this->kalimatRentang($p['dari'], $p['sampai']),
            'Menunggu lebih dari' => $p['menggantung'] ? self::HARI_MENGGANTUNG . ' hari' : null,
            'Urutan' => $p['urut'] . ' (' . ($p['arah'] === 'asc' ? 'menaik' : 'menurun') . ')',
        ]);
    }

    /**
     * Rentang tanggal sebagai satu kalimat, atau null kalau tidak dipakai.
     *
     * Ketiga bentuknya disebut berbeda — hanya awal, hanya akhir, atau
     * keduanya — sebab "Tanggal daftar: 2026-10-01" tidak memberi tahu apakah
     * itu batas bawah atau batas atas.
     */
    private function kalimatRentang(string $dari, string $sampai): ?string
    {
        $rapi = fn (string $t) => \Illuminate\Support\Carbon::parse($t)->translatedFormat('d M Y');

        if ($dari !== '' && $sampai !== '') {
            return $rapi($dari) . ' sampai ' . $rapi($sampai);
        }

        if ($dari !== '') {
            return 'sejak ' . $rapi($dari);
        }

        if ($sampai !== '') {
            return 'sampai ' . $rapi($sampai);
        }

        return null;
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
