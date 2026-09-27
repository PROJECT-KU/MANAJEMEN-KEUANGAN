<?php

namespace App\Livewire\Auth;

use App\Mail\VerifikasiEmailMail;
use App\Rules\BukanEmailSekaliPakai;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Daftar Akun',
    'kelasHalaman' => 'halaman-daftar',
    'warnaTema' => '#0d9488',
    'merekJudul' => 'Mulai dari satu akun.',
    'merekTeks' => 'Buat akun untuk mengikuti Scopus Camp, Clinik Scopus, dan layanan Rumah Scopus Foundation lainnya.',
    'poinMerek' => [
        ['ikon' => 'orang', 'judul' => 'Isi data diri', 'teks' => 'Nama, username, email, dan kata sandi.'],
        ['ikon' => 'surel', 'judul' => 'Verifikasi email', 'teks' => 'Kami kirim tautan aman ke alamat Anda.'],
        ['ikon' => 'centang', 'judul' => 'Langsung dipakai', 'teks' => 'Akun siap untuk semua layanan kami.'],
    ],
])]
class Daftar extends Component
{
    public string $namaLengkap = '';

    public string $username = '';

    public string $email = '';

    public string $telp = '';

    public string $kataSandi = '';

    public string $kataSandiKonfirmasi = '';

    public bool $setuju = false;

    /**
     * Jebakan bot: tersembunyi dari manusia. Namanya sengaja tidak bermakna
     * ('situs'/'website' justru diincar pengisi otomatis peramban).
     */
    public string $kodePos2 = '';

    /** Maksimal pendaftaran per IP dalam satu jam. */
    private const BATAS_DAFTAR = 5;

    /** Lama tautan verifikasi berlaku (jam). */
    private const JAM_VERIFIKASI = 48;

    /** Ambang paling cepat manusia bisa mengisi formulir ini (detik). */
    private const DETIK_TERCEPAT = 4;

    public function mount(): void
    {
        // Waktu buka disimpan di sesi, bukan di properti komponen, supaya
        // tidak bisa disetel ulang dari sisi peramban.
        session(['daftar_dibuka' => now()->timestamp]);
    }

    protected function rules(): array
    {
        return [
            'namaLengkap' => ['required', 'string', 'min:3', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:30', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email:rfc', 'max:150', new BukanEmailSekaliPakai, Rule::unique('users', 'email')],
            'telp' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'kataSandi' => ['required', 'string', 'max:72', AturanKataSandi::defaults(), 'same:kataSandiKonfirmasi'],
            'kataSandiKonfirmasi' => ['required', 'string'],
            'setuju' => ['accepted'],
            'kodePos2' => ['prohibited'],
        ];
    }

    protected function messages(): array
    {
        return [
            'namaLengkap.required' => 'Masukkan nama lengkap Anda.',
            'namaLengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'username.required' => 'Masukkan username Anda.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'username.min' => 'Username minimal 4 karakter.',
            'username.unique' => 'Username ini sudah dipakai.',
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'telp.regex' => 'Nomor telepon hanya boleh berisi angka dan tanda + - ( ).',
            'kataSandi.max' => 'Kata sandi maksimal 72 karakter.',
            'kataSandi.required' => 'Masukkan kata sandi Anda.',
            'kataSandi.min' => 'Kata sandi minimal 8 karakter.',
            'kataSandi.letters' => 'Kata sandi harus memuat huruf.',
            'kataSandi.numbers' => 'Kata sandi harus memuat angka.',
            'kataSandi.uncompromised' => 'Kata sandi ini pernah bocor di internet. Pilih yang lain.',
            'kataSandi.same' => 'Konfirmasi kata sandi tidak cocok.',
            'kataSandiKonfirmasi.required' => 'Ulangi kata sandi Anda.',
            'setuju.accepted' => 'Centang dulu kebijakan dan ketentuan.',
            'kodePos2.prohibited' => 'Pendaftaran tidak dapat diproses. Muat ulang halaman, lalu isi formulir tanpa bantuan pengisi otomatis.',
        ];
    }

    /** Validasi per medan saat pengguna berpindah isian. */
    public function updated(string $medan): void
    {
        $this->validateOnly($medan);

        // Aturan 'same' menempel pada kataSandi. Tanpa pemeriksaan ulang ini,
        // galat "konfirmasi tidak cocok" tetap tampil walau konfirmasinya
        // sudah dibetulkan.
        if ($medan === 'kataSandiKonfirmasi' && $this->kataSandi !== '') {
            $this->validateOnly('kataSandi');
        }
    }

    /**
     * Livewire mengirim seluruh properti publik kembali ke peramban dan
     * menyimpannya di atribut wire:snapshot. Kalau kiriman gagal, kata sandi
     * yang baru diketik akan menganggur di dalam DOM dalam bentuk terbaca.
     * Karena itu setiap kegagalan membersihkan isian rahasianya.
     */
    private function bersihkanRahasia(): void
    {
        $this->reset('kataSandi', 'kataSandiKonfirmasi');
    }

    public function daftar()
    {
        $kunci = 'daftar|' . request()->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_DAFTAR)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak pendaftaran dari jaringan ini. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        try {
            $data = $this->validate();
        } catch (ValidationException $e) {
            $this->bersihkanRahasia();

            throw $e;
        }

        // Perangkap waktu: formulir yang terkirim beberapa milidetik setelah
        // dibuka hampir pasti bukan diketik manusia. Honeypot menangkap
        // pengisi otomatis, bagian ini menangkap skrip yang mengirim langsung.
        $dibuka = (int) session('daftar_dibuka', 0);

        if ($dibuka > 0 && (now()->timestamp - $dibuka) < self::DETIK_TERCEPAT) {
            RateLimiter::hit($kunci, 3600);
            $this->bersihkanRahasia();

            throw ValidationException::withMessages([
                'email' => 'Formulir terkirim terlalu cepat. Periksa kembali isiannya, lalu kirim sekali lagi.',
            ]);
        }

        RateLimiter::hit($kunci, 3600);

        $pengguna = User::create([
            'full_name' => $data['namaLengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'telp' => $data['telp'] ?: null,
            'password' => Hash::make($data['kataSandi']),
        ]);

        $terkirim = $this->kirimTautanVerifikasi($pengguna);

        session()->flash('success', $terkirim
            ? 'Akun berhasil dibuat. Kami kirim tautan verifikasi ke ' . $pengguna->email . '; silakan cek email Anda, lalu masuk.'
            : 'Akun berhasil dibuat, tetapi email verifikasi gagal dikirim. Anda tetap bisa masuk dan memverifikasi email dari halaman profil.');

        return redirect()->route('login');
    }

    /**
     * Kirim tautan verifikasi bertanda tangan. Kegagalan pengiriman tidak boleh
     * membatalkan pendaftaran yang sudah berhasil.
     */
    private function kirimTautanVerifikasi(User $pengguna): bool
    {
        $tautan = URL::temporarySignedRoute('verification.verify', now()->addHours(self::JAM_VERIFIKASI), [
            'id' => $pengguna->getKey(),
            'hash' => sha1($pengguna->getEmailForVerification()),
        ]);

        try {
            Mail::to($pengguna->email)->send(
                new VerifikasiEmailMail($pengguna, $tautan, self::JAM_VERIFIKASI)
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim tautan verifikasi email: ' . $e->getMessage());

            return false;
        }
    }

    public function render()
    {
        return view('livewire.auth.daftar');
    }
}
