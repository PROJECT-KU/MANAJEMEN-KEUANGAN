<?php

namespace App\Mail;

use App\Support\KabarStatusPendaftaran;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Surat pemberitahuan untuk SETIAP perubahan status, semua layanan.
 *
 * Satu surat untuk kelima layanan, bukan lima surat yang mirip. Yang sudah
 * ada membuktikan kenapa: tiga surat status yang ditulis terpisah punya tanda
 * tangan yang berbeda-beda, dan dua di antaranya menuntut model angkatan
 * bertipe khusus — sehingga merakitnya butuh percabangan tersendiri di
 * UbahStatusPendaftaran. Yang ini cukup tahu layanan dan barisnya.
 *
 * Surat khusus yang sudah ada TIDAK dibuang: isinya lebih kaya (tautan grup
 * WhatsApp, tanggal mulai dan selesai angkatan). Yang ini mengisi 18 status
 * yang sebelumnya diam sama sekali.
 */
class PerubahanStatusPendaftaranMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $layanan,
        public $pendaftaran,
    ) {}

    public function build()
    {
        $status = (string) $this->pendaftaran->status;
        $kabar = KabarStatusPendaftaran::untuk($status);

        if ($kabar === null) {
            /*
             * Tidak pernah terjadi selama penjaganya hijau — ada uji yang
             * menuntut TIAP status tiap layanan punya kalimatnya. Dilempar,
             * bukan didiamkan: surat berjudul kosong yang terlanjur sampai ke
             * pendaftar lebih buruk daripada surat yang gagal terkirim dan
             * tercatat di log.
             */
            throw new \RuntimeException(
                'Status "' . $status . '" (' . $this->layanan . ') belum punya kalimat surat.'
            );
        }

        return $this->subject($kabar['judul'] . ' — ' . Pendaftaran::katalog()[$this->layanan]['nama'])
            ->view('emails.pendaftaran.perubahan-status', [
                'kabar' => $kabar,
                'layanan' => $this->layanan,
                'pendaftaran' => $this->pendaftaran,
            ]);
    }
}
