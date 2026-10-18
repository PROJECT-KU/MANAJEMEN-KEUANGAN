<?php

namespace App\Mail;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Riskihajar\Terbilang\Facades\Terbilang;

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
        /*
         * Subjeknya PENDEK dan memuat nomor pendaftarannya.
         *
         * Judul sesi di sini panjangnya 68 huruf, dan "Pendaftaran <judul>
         * tersimpan" terpotong di tengah jalan pada daftar surat — yang
         * terbaca tinggal judul sesinya, tanpa petunjuk bahwa ini bukti
         * pendaftaran. Nomornya ikut supaya suratnya bisa dicari belakangan
         * dengan mengetik nomor yang disebut panitia.
         */
        $surat = $this->subject('Pendaftaran tersimpan — ' . $this->pendaftaran->id_transaksi)
            /*
             * mail.from.name, BUKAN app.name.
             *
             * APP_NAME di .env masih "Laravel", jadi surat yang sampai ke
             * peserta tertulis pengirimnya "Laravel" — terlihat seperti surat
             * nyasar atau penipuan, dan di situlah orang berhenti membacanya.
             * MAIL_FROM_NAME sudah terisi "Rumah Scopus Foundation".
             */
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('public.webinar_eksklusif.email_pendaftaran', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'tautanStatus' => route('public.webinareksklusif.status', $this->pendaftaran->getKey()),
            ]);

        /*
         * FAKTURNYA dilampirkan HANYA selama belum lunas.
         *
         * Surat ini dikirim ulang lewat tombol "kirim ulang bukti
         * pendaftaran", dan tombol itu tetap ada sesudah pembayarannya
         * masuk. Dilampirkan tanpa syarat, yang sudah membayar menerima
         * tagihan untuk uang yang sudah ia bayar — dan sebagian akan
         * membayarnya lagi.
         *
         * Yang sudah lunas memang tidak kekurangan apa-apa di sini:
         * kwitansinya sudah berangkat lewat WebinarEksklusifLunasMail.
         */
        if (! $this->pendaftaran->lunas) {
            $faktur = Pdf::loadView('public.webinar_eksklusif.faktur_pdf', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'terbilang' => Terbilang::make((int) $this->pendaftaran->total_pembayaran, ' rupiah'),
            ]);

            $nomor = str_replace('WE-', 'INV-', (string) $this->pendaftaran->id_transaksi);

            // Nama berkasnya memuat nomornya; alasannya sama seperti di
            // kwitansi — folder unduhan yang sama, nama yang sama bertabrakan.
            $surat->attachData($faktur->output(), $nomor . '.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $surat;
    }
}
