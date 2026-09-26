<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Lupa Kata Sandi',
    'kelasHalaman' => 'halaman-lupa',
    'warnaTema' => '#ea580c',
    'merekJudul' => 'Tidak bisa masuk? Tenang.',
    'merekTeks' => 'Kami kirimkan tautan aman ke email Anda untuk membuat kata sandi baru.',
])]
class LupaPassword extends Component
{
    public string $email = '';

    public bool $terkirim = false;

    /** Maksimal permintaan tautan per email+IP. */
    private const BATAS_KIRIM = 3;

    /** Jendela pembatasan permintaan tautan (detik). */
    private const JENDELA_KIRIM = 600;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email' => 'Format alamat email tidak valid.',
        ];
    }

    public function kirimTautan(): void
    {
        $this->validate();

        $kunci = 'tautan-reset|' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_KIRIM)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak permintaan. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        RateLimiter::hit($kunci, self::JENDELA_KIRIM);

        try {
            // Password broker bawaan Laravel: token disimpan dalam bentuk hash di
            // tabel password_resets, kedaluwarsa mengikuti config auth.passwords,
            // dan hanya berlaku sekali pakai.
            Password::sendResetLink(['email' => $this->email]);
        } catch (\Throwable $e) {
            // Server surat bisa saja tidak terjangkau (DNS, kredensial, kuota).
            // Jangan sampai halamannya galat 500; cukup beri tahu pengguna.
            Log::error('Gagal mengirim tautan atur ulang kata sandi: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'email' => 'Tautan gagal dikirim karena layanan email sedang bermasalah. Silakan coba beberapa saat lagi atau hubungi admin.',
            ]);
        }

        // Hasilnya sengaja tidak dibedakan antara email terdaftar dan tidak,
        // supaya halaman ini tidak bisa dipakai menebak alamat email mana yang
        // punya akun.
        $this->terkirim = true;
    }

    public function render()
    {
        return view('livewire.auth.lupa-password', [
            'menitBerlaku' => (int) config('auth.passwords.users.expire', 60),
        ]);
    }
}
