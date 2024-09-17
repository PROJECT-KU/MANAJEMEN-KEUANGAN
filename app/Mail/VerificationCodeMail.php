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

    /**
     * Create a new message instance.
     *
     * @param Karir $user
     * @param string $appName
     * @return void
     */
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
            ->subject('Kode Verifikasi Email')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->with(['verificationCode' => $this->verificationCode])
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
