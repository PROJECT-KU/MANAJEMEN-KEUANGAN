<?php

namespace App\Mail;

use App\Actions\Webinar\BuatAkunPeserta;
use App\KategoriLayanan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Akun MIS untuk satu peserta webinar yang pembayarannya sudah lunas.
 *
 * Satu surat per ORANG, bukan satu surat per pendaftaran: tiap peserta punya
 * nama pengguna sendiri, dan surat berisi daftar nama pengguna orang lain
 * adalah surat yang membocorkan akun orang lain ke seluruh rombongan.
 *
 * Suratnya bercabang menurut `$baru`:
 *
 *   - akun BARU       -> menyebut nama pengguna DAN sandi bawaannya;
 *   - akun SUDAH ADA  -> hanya mengingatkan nama penggunanya. Menyebut sandi
 *                        di sini akan menyesatkan: sandi orang itu adalah
 *                        yang ia pilih sendiri, dan kami memang tidak
 *                        menyentuhnya.
 *
 * SENGAJA TIDAK ShouldQueue, sama seperti surat webinar lainnya: penjadwal
 * di hosting ini tidak berjalan, jadi antrean berarti surat yang tidak
 * pernah terkirim.
 */
class AkunPesertaWebinarMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nama,
        public string $namaPengguna,
        public bool $baru,
        public ?KategoriLayanan $sesi = null,
    ) {
    }

    public function build()
    {
        $judul = $this->baru
            ? 'Akun MIS Anda sudah siap — Rumah Scopus Foundation'
            : 'Akses rekaman webinar lewat akun MIS Anda — Rumah Scopus Foundation';

        return $this->subject($judul)
            ->view('public.webinar_eksklusif.email_akun', [
                'nama' => $this->nama,
                'namaPengguna' => $this->namaPengguna,
                'baru' => $this->baru,
                'sandi' => BuatAkunPeserta::SANDI_BAWAAN,
                'sesi' => $this->sesi,
                'tautanMasuk' => route('login'),
            ]);
    }
}
