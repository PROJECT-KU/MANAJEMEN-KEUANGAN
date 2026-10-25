<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mengirimkan kembali tautan halaman status pendaftaran Scopus Camp.
 *
 * Alamat halaman status memuat UUID yang tidak bisa ditebak — itu yang
 * menjaga keterangan satu orang tidak terbuka bagi yang menebak nomor
 * urut. Harganya: yang menutup tabnya dan tidak menerima surat buktinya
 * kehilangan pendaftarannya untuk selamanya, termasuk tombol bayarnya.
 *
 * Surat ini jalan kembalinya, dan jalannya SENGAJA lewat email: tautannya
 * tidak pernah ditampilkan di layar kepada yang memintanya. Ditampilkan,
 * siapa pun yang menebak alamat email orang lain bisa membuka
 * pendaftarannya — di halaman itu ada nama, nomor telepon, afiliasi, dan
 * daftar pesertanya.
 */
class TautanPendaftaranScopusCampMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection<int, \App\PendaftaranScopusCamp>  $pendaftaran
     */
    public function __construct(public $pendaftaran) {}

    public function build()
    {
        return $this->subject('Tautan pendaftaran Scopus Camp Anda')
            ->view('emails.scopus_camp.tautan');
    }
}
