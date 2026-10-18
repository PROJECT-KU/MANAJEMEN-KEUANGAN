<?php

namespace App\Mail;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar bahwa batas bayarnya lewat dan kursinya sudah dilepas.
 *
 * Sebelum ini kursinya dilepas DIAM-DIAM. Orangnya meninggalkan halaman
 * bayar, dan satu jam kemudian pendaftarannya tidak berlaku lagi tanpa satu
 * pun kabar — yang ia punya cuma surat "pendaftaran diterima" yang kini
 * tidak benar lagi. Yang menanggung panitia lewat WhatsApp, berminggu-minggu
 * kemudian, saat orangnya datang menanyakan sesi yang kursinya sudah
 * diberikan ke orang lain.
 *
 * Nadanya BUKAN teguran. Tidak membayar dalam satu jam bukan pelanggaran,
 * dan sebagian besar yang menerima surat ini masih mau ikut — jadi yang
 * ditonjolkan jalan kembalinya, bukan apa yang hilang.
 *
 * Seperti bukti pendaftaran dan pengingat, SENGAJA TIDAK ShouldQueue:
 * antrean yang tidak pernah diproses berarti surat yang tidak pernah
 * terkirim, tanpa tanda apa pun bahwa ia tidak terkirim.
 */
class WebinarEksklusifKedaluwarsaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WebinarEksklusifPendaftaran $pendaftaran,
        public ?KategoriLayanan $sesi = null,
    ) {
    }

    public function build()
    {
        /*
         * Judulnya menyebut APA YANG TERJADI, bukan "pemberitahuan".
         *
         * Yang membukanya di daftar surat harus tahu isinya sebelum
         * membuka — dan nomornya ikut supaya bisa dicari lagi nanti.
         */
        return $this->subject('Kursi Anda dilepas — ' . $this->pendaftaran->id_transaksi)
            /*
             * mail.from.name, BUKAN app.name: APP_NAME di .env masih
             * "Laravel". Alasan lengkapnya di WebinarEksklusifPengingatMail.
             */
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('public.webinar_eksklusif.email_kedaluwarsa', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,

                /*
                 * Tautannya ke halaman SESI ITU, bukan ke daftar semua sesi.
                 *
                 * Yang baru kehilangan kursinya ingin kursi yang sama lagi;
                 * dikirim ke daftar, ia harus mencarinya sendiri di antara
                 * sesi lain — dan sebagian berhenti di situ.
                 */
                'tautanDaftar' => $this->sesi
                    ? route('public.webinareksklusif.daftar', $this->sesi->getKey())
                    : route('public.webinareksklusif.index'),
            ]);
    }
}
