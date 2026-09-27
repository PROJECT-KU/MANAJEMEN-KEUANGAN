<?php

namespace App\Mail;

use App\User;
use App\Gaji;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GajiSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $gaji;
    public $appName;
    public $isTerbayar;
    public $jamLembur;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Gaji $gaji
     * @param string $appName
     * @param bool $isTerbayar
     * @return void
     */
    public function __construct(User $user, Gaji $gaji, $appName, $isTerbayar)
    {
        $this->user = $user;
        $this->gaji = $gaji;
        $this->appName = $appName;
        $this->isTerbayar = $isTerbayar;
        $this->jamLembur = $this->hitungJamLembur($gaji);
    }

    /**
     * Total jam lembur dari data gaji yang tersimpan (jumlah_lembur + jumlah_lembur1..10).
     * Nilainya berasal dari perhitungan otomatis presensi saat gaji dibuat/diubah.
     *
     * @param Gaji $gaji
     * @return float
     */
    private function hitungJamLembur(Gaji $gaji)
    {
        $total = (float) str_replace(',', '.', $gaji->jumlah_lembur);

        for ($i = 1; $i <= 10; $i++) {
            $field = 'jumlah_lembur' . $i;
            $total += (float) str_replace(',', '.', $gaji->{$field});
        }

        return round($total, 2);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $logoPath = public_path('assets/img/LogoRSC.png');

        $mail = $this->view('account.gaji.send_email_sukses')
            ->subject('Pembayaran Gaji Berhasil')
            ->from(config('mail.from.address'), $this->appName)
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
