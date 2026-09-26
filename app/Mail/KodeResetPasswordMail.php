<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KodeResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $kode;

    public int $menit;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $kode, int $menit = 60)
    {
        $this->user = $user;
        $this->kode = $kode;
        $this->menit = $menit;
    }

    public function build()
    {
        return $this->view('emails.kode-reset-password')
            ->subject('Kode Verifikasi Atur Ulang Kata Sandi')
            ->from(config('mail.from.address'), $this->appName);
    }
}
