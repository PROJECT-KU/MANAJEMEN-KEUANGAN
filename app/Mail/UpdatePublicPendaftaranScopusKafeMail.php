<?php

namespace App\Mail;

use App\PendaftaranScopusKafe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdatePublicPendaftaranScopusKafeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $isStatus;
    public $pendaftaranScopusKafe;
    public $appName;

    /**
     * Create a new message instance.
     *pusKafe $pendaftaranScopusKafe
     * @param string $appName
     * @param bool $isStatus
     * @return void
     */
    public function __construct($data, PendaftaranScopusKafe $pendaftaranScopusKafe, $appName, $isStatus)
    {
        $this->isStatus = $isStatus;
        $this->data = $data;
        $this->pendaftaranScopusKafe = $pendaftaranScopusKafe;
        $this->appName = $appName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Kwitansinya dilampirkan, menggantikan logo 121.370 bita yang dulu
        // ikut terkirim tanpa dirujuk isi suratnya.
        $kwitansi = \App\Support\LampiranDokumen::rakit(
            'scopus_kafe', 'kwitansi', $this->pendaftaranScopusKafe
        );

        return $this->view('account.pendaftaran_scopus_kafe.send_email_sukses')
            ->subject('Pembayaran diterima — ' . $this->pendaftaranScopusKafe->id_pemesanan)
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($kwitansi['isi'], $kwitansi['nama'], ['mime' => 'application/pdf']);
    }
}
