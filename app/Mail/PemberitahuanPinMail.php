<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar untuk pemilik akun setiap kali PIN masuknya berubah keadaan.
 * Tujuannya supaya perubahan yang bukan dilakukan pemiliknya cepat terlihat.
 */
class PemberitahuanPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    /** 'diaktifkan' | 'diubah' | 'dinonaktifkan' | 'dinonaktifkan-otomatis' */
    public string $aksi;

    public string $ip;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $aksi, string $ip = '')
    {
        $this->user = $user;
        $this->aksi = $aksi;
        $this->ip = $ip;
    }

    public function build()
    {
        $judul = match ($this->aksi) {
            'diaktifkan' => 'PIN Masuk Diaktifkan',
            'diubah' => 'PIN Masuk Diubah',
            'dinonaktifkan-otomatis' => 'PIN Masuk Dinonaktifkan Otomatis',
            default => 'PIN Masuk Dinonaktifkan',
        };

        return $this->view('emails.pemberitahuan-pin')
            ->subject($judul . ' pada Akun Anda')
            ->from(config('mail.from.address'), $this->appName);
    }
}
