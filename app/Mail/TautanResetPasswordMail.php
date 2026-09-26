<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TautanResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $tautan;

    public int $menit;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $tautan, int $menit = 60)
    {
        $this->user = $user;
        $this->tautan = $tautan;
        $this->menit = $menit;
    }

    public function build()
    {
        return $this->view('emails.tautan-reset-password')
            ->subject('Atur Ulang Kata Sandi Akun Anda')
            ->from(config('mail.from.address'), $this->appName);
    }
}
