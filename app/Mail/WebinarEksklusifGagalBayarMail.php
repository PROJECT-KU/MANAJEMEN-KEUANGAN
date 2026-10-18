<?php

namespace App\Mail;

use App\KategoriLayanan;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar bahwa pembayarannya TIDAK BERHASIL — bukan kehabisan waktu.
 *
 * Dua hal yang dulu tertukar, dan keduanya merugikan orangnya:
 *
 *   - gerbang menjawab "failed" (kartu ditolak, saldo kurang, transaksi
 *     dibatalkan bank) -> statusnya dipetakan ke 'expired', jadi yang
 *     terkirim surat "batas waktu pembayarannya sudah lewat". Orang yang
 *     kartunya baru saja ditolak membaca bahwa ia TERLAMBAT, lalu menyalahkan
 *     dirinya sendiri atas sesuatu yang sebenarnya bisa diulang sekarang
 *     juga;
 *   - gerbang menjawab "cancel" -> tidak ada surat sama sekali.
 *
 * Nadanya sengaja menghapus rasa bersalah: pembayaran yang gagal nyaris
 * selalu urusan bank atau kartunya, bukan kesalahan orangnya, dan yang
 * ditonjolkan jalan mencobanya lagi.
 *
 * Seperti surat webinar lainnya, SENGAJA TIDAK ShouldQueue: antrean yang
 * tidak pernah diproses berarti surat yang tidak pernah terkirim.
 */
class WebinarEksklusifGagalBayarMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Tanda tangannya mengikuti KirimSuratStatus::rakit() — barisnya,
     * angkatannya, lalu nama aplikasi. Berbeda sedikit saja, jalur layar
     * panitia melempar TypeError saat merakit, bukan saat mengirim.
     */
    public function __construct(
        public WebinarEksklusifPendaftaran $pendaftaran,
        public ?KategoriLayanan $sesi = null,
        public string $namaAplikasi = '',
    ) {
    }

    public function build()
    {
        return $this->subject('Pembayaran belum berhasil — ' . $this->pendaftaran->id_transaksi)
            // mail.from.name, BUKAN app.name: alasannya di WebinarEksklusifPengingatMail.
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('public.webinar_eksklusif.email_gagal_bayar', [
                'pendaftaran' => $this->pendaftaran,
                'sesi' => $this->sesi,

                /*
                 * Tautannya ke halaman SESI ITU, bukan ke halaman statusnya.
                 *
                 * Kursinya sudah dilepas dan pendaftaran lamanya tidak bisa
                 * dibayar lagi — halaman status cuma akan memberitahunya
                 * sekali lagi bahwa semuanya sudah berakhir. Yang ia
                 * butuhkan borang baru.
                 */
                'tautanDaftar' => $this->sesi
                    ? route('public.webinareksklusif.daftar', $this->sesi->getKey())
                    : route('public.webinareksklusif.index'),
            ]);
    }
}
