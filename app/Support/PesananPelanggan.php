<?php

namespace App\Support;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
                    'waktu' => $baris->created_at ? Carbon::parse($baris->created_at) : null,
                    'ikon' => $s['ikon'],
                    'warna' => $s['warna'],
                ]);
            }
        }

        return $hasil->sortByDesc(fn ($p) => $p['waktu']?->timestamp ?? 0)->values();
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
