<?php

namespace App\Mail;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Pengingat sehari sebelum sesinya berlangsung.
 *
 * Jarak antara mendaftar dan hari-H bisa berminggu-minggu — sesi 21 Oktober
 * sudah dibuka pendaftarannya awal Oktober. Tanpa pengingat, yang membayar
 * jauh hari lupa, dan kursinya terpakai tanpa ada orangnya.
 *
 * Seperti bukti pendaftaran, SENGAJA TIDAK ShouldQueue: antrean yang tidak
 * pernah diproses berarti pengingat yang tidak pernah terkirim, tanpa tanda.
 */
class WebinarEksklusifPengingatMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WebinarEksklusifPendaftaran $pendaftaran,
        public KategoriLayanan $sesi,
    ) {
    }

    public function build()
    {
        return $this->subject('Besok: ' . $this->sesi->nama)
            ->from(config('mail.from.address'), config('app.name'))
            ->view('public.webinar_eksklusif.email_pengingat', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'tautanStatus' => route('public.webinareksklusif.status', $this->pendaftaran->token),
            ]);
    }
}
