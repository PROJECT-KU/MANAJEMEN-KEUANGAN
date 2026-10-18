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
 * Pembayaran diterima, berikut KWITANSINYA sebagai lampiran PDF.
 *
 * Dua jalur menuju surat ini, dan dulu keduanya mengirim surat yang
 * BERBEDA untuk kejadian yang sama:
 *
 *   - lunas lewat DOKU  -> surat "bukti pendaftaran" dikirim ulang dalam
 *                          mode lunas, jadi isinya masih bercerita tentang
 *                          cara membayar;
 *   - lunas dari MIS    -> surat perubahan status umum, yang tidak menyebut
 *                          nominal maupun cara bayarnya sama sekali.
 *
 * Keduanya kini memakai kelas ini. Surat pendaftaran sendiri sudah berjanji
 * "kami kirimkan surat penanda lunas" — janji yang sampai sekarang hanya
 * setengah ditepati, sebab yang datang bukan penanda apa pun.
 *
 * Kwitansinya dirakit SAAT SURATNYA DIBUAT, bukan disimpan di cakram:
 * isinya seluruhnya turunan dari barisnya, jadi berkas tersimpan cuma
 * menambah satu hal yang bisa basi diam-diam saat barisnya dibetulkan.
 *
 * Seperti surat webinar lainnya, SENGAJA TIDAK ShouldQueue: antrean yang
 * tidak pernah diproses berarti surat yang tidak pernah terkirim.
 */
class WebinarEksklusifLunasMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Tanda tangannya mengikuti KirimSuratStatus::rakit() — yang merakit
     * surat layar panitia dengan tiga argumen: barisnya, angkatannya, dan
     * nama aplikasinya. Berbeda sedikit saja, jalur MIS melempar TypeError
     * saat merakit, bukan saat mengirim.
     */
    public function __construct(
        public WebinarEksklusifPendaftaran $pendaftaran,
        public ?KategoriLayanan $sesi = null,
        public string $namaAplikasi = '',
    ) {
    }

    public function build()
    {
        $kwitansi = Pdf::loadView('public.webinar_eksklusif.dokumen_pdf', [
            'jenis' => 'kwitansi',
            'pendaftaran' => $this->pendaftaran,
            'sesi' => $this->sesi,
            'terbilang' => Terbilang::make((int) $this->pendaftaran->total_pembayaran, ' rupiah'),
        ]);

        $nomor = str_replace('WE-', 'KW-', (string) $this->pendaftaran->id_transaksi);

        return $this->subject('Pembayaran diterima — ' . $this->pendaftaran->id_transaksi)
            /*
             * mail.from.name, BUKAN $namaAplikasi maupun app.name.
             *
             * $namaAplikasi datang dari pemanggil dan kosong di jalur DOKU;
             * app.name masih "Laravel". Alasan lengkapnya di
             * WebinarEksklusifPengingatMail.
             */
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('public.webinar_eksklusif.email_lunas', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,
                'tautanStatus' => route('public.webinareksklusif.status', $this->pendaftaran->getKey()),
            ])
            /*
             * Nama berkasnya memuat nomornya, bukan "kwitansi.pdf" saja:
             * yang mengikuti beberapa sesi akan punya beberapa berkas di
             * folder unduhan yang sama, dan nama yang sama bertabrakan.
             */
            ->attachData($kwitansi->output(), $nomor . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
