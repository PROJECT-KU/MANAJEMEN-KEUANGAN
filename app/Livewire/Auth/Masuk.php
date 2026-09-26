<?php

namespace App\Livewire\Auth;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Masuk Akun',
    'kelasHalaman' => 'halaman-masuk',
    'merekJudul' => 'Selamat datang kembali.',
    'merekTeks' => 'Masuk untuk melanjutkan pengelolaan keuangan, presensi, dan layanan Rumah Scopus Foundation.',
])]
class Masuk extends Component
{
    /** Boleh diisi username ATAU alamat email. */
    public string $identitas = '';

    public string $kataSandi = '';

    public bool $ingatSaya = false;

    /** Berapa kali percobaan gagal sebelum dikunci sementara. */
    private const BATAS_PERCOBAAN = 5;

    /** Lama kunci setelah batas percobaan tercapai (detik). */
    private const LAMA_KUNCI = 60;

    protected function rules(): array
    {
        return [
            'identitas' => ['required', 'string', 'max:150'],
            'kataSandi' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'identitas.required' => 'Masukkan username atau email Anda.',
            'kataSandi.required' => 'Masukkan kata sandi Anda.',
        ];
    }

    public function masuk()
    {
        $this->validate();

        $kunci = $this->kunciPembatas();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
            throw ValidationException::withMessages([
                'identitas' => 'Terlalu banyak percobaan. Coba lagi dalam '
                    . RateLimiter::availableIn($kunci) . ' detik.',
            ]);
        }

        // Username atau email, ditentukan dari isian yang diberikan.
        $kolom = filter_var($this->identitas, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $pengguna = User::where($kolom, $this->identitas)->first();

        if ($pengguna && $pengguna->status === 'nonactive') {
            throw ValidationException::withMessages([
                'identitas' => 'Akun ini dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.',
            ]);
        }

        if (! Auth::attempt([$kolom => $this->identitas, 'password' => $this->kataSandi], $this->ingatSaya)) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);

            $this->reset('kataSandi');

            throw ValidationException::withMessages([
                'identitas' => 'Username/email atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);

        // Cegah session fixation setelah pergantian identitas.
        session()->regenerate();

        return redirect()->intended('/account/dashboard');
    }

    private function kunciPembatas(): string
    {
        return 'masuk|' . Str::lower($this->identitas) . '|' . request()->ip();
    }

    public function render()
    {
        return view('livewire.auth.masuk');
    }
}
