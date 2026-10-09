<?php

namespace App\Support;

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
    public static function untuk(object $b): string
    {
        $nama = trim((string) ($b->nama_orang ?? ''));
        $sapaan = $nama === '' ? 'Halo' : 'Halo ' . $nama;
        $layanan = Pendaftaran::katalog()[$b->layanan]['nama'] ?? 'Rumah Scopus Foundation';
        $nomor = trim((string) ($b->nomor ?? ''));
        $total = (int) ($b->total ?? 0);
        $keadaan = Pendaftaran::keadaanDari($b->status ?? null);

        $baris = [$sapaan . ' 👋'];
        $baris[] = '';
        $baris[] = 'Saya panitia ' . $layanan . ' Rumah Scopus Foundation.';

        if ($nomor !== '') {
            $baris[] = 'Nomor pendaftaran Anda: *' . strtoupper($nomor) . '*';
        }

        if ($keadaan === 'menunggu' && $total > 0) {
            $baris[] = '';
            $baris[] = 'Pembayaran Anda belum kami terima. Nominalnya *Rp '
                . number_format($total, 0, ',', '.') . '* 💳';

            /*
             * Peringatan pembulatan hanya kalau memang ada kode uniknya.
             * Untuk nominal bulat, kalimat "jangan dibulatkan" cuma
             * membingungkan — tidak ada yang bisa dibulatkan.
             */
            if ((int) ($b->kode_unik ?? 0) > 0) {
                $baris[] = 'Mohon ditransfer *persis* sampai angka terakhir — '
                    . 'tiga angka di belakang itu kode unik Anda, dan itu yang kami pakai '
                    . 'untuk mencocokkan pembayaran Anda.';
            }

            $baris[] = '';
            $baris[] = 'Kalau sudah, kirimkan bukti transfernya ke nomor ini ya 🙏';
        } elseif ($keadaan === 'lunas') {
            $baris[] = '';
            $baris[] = 'Pembayaran Anda sudah kami terima. Terima kasih 🙏';
        }

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
