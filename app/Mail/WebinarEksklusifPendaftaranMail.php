<?php

namespace App\Mail;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Bukti pendaftaran Webinar Eksklusif, dikirim ke pendaftar.
 *
 * Sebelum ini tidak ada pemberitahuan apa pun untuk fitur ini — berbeda
 * dengan Scopus Kafe yang sudah punya. Akibatnya orang hanya memegang
 * tautan status di layar, dan begitu tabnya ditutup, satu-satunya jalan
 * kembali adalah menghubungi panitia.
 *
 * SENGAJA TIDAK ShouldQueue. Penjadwal di server ini tidak jalan untuk
 * sebagian perintah, dan antrean yang tidak pernah diproses berarti email
 * yang tidak pernah terkirim tanpa satu pun tanda. Dikirim langsung, lebih
 * lambat sepersekian detik tetapi benar-benar sampai.
 */
class WebinarEksklusifPendaftaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WebinarEksklusifPendaftaran $pendaftaran,
        public KategoriLayanan $sesi,
    ) {
    }

    public function build()
    {
        return $this->subject('Pendaftaran ' . $this->sesi->nama . ' tersimpan')
            ->from(config('mail.from.address'), config('app.name'))
            ->view('public.webinar_eksklusif.email_pendaftaran', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'tautanStatus' => route('public.webinareksklusif.status', $this->pendaftaran->token),
            ]);
    }
}
