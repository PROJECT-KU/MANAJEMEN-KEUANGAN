<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifikasiEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $tautan;

    public int $jam;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $tautan, int $jam = 48)
    {
        $this->user = $user;
        $this->tautan = $tautan;
        $this->jam = $jam;
    }

    public function build()
    {
        return $this->view('emails.verifikasi-email')
            ->subject('Verifikasi Alamat Email Anda')
            ->from(config('mail.from.address'), $this->appName);
    }
}
