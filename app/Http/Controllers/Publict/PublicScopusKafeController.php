<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Publict\Concerns\AlurGerbangPembayaran;
use App\Mail\CreatePublicPendaftaranScopusKafeMail;
use App\PendaftaranScopusKafe;
use App\Services\Doku;
use App\Support\HargaKafe;
use App\Support\PendaftaranSemuaLayanan as Katalog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Scopus Kafe: dari memilih sesi sampai hasil pembayarannya.
 *
 * Dirombak 26 Okt 2026 mengikuti Scopus Camp, atas permintaan pemiliknya.
 * Yang diwarisi dari bentuk lamanya dan dibetulkan di sini:
 *
 *   - HARGANYA DATANG DARI PERAMBAN. `biaya`, `kode_unik_pembayaran`,
 *     `subtotal_pembayaran`, dan `total_keseluruhan_pembayaran` seluruhnya
 *     diterima dari kiriman borang lalu disimpan mentah — tujuh medan uang
 *     yang ditentukan sisi yang tidak bisa dipercaya. Siapa pun bisa memesan
 *     tiga sesi bertotal nol tanpa satu baris pun yang menolak;
 *
 *   - PEMBAYARANNYA TRANSFER MANUAL berbukti unggahan, dengan berkas yang
 *     disimpan ke folder yang tersaji lewat HTTP. Pola yang sama sudah
 *     dicabut dari Scopus Camp hari yang sama;
 *
 *   - TIDAK ADA HALAMAN HASIL. Sesudah mengirim borang, orangnya dipantulkan
 *     ke halaman daftar dengan satu kotak hijau — tanpa nomor pemesanan,
 *     tanpa cara kembali, tanpa cara tahu pembayarannya sudah masuk atau
 *     belum.
 *
 * ---------------------------------------------------------------------------
 * TIDAK BERANGKATAN, TETAPI TIDAK BERARTI TIAP HARI
 * ---------------------------------------------------------------------------
 *
 * Layanan ini terbuka terus — tidak ada angkatan yang dibuka dan ditutup —
 * tetapi hanya BERJALAN di akhir pekan: Jumat, Sabtu, Minggu. Jadi yang
 * dipilih pemesan bukan angkatan melainkan TANGGAL, dan tanggal itu harus
 * salah satu dari ketiganya. Aturannya di HargaKafe::HARI_BUKA, satu tempat
 * bersama borangnya.
 */
class PublicScopusKafeController extends Controller
{
    /*
     * Bagian alur gerbang yang sama dengan Scopus Camp: penyusulan keadaan
     * dari gerbang, penomoran tagihan ulang, kembali ke halaman status, dan
     * penyamaran alamat email.
     */
    use AlurGerbangPembayaran;

    /**
     * Status yang berarti kursinya tidak lagi dipegang.
     *
     * Kosakata layanan ini memang begitu sejak awal — 'pembayaran ditolak',
     * bukan 'Pendaftaran Dibatalkan' seperti Scopus Camp — dan TIDAK
     * diseragamkan di sini: sembilan baris yang sudah ada memakainya, begitu
     * pula layar panitia dan surat-suratnya.
     */
    private const STATUS_LEPAS = ['pembayaran ditolak'];

    public function __construct(private Doku $doku)
    {
    }

    protected function layananKunci(): string
    {
        return 'scopus_kafe';
    }

    // <!--================== DAFTAR SESI ==================-->

    public function public(Request $request)
    {
        /*
         * Tenggat bayar ditegakkan DI SINI juga, bukan cuma oleh cron.
         *
         * Cron hPanel di peladen ini belum terpasang, jadi tagihan yang mati
         * tidak pernah dibereskan siapa pun. Halaman ini yang paling sering
         * dibuka orang.
         */
        PendaftaranScopusKafe::lepaskanYangKedaluwarsa();

        return view('public.scopus_kafe.index', [
            'varian' => $this->varianUntukLayar(),
            'tanggalBerikutnya' => $this->tanggalBukaBerikutnya(),
            'gerbangSiap' => $this->doku->siap(),
        ]);
    }

    // <!--================== BORANG ==================-->

    public function FormPendaftaran()
    {
        /*
         * TANPA GERBANG, BORANGNYA TIDAK DIGAMBAR SAMA SEKALI.
         *
         * Sejak jalur unggah bukti dicabut, pemesanan tanpa gerbang pasti
         * ditolak store(). Menggambar borangnya berarti orang mengisi empat
         * isian lalu ditolak di akhir karena hal yang sudah diketahui sebelum
         * ia mengetik huruf pertama.
         */
        if (! $this->doku->siap()) {
            Log::error('Borang Scopus Kafe ditutup: gerbang pembayaran belum siap.');

            return redirect()->route('public.scopuskafe.index')->with('error',
                'Pembayaran daring sedang tidak tersedia, jadi pemesanan ditutup '
                . 'sementara. Silakan hubungi panitia lewat WhatsApp.');
        }

        $varian = $this->varianUntukLayar();

        if ($varian === []) {
            return redirect()->route('public.scopuskafe.index')->with('error',
                'Belum ada sesi yang dibuka. Silakan periksa lagi beberapa saat.');
        }

        return view('public.scopus_kafe.form_pendaftaran', [
            'varian' => $varian,
            'maksSesi' => HargaKafe::MAKS_SESI,
            'tanggalMin' => $this->tanggalBukaBerikutnya()->toDateString(),
            'namaHariBuka' => HargaKafe::namaHariBuka(),
            'menitBayar' => Doku::MENIT_KEDALUWARSA,

            /*
             * Yang SUDAH masuk akun tidak perlu mengetik apa pun. Datanya
             * sudah ada di sesi; menunggu ia mengetik ulang berarti meminta
             * keterangan yang kita pegang.
             */
            'akun' => auth()->user(),
        ]);
    }

    // <!--================== SIMPAN ==================-->

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:100'],
            'telp' => ['required', 'string', 'min:8', 'max:25'],
            'tanggal_pemesanan' => ['required', 'date'],
            'varian' => ['required', 'string'],
            'sesi' => ['required', 'array', 'min:1', 'max:' . HargaKafe::MAKS_SESI],
            'sesi.*' => ['required', 'string'],
            'setuju' => ['accepted'],
        ], [
            'varian.required' => 'Pilih dulu Anda ikut tatap muka atau daring.',
            'nama.required' => 'Namanya belum diisi.',
            'email.required' => 'Alamat emailnya belum diisi.',
            'email.email' => 'Alamat emailnya belum benar.',
            'telp.required' => 'Nomor WhatsApp-nya belum diisi.',
            'tanggal_pemesanan.required' => 'Tanggalnya belum dipilih.',
            'sesi.required' => 'Pilih dulu sesi yang Anda inginkan.',
            'sesi.max' => 'Paling banyak ' . HargaKafe::MAKS_SESI . ' sesi dalam satu pemesanan.',
            'setuju.accepted' => 'Centang dulu persetujuan ketentuannya.',
        ]);

        /*
         * TANPA GERBANG, PEMESANANNYA DITOLAK — tidak jatuh ke unggah bukti.
         *
         * Ditolak dengan terang, bukan diam-diam membuat pemesanan yang tidak
         * bisa dibayar: orangnya berhak tahu bahwa yang bermasalah bukan
         * datanya.
         */
        if (! $this->doku->siap()) {
            Log::error('Pemesanan Scopus Kafe ditolak: gerbang pembayaran belum siap.');

            return back()->withInput()->with('error',
                'Pembayaran daring sedang tidak tersedia, jadi pemesanan belum bisa '
                . 'diproses sekarang. Data Anda TIDAK tersimpan — silakan hubungi '
                . 'panitia lewat WhatsApp, atau coba lagi beberapa saat.');
        }

        $tanggal = Carbon::parse($data['tanggal_pemesanan'])->startOfDay();

        /*
         * DUA ATURAN TANGGAL, dan keduanya menolak di tempat yang sama.
         *
         * Tanpa keduanya, panitia menolaknya dengan tangan lewat WhatsApp dan
         * pemesannya mengulang dari nol — kadang sesudah membayar.
         */
        if ($tanggal->lt(now()->startOfDay())) {
            return back()->withInput()->withErrors([
                'tanggal_pemesanan' => 'Tanggalnya sudah lewat. Pilih tanggal mulai hari ini.',
            ]);
        }

        if (! HargaKafe::hariBuka($tanggal)) {
            return back()->withInput()->withErrors([
                'tanggal_pemesanan' => 'Scopus Kafe hanya berjalan hari '
                    . HargaKafe::namaHariBuka() . '. Silakan pilih salah satunya.',
            ]);
        }

        /*
         * HARGANYA DIHITUNG ULANG DI PELADEN, dari katalog sesinya.
         *
         * Yang diterima dari borang cuma KUNCI sesinya. Sebelumnya tujuh
         * medan uang datang mentah dari peramban.
         */
        $harga = HargaKafe::hitung(
            $data['varian'], $data['sesi'], $request->input('kode_unik_pembayaran')
        );

        if ($harga['sesi'] === []) {
            /*
             * Satu pesan untuk dua sebab — varian karangan dan sesi yang
             * sudah ditarik — sebab keduanya berakhir sama bagi pemesannya:
             * pilihannya tidak berlaku lagi dan ia harus memilih ulang.
             */
            return back()->withInput()->withErrors([
                'sesi' => 'Pilihan sesinya sudah tidak tersedia. Silakan pilih lagi.',
            ]);
        }

        /*
         * PEMESANAN GANDA ditolak sebelum barisnya dibuat — tetapi HANYA
         * selama tagihan yang lama masih hidup.
         *
         * Orang menekan tombolnya dua kali karena mengira yang pertama gagal —
         * halaman ini memang diam beberapa detik saat tagihannya dibuat — dan
         * tanpa penjagaan ini ia mendapat DUA tagihan untuk satu tempat, lalu
         * sebagian membayar keduanya.
         *
         * Yang dijaga itu saja: DUA TAGIHAN TERBUKA. Sampai 31 Okt 2026
         * penjagaan ini mencegat SETIAP baris yang statusnya bukan
         * 'pembayaran ditolak' — dan penyapu kedaluwarsa Kafe tidak pernah
         * menyentuh kolom `status`, jadi tidak ada satu pun keadaan yang
         * melepaskannya kembali. Terukur: memesan satu tanggal lalu tidak
         * membayar mengunci tanggal itu SELAMANYA bagi orang yang sama, dan
         * yang sudah membayar pun tidak bisa memesan lagi.
         *
         * Yang dipindahkan TENGGATNYA, bukan statusnya. Memindahkan status
         * ke 'pembayaran ditolak' saat tenggat lewat sempat dicoba di sini
         * dan dibatalkan: status itu masuk STATUS_LEPAS, dan halaman status
         * berhenti menawarkan "Buat tagihan baru" begitu ia menyentuhnya —
         * jalan keluar satu-satunya yang dimiliki pemesannya ikut hilang.
         *
         * Aturannya disamakan dengan Scopus Camp, keputusan pemiliknya
         * 31 Okt 2026: yang mengunci cuma tagihan yang belum dibayar DAN
         * belum lewat 60 menit. Sesudah lunas boleh memesan lagi; sesudah
         * tenggatnya lewat boleh memesan lagi.
         *
         * Tautannya dikirim ke EMAIL, tidak dibuka di layar: yang dicocokkan
         * cuma tanggal + nama + email, dan ketiganya bukan rahasia. Dibuka di
         * layar, siapa pun yang tahu nama dan email orang lain bisa membuka
         * pemesanannya.
         */
        $sudahAda = PendaftaranScopusKafe::whereDate('tanggal_pemesanan', $tanggal->toDateString())
            ->where('status', Katalog::statusAwal('scopus_kafe'))
            ->whereNull('bayar_pada')
            ->where(function ($q) {
                // Tanpa tenggat tercatat, tagihannya dianggap masih hidup:
                // menebak sebaliknya membuka kembali pintu tagihan ganda.
                $q->whereNull('kedaluwarsa_pada')
                    ->orWhere('kedaluwarsa_pada', '>', now());
            })
            ->where('email', mb_strtolower(trim($data['email'])))
            ->where('nama', trim($data['nama']))
            ->latest('created_at')
            ->first();

        if ($sudahAda !== null) {
            try {
                Mail::to($sudahAda->email)->send(
                    new \App\Mail\TautanPendaftaranScopusKafeMail(collect([$sudahAda]))
                );
            } catch (\Throwable $e) {
                report($e);
            }

            return back()->withInput()->with('success',
                'Data ini masih punya tagihan yang belum dibayar untuk tanggal tersebut. '
                . 'Tautan halaman pemesanannya kami kirimkan ke alamat email yang dipakai '
                . 'saat memesan — periksa juga folder spam. Kalau tagihan itu memang tidak '
                . 'jadi dibayar, tunggu ' . Doku::MENIT_KEDALUWARSA . ' menit sampai '
                . 'tagihannya kedaluwarsa, lalu pemesanan baru bisa dibuat lagi.');
        }

        $isi = [
            'id_pemesanan' => Katalog::nomorBaru('scopus_kafe'),
            'nama' => trim($data['nama']),
            'email' => mb_strtolower(trim($data['email'])),
            'telp' => \App\Support\NomorTelepon::rapikan($data['telp']),
            'tanggal_pemesanan' => $tanggal,
            'varian' => $data['varian'],
            'total_keseluruhan_pembayaran' => (string) $harga['total'],
            'kode_unik_pembayaran' => (string) $harga['kode_unik'],
            'cara_bayar' => 'doku',
            'status' => Katalog::statusAwal('scopus_kafe'),
            'kedaluwarsa_pada' => now()->addMinutes(Doku::MENIT_KEDALUWARSA),
        ];

        /*
         * Tiga slot sesi dalam satu baris: bentuk tabelnya memang begitu
         * sejak awal, dan tidak diubah di sini — mengubahnya berarti
         * memindahkan sembilan baris yang sudah ada beserta layar panitia
         * yang membacanya.
         */
        foreach ($harga['sesi'] as $ke => $sesi) {
            $akhiran = ['', '_kedua', '_ketiga'][$ke] ?? null;

            if ($akhiran === null) {
                break;
            }

            $isi['sesi' . $akhiran] = $sesi['nama'];
            $isi['waktu_mulai' . $akhiran] = $sesi['mulai'];
            $isi['waktu_selesai' . $akhiran] = $sesi['selesai'];
            $isi['lokasi' . $akhiran] = $sesi['lokasi'];
            $isi['biaya' . $akhiran] = (string) $sesi['biaya'];
            $isi['subtotal_pembayaran' . $akhiran] = (string) $sesi['biaya'];
        }

        $simpan = PendaftaranScopusKafe::create($isi);

        if ($simpan === null) {
            return redirect()->route('public.scopuskafe.index')
                ->with('error', 'Pemesanan Scopus Kafe gagal disimpan. Silakan coba lagi.');
        }

        /*
         * Surat dan WhatsApp-nya DIBUNGKUS TERPISAH. Alamat email yang salah
         * ketik melempar, dan lemparan itu akan membatalkan pemesanan yang
         * barisnya SUDAH tersimpan — orangnya melihat halaman galat lalu
         * memesan lagi, dan panitia menerima dua baris untuk satu orang.
         */
        try {
            Mail::to($simpan->email)->send(
                new CreatePublicPendaftaranScopusKafeMail($simpan, $simpan, 'Rumah Scopus Foundation')
            );
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
                ->pendaftaranMasuk('scopus_kafe', $simpan);
        } catch (\Throwable $e) {
            Log::warning('Kabar WhatsApp pemesanan Scopus Kafe gagal.', [
                'nomor' => $simpan->id_pemesanan,
                'pesan' => $e->getMessage(),
            ]);
        }

        return $this->mulaiBayar($simpan);
    }

    // <!--================== BAYAR LEWAT GERBANG ==================-->

    /**
     * Membuat tagihan lalu mengantar pemesannya ke halaman bayar.
     *
     * Gagal membuat tagihan TIDAK menghapus pemesanannya. Orangnya sudah
     * mengisi borang; membatalkannya berarti ia mengulang dari nol karena
     * kesalahan yang bukan miliknya. Ia diantar ke halaman status, tempat
     * tombol "buat tagihan baru" menunggunya.
     */
    private function mulaiBayar(PendaftaranScopusKafe $pemesanan)
    {
        $nomorTagihan = $this->nomorTagihanBaru($pemesanan);

        $judul = collect([$pemesanan->sesi, $pemesanan->sesi_kedua, $pemesanan->sesi_ketiga])
            ->filter()
            ->implode(' + ');

        $hasil = $this->doku->buatTagihan([
            'nomor' => $nomorTagihan,
            'jumlah' => (int) $pemesanan->total_keseluruhan_pembayaran,

            /*
             * Rinciannya HARUS berjumlah sama dengan totalnya, kalau tidak
             * gerbang menolak dengan "AMOUNT NOT MATCH" dan pemesannya cuma
             * melihat "pembayaran sedang bermasalah".
             *
             * Harga satuannya subtotal seluruh sesi, jumlah pesertanya satu:
             * yang dipesan di sini bukan kursi melainkan waktu pendampingan.
             */
            'harga_satuan' => (int) $pemesanan->total_keseluruhan_pembayaran
                - (int) $pemesanan->kode_unik_pembayaran,
            'jumlah_peserta' => 1,
            'potongan' => 0,
            'kode_unik' => (int) $pemesanan->kode_unik_pembayaran,
            'ppn' => 0,
            'judul' => 'Scopus Kafe — ' . ($judul ?: 'pendampingan'),
            'nama' => $pemesanan->nama,
            'email' => $pemesanan->email,
            'telp' => $pemesanan->telp,
            'kembali' => route('public.scopuskafe.status', $pemesanan->getKey()),
            'kanal' => Doku::KANAL_QRIS_DAN_VA,
        ]);

        if (! $hasil['berhasil'] || ! $hasil['url']) {
            $pemesanan->forceFill([
                'bayar_status' => 'gagal-dibuat',
                'note' => trim((string) $pemesanan->note . ' Tagihan daring gagal dibuat: '
                    . ($hasil['pesan'] ?? 'tidak diketahui')),
            ])->save();

            return redirect()->route('public.scopuskafe.status', $pemesanan->getKey())
                ->with('error', 'Pembayaran daring sedang bermasalah. Pemesanan Anda SUDAH '
                    . 'tersimpan — silakan buat tagihan baru lewat tombol di halaman ini, '
                    . 'atau hubungi panitia lewat WhatsApp.');
        }

        $pemesanan->forceFill([
            'bayar_nomor' => $nomorTagihan,
            'bayar_rujukan' => $hasil['rujukan'],
            'bayar_permintaan_id' => $hasil['permintaan_id'],
            'bayar_url' => $hasil['url'],
            'bayar_status' => 'menunggu',
        ])->save();

        return redirect()->away($hasil['url']);
    }

    // <!--================== HALAMAN HASIL ==================-->

    public function status(string $id)
    {
        $pemesanan = PendaftaranScopusKafe::whereKey($id)->first();

        if ($pemesanan === null) {
            return redirect()->route('public.scopuskafe.index')
                ->with('error', 'Pemesanan itu tidak ditemukan.');
        }

        PendaftaranScopusKafe::lepaskanYangKedaluwarsa((string) $pemesanan->getKey());

        $pemesanan = $this->susulkanDariGerbang($pemesanan->refresh());

        return view('public.scopus_kafe.status', [
            'pemesanan' => $pemesanan,
            'gerbangSiap' => $this->doku->siap(),
            'menitBayar' => Doku::MENIT_KEDALUWARSA,
            'sesi' => $this->sesiTerpesan($pemesanan),
        ]);
    }

    public function keadaan(string $id)
    {
        $pemesanan = PendaftaranScopusKafe::whereKey($id)->first();

        if ($pemesanan === null) {
            return response()->json(['ada' => false], 404);
        }

        PendaftaranScopusKafe::lepaskanYangKedaluwarsa((string) $pemesanan->getKey());

        $pemesanan = $this->susulkanDariGerbang($pemesanan->refresh());

        $lunas = $this->sudahLunas($pemesanan);

        return response()->json([
            'ada' => true,
            'lunas' => $lunas,

            /*
             * 'selesai' berarti halamannya TIDAK perlu bertanya lagi. Tagihan
             * yang mati tidak akan dibayar siapa pun, jadi menanyainya tiap
             * beberapa detik tidak pernah memulangkan jawaban berbeda.
             */
            'selesai' => $lunas
                || in_array((string) $pemesanan->status, self::STATUS_LEPAS, true)
                || in_array((string) $pemesanan->bayar_status, [
                    'failed', 'cancel', 'canceled', 'gagal', 'gagal-dibuat', 'expired',
                ], true),
        ]);
    }

    /**
     * Pemberitahuan balik dari gerbang bahwa sebuah tagihan sudah dibayar.
     *
     * Dikecualikan dari CSRF karena yang mengirim peladen gerbang, bukan
     * peramban pemesannya. Penggantinya BUKAN tidak ada penjagaan: tanda
     * tangannya diperiksa lebih dulu — beserta umur dan sekali-pakainya —
     * dan tanpa itu siapa pun yang tahu alamat ini bisa menandai pemesanan
     * mana pun lunas hanya dengan satu permintaan POST.
     */
    public function pemberitahuan(Request $request)
    {
        $sah = $this->doku->pemberitahuanSah(
            '/' . ltrim($request->path(), '/'),
            $request->header('Request-Id'),
            $request->header('Request-Timestamp'),
            $request->header('Signature'),
            $request->getContent()
        );

        if (! $sah) {
            // Kepala yang memuat tanda tangannya TIDAK ikut dicatat.
            Log::warning('Pemberitahuan DOKU Scopus Kafe ditolak: tanda tangannya tidak cocok', [
                'nomor' => $request->input('order.invoice_number'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => false], 401);
        }

        $nomor = (string) $request->input('order.invoice_number');
        $status = mb_strtolower((string) $request->input('transaction.status'));

        /*
         * Dicari lewat KEDUA kolom: nomor tagihan percobaan kedua dan
         * seterusnya berakhiran -R2 dan hanya tersimpan di `bayar_nomor`.
         */
        $pemesanan = PendaftaranScopusKafe::where('id_pemesanan', $nomor)
            ->orWhere('bayar_nomor', $nomor)
            ->first();

        if ($pemesanan === null) {
            Log::warning('DOKU memberi tahu nomor Scopus Kafe yang tidak dikenal', ['nomor' => $nomor]);

            // 200, bukan galat: nomornya memang tidak ada di sini, dan
            // menjawab galat membuat gerbang mengulang pemberitahuan yang
            // sama berhari-hari.
            return response()->json(['ok' => true]);
        }

        if (! in_array($status, ['success', 'settlement'], true)) {
            // Gagal dan kedaluwarsa cuma dicatat di kolom tagihannya.
            // Statusnya TIDAK diturunkan: 'pembayaran ditolak' di layanan ini
            // keputusan panitia, bukan keputusan gerbang.
            $pemesanan->forceFill(['bayar_status' => $status ?: 'gagal'])->save();

            return response()->json(['ok' => true]);
        }

        /*
         * Yang sudah lunas TIDAK diproses lagi. Gerbang boleh mengirim
         * pemberitahuan yang sama lebih dari sekali, dan dua kali proses
         * berarti dua kali surat ke orang yang sama.
         */
        if ($this->sudahLunas($pemesanan)) {
            return response()->json(['ok' => true]);
        }

        $this->tandaiLunas($pemesanan, $status, $this->doku->namaKanal($request->all()));

        return response()->json(['ok' => true]);
    }

    // <!--================== TINDAKAN PEMESAN ==================-->

    public function bayarUlang(string $id)
    {
        $pemesanan = PendaftaranScopusKafe::whereKey($id)->first();

        if ($pemesanan === null) {
            return redirect()->route('public.scopuskafe.index')
                ->with('error', 'Pemesanan itu tidak ditemukan.');
        }

        if ($this->sudahLunas($pemesanan)) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Pembayaran untuk pemesanan ini sudah kami terima.');
        }

        if (in_array((string) $pemesanan->status, self::STATUS_LEPAS, true)) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Pemesanan ini sudah tidak berjalan, jadi tagihannya tidak bisa dibuat '
                . 'lagi. Silakan memesan kembali.');
        }

        /*
         * Tanggalnya harus MASIH DI DEPAN. Tanpa ini, yang tagihannya mati
         * setelah harinya lewat tetap bisa membayar sesi yang sudah selesai.
         */
        if ($pemesanan->tanggal_pemesanan !== null
            && Carbon::parse($pemesanan->tanggal_pemesanan)->startOfDay()->lt(now()->startOfDay())) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Tanggal sesinya sudah lewat, jadi tagihannya tidak bisa dibuat lagi. '
                . 'Silakan memesan tanggal yang baru.');
        }

        if (! $this->doku->siap()) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Pembayaran daring sedang tidak tersedia. Silakan hubungi panitia '
                . 'lewat WhatsApp.');
        }

        /*
         * Tenggat barunya disetel ulang dari sekarang: tagihan gerbangnya
         * sendiri berumur sekian menit sejak dibuat, dan tenggat lama yang
         * dibiarkan membuat halaman status menyembunyikan tombol bayar untuk
         * tagihan yang justru baru saja hidup.
         */
        $pemesanan->forceFill([
            'kedaluwarsa_pada' => now()->addMinutes(Doku::MENIT_KEDALUWARSA),
        ])->save();

        return $this->mulaiBayar($pemesanan);
    }

    public function kirimUlang(string $id)
    {
        $pemesanan = PendaftaranScopusKafe::whereKey($id)->first();

        if ($pemesanan === null) {
            return redirect()->route('public.scopuskafe.index')
                ->with('error', 'Pemesanan itu tidak ditemukan.');
        }

        $alamat = trim((string) $pemesanan->email);

        if (! filter_var($alamat, FILTER_VALIDATE_EMAIL)) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Alamat email pemesanan ini tidak sah, jadi suratnya tidak bisa dikirim. '
                . 'Silakan hubungi panitia.');
        }

        try {
            Mail::to($alamat)->send(
                new CreatePublicPendaftaranScopusKafeMail($pemesanan, $pemesanan, 'Rumah Scopus Foundation')
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->kembaliKeStatus($pemesanan, 'error',
                'Suratnya gagal dikirim. Coba lagi beberapa saat.');
        }

        \App\PendaftaranJejak::create([
            'layanan' => 'scopus_kafe',
            'pendaftaran_id' => (string) $pemesanan->getKey(),
            'aksi' => 'surat',
            'dari' => (string) $pemesanan->status,
            'ke' => $alamat,
            'oleh_nama' => 'pemesan sendiri',
        ]);

        return $this->kembaliKeStatus($pemesanan, 'success',
            'Bukti pemesanan dikirim ulang ke ' . $this->samarkanEmail($alamat)
            . '. Kalau belum masuk dalam beberapa menit, periksa folder spam.');
    }

    /**
     * Faktur atau kwitansi pemesanannya, sebagai PDF.
     *
     * KWITANSI HANYA SETELAH LUNAS. Kwitansi menyatakan uangnya sudah
     * diterima; diberikan sebelum itu, ia jadi surat yang kami tanda tangani
     * sendiri untuk sesuatu yang belum terjadi.
     */
    public function dokumen(string $id, string $jenis)
    {
        if (! in_array($jenis, ['faktur', 'kwitansi'], true)) {
            abort(404);
        }

        $pemesanan = PendaftaranScopusKafe::whereKey($id)->first();

        if ($pemesanan === null) {
            return redirect()->route('public.scopuskafe.index')
                ->with('error', 'Pemesanan itu tidak ditemukan.');
        }

        if ($jenis === 'kwitansi' && ! $this->sudahLunas($pemesanan)) {
            return $this->kembaliKeStatus($pemesanan, 'error',
                'Kwitansinya baru bisa diunduh setelah pembayarannya kami terima.');
        }

        try {
            $berkas = \App\Support\LampiranDokumen::rakit('scopus_kafe', $jenis, $pemesanan);
        } catch (\Throwable $e) {
            report($e);

            return $this->kembaliKeStatus($pemesanan, 'error',
                'Dokumennya gagal dibuat. Coba lagi beberapa saat.');
        }

        return response($berkas['isi'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $berkas['nama'] . '"',
        ]);
    }

    /**
     * Mengirimkan tautan halaman status ke email pemesannya.
     *
     * Alamat halaman status memuat UUID yang tidak bisa ditebak, jadi yang
     * menutup tabnya dan tidak menerima suratnya kehilangan pemesanannya —
     * termasuk tombol bayarnya.
     *
     * DIKIRIM KE EMAIL, tidak ditampilkan di layar, dan JAWABANNYA SELALU
     * SAMA — terdaftar maupun tidak. Jawaban yang berbeda mengubah borang ini
     * jadi alat memeriksa apakah satu alamat pernah memesan di sini.
     */
    public function tautanSaya(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:191'],
        ], [
            'email.required' => 'Alamat emailnya belum diisi.',
            'email.email' => 'Alamat emailnya belum benar.',
        ]);

        $jawaban = 'Kalau alamat itu memang terdaftar, tautan pemesanannya sudah kami '
            . 'kirimkan ke sana. Periksa juga folder spam.';

        $pemesanan = PendaftaranScopusKafe::where('email', $data['email'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        if ($pemesanan->isEmpty()) {
            return back()->with('success', $jawaban);
        }

        try {
            Mail::to($data['email'])->send(
                new \App\Mail\TautanPendaftaranScopusKafeMail($pemesanan)
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', $jawaban);
    }

    // <!--================== BAGIANNYA ==================-->

    protected function sudahLunas($pemesanan): bool
    {
        return in_array(
            (string) $pemesanan->status,
            Katalog::KEADAAN['lunas']['nilai'],
            true
        );
    }

    protected function tandaiLunas($pemesanan, string $status, ?string $kanal): void
    {
        $pemesanan->forceFill([
            'status' => 'pembayaran diterima',
            'bayar_status' => $status !== '' ? $status : 'success',
            'bayar_pada' => now(),

            // Kanal yang BENAR-BENAR dipakai — VA Mandiri, QRIS. Tanpa ini
            // layar panitia cuma bisa berkata "DOKU".
            'bayar_kanal' => $kanal ?: $pemesanan->bayar_kanal,
        ])->save();

        \App\PendaftaranJejak::create([
            'layanan' => 'scopus_kafe',
            'pendaftaran_id' => (string) $pemesanan->getKey(),
            'aksi' => 'status',
            'dari' => Katalog::statusAwal('scopus_kafe'),
            'ke' => 'pembayaran diterima',
            'ringkasan' => 'Dibayar lewat ' . ($kanal ?: 'gerbang') . '; ditandai lunas oleh sistem',
        ]);

        /*
         * Surat penerimaannya lewat jalur yang SAMA dengan yang dipakai
         * panitia: jalur itu yang memilih kelas suratnya dari katalog dan
         * mencatat hasil kirimnya di jejak — dan pemesan yang berkata "saya
         * tidak terima email" hanya bisa dijawab dari catatan itu.
         */
        (new \App\Actions\Pendaftaran\KirimSuratStatus)
            ->jalankan('scopus_kafe', $pemesanan->refresh());

        try {
            app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
                ->lunas('scopus_kafe', $pemesanan->refresh());
        } catch (\Throwable $e) {
            Log::warning('Kabar WhatsApp lunas Scopus Kafe gagal.', [
                'nomor' => $pemesanan->id_pemesanan,
                'pesan' => $e->getMessage(),
            ]);
        }
    }

    /** Sesi yang benar-benar dipesan, dari ketiga slotnya. */
    private function sesiTerpesan(PendaftaranScopusKafe $pemesanan): array
    {
        $keluar = [];

        foreach (['', '_kedua', '_ketiga'] as $akhiran) {
            $nama = trim((string) $pemesanan->{'sesi' . $akhiran});

            if ($nama === '') {
                continue;
            }

            $keluar[] = [
                'nama' => $nama,
                'mulai' => $pemesanan->{'waktu_mulai' . $akhiran},
                'selesai' => $pemesanan->{'waktu_selesai' . $akhiran},
                'lokasi' => $pemesanan->{'lokasi' . $akhiran},
                'biaya' => (int) $pemesanan->{'biaya' . $akhiran},
            ];
        }

        return $keluar;
    }

    /**
     * Varian beserta sesi dan harganya, siap digambar.
     *
     * Dirakit di satu tempat karena DUA layar membacanya — halaman daftar
     * dan borang — dan keduanya harus menyebut harga serta jam yang sama.
     * Dua perakitan yang terpisah pernah jadi sebab angka di kartu dan angka
     * di borang berselisih tanpa ada yang menyadarinya.
     *
     * @return list<array<string, mixed>>
     */
    private function varianUntukLayar(): array
    {
        $keluar = [];

        foreach (HargaKafe::varianTersedia() as $kunci => $tarif) {
            $keluar[] = [
                'kunci' => $kunci,
                'nama' => HargaKafe::namaVarian($kunci),
                'lokasi' => HargaKafe::lokasiVarian($kunci),
                'biaya' => HargaKafe::rupiah($tarif->biaya_persesi),
                'fasilitas' => $tarif->daftar_fasilitas,
                'sesi' => HargaKafe::sesiTersedia($kunci),
            ];
        }

        return $keluar;
    }

    /**
     * Tanggal buka terdekat mulai hari ini.
     *
     * Dipakai borangnya sebagai nilai terkecil yang boleh dipilih, dan
     * halaman daftarnya untuk menyebutkan kapan sesi berikutnya berjalan.
     */
    private function tanggalBukaBerikutnya(): Carbon
    {
        $hari = now()->startOfDay();

        // Paling lama enam langkah: tiap pekan pasti memuat Jumat.
        for ($i = 0; $i < 7; $i++) {
            if (HargaKafe::hariBuka($hari)) {
                return $hari;
            }

            $hari = $hari->copy()->addDay();
        }

        return $hari;
    }
}
