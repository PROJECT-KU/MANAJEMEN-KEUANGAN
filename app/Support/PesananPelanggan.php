<?php

namespace App\Support;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menyatukan pesanan seorang pelanggan dari beberapa tabel layanan.
 *
 * Tiap layanan mencatat pemesannya dengan caranya sendiri, dan caranya tidak
 * seragam:
 *
 *   clinikscopus_pemesanan   customer_id  (rujukan sungguhan ke users)
 *   analisis_bibliometrik    email        (untaian, bukan rujukan)
 *   pendaftaran_scopus_kafe  email        (untaian, bukan rujukan)
 *
 * Dua tabel lain — clinikscopus dan camp — punya kolom user_id, tetapi isinya
 * KARYAWAN yang menginput barisnya, bukan pelanggan yang memesan. Diperiksa
 * pada data yang ada: tidak satu pun user_id di kedua tabel itu menunjuk akun
 * berperan pelanggan. Karena itu keduanya sengaja TIDAK ikut di sini —
 * memasukkannya hanya akan menampilkan pesanan milik orang lain.
 *
 * Pencocokan lewat email memang rapuh: begitu pelanggan mengganti alamatnya,
 * jejak lamanya putus. Itu keterbatasan datanya, bukan keputusan di sini, dan
 * disebutkan apa adanya di layar supaya angkanya tidak dikira pasti.
 */
class PesananPelanggan
{
    /**
     * Sumber pesanan: nama layanan, tabel, kolom penomoran, nilai, dan waktu.
     *
     * Didaftar sebagai data, bukan ditulis berulang sebagai kueri, supaya
     * menambah layanan berikutnya cukup menambah satu baris di sini.
     */
    private const SUMBER = [
        [
            'layanan' => 'Clinik Scopus',
            'tabel' => 'clinikscopus_pemesanan',
            'nomor' => 'kode_booking',
            'nilai' => 'total_pembayaran',
            'email' => 'email_pemesan',
            'punya_customer_id' => true,
            'ikon' => 'fa-user-md',
            'warna' => 'mis-biru',
        ],
        [
            'layanan' => 'Analisis Bibliometrik',
            'tabel' => 'analisis_bibliometrik',
            'nomor' => 'id_transaksi',
            'nilai' => 'total_pembayaran',
            'email' => 'email',
            'punya_customer_id' => false,
            'ikon' => 'fa-chart-line',
            'warna' => 'mis-ungu',
        ],
        [
            'layanan' => 'Scopus Kafe',
            'tabel' => 'pendaftaran_scopus_kafe',
            'nomor' => 'id_pemesanan',
            'nilai' => 'total_keseluruhan_pembayaran',
            'email' => 'email',
            'punya_customer_id' => false,
            'ikon' => 'fa-mug-hot',
            'warna' => 'mis-jingga',
        ],
    ];

    /** Seluruh pesanan satu pelanggan, terbaru di atas. */
    public static function untuk(User $pelanggan): Collection
    {
        $email = mb_strtolower(trim((string) $pelanggan->email));
        $hasil = collect();

        foreach (self::SUMBER as $s) {
            $q = DB::table($s['tabel'])
                ->select([
                    DB::raw("'" . addslashes($s['layanan']) . "' as layanan"),
                    DB::raw($s['nomor'] . ' as nomor'),
                    DB::raw($s['nilai'] . ' as nilai'),
                    'status',
                    'created_at',
                ]);

            $q->where(function ($sub) use ($s, $pelanggan, $email) {
                if ($s['punya_customer_id']) {
                    $sub->orWhere('customer_id', $pelanggan->getKey());
                }

                if ($email !== '') {
                    $sub->orWhereRaw('LOWER(' . $s['email'] . ') = ?', [$email]);
                }
            });

            foreach ($q->get() as $baris) {
                $hasil->push([
                    'layanan' => $baris->layanan,
                    'nomor' => $baris->nomor,
                    'nilai' => (float) $baris->nilai,
                    'status' => (string) $baris->status,
                    // Rupa status dirakit di sini, bukan di tampilan: tiga
                    // layanan memakai kosakata yang berbeda, dan memilah-
                    // milahnya di Blade berarti belasan @if di satu baris.
                    'rupa' => self::rupaStatus((string) $baris->status),
                    'waktu' => $baris->created_at ? Carbon::parse($baris->created_at) : null,
                    'ikon' => $s['ikon'],
                    'warna' => $s['warna'],
                ]);
            }
        }

        return $hasil->sortByDesc(fn ($p) => $p['waktu']?->timestamp ?? 0)->values();
    }

    /**
     * Warna, label Indonesia, dan ikon untuk satu nilai status.
     *
     * Tiga layanan mencatat statusnya dengan kosakata masing-masing, dan
     * kolomnya varchar bebas — bukan enum — jadi tidak ada daftar tertutup
     * yang dijamin basis datanya. Yang sah menurut borang tiap layanan:
     *
     *   Clinik Scopus          pending, paid, completed, canceled
     *   Analisis Bibliometrik  diproses, Pendaftaran Diterima / Ditolak /
     *                          Dibatalkan / Refund / Reschedule
     *   Scopus Kafe            menunggu verifikasi, pembayaran diterima,
     *                          pembayaran ditolak
     *
     * Lima belas nilai, dua bahasa, tiga gaya huruf. Sebelum ini semuanya
     * tampil sebagai satu lencana abu-abu yang sama, sehingga pesanan yang
     * sudah lunas tidak terbedakan dari yang dibatalkan tanpa membaca
     * tulisannya satu per satu.
     *
     * Empat yang berbahasa Inggris diterjemahkan: ini layar orang dalam yang
     * belum tentu paham "canceled", dan sisa layarnya berbahasa Indonesia.
     *
     * Sesudah daftar di atas ada penebak kata kunci. Ia bukan hiasan: karena
     * kolomnya varchar, layanan berikutnya bisa menulis nilai baru kapan saja
     * tanpa migrasi, dan yang belum terdaftar lebih baik jatuh ke warna yang
     * masuk akal daripada ke abu-abu.
     *
     * @return array{label:string, warna:string, ikon:string}
     */
    public static function rupaStatus(string $status): array
    {
        // Dinormalkan supaya "Pendaftaran Diterima" dan "pendaftaran  diterima"
        // tidak jadi dua keadaan yang berbeda.
        $kunci = mb_strtolower(trim(preg_replace('/\s+/', ' ', $status) ?? ''));

        $daftar = [
            // selesai / uangnya sudah masuk
            'completed' => ['Selesai', 'hijau', 'fa-check-circle'],
            'paid' => ['Sudah dibayar', 'hijau', 'fa-check-circle'],
            'pendaftaran diterima' => ['Pendaftaran diterima', 'hijau', 'fa-check-circle'],
            'pembayaran diterima' => ['Pembayaran diterima', 'hijau', 'fa-check-circle'],

            // masih menunggu tindakan orang
            // "Belum dibayar" dan bukan "Menunggu pembayaran": lebih pendek
            // 42px pada lebar ponsel tersempit, dan kalimatnya lebih lugas
            // untuk yang tidak biasa membaca istilah sistem.
            'pending' => ['Belum dibayar', 'kuning', 'fa-clock'],
            'menunggu verifikasi' => ['Menunggu verifikasi', 'kuning', 'fa-clock'],

            // sedang berjalan
            'diproses' => ['Diproses', 'biru', 'fa-spinner'],
            'pendaftaran reschedule' => ['Dijadwalkan ulang', 'biru', 'fa-calendar-alt'],

            // tidak jadi
            'canceled' => ['Dibatalkan', 'merah', 'fa-times-circle'],
            'cancelled' => ['Dibatalkan', 'merah', 'fa-times-circle'],
            'pendaftaran dibatalkan' => ['Pendaftaran dibatalkan', 'merah', 'fa-times-circle'],
            'pendaftaran ditolak' => ['Pendaftaran ditolak', 'merah', 'fa-ban'],
            'pembayaran ditolak' => ['Pembayaran ditolak', 'merah', 'fa-ban'],

            // uangnya kembali — bukan gagal, tapi juga bukan selesai
            'pendaftaran refund' => ['Dana dikembalikan', 'ungu', 'fa-undo'],
        ];

        if (isset($daftar[$kunci])) {
            [$label, $warna, $ikon] = $daftar[$kunci];

            return ['label' => $label, 'warna' => $warna, 'ikon' => $ikon];
        }

        /*
         * Penebak kata kunci, diperiksa berurutan.
         *
         * Urutannya penting: "pembayaran ditolak" memuat kata "bayar" DAN
         * kata "tolak". Yang menggagalkan diperiksa lebih dulu supaya
         * pesanan yang ditolak tidak tampil hijau.
         */
        $tebakan = [
            ['tolak|batal|gagal|cancel|reject|fail|expired|kedaluwarsa', 'merah', 'fa-times-circle'],
            ['refund|kembali', 'ungu', 'fa-undo'],
            ['tunggu|pending|belum', 'kuning', 'fa-clock'],
            ['proses|jadwal|schedule|kerja', 'biru', 'fa-spinner'],
            ['terima|selesai|lunas|bayar|paid|complete|sukses|success|verif', 'hijau', 'fa-check-circle'],
        ];

        foreach ($tebakan as [$pola, $warna, $ikon]) {
            if (preg_match('/' . $pola . '/', $kunci)) {
                return [
                    'label' => Str::title($kunci),
                    'warna' => $warna,
                    'ikon' => $ikon,
                ];
            }
        }

        // Benar-benar tidak dikenali. Abu-abu, bukan warna yang menebak-nebak:
        // lencana hijau pada keadaan yang tidak dipahami lebih menyesatkan
        // daripada lencana netral.
        return [
            'label' => $kunci === '' ? 'Tanpa status' : Str::title($kunci),
            'warna' => 'abu',
            'ikon' => 'fa-circle-notch',
        ];
    }

    /**
     * Ringkasan untuk daftar: jumlah pesanan dan tanggal terakhir, per
     * pelanggan.
     *
     * Dihitung dengan tiga kueri berkelompok untuk SELURUH halaman, bukan tiga
     * kueri per baris — dua belas baris akan jadi tiga puluh enam kueri.
     *
     * @return array<int, array{jumlah:int, terakhir:?Carbon}>
     */
    public static function ringkas(Collection $pelanggan): array
    {
        if ($pelanggan->isEmpty()) {
            return [];
        }

        $id = $pelanggan->pluck('id')->all();

        // Peta email -> id, supaya hasil yang berkunci email bisa dikembalikan
        // ke pemiliknya. Email kosong dilewati: kalau tidak, semua pelanggan
        // tanpa email akan saling mewarisi pesanan.
        $petaEmail = [];
        foreach ($pelanggan as $p) {
            $e = mb_strtolower(trim((string) $p->email));
            if ($e !== '') {
                $petaEmail[$e] = $p->id;
            }
        }

        $ringkas = [];
        $tambah = function ($idPelanggan, $jumlah, $terakhir) use (&$ringkas) {
            if (! $idPelanggan) {
                return;
            }

            $ada = $ringkas[$idPelanggan] ?? ['jumlah' => 0, 'terakhir' => null];
            $ada['jumlah'] += (int) $jumlah;
            $waktu = $terakhir ? Carbon::parse($terakhir) : null;

            if ($waktu && (! $ada['terakhir'] || $waktu->gt($ada['terakhir']))) {
                $ada['terakhir'] = $waktu;
            }

            $ringkas[$idPelanggan] = $ada;
        };

        foreach (self::SUMBER as $s) {
            $surel = 'LOWER(' . $s['email'] . ')';

            /*
             * SATU kueri per tabel, bukan satu untuk customer_id dan satu lagi
             * untuk email.
             *
             * Baris di clinikscopus_pemesanan bisa cocok lewat KEDUANYA
             * sekaligus — customer_id-nya benar dan email pemesannya sama.
             * Dengan dua kueri terpisah, baris itu terhitung dua kali dan
             * jumlah pesanannya jadi dobel. Dikelompokkan bersama, tiap baris
             * hanya masuk satu kelompok.
             */
            $kueri = DB::table($s['tabel'])
                ->selectRaw(
                    ($s['punya_customer_id'] ? 'customer_id' : 'NULL') . ' AS pemilik, '
                    . $surel . ' AS surel, COUNT(*) AS jumlah, MAX(created_at) AS terakhir'
                )
                ->where(function ($q) use ($s, $id, $petaEmail, $surel) {
                    if ($s['punya_customer_id']) {
                        $q->orWhereIn('customer_id', $id);
                    }

                    if (! empty($petaEmail)) {
                        $q->orWhereIn(DB::raw($surel), array_keys($petaEmail));
                    }
                })
                ->groupBy(DB::raw(($s['punya_customer_id'] ? 'customer_id, ' : '') . $surel));

            foreach ($kueri->get() as $b) {
                // customer_id lebih dipercaya daripada email: ia rujukan
                // sungguhan, sedangkan email bisa sudah diganti pemiliknya.
                $pemilik = (isset($b->pemilik) && in_array((int) $b->pemilik, $id, true))
                    ? (int) $b->pemilik
                    : ($petaEmail[$b->surel] ?? null);

                $tambah($pemilik, $b->jumlah, $b->terakhir);
            }
        }

        return $ringkas;
    }
}
