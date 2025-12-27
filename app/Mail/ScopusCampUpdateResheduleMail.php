<?php

namespace App\Mail;

use App\PendaftaranScopusCamp;
use App\CategoriesScopusCamp; // Sesuai dengan controller
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScopusCampUpdateResheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesScopusCamp;
    public $appName;
    public $status; // Property untuk menyimpan status

    /**
     * Create a new message instance.
     *
     * @param PendaftaranScopusCamp $pendaftaran
     * @param CategoriesScopusCamp $categoriesScopusCamp
     * @param string $appName
     * @return void
     */
    public function __construct(PendaftaranScopusCamp $pendaftaran, CategoriesScopusCamp $categoriesScopusCamp, $appName)
    {
        $this->pendaftaran = $pendaftaran;
        $this->categoriesScopusCamp = $categoriesScopusCamp;
        $this->appName = $appName;
        $this->status = $pendaftaran->status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('account.pendaftaran_scopus_camp.mail_reschedule')
            ->subject('Pendaftaran Scopus Camp Di Reschedule')
            ->from('info@rumahscopusfoundation.com', $this->appName);
    }
}
