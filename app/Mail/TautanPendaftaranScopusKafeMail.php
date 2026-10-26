<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mengirimkan kembali tautan halaman status pemesanan Scopus Kafe.
 *
 * Pasangan TautanPendaftaranScopusCampMail, untuk alasan yang sama: alamat
 * halaman status memuat UUID yang tidak bisa ditebak, dan itulah yang
 * menjaga nama, nomor telepon, serta sesi seseorang tidak terbuka bagi yang
 * menebak nomor urut. Harganya: yang menutup tabnya dan tidak menerima
 * suratnya kehilangan pemesanannya, termasuk tombol bayarnya.
 *
 * Jalannya SENGAJA lewat email: tautannya tidak pernah ditampilkan di layar
 * kepada yang memintanya. Ditampilkan, siapa pun yang menebak alamat email
 * orang lain bisa membuka pemesanannya.
 */
class TautanPendaftaranScopusKafeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection<int, \App\PendaftaranScopusKafe>  $pemesanan
     */
    public function __construct(public $pemesanan) {}

    public function build()
    {
        return $this->subject('Tautan pemesanan Scopus Kafe Anda')
            ->view('emails.scopus_kafe.tautan');
    }
}
