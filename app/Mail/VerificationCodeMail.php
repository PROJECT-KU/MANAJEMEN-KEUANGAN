<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{

    use Queueable, SerializesModels;

    public $user;
    public $appName;


    use Queueable, SerializesModels;

    public $verificationCode;

    public function __construct(user $user, $appName, $verificationCode)
    {
        $this->verificationCode = $verificationCode;
        $this->user = $user;
        $this->appName = $appName;
    }

    public function build()
    {
        $logoPath = public_path('assets/img/LogoRSC.png');

        return $this->view('account.profil.verification_email')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->subject('Kode Verifikasi Email')
            ->with(['verificationCode' => $this->verificationCode])
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
