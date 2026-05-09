<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode;
    public $user;
    public $appName;

    public function __construct($user, $appName, $verificationCode)
    {
        $this->verificationCode = $verificationCode;
        $this->user = $user;
        $this->appName = $appName;
    }

    public function build()
    {
        // Hapus variabel $logoPath dan fungsi ->attach()
        return $this->view('account.profil.verification_email')
            ->subject('Kode Verifikasi Email')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->with(['verificationCode' => $this->verificationCode]);
    }
}
