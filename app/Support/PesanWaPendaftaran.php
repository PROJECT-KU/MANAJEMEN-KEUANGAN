<?php

namespace App\Support;

use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

/**
 * Teks siap pakai untuk tombol WhatsApp di layar pendaftaran.
 *
 * Sebelum ini tombolnya membuka percakapan KOSONG. Panitia lalu mengetik
 * ulang nama, nomor pendaftaran, nominal, dan kode uniknya — padahal
 * keempatnya sudah tergambar di baris yang tombolnya baru saja ditekan.
 * Yang paling sering salah ketik justru kode uniknya, dan transfer yang
 * nominalnya meleset tidak bisa dicocokkan sama sekali.
 *
 * Teksnya DRAF, bukan kiriman otomatis: WhatsApp membuka kotak ketik yang
 * sudah terisi dan panitia masih bisa menyuntingnya sebelum mengirim.
 */
class PesanWaPendaftaran
{
    /**
     * @param  object  $b  satu baris hasil Pendaftaran::kueri(), atau model
     *                     yang sudah diberi nama_orang/nomor/total/layanan
     */
    public static function untuk(object $b, ?string $sesi = null): string
    {
        /*
         * Didelegasikan: dulu di sini ada percabangan sendiri (menunggu /
         * lunas / selain itu diam) yang cuma memuat dua dari lima belas
         * status. Pendaftar yang dihubungi saat statusnya "Dijadwalkan
         * ulang" menerima sapaan tanpa satu kata pun tentang jadwalnya.
         */
        return self::tindakLanjut($b, $sesi);
    }

    /**
     * Pesan tindak lanjut untuk STATUS yang sedang berlaku.
     *
     * Kalimatnya dirakit dari KabarStatusPendaftaran — sumber yang sama
     * dengan surat emailnya. Ditulis ulang di sini, dua saluran yang
     * mengabarkan hal yang sama akan berbeda bunyi perlahan, dan pendaftar
     * yang membaca keduanya tidak tahu mana yang berlaku.
     *
     * Dipakai tombol "Follow up" di kartu status, dan juga tombol WhatsApp
     * di daftar maupun kartu identitas lewat untuk().
     */
    /**
     * Kalimat penutup per KEADAAN, khusus WhatsApp.
     *
     * Penutup milik suratnya TIDAK dipakai ulang di sini. Isinya ditulis
     * untuk email — "balas email ini", "lewat email yang sama" — dan
     * menambalnya kata per kata dengan str_replace rapuh: sekali kalimatnya
     * diubah di sana, tambalannya lolos dan pendaftar disuruh membalas email
     * yang tidak sedang ia buka. Terbukti saat dicoba: dua kalimat lolos
     * pada percobaan pertama.
     *
     * Enam keadaan, bukan lima belas status: yang perlu dikerjakan pendaftar
     * sesudah membaca pesan ini sama untuk semua status dalam satu keadaan.
     */
    private const PENUTUP = [
        'menunggu' => 'Kalau sudah transfer, kirimkan bukti transfernya ke nomor ini ya 🙏',
        'lunas' => 'Simpan pesan ini sebagai bukti ya. Sampai bertemu di acaranya 🙏',
        'jadwal' => 'Kalau jadwal barunya ternyata tidak bisa Anda ikuti, balas pesan ini ya — '
            . 'akan kami carikan jalan keluarnya.',
        'refund' => 'Kalau sampai seminggu dananya belum juga masuk, balas pesan ini ya — '
            . 'akan kami telusuri.',
        'tidak_jadi' => 'Kalau menurut Anda ini keliru, balas pesan ini ya — '
            . 'panitia akan memeriksanya lagi.',
        'lain' => 'Kalau ada yang ingin ditanyakan, balas pesan ini ya.',
    ];

    /**
     * Pesan tindak lanjut untuk STATUS yang sedang berlaku.
     *
     * Kalimat keadaannya diambil dari KabarStatusPendaftaran — sumber yang
     * sama dengan surat emailnya. Ditulis ulang di sini, dua saluran yang
     * mengabarkan hal yang sama akan berbeda bunyi perlahan, dan pendaftar
     * yang membaca keduanya tidak tahu mana yang berlaku.
     *
     * @param  string|null  $sesi  nama sesi/angkatan, kalau ada
     */
    public static function tindakLanjut(object $b, ?string $sesi = null): string
    {
        $status = (string) ($b->status ?? '');
        $kabar = KabarStatusPendaftaran::untuk($status);
        $keadaan = Pendaftaran::keadaanDari($status);

        $nama = trim((string) ($b->nama_orang ?? ''));
        $layanan = Pendaftaran::katalog()[$b->layanan]['nama'] ?? 'Rumah Scopus Foundation';
        $nomor = trim((string) ($b->nomor ?? ''));
        $total = (int) ($b->total ?? 0);

        $baris = [($nama === '' ? 'Halo' : 'Halo ' . $nama) . ' 👋'];
        $baris[] = '';
        $baris[] = 'Saya panitia ' . $layanan . ' Rumah Scopus Foundation.';

        /*
         * Status yang belum punya kalimat tidak menghasilkan pesan setengah
         * jadi: kalimat keadaannya dilewati, sapaan dan rinciannya tetap
         * terkirim. Panitia masih bisa mengetik sisanya sendiri — jauh lebih
         * baik daripada tombol yang mati.
         */
        if ($kabar !== null) {
            $baris[] = '';
            /*
             * Kalimat pembuka milik status 'Pendaftaran Reschedule' berbunyi
             * "Rinciannya ada di bawah ini" — benar di surat yang memang
             * punya tabel di bawahnya. Di sini rinciannya menyusul sebagai
             * baris Nomor dan Sesi, jadi janjinya tetap ditepati.
             */
            $baris[] = $kabar['pembuka'];
        }

        $rincian = array_filter([
            $nomor === '' ? null : 'Nomor pendaftaran: *' . strtoupper($nomor) . '*',
            ($sesi === null || trim($sesi) === '') ? null : 'Sesi: ' . trim($sesi),
            ($keadaan === 'menunggu' && $total > 0)
                ? 'Nominal: *Rp ' . number_format($total, 0, ',', '.') . '*'
                : null,
        ]);

        if ($rincian !== []) {
            $baris[] = '';
            foreach ($rincian as $r) {
                $baris[] = $r;
            }
        }

        if ($keadaan === 'menunggu' && $total > 0 && (int) ($b->kode_unik ?? 0) > 0) {
            $baris[] = '';
            $baris[] = 'Mohon ditransfer *persis* sampai angka terakhir — tiga angka di '
                . 'belakang itu kode unik Anda, dan itu yang kami pakai untuk mencocokkan '
                . 'pembayaran Anda.';
        }

        $baris[] = '';
        $baris[] = self::PENUTUP[$keadaan] ?? self::PENUTUP['lain'];

        return implode("\n", $baris);
    }

    /** Sapaan singkat untuk peserta rombongan, yang bukan pendaftarnya. */
    public static function peserta(?string $nama, string $layanan): string
    {
        $sapaan = trim((string) $nama) === '' ? 'Halo' : 'Halo ' . trim((string) $nama);

        return $sapaan . " 👋\n\nSaya panitia "
            . (Pendaftaran::katalog()[$layanan]['nama'] ?? 'Rumah Scopus Foundation')
            . ' Rumah Scopus Foundation.';
    }
}
