<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar saat akun dibuka dari peramban yang belum pernah dipakai masuk.
 * Inilah yang membuat pengambilalihan akun cepat ketahuan pemiliknya.
 */
class MasukPerangkatBaruMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $ip;

    public string $peramban;

    public string $cara;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $ip, string $peramban, string $cara = 'kata sandi')
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->peramban = $peramban;
        $this->cara = $cara;
    }

    public function build()
    {
        return $this->view('emails.masuk-perangkat-baru')
            ->subject('Akun Anda Dibuka dari Perangkat Baru')
            ->from(config('mail.from.address'), $this->appName);
    }
}
