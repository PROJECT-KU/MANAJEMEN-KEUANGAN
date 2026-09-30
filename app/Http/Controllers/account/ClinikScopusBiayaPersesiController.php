<?php

namespace App\Http\Controllers\account;

use App\ClinikScopusBiayaPersesi;
use App\Http\Controllers\Controller;
use Dompdf\Dompdf;
use App\Layanan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Tarif seluruh layanan jasa — satu layar untuk lima layanan.
 *
 * Sebelumnya tiap layanan mengurus harganya sendiri-sendiri: Clinik Scopus
 * punya tabel tarif, Bibliometrik dan Scopus Camp menyimpannya per angkatan,
 * Scopus Kafe diketik ulang di tiap pendaftaran, dan Online Training tidak
 * tercatat di mana pun. Akibatnya harga yang sama diketik berkali-kali, dan
 * tidak ada satu tempat pun yang bisa ditanya "sekarang berapa".
 *
 * Layar ini jadi acuannya. Angkatan dan pendaftaran tetap menyimpan harganya
 * sendiri — pesanan yang sudah terjadi tidak boleh berubah harga hanya karena
 * tarifnya dinaikkan — tetapi mereka mengisi diri dari sini, jadi admin tidak
 * mengetik ulang angka maupun daftar fasilitasnya.
 */
class ClinikScopusBiayaPersesiController extends Controller
{
    private const PER_HALAMAN = 8;

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function bolehMelihat(): bool
    {
        return (bool) Auth::user()?->adalahOrangDalam();
    }

    /** Mengubah tarif menyentuh harga yang ditagihkan; administrator saja. */
    private function bolehMengubah(): bool
    {
        return (bool) Auth::user()?->adalahAdministrator();
    }

    public function index(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke tarif layanan.');
        }

        /*
         * Kartu disusun dari KATALOG, bukan dari isi tabel.
         *
         * Dengan begitu layanan yang tarifnya belum pernah disetel tetap
         * muncul — lengkap dengan tanda bahwa ia belum punya harga. Disusun
         * dari isi tabel, layanan seperti itu hilang sama sekali dari layar,
         * dan tidak ada yang tahu ia terlewat.
         */
        $kartu = [];

        /*
         * Layanannya diambil sebagai model, bukan cuma katalog, karena layarnya
         * juga menyuguhkan pengaturan layanan itu sendiri — dan pengaturannya
         * butuh id serta daftar varian aslinya.
         */
        /*
         * Penyetel tiap tarif dimuat sekali untuk semua kartu. Dibaca lewat
         * relasi per kartu, tiap kartu yang punya jejak menambah satu kueri
         * ke tabel users — belum terasa sekarang karena baru sedikit baris
         * yang punya jejaknya, dan justru akan muncul begitu layarnya dipakai.
         */
        $penyetel = \App\User::whereIn('id', collect($kartu)->pluck('tarif.penginput_id')->filter()->unique())
            ->get(['id', 'full_name', 'username'])->keyBy('id');

        foreach ($kartu as $i => $k) {
            if ($k['tarif'] && $k['tarif']->penginput_id) {
                $kartu[$i]['tarif']->setRelation('penginput', $penyetel[$k['tarif']->penginput_id] ?? null);
            }
        }

        $model = Layanan::aktifBerurutan();
        $berlaku = ClinikScopusBiayaPersesi::semuaYangBerlaku();

        foreach (Layanan::katalog() as $kunci => $tentang) {
            $varian = $tentang['varian'] ?: [null => null];

            foreach ($varian as $kodeVarian => $namaVarian) {
                $kartu[] = [
                    'layanan' => $kunci,
                    'varian' => $kodeVarian ?: null,
                    'nama' => $tentang['nama'],
                    'namaVarian' => $namaVarian,
                    'satuan' => $tentang['satuan'],
                    'ikon' => $tentang['ikon'],
                    'warna' => $tentang['warna'],
                    'tarif' => $berlaku[$kunci . '|' . ($kodeVarian ?: '')] ?? null,
                    // Hanya kartu PERTAMA tiap layanan yang menyuguhkan tombol
                    // pengaturan; dua kartu varian mengatur layanan yang sama,
                    // dan dua tombol untuk satu hal cuma membingungkan.
                    'pengatur' => $kodeVarian === array_key_first($varian) ? ($model[$kunci] ?? null) : null,
                ];
            }
        }

        /*
         * Riwayat bisa disaring per layanan. Dengan katalog yang kini boleh
         * tumbuh sendiri, riwayat semua layanan bercampur jadi satu daftar
         * yang tidak bisa dibaca.
         */
        $saringRiwayat = $request->query('riwayat');

        $riwayat = ClinikScopusBiayaPersesi::query()
            ->where('status', ClinikScopusBiayaPersesi::NONAKTIF)
            ->when($saringRiwayat && array_key_exists($saringRiwayat, Layanan::katalog()),
                fn ($q) => $q->where('layanan', $saringRiwayat))
            ->withCount('clinikScopus')
            ->with('penginput:id,full_name,username')
            ->orderByDesc('updated_at')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        /*
         * Layanan yang dinonaktifkan TETAP ditampilkan, terpisah dan redup.
         * Tanpa ini ia lenyap dari layar dan tidak ada cara mengaktifkannya
         * lagi — padahal pesan penolakan hapus justru menyarankan
         * menonaktifkan. Jalan buntu yang dibuat oleh nasihat kita sendiri.
         */
        $nonaktif = Layanan::where('aktif', false)->orderBy('nama')->get();

        // Tarif yang sudah disetel tapi menunggu tanggalnya.
        $terjadwal = ClinikScopusBiayaPersesi::query()
            ->where('status', ClinikScopusBiayaPersesi::TERJADWAL)
            ->with('penginput:id,full_name,username')
            ->orderBy('berlaku_mulai')
            ->get();

        // Jumlah seluruh riwayat, bukan hanya halaman ini: pemiliknya perlu
        // tahu sedang melihat 2 dari 2 atau 2 dari 40.
        $totalRiwayat = ClinikScopusBiayaPersesi::where('status', ClinikScopusBiayaPersesi::NONAKTIF)->count();

        /*
         * PPN yang paling sering dipakai layanan lain, untuk ditawarkan saat
         * menyetel tarif baru. Bukan disetel diam-diam — ditawarkan, karena
         * yang lupa mengisi PPN baru ketahuan saat ada yang menghitung tagihan.
         */
        $ppnLazim = ClinikScopusBiayaPersesi::query()
            ->aktif()->whereNotNull('ppn')->where('ppn', '>', 0)
            ->selectRaw('ppn, count(*) as n')->groupBy('ppn')
            ->orderByDesc('n')->value('ppn');

        $adaTarif = collect($kartu)->filter(fn ($k) => $k['tarif'] !== null)->count();

        /*
         * Kapan harga terakhir disentuh — pertanyaan pertama saat seseorang
         * curiga harganya berubah. Tiap kartu menyebut penyetelnya dan riwayat
         * menyebut tanggalnya, tetapi tidak ada satu pun yang menjawab itu
         * untuk seluruh layar.
         */
        $terakhir = ClinikScopusBiayaPersesi::query()
            ->with('penginput:id,full_name,username')
            ->latest('updated_at')
            ->first();

        return view('account.clinik_scopus_biaya_persesi.index', [
            'kartu' => $kartu,
            'riwayat' => $riwayat,
            'bolehUbah' => $this->bolehMengubah(),
            'adaTarif' => $adaTarif,
            'totalKartu' => count($kartu),
            'daftarIkon' => Layanan::IKON,
            'daftarWarna' => Layanan::WARNA,
            'nonaktif' => $nonaktif,
            'terjadwal' => $terjadwal,
            'totalRiwayat' => $totalRiwayat,
            'ppnLazim' => $ppnLazim ? (int) $ppnLazim : null,
            'saringRiwayat' => $saringRiwayat,
            'terakhir' => $terakhir,
        ]);
    }

    /**
     * Mengunduh daftar harga sebagai PDF.
     *
     * Dulu ini `window.print()` dengan gaya @media print, dan hasilnya tidak
     * serupa dengan berkas lain yang keluar dari MIS: tanpa logo, tanpa kepala
     * berulang, tanpa kaki. Sekarang memakai cetakan yang sama dengan ekspor
     * Data Pelanggan.
     *
     * Hanya layanan yang tarifnya SUDAH disetel yang ikut: "Belum disetel" itu
     * peringatan untuk admin, bukan keterangan untuk calon peserta.
     */
    public function cetakPdf()
    {
        if (! $this->bolehMelihat()) {
            return redirect()->route('account.dashboard.index')
                ->with('error', 'Anda tidak punya akses ke tarif layanan.');
        }

        $berlaku = ClinikScopusBiayaPersesi::semuaYangBerlaku();
        $baris = [];

        foreach (Layanan::katalog() as $kunci => $tentang) {
            $varian = $tentang['varian'] ?: [null => null];

            foreach ($varian as $kodeVarian => $namaVarian) {
                $tarif = $berlaku[$kunci . '|' . ($kodeVarian ?: '')] ?? null;

                if (! $tarif) {
                    continue;
                }

                $baris[] = [
                    'nama' => $tentang['nama'],
                    'namaVarian' => $namaVarian,
                    'satuan' => $tentang['satuan'],
                    'tarif' => $tarif,
                ];
            }
        }

        $html = view('account.clinik_scopus_biaya_persesi.cetak-pdf', ['baris' => $baris])->render();

        $dompdf = new Dompdf();
        $pengaturan = $dompdf->getOptions();
        $pengaturan->setIsPhpEnabled(true);
        $pengaturan->setIsRemoteEnabled(false);
        $dompdf->setOptions($pengaturan);
        $dompdf->loadHtml($html);
        // Tegak: tiga kolom, dan daftar fasilitasnya butuh tinggi bukan lebar.
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nama = 'daftar-harga-layanan-' . now()->format('Ymd-His') . '.pdf';

        // response(), bukan $dompdf->stream(): stream() memanggil header() dan
        // echo sendiri sehingga kepalanya lewat dari lapisan respons Laravel.
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }

    /**
     * Menyetel tarif satu layanan.
     *
     * Satu pintu untuk dua keadaan:
     *
     *   tanpa perbaiki  -> tarif BARU untuk pasangan itu, langsung berlaku
     *   dengan perbaiki -> membetulkan tarif yang sedang berlaku
     *
     * Dibedakan dari niatnya, bukan dari tombol yang ditekan: menaikkan harga
     * layak dicatat sebagai baris baru, sementara membetulkan salah ketik
     * tidak boleh meninggalkan jejak seolah harganya pernah berubah.
     */
    public function simpan(Request $request)
    {
        if (! $this->bolehMengubah()) {
            return back()->with('error', 'Hanya administrator yang boleh mengubah tarif.');
        }

        $data = $request->validate([
            'layanan' => ['required', Rule::in(array_keys(Layanan::katalog()))],
            'varian' => ['nullable', 'string', 'max:40'],
            'biaya_persesi' => ['required', 'string'],
            'ppn' => ['nullable', 'integer', 'min:0', 'max:100'],
            'fasilitas' => ['nullable', 'string', 'max:8000'],
            'kegiatan' => ['nullable', 'string', 'max:8000'],
            'kontak' => ['nullable', 'string', 'max:500'],
            'template_deskripsi' => ['nullable', 'string', 'max:20000'],
            'berlaku_mulai' => ['nullable', 'date', 'after:today'],
            'perbaiki' => ['nullable', 'uuid'],
        ], [
            'layanan.required' => 'Layanannya tidak dikenali.',
            'biaya_persesi.required' => 'Isi dulu tarifnya.',
            'ppn.max' => 'PPN tidak masuk akal kalau lebih dari 100 persen.',
            'berlaku_mulai.after' => 'Tanggal mulainya harus setelah hari ini. '
                . 'Untuk berlaku sekarang juga, kosongkan saja.',
        ]);

        $tentang = Layanan::katalog()[$data['layanan']];
        $varian = ($data['varian'] ?? null) ?: null;

        // Varian yang tidak dikenal layanannya ditolak: kiriman datang dari
        // peramban, dan varian karangan akan membuat tarif yang tidak pernah
        // terbaca oleh siapa pun.
        if ($varian !== null && ! array_key_exists($varian, $tentang['varian'])) {
            return back()->withInput()->withErrors(['varian' => 'Varian itu tidak ada pada layanan tersebut.']);
        }

        if ($varian === null && $tentang['varian'] !== []) {
            return back()->withInput()->withErrors(['varian' => 'Layanan ini harus punya varian.']);
        }

        // Isian tarif diketik berformat "Rp 4.500.000" oleh pemolesnya di layar.
        $tarif = (int) preg_replace('/\D+/', '', $data['biaya_persesi']);

        if ($tarif < 1) {
            return back()->withInput()->withErrors(['biaya_persesi' => 'Tarifnya harus lebih dari nol.']);
        }

        $ppn = $request->filled('ppn') ? (int) $data['ppn'] : null;
        $fasilitas = $this->uraikanDaftar($data['fasilitas'] ?? null, 'fasilitas');
        $kegiatan = $this->uraikanDaftar($data['kegiatan'] ?? null, 'kegiatan');
        $kontak = trim((string) ($data['kontak'] ?? '')) ?: null;
        $cetakan = trim((string) ($data['template_deskripsi'] ?? '')) ?: null;

        $sebutan = $tentang['nama'] . ($varian ? ' (' . $tentang['varian'][$varian] . ')' : '');

        /*
         * "Perbaiki" membetulkan tarif yang SEDANG berlaku; tanggal mulai
         * menjadwalkan yang AKAN berlaku. Keduanya bertentangan, dan cabang
         * perbaikan tidak memakai tanggalnya sama sekali — jadi dibiarkan,
         * tanggal yang dikirim hilang tanpa kabar apa pun.
         */
        if (! empty($data['perbaiki']) && ! empty($data['berlaku_mulai'])) {
            return back()->withInput()->withErrors([
                'berlaku_mulai' => 'Tidak bisa sekaligus memperbaiki tarif yang berlaku dan '
                    . 'menjadwalkan yang baru. Ubah angkanya supaya jadi tarif baru, '
                    . 'atau kosongkan tanggalnya.',
            ]);
        }

        if (! empty($data['perbaiki'])) {
            $lama = ClinikScopusBiayaPersesi::find($data['perbaiki']);

            if ($lama && $lama->layanan === $data['layanan'] && $lama->varian === $varian) {
                $lama->update([
                    'biaya_persesi' => $tarif,
                    'ppn' => $ppn,
                    'fasilitas' => $fasilitas,
                    'kegiatan' => $kegiatan,
                    'kontak' => $kontak,
                    'template_deskripsi' => $cetakan,
                    // Jejaknya menunjuk yang TERAKHIR mengubah, bukan yang
                    // pertama membuat: memperbaiki justru cara tarif paling
                    // sering berubah, dan "disetel oleh A" pada angka yang
                    // ditulis B adalah jejak yang menunjuk orang keliru.
                    'penginput_id' => auth()->id(),
                ]);
                $lama->jadikanBerlaku();

                return redirect()
                    ->route('account.Clinik-Scopus-Biaya-Persesi.index')
                    ->with('success', 'Tarif ' . $sebutan . ' diperbarui jadi ' . $lama->tarif_terbaca . '.');
            }
        }

        $mulai = $data['berlaku_mulai'] ?? null;

        $baru = ClinikScopusBiayaPersesi::create([
            'layanan' => $data['layanan'],
            'varian' => $varian,
            'biaya_persesi' => $tarif,
            'ppn' => $ppn,
            'fasilitas' => $fasilitas,
            'kegiatan' => $kegiatan,
            'kontak' => $kontak,
            'template_deskripsi' => $cetakan,
            'berlaku_mulai' => $mulai,
            'status' => ClinikScopusBiayaPersesi::NONAKTIF,
        ]);

        /*
         * Bertanggal: disimpan menunggu, tarif yang sekarang TIDAK diganggu.
         * Ia naik sendiri saat tanggalnya tiba — lihat naikkanYangSudahWaktunya
         * di modelnya.
         */
        if ($mulai) {
            $baru->forceFill(['status' => ClinikScopusBiayaPersesi::TERJADWAL])->save();

            return redirect()
                ->route('account.Clinik-Scopus-Biaya-Persesi.index')
                ->with('success', 'Tarif ' . $sebutan . ' ' . $baru->tarif_terbaca
                    . ' dijadwalkan mulai ' . $baru->berlaku_mulai->locale('id')->translatedFormat('d F Y')
                    . '. Harga sekarang tidak berubah.');
        }

        $baru->jadikanBerlaku();

        return redirect()
            ->route('account.Clinik-Scopus-Biaya-Persesi.index')
            ->with('success', 'Tarif ' . $sebutan . ' ' . $baru->tarif_terbaca . ' mulai berlaku.');
    }

    /**
     * Mengambil daftar fasilitas dari teks yang diketik ATAU ditempel.
     *
     * Admin tidak menulis daftar fasilitas dari nol — mereka sudah punya teks
     * pengumuman angkatan (yang selama ini tersimpan di kolom `desc`), dan di
     * dalamnya ada satu bagian berjudul "Fasilitas Peserta". Menyuruh mereka
     * mengetik ulang tujuh baris itu justru menambah pekerjaan, padahal
     * seluruh layar ini dibuat supaya isiannya makin sedikit.
     *
     * Jadi teks utuhnya boleh ditempel apa adanya: yang diambil hanya baris
     * di bawah judul yang menyebut "fasilitas", dan berhenti di baris pertama
     * yang bukan butir daftar. Tanpa itu, menempel pengumuman menghasilkan 19
     * "fasilitas" — judul, tanggal, harga, sampai nomor telepon panitia.
     *
     * Kalau tidak ada judul semacam itu, isinya diperlakukan seperti daftar
     * biasa: satu baris satu fasilitas. Bukan dipisah koma, karena
     * fasilitasnya sendiri kerap memuat koma ("Konsumsi pagi, siang, dan
     * sore").
     *
     * @return array<int, string>|null
     */
    private function uraikanDaftar(?string $teks, string $kata = 'fasilitas'): ?array
    {
        $baris = preg_split('/\r\n|\r|\n/', (string) $teks) ?: [];
        $baris = array_map(fn ($b) => trim($b), $baris);

        $mulai = null;

        foreach ($baris as $i => $b) {
            if ($b !== '' && $this->judulBagian($b, $kata)) {
                $mulai = $i + 1;
                break;
            }
        }

        if ($mulai !== null) {
            $ambil = [];

            for ($i = $mulai; $i < count($baris); $i++) {
                if ($baris[$i] === '') {
                    // Baris kosong SEBELUM butir pertama cuma jarak di bawah
                    // judulnya; sesudah itu, ia penutup bagiannya.
                    if ($ambil === []) {
                        continue;
                    }

                    break;
                }

                if (! $this->butirDaftar($baris[$i])) {
                    break;
                }

                $ambil[] = $baris[$i];
            }

            $baris = $ambil;
        }

        $bersih = array_values(array_filter(
            array_map(fn ($b) => $this->tanpaPenandaDaftar($b), $baris),
            fn ($b) => $b !== ''
        ));

        return $bersih === [] ? null : $bersih;
    }

    /**
     * Apakah barisnya judul bagian yang dicari.
     *
     * Butir bernomor dan butir bertanda hubung dikecualikan supaya butir yang
     * kebetulan menyebut kata itu ("- Fasilitas olahraga") tidak disangka
     * judul. Judul di teks mereka ditandai emoji, bukan angka atau tanda
     * hubung — misalnya "\u{1F539} Fasilitas Peserta".
     */
    private function judulBagian(string $baris, string $kata): bool
    {
        if (preg_match('/^(\\d+\\s*[.)]|[-*])\\s*/u', $baris)) {
            return false;
        }

        return (bool) preg_match('/' . preg_quote($kata, '/') . '/iu', $baris);
    }

    /** Apakah barisnya berbentuk butir daftar — bernomor, bertanda, atau bercentang. */
    private function butirDaftar(string $baris): bool
    {
        return (bool) preg_match('/^(\d+\s*[.)]|[-*\x{2022}\x{00B7}\x{2013}\x{2705}\x{2714}\x{25AB}\x{25B8}])\s*/u', $baris);
    }

    /**
     * Membuang penanda butirnya, menyisakan teks fasilitasnya saja.
     *
     * Penanda /u wajib: tanpa itu kelas karakternya dicocokkan per BITA, jadi
     * "•" terpotong separuh dan sisanya UTF-8 rusak yang gagal disandikan ke
     * JSON saat disimpan.
     */
    private function tanpaPenandaDaftar(string $baris): string
    {
        return trim(preg_replace(
            '/^(\d+\s*[.)]|[-*\x{2022}\x{00B7}\x{2013}\x{2705}\x{2714}\x{25AB}\x{25B8}])\s*/u',
            '',
            $baris
        ));
    }

    /** Memberlakukan lagi tarif lama, tanpa mengetik ulang angkanya. */
    public function berlakukan(ClinikScopusBiayaPersesi $tarif)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh mengubah tarif.',
            ], 403);
        }

        /*
         * Nominalnya diperiksa di sini juga, bukan hanya di borang penyetelan.
         * Baris riwayat berharga nol memang ada (Online Training disetel nol
         * saat datanya diisi awal), dan tanpa pemeriksaan ini satu klik
         * menjadikannya tarif yang berlaku — padahal borangnya menolak nol
         * mentah-mentah. Dua pintu ke satu tempat harus punya aturan sama.
         */
        if ((int) $tarif->biaya_persesi < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif ini bernilai nol, jadi tidak bisa diberlakukan. '
                    . 'Setel harga barunya lewat tombol di kartu layanan.',
            ], 409);
        }

        $tarif->jadikanBerlaku();

        $sebutan = $tarif->nama_layanan . ($tarif->nama_varian ? ' (' . $tarif->nama_varian . ')' : '');

        return response()->json([
            'success' => true,
            'message' => 'Tarif ' . $sebutan . ' ' . $tarif->tarif_terbaca . ' kembali berlaku.',
        ]);
    }

    /**
     * Menghapus satu baris riwayat tarif.
     *
     * Diperiksa lebih dulu, bukan dicoba lalu ditangkap: clinikscopus.
     * biaya_persesi_id berkunci asing ON DELETE NO ACTION, jadi menghapus
     * tarif yang masih dipakai sesi mana pun ditolak MySQL dengan galat 1451
     * yang hanya menyebut nama constraint-nya.
     */
    public function destroy(ClinikScopusBiayaPersesi $tarif)
    {
        if (! $this->bolehMengubah()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya administrator yang boleh menghapus tarif.',
            ], 403);
        }

        if ($tarif->berlaku) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif ini sedang berlaku. Setel tarif lain dulu sebagai penggantinya.',
            ], 409);
        }

        // Diingat sebelum barisnya hilang, untuk menyusun pesannya.
        $terjadwal = $tarif->terjadwal;

        $dipakai = $tarif->dipakai_sesi;

        if ($dipakai > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif ini masih jadi acuan harga ' . $dipakai . ' sesi. '
                    . 'Menghapusnya akan memutus riwayat harga pesanan yang sudah terjadi.',
            ], 409);
        }

        try {
            $tarif->delete();
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tarif ini masih tertaut ke data lain, jadi belum bisa dihapus.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => $terjadwal
                ? 'Jadwal kenaikan dibatalkan. Harga yang berlaku tidak berubah.'
                : 'Tarif lama dihapus dari riwayat.',
        ]);
    }
}
