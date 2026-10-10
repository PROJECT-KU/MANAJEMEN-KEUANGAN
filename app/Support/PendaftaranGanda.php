<?php

namespace App\Support;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;

/**
 * Pendaftaran lain atas nama orang yang sama.
 *
 * Orang yang mengira borangnya gagal lalu mengisi ulang meninggalkan dua
 * baris yang tidak saling tahu — dan dua kursi terpakai untuk satu orang.
 * Terukur paling sering pada angkatan yang kuotanya ketat: kursinya habis
 * di layar sementara ada yang sebenarnya masih kosong.
 *
 * Dicocokkan lewat EMAIL atau NOMOR, bukan nama. Nama yang sama tidak
 * berarti orang yang sama — "Muhammad Rizki" ada belasan — sedangkan nama
 * yang berbeda sering justru orang yang sama ("Rizki" vs "M. Rizki").
 * Email dan nomor adalah satu-satunya yang menunjuk orang.
 */
class PendaftaranGanda
{
    /** Secukupnya untuk menyadari ada yang ganda; bukan daftar riwayat. */
    public const BATAS = 6;

    /**
     * Pendaftaran lain milik orang yang sama, di SELURUH layanan.
     *
     * Lintas layanan, bukan satu layanan saja: orang yang sama mendaftar
     * Scopus Camp dan Bibliometrik itu wajar dan bukan masalah — tetapi
     * panitia yang sedang memeriksa pembayarannya tetap perlu tahu, sebab
     * satu transfer bisa saja dimaksudkan untuk keduanya.
     *
     * @return Collection<int, object>
     */
    public static function lain(string $layanan, string $id, ?string $email, ?string $telp): Collection
    {
        $email = mb_strtolower(trim((string) $email));

        /*
         * Nomornya dicari dalam SEMUA bentuk yang mungkin tersimpan.
         *
         * Kolomnya diisi bertahun-tahun oleh layar yang berbeda, jadi satu
         * orang bisa tersimpan sebagai "6281…", "0811…", atau
         * "+62 811-…". Dicari satu bentuk saja, sebagian besar yang ganda
         * tidak pernah ketemu — dan diamnya terbaca sebagai "tidak ada yang
         * ganda", bukan sebagai pencarian yang meleset.
         */
        $bentuk = NomorTelepon::semuaBentuk($telp);

        if ($email === '' && $bentuk === []) {
            return collect();
        }

        return Pendaftaran::kueri()
            ->where(function ($q) use ($email, $bentuk) {
                if ($email !== '') {
                    $q->orWhereRaw('LOWER(email) = ?', [$email]);
                }

                if ($bentuk !== []) {
                    /*
                     * Kolomnya dibersihkan DI SQL, bukan dicocokkan apa
                     * adanya: nomornya tersimpan seperti diketik —
                     * "0811-2233-0001", "+62 811-2233-0001" — sementara
                     * bentuk yang dicari berangka saja. Dicocokkan langsung,
                     * tidak ada satu pun yang pernah cocok, dan diamnya
                     * terbaca sebagai "tidak ada yang ganda".
                     */
                    $q->orWhereIn(
                        \Illuminate\Support\Facades\DB::raw(NomorTelepon::tanpaTandaSql('telp')),
                        $bentuk
                    );
                }
            })
            // Dirinya sendiri dibuang DI SINI, bukan disaring sesudahnya:
            // dengan batas enam baris, barisnya sendiri memakan satu slot
            // dan yang keenam tidak pernah terlihat.
            ->whereRaw('NOT (layanan = ? AND id = ?)', [$layanan, $id])
            ->orderByDesc('waktu')
            ->limit(self::BATAS)
            ->get();
    }
}
