<?php

namespace App\Livewire\Auth;

use App\AktivitasMasuk;
use App\Mail\PeringatanKeamananMail;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Masuk Akun',
    'kelasHalaman' => 'halaman-masuk',
    'warnaTema' => '#0891b2',
    'merekJudul' => 'Selamat datang kembali.',
    'merekTeks' => 'Masuk untuk melanjutkan ke layanan Rumah Scopus Foundation.',
])]
class Masuk extends Component
{
    /** Boleh diisi username ATAU alamat email. */
    public string $identitas = '';

    public string $kataSandi = '';

    public bool $ingatSaya = false;

    /** Sisa detik penguncian, dipakai untuk hitung mundur di layar. */
    public int $detikTunggu = 0;

    /** Berapa kali percobaan gagal sebelum dikunci sementara. */
    private const BATAS_PERCOBAAN = 5;

    /** Lama kunci setelah batas percobaan tercapai (detik). */
    private const LAMA_KUNCI = 60;

    /**
     * Pembatas kedua, dihitung per akun tanpa memandang IP. Tanpa ini,
     * penyerang yang berganti-ganti IP bisa terus menebak satu akun karena
     * pembatas pertama hanya berlaku per kombinasi identitas+IP.
     */
    private const BATAS_PER_AKUN = 20;

    /** Lama kunci pembatas per akun (detik). */
    private const LAMA_KUNCI_AKUN = 900;

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
        $kunciAkun = $this->kunciPembatasAkun();

        if (RateLimiter::tooManyAttempts($kunciAkun, self::BATAS_PER_AKUN)) {
            AktivitasMasuk::catat(null, $this->identitas, false, 'akun dikunci sementara');
            $this->beriTahuPemilikAkun();

            throw ValidationException::withMessages([
                'identitas' => 'Akun ini dikunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunciAkun) / 60) . ' menit.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
            AktivitasMasuk::catat(null, $this->identitas, false, 'dikunci sementara');
            $this->beriTahuPemilikAkun();

            $this->detikTunggu = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'identitas' => 'Terlalu banyak percobaan. Coba lagi dalam ' . $this->detikTunggu . ' detik.',
            ]);
        }

        // Username atau email, ditentukan dari isian yang diberikan.
        $kolom = filter_var($this->identitas, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $pengguna = User::where($kolom, $this->identitas)->first();

        if ($pengguna && $pengguna->status === 'nonactive') {
            AktivitasMasuk::catat($pengguna, $this->identitas, false, 'akun nonaktif');

            throw ValidationException::withMessages([
                'identitas' => 'Akun ini dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.',
            ]);
        }

        if (! Auth::attempt([$kolom => $this->identitas, 'password' => $this->kataSandi], $this->ingatSaya)) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);
            RateLimiter::hit($kunciAkun, self::LAMA_KUNCI_AKUN);

            AktivitasMasuk::catat($pengguna, $this->identitas, false, $pengguna ? 'kata sandi salah' : 'akun tidak ditemukan');

            $this->reset('kataSandi');

            throw ValidationException::withMessages([
                'identitas' => 'Username/email atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);
        RateLimiter::clear($kunciAkun);

        AktivitasMasuk::catat(Auth::user(), $this->identitas, true);

        // Cegah session fixation setelah pergantian identitas.
        session()->regenerate();

        return redirect()->intended('/account/dashboard');
    }

    private function kunciPembatas(): string
    {
        return 'masuk|' . Str::lower($this->identitas) . '|' . request()->ip();
    }

    private function kunciPembatasAkun(): string
    {
        return 'masuk-akun|' . Str::lower($this->identitas);
    }

    public function render()
    {
        return view('livewire.auth.masuk');
    }

    /**
     * Beri tahu pemilik akun saat akunnya dikunci karena percobaan beruntun.
     * Dikirim sekali per periode kunci supaya tidak jadi banjir surat.
     */
    private function beriTahuPemilikAkun(): void
    {
        $kolom = filter_var($this->identitas, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $pengguna = User::where($kolom, $this->identitas)->first();

        if (! $pengguna) {
            return;
        }

        $penanda = 'peringatan-masuk|' . $pengguna->getKey() . '|' . request()->ip();

        if (Cache::get($penanda)) {
            return;
        }

        Cache::put($penanda, true, now()->addMinutes(30));

        try {
            Mail::to($pengguna->email)->send(
                new PeringatanKeamananMail($pengguna, (string) request()->ip(), self::BATAS_PERCOBAAN)
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim peringatan keamanan: ' . $e->getMessage());
        }
    }
}
