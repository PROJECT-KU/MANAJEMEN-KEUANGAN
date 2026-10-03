<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Services\Doku;
use App\Support\NomorTelepon;
use App\User;
use App\WebinarEksklusifPendaftaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\WebinarEksklusifPendaftaranMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $sudahAda = WebinarEksklusifPendaftaran::where('kategori_id', $sesi->getKey())
            ->where('email', mb_strtolower(trim($data['email'])))
            ->whereIn('status', ['pending', 'paid'])
            ->where(function ($q) {
                // Yang sudah dibayar selalu dihitung; yang masih menunggu
                // hanya selama belum lewat batas waktunya.
                $q->where('status', 'paid')
                    ->orWhereNull('kedaluwarsa_pada')
                    ->orWhere('kedaluwarsa_pada', '>', now());
            })
            ->latest()
            ->first();

        if ($sudahAda !== null) {
            return redirect()->route('public.webinareksklusif.status', $sudahAda->getKey())
                ->with('error', $sudahAda->status === 'paid'
                    ? 'Email itu sudah terdaftar di sesi ini. Ini rincian pendaftaran Anda.'
                    : 'Email itu sudah punya pendaftaran yang belum dibayar di sesi ini. '
                        . 'Lanjutkan yang ini saja supaya kursinya tidak terpotong dua kali.');
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

        return view('public.webinar_eksklusif.status', [
            'pendaftaran' => $pendaftaran,
            'sesi' => $pendaftaran->angkatan,
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
