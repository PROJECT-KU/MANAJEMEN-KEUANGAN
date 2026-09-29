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
 * Tidak satu pun dari keempat tabel layanan ini menyimpan rujukan ke akun
 * pemesannya — kecuali Clinik Scopus, yang punya customer_id. Sisanya hanya
 * mencatat nama, email, dan nomor telepon yang diketik sendiri oleh pemesan.
 * Jadi penautannya memang menebak, dan yang bisa dikerjakan adalah menebak
 * dengan urutan yang paling kecil kemungkinan salahnya:
 *
 *   1. customer_id   rujukan sungguhan, tidak bisa salah
 *   2. nomor telepon dibakukan dulu; dilewati kalau dipakai lebih dari satu akun
 *   3. alamat email  dilewati kalau dipakai lebih dari satu akun
 *   4. nama          dilewati kalau dipakai lebih dari satu akun
 *
 * Nomor telepon didahulukan atas email karena pada data yang ada ia jauh lebih
 * sering cocok: terukur pada 183 baris pesanan, nomor telepon menautkan 29
 * baris, email menambah 3, dan nama tidak menambah satu pun. Orang mengganti
 * alamat emailnya; nomor teleponnya jauh lebih jarang berubah.
 *
 * Kunci yang menunjuk lebih dari satu akun TIDAK DIPAKAI sama sekali, bukan
 * dipakai untuk salah satunya. Pada data yang ada, lima nomor telepon dipakai
 * dua sampai tiga akun sekaligus — menautkannya ke salah satu berarti
 * menampilkan pesanan orang lain di halaman seseorang, dan itu lebih buruk
 * daripada tidak menampilkan apa-apa.
 *
 * TIDAK termasuk di sini:
 *
 * - Online Training. Layanannya dijalankan di luar sistem ini
 *   (rumahscopus.com/courses/online-class/) dan tidak punya tabel di basis
 *   data ini, jadi pesanannya tidak bisa dibaca dari sini sama sekali.
 * - Tabel `clinikscopus` dan `camp`. Keduanya punya kolom user_id, tetapi
 *   isinya KARYAWAN yang menginput barisnya, bukan pelanggan yang memesan;
 *   `camp` malah tabel biaya acara (gaji trainer, tiket), bukan pemesanan.
 */
class PesananPelanggan
{
    /**
     * Sumber pesanan: nama layanan, tabel, dan nama-nama kolomnya.
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
            'telp' => 'telp_pemesan',
            'email' => 'email_pemesan',
            'nama' => 'nama_pemesan',
            'customer_id' => 'customer_id',
            'ikon' => 'fa-user-md',
            'warna' => 'mis-biru',
        ],
        [
            'layanan' => 'Analisis Bibliometrik',
            'tabel' => 'analisis_bibliometrik',
            'nomor' => 'id_transaksi',
            'nilai' => 'total_pembayaran',
            'telp' => 'telp',
            'email' => 'email',
            'nama' => 'nama',
            'customer_id' => null,
            'ikon' => 'fa-chart-line',
            'warna' => 'mis-ungu',
        ],
        [
            'layanan' => 'Scopus Kafe',
            'tabel' => 'pendaftaran_scopus_kafe',
            'nomor' => 'id_pemesanan',
            'nilai' => 'total_keseluruhan_pembayaran',
            'telp' => 'telp',
            'email' => 'email',
            'nama' => 'nama',
            'customer_id' => null,
            // fa-mug-hot baru ada di Font Awesome 6; yang dibundel di sini
            // versi 5, jadi ikon Scopus Kafe selama ini kosong di layar lebar.
            'ikon' => 'fa-coffee',
            'warna' => 'mis-jingga',
        ],
        [
            'layanan' => 'Scopus Camp',
            'tabel' => 'scopus_camp_pendaftaran',
            'nomor' => 'id_transaksi',
            'nilai' => 'total_pembayaran',
            'telp' => 'telp',
            'email' => 'email',
            'nama' => 'nama',
            'customer_id' => null,
            'ikon' => 'fa-campground',
            'warna' => 'mis-hijau',
        ],
    ];

    /** Urutan pencocokan cadangan, dari yang paling dipercaya. */
    private const URUTAN_KUNCI = ['telp', 'email', 'nama'];

    /**
     * Nomor telepon dalam satu bentuk baku.
     *
     * Nomor yang sama ditulis bermacam-macam: "0858-4276-1933",
     * "085842761933", "+6285842761933", "6285842761933". Tanpa dibakukan,
     * keempatnya jadi empat orang yang berbeda — dan pada data yang ada
     * sebagian besar nomor pelanggan memang bertanda hubung.
     *
     * Mengembalikan untaian kosong untuk yang tidak bisa dipercaya: kurang
     * dari sembilan angka bukan nomor telepon melainkan sisa isian seperti
     * "-", "0", atau "12345". Nilai kosong tidak pernah dipakai mencocokkan,
     * jadi puluhan baris berisian kosong tidak saling mewarisi pesanan.
     */
    public static function nomorBaku(?string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor) ?? '';

        if ($angka === '') {
            return '';
        }

        // 62 di depan berarti kode negara; disamakan dengan penulisan lokal.
        if (str_starts_with($angka, '62')) {
            $angka = '0' . substr($angka, 2);
        } elseif (! str_starts_with($angka, '0')) {
            $angka = '0' . $angka;
        }

        return strlen($angka) >= 9 ? $angka : '';
    }

    /** Nama dalam satu bentuk baku: huruf kecil, spasi tunggal. */
    public static function namaBaku(?string $nama): string
    {
        $bersih = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $nama) ?? ''));

        // Nama sependek satu atau dua huruf tidak menunjuk siapa pun.
        return mb_strlen($bersih) >= 3 ? $bersih : '';
    }

    public static function emailBaku(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    /** Ketiga kunci pengenal milik satu akun. */
    private static function kunciAkun(object $orang): array
    {
        return [
            'telp' => self::nomorBaku($orang->telp ?? null),
            'email' => self::emailBaku($orang->email ?? null),
            'nama' => self::namaBaku($orang->full_name ?? null),
        ];
    }

    /**
     * Peta kunci pengenal -> id pemiliknya, HANYA untuk kunci yang menunjuk
     * tepat satu akun.
     *
     * Di sinilah aturan "kalau nomornya banyak yang sama, pakai cadangan"
     * dikerjakan: nomor yang dipakai dua akun tidak masuk peta sama sekali,
     * sehingga baris bernomor itu jatuh dengan sendirinya ke email, lalu ke
     * nama.
     */
    private static function indeks(Collection $pelanggan): array
    {
        $kumpul = ['telp' => [], 'email' => [], 'nama' => []];

        foreach ($pelanggan as $orang) {
            foreach (self::kunciAkun($orang) as $jenis => $nilai) {
                if ($nilai !== '') {
                    $kumpul[$jenis][$nilai][] = $orang->getKey();
                }
            }
        }

        $peta = ['telp' => [], 'email' => [], 'nama' => []];

        foreach ($kumpul as $jenis => $daftar) {
            foreach ($daftar as $nilai => $pemilik) {
                $unik = array_unique($pemilik);

                if (count($unik) === 1) {
                    $peta[$jenis][$nilai] = (int) reset($unik);
                }
            }
        }

        return $peta;
    }

    /**
     * Siapa pemilik satu baris pesanan — atau null kalau tidak ada yang cocok.
     *
     * Sebagian besar baris memang tidak punya pemilik: kebanyakan orang yang
     * memesan layanan tidak pernah mendaftar akun. Terukur, 151 dari 183 baris
     * tidak cocok dengan akun mana pun, dan itu wajar.
     */
    private static function pemilik(array $peta, array $sumber, object $baris, array $idPelanggan): ?int
    {
        // customer_id mendahului segalanya: ia rujukan sungguhan, bukan
        // tebakan dari untaian yang diketik orang. Tetap diperiksa apakah
        // menunjuk akun pelanggan — di tabel lain kolom serupa justru berisi
        // karyawan yang menginput barisnya.
        if ($sumber['customer_id'] !== null && ! empty($baris->kunci_id)) {
            $id = (int) $baris->kunci_id;

            if (isset($idPelanggan[$id])) {
                return $id;
            }
        }

        $kunci = [
            'telp' => self::nomorBaku($baris->kunci_telp ?? null),
            'email' => self::emailBaku($baris->kunci_email ?? null),
            'nama' => self::namaBaku($baris->kunci_nama ?? null),
        ];

        foreach (self::URUTAN_KUNCI as $jenis) {
            $nilai = $kunci[$jenis];

            if ($nilai !== '' && isset($peta[$jenis][$nilai])) {
                return $peta[$jenis][$nilai];
            }
        }

        return null;
    }

    /** Kolom pengenal satu sumber, dinamai seragam supaya bisa diperiksa bersama. */
    private static function pilihPengenal(array $sumber): array
    {
        $pilih = [
            DB::raw($sumber['telp'] . ' as kunci_telp'),
            DB::raw($sumber['email'] . ' as kunci_email'),
            DB::raw($sumber['nama'] . ' as kunci_nama'),
        ];

        if ($sumber['customer_id'] !== null) {
            $pilih[] = DB::raw($sumber['customer_id'] . ' as kunci_id');
        }

        return $pilih;
    }

    /** Seluruh akun pelanggan; dipakai untuk menyusun peta pengenal. */
    private static function semuaPelanggan(): Collection
    {
        return User::query()
            ->where('peran', User::PERAN_PELANGGAN)
            ->get(['id', 'full_name', 'email', 'telp']);
    }

    /** Seluruh pesanan satu pelanggan, terbaru di atas. */
    public static function untuk(User $pelanggan): Collection
    {
        /*
         * Peta seluruh pelanggan dibangun juga di sini, bukan cuma kunci milik
         * orang ini.
         *
         * Alasannya: sebuah baris bisa saja bernomor telepon milik orang ini
         * TETAPI nomor itu dipakai tiga akun. Tanpa peta lengkap hal itu tidak
         * ketahuan dari sini, dan halaman orang ini akan memuat pesanan dua
         * orang lain. Satu kueri tambahan atas tabel akun adalah harga murah
         * untuk itu.
         */
        $semua = self::semuaPelanggan();
        $peta = self::indeks($semua);
        $idPelanggan = array_fill_keys($semua->pluck('id')->all(), true);
        $sayaId = (int) $pelanggan->getKey();

        $hasil = collect();

        foreach (self::SUMBER as $s) {
            $baris = DB::table($s['tabel'])
                ->select(array_merge([
                    DB::raw($s['nomor'] . ' as nomor'),
                    DB::raw($s['nilai'] . ' as nilai'),
                    'status',
                    'created_at',
                ], self::pilihPengenal($s)))
                ->get();

            foreach ($baris as $b) {
                if (self::pemilik($peta, $s, $b, $idPelanggan) !== $sayaId) {
                    continue;
                }

                $hasil->push([
                    'layanan' => $s['layanan'],
                    'nomor' => $b->nomor,
                    'nilai' => (float) $b->nilai,
                    'status' => (string) $b->status,
                    // Rupa status dirakit di sini, bukan di tampilan: empat
                    // layanan memakai kosakata yang berbeda, dan memilah-
                    // milahnya di Blade berarti belasan @if di satu baris.
                    'rupa' => self::rupaStatus((string) $b->status),
                    'waktu' => $b->created_at ? Carbon::parse($b->created_at) : null,
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
     * Empat layanan mencatat statusnya dengan kosakata masing-masing, dan
     * kolomnya varchar bebas — bukan enum — jadi tidak ada daftar tertutup
     * yang dijamin basis datanya. Yang sah menurut borang tiap layanan:
     *
     *   Clinik Scopus          pending, paid, completed, canceled
     *   Analisis Bibliometrik  diproses, Pendaftaran Diterima / Ditolak /
     *   & Scopus Camp          Dibatalkan / Refund / Reschedule
     *   Scopus Kafe            menunggu verifikasi, pembayaran diterima,
     *                          pembayaran ditolak
     *
     * Tiga belas nilai, dua bahasa, tiga gaya huruf. Sebelum ini semuanya
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

            // masih menunggu tindakan orang.
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
     * Satu kueri per tabel untuk SELURUH halaman, bukan satu kueri per baris
     * pelanggan — dua belas baris akan jadi empat puluh delapan kueri.
     *
     * Penautannya dikerjakan di PHP, bukan di SQL, karena aturannya
     * bertingkat: kunci mana yang dipakai bergantung pada apakah kunci itu
     * menunjuk lebih dari satu akun, dan itu baru diketahui sesudah seluruh
     * akun dilihat. Tiap baris diselesaikan tepat sekali, jadi tidak ada baris
     * yang terhitung dua kali karena cocok lewat dua kunci sekaligus.
     *
     * Harganya: seluruh baris keempat tabel dibaca. Ratusan baris sekarang,
     * dan itu murah; kalau kelak mencapai puluhan ribu, penautannya perlu
     * dipindahkan ke SQL.
     *
     * @return array<int, array{jumlah:int, terakhir:?Carbon}>
     */
    public static function ringkas(Collection $pelanggan): array
    {
        if ($pelanggan->isEmpty()) {
            return [];
        }

        /*
         * Petanya dibangun dari SELURUH pelanggan, bukan cuma yang tampil di
         * halaman ini. Kalau hanya dari yang tampil, sebuah nomor yang
         * sebenarnya dipakai tiga akun bisa tampak menunjuk satu akun saja —
         * karena dua akun lainnya kebetulan ada di halaman berikutnya — dan
         * pesanan orang lain ikut terhitung.
         */
        $semua = self::semuaPelanggan();
        $peta = self::indeks($semua);
        $idPelanggan = array_fill_keys($semua->pluck('id')->all(), true);

        // Hanya yang diminta yang dikembalikan, walau petanya menyeluruh.
        $diminta = array_fill_keys($pelanggan->pluck('id')->all(), true);

        $ringkas = [];

        foreach (self::SUMBER as $s) {
            $baris = DB::table($s['tabel'])
                ->select(array_merge(['created_at'], self::pilihPengenal($s)))
                ->get();

            foreach ($baris as $b) {
                $id = self::pemilik($peta, $s, $b, $idPelanggan);

                if ($id === null || ! isset($diminta[$id])) {
                    continue;
                }

                $ada = $ringkas[$id] ?? ['jumlah' => 0, 'terakhir' => null];
                $ada['jumlah']++;

                $waktu = $b->created_at ? Carbon::parse($b->created_at) : null;

                if ($waktu && (! $ada['terakhir'] || $waktu->gt($ada['terakhir']))) {
                    $ada['terakhir'] = $waktu;
                }

                $ringkas[$id] = $ada;
            }
        }

        return $ringkas;
    }
}
