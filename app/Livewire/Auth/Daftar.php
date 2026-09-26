<?php

namespace App\Livewire\Auth;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Daftar Akun',
    'kelasHalaman' => 'halaman-daftar',
    'merekJudul' => 'Mulai dari satu akun.',
    'merekTeks' => 'Buat akun untuk mengikuti Scopus Camp, Clinik Scopus, dan layanan Rumah Scopus Foundation lainnya.',
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

    protected function rules(): array
    {
        return [
            'namaLengkap' => ['required', 'string', 'min:3', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'min:4', 'max:30', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email:rfc', 'max:150', Rule::unique('users', 'email')],
            'telp' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'kataSandi' => ['required', 'string', 'min:8', 'same:kataSandiKonfirmasi'],
            'kataSandiKonfirmasi' => ['required', 'string'],
            'setuju' => ['accepted'],
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
            'kataSandi.required' => 'Masukkan kata sandi Anda.',
            'kataSandi.min' => 'Kata sandi minimal 8 karakter.',
            'kataSandi.same' => 'Konfirmasi kata sandi tidak cocok.',
            'kataSandiKonfirmasi.required' => 'Ulangi kata sandi Anda.',
            'setuju.accepted' => 'Centang dulu kebijakan dan ketentuan.',
        ];
    }

    /** Validasi per medan saat pengguna berpindah isian. */
    public function updated(string $medan): void
    {
        $this->validateOnly($medan);
    }

    public function daftar()
    {
        $data = $this->validate();

        User::create([
            'full_name' => $data['namaLengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'telp' => $data['telp'] ?: null,
            'password' => Hash::make($data['kataSandi']),
        ]);

        session()->flash('success', 'Akun berhasil dibuat. Silakan masuk memakai username dan kata sandi Anda.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.daftar');
    }
}
