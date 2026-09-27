<?php

namespace App\Livewire\Akun;

use App\Mail\PemberitahuanPinMail;
use App\Rules\PinAman;
use App\Support\IngatanMasuk;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Pengaturan PIN masuk di halaman profil.
 *
 * PIN adalah jalan pintas, bukan pengganti kata sandi: mengaktifkan,
 * mengubah, dan mematikannya selalu meminta kata sandi pemilik akun.
 */
class PengaturanPin extends Component
{
    public string $kataSandi = '';

    public string $pin = '';

    public string $pinKonfirmasi = '';

    /** Dipakai saat mendaftarkan perangkat baru memakai PIN yang sudah ada. */
    public string $pinPerangkat = '';

    /** Pesan hasil aksi terakhir, ditampilkan di dalam tab. */
    public string $pesan = '';

    /** 'sukses' atau 'galat'. */
    public string $jenisPesan = 'sukses';

    /** Berapa kali PIN boleh salah saat mendaftarkan perangkat. */
    private const BATAS_PIN_SALAH = 5;

    /** Berapa kali kata sandi boleh salah sebelum ditunda. */
    private const BATAS_SANDI_SALAH = 5;

    private const LAMA_KUNCI = 900;

    protected function rules(): array
    {
        return [
            'kataSandi' => ['required', 'string'],
            'pin' => ['required', 'digits:' . $this->panjangPin(), new PinAman($this->pengguna()->tanggal_lahir)],
            'pinKonfirmasi' => ['required', 'same:pin'],
        ];
    }

    protected function messages(): array
    {
        return [
            'kataSandi.required' => 'Masukkan kata sandi akun Anda.',
            'pin.required' => 'Masukkan PIN baru.',
            'pin.digits' => 'PIN harus terdiri dari ' . $this->panjangPin() . ' angka.',
            'pinKonfirmasi.required' => 'Ulangi PIN baru Anda.',
            'pinKonfirmasi.same' => 'Ulangan PIN tidak sama dengan PIN baru.',
        ];
    }

    /**
     * Apakah peramban ini sudah terdaftar sebagai perangkat PIN milik akun
     * yang sedang dibuka. PIN berlaku per perangkat, sebab di halaman masuk
     * akun dikenali dari ingatan perangkat, bukan dari isian.
     */
    public function perangkatSiap(): bool
    {
        $ingatan = IngatanMasuk::baca();

        return $ingatan !== null
            && $ingatan['mode'] === 'pin'
            && $this->ingatanMilikSaya($ingatan, $this->pengguna());
    }

    /**
     * Daftarkan perangkat yang sedang dipakai memakai PIN yang sudah ada.
     * Inilah jalan untuk memakai PIN di HP setelah PIN dibuat di komputer:
     * masuk sekali dengan kata sandi di HP, lalu masukkan PIN di sini.
     */
    public function aktifkanDiPerangkat(): void
    {
        $this->pesan = '';

        $pengguna = $this->pengguna();

        if (! $pengguna->pinAktif()) {
            $this->jenisPesan = 'galat';
            $this->pesan = 'PIN belum aktif pada akun ini. Buat PIN dulu di bawah.';

            return;
        }

        $this->validateOnly('pinPerangkat', [
            'pinPerangkat' => ['required', 'digits:' . $this->panjangPin()],
        ], [
            'pinPerangkat.required' => 'Masukkan PIN Anda.',
            'pinPerangkat.digits' => 'PIN terdiri dari ' . $this->panjangPin() . ' angka.',
        ]);

        $kunci = 'pin-perangkat|' . $pengguna->getKey();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PIN_SALAH)) {
            throw ValidationException::withMessages([
                'pinPerangkat' => 'Terlalu banyak PIN salah. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        if (! $pengguna->pinCocok($this->pinPerangkat)) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);

            $this->reset('pinPerangkat');

            throw ValidationException::withMessages([
                'pinPerangkat' => 'PIN tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);

        $ingatan = IngatanMasuk::baca();

        IngatanMasuk::simpan(
            (string) $pengguna->username,
            'pin',
            $this->ingatanMilikSaya($ingatan, $pengguna) ? $ingatan['ingat'] : false,
            (int) $pengguna->getKey()
        );

        $this->reset('pinPerangkat');

        $this->jenisPesan = 'sukses';
        $this->pesan = 'Perangkat ini sekarang bisa dipakai masuk dengan PIN.';
    }

    /** Perangkat ini tidak lagi boleh memakai PIN (akun tetap berPIN). */
    public function lupakanPerangkat(): void
    {
        $this->pesan = '';

        $ingatan = IngatanMasuk::baca();

        if ($this->ingatanMilikSaya($ingatan, $this->pengguna())) {
            IngatanMasuk::lupakan();
        }

        $this->jenisPesan = 'sukses';
        $this->pesan = 'Perangkat ini dilupakan. PIN Anda tetap aktif dan bisa dipakai di perangkat lain.';
    }

    /** Aktifkan PIN, atau ganti PIN yang sudah ada. */
    public function simpan(): void
    {
        $this->pesan = '';

        // Kata sandi dan PIN ikut terkirim balik ke peramban lewat
        // wire:snapshot; pada tiap kegagalan keduanya dibuang.
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

            throw $e;
        }

        $pengguna = $this->pengguna();
        $sudahAda = $pengguna->pinAktif();

        $this->pastikanKataSandiBenar($pengguna);

        $pengguna->aturPin($this->pin);

        // Perangkat yang dipakai mengaktifkan PIN langsung diingat, supaya di
        // halaman masuk cukup mengetik PIN tanpa username dan kata sandi.
        // Penanda "Ingat saya" dibiarkan apa adanya: mengaktifkan PIN tidak
        // ikut membuat isian username terisi otomatis kalau tidak diminta.
        $ingatan = IngatanMasuk::baca();

        IngatanMasuk::simpan(
            $this->ingatanMilikSaya($ingatan, $pengguna) ? $ingatan['identitas'] : (string) $pengguna->username,
            'pin',
            $this->ingatanMilikSaya($ingatan, $pengguna) ? $ingatan['ingat'] : false,
            (int) $pengguna->getKey()
        );

        $this->beriTahu($pengguna, $sudahAda ? 'diubah' : 'diaktifkan');

        $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

        $this->jenisPesan = 'sukses';
        $this->pesan = $sudahAda
            ? 'PIN berhasil diubah. PIN lama sudah tidak berlaku.'
            : 'PIN berhasil diaktifkan. Di halaman masuk, pilih tab PIN lalu masukkan ' . $this->panjangPin() . ' angka PIN Anda.';
    }

    /** Matikan PIN; masuk kembali hanya dengan kata sandi. */
    public function nonaktifkan(): void
    {
        $this->pesan = '';

        $this->validateOnly('kataSandi', ['kataSandi' => ['required', 'string']]);

        $pengguna = $this->pengguna();

        if (! $pengguna->pinAktif()) {
            $this->jenisPesan = 'galat';
            $this->pesan = 'PIN memang belum aktif pada akun ini.';

            return;
        }

        $this->pastikanKataSandiBenar($pengguna);

        $pengguna->matikanPin();

        // Ingatan perangkat: kalau tadinya hanya dipasang untuk PIN, sekalian
        // dihapus. Kalau pengguna memang mencentang "Ingat saya", username-nya
        // tetap diingat tapi cara masuknya kembali ke kata sandi.
        $ingatan = IngatanMasuk::baca();

        if ($this->ingatanMilikSaya($ingatan, $pengguna)) {
            if ($ingatan['ingat']) {
                IngatanMasuk::simpan($ingatan['identitas'], 'sandi', true, (int) $pengguna->getKey());
            } else {
                IngatanMasuk::lupakan();
            }
        }

        $this->beriTahu($pengguna, 'dinonaktifkan');

        $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

        $this->jenisPesan = 'sukses';
        $this->pesan = 'PIN dinonaktifkan. Masuk kini hanya bisa memakai kata sandi.';
    }

    private function pastikanKataSandiBenar(User $pengguna): void
    {
        $kunci = 'atur-pin|' . $pengguna->getKey();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_SANDI_SALAH)) {
            throw ValidationException::withMessages([
                'kataSandi' => 'Terlalu banyak kata sandi salah. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        if (! Hash::check($this->kataSandi, (string) $pengguna->password)) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);

            $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

            throw ValidationException::withMessages([
                'kataSandi' => 'Kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);
    }

    private function beriTahu(User $pengguna, string $aksi): void
    {
        try {
            Mail::to($pengguna->email)->send(
                new PemberitahuanPinMail($pengguna, $aksi, (string) request()->ip())
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pemberitahuan PIN: ' . $e->getMessage());
        }
    }

    /** Apakah ingatan di perangkat ini memang milik akun yang sedang dibuka. */
    private function ingatanMilikSaya(?array $ingatan, User $pengguna): bool
    {
        if ($ingatan === null) {
            return false;
        }

        if ($ingatan['uid'] !== null) {
            return $ingatan['uid'] === (int) $pengguna->getKey();
        }

        return in_array($ingatan['identitas'], [$pengguna->username, $pengguna->email], true);
    }

    private function pengguna(): User
    {
        return auth()->user();
    }

    private function panjangPin(): int
    {
        return (int) config('auth.pin.panjang', 6);
    }

    public function render()
    {
        return view('livewire.akun.pengaturan-pin', [
            'pengguna' => $this->pengguna(),
            'panjangPin' => $this->panjangPin(),
        ]);
    }
}
