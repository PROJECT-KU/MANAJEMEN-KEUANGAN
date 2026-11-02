<?php

namespace App\Mail;

use App\CategoriesOnlineTraining;
use App\PendaftaranOnlineTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Kabar bahwa kursi Class Online dilepas karena tenggat bayarnya terlewat.
 *
 * Sampai 26 Okt 2026 kejadian ini hanya dikabari lewat WhatsApp — dan
 * perangkat Fonnte-nya sedang putus, jadi tidak dikabari ke mana pun.
 * Orangnya baru tahu kursinya hilang kalau ia kebetulan membuka halaman
 * statusnya lagi, kadang berhari-hari kemudian, dan yang pertama ia kerjakan
 * adalah menghubungi panitia.
 *
 * Webinar Eksklusif sudah punya suratnya sejak lama. Dua saluran, sebab yang
 * satu gagal tidak boleh membuat orangnya tidak tahu sama sekali.
 */
class OnlineTrainingKedaluwarsaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PendaftaranOnlineTraining $pendaftaran,
        public CategoriesOnlineTraining $angkatan,
    ) {}

    public function build()
    {
        return $this->subject('Kursi Class Online Anda dilepas — ' . $this->pendaftaran->id_transaksi)
            ->view('emails.online_training.kedaluwarsa');
    }
}
