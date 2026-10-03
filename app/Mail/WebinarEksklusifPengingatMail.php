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
        // Pendek, dengan alasan yang sama seperti di bukti pendaftaran.
        return $this->subject('Besok: sesi Anda — ' . $this->pendaftaran->id_transaksi)
            /*
             * mail.from.name, BUKAN app.name.
             *
             * APP_NAME di .env masih "Laravel", jadi surat yang sampai ke
             * peserta tertulis pengirimnya "Laravel" — terlihat seperti surat
             * nyasar atau penipuan, dan di situlah orang berhenti membacanya.
             * MAIL_FROM_NAME sudah terisi "Rumah Scopus Foundation".
             */
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('public.webinar_eksklusif.email_pengingat', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'tautanStatus' => route('public.webinareksklusif.status', $this->pendaftaran->token),
            ]);
    }
}
