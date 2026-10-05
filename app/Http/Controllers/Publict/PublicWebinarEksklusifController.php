<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Services\Doku;
use App\Services\Gambar;
use App\Support\AlamatGambar;
use App\Support\NomorTelepon;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\WebinarEksklusifPendaftaranMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Halaman publik Webinar Eksklusif: borang pendaftaran dan pembayarannya.
 *
 * Halaman pemasarannya sendiri ada di subdomain tersendiri dan mengambil
 * datanya lewat ApiWebinarEksklusifController. Yang di sini bagian yang tidak
 * boleh ada di halaman statis: borang yang menulis ke basis data, penjaga
 * kuota, dan jalur pembayaran.
 */
class PublicWebinarEksklusifController extends Controller
{
    private const KODE = 'webinar_eksklusif';

    /** Awalan kunci sesi penanda layar ringkas, disambung id pendaftarannya. */
    private const KUNCI_RINGKAS = 'we_ringkas_';

    /*
     * Batas ukuran bukti transfer, dalam KB.
     *
     * 8 MB dipilih dari berkasnya, bukan ditebak: tangkapan layar m-banking
     * di ponsel masa kini 200-600 KB, sementara FOTO layar memakai kamera
     * 12 MP tembus 4 MB dan HEIC iPhone sekitar 2 MB. Dipatok 2 MB seperti
     * layar lama, separuh peserta akan ditolak di percobaan pertama.
     */
    private const BUKTI_MAKS_KB = 8192;

    public function __construct(private Doku $doku)
    {
    }

    /**
     * Data pengunjung yang sedang masuk, untuk mengisi borang di muka.
     *
     * Larik kosong kalau ia tamu — bukan null, supaya tampilannya cukup
     * memanggil old('nama', $isiAwal['nama'] ?? '') tanpa bercabang.
     *
     * @return array<string,string>
     */
    /**
     * Potongan harga dari kode diskon angkatannya.
     *
     * Polanya mengikuti Scopus Camp: kodenya disimpan di angkatan, bukan di
     * tabel promo tersendiri. Nol kalau angkatannya memang tidak punya kode,
     * kodenya tidak cocok, atau nominalnya tidak masuk akal.
     *
     * Tidak pernah melebihi totalnya: potongan yang lebih besar daripada
     * tagihan akan membuat total negatif, dan gerbang pembayaran menolak
     * angka seperti itu dengan galat yang tidak menyebut sebabnya.
     */
    private function hitungPotongan(KategoriLayanan $sesi, ?string $kode, int $total): int
    {
        $kodeSesi = trim((string) $sesi->kode_diskon);
        $kode = trim((string) $kode);

        if ($kodeSesi === '' || $kode === '' || ! hash_equals($kodeSesi, $kode)) {
            return 0;
        }

        $nominal = (int) preg_replace('/\\D+/', '', (string) $sesi->nominal_diskon);

        return max(0, min($nominal, $total));
    }

    /*
     * Batas kode unik: 500 sampai 1.500 rupiah, SAMA untuk semua layanan.
     *
     * Dibaca dari PendaftaranSemuaLayanan::KODE_UNIK, bukan ditulis ulang di
     * sini: angkanya sempat berbeda-beda antar layanan, dan dua tempat yang
     * menyebut hal yang sama pasti berselisih begitu salah satunya diubah.
     */
    public const KODE_UNIK_MIN = \App\Support\PendaftaranSemuaLayanan::KODE_UNIK[0];

    public const KODE_UNIK_MAKS = \App\Support\PendaftaranSemuaLayanan::KODE_UNIK[1];

    /**
     * Kode unik yang BELUM dipakai pendaftaran lain yang masih hidup.
     *
     * Acak saja tidak cukup. Seluruh gunanya adalah membuat nominal transfer
     * berbeda antar-orang; kalau dua pendaftaran di angkatan yang sama
     * kebetulan bertotal sama persis, satu mutasi rekening menunjuk dua
     * pendaftaran dan panitia kembali menebak — justru keadaan yang hendak
     * dihindari, dan tidak ada gejala apa pun yang menandainya.
     *
     * Yang dibandingkan TOTALNYA, bukan kodenya: dua pendaftaran berjumlah
     * peserta berbeda boleh berkode sama, sebab totalnya tetap berbeda.
     *
     * Kalau 1.001 kemungkinan itu benar-benar habis — butuh lebih dari seribu
     * pendaftaran bertotal dasar sama yang semuanya belum lunas — yang
     * dipulangkan nol. Lebih baik dua nominal kembar daripada pendaftarannya
     * gagal tersimpan.
     */
    private function kodeUnikBebas(KategoriLayanan $sesi, int $totalDasar): int
    {
        $terpakai = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->getKey())
            ->whereIn('status', ['pending', 'paid'])
            ->pluck('total_pembayaran')
            ->map(fn ($t) => (int) $t)
            ->flip();

        for ($coba = 0; $coba < 40; $coba++) {
            $kode = random_int(self::KODE_UNIK_MIN, self::KODE_UNIK_MAKS);

            if (! $terpakai->has($totalDasar + $kode)) {
                return $kode;
            }
        }

        // Sudah 40 kali meleset; disisir berurutan supaya yang tersisa ketemu.
        for ($kode = self::KODE_UNIK_MIN; $kode <= self::KODE_UNIK_MAKS; $kode++) {
            if (! $terpakai->has($totalDasar + $kode)) {
                return $kode;
            }
        }

        return 0;
    }

    private function isiAwalDariAkun(): array
    {
        $akun = auth()->user();

        if ($akun === null) {
            return [];
        }

        return array_filter([
            'nama' => (string) ($akun->full_name ?? ''),
            'email' => (string) ($akun->email ?? ''),
            'telp' => NomorTelepon::rapikan($akun->telp ?? ''),
            'affiliasi' => (string) ($akun->company ?? ''),
        ], fn ($v) => $v !== '');
    }

    /**
     * BATAS WAKTU TRANSFER MANUAL JAUH LEBIH PANJANG DARIPADA GERBANG.
     *
     * Sebelumnya keduanya memakai Doku::MENIT_KEDALUWARSA (60 menit).
     * Enam puluh menit masuk akal untuk virtual account — ia memang
     * kedaluwarsa sendiri di sisi gerbang. Untuk transfer manual tidak:
     * orangnya harus membuka m-banking atau pergi ke ATM, mengirim bukti ke
     * WhatsApp, lalu MENUNGGU panitia mengonfirmasi dengan tangan.
     *
     * Dengan 60 menit, tiap pendaftaran transfer pasti kedaluwarsa dan
     * kursinya dilepas walau uangnya sudah dikirim — terukur: dari 3
     * pendaftaran yang pernah ada di basis data ini, ketiganya berstatus
     * expired dan tidak satu pun pernah berstatus paid.
     */
    private const JAM_KEDALUWARSA_TRANSFER = 24;

    /**
     * Batas rombongan yang boleh didaftarkan sendiri lewat borang.
     *
     * Dulu 50. Terukur di layar 390 px: 50 peserta membuat kartu isiannya
     * setinggi 7.362 px dengan 104 kotak isian — delapan layar penuh kotak
     * nama kosong, dan tombol daftar beserta totalnya terkubur di paling
     * bawah. Borang sepanjang itu lebih sering ditinggalkan daripada diisi.
     *
     * Sepuluh menyisakan kartu setinggi ~2.250 px, masih wajar. Yang lebih
     * besar diarahkan ke panitia, yang memang sudah menanganinya lewat
     * WhatsApp.
     */
    public const MAKS_ROMBONGAN = 10;

    private static function menitKedaluwarsa(bool $lewatGerbang): int
    {
        return $lewatGerbang
            ? Doku::MENIT_KEDALUWARSA
            : self::JAM_KEDALUWARSA_TRANSFER * 60;
    }

    // --------------------------------------------------- isi otomatis

    /**
     * Mencari data pendaftar dari nomor WhatsApp-nya, untuk mengisi borang
     * secara otomatis.
     *
     * INI MEMBUKA DATA, dan itu disengaja terbatas. Siapa pun yang tahu
     * sebuah nomor bisa menukarnya jadi nama dan email lewat jalur ini, jadi
     * yang dijaga:
     *
     * 1. Nomornya harus LENGKAP (9-15 angka). Potongan nomor tidak dicari,
     *    sehingga tidak bisa disisir dari awalan.
     * 2. Dibatasi 10 permintaan per menit per alamat IP (di berkas rute).
     * 3. Hanya dijawab kalau cocoknya SATU ORANG. Di basis data ini ada satu
     *    nomor yang dipakai empat akun sekaligus (manager, staff, rental,
     *    karyawan) — menjawabnya berarti menyerahkan identitas orang yang
     *    salah.
     * 4. Emailnya dikembalikan utuh karena memang untuk diisikan ke borang,
     *    tetapi tiap pencarian yang BERHASIL dicatat ke log, supaya
     *    penyisiran meninggalkan jejak.
     *
     * Yang sudah masuk akun tidak lewat sini sama sekali: datanya sudah
     * dirender peladen di borangnya.
     */
    public function cariPendaftar(Request $request)
    {
        $data = $request->validate([
            'telp' => ['required', 'string', 'max:30'],
        ]);

        $nomor = NomorTelepon::rapikan($data['telp']);

        if (! NomorTelepon::masukAkal($nomor)) {
            return response()->json(['ditemukan' => false]);
        }

        $ketemu = $this->cariDariNomor($nomor);

        if ($ketemu === null) {
            return response()->json(['ditemukan' => false]);
        }

        Log::info('Isi otomatis borang Webinar Eksklusif', [
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

    /**
     * Satu orang yang nomornya cocok, atau null.
     *
     * Pendaftaran sebelumnya didahulukan daripada akun: isinya persis
     * sebentuk dengan borang ini (nama beserta gelar, asal instansi),
     * sedangkan nama di akun sering sekadar "admin" atau "staff".
     *
     * @return array{nama:string,email:string,affiliasi:?string,sumber:string}|null
     */
    private function cariDariNomor(string $nomor): ?array
    {
        /*
         * Dicocokkan ke SEMUA bentuk yang mungkin tersimpan, bukan satu.
         * Kolom telp diisi bertahun-tahun oleh layar yang berbeda: ada yang
         * "6281...", ada "0812...", ada "+62 812-...". Mencari satu bentuk
         * saja membuat sebagian orang tidak pernah ketemu, dan diamnya
         * terbaca seperti ia memang belum pernah mendaftar.
         */
        $bentuk = NomorTelepon::semuaBentuk($nomor);
        $bersih = "REPLACE(REPLACE(REPLACE(REPLACE(telp, '+', ''), '-', ''), ' ', ''), '.', '')";

        $dari = WebinarEksklusifPendaftaran::query()
            ->whereRaw("$bersih IN (" . implode(',', array_fill(0, count($bentuk), '?')) . ')', $bentuk)
            ->latest()
            ->first();

        if ($dari !== null) {
            return [
                'nama' => (string) $dari->nama,
                'email' => (string) $dari->email,
                'affiliasi' => $dari->affiliasi,
                'sumber' => 'pendaftaran',
            ];
        }

        $akun = User::query()
            ->whereRaw("$bersih IN (" . implode(',', array_fill(0, count($bentuk), '?')) . ')', $bentuk)
            ->get(['full_name', 'email', 'company']);

        /*
         * Lebih dari satu akun berarti nomornya dipakai bersama — dan tidak
         * ada cara memilih yang benar dari sini. Didiamkan, bukan ditebak.
         */
        if ($akun->count() !== 1) {
            return null;
        }

        $u = $akun->first();

        return [
            'nama' => (string) $u->full_name,
            'email' => (string) $u->email,
            'affiliasi' => $u->company,
            'sumber' => 'akun',
        ];
    }

    /**
     * Memeriksa kode diskon tanpa mengirim borangnya.
     *
     * Tanpa ini, orang yang mengetik kode tidak mendapat tanda apa pun:
     * totalnya di layar tetap harga penuh, tidak ada kabar kodenya benar atau
     * salah, dan baru ketahuan sesudah mengirim. Yang salah ketik membayar
     * penuh tanpa tahu kenapa.
     *
     * Potongannya tetap dihitung ULANG saat menyimpan — jawaban di sini hanya
     * untuk ditampilkan, dan apa pun yang dikirim balik peramban tidak pernah
     * dipercaya.
     */
    public function cekDiskon(Request $request, string $id)
    {
        $sesi = $this->kueriAktif()->whereKey($id)->first();

        if ($sesi === null) {
            return response()->json(['cocok' => false, 'pesan' => 'Sesi itu sudah tidak dibuka.']);
        }

        $kode = trim((string) $request->input('kode'));
        $jumlah = max(1, min(self::MAKS_ROMBONGAN, (int) $request->input('jumlah', 1)));

        $total = (int) $sesi->biaya * $jumlah;
        $potongan = $this->hitungPotongan($sesi, $kode, $total);

        if ($potongan <= 0) {
            return response()->json([
                'cocok' => false,
                'pesan' => $kode === '' ? '' : 'Kode itu tidak cocok untuk sesi ini.',
            ]);
        }

        return response()->json([
            'cocok' => true,
            'potongan' => $potongan,
            'total' => max(0, $total - $potongan),
            'pesan' => 'Kode dipakai, potongan Rp ' . number_format($potongan, 0, ',', '.') . '.',
        ]);
    }

    // ------------------------------------------------------------- daftar

    /** Semua sesi yang sedang dibuka. */
    public function index()
    {
        KategoriLayanan::hitungPendaftar();

        return view('public.webinar_eksklusif.index', [
            'sesi' => $this->kueriAktif()->get(),
        ]);
    }

    /**
     * Borang pendaftaran satu sesi, dicari dari UUID angkatannya.
     *
     * Tokennya dibuang 3 Okt 2026. Alasan lamanya "bisa menutup satu tautan
     * yang bocor" tidak pernah terpakai: tidak ada layar mana pun yang bisa
     * memutar tokennya, jadi yang tersisa hanya alamat dua kali lebih panjang
     * dan dua nilai yang harus dijaga tetap cocok. Yang menutup pendaftaran
     * adalah status angkatannya, dan itu memang sudah diperiksa kueriAktif().
     */
    public function daftar(string $id)
    {
        $sesi = $this->temukan($id);

        if ($sesi === null) {
            return $this->kembaliKeDaftar('Sesi itu sudah tidak dibuka lagi.');
        }

        /*
         * Kursi yang ditinggalkan dilepas DI SINI, bukan hanya oleh perintah
         * terjadwal.
         *
         * Bergantung penjadwal saja berarti satu titik kegagalan yang diam:
         * kalau ia tidak jalan, kursi yang ditinggalkan menahan tempatnya
         * selamanya, dan yang tampil di layar "Tersisa 0 kursi" padahal
         * sebenarnya kosong. Dipanggil di sini, kursinya kembali tepat saat
         * ada orang yang hendak memakainya.
         */
        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa($sesi->getKey());

        KategoriLayanan::hitungPendaftar();

        $sesi->refresh();

        return view('public.webinar_eksklusif.form_pendaftaran', [
            'sesi' => $sesi,
            'tarif' => $sesi->tarif(),
            /*
             * Isian awal untuk yang sudah masuk akun, dirender peladen.
             *
             * TIDAK lewat jalur pencarian nomor: datanya miliknya sendiri,
             * jadi tidak ada yang dibuka ke siapa pun, dan borangnya sudah
             * terisi begitu halaman terbuka — tanpa menunggu ia mengetik
             * nomornya lebih dulu.
             */
            'isiAwal' => $this->isiAwalDariAkun(),
            // Dikirim ke tampilan supaya kalimat di layar menyesuaikan, bukan
            // supaya tampilannya memutuskan sendiri cara bayarnya.
            'bayarDaring' => $this->doku->siap(),
        ]);
    }

    // ------------------------------------------------------------ simpan

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori_id' => ['required', 'uuid'],
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:120'],
            /*
             * Yang diperiksa ANGKANYA, bukan panjang teksnya.
             *
             * Dengan 'string|max:30' saja, "tidak punya wa" lolos — lalu
             * rapikanTelp() membuang semua non-angka dan yang tersimpan
             * string KOSONG. Terbukti: pendaftarannya diterima, kursinya
             * terpotong, dan panitia baru tahu saat hendak memasukkan
             * orangnya ke grup.
             *
             * 9 angka adalah nomor Indonesia terpendek yang masuk akal
             * (08xx + 7), 15 batas E.164.
             */
            'telp' => ['required', 'string', 'max:30', function ($medan, $nilai, $gagal) {
                if (! NomorTelepon::masukAkal($nilai)) {
                    $gagal('Nomor WhatsApp-nya belum benar — tulis angkanya saja, contoh 0812 3456 7890.');
                }
            }],
            'affiliasi' => ['nullable', 'string', 'max:160'],
            'jumlah_pendaftar' => ['required', 'integer', 'min:1', 'max:' . self::MAKS_ROMBONGAN],
            // Persetujuan dipakainya data. Disimpan waktunya, bukan cuma
            // dicentang lalu dilupakan — kalau ditanya, harus bisa dijawab
            // kapan orangnya menyetujui.
            'setuju' => ['accepted'],

            /*
             * Nama peserta KEDUA DAN SETERUSNYA.
             *
             * Wajib sebanyak peserta yang dipesan dikurangi satu: yang
             * pertama sudah terisi di medan nama. Tanpa ini, pendaftaran 37
             * orang hanya menyisakan satu nama — dan 36 sertifikat tidak bisa
             * diterbitkan atas nama siapa pun.
             */
            'peserta' => ['array', 'max:49'],
            'peserta.*.nama' => ['required', 'string', 'max:120'],
            'peserta.*.email' => ['nullable', 'email:rfc', 'max:120'],

            'kode_diskon' => ['nullable', 'string', 'max:40'],
        ], [
            'nama.required' => 'Nama lengkapnya diisi dulu, ya.',
            'email.required' => 'Emailnya diisi dulu — tautan Zoom dikirim ke sana.',
            'email.email' => 'Alamat emailnya belum benar.',
            'telp.required' => 'Nomor WhatsApp-nya diisi dulu, ya.',
            'setuju.accepted' => 'Centang persetujuannya dulu, ya.',
            'peserta.*.nama.required' => 'Nama tiap peserta diisi dulu, ya — sertifikatnya atas nama mereka.',
            'peserta.*.email.email' => 'Ada alamat email peserta yang belum benar.',
            'jumlah_pendaftar.min' => 'Minimal satu peserta.',
            'jumlah_pendaftar.max' => 'Lebih dari ' . self::MAKS_ROMBONGAN
                . ' peserta, hubungi panitia dulu ya — kami bantu daftarkan sekaligus.',
        ]);

        /*
         * validate() TIDAK memuat kunci yang tidak dikirim peramban, dan
         * "asal instansi" memang boleh dikosongkan. Dibaca langsung,
         * $data['affiliasi'] melempar "Undefined array key" — galat yang
         * hanya muncul pada orang yang mengosongkannya, jadi gampang lolos
         * dari pemeriksaan manual.
         */
        $data['affiliasi'] = $data['affiliasi'] ?? null;
        $data['peserta'] = array_values($data['peserta'] ?? []);

        /*
         * Jumlah nama diperiksa TERPISAH dari aturan di atas, sebab yang
         * diperiksa hubungan antar-medan: sebanyak peserta yang dipesan,
         * dikurangi pendaftar utamanya.
         *
         * Diperiksa di peladen, bukan hanya di peramban: borang yang dikirim
         * tanpa JavaScript akan lolos begitu saja, dan yang tersimpan
         * pendaftaran 10 orang dengan satu nama.
         */
        /*
         * Email peserta tidak boleh kembar — baik sesama peserta maupun
         * dengan pendaftar utamanya. Dua orang beremail sama berarti satu di
         * antaranya tidak akan pernah menerima apa pun, dan yang ketahuan
         * belakangan hanyalah "sertifikat saya tidak sampai".
         */
        $surel = array_filter(array_map(
            fn ($o) => mb_strtolower(trim((string) ($o['email'] ?? ''))),
            $data['peserta']
        ));

        $surel[] = mb_strtolower(trim($data['email']));

        if (count($surel) !== count(array_unique($surel))) {
            return back()->withInput()->withErrors([
                'peserta' => 'Ada email yang dipakai dua kali. Tiap peserta perlu email sendiri, '
                    . 'atau kosongkan saja yang tidak punya.',
            ]);
        }

        $perluNama = max(0, (int) $data['jumlah_pendaftar'] - 1);

        if (count($data['peserta']) !== $perluNama) {
            return back()->withInput()->withErrors([
                'peserta' => $perluNama === 0
                    ? 'Jumlah pesertanya satu, jadi tidak perlu nama tambahan.'
                    : 'Isi nama ' . $perluNama . ' peserta lainnya — sertifikatnya atas nama mereka.',
            ]);
        }

        $sesi = $this->kueriAktif()->whereKey($data['kategori_id'])->first();

        if ($sesi === null) {
            return $this->kembaliKeDaftar('Sesi itu sudah tidak dibuka lagi.');
        }

        /*
         * Kuota diperiksa DI DALAM transaksi dengan baris angkatannya dikunci.
         *
         * Diperiksa di luar, dua orang yang menekan "Daftar" pada detik yang
         * sama sama-sama melihat sisa 1 dan keduanya lolos. Halaman ini
         * halaman iklan — bersamaan itu justru keadaan yang biasa, bukan yang
         * langka.
         */
        // Lihat alasannya di daftar(): kursi yang ditinggalkan dilepas lebih
        // dulu supaya pemeriksaan kuota di bawah memakai angka yang benar.
        WebinarEksklusifPendaftaran::lepaskanYangKedaluwarsa($sesi->getKey());

        /*
         * PENJAGA PENDAFTARAN GANDA.
         *
         * Diperiksa sebelum kuota dikunci: tanpa ini satu orang yang menekan
         * "Daftar" berkali-kali — atau menyegarkan halaman pembayaran —
         * membuat beberapa pendaftaran, dan TIAP SATUNYA memotong kursi yang
         * baru kembali setengah jam kemudian saat kedaluwarsa.
         *
         * Yang sudah lunas tidak boleh mendaftar lagi; yang masih menunggu
         * bayar diantar ke tagihannya yang lama, bukan dibuatkan yang baru.
         */
        $email = mb_strtolower(trim($data['email']));

        /*
         * Dicocokkan KETIGANYA sekaligus: nomor, email, DAN nama.
         *
         * Bukan salah satunya. Satu nomor WhatsApp dipakai bersama lebih sering
         * daripada yang terlihat — panitia kampus mendaftarkan beberapa orang
         * dari nomornya sendiri, dan suami-istri berbagi satu nomor. Dicegat
         * dari nomornya saja, orang kedua tidak akan pernah bisa didaftarkan;
         * yang terjadi bukan kuota terjaga, melainkan pendaftaran yang hilang.
         *
         * Yang dicegat hanya pengulangan data yang BENAR-BENAR sama — orang
         * yang menekan "Daftar" dua kali, atau menyegarkan halaman
         * pembayarannya.
         *
         *   nomor + email + nama sama   -> satu pendaftaran (dicegat)
         *   nomor sama, email/nama beda -> pendaftaran sendiri
         *   ketiganya beda              -> pendaftaran sendiri
         *
         * Nomornya dicari lewat semuaBentuk(): kolomnya diisi bertahun-tahun
         * oleh layar yang berbeda, jadi satu orang bisa tersimpan sebagai
         * "62895...", "0895...", atau "+62 895-...". Mencari satu bentuk saja
         * membuat sebagian orang tidak pernah ketemu, dan diamnya terbaca
         * sebagai "memang belum pernah mendaftar".
         */
        $bentukNomor = NomorTelepon::semuaBentuk($data['telp']);

        $sudahAda = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->getKey())
            ->where('email', $email)
            ->when($bentukNomor !== [], fn ($q) => $q->whereIn('telp', $bentukNomor))
            ->whereIn('status', ['pending', 'paid'])
            ->where(function ($q) {
                // Yang sudah dibayar selalu dihitung; yang masih menunggu
                // hanya selama belum lewat batas waktunya.
                $q->where('status', 'paid')
                    ->orWhereNull('kedaluwarsa_pada')
                    ->orWhere('kedaluwarsa_pada', '>', now());
            })
            ->latest()
            ->get()
            /*
             * NAMANYA dibandingkan di PHP, bukan di dalam kueri.
             *
             * Perapiannya — huruf kecil semua, spasi ganda dirapatkan — tidak
             * bisa ditulis sama persis di SQL: REPLACE bersarang hanya
             * merapatkan spasi ganda beberapa lapis, sementara preg_replace
             * merapatkan tab dan spasi berapa pun sekaligus. Dua aturan yang
             * tidak persis sama berarti penjaganya diam-diam berhenti bekerja
             * pada nama yang kebetulan jatuh di antaranya.
             *
             * Barisnya sedikit — sudah disaring email, nomor, dan sesi — jadi
             * menyaringnya di PHP tidak menambah beban yang terasa.
             */
            ->first(fn ($baris) => $this->namaRapi($baris->nama) === $this->namaRapi($data['nama']));

        if ($sudahAda !== null) {
            /*
             * Yang cocok KETIGANYA, jadi kalimatnya menyebut datanya sebagai
             * satu kesatuan. Menyebut salah satu saja — "Email itu sudah
             * terdaftar" — membuat orang mengira cukup mengganti email itu,
             * lalu ia mencoba lagi.
             */
            /*
             * Dikirim sebagai 'kabar', BUKAN 'error'.
             *
             * Ini bukan kesalahan pendaftar — pendaftarannya memang ada dan
             * masih berlaku. Pita merah di layar orang yang tidak berbuat
             * salah membuatnya mengira pendaftarannya gagal, lalu ia mencoba
             * lagi; justru itu yang hendak dihentikan.
             */
            /*
             * Penandanya DISIMPAN di sesi, bukan dikirim sebagai pesan
             * sekali-pakai.
             *
             * Versi pertama memakai flash, dan ia hilang begitu halamannya
             * dimuat ulang — layar penuh beserta borang unggahnya muncul lagi
             * tepat pada orang yang baru saja diberi tahu tidak perlu berbuat
             * apa-apa. Ditekan F5 sekali, seluruh penjagaannya batal.
             *
             * Diberi kunci per pendaftaran, bukan satu penanda untuk semua:
             * satu peramban bisa memegang beberapa pendaftaran sekaligus
             * (panitia yang mendaftarkan beberapa orang), dan penanda tunggal
             * akan meringkas halaman yang bukan haknya.
             */
            session()->put(self::KUNCI_RINGKAS . $sudahAda->getKey(), true);

            return redirect()->route('public.webinareksklusif.status', $sudahAda->getKey())
                ->with('kabar', $sudahAda->status === 'paid'
                    ? 'Data ini sudah terdaftar di sesi ini dan pembayarannya sudah lunas.'
                        . ' Ini rincian pendaftaran Anda.'
                    : 'Data ini sudah punya pendaftaran di sesi ini. Kursinya sudah ditahan'
                        . ' untuk Anda — tidak perlu mendaftar lagi.');
        }

        try {
            $pendaftaran = DB::transaction(function () use ($sesi, $data) {
                $terkunci = KategoriLayanan::whereKey($sesi->getKey())->lockForUpdate()->first();

                $sisa = $terkunci->total_kuota === null ? null : (int) $terkunci->sisa_kuota;

                if ($sisa !== null && $sisa < $data['jumlah_pendaftar']) {
                    throw new KuotaHabis($sisa);
                }

                $harga = (int) $terkunci->biaya;
                $total = $harga * $data['jumlah_pendaftar'];

                /*
                 * Potongan dihitung ULANG DI SINI dari angkatannya, bukan
                 * dipercaya dari borang. Nominal yang dikirim peramban bisa
                 * disunting siapa saja sebelum dikirim.
                 */
                $potongan = $this->hitungPotongan($terkunci, $data['kode_diskon'] ?? null, $total);
                $total = max(0, $total - $potongan);

                /*
                 * Kode unik DITAMBAHKAN ke tagihan, bukan dikurangkan.
                 *
                 * Gunanya mencocokkan transfer: dua orang yang mendaftar paket
                 * sama akan mengirim nominal yang persis sama, dan panitia
                 * tidak punya cara tahu uang masuk itu dari siapa. Dengan tiga
                 * digit terakhir yang berbeda, satu mutasi rekening langsung
                 * menunjuk satu pendaftaran.
                 *
                 * Hanya untuk transfer manual. Lewat gerbang pembayaran,
                 * pencocokannya memakai nomor rujukan dan menambah angka receh
                 * di sana justru membingungkan.
                 */
                $kodeUnik = $this->doku->siap() ? 0 : $this->kodeUnikBebas($terkunci, $total);
                $total += $kodeUnik;

                $pendaftaran = WebinarEksklusifPendaftaran::create([
                    'kategori_id' => $terkunci->getKey(),
                    'nama' => trim($data['nama']),
                    'email' => mb_strtolower(trim($data['email'])),
                    'telp' => NomorTelepon::rapikan($data['telp']),
                    'affiliasi' => $data['affiliasi'] ? trim($data['affiliasi']) : null,
                    'disetujui_pada' => now(),
                    'jumlah_pendaftar' => $data['jumlah_pendaftar'],
                    'total_pembayaran' => (string) $total,
                    'user_id' => auth()->id(),
                    'kode_unik' => $kodeUnik > 0 ? (string) $kodeUnik : null,
                    'kode_diskon' => $potongan > 0 ? trim((string) $data['kode_diskon']) : null,
                    'nominal_diskon' => $potongan > 0 ? (string) $potongan : null,
                    'cara_bayar' => $this->doku->siap() ? 'doku' : 'transfer',
                    'status' => 'pending',
                    'kedaluwarsa_pada' => now()->addMinutes(self::menitKedaluwarsa($this->doku->siap())),
                ]);

                /*
                 * Kuota dipotong SEKARANG, bukan nanti saat lunas. Dipotong
                 * saat lunas, kursi yang sedang dibayar orang masih terlihat
                 * kosong dan bisa diambil orang lain — dan yang sudah
                 * terlanjur membayar tidak punya tempat.
                 *
                 * Yang pendaftarannya kedaluwarsa dikembalikan kuotanya oleh
                 * perintah terjadwal.
                 */
                foreach ($data['peserta'] as $urutan => $orang) {
                    $pendaftaran->pesertaLain()->create([
                        'urutan' => $urutan,
                        'nama' => trim($orang['nama']),
                        'email' => isset($orang['email']) && $orang['email'] !== ''
                            ? mb_strtolower(trim($orang['email']))
                            : null,
                    ]);
                }

                if ($sisa !== null) {
                    $terkunci->forceFill([
                        'sisa_kuota' => (string) max(0, $sisa - $data['jumlah_pendaftar']),
                    ])->save();
                }

                return $pendaftaran;
            });
        } catch (KuotaHabis $e) {
            return back()->withInput()->with('error', $e->pesanUntukOrang());
        }

        $this->kirimEmailPendaftaran($pendaftaran, $sesi);

        return $this->mulaiBayar($pendaftaran, $sesi);
    }

    /**
     * Membuat tagihan lalu mengarahkan ke halaman bayar.
     *
     * Gagal membuat tagihan TIDAK membatalkan pendaftarannya: orangnya sudah
     * mengisi borang, dan menghapusnya berarti ia harus mengulang dari nol
     * karena kesalahan yang bukan miliknya. Ia diarahkan ke halaman status
     * yang memuat cara bayar manual dan nomor panitia.
     */
    private function mulaiBayar(WebinarEksklusifPendaftaran $pendaftaran, KategoriLayanan $sesi)
    {
        if (! $this->doku->siap()) {
            return redirect()->route('public.webinareksklusif.status', $pendaftaran->getKey());
        }

        $hasil = $this->doku->buatTagihan([
            'nomor' => $pendaftaran->id_transaksi,
            'jumlah' => (int) $pendaftaran->total_pembayaran,
            'harga_satuan' => (int) $sesi->biaya,
            'jumlah_peserta' => $pendaftaran->jumlah_pendaftar,
            'judul' => $sesi->nama,
            'nama' => $pendaftaran->nama,
            'email' => $pendaftaran->email,
            'telp' => $pendaftaran->telp,
            'kembali' => route('public.webinareksklusif.status', $pendaftaran->getKey()),
        ]);

        if (! $hasil['berhasil'] || ! $hasil['url']) {
            $pendaftaran->forceFill([
                'cara_bayar' => 'transfer',
                'note' => 'Pembayaran daring gagal dibuat: ' . ($hasil['pesan'] ?? 'tidak diketahui'),
            ])->save();

            return redirect()->route('public.webinareksklusif.status', $pendaftaran->getKey())
                ->with('error', 'Pembayaran daring sedang bermasalah. '
                    . 'Pendaftaran Anda sudah tersimpan — silakan bayar lewat transfer.');
        }

        $pendaftaran->forceFill([
            'bayar_rujukan' => $hasil['rujukan'],
            'bayar_status' => 'menunggu',
        ])->save();

        return redirect()->away($hasil['url']);
    }

    // ------------------------------------------------------------ status

    /** Halaman status satu pendaftaran; tautannya dikirim ke email peserta. */
    public function status(string $id)
    {
        $pendaftaran = WebinarEksklusifPendaftaran::whereKey($id)->first();

        if ($pendaftaran === null) {
            return $this->kembaliKeDaftar('Pendaftaran itu tidak ditemukan.');
        }

        /*
         * Alamat buktinya dirakit lewat AlamatGambar, bukan asset() langsung:
         * nilai kolomnya bisa jalur di cakram unggahan (unggahan baru) ATAU
         * nama berkas peninggalan jalur lama, dan hanya AlamatGambar yang tahu
         * keduanya. null kalau kolomnya terisi tetapi berkasnya sudah tidak
         * ada — peserta lebih baik melihat borang unggah kosong daripada
         * tautan yang membuka halaman galat.
         */
        $buktiUrl = AlamatGambar::url($pendaftaran->gambar);

        /*
         * Layar ringkas: hanya rincian, hitung mundur, dan statusnya.
         *
         * Dipasang saat orangnya dicegat karena mendaftar lagi dengan data
         * yang sama, dan BERTAHAN sampai ia sendiri memintanya dibuka —
         * dimuat ulang pun tidak menghapusnya.
         *
         * TIDAK ada jalan keluar dari layar ini, dan itu disengaja. Versi
         * sebelumnya menyediakan tautan ?penuh=1, dan sekali ditekan penanda
         * ringkasnya terhapus — sejak itu tiap muat ulang memulangkan layar
         * penuh beserta borang unggahnya, persis keadaan yang hendak
         * dihindari.
         *
         * Yang dicegat memang sudah pernah mendaftar, dan cara membayarnya
         * sudah ada di email bukti pendaftaran yang ia terima saat itu —
         * tombol pengirim ulangnya ada di layar ini.
         */
        $kunciRingkas = self::KUNCI_RINGKAS . $pendaftaran->getKey();

        return view('public.webinar_eksklusif.status', [
            'ringkas' => (bool) session($kunciRingkas),
            'pendaftaran' => $pendaftaran,
            'sesi' => $pendaftaran->angkatan,
            'buktiUrl' => $buktiUrl,
            'buktiAda' => $buktiUrl !== null,
        ]);
    }

    // ----------------------------------------------- pemberitahuan DOKU

    /**
     * Pemberitahuan balik dari DOKU bahwa sebuah tagihan sudah dibayar.
     *
     * Dikecualikan dari CSRF (lihat VerifyCsrfToken) karena yang mengirim
     * peladen DOKU, bukan peramban peserta. Penggantinya BUKAN tidak ada
     * penjagaan: tanda tangannya diperiksa lebih dulu, dan tanpa itu siapa pun
     * yang tahu alamat ini bisa menandai pendaftaran mana pun sebagai lunas.
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

        // Isi kepala yang mengandung tanda tangan TIDAK ikut dicatat.
        $sah = $sah ?: $this->tolakPemberitahuan($request);

        if (! $sah) {
            return response()->json(['ok' => false], 401);
        }

        $nomor = (string) $request->input('order.invoice_number');
        $status = mb_strtolower((string) $request->input('transaction.status'));

        $pendaftaran = WebinarEksklusifPendaftaran::where('id_transaksi', $nomor)->first();

        if ($pendaftaran === null) {
            Log::warning('DOKU memberi tahu nomor yang tidak dikenal', ['nomor' => $nomor]);

            // 200: nomornya memang tidak ada di sini, dan menjawab galat
            // membuat DOKU mengulang pemberitahuan yang sama berhari-hari.
            return response()->json(['ok' => true]);
        }

        // Sudah lunas tidak diproses lagi: DOKU boleh mengirim pemberitahuan
        // yang sama lebih dari sekali, dan dua kali proses berarti dua kali
        // email dan dua kali potong kuota.
        if ($pendaftaran->status === 'paid') {
            return response()->json(['ok' => true]);
        }

        if (in_array($status, ['success', 'settlement'], true)) {
            $pendaftaran->forceFill([
                'status' => 'paid',
                'bayar_status' => $status,
                'bayar_pada' => now(),
            ])->save();
        } elseif (in_array($status, ['failed', 'expired', 'cancel'], true)) {
            $pendaftaran->forceFill([
                'status' => $status === 'cancel' ? 'cancel' : 'expired',
                'bayar_status' => $status,
            ])->save();

            $this->kembalikanKuota($pendaftaran);
        }

        return response()->json(['ok' => true]);
    }

    private function tolakPemberitahuan(Request $request): bool
    {
        Log::warning('Pemberitahuan DOKU ditolak: tanda tangannya tidak cocok', [
            'nomor' => $request->input('order.invoice_number'),
            'ip' => $request->ip(),
        ]);

        return false;
    }

    /** Mengembalikan kursi yang sempat dipesan tetapi tidak jadi dibayar. */
    private function kembalikanKuota(WebinarEksklusifPendaftaran $pendaftaran): void
    {
        $sesi = $pendaftaran->angkatan;

        if ($sesi === null || $sesi->total_kuota === null) {
            return;
        }

        DB::transaction(function () use ($sesi, $pendaftaran) {
            $terkunci = KategoriLayanan::whereKey($sesi->getKey())->lockForUpdate()->first();

            $terkunci->forceFill([
                // Tidak boleh melebihi totalnya: pengembalian ganda — misalnya
                // dari pemberitahuan yang datang dua kali — akan membuka kursi
                // yang sebenarnya tidak ada.
                'sisa_kuota' => (string) min(
                    (int) $terkunci->total_kuota,
                    (int) $terkunci->sisa_kuota + $pendaftaran->jumlah_pendaftar
                ),
            ])->save();
        });
    }

    // ------------------------------------------------------------ bantu

    private function kueriAktif()
    {
        return KategoriLayanan::query()
            ->where('layanan', self::KODE)
            ->where('status', 'active')
            ->whereRaw('coalesce(selesai, mulai) >= ?', [Carbon::today()->toDateString()])
            ->orderBy('mulai');
    }

    /**
     * Angkatan yang sedang dibuka, dicari dari UUID-nya saja.
     *
     * Tokennya dibuang: kuncinya Str::uuid() acak — 122 bit — jadi tebakannya
     * sudah mustahil tanpa nilai kedua, dan dua nilai yang harus cocok berarti
     * dua tempat yang bisa salah sinkron.
     */
    private function temukan(string $id): ?KategoriLayanan
    {
        return $this->kueriAktif()->whereKey($id)->first();
    }

    private function kembaliKeDaftar(string $pesan)
    {
        return redirect()->route('public.webinareksklusif.index')->with('error', $pesan);
    }

    /** Nomor telepon disimpan berangka saja supaya bisa langsung jadi tautan WA. */
    /**
     * Mengirim ULANG bukti pendaftaran ke email yang sama.
     *
     * Satu-satunya jalan kembali ke halaman ini adalah tautan ber-UUID. Kalau
     * emailnya terhapus atau masuk folder sampah, orangnya kehilangan nomor
     * pendaftaran dan cara bayarnya sekaligus — dan yang menanggung adalah
     * panitia lewat WhatsApp.
     *
     * Dikirim ke alamat yang TERSIMPAN, bukan ke alamat yang diketik di
     * borang: kalau penerimanya bisa ditentukan dari luar, siapa pun yang
     * memegang tautan ini bisa memakainya untuk mengirimi orang lain.
     */
    public function kirimUlang(string $id)
    {
        $pendaftaran = WebinarEksklusifPendaftaran::whereKey($id)->first();

        if ($pendaftaran === null) {
            return $this->kembaliKeDaftar('Pendaftaran itu tidak ditemukan.');
        }

        $sesi = $pendaftaran->angkatan;

        if ($sesi === null) {
            return back()->with('error', 'Sesi pendaftaran ini sudah tidak ada.');
        }

        $this->kirimEmailPendaftaran($pendaftaran, $sesi);

        return back()->with('sukses',
            'Bukti pendaftaran dikirim ulang ke ' . $this->samarkanEmail($pendaftaran->email)
            . '. Kalau belum masuk dalam beberapa menit, periksa folder spam.');
    }


    /**
     * Peserta mengunggah bukti transfernya sendiri dari halaman status.
     *
     * Ada selama gerbang pembayaran DOKU belum terverifikasi. Sebelum ini
     * satu-satunya jalan mengirim bukti adalah WhatsApp panitia, dan bukti
     * yang menumpuk di satu nomor pribadi tidak pernah sampai ke baris
     * pendaftarannya — yang memeriksa harus mencocokkan tangkapan layar
     * dengan daftar, satu per satu.
     *
     * Berkasnya SELALU keluar sebagai WebP lewat App\Services\Gambar:
     * aslinya — JPG, PNG, atau HEIC dari iPhone — dibongkar, diperkecil, lalu
     * dihapus. Yang tersimpan di cakram hanya WebP-nya.
     */
    public function unggahBukti(Request $request, string $id)
    {
        $pendaftaran = WebinarEksklusifPendaftaran::whereKey($id)->first();

        if ($pendaftaran === null) {
            return $this->kembaliKeDaftar('Pendaftaran itu tidak ditemukan.');
        }

        /*
         * Diperiksa DI SINI, bukan cuma disembunyikan di tampilan.
         *
         * Borangnya memang tidak digambar untuk pendaftaran yang sudah lunas,
         * batal, atau kedaluwarsa — tetapi alamatnya tetap bisa dikirimi
         * permintaan oleh siapa pun yang pernah membuka halamannya, dan
         * menerima bukti untuk kursi yang sudah dilepas berarti menjanjikan
         * sesuatu yang tidak ada.
         */
        if ($pendaftaran->lunas) {
            return back()->with('error', 'Pembayaran Anda sudah lunas; buktinya tidak perlu lagi.');
        }

        if ($pendaftaran->status === 'cancel') {
            return back()->with('error', 'Pendaftaran ini sudah dibatalkan.');
        }

        if ($pendaftaran->sudah_kedaluwarsa || $pendaftaran->status === 'expired') {
            return back()->with('error',
                'Batas waktunya sudah lewat dan kursinya dilepas. Hubungi panitia kalau masih ingin ikut.');
        }

        /*
         * HEIC/HEIF ikut diterima — itu format BAWAAN kamera iPhone, dan
         * peserta yang memotret bukti transfernya di situ tidak tahu berkasnya
         * bukan JPG. Ditolak di sini, ia hanya melihat "format tidak
         * didukung" tanpa tahu harus berbuat apa.
         *
         * Diperiksa lewat AKHIRAN namanya, bukan jenis MIME-nya: finfo di
         * sebagian peladen memulangkan application/octet-stream untuk HEIC,
         * dan aturan mimes: akan menolak berkas yang sebetulnya sah. Isinya
         * sendiri tetap diperiksa — Gambar::simpan() memulangkan null kalau
         * yang diunggah ternyata bukan gambar yang bisa dibaca.
         */
        $request->validate([
            'bukti' => ['required', 'file', 'max:' . self::BUKTI_MAKS_KB,
                'extensions:jpg,jpeg,png,heic,heif'],
        ], [
            'bukti.required' => 'Pilih dulu berkas bukti transfernya.',
            'bukti.extensions' => 'Formatnya harus JPG, PNG, atau HEIC.',
            'bukti.max' => 'Berkasnya terlalu besar; paling besar '
                . (self::BUKTI_MAKS_KB / 1024) . ' MB.',
        ]);

        $lama = (string) $pendaftaran->gambar;

        $jalur = (new Gambar())->simpan($request->file('bukti'), 'bukti/' . self::KODE);

        if ($jalur === null) {
            /*
             * Yang paling mungkin: HEIC di peladen tanpa alat pembongkarnya.
             * Pesannya menyebut jalan keluar yang bisa dikerjakan peserta
             * sendiri, bukan "terjadi kesalahan".
             */
            return back()->with('error',
                'Berkasnya tidak bisa kami baca sebagai gambar. Kalau itu foto dari iPhone,'
                . ' coba kirim ulang sebagai JPG.');
        }

        $pendaftaran->forceFill(['gambar' => $jalur])->save();

        /*
         * Bukti yang DIGANTI ikut dibuang dari cakram. Tanpa ini tiap
         * unggahan ulang meninggalkan satu WebP yatim yang tidak ditunjuk
         * baris mana pun dan tidak pernah terhapus.
         */
        if ($lama !== '' && $lama !== $jalur && Storage::disk(Gambar::CAKRAM)->exists($lama)) {
            Storage::disk(Gambar::CAKRAM)->delete($lama);
        }

        /*
         * Kalimatnya PENDEK saja. Halaman yang ditujunya kini layar selesai
         * yang sudah menjelaskan semuanya — "panitia memeriksa pada jam
         * kerja", "tidak perlu mengirim apa pun lagi" — dan mengulanginya di
         * pita hijau membuat tiga kalimat yang sama bertumpuk di satu layar.
         */
        return back()->with('sukses', 'Bukti transfer Anda sudah kami terima.');
    }

    /**
     * Nama yang sudah dirapikan untuk DIBANDINGKAN, bukan untuk ditampilkan.
     *
     * Huruf kecil semua dan spasi gandanya dirapatkan. Dipakai bersama
     * dengan kueri penjaga pendaftaran ganda; kalau perapiannya di sini
     * berbeda dari yang di SQL, pembandingannya tidak akan pernah cocok dan
     * penjaganya diam-diam berhenti bekerja.
     */
    private function namaRapi(?string $nama): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $nama)));
    }

    /**
     * Email disamarkan di tengahnya sebelum ditampilkan.
     *
     * Halaman ini bisa dibuka siapa pun yang memegang tautannya — termasuk
     * tautan yang ikut tersalin saat dibagikan. Menulis alamat lengkapnya di
     * layar menyerahkan alamat orang lain kepada yang memegang tautan.
     */
    private function samarkanEmail(string $email): string
    {
        [$nama, $ranah] = array_pad(explode('@', $email, 2), 2, '');

        $tampak = mb_substr($nama, 0, min(2, max(1, mb_strlen($nama) - 1)));

        return $tampak . str_repeat('*', max(1, mb_strlen($nama) - mb_strlen($tampak)))
            . ($ranah !== '' ? '@' . $ranah : '');
    }

    /**
     * Mengirim bukti pendaftaran ke email pendaftar.
     *
     * Sebelum ini tidak ada pemberitahuan apa pun: orang hanya memegang
     * tautan status di layar, dan begitu tabnya ditutup, tautannya hilang —
     * padahal emailnya sudah kita minta sejak awal.
     *
     * DIBUNGKUS try/catch dengan sengaja. Pendaftarannya sudah tersimpan dan
     * kursinya sudah terpotong saat baris ini jalan; kalau peladen surat
     * sedang bermasalah, yang pantas terjadi adalah galatnya dicatat, bukan
     * orangnya melihat layar error padahal pendaftarannya berhasil.
     */
    private function kirimEmailPendaftaran(WebinarEksklusifPendaftaran $pendaftaran, KategoriLayanan $sesi): void
    {
        try {
            /*
             * Peserta tambahan yang mengisi email IKUT dikirimi.
             *
             * Emailnya sudah kita minta di borang; tidak mengirim apa pun ke
             * sana berarti meminta data yang tidak dipakai, dan orangnya tidak
             * punya bukti bahwa ia terdaftar. Yang tidak mengisi email tidak
             * dikirimi apa-apa — itu memang boleh dikosongkan.
             */
            $penerima = array_values(array_unique(array_filter(array_merge(
                [$pendaftaran->email],
                $pendaftaran->pesertaLain->pluck('email')->all()
            ))));

            Mail::to($penerima)->send(
                new WebinarEksklusifPendaftaranMail($pendaftaran, $sesi)
            );
        } catch (\Throwable $e) {
            Log::error('Email pendaftaran Webinar Eksklusif gagal dikirim', [
                'pendaftaran' => $pendaftaran->getKey(),
                'email' => $pendaftaran->email,
                'pesan' => $e->getMessage(),
            ]);
        }
    }

}

/**
 * Kuota tidak cukup untuk jumlah peserta yang diminta.
 *
 * Pengecualian tersendiri, bukan `return` di tengah closure: pemeriksaannya
 * ada DI DALAM transaksi, dan return dari sana akan menganggap transaksinya
 * berhasil sehingga kursi yang tidak ada tetap terpotong.
 */
class KuotaHabis extends \RuntimeException
{
    public function __construct(private int $sisa)
    {
        parent::__construct('kuota tidak cukup');
    }

    public function pesanUntukOrang(): string
    {
        return $this->sisa < 1
            ? 'Maaf, kuotanya baru saja penuh.'
            : 'Maaf, kursinya tinggal ' . $this->sisa . '. Kurangi jumlah pesertanya, ya.';
    }
}
