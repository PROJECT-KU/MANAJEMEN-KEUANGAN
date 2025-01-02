<?php

namespace App\Mail;

use App\PendaftaranScopusKafe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatePublicPendaftaranScopusKafeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $datas;
    public $pendaftaranScopusKafe;
    public $appName;

    /**
     * Create a new message instance.
     *pusKafe $pendaftaranScopusKafe
     * @param string $appName
     * @param bool $isStatus
     * @return void
     */
    public function __construct($datas, PendaftaranScopusKafe $pendaftaranScopusKafe, $appName)
    {
        $this->datas = $datas;
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

        return $this->view('public.scopus_kafe.send_email_sukses')
            ->subject('Pendaftaran Scopus Kafe Berhasil Terkirim')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
