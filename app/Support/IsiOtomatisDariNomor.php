<?php

namespace App\Support;

use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Data diri satu orang, dicari dari nomor WhatsApp-nya.
 *
 * Gunanya mengisi borang pendaftaran otomatis: orangnya mengetik nomor, dan
 * nama, email, serta asal instansinya terisi sendiri dari pesanan yang pernah
 * ia buat. Sasaran layanan ini orang berusia lanjut, dan empat isian yang
 * harus diketik di ponsel adalah tempat paling banyak orang berhenti.
 *
 * ---------------------------------------------------------------------------
 * MENCARI DI SELURUH LAYANAN, bukan di satu tabel
 * ---------------------------------------------------------------------------
 *
 * Yang pernah ikut Scopus Kafe lalu mendaftar Scopus Camp adalah orang yang
 * sama, dan datanya sudah ada. Mencari hanya di tabel layanan yang sedang
 * dibuka membuat sebagian besar pelanggan lama tidak pernah ketemu — dan
 * diamnya terbaca seperti ia memang belum pernah memesan apa pun.
 *
 * Yang TERBARU menang. Orang pindah instansi dan berganti alamat surat;
 * pesanan lima tahun lalu bukan keterangan yang ingin ia pakai hari ini.
 *
 * ---------------------------------------------------------------------------
 * Yang perlu diketahui siapa pun yang menyentuh kelas ini
 * ---------------------------------------------------------------------------
 *
 * Kelas ini MENUKAR NOMOR TELEPON JADI NAMA DAN EMAIL. Siapa pun yang bisa
 * memanggil alamat yang memakainya bisa memanen keduanya dengan mencoba nomor
 * satu per satu. Itu diterima sebagai harga kemudahan ini — keputusan yang
 * sudah diambil saat jalur yang sama dibuat untuk Webinar Eksklusif — dengan
 * dua syarat yang TIDAK BOLEH dilepas di pemanggilnya:
 *
 *   1. alamatnya dibatasi jumlah permintaan per menit per IP;
 *   2. nomornya dicatat TERSAMAR di log, tidak pernah utuh.
 *
 * Yang dipulangkan sengaja cuma tiga medan. Jangan tambah nomor pesanan,
 * riwayat, atau nominal: borangnya tidak membutuhkannya, dan tiap medan baru
 * di sini jadi medan baru yang bisa dipanen.
 */
class IsiOtomatisDariNomor
{
    /**
     * Tabel yang ditengok, beserta nama kolomnya masing-masing.
     *
     * Ditulis sebagai data, bukan lima kueri: kelimanya memakai nama kolom
     * yang berbeda untuk hal yang sama — Clinik Scopus memakai akhiran
     * `_pemesan`, dan Scopus Kafe tidak punya kolom asal instansi sama
     * sekali. Satu salinan kueri per tabel akan berselisih diam-diam.
     *
     * @var array<int, array{tabel:string, nama:string, email:string, telp:string, afiliasi:?string}>
     */
    private const SUMBER = [
        ['tabel' => 'scopus_camp_pendaftaran', 'nama' => 'nama', 'email' => 'email',
         'telp' => 'telp', 'afiliasi' => 'affiliasi'],

        ['tabel' => 'webinar_eksklusif_pendaftaran', 'nama' => 'nama', 'email' => 'email',
         'telp' => 'telp', 'afiliasi' => 'affiliasi'],

        ['tabel' => 'analisis_bibliometrik', 'nama' => 'nama', 'email' => 'email',
         'telp' => 'telp', 'afiliasi' => 'affiliasi'],

        ['tabel' => 'clinikscopus_pemesanan', 'nama' => 'nama_pemesan', 'email' => 'email_pemesan',
         'telp' => 'telp_pemesan', 'afiliasi' => 'afiliasi_pemesan'],

        // Scopus Kafe memang tidak menyimpan asal instansi. Ditulis null,
        // bukan dibiarkan kosong: kolom yang tidak ada akan melempar
        // "Unknown column" saat kuerinya dirakit.
        ['tabel' => 'pendaftaran_scopus_kafe', 'nama' => 'nama', 'email' => 'email',
         'telp' => 'telp', 'afiliasi' => null],
    ];

    /**
     * Satu orang yang nomornya cocok, atau null kalau tidak ketemu.
     *
     * @return array{nama:string, email:string, affiliasi:?string, sumber:string}|null
     */
    public function cari(?string $nomorMentah): ?array
    {
        $nomor = NomorTelepon::rapikan((string) $nomorMentah);

        if (! NomorTelepon::masukAkal($nomor)) {
            return null;
        }

        /*
         * Dicocokkan ke SEMUA bentuk yang mungkin tersimpan, bukan satu.
         *
         * Kolom telp diisi bertahun-tahun oleh layar yang berbeda: ada yang
         * "6281...", ada "0812...", ada "+62 812-...". Mencari satu bentuk
         * saja membuat sebagian orang tidak pernah ketemu, dan diamnya
         * terbaca seperti ia memang belum pernah memesan.
         */
        $bentuk = NomorTelepon::semuaBentuk($nomor);

        $dari = $this->dariPesanan($bentuk);

        if ($dari !== null) {
            return $dari;
        }

        return $this->dariAkun($bentuk);
    }

    /**
     * Pembersih tanda baca nomor, sebagai ungkapan SQL.
     *
     * Dikerjakan basis data, bukan di PHP: tanpa ini seluruh isi kelima
     * tabel harus ditarik dulu untuk dibandingkan satu per satu.
     */
    private function bersih(string $kolom): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE($kolom, '+', ''), '-', ''), ' ', ''), '.', '')";
    }

    /**
     * @param  array<int, string>  $bentuk
     * @return array{nama:string, email:string, affiliasi:?string, sumber:string}|null
     */
    private function dariPesanan(array $bentuk): ?array
    {
        $isian = implode(',', array_fill(0, count($bentuk), '?'));
        $terbaru = null;

        foreach (self::SUMBER as $s) {
            /*
             * Tabel yang belum ada DILEWATI, bukan dibiarkan melempar.
             *
             * Layanan boleh dibuang — tabel pendaftaran per layanan memang
             * sudah pernah dihapus di proyek ini — dan borang pendaftaran
             * yang 500 karena tabel layanan LAIN sudah tidak ada adalah
             * kegagalan yang sama sekali tidak masuk akal bagi yang
             * membukanya.
             */
            if (! \Illuminate\Support\Facades\Schema::hasTable($s['tabel'])) {
                continue;
            }

            $baris = DB::table($s['tabel'])
                ->selectRaw(
                    $s['nama'] . ' as nama, ' . $s['email'] . ' as email, '
                    . ($s['afiliasi'] === null ? 'NULL' : $s['afiliasi']) . ' as affiliasi, '
                    . 'created_at'
                )
                ->whereRaw($this->bersih($s['telp']) . " IN ($isian)", $bentuk)
                ->whereNotNull($s['nama'])
                ->whereNotNull($s['email'])
                ->orderByDesc('created_at')
                ->first();

            if ($baris === null) {
                continue;
            }

            // Yang TERBARU di antara kelima tabel, bukan yang pertama ketemu.
            // Urutan daftar SUMBER di atas tidak boleh ikut menentukan
            // keterangan siapa yang dipakai.
            if ($terbaru === null || (string) $baris->created_at > (string) $terbaru->created_at) {
                $terbaru = $baris;
            }
        }

        if ($terbaru === null) {
            return null;
        }

        return [
            'nama' => (string) $terbaru->nama,
            'email' => (string) $terbaru->email,
            'affiliasi' => $terbaru->affiliasi,
            'sumber' => 'pesanan',
        ];
    }

    /**
     * Akun pengguna, kalau tidak ada pesanan yang cocok.
     *
     * Didahului pesanan dengan sengaja: isi pesanan persis sebentuk dengan
     * borang ini — nama beserta gelar, asal instansi — sedangkan nama di akun
     * sering sekadar "admin" atau "staff".
     *
     * @param  array<int, string>  $bentuk
     * @return array{nama:string, email:string, affiliasi:?string, sumber:string}|null
     */
    private function dariAkun(array $bentuk): ?array
    {
        $isian = implode(',', array_fill(0, count($bentuk), '?'));

        $akun = User::query()
            ->whereRaw($this->bersih('telp') . " IN ($isian)", $bentuk)
            ->get(['full_name', 'email', 'company']);

        /*
         * Lebih dari satu akun berarti nomornya dipakai bersama — dan tidak
         * ada cara memilih yang benar dari sini. Didiamkan, bukan ditebak:
         * borang yang terisi nama ORANG LAIN jauh lebih buruk daripada
         * borang yang tetap kosong.
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
}
