<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Mail\TautanResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'level',
        'jenis',
        'telp',
        'company',
        'alamat_company',
        'telp_company',
        'email_company',
        'logo_company',
        'pj_company',
        'level',
        'nik',
        'tanggal_lahir',
        'norek',
        'bank',
        'gambar',
        'jobdesk',
        'total_hadir',
        'code_verified_mail',
        'code_verified_mail_sent_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'pin_aktif' => 'boolean',
        'pin_diubah_pada' => 'datetime',
    ];

    /**
     * PIN masuk sengaja TIDAK ada di $fillable: nilainya hanya boleh disetel
     * lewat aturPin() supaya selalu teracak dan tidak pernah bisa ikut
     * terisi dari data permintaan.
     */
    public function pinAktif(): bool
    {
        return (bool) $this->pin_aktif && ! empty($this->pin);
    }

    /** Simpan PIN baru dalam bentuk teracak dan langsung aktifkan. */
    public function aturPin(string $pin): void
    {
        $this->forceFill([
            'pin' => Hash::make($pin),
            'pin_aktif' => true,
            'pin_diubah_pada' => now(),
        ])->save();
    }

    /** Matikan PIN dan buang nilainya. */
    public function matikanPin(): void
    {
        $this->forceFill([
            'pin' => null,
            'pin_aktif' => false,
            'pin_diubah_pada' => null,
        ])->save();
    }

    public function pinCocok(string $pin): bool
    {
        return $this->pinAktif() && Hash::check($pin, (string) $this->pin);
    }

    /**
     * Kirim tautan atur ulang kata sandi memakai surat milik aplikasi ini,
     * bukan notifikasi bawaan Laravel, supaya tampilannya seragam.
     */
    public function sendPasswordResetNotification($token)
    {
        $tautan = route('password.atur-ulang', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        Mail::to($this->getEmailForPasswordReset())->send(
            new TautanResetPasswordMail($this, $tautan, (int) config('auth.passwords.users.expire', 60))
        );
    }
}
