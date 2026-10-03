<?php

namespace App\Http\Controllers\account;

use App\Exports\PendaftaranLayananExport;
use App\Http\Controllers\Controller;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use App\Support\PesananPelanggan;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
 * Kelima layar lama TIDAK dibuang. Mereka punya kemampuan yang layar ini
 * sengaja tidak punya (menyunting, menghapus, menandai lunas, membalas
 * pemesanan), dan aturan kuotanya berbeda di tiap layanan. Layar ini mencari,
 * menghitung, dan mengekspor; tiap barisnya menautkan ke layar layanannya
 * untuk tindakan. Polanya sama dengan Angkatan Layanan, yang juga dibiarkan
 * berdampingan dengan dua layar kategori lamanya.
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
        $ringkasan = $this->ringkasan($pilihan['layanan']);

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

        $urut = array_key_exists((string) $request->input('urut'), Pendaftaran::URUTAN)
            ? (string) $request->input('urut')
            : 'waktu';

        $arah = $request->input('arah') === 'naik' ? 'asc' : 'desc';

        return [
            'cari' => $cari,
            'layanan' => $layanan,
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
            'adaSaringan' => $cari !== '' || $layanan !== '' || $keadaanDipilih !== '' || $bukti !== '',
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
    private function ringkasan(string $layanan): array
    {
        $mentah = Pendaftaran::kueri()
            ->when($layanan !== '', fn ($q) => $q->where('layanan', $layanan))
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
