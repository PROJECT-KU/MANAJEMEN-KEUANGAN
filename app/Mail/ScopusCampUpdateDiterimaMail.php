<?php

namespace App\Mail;

use App\PendaftaranScopusCamp;
use App\CategoriesScopusCamp; // ubah dari CategoriesAnalisisBibliometrik
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScopusCampUpdateDiterimaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesScopusCamp;
    public $appName;
    public $status;

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
        /*
         * KWITANSINYA dilampirkan, dan sebelum 24 Okt 2026 tidak ada.
         *
         * Surat ini yang dikirim saat pembayarannya diterima — lewat DOKU
         * maupun diperiksa panitia — dan ia menyebut nominalnya. Tanpa
         * kwitansi, yang harus menyerahkan bukti ke bendahara kantornya tidak
         * punya apa pun untuk diserahkan; dan itu justru pemesan rombongan,
         * yang nominalnya paling besar.
         *
         * 'kwitansi', bukan disimpulkan cetakannya dari status: kwitansi
         * hanya boleh terbit kalau uangnya memang sudah diterima, dan surat
         * inilah yang tahu itu.
         */
        $kwitansi = \App\Support\LampiranDokumen::rakit(
            'scopus_camp', 'kwitansi', $this->pendaftaran, $this->categoriesScopusCamp
        );

        return $this->view('account.pendaftaran_scopus_camp.mail_diterima')
            ->subject('Pembayaran diterima — ' . $this->pendaftaran->id_transaksi)
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($kwitansi['isi'], $kwitansi['nama'], ['mime' => 'application/pdf']);
    }
}
