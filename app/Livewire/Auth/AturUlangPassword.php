<?php

namespace App\Livewire\Auth;

use App\Mail\PasswordResetSuccessMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Atur Ulang Kata Sandi',
    'kelasHalaman' => 'halaman-atur-ulang',
    'warnaTema' => '#7c3aed',
    'merekJudul' => 'Buat kata sandi baru.',
    'merekTeks' => 'Tentukan kata sandi baru untuk akun Anda, lalu masuk seperti biasa.',
])]
class AturUlangPassword extends Component
{
    public string $email = '';

    public string $token = '';

    public string $kataSandi = '';

    public string $kataSandiKonfirmasi = '';

    public function mount(?string $token = null): void
    {
        // Token datang dari parameter rute, email dari query string tautan.
        $this->token = $token ?? '';
        $this->email = (string) request()->query('email', '');
    }

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:150'],
            'token' => ['required', 'string'],
            'kataSandi' => ['required', 'string', AturanKataSandi::defaults(), 'same:kataSandiKonfirmasi'],
            'kataSandiKonfirmasi' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Alamat email tidak terbaca dari tautan.',
            'email.email' => 'Format alamat email tidak valid.',
            'token.required' => 'Tautan tidak lengkap. Silakan minta tautan baru.',
            'kataSandi.required' => 'Masukkan kata sandi baru.',
            'kataSandi.min' => 'Kata sandi minimal 8 karakter.',
            'kataSandi.letters' => 'Kata sandi harus memuat huruf.',
            'kataSandi.numbers' => 'Kata sandi harus memuat angka.',
            'kataSandi.uncompromised' => 'Kata sandi ini pernah bocor di internet. Pilih yang lain.',
            'kataSandi.same' => 'Konfirmasi kata sandi tidak cocok.',
            'kataSandiKonfirmasi.required' => 'Ulangi kata sandi baru Anda.',
        ];
    }

    public function simpan()
    {
        $this->validate();

        // Password broker memeriksa token (tersimpan sebagai hash), masa
        // berlakunya, lalu menghapusnya setelah dipakai.
        $hasil = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->kataSandi,
                'password_confirmation' => $this->kataSandiKonfirmasi,
                'token' => $this->token,
            ],
            function ($pengguna, $kataSandiBaru) {
                $pengguna->forceFill([
                    'password' => bcrypt($kataSandiBaru),
                    'remember_token' => Str::random(60),
                    'reset_token' => null,
                ])->save();

                event(new PasswordReset($pengguna));

                try {
                    Mail::to($pengguna->email)->send(
                        new PasswordResetSuccessMail($pengguna, 'Rumah Scopus Foundation')
                    );
                } catch (\Throwable $e) {
                    // Kata sandi sudah terganti; surat pemberitahuan hanya
                    // pelengkap, jadi kegagalannya cukup dicatat.
                    Log::error('Gagal mengirim pemberitahuan kata sandi berhasil diubah: ' . $e->getMessage());
                }
            }
        );

        if ($hasil !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => $this->pesanGagal($hasil),
            ]);
        }

        session()->flash('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');

        return redirect()->route('login');
    }

    private function pesanGagal(string $hasil): string
    {
        return match ($hasil) {
            Password::INVALID_TOKEN => 'Tautan sudah kedaluwarsa atau pernah dipakai. Silakan minta tautan baru.',
            Password::INVALID_USER => 'Akun dengan alamat email tersebut tidak ditemukan.',
            default => 'Kata sandi gagal diperbarui. Silakan minta tautan baru.',
        };
    }

    public function render()
    {
        return view('livewire.auth.atur-ulang-password');
    }
}
