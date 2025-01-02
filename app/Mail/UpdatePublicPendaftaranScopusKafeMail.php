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
        $logoPath = public_path('assets/img/LogoRSC.png');

        return $this->view('account.pendaftaran_scopus_kafe.send_email_sukses')
            ->subject('Pembayaran Scopus Kafe Diterima')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
