<?php

namespace App\Http\Controllers\Publict;

use App\Http\Controllers\Controller;
use App\KategoriLayanan;
use App\Services\Doku;
use App\SharingSessionPendaftaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Halaman publik Sharing Session: borang pendaftaran dan pembayarannya.
 *
 * Halaman pemasarannya sendiri ada di subdomain tersendiri dan mengambil
 * datanya lewat ApiSharingSessionController. Yang di sini bagian yang tidak
 * boleh ada di halaman statis: borang yang menulis ke basis data, penjaga
 * kuota, dan jalur pembayaran.
 */
class PublicSharingSessionController extends Controller
{
    private const KODE = 'sharing_session';

    public function __construct(private Doku $doku)
    {
    }

    // ------------------------------------------------------------- daftar

    /** Semua sesi yang sedang dibuka. */
    public function index()
    {
        KategoriLayanan::hitungPendaftar();

        return view('public.sharing_session.index', [
            'sesi' => $this->kueriAktif()->get(),
        ]);
    }

    /**
     * Borang pendaftaran satu sesi.
     *
     * Token ikut diperiksa, bukan id saja. Id-nya UUID jadi tidak bisa
     * ditebak, tetapi alamat borang beredar lewat iklan dan grup WhatsApp —
     * token memberi jalan menutup satu tautan yang bocor tanpa menyentuh yang
     * lain.
     */
    public function daftar(string $id, string $token)
    {
        $sesi = $this->temukan($id, $token);

        if ($sesi === null) {
            return $this->kembaliKeDaftar('Sesi itu sudah tidak dibuka lagi.');
        }

        KategoriLayanan::hitungPendaftar();

        return view('public.sharing_session.form_pendaftaran', [
            'sesi' => $sesi,
            'tarif' => $sesi->tarif(),
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
            'telp' => ['required', 'string', 'max:30'],
            'affiliasi' => ['nullable', 'string', 'max:160'],
            'jumlah_pendaftar' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'nama.required' => 'Nama lengkapnya diisi dulu, ya.',
            'email.required' => 'Emailnya diisi dulu — tautan Zoom dikirim ke sana.',
            'email.email' => 'Alamat emailnya belum benar.',
            'telp.required' => 'Nomor WhatsApp-nya diisi dulu, ya.',
            'jumlah_pendaftar.min' => 'Minimal satu peserta.',
            'jumlah_pendaftar.max' => 'Lebih dari 50 peserta, hubungi panitia dulu ya.',
        ]);

        /*
         * validate() TIDAK memuat kunci yang tidak dikirim peramban, dan
         * "asal instansi" memang boleh dikosongkan. Dibaca langsung,
         * $data['affiliasi'] melempar "Undefined array key" — galat yang
         * hanya muncul pada orang yang mengosongkannya, jadi gampang lolos
         * dari pemeriksaan manual.
         */
        $data['affiliasi'] = $data['affiliasi'] ?? null;

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
        try {
            $pendaftaran = DB::transaction(function () use ($sesi, $data) {
                $terkunci = KategoriLayanan::whereKey($sesi->getKey())->lockForUpdate()->first();

                $sisa = $terkunci->total_kuota === null ? null : (int) $terkunci->sisa_kuota;

                if ($sisa !== null && $sisa < $data['jumlah_pendaftar']) {
                    throw new KuotaHabis($sisa);
                }

                $harga = (int) $terkunci->biaya;
                $total = $harga * $data['jumlah_pendaftar'];

                $pendaftaran = SharingSessionPendaftaran::create([
                    'kategori_id' => $terkunci->getKey(),
                    'nama' => trim($data['nama']),
                    'email' => mb_strtolower(trim($data['email'])),
                    'telp' => $this->rapikanTelp($data['telp']),
                    'affiliasi' => $data['affiliasi'] ? trim($data['affiliasi']) : null,
                    'jumlah_pendaftar' => $data['jumlah_pendaftar'],
                    'total_pembayaran' => (string) $total,
                    'cara_bayar' => $this->doku->siap() ? 'doku' : 'transfer',
                    'status' => 'pending',
                    'kedaluwarsa_pada' => now()->addMinutes(Doku::MENIT_KEDALUWARSA),
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
    private function mulaiBayar(SharingSessionPendaftaran $pendaftaran, KategoriLayanan $sesi)
    {
        if (! $this->doku->siap()) {
            return redirect()->route('public.sharingsession.status', $pendaftaran->token);
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
            'kembali' => route('public.sharingsession.status', $pendaftaran->token),
        ]);

        if (! $hasil['berhasil'] || ! $hasil['url']) {
            $pendaftaran->forceFill([
                'cara_bayar' => 'transfer',
                'note' => 'Pembayaran daring gagal dibuat: ' . ($hasil['pesan'] ?? 'tidak diketahui'),
            ])->save();

            return redirect()->route('public.sharingsession.status', $pendaftaran->token)
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
    public function status(string $token)
    {
        $pendaftaran = SharingSessionPendaftaran::where('token', $token)->first();

        if ($pendaftaran === null) {
            return $this->kembaliKeDaftar('Pendaftaran itu tidak ditemukan.');
        }

        return view('public.sharing_session.status', [
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

        $pendaftaran = SharingSessionPendaftaran::where('id_transaksi', $nomor)->first();

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
    private function kembalikanKuota(SharingSessionPendaftaran $pendaftaran): void
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

    private function temukan(string $id, string $token): ?KategoriLayanan
    {
        return $this->kueriAktif()->whereKey($id)->where('token', $token)->first();
    }

    private function kembaliKeDaftar(string $pesan)
    {
        return redirect()->route('public.sharingsession.index')->with('error', $pesan);
    }

    /** Nomor telepon disimpan berangka saja supaya bisa langsung jadi tautan WA. */
    private function rapikanTelp(string $telp): string
    {
        $angka = preg_replace('/\D+/', '', $telp);

        return preg_replace('/^0/', '62', (string) $angka);
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
