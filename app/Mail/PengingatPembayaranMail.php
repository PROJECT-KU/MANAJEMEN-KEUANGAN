<?php

namespace App\Mail;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Pengingat untuk pendaftar yang pembayarannya belum masuk.
 *
 * Satu surat untuk kelima layanan, rupanya sama dengan surat status: logo
 * yayasan di tengah, judul di tengah, rincian dua lajur. Kerangkanya memang
 * satu, jadi ia tidak bisa berbeda rupa.
 *
 * @see \App\Console\Commands\KirimPengingatBayar kapan surat ini dikirim
 */
class PengingatPembayaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $layanan,
        public $pendaftaran,
        public int $hariBerlalu = 0,
    ) {}

    public function build()
    {
        return $this->subject('Pembayaran pendaftaran Anda belum kami terima — '
                . (Pendaftaran::katalog()[$this->layanan]['nama'] ?? 'Rumah Scopus Foundation'))
            ->view('emails.pendaftaran.pengingat-bayar', [
                'layanan' => $this->layanan,
                'pendaftaran' => $this->pendaftaran,
                'hariBerlalu' => $this->hariBerlalu,
            ]);
    }
}
