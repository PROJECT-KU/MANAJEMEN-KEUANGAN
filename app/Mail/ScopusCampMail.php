<?php

namespace App\Mail;

use App\PendaftaranScopusCamp;
use App\CategoriesScopusCamp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Riskihajar\Terbilang\Facades\Terbilang;

class ScopusCampMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesScopusCamp;
    public $appName;
    public $analisisbibliometrikName;
    public $status;

    /**
     * Create a new message instance.
     *
     * @param PendaftaranScopusCamp 
     * @param CategoriesScopusCamp 
     * @param string 
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
        /*
         * Lampirannya lewat perakit BERSAMA, bukan Pdf::loadView di sini.
         *
         * Perakit itu yang memilih cetakannya, merakit terbilang dari kolom
         * total yang benar, dan menamai berkasnya. Disalin ke tiap Mailable,
         * ketiganya akan berselisih di sebagian — dan yang berselisih pada
         * dokumen keuangan adalah angka.
         */
        $faktur = \App\Support\LampiranDokumen::rakit(
            'scopus_camp', 'faktur', $this->pendaftaran, $this->categoriesScopusCamp
        );

        return $this->view('public.scopus_camp.mail')
            ->subject('Pendaftaran Scopus Camp Berhasil Terkirim')
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($faktur['isi'], $faktur['nama'], ['mime' => 'application/pdf']);
    }
}
