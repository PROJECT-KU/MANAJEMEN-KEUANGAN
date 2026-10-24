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
        /*
         * FAKTURNYA dilampirkan, dan sebelum 24 Okt 2026 Scopus Kafe tidak
         * punya lampiran sama sekali.
         *
         * Yang dilampirkan dulu justru LOGO: assets/img/LogoRSC.png, 121.370
         * bita, pada tiap surat — dan isi suratnya tidak pernah merujuknya,
         * jadi ia muncul di kotak masuk sebagai berkas gambar yang tidak jelas
         * gunanya sementara tagihannya sendiri tidak terlampir. Logonya kini
         * disisipkan kerangka suratnya lewat aset_surat().
         */
        $faktur = \App\Support\LampiranDokumen::rakit(
            'scopus_kafe', 'faktur', $this->pendaftaranScopusKafe
        );

        return $this->view('public.scopus_kafe.send_email_sukses')
            ->subject('Pendaftaran Scopus Kafe Berhasil Terkirim')
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($faktur['isi'], $faktur['nama'], ['mime' => 'application/pdf']);
    }
}
