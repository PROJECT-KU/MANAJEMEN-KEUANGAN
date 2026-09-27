<?php

namespace App\Livewire\Auth;

use App\Mail\VerifikasiEmailMail;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Kirim Ulang Verifikasi',
    'kelasHalaman' => 'halaman-daftar',
    'warnaTema' => '#0d9488',
    'merekJudul' => 'Belum menerima emailnya?',
    'merekTeks' => 'Kami kirimkan ulang tautan verifikasi ke alamat email yang Anda daftarkan.',
    'poinMerek' => [
        ['ikon' => 'surel', 'judul' => 'Cek kotak masuk', 'teks' => 'Termasuk folder spam dan promosi.'],
        ['ikon' => 'jam', 'judul' => 'Berlaku 48 jam', 'teks' => 'Lewat dari itu, minta tautan baru di sini.'],
        ['ikon' => 'centang', 'judul' => 'Sekali klik', 'teks' => 'Akun langsung aktif setelah diverifikasi.'],
    ],
])]
class KirimUlangVerifikasi extends Component
{
    public string $email = '';

    public bool $terkirim = false;

    /** Maksimal permintaan per email+IP. */
    private const BATAS_KIRIM = 3;

    /** Jendela pembatasan (detik). */
    private const JENDELA_KIRIM = 600;

    /** Lama tautan berlaku (jam). */
    private const JAM_BERLAKU = 48;

    public function mount(): void
    {
        // Kalau sedang masuk, alamatnya diisikan otomatis.
        $this->email = auth()->check() ? (string) auth()->user()->email : '';
    }

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

    public function kirimUlang(): void
    {
        $this->validate();

        $kunci = 'verifikasi-ulang|' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_KIRIM)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak permintaan. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        RateLimiter::hit($kunci, self::JENDELA_KIRIM);

        $pengguna = User::where('email', $this->email)->first();

        // Email yang tidak terdaftar atau sudah terverifikasi tetap
        // mendapat tampilan yang sama, supaya halaman ini tidak bisa dipakai
        // menebak alamat mana yang punya akun.
        if ($pengguna && ! $pengguna->email_verified_at) {
            $tautan = URL::temporarySignedRoute('verification.verify', now()->addHours(self::JAM_BERLAKU), [
                'id' => $pengguna->getKey(),
                'hash' => sha1($pengguna->getEmailForVerification()),
            ]);

            try {
                Mail::to($pengguna->email)->send(
                    new VerifikasiEmailMail($pengguna, $tautan, self::JAM_BERLAKU)
                );
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim ulang tautan verifikasi: ' . $e->getMessage());

                throw ValidationException::withMessages([
                    'email' => 'Email gagal dikirim karena layanan email sedang bermasalah. Coba beberapa saat lagi.',
                ]);
            }
        }

        $this->terkirim = true;
    }

    public function render()
    {
        return view('livewire.auth.kirim-ulang-verifikasi', [
            'jamBerlaku' => self::JAM_BERLAKU,
        ]);
    }
}
