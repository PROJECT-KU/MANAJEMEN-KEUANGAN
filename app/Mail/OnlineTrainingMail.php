<?php

namespace App\Mail;

use App\PendaftaranOnlineTraining;
use App\CategoriesOnlineTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Riskihajar\Terbilang\Facades\Terbilang;

class OnlineTrainingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesOnlineTraining;
    public $appName;
    public $analisisbibliometrikName;
    public $status;

    /**
     * Create a new message instance.
     *
     * @param PendaftaranOnlineTraining 
     * @param CategoriesOnlineTraining 
     * @param string 
     * @return void
     */
    public function __construct(PendaftaranOnlineTraining $pendaftaran, CategoriesOnlineTraining $categoriesOnlineTraining, $appName)
    {
        $this->pendaftaran = $pendaftaran;
        $this->categoriesOnlineTraining = $categoriesOnlineTraining;
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
            'online_training', 'faktur', $this->pendaftaran, $this->categoriesOnlineTraining
        );

        return $this->view('public.online_training.mail')
            ->subject('Pendaftaran Class Online Berhasil Terkirim')
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($faktur['isi'], $faktur['nama'], ['mime' => 'application/pdf']);
    }
}
