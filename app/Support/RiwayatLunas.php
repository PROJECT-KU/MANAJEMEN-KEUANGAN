<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pernahkah satu nomor WhatsApp ikut sebuah layanan SAMPAI LUNAS.
 *
 * Dipakai promo bersyarat riwayat: alumni Webinar Eksklusif dapat potongan
 * saat mendaftar Scopus Camp. Yang membedakannya dari sekadar "pernah
 * mendaftar" adalah syarat LUNAS-nya, dan itu bukan kerewelan — keputusan
 * pemiliknya 31 Okt 2026, sebab pendaftaran yang tidak pernah dibayar tidak
 * membuat siapa pun jadi alumni. Dengan syarat yang longgar, satu orang bisa
 * membuat pendaftaran palsu di layanan termurah, membiarkannya kedaluwarsa,
 * lalu memakai potongannya selamanya di layanan termahal.
 *
 * Nomornya dicocokkan dalam SEMUA BENTUK yang lazim ditulis — "0812…",
 * "62812…", "+62 812-…" — sebab kolom telp diisi bertahun-tahun oleh layar
 * yang berbeda-beda. Dicocokkan satu bentuk saja, alumni yang nomornya
 * tersimpan dalam ejaan lain tidak pernah ketemu, dan diamnya terbaca
 * seperti ia memang belum pernah ikut.
 */
class RiwayatLunas
{
    /**
     * Nomor ini pernah lunas di SALAH SATU layanan yang disebut.
     *
     * Salah satu, bukan semuanya: admin yang mencentang tiga layanan
     * bermaksud "alumni mana pun dari ketiganya", bukan "yang pernah ikut
     * ketiga-tiganya" — syarat terakhir itu hampir tidak pernah terpenuhi
     * siapa pun dan akan terbaca sebagai promo yang rusak.
     *
     * @param  list<string>  $layanan  kunci layanan; kosong = tanpa syarat
     */
    public static function pernahLunas(?string $telp, array $layanan): bool
    {
        if ($layanan === []) {
            return true;
        }

        $bentuk = NomorTelepon::semuaBentuk($telp);

        if ($bentuk === []) {
            return false;
        }

        foreach ($layanan as $kunci) {
            if (self::lunasDi((string) $kunci, $bentuk)) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $bentuk */
    private static function lunasDi(string $layanan, array $bentuk): bool
    {
        $sumber = PendaftaranSemuaLayanan::sumber($layanan);

        if ($sumber === null) {
            return false;
        }

        $tabel = (string) ($sumber['tabel'] ?? '');
        $kolomTelp = (string) ($sumber['kolom']['telp'] ?? '');

        /*
         * Tabel yang belum ada DILEWATI, bukan dibiarkan melempar.
         *
         * Layanan boleh dibuang — tabel pendaftaran per layanan memang sudah
         * pernah dihapus di proyek ini — dan borang pendaftaran yang 500
         * karena tabel layanan LAIN tidak ada lagi adalah kegagalan yang
         * sama sekali tidak masuk akal bagi yang membukanya.
         */
        if ($tabel === '' || $kolomTelp === '' || ! Schema::hasTable($tabel)) {
            return false;
        }

        /*
         * Status lunasnya dibaca dari KEADAAN, bukan ditulis ulang di sini.
         *
         * Tiap layanan memakai kosakata sendiri — 'Pendaftaran Diterima',
         * 'paid', 'pembayaran diterima', 'completed'. Daftar yang disalin ke
         * sini akan ketinggalan pada hari layanan berikutnya datang dengan
         * kata keenam, dan promonya diam-diam berhenti berlaku untuk alumni
         * layanan itu.
         */
        $lunas = PendaftaranSemuaLayanan::KEADAAN['lunas']['nilai'];

        $isian = implode(',', array_fill(0, count($bentuk), '?'));

        return DB::table($tabel)
            ->whereIn('status', $lunas)
            ->whereRaw(NomorTelepon::tanpaTandaSql($kolomTelp) . " IN ($isian)", $bentuk)
            ->exists();
    }
}
