<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Tautan sekali klik untuk mematikan PIN bagi yang lupa PIN-nya. */
class TautanMatikanPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $tautan;

    public int $menit;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $tautan, int $menit = 30)
    {
        $this->user = $user;
        $this->tautan = $tautan;
        $this->menit = $menit;
    }

    public function build()
    {
        return $this->view('emails.tautan-matikan-pin')
            ->subject('Matikan PIN Masuk Anda')
            ->from(config('mail.from.address'), $this->appName);
    }
}
