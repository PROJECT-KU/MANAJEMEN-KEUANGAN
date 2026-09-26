<?php

namespace App\Livewire\Auth;

use App\Mail\KodeResetPasswordMail;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Lupa Kata Sandi',
    'kelasHalaman' => 'halaman-lupa',
    'merekJudul' => 'Tidak bisa masuk? Tenang.',
    'merekTeks' => 'Kami kirim kode verifikasi ke email Anda, lalu Anda bisa membuat kata sandi baru dalam satu langkah.',
])]
class LupaPassword extends Component
{
    public string $email = '';

    /** Maksimal permintaan kode per email+IP. */
    private const BATAS_KIRIM = 3;

    /** Jendela pembatasan permintaan kode (detik). */
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

    public function kirimKode()
    {
        $this->validate();

        $kunci = 'kode-reset|' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_KIRIM)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak permintaan kode. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        RateLimiter::hit($kunci, self::JENDELA_KIRIM);

        $pengguna = User::where('email', $this->email)->first();

        // Email yang tidak terdaftar tetap diarahkan ke halaman berikutnya tanpa
        // mengirim apa pun, supaya halaman ini tidak bisa dipakai menebak-nebak
        // alamat email mana yang punya akun.
        if ($pengguna) {
            $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_resets')->where('email', $pengguna->email)->delete();
            DB::table('password_resets')->insert([
                'email' => $pengguna->email,
                'token' => Hash::make($kode),
                'created_at' => Carbon::now(),
            ]);

            Mail::to($pengguna->email)->send(
                new KodeResetPasswordMail($pengguna, $kode, $this->menitKedaluwarsa())
            );
        }

        session()->flash('info', 'Jika email tersebut terdaftar, kode verifikasi sudah kami kirim. Periksa juga folder spam.');

        return redirect()->route('password.atur-ulang', ['email' => $this->email]);
    }

    private function menitKedaluwarsa(): int
    {
        return (int) config('auth.passwords.users.expire', 60);
    }

    public function render()
    {
        return view('livewire.auth.lupa-password');
    }
}
