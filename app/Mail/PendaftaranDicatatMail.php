<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar untuk orang yang DIDAFTARKAN panitia.
 *
 * Nomor pendaftaran dan kode uniknya baru dibuat saat barisnya disimpan, dan
 * justru dua hal itu yang harus sampai ke orangnya — kode uniknya yang
 * membuat nominal transfernya bisa dicocokkan dengan mutasi rekening.
 * Sebelum ini tidak ada surat sama sekali dari jalur panitia, jadi keduanya
 * disalin manual ke WhatsApp satu per satu.
 */
class PendaftaranDicatatMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nama,
        public string $layanan,
        public string $nomor,
        public int $total,
        public int $kodeUnik,
        public ?string $angkatan,
        public ?string $tanggal,
        public string $caraBayar,
        public bool $sudahLunas,
        public string $appName = 'Rumah Scopus Foundation',
    ) {
    }

    public function build()
    {
        return $this->view('emails.pendaftaran-dicatat')
            ->subject('Pendaftaran ' . $this->layanan . ' tercatat — ' . $this->nomor)
            ->from(config('mail.from.address'), $this->appName);
    }
}
