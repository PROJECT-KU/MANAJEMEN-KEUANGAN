<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Harga pemesanan Scopus Kafe, DIHITUNG DI PELADEN.
 *
 * Sebelum ini `biaya`, `kode_unik_pembayaran`, `subtotal_pembayaran`, dan
 * `total_keseluruhan_pembayaran` datang dari kiriman borang lalu disimpan
 * mentah — tujuh medan uang yang seluruhnya ditentukan sisi yang tidak bisa
 * dipercaya. Siapa pun bisa memesan tiga sesi dengan total nol tanpa satu
 * baris pun yang menolak, dan panitia baru tahu saat mencocokkan mutasi.
 *
 * Perkara yang sama sudah dibetulkan di Scopus Camp lewat HargaUmum. Kelas
 * ini pasangannya untuk layanan yang TIDAK berangkatan: harganya tidak
 * datang dari satu baris angkatan melainkan dari sesi-sesi yang dipilih.
 *
 * Tidak memakai HargaUmum: rumus di sana menuntut KategoriLayanan — satu
 * angkatan, satu harga satuan, dikali jumlah peserta. Scopus Kafe tidak punya
 * angkatan sama sekali; yang dijumlahkan beberapa sesi yang harganya boleh
 * berbeda-beda.
 */
class HargaKafe
{
    /** Paling banyak tiga sesi dalam satu pemesanan; tabelnya memang segitu. */
    public const MAKS_SESI = 3;

    /**
     * Sesi yang boleh dipesan, dibaca dari katalognya sendiri.
     *
     * Hanya yang berstatus 'publish'. Sesi yang ditarik panitia tidak boleh
     * dipesan lewat alamat borang yang masih tersimpan di tab seseorang.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function sesiTersedia()
    {
        return DB::table('meme')
            ->where('status', 'publish')
            ->orderBy('waktu_mulai')

            /*
             * Pemutus seri: dua sesi yang jam mulainya sama tidak punya
             * urutan yang ditentukan tanpa baris ini, dan urutannya boleh
             * berubah antar permintaan — jadi nomor sesi yang dilihat orang
             * di borang bisa berbeda dari yang ia lihat sedetik sebelumnya.
             */
            ->orderBy('id')
            ->get();
    }

    /**
     * Rincian harga untuk sesi-sesi yang dipilih.
     *
     * HARGANYA DIBACA DARI KATALOG, bukan dari kiriman borang. Yang diterima
     * dari borang cuma KUNCI sesinya — dan kunci yang tidak ada di katalog
     * dilewati, bukan dihargai nol.
     *
     * @param  array<int, string>  $kunciSesi
     * @return array{sesi: array<int, object>, subtotal: int, kode_unik: int, total: int}
     */
    public static function hitung(array $kunciSesi, $kodeUnikDikirim = null): array
    {
        $katalog = self::sesiTersedia()->keyBy('id');

        $dipilih = [];

        foreach ($kunciSesi as $kunci) {
            $kunci = (string) $kunci;

            /*
             * Kunci yang tidak ada di katalog DILEWATI, bukan dihargai nol:
             * dihargai nol, pemesanan berisi satu sesi sungguhan dan dua sesi
             * karangan tetap tersimpan dan tagihannya kurang.
             *
             * Sesi kembar tidak perlu ditolak terpisah — $dipilih DIKUNCI
             * pada kunci sesinya, jadi yang sama menimpa dirinya sendiri.
             * Pemeriksaan isset() tambahan sempat ada di sini lalu dicabut:
             * ia tidak bisa dibuat merah oleh uji mana pun, sebab yang
             * dijanjikannya sudah dijamin bentuk lariknya.
             */
            if ($kunci === '' || ! isset($katalog[$kunci])) {
                continue;
            }

            $dipilih[$kunci] = $katalog[$kunci];

            if (count($dipilih) >= self::MAKS_SESI) {
                break;
            }
        }

        $subtotal = 0;

        foreach ($dipilih as $sesi) {
            $subtotal += self::rupiah($sesi->biaya);
        }

        /*
         * Kode uniknya dihitung SEKALI ke dalam peubah. Dipanggil dua kali —
         * sekali untuk kolomnya, sekali di dalam penjumlahan total — keduanya
         * akan memberi angka berbeda, dan nominal yang ditagihkan tidak
         * berakhir dengan kode yang tersimpan di sebelahnya.
         */
        $kodeUnik = self::kodeUnik($kodeUnikDikirim);

        return [
            'sesi' => array_values($dipilih),
            'subtotal' => $subtotal,
            'kode_unik' => $kodeUnik,
            'total' => max(0, $subtotal + $kodeUnik),
        ];
    }

    /**
     * "1.000.000" jadi 1000000.
     *
     * Kolom `biaya` di katalog sesinya varchar dan isinya memang bertitik —
     * terukur: "1.000.000". Dibaca apa adanya sebagai angka, PHP memulangkan
     * 1 dan seluruh sesi berharga satu rupiah.
     */
    public static function rupiah($nilai): int
    {
        return (int) preg_replace('/\D+/', '', (string) $nilai);
    }

    /**
     * Kode unik yang dipakai: yang dikirim borangnya kalau masuk akal, kalau
     * tidak dibuat baru.
     *
     * Dipercaya mentah, siapa pun bisa mengirim 0 dan menghapus satu-satunya
     * penanda yang membedakan dua pemesanan bernominal sama di mutasi
     * rekening — atau mengirim angka besar dan menaikkan tagihannya sendiri.
     */
    private static function kodeUnik($dikirim): int
    {
        [$min, $maks] = PendaftaranSemuaLayanan::KODE_UNIK;

        $angka = (int) preg_replace('/\D+/', '', (string) $dikirim);

        return $angka >= $min && $angka <= $maks ? $angka : random_int($min, $maks);
    }

    /**
     * Hari penyelenggaraannya: Jumat, Sabtu, Minggu.
     *
     * Keputusan pemiliknya 26 Okt 2026. Layanan ini tidak berangkatan — ia
     * terbuka terus — tetapi hanya berjalan di akhir pekan, jadi tanggal yang
     * dipilih pemesan harus salah satu dari ketiganya. Tanpa aturan ini
     * orang memesan hari Selasa, panitia menolaknya dengan tangan lewat
     * WhatsApp, dan pemesannya mengulang dari nol.
     *
     * @return array<int, int>  nomor hari ISO (1=Senin … 7=Minggu)
     */
    public const HARI_BUKA = [5, 6, 7];

    /** Nama harinya untuk ditulis di layar. */
    public static function namaHariBuka(): string
    {
        return 'Jumat, Sabtu, atau Minggu';
    }

    /** Tanggal itu hari penyelenggaraan atau bukan. */
    public static function hariBuka($tanggal): bool
    {
        try {
            return in_array(
                (int) \Illuminate\Support\Carbon::parse($tanggal)->dayOfWeekIso,
                self::HARI_BUKA,
                true
            );
        } catch (\Throwable) {
            return false;
        }
    }
}
