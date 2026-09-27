<?php

namespace App\Livewire\Akun;

use App\AktivitasMasuk;
use App\Support\PenandaPerangkat;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Ringkasan keamanan akun untuk pemiliknya sendiri: riwayat percobaan masuk
 * dan tombol untuk mengakhiri sesi di perangkat lain.
 *
 * Halaman jejak masuk yang lama hanya bisa dibuka manager/CEO/admin. Padahal
 * pemilik akunlah yang paling cepat sadar kalau ada masuk yang bukan dirinya.
 */
class KeamananAkun extends Component
{
    public string $kataSandi = '';

    public string $pesan = '';

    public string $jenisPesan = 'sukses';

    /** Berapa banyak baris riwayat yang ditampilkan. */
    private const JUMLAH_RIWAYAT = 15;

    private const BATAS_SANDI_SALAH = 5;

    private const LAMA_KUNCI = 900;

    /** Akhiri sesi di semua perangkat lain, sesi ini tetap hidup. */
    public function keluarkanPerangkatLain(): void
    {
        $this->pesan = '';

        $this->validate(
            ['kataSandi' => ['required', 'string']],
            ['kataSandi.required' => 'Masukkan kata sandi akun Anda.']
        );

        $pengguna = $this->pengguna();
        $kunci = 'keluarkan-perangkat|' . $pengguna->getKey();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_SANDI_SALAH)) {
            throw ValidationException::withMessages([
                'kataSandi' => 'Terlalu banyak kata sandi salah. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        if (! Hash::check($this->kataSandi, (string) $pengguna->password)) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);

            $this->reset('kataSandi');

            throw ValidationException::withMessages([
                'kataSandi' => 'Kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);

        // Menyegarkan sidik kata sandi di sesi ini; sesi lain yang memegang
        // sidik lama otomatis berakhir lewat middleware AuthenticateSession.
        Auth::logoutOtherDevices($this->kataSandi);

        AktivitasMasuk::catat($pengguna, (string) ($pengguna->username ?? $pengguna->email), true, 'keluar dari perangkat lain');

        $this->reset('kataSandi');

        $this->jenisPesan = 'sukses';
        $this->pesan = 'Sesi di perangkat lain sudah diakhiri. Perangkat ini tetap masuk.';
    }

    private function pengguna(): User
    {
        return auth()->user();
    }

    public function render()
    {
        $pengguna = $this->pengguna();

        return view('livewire.akun.keamanan-akun', [
            'pengguna' => $pengguna,
            'perangkatIni' => PenandaPerangkat::ambil(),
            'riwayat' => AktivitasMasuk::where('user_id', $pengguna->getKey())
                ->latest('id')
                ->limit(self::JUMLAH_RIWAYAT)
                ->get(),
            'gagalTerakhir' => AktivitasMasuk::where('user_id', $pengguna->getKey())
                ->where('berhasil', false)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ]);
    }
}
