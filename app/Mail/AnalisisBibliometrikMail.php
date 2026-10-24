<?php

namespace App\Mail;

use App\AnalisisBibliometrik;
use App\CategoriesAnalisisBibliometrik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Riskihajar\Terbilang\Facades\Terbilang;

class AnalisisBibliometrikMail extends Mailable
{
    use Queueable, SerializesModels;

    public $analisisbibliometrik;
    public $categoriesanalisisbibliometrik;
    public $appName;
    public $analisisbibliometrikName;
    public $status;

    /**
     * Create a new message instance.
     *
     * @param AnalisisBibliometrik 
     * @param CategoriesAnalisisBibliometrik 
     * @param string 
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
        // Lewat perakit bersama; alasannya di ScopusCampMail.
        $faktur = \App\Support\LampiranDokumen::rakit(
            'bibliometrik', 'faktur',
            $this->analisisbibliometrik, $this->categoriesanalisisbibliometrik
        );

        return $this->view('public.analisis_bibliometrik.mail')
            ->subject('Pendaftaran Analisis Bibliometrik Berhasil Terkirim')
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($faktur['isi'], $faktur['nama'], ['mime' => 'application/pdf']);
    }
}
