<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar bahwa sesi di semua perangkat lain baru saja diakhiri.
 *
 * Tindakan ini memang alat pengamanan, tetapi ia juga bisa dipakai orang
 * yang sudah terlanjur masuk untuk mengusir pemilik aslinya dari
 * perangkatnya sendiri. Sampai sekarang itu satu-satunya tindakan besar di
 * halaman keamanan yang tidak meninggalkan kabar apa pun di kotak masuk.
 */
class PemberitahuanKeluarPerangkatMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $ip;

    public string $appName = 'Rumah Scopus Foundation';

    public function __construct(User $user, string $ip = '')
    {
        $this->user = $user;
        $this->ip = $ip;
    }

    public function build()
    {
        return $this->view('emails.keluar-perangkat-lain')
            ->subject('Sesi di Perangkat Lain Diakhiri')
            ->from(config('mail.from.address'), $this->appName);
    }
}
