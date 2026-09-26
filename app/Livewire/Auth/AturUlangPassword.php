<?php

namespace App\Livewire\Auth;

use App\Mail\KodeResetPasswordMail;
use App\Mail\PasswordResetSuccessMail;
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
    'judulHalaman' => 'Atur Ulang Kata Sandi',
    'kelasHalaman' => 'halaman-atur-ulang',
    'merekJudul' => 'Buat kata sandi baru.',
    'merekTeks' => 'Masukkan kode 6 digit yang kami kirim ke email Anda, lalu tentukan kata sandi baru yang kuat.',
])]
class AturUlangPassword extends Component
{
    public string $email = '';

    public string $kode = '';

    public string $kataSandi = '';

    public string $kataSandiKonfirmasi = '';

    /** Maksimal percobaan kode yang salah per email+IP. */
    private const BATAS_COBA = 5;

    /** Jendela pembatasan percobaan kode (detik). */
    private const JENDELA_COBA = 600;

    public function mount(?string $email = null): void
    {
        $this->email = $email ?? '';
    }

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:150'],
            'kode' => ['required', 'digits:6'],
            'kataSandi' => ['required', 'string', 'min:8', 'same:kataSandiKonfirmasi'],
            'kataSandiKonfirmasi' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email' => 'Format alamat email tidak valid.',
            'kode.required' => 'Masukkan kode verifikasi dari email.',
            'kode.digits' => 'Kode verifikasi terdiri dari 6 angka.',
            'kataSandi.required' => 'Masukkan kata sandi baru.',
            'kataSandi.min' => 'Kata sandi minimal 8 karakter.',
            'kataSandi.same' => 'Konfirmasi kata sandi tidak cocok.',
            'kataSandiKonfirmasi.required' => 'Ulangi kata sandi baru Anda.',
        ];
    }

    public function simpan()
    {
        $this->validate();

        $kunci = 'reset-coba|' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_COBA)) {
            throw ValidationException::withMessages([
                'kode' => 'Terlalu banyak percobaan. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        $baris = DB::table('password_resets')->where('email', $this->email)->first();

        if (! $baris) {
            throw ValidationException::withMessages([
                'kode' => 'Kode tidak ditemukan. Silakan minta kode baru.',
            ]);
        }

        $kedaluwarsa = Carbon::parse($baris->created_at)
            ->addMinutes((int) config('auth.passwords.users.expire', 60));

        if ($kedaluwarsa->isPast()) {
            DB::table('password_resets')->where('email', $this->email)->delete();

            throw ValidationException::withMessages([
                'kode' => 'Kode sudah kedaluwarsa. Silakan minta kode baru.',
            ]);
        }

        if (! Hash::check($this->kode, $baris->token)) {
            RateLimiter::hit($kunci, self::JENDELA_COBA);

            throw ValidationException::withMessages([
                'kode' => 'Kode verifikasi salah.',
            ]);
        }

        $pengguna = User::where('email', $this->email)->first();

        if (! $pengguna) {
            throw ValidationException::withMessages([
                'email' => 'Akun dengan email tersebut tidak ditemukan.',
            ]);
        }

        $pengguna->password = Hash::make($this->kataSandi);
        $pengguna->reset_token = null;
        $pengguna->save();

        // Kode sekali pakai: baris dihapus supaya tidak bisa dipakai ulang.
        DB::table('password_resets')->where('email', $this->email)->delete();
        RateLimiter::clear($kunci);

        Mail::to($pengguna->email)->send(
            new PasswordResetSuccessMail($pengguna, 'Rumah Scopus Foundation')
        );

        session()->flash('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');

        return redirect()->route('login');
    }

    public function kirimUlang(): void
    {
        $this->validateOnly('email');

        $kunci = 'kode-reset|' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, 3)) {
            throw ValidationException::withMessages([
                'kode' => 'Terlalu banyak permintaan kode. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        RateLimiter::hit($kunci, 600);

        $pengguna = User::where('email', $this->email)->first();

        if ($pengguna) {
            $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_resets')->where('email', $pengguna->email)->delete();
            DB::table('password_resets')->insert([
                'email' => $pengguna->email,
                'token' => Hash::make($kode),
                'created_at' => Carbon::now(),
            ]);

            Mail::to($pengguna->email)->send(
                new KodeResetPasswordMail($pengguna, $kode, (int) config('auth.passwords.users.expire', 60))
            );
        }

        session()->flash('info', 'Kode baru sudah dikirim jika email tersebut terdaftar.');
    }

    public function render()
    {
        return view('livewire.auth.atur-ulang-password');
    }
}
