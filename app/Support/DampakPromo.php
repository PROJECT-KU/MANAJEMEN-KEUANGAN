<?php

namespace App\Support;

use App\PromoLayanan;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * Berapa pendaftaran yang kena sebuah promo, dan berapa rupiah yang dipotong.
 *
 * Pertanyaan pertama yang ditanyakan siapa pun tentang promo adalah "dipakai
 * berapa orang, kita korbankan berapa?" — dan sebelum ini tidak ada satu layar
 * pun yang bisa menjawabnya, padahal datanya sudah tersimpan sejak pendaftaran
 * pertama yang kena promo.
 *
 * Dihitung dari `promo_id`, BUKAN dari untaian nama di `kode_diskon`.
 * Menghitung dari nama akan berbohong begitu promonya diganti nama: seluruh
 * pendaftaran lama berhenti terhitung, tanpa galat dan tanpa ada yang curiga —
 * angkanya tinggal turun.
 *
 * Satu-satunya pintu untuk angka ini. Ia dipakai layar rincian promo DAN
 * penjaga kuota; dua tempat yang menghitungnya sendiri cepat atau lambat
 * menjawab berbeda untuk promo yang sama.
 */
class DampakPromo
{
    /**
     * promo_id => ['pendaftaran' => n, 'peserta' => n, 'potongan' => rupiah]
     *
     * @var array<string, array<string, int>>|null
     */
    private static ?array $ingatan = null;

    /** Dibuang uji yang mengubah pendaftarannya di tengah jalan. */
    public static function lupakan(): void
    {
        self::$ingatan = null;
    }

    /**
     * Layanan yang pendaftarannya bisa kena promo.
     *
     * Dibaca dari katalog, bukan ditulis ulang di sini: layanan yang nanti
     * ditambahkan beserta tabel pendaftarannya akan ikut terhitung tanpa ada
     * yang perlu ingat memperbarui daftar di tempat kedua.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function sumberBerpromo(): array
    {
        $keluar = [];

        foreach (array_keys(Pendaftaran::katalog()) as $layanan) {
            $sumber = Pendaftaran::sumber($layanan);

            if ($sumber === null || empty($sumber['berangkatan'])) {
                continue;
            }

            // Tabel yang belum punya kolom talinya dilewati — bukan dianggap
            // nol. Nol yang berbohong lebih berbahaya daripada kolom hilang.
            if (! \Illuminate\Support\Facades\Schema::hasColumn($sumber['tabel'], 'promo_id')) {
                continue;
            }

            $keluar[$layanan] = $sumber;
        }

        return $keluar;
    }

    /**
     * Angka ringkas untuk SEMUA promo sekaligus, dalam satu kueri per tabel.
     *
     * Dibaca per promo, layar daftar dengan 10 promo menjalankan 30 kueri —
     * tiga tabel dikali sepuluh baris.
     *
     * @return array<string, array<string, int>>
     */
    public static function semua(): array
    {
        if (self::$ingatan !== null) {
            return self::$ingatan;
        }

        $hasil = [];

        foreach (self::sumberBerpromo() as $sumber) {
            $kolom = $sumber['kolom'];

            $baris = DB::table($sumber['tabel'])
                ->whereNotNull('promo_id')
                ->selectRaw(
                    'promo_id,'
                    . ' count(*) as j_daftar,'
                    // Kolomnya varchar di sebagian tabel; dijumlahkan apa
                    // adanya, "1" dan "1 orang" ikut terbaca berbeda.
                    . ' sum(coalesce(cast(' . $kolom['jumlah'] . ' as unsigned), 1)) as j_peserta,'
                    . ' sum(coalesce(cast(' . $kolom['nominal_diskon'] . ' as unsigned), 0)) as j_potong'
                )
                ->groupBy('promo_id')
                ->get();

            foreach ($baris as $b) {
                $id = (string) $b->promo_id;

                $hasil[$id] ??= ['pendaftaran' => 0, 'peserta' => 0, 'potongan' => 0];
                $hasil[$id]['pendaftaran'] += (int) $b->j_daftar;
                $hasil[$id]['peserta'] += (int) $b->j_peserta;
                $hasil[$id]['potongan'] += (int) $b->j_potong;
            }
        }

        return self::$ingatan = $hasil;
    }

    /** Angka ringkas satu promo; nol kalau belum pernah dipakai. */
    public static function untuk(string $promoId): array
    {
        return self::semua()[$promoId] ?? ['pendaftaran' => 0, 'peserta' => 0, 'potongan' => 0];
    }

    /** Berapa PENDAFTARAN yang sudah memakai promo ini; dipakai penjaga kuota. */
    public static function terpakai(string $promoId): int
    {
        return self::untuk($promoId)['pendaftaran'];
    }

    /**
     * Rincian per angkatan untuk satu promo.
     *
     * Bukan sekadar totalnya: promo yang menyasar enam angkatan dan ternyata
     * cuma dipakai di satu angkatan adalah keterangan yang mengubah keputusan
     * promo berikutnya — dan itu tidak terlihat dari angka gabungan.
     *
     * @return list<array<string, mixed>>
     */
    public static function perAngkatan(PromoLayanan $promo): array
    {
        $keluar = [];

        foreach (self::sumberBerpromo() as $layanan => $sumber) {
            $kolom = $sumber['kolom'];

            $baris = DB::table($sumber['tabel'] . ' as p')
                ->leftJoin('kategori_layanan as k', 'k.id', '=', 'p.' . $kolom['angkatan_id'])
                ->where('p.promo_id', $promo->getKey())
                ->selectRaw(
                    'p.' . $kolom['angkatan_id'] . ' as kategori_id,'
                    . ' k.nama as nama_angkatan, k.nama_ke,'
                    . ' count(*) as j_daftar,'
                    . ' sum(coalesce(cast(p.' . $kolom['jumlah'] . ' as unsigned), 1)) as j_peserta,'
                    . ' sum(coalesce(cast(p.' . $kolom['nominal_diskon'] . ' as unsigned), 0)) as j_potong'
                )
                ->groupBy('p.' . $kolom['angkatan_id'], 'k.nama', 'k.nama_ke')
                ->get();

            foreach ($baris as $b) {
                $keluar[] = [
                    'layanan' => $layanan,
                    'nama_layanan' => $sumber['nama'],
                    'kategori_id' => $b->kategori_id,
                    // Angkatan yang sudah dihapus tetap ditampilkan apa
                    // adanya; dibuang, jumlah per angkatan tidak lagi
                    // menjumlah ke totalnya dan angkanya terbaca salah hitung.
                    'nama' => $b->nama_angkatan ?: 'Angkatan yang sudah dihapus',
                    'ke' => $b->nama_ke,
                    'pendaftaran' => (int) $b->j_daftar,
                    'peserta' => (int) $b->j_peserta,
                    'potongan' => (int) $b->j_potong,
                ];
            }
        }

        // Yang paling besar potongannya di atas: itu yang pertama dicari.
        usort($keluar, fn ($a, $b) => $b['potongan'] <=> $a['potongan']);

        return $keluar;
    }
}
