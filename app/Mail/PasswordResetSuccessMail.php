<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $appName;

    /**
     * Alamat IP pengubah. Opsional supaya dua pemanggil lama (alur "lupa
     * kata sandi") tidak perlu ikut diubah; kalau kosong, baris IP-nya
     * memang tidak ditampilkan.
     */
    public string $ip;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $appName
     * @return void
     */
    public function __construct(User $user, $appName, string $ip = '')
    {
        $this->user = $user;
        $this->appName = $appName;
        $this->ip = $ip;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Logo disisipkan di dalam badan surat lewat $message->embed(),
        // jadi tidak perlu dilampirkan sebagai berkas terpisah.
        return $this->view('auth.email_lupa_password')
            ->subject('Kata Sandi Berhasil Diubah')
            ->from(config('mail.from.address'), $this->appName);
    }
}
