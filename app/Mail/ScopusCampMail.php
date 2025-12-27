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
        $pdf = Pdf::loadView('public.scopus_camp.invoice', [
            'pendaftaran' => $this->pendaftaran,
            'categoriesScopusCamp' => $this->categoriesScopusCamp,
            'terbilang' => Terbilang::make($this->pendaftaran->total_pembayaran),
        ]);

        return $this->view('public.scopus_camp.mail')
            ->subject('Pendaftaran Scopus Camp Berhasil Terkirim')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->attachData($pdf->output(), 'invoice_' . strtoupper($this->pendaftaran->id_transaksi) . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
