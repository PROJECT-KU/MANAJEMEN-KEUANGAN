<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\CategoriesScopusCamp;
use App\PendaftaranScopusCamp;
use App\Mail\ScopusCampMail;
use App\Services\Doku;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicScopusCampController extends Controller
{
    public function __construct(private Doku $doku)
    {
    }


    // <!--================== NAMPILIN KATEGORI ==================-->
    public function public(Request $request)
    {
        /*
         * MODEL, bukan DB::table().
         *
         * Baris mentah stdClass tidak punya accessor, jadi
         * `harga_setelah_promo` padanya adalah properti yang tidak ada —
         * halamannya 500 dengan "Undefined property", dan tanpa accessor itu
         * harga promo tidak akan pernah terlihat di halaman ini.
         *
         * Promonya dimuat berkelompok di bawah: satu kueri untuk seluruh
         * daftar, bukan satu per kartu.
         */
        $kueri = CategoriesScopusCamp::where('layanan', 'scopus_camp')
            ->where('status', 'active')
            /*
             * Yang tanggal mulainya SUDAH TIBA tidak ikut dipajang.
             *
             * KategoriLayanan::tanggalMulaiSudahTiba() menyebut angkatan
             * begitu sudah tertutup sejak 5 Okt 2026, atas permintaan
             * pemiliknya. Terukur di halaman ini: tiga angkatan yang sudah
             * berjalan masih terpampang lengkap dengan tombol "Daftar
             * Sekarang" — orang membayar acara yang sudah separuh jalan,
             * dan tidak ada galat apa pun yang memberi tahu.
             */
            ->whereDate('mulai', '>', now()->toDateString())
            ->orderBy('mulai', 'asc')

            /*
             * PEMUTUS SERI, dan ia bukan kerapian.
             *
             * Angkatan yang tanggal mulainya SAMA — dan itu wajar: satu
             * gelombang Scopus Camp dibuka di beberapa kota pada tanggal
             * yang sama — tidak punya urutan yang ditentukan tanpa baris
             * ini. MySQL boleh memulangkannya dalam urutan apa pun, dan
             * urutan itu boleh berubah antar permintaan.
             *
             * Akibatnya terlihat di bagian "Bedanya": paket mana yang
             * dijelaskan lebih dulu berganti-ganti tiap halaman dimuat.
             * Tertangkap 23 Okt 2026 sebagai uji yang kadang hijau kadang
             * merah pada kode yang sama persis — tiga kali jalan, dua
             * merah satu hijau.
             */
            ->orderBy('id', 'asc');

        /*
         * Dipaginasi. Seluruhnya dulu dicetak di satu halaman: terukur 294
         * kartu setinggi 22.711px dengan markah 627 KB. Angka itu memang
         * sebagian besar baris uji di basis data lokal, tetapi batasnya tetap
         * perlu — tanpa itu halaman ini tumbuh selamanya mengikuti jumlah
         * angkatan yang pernah dibuka.
         */
        $categories = $kueri->paginate(12)->withQueryString();

        \App\Support\PromoAngkatan::muat($categories->getCollection());

        /*
         * Fasilitasnya TIDAK sama untuk semua angkatan, dan itu sempat salah
         * di halaman ini.
         *
         * Versi pertama rombakan ini memajang satu daftar fasilitas di kolom
         * kiri, diambil dari angkatan PERTAMA pada halaman itu. Terukur di
         * tarif yang berlaku, selisihnya nyata:
         *
         *   Pulau Jawa      Rp 5.500.000 — 7 butir, termasuk penginapan di
         *                   tempat acara serta mushola, kolam renang, dan
         *                   treadmill;
         *   Luar Pulau Jawa Rp 6.500.000 — 5 butir, TANPA keduanya.
         *
         * Jadi satu daftar untuk semuanya menjanjikan penginapan dan kolam
         * renang kepada peserta luar Jawa — yang justru membayar satu juta
         * lebih mahal. Tidak ada galat; yang menanggungnya panitia yang
         * harus menjelaskan saat orangnya sudah sampai di lokasi.
         *
         * Dikelompokkan menurut ISI daftarnya, bukan menurut kode variannya:
         * angkatan boleh menyimpan salinan fasilitasnya sendiri yang berbeda
         * dari tarif induk, dan pengelompokan menurut kode akan menyatukan
         * dua daftar yang sebenarnya tidak sama.
         */
        $namaVarian = collect(optional(\App\Layanan::where('kode', 'scopus_camp')->first())->varian ?? [])
            ->pluck('nama', 'kode');

        $paket = $categories->getCollection()
            ->groupBy(fn ($a) => json_encode($a->daftar_fasilitas))
            ->map(function ($kelompok) use ($namaVarian) {
                $varian = $kelompok->pluck('varian')->filter()->unique()->values();

                return [
                    'fasilitas' => $kelompok->first()->daftar_fasilitas,
                    'label' => $varian
                        ->map(fn ($v) => $namaVarian[$v] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $v)))
                        ->implode(' & '),
                    'jumlah' => $kelompok->count(),
                ];
            })
            ->filter(fn ($p) => ! empty($p['fasilitas']))
            ->values();

        /*
         * Yang SAMA ditulis sekali, selisihnya disebut per varian.
         *
         * Bentuk sebelumnya memajang dua daftar utuh berdampingan — dan
         * terukur, keduanya mengulang 5 dari 7 butir yang sama. Untuk tahu
         * bedanya, pembaca harus membandingkan dua daftar baris per baris.
         * Sasaran halaman ini orang berusia lanjut, dan yang mereka cari
         * cuma satu hal: apa bedanya.
         *
         * Pemisahan ini HANYA dipakai kalau ada lebih dari satu paket DAN
         * irisannya benar-benar ada. Dengan satu paket, "yang sama" tidak
         * punya lawan; tanpa irisan, memisahkannya cuma menambah satu
         * judul tanpa mengurangi apa pun yang harus dibaca.
         */
        $fasilitasUmum = [];

        if ($paket->count() > 1) {
            $semua = $paket->pluck('fasilitas')->all();
            $fasilitasUmum = array_values(array_intersect(...$semua));
        }

        $pisahkan = $fasilitasUmum !== [];

        if ($pisahkan) {
            $paket = $paket->map(function ($p) use ($fasilitasUmum) {
                $p['khusus'] = array_values(array_diff($p['fasilitas'], $fasilitasUmum));

                return $p;
            });
        }

        return view('public.scopus_camp.index', compact(
            'categories', 'paket', 'namaVarian', 'fasilitasUmum', 'pisahkan'
        ));
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN DETAIL KATEGORI ==================-->
    public function selengkapnya($id)
    {
        $item = CategoriesScopusCamp::findOrFail($id);

        /*
         * Daftar di bilah kanan dulu `orderBy('created_at','desc')->take(6)`
         * — tanpa saringan apa pun.
         *
         * Akibatnya tiga hal yang semuanya terlihat di layar: angkatan yang
         * kuotanya sudah habis ikut ditawarkan, angkatan yang tanggalnya sudah
         * lewat ikut ditawarkan, dan angkatan yang SEDANG DIBUKA kalah urutan
         * oleh baris yang paling baru dibuat. Diurut menurut tanggal
         * acaranya, bukan tanggal pembuatan barisnya: yang dicari orang
         * "kapan yang berikutnya", bukan "mana yang terakhir diketik admin".
         */
        $terbaru = CategoriesScopusCamp::where('layanan', 'scopus_camp')
            ->where('status', 'active')
            ->whereDate('mulai', '>', now()->toDateString())
            ->where(function ($kueri) {
                $kueri->whereNull('total_kuota')->orWhere('sisa_kuota', '>', 0);
            })
            ->whereKeyNot($item->getKey())
            ->orderBy('mulai')
            ->take(5)
            ->get();

        // Tanpa ini, harga di bilah kanan memakai harga penuh sementara
        // halaman daftarnya menjanjikan harga promo.
        \App\Support\PromoAngkatan::muat($terbaru);

        /*
         * ALASAN TERTUTUPNYA, bukan hanya penuh-tidaknya.
         *
         * Halaman ini sampai 25 Okt 2026 cuma menghitung sisa kursi. Terukur
         * pada angkatan Scopus Camp Padang — status 'non active', tanggal
         * mulainya 4 Sep, sudah lewat: halamannya menjawab 200 dengan DUA
         * tombol "Daftar sekarang", dan menekannya memantulkan orangnya ke
         * daftar angkatan tanpa satu kata pun penjelasan.
         *
         * Dipakai aturan yang SAMA dengan borangnya — ketiga butirnya, bukan
         * satu — supaya halaman ini tidak pernah menjanjikan apa yang
         * borangnya menolak.
         */
        return view('public.scopus_camp.selengkapnya', [
            'item' => $item,
            'terbaru' => $terbaru,
            'tertutup' => $this->alasanTertutup($item),
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== NAMPILIN FORM PENDAFTARAN ==================-->
    public function FormPendaftaran($id)
    {
        $item = CategoriesScopusCamp::findOrFail($id);

        /*
         * Angkatan yang sudah tertutup TIDAK digambar borangnya.
         *
         * Halaman daftarnya memang menyembunyikannya, tetapi alamat borang
         * ini tetap bisa dibuka dari tautan lama, surat, atau penanda buku —
         * dan terukur 23 Okt 2026 ia menjawab 200 untuk ketiganya sekaligus:
         * angkatan yang kursinya habis, yang tanggalnya sudah lewat, dan
         * yang masih draft.
         *
         * Dicegat di sini, bukan hanya saat mengirim: membiarkan orang
         * mengisi empat isian lalu menolaknya di akhir adalah cara paling
         * buruk menyampaikan kabar yang sudah diketahui sejak awal.
         */
        $tertutup = $this->alasanTertutup($item);

        if ($tertutup !== null) {
            return redirect()->route('public.scopuscamp.index')->with('error', $tertutup);
        }

        /*
         * Borangnya BERCABANG menurut kesiapan gerbang pembayaran.
         *
         * Dengan gerbang siap, nomor rekening dan unggah bukti transfer
         * tidak digambar sama sekali: orangnya menekan satu tombol, membayar
         * di DOKU dengan cara apa pun yang ia punya, dan selesai. Tanpa
         * gerbang, jalur transfer manual yang lama tetap utuh — kalau tidak,
         * peladen yang kredensial DOKU-nya belum dipasang akan memajang
         * borang yang tidak punya satu pun cara membayar.
         *
         * Diperiksa di sini, BUKAN di dalam Blade: satu sumber jawaban untuk
         * borang dan untuk penyimpanannya. Kalau keduanya bertanya sendiri-
         * sendiri, borang bisa menuntut bukti transfer sementara peladen
         * mengarahkan ke DOKU.
         */
        /*
         * Yang SUDAH masuk akun tidak perlu mengetik apa pun.
         *
         * Dirender terisi dari peladen, bukan lewat pencarian nomor: datanya
         * sudah ada di sesi, dan menunggu ia mengetik nomornya sendiri dulu
         * berarti meminta keterangan yang kita pegang.
         */
        $akun = auth()->user();

        return view('public.scopus_camp.form_pendaftaran', [
            'item' => $item,
            'bayarDaring' => $this->doku->siap(),

            /*
             * Sisa kursi yang SEBENARNYA — sudah mengecualikan pendaftaran
             * yang dibatalkan. `$item->sisa_kuota` tidak dipakai: kolom itu
             * diisi hitungan lama yang ikut menghitung kursi orang yang
             * sudah batal, dan terukur ia meleset sampai 15 kursi.
             */
            'sisaKursi' => $this->sisaKursi($item),

            // Tenggat bayarnya disebut di borang, bukan baru ditemukan
            // orangnya saat tagihannya sudah mati.
            'menitBayar' => Doku::MENIT_KEDALUWARSA,

            'isiAwal' => $akun === null ? [] : array_filter([
                'nama' => $akun->full_name,
                'email' => $akun->email,
                'telp' => $akun->telp,
                'affiliasi' => $akun->company,
            ]),
        ]);
    }

    /**
     * Data diri pemilik satu nomor WhatsApp, untuk mengisi borang otomatis.
     *
     * Alamat ini MENUKAR NOMOR JADI NAMA DAN EMAIL. Siapa pun bisa
     * memanggilnya, jadi siapa pun bisa memanen keduanya dengan mencoba nomor
     * satu per satu. Itu diterima sebagai harga kemudahan ini — keputusan
     * yang sudah diambil saat jalur yang sama dibuat untuk Webinar Eksklusif
     * — dengan dua syarat yang tidak boleh dilepas: alamatnya dibatasi
     * jumlah permintaan per menit per IP di routes/web.php, dan nomornya
     * dicatat TERSAMAR di bawah, tidak pernah utuh.
     */
    public function cariPendaftar(Request $request, \App\Support\IsiOtomatisDariNomor $pencari)
    {
        $data = $request->validate([
            'telp' => ['required', 'string', 'max:30'],
        ]);

        $ketemu = $pencari->cari($data['telp']);

        if ($ketemu === null) {
            // Jawaban yang SAMA untuk nomor yang tidak masuk akal dan nomor
            // yang memang belum pernah memesan. Membedakannya memberi tahu
            // pemanen mana nomor yang benar-benar ada di sini.
            return response()->json(['ditemukan' => false]);
        }

        $nomor = \App\Support\NomorTelepon::rapikan($data['telp']);

        Log::info('Isi otomatis borang Scopus Camp', [
            'nomor' => substr($nomor, 0, 5) . '***' . substr($nomor, -3),
            'ip' => $request->ip(),
            'sumber' => $ketemu['sumber'],
        ]);

        return response()->json([
            'ditemukan' => true,
            'nama' => $ketemu['nama'],
            'email' => $ketemu['email'],
            'affiliasi' => $ketemu['affiliasi'],
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== CEK KODE DISKON ==================-->
    public function cekKodeDiskon(Request $request, $id)
    {
        $kode = $request->query('kode_diskon');

        \Log::info('Cek Kode Diskon', [
            'id_diterima' => $id,
            'kode_diterima' => $kode,
        ]);

        $diskon = DB::table('kategori_layanan')->where('layanan', 'scopus_camp')
            ->where('id', $id)
            ->first();

        if (!$diskon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan. ID: ' . $id,
            ]);
        }

        if (is_null($diskon->kode_diskon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode diskon tidak tersedia untuk Batch ini.',
            ]);
        }

        if ($diskon->kode_diskon !== $kode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode diskon tidak cocok.',
            ]);
        }

        $nominal = trim((string)$diskon->nominal_diskon);
        $casted = is_numeric($nominal) ? (int)$nominal : 0;

        return response()->json([
                    'status' => 'success',
            'kode_input' => $kode,
            'nominal_diskon' => $nominal,
            'casted_nominal_diskon' => $casted,
        ]);
    }
    // <!--================== END ==================-->

    // <!--================== KIRIM DATA PENDAFTARAN ==================-->
    /*
     * generateRandomToken() dan generateRandomId() DIBUANG dari sini.
     *
     * Keduanya tidak dipanggil siapa pun lagi: nomor pendaftarannya dirakit
     * PendaftaranSemuaLayanan::nomorBaru() dan tokennya Str::random(). Salinan
     * yang sama masih hidup di empat pengendali lain, dan itu disengaja —
     * membuangnya dari sana mengubah empat layanan sekaligus.
     */

    /**
     * Status pendaftaran yang TIDAK lagi menahan kursi.
     *
     * Kuotanya dulu dihitung `sum(jumlah_pendaftar)` atas SELURUH baris,
     * termasuk yang sudah dibatalkan, ditolak, dan direfund. Terukur di basis
     * data 23 Okt 2026: 23 kursi tertahan di 5 angkatan — Scopus Camp
     * Yogyakarta #199 terbaca "tinggal 4 kursi" padahal 15 dari 16 yang
     * terpakai milik pendaftaran yang sudah batal. Itu kursi yang tidak
     * terjual karena sistemnya salah hitung, dan tidak ada galat apa pun
     * yang menunjukkannya.
     */
    private const STATUS_LEPAS_KURSI = [
        'Pendaftaran Dibatalkan',
        'Pendaftaran Ditolak',
        'Pendaftaran Refund',
    ];

    /**
     * Berapa kursi yang masih bisa dijual, menurut kolom `sisa_kuota`.
     *
     * Kolom itu yang dipakai SELURUH aplikasi — halaman daftar, layar
     * panitia, dan ekspor — dan panitia bisa menyuntingnya sendiri untuk
     * menutup angkatan lebih awal. Jadi ia yang jadi pegangan di sini juga;
     * menghitungnya ulang saat dibaca akan diam-diam MEMBUKA KEMBALI
     * angkatan yang sengaja ditutup panitia dengan menurunkan angkanya.
     *
     * Yang diperbaiki bukan pembacaannya melainkan PEMELIHARAANNYA — lihat
     * hitungUlangSisaKursi().
     */
    private function sisaKursi(CategoriesScopusCamp $angkatan): ?int
    {
        if ($angkatan->total_kuota === null) {
            return null;   // tidak dibatasi
        }

        return max(0, (int) $angkatan->sisa_kuota);
    }

    /**
     * Sisa kursi yang SEHARUSNYA, dihitung dari pendaftaran yang masih hidup.
     *
     * Dipakai saat menuliskan kembali kolomnya, bukan saat membacanya.
     *
     * Rumus lamanya `total_kuota - sum(jumlah_pendaftar)` atas SELURUH baris,
     * termasuk yang sudah dibatalkan, ditolak, dan direfund. Terukur 23 Okt
     * 2026: 23 kursi tertahan di 5 angkatan — Scopus Camp Yogyakarta #199
     * terbaca "tinggal 4 kursi" padahal 15 dari 16 yang terpakai milik
     * pendaftaran yang sudah batal. Itu kursi yang tidak terjual karena
     * sistemnya salah hitung, dan tidak ada galat apa pun yang menunjukkannya.
     */
    public static function hitungUlangSisaKursi(CategoriesScopusCamp $angkatan): ?int
    {
        if ($angkatan->total_kuota === null) {
            return null;
        }

        $terpakai = (int) PendaftaranScopusCamp::where('kategori_id', $angkatan->getKey())
            ->whereNotIn('status', self::STATUS_LEPAS_KURSI)
            ->sum('jumlah_pendaftar');

        return max(0, (int) $angkatan->total_kuota - $terpakai);
    }

    /**
     * Alasan satu angkatan TIDAK boleh menerima pendaftaran, atau null.
     *
     * Dipakai dua kali — saat borangnya dibuka dan saat borangnya dikirim —
     * dan itu memang perlu keduanya: halaman daftarnya menyembunyikan
     * angkatan yang sudah tertutup, tetapi alamat borangnya tetap bisa
     * dibuka dari tautan lama, surat, atau penanda buku.
     *
     * Terukur 23 Okt 2026, sebelum ada pemeriksaan ini: borangnya menjawab
     * 200 untuk angkatan yang kursinya habis, yang tanggalnya sudah lewat,
     * DAN yang masih draft — dan kiriman ke angkatan draft yang sudah
     * berjalan diterima lengkap dengan tagihan DOKU yang benar-benar terbit.
     */
    private function alasanTertutup(CategoriesScopusCamp $angkatan): ?string
    {
        if ($angkatan->status !== 'active') {
            return 'Angkatan itu belum dibuka untuk pendaftaran umum.';
        }

        if ($angkatan->mulai !== null
            && \Carbon\Carbon::parse($angkatan->mulai)->startOfDay()->lte(now()->startOfDay())) {
            return 'Angkatan itu sudah berjalan, jadi pendaftarannya sudah ditutup.';
        }

        $sisa = $this->sisaKursi($angkatan);

        if ($sisa !== null && $sisa <= 0) {
            return 'Kursi angkatan itu sudah habis.';
        }

        return null;
    }

    public function store(Request $request)
    {
        /*
         * VALIDASI, dan sampai 23 Okt 2026 tidak ada satu pun di sini.
         *
         * Terukur: borang KOSONG SAMA SEKALI — tanpa nama, email, maupun
         * nomor — diterima, tersimpan sebagai NULL bertiga, dan tagihan DOKU
         * sungguhan terbit atasnya. `email=BUKAN-EMAIL` dan `telp=abc`
         * tersimpan apa adanya. Yang menjaga cuma atribut `required` di
         * peramban, dan itu hilang begitu orang memakai alat lain.
         *
         * Akibatnya panitia memegang pendaftaran yang tidak bisa dihubungi,
         * dan surat buktinya tidak pernah sampai ke siapa pun.
         */
        $data = $request->validate([
            'kategori_id' => ['required', 'string'],
            'nama' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:100'],
            'telp' => ['required', 'string', 'min:8', 'max:25'],
            'affiliasi' => ['required', 'string', 'min:3', 'max:150'],
                    'jumlah_pendaftar' => ['required', 'integer', 'min:1', 'max:20'],
                    'kode_diskon' => ['nullable', 'string', 'max:50'],
            'setuju' => ['accepted'],

            /*
             * Nama peserta kedua dan seterusnya.
             *
             * Wajib sebanyak peserta yang dipesan DIKURANGI SATU — yang
             * pertama sudah terisi di medan nama. Tanpa ini, pendaftaran
             * rombongan hanya menyisakan satu nama: panitia menagih biaya
             * untuk lima orang lalu memegang daftar hadir berisi satu, dan
             * sisanya harus dikejar lewat WhatsApp satu per satu.
             */
            'peserta' => ['array', 'max:19'],
            'peserta.*.nama' => ['required', 'string', 'min:3', 'max:120'],

            /*
             * NOMOR WHATSAPP, bukan email — keputusan pemiliknya 23 Okt 2026.
             *
             * Itu juga yang sudah dipakai jalur panitia: kotak daftar peserta
             * di layar rincian berbentuk "Budi Santoso, 081234567890", dan
             * ketujuh baris peserta yang sudah ada di basis data punya `telp`
             * terisi dengan `email` NULL bertujuh. Meminta email di borang
             * umum berarti mengisi kolom yang tidak dibaca siapa pun,
             * sementara kolom yang dipakai panitia tetap kosong.
             *
             * Boleh dikosongkan, sama seperti di layar panitia: yang
             * mendaftarkan rombongan belum tentu memegang nomor semua
             * orangnya saat itu juga, dan memaksakannya membuat orang
             * mengarang nomor — yang jauh lebih buruk daripada kolom kosong.
             */
            'peserta.*.telp' => ['nullable', 'string', 'max:25'],
        ], [
            'setuju.accepted' => 'Centang dulu persetujuan ketentuannya.',
            'peserta.*.nama.required' => 'Nama tiap peserta diisi dulu, ya — '
                . 'sertifikatnya atas nama mereka.',
        ]);

        /*
         * validate() TIDAK memuat kunci yang tidak dikirim peramban. Dibaca
         * langsung, $data['peserta'] melempar "Undefined array key" pada
         * SETIAP pendaftaran satu orang — yaitu hampir semuanya.
         */
        $data['peserta'] = array_values($data['peserta'] ?? []);

        /*
         * Jumlah namanya diperiksa TERPISAH, sebab yang diperiksa hubungan
         * antar-medan: sebanyak peserta yang dipesan, dikurangi pendaftar
         * utamanya.
         *
         * Di peladen, bukan hanya di peramban: borang yang dikirim tanpa
         * JavaScript akan lolos begitu saja, dan yang tersimpan pendaftaran
         * sepuluh orang dengan satu nama.
         */
        $perluNama = max(0, (int) $data['jumlah_pendaftar'] - 1);

        if (count($data['peserta']) !== $perluNama) {
            return back()->withInput()->withErrors([
                'peserta' => $perluNama === 0
                    ? 'Jumlah pesertanya satu, jadi tidak perlu nama tambahan.'
                    : 'Isi nama ' . $perluNama . ' peserta lainnya — sertifikatnya '
                        . 'atas nama mereka.',
            ]);
        }

        /*
         * Nomornya DINORMALKAN sekarang juga, sebelum diperiksa kembar.
         *
         * "0812-3456-7890" dan "081234567890" adalah nomor yang SAMA, tetapi
         * sebagai untaian keduanya berbeda — jadi memeriksa kembarnya pada
         * teks mentah akan meloloskan dua peserta bernomor sama yang cuma
         * beda tanda hubung.
         */
        foreach ($data['peserta'] as $i => $orang) {
            $data['peserta'][$i]['telp'] = \App\Support\NomorTelepon::rapikan(
                (string) ($orang['telp'] ?? '')
            );
        }

        /*
         * Nomor yang diisi harus MASUK AKAL. Nomor sepotong seperti "0812"
         * tersimpan apa adanya dan baru ketahuan saat panitia menekannya dan
         * tidak ada yang menjawab.
         */
        foreach ($data['peserta'] as $orang) {
            if ($orang['telp'] !== '' && ! \App\Support\NomorTelepon::masukAkal($orang['telp'])) {
                return back()->withInput()->withErrors([
                    'peserta' => 'Ada nomor WhatsApp peserta yang belum benar. '
                        . 'Periksa lagi, atau kosongkan saja yang belum diketahui.',
                ]);
            }
        }

        /*
         * Nomor peserta tidak boleh kembar, baik sesama peserta maupun dengan
         * pendaftar utamanya. Dua orang bernomor sama berarti satu di
         * antaranya tidak akan pernah dihubungi, dan yang ketahuan belakangan
         * hanyalah "saya tidak pernah dimasukkan ke grup".
         */
        $nomor = array_filter(array_column($data['peserta'], 'telp'));
        $nomor[] = \App\Support\NomorTelepon::rapikan($data['telp']);

        if (count($nomor) !== count(array_unique($nomor))) {
            return back()->withInput()->withErrors([
                'peserta' => 'Ada nomor WhatsApp yang dipakai dua kali. Tiap peserta '
                    . 'perlu nomor sendiri, atau kosongkan saja yang belum diketahui.',
            ]);
        }

        $kategori = CategoriesScopusCamp::findOrFail($data['kategori_id']);

        $tertutup = $this->alasanTertutup($kategori);

        if ($tertutup !== null) {
            return redirect()->route('public.scopuscamp.index')->with('error', $tertutup);
        }

        $jumlahPendaftarBaru = (int) $data['jumlah_pendaftar'];

        /*
         * Lewat gerbang pembayaran kalau kredensialnya ada, kalau tidak
         * transfer manual seperti dulu.
         *
         * Ditanyakan SEKALI lalu disimpan: kalau jawabannya sempat berubah
         * di tengah, barisnya bisa tersimpan sebagai 'doku' tanpa tagihan,
         * atau sebagai 'transfer' tanpa bukti.
         */
        $bayarDaring = $this->doku->siap();

        if (! $bayarDaring && ! $request->hasFile('gambar')) {
            return back()->withInput()
                ->withErrors(['gambar' => 'Bukti transfernya belum diunggah.']);
        }

        /*
         * PENDAFTARAN GANDA ditolak sebelum barisnya dibuat.
         *
         * Orang menekan tombolnya dua kali karena mengira yang pertama
         * gagal — halaman ini memang diam beberapa detik saat tagihannya
         * dibuat — dan tanpa penjagaan ini ia mendapat DUA tagihan untuk
         * satu kursi, lalu sebagian membayar keduanya.
         *
         * Yang dicegat diantar ke halaman status pendaftaran yang sudah ada,
         * bukan ditolak dengan pesan galat: ia memang berhak atas kursinya,
         * dan yang ia butuhkan tautan bayarnya — bukan pemberitahuan bahwa
         * ia sudah mendaftar.
         */
        $sudahAda = PendaftaranScopusCamp::where('kategori_id', $kategori->getKey())
            ->whereNotIn('status', self::STATUS_LEPAS_KURSI)
            ->where('email', mb_strtolower(trim($data['email'])))
            ->where('nama', trim($data['nama']))
            ->latest('created_at')
            ->first();

        if ($sudahAda !== null) {
            return redirect()->route('public.scopuscamp.status', $sudahAda->getKey())
                ->with('error', 'Data ini sudah terdaftar di angkatan tersebut. '
                    . 'Kursinya sudah ditahan untuk Anda — tidak perlu mendaftar lagi.');
        }

        // SAVE IMAGE
        $imagePath = null;

        if (! $bayarDaring && $request->hasFile('gambar')) {
            $image = $request->file('gambar');

            $imageName = Str::uuid() . '_Bukti_TF_Scopus_Camp.' . $image->getClientOriginalExtension();
            $imagePath = 'ScopusCamp/' . $imageName;

            $image->move(public_path('ScopusCamp'), $imageName);
        }
        // END

        // CEK APAKAH KODE DISKON NULL ATAU TIDAK
        $kodeDiskon = $data['kode_diskon'] ?? null;
        $nominalDiskon = null;

        if (! empty($kodeDiskon)) {
            $nominalDiskon = str_replace('.', '', (string) $request->input('nominal_diskon'));
        }
        // END

        /*
         * Harganya DIHITUNG ULANG di peladen, bukan diterima dari borangnya.
         *
         * Sebelum ini `total_pembayaran`, `ppn`, dan `kode_unik` disimpan
         * mentah dari kiriman — nominal yang ditagihkan sepenuhnya ditentukan
         * sisi yang tidak bisa dipercaya, dan siapa pun bisa mendaftar dengan
         * total nol tanpa satu baris pun yang menolak.
         *
         * Rumusnya di App\Support\HargaUmum, satu tempat bersama borangnya —
         * jadi angka yang dipajang dan angka yang ditagih tidak bisa berselisih.
         */
        $harga = \App\Support\HargaUmum::hitung(
            $kategori,
            $jumlahPendaftarBaru,
            $request->input('kode_unik'),
            $nominalDiskon
        );

        $cleanPPN = (string) $harga['ppn'];
        $cleanTotalPembayaran = (string) $harga['total'];

        /*
         * KURSINYA DIKUNCI, lalu diperiksa lagi DI DALAM kunci.
         *
         * Pemeriksaan di atas dilakukan sebelum transaksi, jadi ia cuma
         * menyaring lebih awal. Tanpa kunci baris, dua orang yang menekan
         * "Lanjut ke pembayaran" pada detik yang sama sama-sama lolos
         * pemeriksaan itu — keduanya membayar kursi terakhir yang sama, dan
         * yang kalah baru tahu saat sudah sampai di lokasi.
         *
         * Pola ini disalin dari Webinar Eksklusif yang sudah memakainya.
         */
        try {
            $save = DB::transaction(function () use (
                $kategori, $data, $jumlahPendaftarBaru, $bayarDaring,
                $imagePath, $kodeDiskon, $nominalDiskon, $harga,
                $cleanPPN, $cleanTotalPembayaran, $request
            ) {
                $terkunci = CategoriesScopusCamp::whereKey($kategori->getKey())
                    ->lockForUpdate()
                    ->first();

                $sisa = $this->sisaKursi($terkunci);

                if ($sisa !== null && $sisa < $jumlahPendaftarBaru) {
                    throw new \RuntimeException($sisa <= 0
                        ? 'Kursi angkatan itu baru saja habis.'
                        : 'Kursinya tinggal ' . $sisa . ', tidak cukup untuk '
                            . $jumlahPendaftarBaru . ' orang.');
                }

                $baris = PendaftaranScopusCamp::create([
                    'token'                                                     => \Illuminate\Support\Str::random(30),
                    'id_transaksi'                                              => \App\Support\PendaftaranSemuaLayanan::nomorBaru('scopus_camp'),
                    'kategori_id'                                               => $terkunci->getKey(),
                    'email'                                                     => mb_strtolower(trim($data['email'])),
                    'nama'                                                      => trim($data['nama']),
                    'telp'                                                      => \App\Support\NomorTelepon::rapikan($data['telp']),
                    'affiliasi'                                                 => trim($data['affiliasi']),
                    'ppn'                                                       => $cleanPPN,
                    'kode_unik'                                                 => $harga['kode_unik'],
                    'gambar'                                                    => $imagePath,
                    'jumlah_pendaftar'                                          => $jumlahPendaftarBaru,
                    'kode_diskon'                                               => $kodeDiskon,
                    'nominal_diskon'                                            => $nominalDiskon,
                    'total_pembayaran'                                          => $cleanTotalPembayaran,
            // Tali ke promo yang memotong harganya; dibaca layar dampak promo.
                    'promo_id'                                                  => $harga['promo']?->getKey(),

            /*
             * Statusnya DIPUTUSKAN DI SINI, tidak lagi diterima dari borang.
             *
             * Sebelum ini `$request->input('status') ?? 'diproses'`: siapa
             * pun bisa mengirimkan status='Pendaftaran Diterima' bersama
             * borangnya dan mendaftar sebagai lunas tanpa membayar sepeser
             * pun. Tidak ada galat, dan di layar panitia ia terbaca persis
             * sama dengan yang sudah diperiksa buktinya.
             *
             * Nilainya dibaca dari katalog, bukan dituliskan ulang: ketiga
             * layanan memakai kata yang berbeda untuk keadaan yang sama, dan
             * salinan kedua di sini akan berselisih diam-diam saat katalognya
             * berubah.
             */
                    'status'                                                    => \App\Support\PendaftaranSemuaLayanan::statusAwal('scopus_camp'),

            // 'doku' atau 'transfer'; layar panitia membedakan keduanya,
            // dan hanya yang 'doku' punya tagihan untuk ditutup saat
            // pendaftarannya dibatalkan.
                    'cara_bayar'                                                => $bayarDaring ? 'doku' : 'transfer',

            /*
             * Tenggat HANYA untuk yang lewat gerbang: tagihan DOKU memang
             * mati sendiri sesudah sekian menit, sedangkan transfer manual
             * tidak punya tenggat sama sekali di layanan ini.
             */
                    'kedaluwarsa_pada'                                          => $bayarDaring
                ? now()->addMinutes(Doku::MENIT_KEDALUWARSA)
                : null,

                    /*
                     * `note` TIDAK lagi diambil dari kiriman borang.
                     *
                     * Kolom itu kolom catatan PANITIA — dibaca dan disunting
                     * di layar Pendaftar Layanan — dan sampai sekarang isinya
                     * bisa ditentukan siapa pun yang mengirim borang ini,
                     * sebanyak apa pun, tanpa satu baris pun yang membatasinya.
                     * Borangnya sendiri memang tidak pernah punya medan itu.
                     */
                ]);

                /*
                 * Nama peserta lainnya disimpan DI DALAM transaksi yang sama.
                 *
                 * Di luarnya, pendaftaran yang gagal di tengah meninggalkan
                 * baris peserta yang induknya tidak ada — dan tidak ada satu
                 * pun layar yang memperlihatkannya.
                 */
                foreach ($data['peserta'] as $ke => $orang) {
                    $baris->pesertaLain()->create([
                        /*
                         * Mulai dari SATU, bukan nol.
                         *
                         * Urutan 0 dipegang pendaftar utamanya, yang tersimpan
                         * di baris pendaftarannya sendiri — itu perjanjian
                         * yang sudah dipakai jalur panitia, dan ketujuh baris
                         * yang sudah ada di basis data bernomor 1 sampai 7.
                         * Menulis dari nol membuat layar panitia memasangkan
                         * baris yang salah saat daftarnya disunting ulang.
                         */
                        'urutan' => $ke + 1,
                        'nama' => trim($orang['nama']),

                        // Kosong disimpan null, bukan untaian kosong: dua
                        // penanda "tidak ada nomor" di satu kolom membuat
                        // tiap pembacanya harus memeriksa keduanya.
                        'telp' => $orang['telp'] !== '' ? $orang['telp'] : null,
                    ]);
                }

                /*
                 * `sisa_kuota` disetel ulang dari hitungan yang BARU —
                 * yang sudah mengecualikan pendaftaran batal. Sebelum ini
                 * ia dihitung dari sum() atas seluruh baris, jadi tiap
                 * pendaftaran baru menuliskan kembali angka yang keliru.
                 */
                if ($terkunci->total_kuota !== null) {
                    $terkunci->forceFill([
                        'sisa_kuota' => (string) self::hitungUlangSisaKursi($terkunci),
                    ])->save();
                }

                return $baris;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($save) {
            $appName = 'Rumah Scopus Foundation';
            $emailTo = $data['email'];

            /*
             * Surat buktinya tetap dikirim lebih dulu, lewat gerbang maupun
             * tidak. Ia yang memuat nomor pendaftaran dan tautan halaman
             * statusnya — satu-satunya jalan kembali bagi yang tab
             * pembayarannya telanjur tertutup.
             *
             * Dibungkus: alamat email yang salah ketik melempar, dan sampai
             * sekarang lemparan itu membatalkan SELURUH pendaftaran yang
             * barisnya sudah tersimpan dan kuotanya sudah terpotong —
             * orangnya melihat halaman galat dan mendaftar lagi.
             */
            try {
                Mail::to($emailTo)->send(new ScopusCampMail($save, $kategori, $appName));
            } catch (\Throwable $e) {
                report($e);
            }

            /*
             * WhatsApp-nya sepasang dengan suratnya, dan sebabnya sama:
             * tidak semua orang membuka emailnya, dan yang tidak membukanya
             * kehilangan nomor pendaftaran, tagihan, dan batas waktunya
             * sekaligus — lalu kursinya dilepas tanpa ia pernah tahu.
             *
             * try-nya TERPISAH dari milik surat: surat yang gagal tidak boleh
             * ikut membatalkan WhatsApp-nya, dan sebaliknya. Keduanya juga
             * tidak boleh menggagalkan pendaftaran yang barisnya SUDAH
             * tersimpan dan kuotanya sudah terpotong.
             */
            try {
                app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
                    ->pendaftaranMasuk('scopus_camp', $save, $kategori);
            } catch (\Throwable $e) {
                Log::warning('Kabar WhatsApp pendaftaran Scopus Camp gagal.', [
                    'nomor' => $save->id_transaksi,
                    'pesan' => $e->getMessage(),
                ]);
            }

            if ($bayarDaring) {
                return $this->mulaiBayar($save, $kategori);
            }

            return redirect()->route('public.scopuscamp.index')->with('success', 'Pendaftaran Analisis Bibliometrik Terkirim!');
        } else {
            return redirect()->route('public.scopuscamp.index')->with('error', 'Pendaftaran Analisis Bibliometrik Gagal Terkirim!');
        }
    }

    // <!--================== END ==================-->

    // <!--================== BAYAR LEWAT DOKU ==================-->

    /**
     * Membuat tagihan lalu mengantar pendaftarnya ke halaman bayar DOKU.
     *
     * Gagal membuat tagihan TIDAK menghapus pendaftarannya. Orangnya sudah
     * mengisi borang dan kursinya sudah ditahan; membatalkannya berarti ia
     * mengulang dari nol karena kesalahan yang bukan miliknya. Ia diantar ke
     * halaman status, yang memuat nomor rekening dan nomor panitia.
     */
    /**
     * Nomor tagihan untuk percobaan pembayaran BERIKUTNYA.
     *
     * Percobaan pertama memakai nomor pendaftarannya apa adanya — itu yang
     * paling mudah dicocokkan panitia dengan mutasi, dan sebagian besar
     * pendaftaran tidak pernah butuh percobaan kedua.
     *
     * Sesudah itu ditambahkan -R2, -R3, dan seterusnya. Angkanya dibaca dari
     * nomor tagihan SEBELUMNYA, bukan dari penghitung di kolom lain: satu
     * sumber, dan tidak ada dua nilai yang bisa berselisih.
     *
     * Akhirannya memakai huruf R, bukan hanya angka. Nomor pendaftaran kita
     * sendiri berakhir dengan tanda hubung dan empat angka
     * (CAMP-20261024-0001), jadi akhiran yang cuma angka tidak bisa
     * dibedakan dari bagian nomornya sendiri oleh mata siapa pun yang
     * menelusuri pembayaran.
     */
    private function nomorTagihanBaru(PendaftaranScopusCamp $pendaftaran): string
    {
        $pokok = (string) $pendaftaran->id_transaksi;
        $sebelumnya = trim((string) $pendaftaran->bayar_nomor);

        /*
         * Nomor tagihan sebelumnya SUDAH tercatat: hitungannya dilanjutkan
         * dari situ, bukan dari penghitung di kolom lain. Satu sumber, dan
         * tidak ada dua nilai yang bisa berselisih.
         */
        if ($sebelumnya !== '' && $sebelumnya !== $pokok) {
            $ke = preg_match('/-R(\d+)$/', $sebelumnya, $cocok) === 1
                ? ((int) $cocok[1]) + 1
                : 2;

            return $pokok . '-R' . $ke;
        }

        /*
         * Belum tercatat — tetapi itu TIDAK berarti belum pernah ada
         * tagihannya.
         *
         * Kolom `bayar_nomor` baru ada sejak 25 Okt 2026. Pendaftaran yang
         * tagihannya dibuat sebelum itu memakai nomor pendaftarannya apa
         * adanya dan tidak mencatatnya di mana pun — jadi nomor itu SUDAH
         * terpakai di DOKU, dan memakainya lagi dijawab "INVOICE ALREADY
         * USED". Persis itu yang terjadi pada CAMP-20261024-0001, yang sejak
         * 24 Okt tidak bisa dibayar sama sekali.
         *
         * Jejak tagihan lama dibaca dari keempat kolom yang hanya terisi
         * kalau tagihannya memang pernah dibuat atau pernah dicoba. Satu pun
         * terisi sudah cukup: lebih baik melewati satu nomor yang sebenarnya
         * masih bersih daripada memakai nomor yang sudah mati.
         */
        $pernahAda = $sebelumnya === $pokok
            || trim((string) $pendaftaran->bayar_rujukan) !== ''
            || trim((string) $pendaftaran->bayar_permintaan_id) !== ''
            || trim((string) $pendaftaran->bayar_url) !== ''
            || trim((string) $pendaftaran->bayar_status) !== '';

        return $pernahAda ? $pokok . '-R2' : $pokok;
    }

    private function mulaiBayar(PendaftaranScopusCamp $pendaftaran, CategoriesScopusCamp $kategori)
    {
        /*
         * Rinciannya HARUS berjumlah sama dengan total yang tersimpan, kalau
         * tidak DOKU menolak dengan "AMOUNT NOT MATCH". Jadi ketiga bagian
         * yang membentuk total — potongan, kode unik, dan PPN — ikut dikirim
         * semuanya, bukan cuma harga kali jumlah peserta.
         *
         * PPN-nya dikirim sebagai baris tersendiri karena di layanan ini ia
         * dihitung dari SATU kursi, bukan dari subtotal; dilipat ke harga
         * satuan, angkanya akan meleset begitu pesertanya lebih dari satu.
         */
        /*
         * Nomor tagihannya BUKAN nomor pendaftarannya begitu tagihan ini
         * dibuat ulang. DOKU menolak nomor yang sudah pernah dipakai dengan
         * "INVOICE ALREADY USED", jadi percobaan kedua dan seterusnya
         * memakai nomor berakhiran -R2, -R3, dan disimpan di kolomnya
         * sendiri supaya pemberitahuan baliknya tetap menemukan barisnya.
         */
        $nomorTagihan = $this->nomorTagihanBaru($pendaftaran);

        $hasil = $this->doku->buatTagihan([
            'nomor' => $nomorTagihan,
            'jumlah' => (int) $pendaftaran->total_pembayaran,
            'harga_satuan' => (int) $kategori->harga_setelah_promo,
            'jumlah_peserta' => (int) $pendaftaran->jumlah_pendaftar,
            'potongan' => (int) $pendaftaran->nominal_diskon,
            'label_potongan' => $pendaftaran->kode_diskon
                ? 'Potongan ' . $pendaftaran->kode_diskon
                : 'Potongan',
                    'kode_unik' => (int) $pendaftaran->kode_unik,
                    'ppn' => (int) $pendaftaran->ppn,
            'judul' => trim($kategori->nama . ' ' . ($kategori->nama_ke ? '#' . $kategori->nama_ke : '')),
            'nama' => $pendaftaran->nama,
            'email' => $pendaftaran->email,
            'telp' => $pendaftaran->telp,
            'kembali' => route('public.scopuscamp.status', $pendaftaran->getKey()),

            /*
             * HANYA QRIS dan Virtual Account, atas keputusan pemiliknya
             * 23 Okt 2026.
             *
             * Dibatasi di permintaan tagihannya, bukan cuma di daftar yang
             * dipajang borang. Daftar di borang itu janji; yang menentukan
             * pilihan sebenarnya halaman DOKU, dan tanpa medan ini ia tetap
             * memajang kartu, e-wallet, dan gerai — persis yang tidak jadi
             * diterima.
             */
            'kanal' => Doku::KANAL_QRIS_DAN_VA,
        ]);

        if (! $hasil['berhasil'] || ! $hasil['url']) {
            $pendaftaran->forceFill([
                'bayar_status' => 'gagal-dibuat',
                'note' => trim((string) $pendaftaran->note . ' Tagihan daring gagal dibuat: '
                    . ($hasil['pesan'] ?? 'tidak diketahui')),
            ])->save();

            return redirect()->route('public.scopuscamp.status', $pendaftaran->getKey())
                ->with('error', 'Pembayaran daring sedang bermasalah. Pendaftaran Anda SUDAH '
                    . 'tersimpan dan kursinya ditahan — silakan hubungi panitia lewat '
                    . 'WhatsApp untuk menyelesaikan pembayarannya.');
        }

        $pendaftaran->forceFill([
            'bayar_nomor' => $nomorTagihan,
            'bayar_rujukan' => $hasil['rujukan'],

            // Diwajibkan Cancel Order API DOKU untuk menutup tagihan ini
            // kalau pendaftarannya dibatalkan sebelum tenggatnya habis.
            'bayar_permintaan_id' => $hasil['permintaan_id'],

            /*
             * Alamat halaman bayarnya DISIMPAN, bukan dipakai sekali lalu
             * dibuang. Yang menutup tabnya — atau membuka tautan di emailnya
             * nanti — kalau tidak begini mendarat di halaman status tanpa
             * satu pun tombol untuk membayar.
             */
            'bayar_url' => $hasil['url'],

            'bayar_status' => 'menunggu',
        ])->save();

        return redirect()->away($hasil['url']);
    }

    /**
     * Halaman keadaan satu pendaftaran; tautannya ikut di surat buktinya.
     *
     * Satu-satunya jalan masuk adalah tautan ber-UUID. Tidak ada daftar dan
     * tidak ada pencarian: nomor urut akan membuat pendaftaran orang lain
     * bisa dibuka cukup dengan menebak.
     */
    public function status(string $id)
    {
        $pendaftaran = PendaftaranScopusCamp::whereKey($id)->first();

        if ($pendaftaran === null) {
            return redirect()->route('public.scopuscamp.index')
                ->with('error', 'Pendaftaran itu tidak ditemukan.');
        }

        $pendaftaran = $this->susulkanDariGerbang($pendaftaran);

        /*
         * Peserta rombongannya dimuat sekaligus.
         *
         * Halaman ini satu-satunya tempat pendaftar bisa MEMERIKSA siapa saja
         * yang ia daftarkan. Tanpa itu, salah ketik nama baru ketahuan di
         * sertifikat — saat sudah terlambat diperbaiki.
         */
        $pendaftaran->load('pesertaLain');

        $kategori = CategoriesScopusCamp::find($pendaftaran->kategori_id);

        return view('public.scopus_camp.status', [
            'pendaftaran' => $pendaftaran,
            'kategori' => $kategori,

            /*
             * Alasan angkatannya tertutup, atau null kalau masih terbuka.
             *
             * Dihitung DI SINI, bukan di Blade: aturannya tiga butir —
             * status, tanggal mulai, dan sisa kursi — dan ia sudah dipakai
             * borang pendaftaran serta bayarUlang(). Salinan keempatnya di
             * Blade akan berselisih pada butir yang terlupa, dan yang
             * terlihat orangnya adalah tombol "Buat tagihan baru" yang
             * ditolak peladennya sendiri.
             */
            'tertutup' => $kategori === null
                ? 'Angkatan pendaftaran ini sudah tidak ada.'
                : $this->alasanTertutup($kategori),

            // Tombol "Buat tagihan baru" tidak digambar kalau gerbangnya
            // memang belum dipasang di peladen ini.
            'gerbangSiap' => $this->doku->siap(),

            'menitBayar' => Doku::MENIT_KEDALUWARSA,
        ]);
    }

    /**
     * Keadaan ringkas satu pendaftaran, untuk ditanyakan berulang halamannya.
     *
     * Yang dipulangkan HANYA keadaannya — tidak ada nama, email, maupun
     * nominal. Alamat ini tidak memakai sesi, jadi siapa pun yang memegang
     * tautannya bisa memanggilnya, dan semua yang ada di sini memang sudah
     * terlihat di halamannya sendiri.
     */
    public function keadaan(string $id)
    {
        $pendaftaran = PendaftaranScopusCamp::whereKey($id)->first();

        if ($pendaftaran === null) {
            return response()->json(['ada' => false], 404);
        }

        $pendaftaran = $this->susulkanDariGerbang($pendaftaran);

        $lunas = $this->sudahLunas($pendaftaran);

        return response()->json([
            'ada' => true,
            'lunas' => $lunas,

            /*
             * "selesai" berarti halamannya TIDAK perlu bertanya lagi.
             * Dihitung di sini, bukan di peramban: aturannya hidup di satu
             * tempat, dan menyalinnya ke JavaScript berarti satu salinan
             * lagi yang harus ikut diubah tiap kali.
             */
            /*
             * 'selesai' juga saat tagihannya GAGAL atau KEDALUWARSA.
             *
             * Tanpa itu pollernya menanyai keadaan yang tidak akan pernah
             * berubah sendiri — tagihan mati tidak akan dibayar siapa pun —
             * dan tiap tab yang terbuka menambah satu permintaan tiap
             * beberapa detik. Di hosting ini tumpukan poller pernah
             * menghabiskan kuota prosesnya dan berakhir 503.
             *
             * Yang mengubahnya tindakan orangnya sendiri: menekan "Buat
             * tagihan baru", yang memuat ulang halaman ini.
             */
            'selesai' => $lunas
                || in_array((string) $pendaftaran->status, [
                    'Pendaftaran Dibatalkan', 'Pendaftaran Ditolak', 'Pendaftaran Refund',
                ], true)
                || in_array((string) $pendaftaran->bayar_status, [
                    'failed', 'cancel', 'canceled', 'gagal', 'gagal-dibuat', 'expired',
                ], true),
        ]);
    }

    /** Pendaftaran ini sudah dibayar. */
    private function sudahLunas(PendaftaranScopusCamp $pendaftaran): bool
    {
        return in_array(
            (string) $pendaftaran->status,
            \App\Support\PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'],
            true
        );
    }

    /**
     * Menanyakan LANGSUNG ke DOKU, lalu menyusulkan statusnya sendiri.
     *
     * Pemberitahuan dari DOKU tetap jalan dan tetap jadi pegangan utama — ia
     * yang datang walau halamannya sudah ditutup. Tetapi ia berjalan dari
     * peladen ke peladen dan hampir selalu kalah cepat dari peramban yang
     * cuma berpindah halaman; di mesin pengembang ia tidak pernah sampai
     * sama sekali, sebab `localhost` tidak bisa dihubungi dari luar.
     *
     * Jadi selama pembayarnya masih menatap layarnya, KITA yang bertanya.
     */
    private function susulkanDariGerbang(PendaftaranScopusCamp $pendaftaran): PendaftaranScopusCamp
    {
        // Yang transfer manual tidak punya tagihan di sana untuk ditanyakan.
        if ($pendaftaran->cara_bayar !== 'doku' || $this->sudahLunas($pendaftaran)) {
            return $pendaftaran;
        }

        /*
         * Yang ditanyakan nomor TAGIHANNYA, bukan nomor pendaftarannya.
         * Sesudah tagihannya dibuat ulang keduanya berbeda, dan menanyakan
         * nomor pendaftaran memulangkan keadaan tagihan LAMA — yang memang
         * gagal — sehingga pembayaran yang baru saja berhasil tidak pernah
         * terbaca.
         */
        $kabar = $this->doku->periksaStatus(
            (string) ($pendaftaran->bayar_nomor ?: $pendaftaran->id_transaksi)
        );

        // tahu=false berarti DOKU tidak menjawab atau belum mengenal
        // nomornya — BUKAN berarti belum dibayar.
        if (! $kabar['tahu']) {
            return $pendaftaran;
        }

        /*
         * KEGAGALANNYA IKUT DICATAT, bukan dibuang.
         *
         * Sampai 25 Okt 2026 cabang ini memulangkan barisnya apa adanya
         * untuk semua jawaban selain lunas — jadi jawaban "failed" yang
         * sudah ada di tangan dibuang, dan halaman statusnya tetap berbunyi
         * "Tinggal satu langkah lagi" lengkap dengan tombol bayar yang
         * mengantar ke tagihan mati. Terukur begitu: pendaftaran
         * ber-bayar_status 'failed' digambar sebagai menunggu pembayaran.
         *
         * Statusnya TIDAK diturunkan. Gagal di gerbang hanya berarti
         * tagihannya mati; kursinya masih ditahan sampai tenggatnya lewat,
         * dan 'Pendaftaran Dibatalkan' di layanan ini keputusan panitia yang
         * menyeret surat pembatalan ke pendaftarnya.
         *
         * Ditulis hanya kalau nilainya BERUBAH: halaman ini ditanyai berkala
         * oleh pollernya sendiri, dan menyimpan baris yang sama tiap
         * beberapa detik membebani basis data tanpa mengubah apa pun.
         */
        if ($kabar['gagal']) {
            $baru = (string) $kabar['status'];

            if ($baru !== '' && $baru !== (string) $pendaftaran->bayar_status) {
                $pendaftaran->forceFill(['bayar_status' => $baru])->save();
            }

            return $pendaftaran;
        }

        if (! $kabar['lunas']) {
            return $pendaftaran;
        }

        /*
         * Pembayaran yang LEBIH TUA daripada pendaftarannya TIDAK diterima.
         *
         * Nomor tagihan bisa terpakai ulang, dan gerbang akan menjawab
         * SUCCESS untuk nomor itu sebab memang pernah dibayar — yang
         * membayar pendahulunya. Tanpa penjagaan ini pendaftaran baru
         * tertandai lunas tanpa ada uang masuk, dan pendaftarnya menerima
         * surat "pendaftaran Anda diterima".
         *
         * Sepuluh menit kelonggaran terhadap selisih jam antara peladen kita
         * dan peladen gerbang; pembayaran yang sah selalu terjadi SESUDAH
         * pendaftarannya.
         */
        if ($kabar['waktu'] !== null
            && $pendaftaran->created_at !== null
            && $kabar['waktu']->lt($pendaftaran->created_at->copy()->subMinutes(10))) {
            Log::warning('Pembayaran di gerbang lebih tua daripada pendaftarannya; tidak diwariskan', [
                'nomor' => $pendaftaran->id_transaksi,
                'bayar_pada' => (string) $kabar['waktu'],
                'pendaftaran_dibuat' => (string) $pendaftaran->created_at,
            ]);

            return $pendaftaran;
        }

        $this->tandaiLunas($pendaftaran, (string) $kabar['status'], $kabar['kanal']);

        return $pendaftaran->refresh();
    }

    /**
     * Menandai satu pendaftaran lunas, dari jalur mana pun kabarnya datang.
     *
     * SATU tempat untuk dua jalur — yang bertanya sendiri dan yang
     * diberitahu DOKU. Mana pun yang tiba lebih dulu, hasilnya tidak boleh
     * bisa dibedakan: kolom yang sama terisi dan surat yang sama berangkat.
     */
    private function tandaiLunas(PendaftaranScopusCamp $pendaftaran, string $status, ?string $kanal): void
    {
        /*
         * 'Pendaftaran Diterima', bukan status baru.
         *
         * Itulah kata yang sudah dipakai layanan ini untuk "uangnya sudah
         * masuk, kursinya milik Anda" — dibaca layar panitia sebagai Lunas,
         * dan surat penerimaannya menempel padanya. Menambah kata baru
         * berarti pendaftaran yang membayar lewat DOKU tidak terhitung di
         * satu pun ringkasan yang sudah ada.
         */
        $pendaftaran->forceFill([
                    'status' => 'Pendaftaran Diterima',
            'bayar_status' => $status !== '' ? $status : 'success',
            'bayar_pada' => now(),

            // Kanal yang BENAR-BENAR dipakai — VA Mandiri, QRIS, OVO.
            // Tanpa ini layar panitia cuma bisa berkata "DOKU".
            'bayar_kanal' => $kanal ?: $pendaftaran->bayar_kanal,
        ])->save();

        \App\PendaftaranJejak::create([
            'layanan' => 'scopus_camp',
            'pendaftaran_id' => (string) $pendaftaran->getKey(),
            'aksi' => 'status',
            'dari' => 'diproses',
            'ke' => 'Pendaftaran Diterima',
            'ringkasan' => 'Dibayar lewat ' . ($kanal ?: 'DOKU') . '; ditandai lunas oleh sistem',
        ]);

        /*
         * Surat penerimaannya dikirim lewat jalur yang SAMA dengan yang
         * dipakai panitia, bukan Mail::send sendiri di sini. Jalur itu yang
         * memilih kelas suratnya dari katalog dan mencatat hasil kirimnya di
         * jejak — dan pendaftar yang berkata "saya tidak terima email" hanya
         * bisa dijawab dari catatan itu.
         */
        (new \App\Actions\Pendaftaran\KirimSuratStatus)
            ->jalankan('scopus_camp', $pendaftaran->refresh());

        /*
         * WhatsApp-nya dipanggil DI SINI, bukan lewat UbahStatusPendaftaran.
         *
         * Jalur ini memang tidak melewatinya — aksi itu milik layar panitia,
         * dan memanggilnya dari pemberitahuan gerbang akan ikut menutup
         * tagihan yang baru saja dibayar. Yang dipakai bersama cuma
         * perakit pemberitahuannya.
         *
         * Dibungkus: kegagalan di sini tidak boleh membuat kami menjawab
         * bukan-200 ke DOKU, yang lalu mengulang pemberitahuan pembayaran
         * yang sudah tercatat — dan pengulangannya mengirim surat kedua ke
         * orang yang sama.
         */
        try {
            app(\App\Actions\Pendaftaran\KabariWhatsApp::class)
                ->lunas('scopus_camp', $pendaftaran->refresh());
        } catch (\Throwable $e) {
            Log::warning('Kabar WhatsApp lunas Scopus Camp gagal.', [
                'nomor' => $pendaftaran->id_transaksi,
                'pesan' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Pendaftar membuat sendiri tagihan baru dari halaman statusnya.
     *
     * Sebelum ini tagihan yang gagal atau kedaluwarsa hanya bisa dibuat ulang
     * PANITIA, dengan tangan, setelah dihubungi lewat WhatsApp — dan sampai
     * itu terjadi orangnya memegang tombol bayar yang mengantar ke halaman
     * galat DOKU. Jalur ini yang paling sering dibutuhkan: pembayaran gagal
     * di bank dan tenggat yang terlewat keduanya kejadian sehari-hari.
     *
     * YANG DIPERIKSA DI SINI, dan ketiganya pernah jadi lubang di tempat lain:
     *
     *   1. yang SUDAH lunas tidak boleh membuat tagihan lagi — uangnya sudah
     *      masuk, dan tagihan kedua membuat orangnya membayar dua kali;
     *   2. yang statusnya sudah dihentikan panitia juga tidak — kursinya
     *      sudah dikembalikan ke kuota dan mungkin sudah diambil orang lain;
     *   3. angkatannya harus masih terbuka. Tanpa ini, yang tagihannya mati
     *      setelah acaranya berjalan tetap bisa membayar acara yang sudah
     *      selesai.
     */
    public function bayarUlang(string $id)
    {
        $pendaftaran = PendaftaranScopusCamp::whereKey($id)->first();

        if ($pendaftaran === null) {
            return redirect()->route('public.scopuscamp.index')
                ->with('error', 'Pendaftaran itu tidak ditemukan.');
        }

        if ($this->sudahLunas($pendaftaran)) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Pembayaran untuk pendaftaran ini sudah kami terima.');
        }

        if (in_array((string) $pendaftaran->status, self::STATUS_LEPAS_KURSI, true)) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Pendaftaran ini sudah tidak berjalan, jadi '
                . 'tagihannya tidak bisa dibuat lagi. Silakan mendaftar kembali.');
        }

        $kategori = CategoriesScopusCamp::find($pendaftaran->kategori_id);

        if ($kategori === null) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Angkatan pendaftaran ini sudah tidak ada. '
                . 'Silakan hubungi panitia.');
        }

        $tertutup = $this->alasanTertutup($kategori);

        if ($tertutup !== null) {
            return $this->kembaliKeStatus($pendaftaran, 'error', $tertutup . ' Silakan hubungi panitia lewat WhatsApp.');
        }

        if (! $this->doku->siap()) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Pembayaran daring sedang tidak tersedia. '
                . 'Silakan hubungi panitia lewat WhatsApp.');
        }

        /*
         * Tenggat barunya DISETEL ULANG dari sekarang.
         *
         * Tagihan DOKU-nya sendiri berumur MENIT_KEDALUWARSA sejak dibuat,
         * jadi tenggat lama yang dibiarkan membuat halaman status menyembunyikan
         * tombol bayar untuk tagihan yang justru baru saja hidup — dan
         * perintah sapuan melepas kursinya beberapa menit kemudian.
         */
        $pendaftaran->forceFill([
            'kedaluwarsa_pada' => now()->addMinutes(\App\Services\Doku::MENIT_KEDALUWARSA),
        ])->save();

        return $this->mulaiBayar($pendaftaran, $kategori);
    }

    /**
     * Mengirim ulang surat bukti pendaftarannya.
     *
     * Dipakai yang emailnya salah ketik, yang suratnya masuk spam, dan yang
     * menghapusnya. Tanpa ini satu-satunya jalan mendapatkan nomor
     * pendaftaran dan fakturnya kembali adalah menghubungi panitia.
     *
     * Dibatasi throttle di rutenya: satu penekanan satu surat, dan tombol
     * yang bisa ditekan berulang-ulang adalah pengirim surat massal ke
     * alamat orang lain.
     */
    public function kirimUlang(string $id)
    {
        $pendaftaran = PendaftaranScopusCamp::whereKey($id)->first();

        if ($pendaftaran === null) {
            return redirect()->route('public.scopuscamp.index')
                ->with('error', 'Pendaftaran itu tidak ditemukan.');
        }

        $kategori = CategoriesScopusCamp::find($pendaftaran->kategori_id);

        if ($kategori === null) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Angkatan pendaftaran ini sudah tidak ada, '
                . 'jadi suratnya tidak bisa dirakit.');
        }

        $alamat = trim((string) $pendaftaran->email);

        if (! filter_var($alamat, FILTER_VALIDATE_EMAIL)) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Alamat email pendaftaran ini tidak sah, jadi '
                . 'suratnya tidak bisa dikirim. Silakan hubungi panitia.');
        }

        try {
            Mail::to($alamat)->send(
                new ScopusCampMail($pendaftaran, $kategori, 'Rumah Scopus Foundation')
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->kembaliKeStatus($pendaftaran, 'error', 'Suratnya gagal dikirim. Coba lagi beberapa saat.');
        }

        \App\PendaftaranJejak::create([
            'layanan' => 'scopus_camp',
            'pendaftaran_id' => (string) $pendaftaran->getKey(),
            'aksi' => 'surat',
            'dari' => (string) $pendaftaran->status,
            'ke' => $alamat,
            'oleh_nama' => 'pendaftar sendiri',
        ]);

        return $this->kembaliKeStatus($pendaftaran, 'success', 'Bukti pendaftaran dikirim ulang ke '
            . $this->samarkanEmail($alamat)
            . '. Kalau belum masuk dalam beberapa menit, periksa folder spam.');
    }

    /**
     * Faktur atau kwitansi pendaftarannya, sebagai PDF.
     *
     * Halaman status ini dirancang SEBAGAI kwitansi — nomor di puncak, deret
     * label-nilai, nominal di kaki — tetapi tidak ada yang bisa diunduh dari
     * sana. Berkasnya sampai sekarang hanya hidup sebagai lampiran surat, jadi
     * yang suratnya tidak sampai tidak punya faktur sama sekali, dan bagian
     * keuangan kampusnya memintanya.
     *
     * KWITANSI HANYA SETELAH LUNAS. Kwitansi menyatakan uangnya sudah
     * diterima; diberikan sebelum itu, ia jadi surat palsu yang kami tanda
     * tangani sendiri — dan bisa dipakai menagih balik lembaga ini.
     */
    public function dokumen(string $id, string $jenis)
    {
        if (! in_array($jenis, ['faktur', 'kwitansi'], true)) {
            abort(404);
        }

        $pendaftaran = PendaftaranScopusCamp::whereKey($id)->first();

        if ($pendaftaran === null) {
            return redirect()->route('public.scopuscamp.index')
                ->with('error', 'Pendaftaran itu tidak ditemukan.');
        }

        if ($jenis === 'kwitansi' && ! $this->sudahLunas($pendaftaran)) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Kwitansinya baru bisa diunduh setelah '
                . 'pembayarannya kami terima.');
        }

        $kategori = CategoriesScopusCamp::find($pendaftaran->kategori_id);

        if ($kategori === null) {
            return $this->kembaliKeStatus($pendaftaran, 'error', 'Angkatan pendaftaran ini sudah tidak ada, '
                . 'jadi dokumennya tidak bisa dirakit.');
        }

        try {
            $berkas = \App\Support\LampiranDokumen::rakit(
                'scopus_camp', $jenis, $pendaftaran, $kategori
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->kembaliKeStatus($pendaftaran, 'error', 'Dokumennya gagal dibuat. Coba lagi beberapa saat.');
        }

        /*
         * Dikirim sebagai unduhan, bukan ditampilkan di tab.
         *
         * Yang membukanya sebagian dari ponsel, dan PDF yang terbuka di tab
         * peramban ponsel sering tidak punya tombol simpan yang terlihat —
         * orangnya mengira berkasnya tidak pernah ia dapat.
         */
        return response($berkas['isi'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $berkas['nama'] . '"',
        ]);
    }

    /**
     * Mengirimkan tautan halaman status ke email pendaftarnya.
     *
     * Untuk yang kehilangan tautannya: alamat halaman status memuat UUID yang
     * tidak bisa ditebak, jadi yang menutup tabnya dan tidak menerima
     * suratnya kehilangan pendaftarannya untuk selamanya — termasuk tombol
     * bayarnya.
     *
     * DIKIRIM KE EMAIL, tidak ditampilkan di layar. Menampilkannya berarti
     * siapa pun yang menebak alamat email orang lain bisa membuka
     * pendaftarannya: di halaman itu ada nama, nomor telepon, afiliasi, dan
     * daftar pesertanya.
     *
     * JAWABANNYA SELALU SAMA, terdaftar maupun tidak. Jawaban yang berbeda
     * mengubah borang ini jadi alat memeriksa apakah satu alamat email pernah
     * mendaftar di sini.
     */
    public function tautanSaya(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:191'],
        ], [
            'email.required' => 'Alamat emailnya belum diisi.',
            'email.email' => 'Alamat emailnya belum benar.',
        ]);

        $jawaban = 'Kalau alamat itu memang terdaftar, tautan pendaftarannya sudah kami '
            . 'kirimkan ke sana. Periksa juga folder spam.';

        $pendaftaran = PendaftaranScopusCamp::where('email', $data['email'])
            ->orderByDesc('created_at')

            // Pemutus seri: pendaftaran yang dibuat dalam detik yang sama
            // tidak punya urutan yang ditentukan tanpa kunci kedua.
            ->orderByDesc('id')
            ->take(10)
            ->get();

        if ($pendaftaran->isEmpty()) {
            return back()->with('success', $jawaban);
        }

        try {
            Mail::to($data['email'])->send(
                new \App\Mail\TautanPendaftaranScopusCampMail($pendaftaran)
            );
        } catch (\Throwable $e) {
            report($e);

            // Jawabannya tetap sama: kegagalan surat yang disebutkan di sini
            // pun membocorkan bahwa alamatnya memang terdaftar.
            return back()->with('success', $jawaban);
        }

        return back()->with('success', $jawaban);
    }

    /**
     * Kembali ke halaman status pendaftaran ini, membawa pesannya.
     *
     * BUKAN back(). Ketiga jalur di atas bisa dibuka langsung dari alamatnya
     * — tautan di surat, tautan yang disalin, tombol yang ditekan ulang
     * sesudah tab lain ditutup — dan permintaan tanpa kepala Referer membuat
     * back() jatuh ke halaman muka situs. Terukur begitu: menolak unduhan
     * kwitansi yang belum lunas mengantar orangnya ke / dengan pesan yang
     * tidak digambar halaman itu, jadi ia tidak pernah tahu kenapa.
     */
    private function kembaliKeStatus(PendaftaranScopusCamp $pendaftaran, string $kunci, string $pesan)
    {
        return redirect()
            ->route('public.scopuscamp.status', $pendaftaran->getKey())
            ->with($kunci, $pesan);
    }

    /** Alamat email yang disebutkan kembali ke pengirimnya, sebagian ditutup. */
    private function samarkanEmail(string $email): string
    {
        [$nama, $ranah] = array_pad(explode('@', $email, 2), 2, '');

        $tampak = mb_substr($nama, 0, min(2, max(1, mb_strlen($nama) - 1)));

        return $tampak . str_repeat('*', max(1, mb_strlen($nama) - mb_strlen($tampak)))
            . ($ranah !== '' ? '@' . $ranah : '');
    }

    /**
     * Pemberitahuan balik dari DOKU bahwa sebuah tagihan sudah dibayar.
     *
     * Dikecualikan dari CSRF karena yang mengirim peladen DOKU, bukan
     * peramban pendaftarnya. Penggantinya BUKAN tidak ada penjagaan: tanda
     * tangannya diperiksa lebih dulu, dan tanpa itu siapa pun yang tahu
     * alamat ini bisa menandai pendaftaran mana pun sebagai lunas hanya
     * dengan satu permintaan POST.
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
            Log::warning('Pemberitahuan DOKU Scopus Camp ditolak: tanda tangannya tidak cocok', [
                'nomor' => $request->input('order.invoice_number'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => false], 401);
        }

        $nomor = (string) $request->input('order.invoice_number');
        $status = mb_strtolower((string) $request->input('transaction.status'));

        /*
         * Dicari lewat KEDUA kolom. Nomor tagihan percobaan kedua dan
         * seterusnya berakhiran -R2, -R3 dan hanya tersimpan di
         * `bayar_nomor`; dicari lewat `id_transaksi` saja, pembayaran yang
         * sudah masuk tidak menemukan barisnya dan uangnya tidak tercatat.
         */
        $pendaftaran = PendaftaranScopusCamp::where('id_transaksi', $nomor)
            ->orWhere('bayar_nomor', $nomor)
            ->first();

        if ($pendaftaran === null) {
            Log::warning('DOKU memberi tahu nomor Scopus Camp yang tidak dikenal', ['nomor' => $nomor]);

            // 200, bukan galat: nomornya memang tidak ada di sini, dan
            // menjawab galat membuat DOKU mengulang pemberitahuan yang sama
            // berhari-hari.
            return response()->json(['ok' => true]);
        }

        if (! in_array($status, ['success', 'settlement'], true)) {
            // Gagal dan kedaluwarsa cuma dicatat di kolom tagihannya.
            // Statusnya TIDAK diturunkan: di layanan ini 'Pendaftaran
            // Dibatalkan' adalah keputusan panitia, bukan keputusan gerbang,
            // dan ia menyeret surat pembatalan ke pendaftarnya.
            $pendaftaran->forceFill(['bayar_status' => $status ?: 'gagal'])->save();

            return response()->json(['ok' => true]);
        }

        /*
         * Yang sudah lunas TIDAK diproses lagi. DOKU boleh mengirim
         * pemberitahuan yang sama lebih dari sekali, dan dua kali proses
         * berarti dua kali surat ke orang yang sama.
         */
        if ($this->sudahLunas($pendaftaran)) {
            return response()->json(['ok' => true]);
        }

        $this->tandaiLunas($pendaftaran, $status, $this->doku->namaKanal($request->all()));

        return response()->json(['ok' => true]);
    }

    // <!--================== END ==================-->

}
