<?php

namespace App\Mail;

use App\PendaftaranOnlineTraining;
use App\CategoriesOnlineTraining; // ubah dari CategoriesAnalisisBibliometrik
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnlineTrainingUpdateDiterimaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesOnlineTraining;
    public $appName;
    public $status;

    /**
     * Create a new message instance.
     *
     * @param PendaftaranOnlineTraining $pendaftaran
     * @param CategoriesOnlineTraining $categoriesOnlineTraining
     * @param string $appName
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
            'online_training', 'kwitansi', $this->pendaftaran, $this->categoriesOnlineTraining
        );

        return $this->view('account.pendaftaran_online_training.mail_diterima')
            ->subject('Pembayaran diterima — ' . $this->pendaftaran->id_transaksi)
            ->from(config('mail.from.address'), $this->appName)
            ->attachData($kwitansi['isi'], $kwitansi['nama'], ['mime' => 'application/pdf']);
    }
}
