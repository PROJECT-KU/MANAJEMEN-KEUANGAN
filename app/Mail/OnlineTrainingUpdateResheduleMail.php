<?php

namespace App\Mail;

use App\PendaftaranOnlineTraining;
use App\CategoriesOnlineTraining; // Sesuai dengan controller
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnlineTrainingUpdateResheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;
    public $categoriesOnlineTraining;
    public $appName;
    public $status; // Property untuk menyimpan status

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
        return $this->view('account.pendaftaran_online_training.mail_reschedule')
            ->subject('Pendaftaran Class Online Di Reschedule')
            ->from(config('mail.from.address'), $this->appName);
    }
}
