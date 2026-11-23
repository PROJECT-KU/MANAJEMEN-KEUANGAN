<?php

namespace App\Actions\Webinar;

use App\User;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Membuatkan akun MIS untuk peserta webinar yang pembayarannya sudah lunas.
 *
 * Gunanya satu: peserta perlu bisa masuk untuk menonton rekaman webinarnya
 * sesudah acara selesai. Tanpa akun, rekamannya cuma bisa dibagikan sebagai
 * tautan YouTube terbuka — dan tautan terbuka berarti yang tidak membayar
 * ikut menonton.
 *
 * ---------------------------------------------------------------------------
 * SEMUA PESERTA, bukan hanya pemesannya
 * ---------------------------------------------------------------------------
 *
 * Satu pendaftaran bisa memuat beberapa peserta, masing-masing dengan nama
 * dan email sendiri — itu sebabnya tabel pesertanya menyimpan email. Kalau
 * satu kampus mendaftarkan lima orang dan hanya pemesannya yang dapat akun,
 * keempat orang lain menonton dengan meminjam akun pemesannya, dan sandi satu
 * akun beredar di lima tangan.
 *
 * ---------------------------------------------------------------------------
 * EMAIL YANG SUDAH PUNYA AKUN TIDAK DISENTUH
 * ---------------------------------------------------------------------------
 *
 * Orang yang sama mendaftar dua webinar berarti dua pendaftaran dengan email
 * yang sama. Menyetel ulang sandinya jadi bawaan pada pendaftaran kedua akan
 * MENGUNCI orang itu keluar dari akunnya sendiri — sandi yang ia pilih dan
 * ia hafal tiba-tiba tidak berlaku, tanpa ada yang memberitahunya. Akun yang
 * sudah ada dipakai apa adanya; suratnya cuma mengingatkan nama penggunanya.
 *
 * ---------------------------------------------------------------------------
 * Sandi bawaannya SAMA untuk semua orang, dan itu keputusan sadar
 * ---------------------------------------------------------------------------
 *
 * Diminta pemiliknya supaya suratnya bisa menyebutkan sandi yang mudah
 * diketik orang yang tidak terbiasa. Akibatnya nyata: MIS masuk lewat
 * username, username peserta dirakit dari namanya, jadi selama sandinya
 * belum diganti, nama orang lain adalah kunci akun orang lain.
 *
 * Lubang itu ditutup oleh `wajib_ganti_sandi`, yang menahan akun di halaman
 * ganti sandi sampai ia memilih sandinya sendiri. Aturan sandi aplikasi ini
 * memakai uncompromised(), jadi "Password123" sendiri akan ditolak saat ia
 * mencoba memakainya lagi.
 */
class BuatAkunPeserta
{
    /**
     * Sandi bawaan, ditulis SEKALI di sini.
     *
     * Dipakai pembuat akun dan disebutkan surat. Dua salinan akan berselisih
     * perlahan, dan selisihnya berupa surat yang menyebutkan sandi yang tidak
     * pernah dipasang — orangnya tidak bisa masuk dan tidak ada yang tahu
     * kenapa.
     */
    public const SANDI_BAWAAN = 'Password123';

    /** Batas kolom users.username. */
    private const PANJANG_NAMA_PENGGUNA = 255;

    /**
     * @return list<array{nama:string, email:string, username:string, baru:bool}>
     *         Satu baris per peserta yang punya akun sesudah ini berjalan.
     */
    public function untuk(WebinarEksklusifPendaftaran $pendaftaran): array
    {
        $hasil = [];
        $sudah = [];

        foreach ($pendaftaran->semuaPeserta() as $peserta) {
            /*
             * Dinormalkan jadi huruf kecil.
             *
             * Orang yang sama mengetik "Budi@Kampus.ac.id" hari ini dan
             * "budi@kampus.ac.id" bulan depan. Tanpa penormalan, pencocokan
             * "sudah punya akun?" meleset dan ia dibuatkan akun KEDUA —
             * sampai MySQL menolaknya karena indeks uniknya memang tidak
             * peka huruf besar-kecil, dan penolakan itu jatuh di tengah
             * pemrosesan pembayaran.
             */
            $email = strtolower(trim((string) ($peserta['email'] ?? '')));

            /*
             * Peserta tanpa email DILEWATI, bukan dibuatkan akun bernama
             * karangan. Kolom email peserta memang boleh kosong — sebagian
             * pemesan mengisi nama rekannya saja — dan akun tanpa alamat
             * tidak bisa dikirimi apa pun, tidak bisa dipulihkan sandinya,
             * dan tidak akan pernah dipakai siapa pun.
             */
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Satu pendaftaran bisa memuat email yang sama dua kali (pemesan
            // mendaftarkan dirinya sendiri lagi sebagai peserta). Dihitung
            // sekali.
            if (isset($sudah[$email])) {
                continue;
            }

            $sudah[$email] = true;
            $nama = trim((string) ($peserta['nama'] ?? '')) ?: 'Peserta Webinar';

            $akun = User::where('email', $email)->first();

            if ($akun !== null) {
                $hasil[] = [
                    'nama' => $nama,
                    'email' => $email,
                    'username' => (string) $akun->username,
                    'baru' => false,
                ];

                continue;
            }

            $akun = $this->buat($nama, $email);

            $hasil[] = [
                'nama' => $nama,
                'email' => $email,
                'username' => (string) $akun->username,
                'baru' => true,
            ];
        }

        return $hasil;
    }

    private function buat(string $nama, string $email): User
    {
        $akun = User::create([
            'full_name' => Str::limit($nama, 250, ''),
            'username' => $this->namaPenggunaUnik($nama),
            'email' => $email,
            'password' => Hash::make(self::SANDI_BAWAAN),
            'level' => 'user',
            'peran' => User::PERAN_PELANGGAN,
        ]);

        /*
         * forceFill, sama seperti jalur pendaftaran yang sudah ada: kolom
         * status dan penanda verifikasi memang tidak ada di $fillable, dan
         * itu disengaja supaya tidak bisa disetel dari borang mana pun.
         *
         * email_verified_at diisi karena alamat ini SUDAH terbukti dipakai:
         * ke sanalah faktur dan tanda terima pembayarannya dikirim, dan
         * pembayarannya sudah masuk. Menahannya "belum terverifikasi" berarti
         * akun yang baru dikirim justru ditolak di pintu oleh middleware
         * `terverifikasi`.
         */
        $akun->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'wajib_ganti_sandi' => true,
        ])->save();

        return $akun;
    }

    /**
     * Nama pengguna dari nama orang, dijamin belum dipakai.
     *
     * `username` UNIK dan dipakai sebagai kunci masuk. Dua peserta bernama
     * "Ahmad Fauzi" bukan hal langka, dan tanpa penomoran di bawah, peserta
     * kedua GAGAL dibuatkan akun dengan galat kunci ganda — di tengah
     * pengiriman surat lunas, sesudah uangnya masuk.
     */
    private function namaPenggunaUnik(string $nama): string
    {
        $dasar = Str::slug($nama, '_');

        /*
         * Nama yang seluruhnya di luar huruf latin — atau cuma tanda baca —
         * membuat slug KOSONG, dan username kosong melanggar NOT NULL.
         * Gejalanya muncul jauh dari sebabnya.
         */
        if ($dasar === '') {
            $dasar = 'peserta';
        }

        // Disisakan ruang untuk akhiran "_99".
        $dasar = Str::limit($dasar, self::PANJANG_NAMA_PENGGUNA - 6, '');
        $calon = $dasar;
        $urutan = 1;

        while (User::where('username', $calon)->exists()) {
            $urutan++;
            $calon = $dasar . '_' . $urutan;

            /*
             * Jalan keluar darurat. Tanpa ini, tabrakan yang tak terduga
             * membuat putaran ini berjalan selamanya dan menggantungkan
             * permintaan yang sedang memproses pembayaran.
             */
            if ($urutan > 200) {
                $calon = $dasar . '_' . Str::lower(Str::random(6));
                break;
            }
        }

        return $calon;
    }
}
