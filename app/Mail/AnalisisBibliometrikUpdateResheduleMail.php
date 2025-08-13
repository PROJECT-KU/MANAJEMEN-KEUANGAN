<?php

namespace App\Mail;

use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnalisisBibliometrikUpdateResheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $analisisbibliometrik;
    public $categoriesanalisisbibliometrik;
    public $appName;
    public $status; // Property untuk menyimpan status

    /**
     * Create a new message instance.
     *
     * @param AnalisisBibliometrik 
     * @param CategoriesAnalisisBibliometrik 
     * @param string $appName
     * @return void
     */
    public function __construct(AnalisisBibliometrik $analisisbibliometrik, CategoriesAnalisisBibliometrik $categoriesanalisisbibliometrik, $appName)
    {
        $this->analisisbibliometrik = $analisisbibliometrik;
        $this->categoriesanalisisbibliometrik = $categoriesanalisisbibliometrik;
        $this->appName = $appName;
        $this->status = $analisisbibliometrik->status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('account.analisis_bibliometrik.mail_reschedule')
            ->subject('Pendaftaran Analisis Bibliometrik Di Reschedule')
            ->from('info@rumahscopusfoundation.com', $this->appName);
    }
}
