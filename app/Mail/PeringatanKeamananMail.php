<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PeringatanKeamananMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $ip;

    public int $percobaan;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $ip, int $percobaan)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->percobaan = $percobaan;
    }

    public function build()
    {
        return $this->view('emails.peringatan-keamanan')
            ->subject('Percobaan Masuk Gagal Berulang pada Akun Anda')
            ->from(config('mail.from.address'), $this->appName);
    }
}
