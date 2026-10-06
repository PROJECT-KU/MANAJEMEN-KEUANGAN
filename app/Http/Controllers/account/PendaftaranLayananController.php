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
     * Folder bukti transfer termin, di bawah public/.
     *
     * Folder tersendiri, bukan folder bukti salah satu layanan: satu termin
     * milik PESANAN, bukan milik satu pendaftaran — dan pesanan lembaga bisa
     * memuat dua layanan berbeda sekaligus.
     */
    public const FOLDER_BUKTI_TERMIN = 'bukti-termin';

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

    /**
     * Nama medan saringan di alamat halaman, SELURUHNYA.
     *
     * Daftar ini yang dibawa serta oleh tombol unduh, kepala kolom pengurut,
     * dan penomoran halaman — jadi ia menentukan apa yang masih terpasang
     * sesudah orangnya menekan salah satu dari ketiganya.
     *
     * Ditaruh di sini, tepat di atas bacaPilihan() yang membaca medan-medan
     * ini, karena kedua tempat itu WAJIB sepakat dan sebelumnya tidak.
     * Daftarnya dulu ditulis ulang di dalam markah halaman daftar, dan saat
     * saringan cara bayar ditambahkan, daftar di markah itu tidak ikut
     * diperbarui. Akibatnya: menyaring "Transfer bank" lalu menekan Unduh
     * Excel memulangkan SELURUH cara bayar, dan kepala berkasnya pun tidak
     * menyebut "Cara bayar" — jadi tidak ada satu pun petunjuk bahwa
     * saringannya tertinggal. Kegagalan yang paling buruk bentuknya: tidak
     * ada galat, berkasnya terunduh, hanya isinya yang bukan yang diminta.
     *
     * 'urut' dan 'arah' TIDAK di sini. Keduanya bukan saringan, dan kepala
     * kolom pengurut justru harus boleh menimpanya.
     */
    private const MEDAN_SARINGAN = [
        'cari', 'layanan', 'keadaan', 'bukti', 'bayar',
        'angkatan', 'dari', 'sampai', 'lama',
    ];

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
            /*
             * HANYA angkatan aktif. Kuerinya dulu hanya menyaring tanggal,
             * jadi angkatan berstatus 'draft' atau 'nonactive' ikut ditawarkan
             * ke panitia — angkatan yang sengaja belum dibuka sudah bisa diisi
             * pendaftar. Belum terjadi (ketujuhnya kebetulan aktif), dan
             * ditutup sebelum terjadi.
             */
            ->where('status', 'active')
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
            'varian' => $this->varianLayanan(array_keys($katalog)),
            'tarif' => $this->tarifLayanan(array_keys($katalog)),
            /*
             * Angkatan yang sudah terpilih sepulang dari "simpan & tambah
             * lagi". Tanpa ini, mendaftarkan rombongan ke satu angkatan tetap
             * menuntut memilih angkatannya lagi tiap orang — persis pekerjaan
             * yang hendak dihemat tombol itu.
             */
            'terpilihAngkatan' => (string) $request->input('kategori', ''),
            /*
             * Pesanan lembaga yang masih terbuka. Yang sudah selesai ditagih
             * tidak ditawarkan: menambahkan kursi ke pesanan yang fakturnya
             * sudah keluar membuat fakturnya berbohong.
             */
            'pemesanan' => \App\PemesananLembaga::where('status', \App\PemesananLembaga::TERBUKA)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'kode', 'nama_lembaga']),
            'terpilihPemesanan' => (string) $request->input('pemesanan', ''),
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
    /**
     * Pesan kalau orang ini SUDAH terdaftar, atau null kalau belum.
     *
     * Sebelumnya tidak ada pemeriksaan sama sekali: orang yang sama bisa
     * didaftarkan dua kali ke angkatan yang sama tanpa peringatan, dan
     * kuotanya ikut berkurang dua kali.
     *
     * Dicocokkan lewat email ATAU nomor telepon, sebab satu orang sering
     * memberi email berbeda saat ditanya dua kali, dan nomornya diadu dalam
     * bentuk ANGKA SAJA — "0816-0000-1234" dan "+62 816 0000 1234" orang yang
     * sama, dan hanya cocok sesudah pemisahnya dibuang.
     *
     * Dibatasi ke angkatan yang sama untuk layanan berangkatan: orang yang
     * sama memang boleh ikut angkatan Oktober dan November sekaligus.
     */
    private function pendaftarGanda(string $layanan, Request $request): ?string
    {
        $sumber = Pendaftaran::sumber($layanan);
        $kolom = $sumber['kolom'] ?? [];

        if (! isset($kolom['email'], $kolom['telp'])) {
            return null;
        }

        $email = trim((string) $request->input('email'));
        $kategori = (string) $request->input('kategori_id');

        /*
         * DUA bentuk nomor diadu sekaligus.
         *
         * Baris baru disimpan dalam bentuk WhatsApp (62…), sedangkan ratusan
         * baris lama tersimpan apa adanya (08…). Mengadu satu bentuk saja
         * berarti separuh riwayatnya tidak pernah ketemu.
         */
        $bentuk = array_values(array_unique(array_filter([
            $this->telpBersih((string) $request->input('telp')),
            $this->telpWa((string) $request->input('telp')),
        ])));

        $kueri = DB::table($sumber['tabel'])
            ->when(
                ($sumber['berangkatan'] ?? false) && $kategori !== '' && isset($kolom['angkatan_id']),
                fn ($q) => $q->where($kolom['angkatan_id'], $kategori)
            )
            ->where(function ($q) use ($kolom, $email, $bentuk) {
                /*
                 * Email kosong TIDAK diadu. Sejak email jadi tidak wajib,
                 * mengadunya berarti setiap pendaftar tanpa email dianggap
                 * kembaran pendaftar tanpa email sebelumnya.
                 */
                if ($email !== '') {
                    $q->orWhere($kolom['email'], $email);
                }

                foreach ($bentuk as $nomor) {
                    /*
                     * Pemisahnya dibuang di SISI SQL, bukan dengan LIKE:
                     * nomor tersimpan apa adanya dengan spasi, tanda hubung,
                     * dan kurung yang berbeda-beda, jadi pembandingan
                     * untaian mentah hampir selalu meleset.
                     */
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(" . $kolom['telp']
                            . ", ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') = ?",
                        [$nomor]
                    );
                }
            });

        if ($email === '' && $bentuk === []) {
            return null;
        }

        $ada = $kueri->first([$kolom['nomor'] ?? 'id', $kolom['nama_orang']]);

        if ($ada === null) {
            return null;
        }

        $nomor = $ada->{$kolom['nomor'] ?? 'id'} ?? '-';

        return 'Orang ini sepertinya sudah terdaftar — ' . $ada->{$kolom['nama_orang']}
            . ' (' . $nomor . ')'
            . (($sumber['berangkatan'] ?? false) ? ' di angkatan yang sama' : '')
            . '. Periksa dulu di daftar; kalau memang orang yang berbeda, '
            . 'ubah salah satu email atau nomornya.';
    }

    /**
     * Daftar varian tiap layanan, untuk layanan yang memilihnya sendiri.
     *
     * @param  array<int, string>  $layanan
     * @return array<string, array<string, string>>
     */
    private function varianLayanan(array $layanan): array
    {
        $katalog = \App\Layanan::katalog();
        $keluar = [];

        foreach ($layanan as $kode) {
            $keluar[$kode] = $katalog[$kode]['varian'] ?: [];
        }

        return $keluar;
    }

    /**
     * Harga tiap tarif aktif, berkunci "layanan|varian".
     *
     * Dipakai borang untuk mengisi sendiri nominal layanan TANPA angkatan:
     * harganya sudah disetel di Tarif Layanan, dan menyuruh panitia
     * mengetiknya lagi berarti dua tempat yang bisa berselisih.
     *
     * @param  array<int, string>  $layanan
     * @return array<string, int>
     */
    private function tarifLayanan(array $layanan): array
    {
        return \App\ClinikScopusBiayaPersesi::query()
            ->where('status', \App\ClinikScopusBiayaPersesi::AKTIF)
            ->whereIn('layanan', $layanan)
            ->get(['layanan', 'varian', 'biaya_persesi'])
            ->mapWithKeys(fn ($t) => [
                $t->layanan . '|' . ($t->varian ?? '') => (int) $t->biaya_persesi,
            ])
            ->all();
    }

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
            /*
             * TIDAK wajib, dan itu disengaja.
             *
             * Yang datang langsung sering tidak punya email, dan mewajibkannya
             * memaksa panitia mengarang alamat — lalu alamat karangan itu
             * dikirimi surat. Nomor WhatsApp-nya yang tetap wajib; itu yang
             * benar-benar dipakai menghubungi orangnya.
             *
             * 'email' saja, bukan 'email:rfc,dns': sebagian domain pendaftar
             * tidak bisa dicari dari peladen ini, dan menolaknya membuat orang
             * yang sah tidak bisa didaftarkan sama sekali.
             */
            'email' => ['nullable', 'email', 'max:255'],
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
            // Daftar variannya hidup di `layanan.varian`, jadi nilainya
            // dicocokkan di lapisan tindakan — di sini cukup bentuknya.
            'varian' => ['nullable', 'string', 'max:40'],
            /*
             * Bukti bayar boleh langsung diunggah dari sini. Sebelumnya tidak
             * bisa sama sekali: pendaftar yang datang membawa struk harus
             * disimpan dulu, lalu buktinya diunggah dari halaman rincian.
             *
             * 4 MB, dan batasnya disebut di layar: berkas dari kamera ponsel
             * rutin melewatinya, dan penolakan tanpa angka tidak memberi tahu
             * harus diapakan.
             */
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            // Nama sesi Scopus Kafe; tabelnya memang punya kolomnya.
            'sesi' => ['nullable', 'string', 'max:120'],
            // Satu nama per baris; batas atasnya jumlah yang dibayar.
            'peserta' => ['nullable', 'string', 'max:4000'],
            'lagi' => ['nullable', 'boolean'],
            // Kabar ke pendaftarnya: dipilih, bukan selalu. Orang yang
            // mendaftar lewat WhatsApp sering memberi email asal-asalan, dan
            // surat ke alamat karangan hanya menambah laporan gagal kirim.
            'kabari' => ['nullable', 'boolean'],
            /*
             * Pesanan lembaga: boleh menunjuk yang sudah ada, atau membuat
             * baru dengan mengisi namanya. Keduanya tidak wajib — pendaftar
             * perorangan tetap jalur utamanya.
             */
            /*
             * Pertanyaan pertama borangnya, dan penentu sisanya. Dikirim juga
             * ke peladen supaya aturan "nama lembaga wajib" bisa dinyatakan —
             * tanpa jenisnya, peladen tidak bisa membedakan pesanan lembaga
             * yang namanya lupa diisi dari pendaftar perorangan biasa.
             */
            'jenis' => ['nullable', Rule::in(['perorangan', 'lembaga'])],
            'pemesanan_id' => ['nullable', 'string', 'exists:pemesanan_lembaga,id'],
            'lembaga_nama' => [
                'nullable', 'string', 'max:255',
                // Wajib hanya kalau jalurnya lembaga DAN tidak menunjuk
                // pesanan yang sudah ada.
                Rule::requiredIf(fn () => $request->input('jenis') === 'lembaga'
                    && trim((string) $request->input('pemesanan_id')) === ''),
            ],
            'lembaga_alamat' => ['nullable', 'string', 'max:1000'],
            'lembaga_npwp' => ['nullable', 'string', 'max:40'],
            'lembaga_po' => ['nullable', 'string', 'max:60'],
            'lembaga_pic' => ['nullable', 'string', 'max:255'],
            'lembaga_pic_email' => ['nullable', 'email', 'max:255'],
            'lembaga_pic_telp' => ['nullable', 'string', 'max:40'],
            'abaikan_ganda' => ['nullable', 'boolean'],
        ];

        $layanan = (string) $request->input('layanan');

        /*
         * Sesi dicocokkan ke daftar milik VARIAN yang dipilih.
         *
         * Dulu kotak teks bebas, jadi satu sesi yang sama bisa tersimpan
         * "Sesi 1", "sesi1", atau "pagi" — dan daftar hadir per sesi tidak
         * bisa dikelompokkan dari data yang begitu. Scopus Kafe offline punya
         * dua sesi, online hanya sesi pagi, jadi daftarnya memang bergantung
         * pada variannya.
         *
         * Ditolak di sini, bukan dibuang diam-diam di lapisan tindakan:
         * pendaftaran yang tersimpan TANPA sesi padahal panitia merasa sudah
         * memilihnya adalah kesalahan yang tidak terlihat sampai hari
         * pelaksanaan.
         */
        $sesiBoleh = array_column(
            Pendaftaran::sesiPilihan($layanan, $request->input('varian')),
            'nilai'
        );

        if ($sesiBoleh !== []) {
            $aturan['sesi'] = ['required', Rule::in($sesiBoleh)];
        }

        if (array_key_exists($layanan, $katalog) && $katalog[$layanan]['berangkatan']) {
            $aturan['kategori_id'] = ['required', 'string', 'exists:kategori_layanan,id'];
        } else {
            // Layanan tanpa angkatan tidak punya harga yang bisa diambil
            // sendiri; nominalnya memang harus diketik.
            $aturan['total'] = ['required', 'string'];
        }

        $request->validate($aturan, [
            'sesi.required' => 'Sesinya belum dipilih.',
            'sesi.in' => 'Sesi itu tidak ada pada varian yang dipilih.',
        ], [
            'kategori_id' => 'angkatan',
            'telp' => 'nomor WhatsApp',
            'total' => 'total bayar',
            'cara_bayar' => 'cara bayar',
            'bukti' => 'bukti bayar',
            'lembaga_nama' => 'nama lembaga',
        ]);

        /*
         * Pemeriksaan ganda SESUDAH validasi bentuk, dan bisa dilewati sekali.
         *
         * Bukan larangan keras: dua orang berbeda bisa saja berbagi satu
         * nomor WhatsApp keluarga, dan panitia yang tahu itu harus tetap bisa
         * melanjutkan. Jadi kirim pertama ditahan dengan keterangan, dan
         * tombol "tetap simpan" mengirim ulang dengan penanda ini.
         */
        if (! $request->boolean('abaikan_ganda')) {
            $ganda = $this->pendaftarGanda($layanan, $request);

            if ($ganda !== null) {
                return back()->withInput()
                    ->withErrors(['email' => $ganda])
                    ->with('ganda', true);
            }
        }

        /*
         * Nomor teleponnya DINORMALKAN sebelum disimpan.
         *
         * Tanpa itu satu kolom berisi "0816-0000-1234", "+62 816 0000 1234",
         * dan "0816 0000 1234" berdampingan — pencarian meleset, dan tautan
         * WhatsApp yang dirakit dari nilai begitu gagal terbuka.
         */
        $isian = $request->all();
        $isian['telp'] = $this->telpWa((string) $request->input('telp'));

        /*
         * Pesanan lembaga BARU dibuat lebih dulu, sebelum pendaftarannya,
         * supaya idnya sudah ada saat barisnya diikat. Dibuat hanya kalau
         * namanya diisi dan tidak ada pesanan lama yang dipilih.
         */
        $namaLembaga = trim((string) $request->input('lembaga_nama'));

        // $request->all() tidak memuat kunci yang tidak dikirim sama sekali —
        // dan kiriman tanpa bagian lembaga memang tidak mengirimnya.
        $isian['pemesanan_id'] = trim((string) $request->input('pemesanan_id', '')) ?: null;

        /*
         * Pesanan lembaga hanya dibuat kalau jalurnya memang lembaga.
         *
         * Tanpa syarat ini, nama lembaga yang tertinggal di isian tersembunyi
         * — misalnya sesudah admin berpindah dari jalur lembaga ke
         * perorangan — tetap membuat pesanan yang tidak pernah dimaksudkan.
         */
        if ($request->input('jenis') === 'lembaga' && $isian['pemesanan_id'] === null && $namaLembaga !== '') {
            $lembaga = \App\PemesananLembaga::create([
                'nama_lembaga' => $namaLembaga,
                'alamat' => $request->input('lembaga_alamat'),
                'npwp' => $request->input('lembaga_npwp'),
                'no_po' => $request->input('lembaga_po'),
                // PIC-nya jatuh ke pendaftarnya kalau tidak disebut sendiri:
                // untuk pesanan kecil keduanya memang orang yang sama.
                'pic_nama' => trim((string) $request->input('lembaga_pic')) ?: (string) $request->input('nama'),
                'pic_email' => $request->input('lembaga_pic_email') ?: $request->input('email'),
                'pic_telp' => $this->telpWa((string) ($request->input('lembaga_pic_telp') ?: $request->input('telp'))),
                'dibuat_oleh' => $this->siapa(),
            ]);

            $isian['pemesanan_id'] = $lembaga->getKey();
        }

        $hasil = (new BuatPendaftaran)->jalankan($layanan, $isian, $this->siapa());

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
        /*
         * "Simpan & tambah lagi" kembali ke borang dengan layanan dan
         * angkatannya sudah terpilih. Mendaftarkan rombongan satu per satu
         * sebelumnya berarti sepuluh kali kembali ke borang dan sepuluh kali
         * memilih ulang layanan serta angkatannya.
         */
        if ($request->boolean('lagi')) {
            return redirect()
                ->route('account.pendaftaran-layanan.baru', array_filter([
                    'layanan' => $layanan,
                    'kategori' => $request->input('kategori_id'),
                    // Pesanan lembaganya ikut terbawa: memesan 30 kursi berarti
                    // beberapa kali menyimpan, dan memilih ulang pesanannya tiap
                    // kali persis pekerjaan yang hendak dihemat tombol ini.
                    'pemesanan' => $isian['pemesanan_id'],
                ]))
                ->with('sukses', $hasil['pesan'] . ' Silakan isi pendaftar berikutnya.');
        }

        /*
         * Pesanan rombongan: disebutkan DI MANA DP-nya dicatat.
         *
         * Tab "Termin & DP" sudah ada di halaman yang sedang dituju, tetapi
         * tidak ada yang tahu ia ada sampai mencarinya — dan yang dicari
         * panitia justru tombol DP di borang pembuatan. Kalimat ini muncul
         * tepat saat halamannya terbuka, dengan tabnya terlihat di layar.
         *
         * Syaratnya diambil dari indukBayar(), bukan ditulis ulang: kalau ia
         * berbeda dari syarat tabnya, kalimat ini menjanjikan tab yang tidak
         * ada.
         *
         * TIDAK ditambahkan ke jalur "simpan & tambah lagi" di atas: jalur itu
         * kembali ke borang pembuatan, dan menunjuk tab yang tidak terlihat di
         * layar sama saja dengan tidak menjawab.
         */
        $pesan = $hasil['pesan'];

        if ($this->indukBayar($layanan, (string) $hasil['model']->getKey()) !== null) {
            $pesan .= ' Ini pesanan rombongan — kalau DP-nya sudah masuk, catat di tab "Termin & DP".';
        }

        return redirect()
            ->route('account.pendaftaran-layanan.rincian', [$layanan, $hasil['model']->getKey()])
            ->with('sukses', $pesan);
    }

    /**
     * Membaca daftar peserta dari berkas Excel/CSV, lalu mengembalikannya
     * sebagai teks untuk ditaruh di kotak isiannya.
     *
     * TIDAK menyimpan apa pun. Hasil bacanya ditulis balik ke kotak teks yang
     * sama supaya panitia MELIHAT apa yang terbaca dan bisa membetulkannya
     * sebelum menekan simpan; langsung masuk ke basis data berarti satu kolom
     * yang salah terbaca baru ketahuan di hari acara.
     *
     * Lembaga mengirim daftar pesertanya sebagai lampiran Excel, bukan
     * diketik di badan pesan. Tanpa jalur ini, rombongan 30 orang berarti 30
     * baris yang disalin satu per satu — pekerjaan yang paling mungkin
     * dilewati panitia, dan begitu dilewati, 29 nomor pesertanya hilang.
     */
    public function bacaPeserta(Request $request)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        /*
         * Jenisnya diperiksa dari NAMA berkasnya, bukan lewat aturan `mimes`.
         * CSV yang dibuat Excel di macOS terkirim sebagai `text/plain`
         * sedangkan yang dari Windows `application/vnd.ms-excel`, dan aturan
         * mimes menolak salah satunya tergantung komputer panitianya —
         * penolakan yang tidak mungkin ia pahami sebab berkasnya memang .csv.
         */
        $request->validate([
            'berkas' => ['required', 'file', 'max:2048'],
        ], [
            'berkas.required' => 'Berkasnya belum dipilih.',
            'berkas.max' => 'Berkasnya lebih dari 2 MB.',
        ]);

        $berkas = $request->file('berkas');
        $jenis = strtolower((string) $berkas->getClientOriginalExtension());

        if (! in_array($jenis, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Berkasnya harus Excel (.xlsx atau .xls) atau CSV.',
            ], 422);
        }

        try {
            // Lembar PERTAMA saja. Berkas lembaga kerap punya lembar lain
            // berisi anggaran atau catatan, dan menggabungkan semuanya
            // memasukkan baris yang bukan orang ke daftar pesertanya.
            /*
             * Jenis pembacanya disebut, tidak dibiarkan ditebak. Berkas
             * unggahan tersimpan dengan nama sementara tanpa akhiran, jadi
             * tebakan dari nama berkas bisa meleset — dan melesetnya berupa
             * galat yang menyebut "Unable to identify file type", kalimat
             * yang tidak memberi tahu panitia harus berbuat apa.
             */
            $pembaca = match ($jenis) {
                'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
                'xls' => \Maatwebsite\Excel\Excel::XLS,
                default => \Maatwebsite\Excel\Excel::CSV,
            };

            $lembar = \Maatwebsite\Excel\Facades\Excel::toArray(new class {}, $berkas, null, $pembaca);
            $baris = $lembar[0] ?? [];
        } catch (\Throwable $e) {
            \Log::error('Daftar peserta gagal dibaca', [
                'nama' => $berkas->getClientOriginalName(),
                'sebab' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'pesan' => 'Berkasnya tidak bisa dibaca. Coba simpan ulang sebagai .xlsx atau .csv.',
            ], 422);
        }

        $orang = \App\Support\DaftarPeserta::dariBaris($baris);

        if ($orang === []) {
            return response()->json([
                'ok' => false,
                'pesan' => 'Tidak ada nama yang terbaca di berkas itu.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'jumlah' => count($orang),
            // Berapa yang BERNOMOR disebut terpisah: daftar yang terbaca
            // namanya saja terlihat berhasil, padahal justru nomornya yang
            // hendak dikumpulkan.
            'bernomor' => count(array_filter($orang, fn ($o) => $o['telp'] !== '')),
            'teks' => \App\Support\DaftarPeserta::sebagaiTeks($orang),
        ]);
    }

    /**
     * Induk pembayaran sebuah pendaftaran, beserta tagihannya — atau null.
     *
     * Dirakit di satu tempat dan dipakai bersama oleh pencatat, penghapus,
     * dan layar rinciannya, supaya ketiganya tidak pernah berbeda pendapat
     * tentang siapa yang menanggung tagihan.
     *
     * @return array{jenis: string, induk_id: string, layanan: string|null, tagihan: int}|null
     */
    private function indukBayar(string $layanan, string $id): ?array
    {
        $baris = Pendaftaran::kueri()->where('layanan', $layanan)->where('id', $id)->first();

        if ($baris === null) {
            return null;
        }

        return \App\PembayaranPendaftaran::indukUntuk(
            $layanan,
            $id,
            \App\PemesananLembaga::untukPendaftaran($layanan, $id),
            max(1, (int) $baris->jumlah),
            (int) $baris->total
        );
    }

    /**
     * Mencatat satu kali uang masuk: DP, cicilan, atau pelunasan.
     *
     * Sebelum ini uang hanya punya dua keadaan — "Menunggu bayar" atau
     * "Lunas" — sehingga lembaga yang sudah mentransfer DP 30% tercatat sama
     * persis dengan yang belum bayar sepeser pun.
     *
     * Hanya untuk pesanan rombongan. Pendaftar satu kursi tetap membayar
     * penuh di muka: membuka cicilan untuk satu orang berarti kursi yang
     * ditahan berbulan-bulan oleh uang yang tidak seberapa.
     */
    public function catatPembayaran(Request $request, string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        if (! array_key_exists($layanan, Pendaftaran::katalog())) {
            abort(404);
        }

        $induk = $this->indukBayar($layanan, $id);

        if ($induk === null) {
            return back()->with('info',
                'Pembayaran bertermin hanya untuk pesanan rombongan atau lembaga. '
                . 'Pendaftaran satu orang dicatat lunas lewat tombol statusnya.');
        }

        $data = $request->validate([
            'nominal' => ['required', 'string', 'max:20'],
            /*
             * Tanggal uang MASUK, dan tidak boleh di masa depan: panitia rutin
             * menyusulkan catatan beberapa hari kemudian, tetapi mencatat uang
             * yang belum masuk berarti sisa tagihan yang salah sampai hari itu
             * tiba.
             */
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'cara_bayar' => ['nullable', Rule::in(array_keys(Pendaftaran::caraBayarPilihan()))],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'nominal.required' => 'Nominalnya belum diisi.',
            'tanggal.required' => 'Tanggal uang masuknya belum diisi.',
            'tanggal.before_or_equal' => 'Tanggalnya tidak boleh di masa depan — '
                . 'yang dicatat di sini uang yang SUDAH masuk.',
            'bukti.max' => 'Bukti transfernya lebih dari 4 MB.',
        ]);

        // Diketik berformat "1.500.000" oleh pemolesnya di layar.
        $nominal = (int) preg_replace('/\D+/', '', $data['nominal']);

        if ($nominal < 1) {
            return back()->withErrors(['nominal' => 'Nominalnya harus lebih dari nol.'])->withInput();
        }

        $pembayaran = \App\PembayaranPendaftaran::create([
            'jenis' => $induk['jenis'],
            'induk_id' => $induk['induk_id'],
            'layanan' => $induk['layanan'],
            'urutan' => \App\PembayaranPendaftaran::urutanBerikut($induk['jenis'], $induk['induk_id']),
            'nominal' => $nominal,
            'tanggal' => $data['tanggal'],
            'cara_bayar' => $data['cara_bayar'] ?? null,
            'catatan' => trim((string) ($data['catatan'] ?? '')) ?: null,
            'dicatat_oleh' => $this->siapa(),
        ]);

        $this->simpanBuktiTermin($pembayaran, $request->file('bukti'));

        $ringkas = \App\PembayaranPendaftaran::ringkas(
            $induk['jenis'], $induk['induk_id'], $induk['tagihan']
        );

        /*
         * Sisanya disebut di kalimat suksesnya, bukan dibiarkan dicari
         * sendiri. Pertanyaan berikutnya SELALU "jadi kurang berapa", dan
         * jawabannya sudah ada di tangan saat kalimat ini dirakit.
         */
        $pesan = 'Pembayaran Rp ' . number_format($nominal, 0, ',', '.') . ' tercatat. ';

        $pesan .= $ringkas['lunas']
            ? 'Tagihannya sudah lunas.'
            : 'Sisa tagihan Rp ' . number_format($ringkas['sisa'], 0, ',', '.') . '.';

        return back()->with('sukses', $pesan);
    }

    /**
     * Menghapus satu catatan pembayaran.
     *
     * Dihapus seutuhnya, bukan ditandai batal: yang dihapus adalah catatan
     * yang salah ketik, dan baris "Rp 15.000.000 (dibatalkan)" yang menetap
     * di kwitansi lembaga menimbulkan pertanyaan yang tidak punya jawaban.
     */
    public function hapusPembayaran(string $pembayaran)
    {
        if (! $this->bolehMenghapus()) {
            return $this->tolak();
        }

        $baris = \App\PembayaranPendaftaran::find($pembayaran);

        if ($baris === null) {
            abort(404);
        }

        $nominal = (int) $baris->nominal;
        $baris->delete();

        return back()->with('sukses',
            'Catatan pembayaran Rp ' . number_format($nominal, 0, ',', '.') . ' dihapus.');
    }

    /**
     * Kwitansi cetak untuk satu pembayaran.
     *
     * Halaman bergaya cetak, bukan PDF — alasannya sama seperti slip dan
     * faktur: membuat PDF di peladen menambah satu kemungkinan gagal,
     * sedangkan Ctrl+P sudah menghasilkan berkas yang bisa dilampirkan ke
     * email lembaganya.
     */
    public function kwitansi(string $pembayaran)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $baris = \App\PembayaranPendaftaran::find($pembayaran);

        if ($baris === null) {
            abort(404);
        }

        $lembaga = null;
        $tagihan = 0;
        $untuk = '';

        if ($baris->jenis === \App\PembayaranPendaftaran::LEMBAGA) {
            $lembaga = \App\PemesananLembaga::find($baris->induk_id);

            if ($lembaga === null) {
                abort(404);
            }

            $tagihan = $lembaga->tagihan();
            $untuk = $lembaga->nama_lembaga;
        } else {
            $induk = Pendaftaran::kueri()
                ->where('layanan', (string) $baris->layanan)
                ->where('id', (string) $baris->induk_id)
                ->first();

            if ($induk === null) {
                abort(404);
            }

            $tagihan = (int) $induk->total;
            $untuk = (string) $induk->nama_orang;
        }

        $semua = \App\PembayaranPendaftaran::milik($baris->jenis, (string) $baris->induk_id)
            ->terurut()->get();

        return view('account.pendaftaran_layanan.kwitansi', [
            'bayar' => $baris,
            'lembaga' => $lembaga,
            'untuk' => $untuk,
            'semua' => $semua,
            'ringkas' => \App\PembayaranPendaftaran::ringkas(
                $baris->jenis, (string) $baris->induk_id, $tagihan
            ),
        ]);
    }

    /**
     * Menyimpan bukti transfer satu termin.
     *
     * Folder tersendiri, bukan folder bukti layanannya: satu termin milik
     * PESANAN, bukan milik satu pendaftaran — dan menaruhnya di folder salah
     * satu layanan membuat buktinya tidak bisa ditemukan lagi begitu pesanan
     * itu memuat dua layanan berbeda.
     */
    private function simpanBuktiTermin(\App\PembayaranPendaftaran $bayar, $berkas): void
    {
        if (! $berkas instanceof \Illuminate\Http\UploadedFile || ! $berkas->isValid()) {
            return;
        }

        try {
            $tujuan = public_path(self::FOLDER_BUKTI_TERMIN);

            if (! is_dir($tujuan)) {
                mkdir($tujuan, 0755, true);
            }

            /*
             * Namanya dirakit sistem, bukan memakai nama asli kiriman. Nama
             * berkas dari ponsel sering memuat spasi dan tanda kutip, dan
             * firewall hosting menolak alamat berapostrof dengan 403 sebelum
             * PHP sempat jalan — buktinya tersimpan tetapi tidak pernah bisa
             * dibuka.
             */
            $nama = 'termin-' . now()->format('Ymd-His') . '-'
                . \Illuminate\Support\Str::random(6) . '.'
                . strtolower($berkas->getClientOriginalExtension() ?: 'jpg');

            $berkas->move($tujuan, $nama);

            $bayar->forceFill(['bukti' => $nama])->save();
        } catch (\Throwable $e) {
            // Gagal simpan bukti TIDAK menggagalkan catatan pembayarannya:
            // angkanya sudah benar dan itu yang dipakai menghitung sisa,
            // sedangkan buktinya bisa diunggah ulang.
            \Log::error('Bukti termin gagal disimpan', [
                'pembayaran' => $bayar->getKey(),
                'sebab' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Halaman rincian satu pendaftaran.
     *
     * Satu halaman untuk kelima layanan, dengan bagian borang yang berbeda
     * per layanan — bukan lima halaman. Kuncinya SELALU dicocokkan ke katalog
     * tertutup lebih dulu: `$layanan` datang dari alamat halaman, dan
     * memakainya mentah berarti membiarkan nama kelas mana pun dipanggil.
     */
    /**
     * Faktur satu pesanan lembaga.
     *
     * Kuota tiap angkatan 20 kursi sedangkan lembaga rutin memesan lebih,
     * jadi pesanannya terpaksa dipecah ke beberapa angkatan. Faktur inilah
     * yang menyatukannya kembali: satu halaman, satu jumlah akhir, satu
     * identitas lembaga beserta NPWP dan nomor surat pesanannya.
     *
     * Halaman bergaya cetak, bukan PDF — alasannya sama seperti slip:
     * membuat PDF di peladen menambah satu kemungkinan gagal, dan Ctrl+P
     * sudah menghasilkan berkas yang bisa dilampirkan ke email.
     */
    public function faktur(string $pemesanan)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        $lembaga = \App\PemesananLembaga::find($pemesanan);

        if ($lembaga === null) {
            abort(404);
        }

        $baris = $lembaga->pendaftaran();

        return view('account.pendaftaran_layanan.faktur', [
            'lembaga' => $lembaga,
            'baris' => $baris,
            'katalog' => Pendaftaran::katalog(),
            'jumlahKursi' => $baris->sum(fn ($b) => max(1, (int) $b->jumlah)),
            'jumlahUang' => $baris->sum(fn ($b) => (int) $b->total),
            // Termin yang sudah masuk. Faktur yang menyebut total tagihan saja
            // sementara lembaganya sudah membayar DP terbaca seperti tagihan
            // yang belum disentuh — dan itu yang dibawa ke rapat anggaran.
            'pembayaran' => $lembaga->pembayaran(),
            'ringkas' => $lembaga->ringkasBayar(),
        ]);
    }

    /**
     * Slip pendaftaran, untuk dicetak dan diberikan ke orangnya.
     *
     * Pendaftar yang datang langsung dan membayar tunai sebelumnya pulang
     * tanpa pegangan apa pun: nomor dan kode uniknya hanya ada di layar
     * panitia dan di email — dan sebagian dari mereka tidak punya email.
     *
     * Halaman biasa dengan gaya cetak, bukan PDF: panitia menekan Ctrl+P di
     * depan orangnya, dan membuat PDF di peladen menambah satu kemungkinan
     * gagal pada pekerjaan yang harus selesai dalam hitungan detik.
     */
    public function slip(string $layanan, string $id)
    {
        if (! $this->bolehMelihat()) {
            return $this->tolak();
        }

        if (! array_key_exists($layanan, Pendaftaran::katalog())) {
            abort(404);
        }

        $pendaftaran = Pendaftaran::temukan($layanan, $id);

        if ($pendaftaran === null) {
            abort(404);
        }

        $baris = Pendaftaran::kueri()->where('layanan', $layanan)->where('id', $id)->first();

        if ($baris === null) {
            abort(404);
        }

        return view('account.pendaftaran_layanan.slip', [
            'layanan' => $layanan,
            'nama' => Pendaftaran::katalog()[$layanan]['nama'],
            'baris' => $baris,
            'angkatan' => Pendaftaran::angkatanBaris($baris),
            'caraBayar' => Pendaftaran::caraBayar($baris->cara_bayar ?? null),
            'keadaan' => Pendaftaran::KEADAAN[Pendaftaran::keadaanDari($baris->status)] ?? null,
            'peserta' => \App\PendaftaranPeserta::milik($layanan, $id)->terurut()->get(['nama', 'telp']),
        ]);
    }

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
        /*
         * HANYA angkatan aktif — ditambah angkatan yang SEDANG dipakai baris
         * ini, walau sudah nonaktif.
         *
         * Memindahkan peserta ke angkatan yang sudah ditutup tidak ada
         * gunanya: ia tidak terpajang, tidak menerima pendaftar, dan
         * acaranya sudah lewat. Daftarnya pun jadi panjang tanpa guna —
         * terukur 59 angkatan, 36 di antaranya tidak aktif.
         *
         * Yang sedang dipakai tetap disertakan, dan itu BUKAN kelonggaran:
         * tanpa itu, pendaftaran di angkatan yang sudah ditutup akan membuka
         * borang dengan pilihan yang tidak memuat nilainya sendiri. Select
         * lalu menampilkan baris pertama seolah itu pilihannya, dan satu
         * tekan Simpan memindahkan pesertanya tanpa ada yang meminta.
         *
         * Borang TAMBAH sudah menyaring begini sejak awal; yang tertinggal
         * cuma borang ubah di halaman ini.
         */
        $angkatan = Pendaftaran::berangkatan($layanan)
            ? \App\KategoriLayanan::where('layanan', $layanan)
                /*
                 * Nilainya dibaca dari MODEL pendaftarannya, bukan dari $baris.
                 * $baris datang dari kueri gabungan yang tidak selalu membawa
                 * kolom kategori_id — dan saat ia kosong, angkatan yang sedang
                 * dipakai ikut hilang dari pilihan tanpa satu pun galat.
                 */
                ->where(function ($q) use ($pendaftaran) {
                    $q->where('status', 'active')
                        ->orWhere('id', (string) ($pendaftaran->kategori_id ?? ''));
                })
                ->orderByDesc('mulai')
                // nama_ke dan mulai ikut dibaca: angkatan Scopus Camp bernama
                // SAMA PERSIS berpuluh-puluh, dan tanpa nomor serta tanggalnya
                // pilihan di daftar tidak bisa dibedakan satu pun.
                ->get(['id', 'nama', 'nama_ke', 'mulai', 'status', 'total_kuota', 'sisa_kuota'])
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
            /*
             * Jejak perubahan, DARI TABELNYA SENDIRI — bukan dari kolom
             * catatan panitia seperti dulu. Lihat migrasi jejak_pendaftaran
             * soal kenapa keduanya dipisah.
             */
            'jejak' => \App\PendaftaranJejak::milik($layanan, (string) $pendaftaran->getKey())
                ->terurut()->get(),
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
            'bayar' => ['jumlah_pendaftar', 'ppn', 'kode_unik',
                'kode_diskon', 'nominal_diskon', 'total_pembayaran',
                'total_keseluruhan_pembayaran'],
            // kategori_id ikut tab Jadwal, mengikuti letak isiannya di layar.
            // Kalau tertinggal di 'bayar', galat angkatan penuh akan membuka
            // tab Pembayaran sementara isiannya ada di tab Jadwal.
            'sesi' => ['kategori_id', 'tanggal_pemesanan', 'sesi', 'jam_sesi', 'waktu_mulai',
                'waktu_selesai', 'lokasi', 'biaya', 'kode_unik_pembayaran',
                'subtotal_pembayaran', 'sesi_kedua', 'sesi_ketiga', 'group_wa'],
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
            /*
             * Berapa saringan LIPAT yang sedang terpasang.
             *
             * Kelimanya jarang dipakai dan dilipat di layar supaya yang
             * tersisa cuma tiga kendali — tetapi saringan aktif yang
             * tersembunyi membuat daftar terlihat kurang isinya tanpa ada yang
             * bisa menjelaskan kenapa. Jumlahnya disebut di tuasnya, dan
             * lipatannya terbuka sendiri kalau lebih dari nol.
             */
            'jumlahSaringanLain' => $jumlahLain = count(array_filter(
                [$bukti, $caraBayar, $dari, $sampai, $angkatan],
                fn ($n) => $n !== '' && $n !== null
            )),
            'adaSaringanLain' => $jumlahLain > 0,
            /*
             * Saringan yang sedang terpasang, siap ditempelkan ke tautan.
             *
             * Dirakit DI SINI, bukan di markah halamannya: ini satu-satunya
             * tempat yang tahu medan apa saja yang dimengerti layar ini, dan
             * merakitnya di tempat lain berarti dua daftar yang harus sepakat
             * tanpa ada yang memaksanya. Lihat MEDAN_SARINGAN.
             *
             * Nilai mentah dari permintaan, BUKAN nilai yang sudah
             * dibersihkan di atas: nama medannya berbeda ('bayar' vs
             * 'caraBayar', 'lama' vs 'menggantung'), dan yang harus
             * ditempelkan ke tautan adalah nama yang dibaca peladen.
             * Nilai yang tidak sah tetap aman karena ia dibersihkan lagi saat
             * alamat barunya dibuka.
             */
            'bawa' => array_filter(
                $request->only(self::MEDAN_SARINGAN),
                fn ($n) => $n !== null && $n !== ''
            ),
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
    /**
     * Nomor telepon tanpa pemisah, untuk diadu dengan yang tersimpan.
     *
     * Dipisahkan dari telpAngka(): yang itu TIDAK menerima argumen — ia
     * merakit ungkapan SQL untuk kolomnya. Dipanggil dengan nomor sebagai
     * argumen, PHP tidak mengeluh dan ia tetap mengembalikan ungkapan SQL-nya,
     * sehingga pembandingan nomornya tidak pernah cocok sekali pun.
     *
     * Daftar tandanya disamakan dengan telpAngka() supaya kedua sisi
     * perbandingan membuang hal yang sama.
     */
    /**
     * Nomor dalam bentuk yang dipakai WhatsApp: 62xxxxxxxxxx.
     *
     * Hanya bentuknya yang dirapikan, bukan isinya — nomor yang tidak dikenali
     * polanya dikembalikan apa adanya (sesudah pemisahnya dibuang), sebab
     * menebak-nebak nomor orang lebih buruk daripada menyimpan apa yang
     * diketik.
     */
    private function telpWa(string $telp): string
    {
        $angka = $this->telpBersih($telp);

        if ($angka === '') {
            return '';
        }

        if (str_starts_with($angka, '0')) {
            return '62' . ltrim(substr($angka, 1), '0');
        }

        if (str_starts_with($angka, '8')) {
            return '62' . $angka;
        }

        return $angka;
    }

    private function telpBersih(string $telp): string
    {
        return str_replace(['-', ' ', '(', ')', '+', '.'], '', trim($telp));
    }

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
