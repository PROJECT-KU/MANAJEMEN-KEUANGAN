<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar ke alamat email LAMA bahwa email akun baru saja dipindahkan.
 *
 * Tanpa ini, memindahkan alamat email adalah satu-satunya perubahan besar
 * yang bisa terjadi tanpa sepengetahuan pemiliknya: kabar verifikasi hanya
 * dikirim ke alamat baru, jadi pemilik alamat lama tidak pernah tahu
 * akunnya berpindah — padahal alamat email itulah jalan memulihkan akun.
 */
class EmailAkunDipindahMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $emailLama;

    public string $emailBaru;

    public string $ip;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $emailLama, string $emailBaru, string $ip = '')
    {
        $this->user = $user;
        $this->emailLama = $emailLama;
        $this->emailBaru = $emailBaru;
        $this->ip = $ip;
    }

    public function build()
    {
        return $this->view('emails.email-akun-dipindah')
            ->subject('Alamat Email Akun Anda Diganti')
            ->from(config('mail.from.address'), $this->appName);
    }
}
