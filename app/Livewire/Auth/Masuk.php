<?php

namespace App\Livewire\Auth;

use App\AktivitasMasuk;
use App\Mail\PemberitahuanPinMail;
use App\Mail\MasukPerangkatBaruMail;
use App\Mail\PeringatanKeamananMail;
use App\Mail\TautanMatikanPinMail;
use App\Support\IngatanMasuk;
use App\Support\PenandaPerangkat;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman masuk dengan dua cara:
 *
 *  1. Kata sandi — selalu tersedia. Centang "Ingat saya" hanya menyimpan
 *     username/email di perangkat ini, tidak pernah kata sandinya.
 *  2. PIN — jalan pintas enam angka. Baru hidup setelah pemilik akun
 *     mengaktifkannya sendiri di Profil, dan hanya di perangkat tempat PIN
 *     itu diaktifkan. Karena akunnya sudah dikenali perangkat, masuk dengan
 *     PIN tidak perlu mengetik username maupun kata sandi.
 */
#[Layout('components.layouts.auth', [
    'judulHalaman' => 'Masuk Akun',
    'kelasHalaman' => 'halaman-masuk',
    'warnaTema' => '#3730a3',
    'merekJudul' => 'Selamat datang kembali.',
    'merekTeks' => 'Masuk untuk melanjutkan ke layanan Rumah Scopus Foundation.',
    'poinMerek' => [
        ['ikon' => 'kunci', 'judul' => 'Kata sandi atau PIN', 'teks' => 'Pilih cara masuk yang paling cepat untuk Anda.'],
        ['ikon' => 'perisai', 'judul' => 'Akun terjaga', 'teks' => 'Percobaan masuk dibatasi dan dicatat.'],
        ['ikon' => 'jejak', 'judul' => 'Riwayat terpantau', 'teks' => 'Masuk dari perangkat baru langsung dikabari.'],
    ],
])]
class Masuk extends Component
{
    /** Boleh diisi username ATAU alamat email. */
    public string $identitas = '';

    public string $kataSandi = '';

    /** Jalan pintas enam angka, hanya untuk akun yang mengaktifkannya. */
    public string $pin = '';

    /** 'sandi' atau 'pin'. */
    public string $mode = 'sandi';

    public bool $ingatSaya = false;

    /** Username/email yang diingat perangkat ini, untuk ditampilkan. */
    public string $akunPerangkat = '';

    /** Isian identitas terisi otomatis karena "Ingat saya" pernah dicentang. */
    public bool $identitasTersimpan = false;

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

    public function mount(): void
    {
        $ingatan = IngatanMasuk::baca();

        if ($ingatan === null) {
            return;
        }

        $this->akunPerangkat = $ingatan['identitas'];

        // Isian hanya diisikan otomatis bila pengguna memang meminta lewat
        // centang "Ingat saya".
        if ($ingatan['ingat']) {
            $this->identitas = $ingatan['identitas'];
            $this->ingatSaya = true;
            $this->identitasTersimpan = true;
        }

        if ($ingatan['mode'] === 'pin' && $this->siapPin()) {
            $this->mode = 'pin';
        }
    }

    /**
     * PIN siap dipakai bila perangkat ini mengingat satu akun DAN akun itu
     * memang sudah mengaktifkan PIN dari halaman profilnya. Kalau belum,
     * tab PIN di layar ditampilkan mati.
     */
    #[Computed]
    public function siapPin(): bool
    {
        $pengguna = $this->penggunaPerangkat();

        return $pengguna !== null && $pengguna->pinAktif();
    }

    protected function rules(): array
    {
        if ($this->mode === 'pin') {
            // Identitas tidak diminta: akunnya sudah dikenali perangkat ini.
            return [
                'pin' => ['required', 'digits:' . $this->panjangPin()],
            ];
        }

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
            'pin.required' => 'Masukkan PIN Anda.',
            'pin.digits' => 'PIN terdiri dari ' . $this->panjangPin() . ' angka.',
        ];
    }

    /** Pindah antara masuk dengan kata sandi dan masuk dengan PIN. */
    public function gantiMode(string $mode): void
    {
        // Mode PIN hanya boleh dipilih bila memang sudah aktif di perangkat ini.
        $this->mode = ($mode === 'pin' && $this->siapPin()) ? 'pin' : 'sandi';

        $this->reset('kataSandi', 'pin', 'detikTunggu');
        $this->resetValidation();
    }

    /**
     * Centang "Ingat saya" langsung menyimpan identitas yang sedang diketik,
     * dan menghapusnya begitu centangnya dilepas.
     */
    public function updatedIngatSaya($nilai): void
    {
        $ingatan = IngatanMasuk::baca();

        if ($nilai) {
            $this->simpanIngatan(
                trim($this->identitas) !== '' ? $this->identitas : $this->akunPerangkat,
                $ingatan['mode'] ?? 'sandi',
                true,
                $ingatan['uid'] ?? null
            );

            return;
        }

        // Centang dilepas: isian tidak diisikan otomatis lagi. Kalau perangkat
        // ini dipakai untuk PIN, ingatannya dipertahankan tanpa penanda itu
        // supaya PIN tetap bisa dipakai.
        if ($ingatan !== null && $ingatan['mode'] === 'pin') {
            $this->simpanIngatan($ingatan['identitas'], 'pin', false, $ingatan['uid']);
            $this->identitasTersimpan = false;

            return;
        }

        $this->lupakanIngatan();
    }

    /** Tombol "Bukan Anda?" / "Ganti akun": lupakan ingatan perangkat ini. */
    public function lupakanSaya(): void
    {
        $this->lupakanIngatan();

        $this->reset('identitas', 'kataSandi', 'pin', 'akunPerangkat');
        $this->ingatSaya = false;
        $this->mode = 'sandi';
        $this->resetValidation();
    }

    /**
     * Lupa PIN: kirim tautan ke email pemilik akun untuk mematikan PIN.
     * Tautannya tidak bisa dipakai masuk, hanya mencabut jalan pintas.
     */
    public function mintaMatikanPin(): void
    {
        $pengguna = $this->penggunaPerangkat();

        if ($pengguna === null || ! $pengguna->pinAktif()) {
            $this->mode = 'sandi';

            session()->flash('error', 'PIN tidak aktif di perangkat ini. Silakan masuk dengan kata sandi.');

            return;
        }

        $kunci = 'matikan-pin|' . $pengguna->getKey();

        if (RateLimiter::tooManyAttempts($kunci, 3)) {
            throw ValidationException::withMessages([
                'pin' => 'Permintaan terlalu sering. Coba lagi dalam '
                    . ceil(RateLimiter::availableIn($kunci) / 60) . ' menit.',
            ]);
        }

        RateLimiter::hit($kunci, 3600);

        $tautan = URL::temporarySignedRoute('pin.matikan', now()->addMinutes(30), [
            'id' => $pengguna->getKey(),
            'hash' => sha1($pengguna->getEmailForVerification()),
        ]);

        try {
            Mail::to($pengguna->email)->send(new TautanMatikanPinMail($pengguna, $tautan, 30));

            session()->flash('success', 'Tautan untuk mematikan PIN sudah dikirim ke email akun ini. Tautannya berlaku 30 menit.');
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim tautan matikan PIN: ' . $e->getMessage());

            session()->flash('error', 'Email gagal dikirim. Silakan masuk dengan kata sandi, lalu ubah PIN dari halaman profil.');
        }
    }

    public function masuk()
    {
        $this->validate();

        return $this->mode === 'pin' ? $this->masukDenganPin() : $this->masukDenganSandi();
    }

    // ------------------------------------------------------------- kata sandi

    private function masukDenganSandi()
    {
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

        $kolom = $this->kolomIdentitas($this->identitas);
        $pengguna = User::where($kolom, $this->identitas)->first();

        // Kata sandi diperiksa lebih dulu. Kalau urutannya dibalik, pesan
        // "akun dinonaktifkan" akan memberi tahu orang asing bahwa username
        // itu memang ada.
        $cocok = $pengguna && Hash::check($this->kataSandi, (string) $pengguna->password);

        if (! $cocok) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI);
            RateLimiter::hit($kunciAkun, self::LAMA_KUNCI_AKUN);

            AktivitasMasuk::catat($pengguna, $this->identitas, false, $pengguna ? 'kata sandi salah' : 'akun tidak ditemukan');

            $this->reset('kataSandi');

            throw ValidationException::withMessages([
                'identitas' => 'Username/email atau kata sandi tidak cocok.',
            ]);
        }

        $this->pastikanAkunAktif($pengguna);

        // Ikut menyegarkan sidik kata sandi bila tingkat pengacakannya berubah.
        if (Hash::needsRehash((string) $pengguna->password)) {
            $pengguna->forceFill(['password' => Hash::make($this->kataSandi)])->save();
        }

        $perangkatBaru = $this->perangkatBaru($pengguna);

        Auth::login($pengguna, $this->ingatSaya);

        RateLimiter::clear($kunci);
        RateLimiter::clear($kunciAkun);

        AktivitasMasuk::catat($pengguna, $this->identitas, true);

        if ($perangkatBaru) {
            $this->beriTahuPerangkatBaru($pengguna, 'kata sandi');
        }

        return $this->selesaikanMasuk('sandi');
    }

    // -------------------------------------------------------------------- PIN

    private function masukDenganPin()
    {
        // Akun diambil dari ingatan perangkat, bukan dari properti komponen:
        // nilai properti bisa disetel dari sisi peramban, dan PIN enam angka
        // yang bisa diarahkan ke akun mana pun sama dengan tebakan bebas.
        $ingatan = IngatanMasuk::baca();
        $pengguna = $this->penggunaPerangkat();

        if ($ingatan === null || $pengguna === null || ! $pengguna->pinAktif()) {
            $this->mode = 'sandi';
            $this->reset('pin');

            throw ValidationException::withMessages([
                'pin' => 'PIN belum aktif di perangkat ini. Masuk dengan kata sandi, lalu aktifkan PIN dari '
                    . 'halaman Profil.',
            ]);
        }

        $this->identitas = $ingatan['identitas'];

        $kunciPin = $this->kunciPembatasPin($pengguna);
        $batas = $this->batasPinGagal();

        if (RateLimiter::tooManyAttempts($kunciPin, $batas)) {
            AktivitasMasuk::catat($pengguna, $this->identitas, false, 'PIN dikunci sementara');

            $this->detikTunggu = RateLimiter::availableIn($kunciPin);

            throw ValidationException::withMessages([
                'pin' => 'Terlalu banyak percobaan PIN. Masuk dengan kata sandi, atau coba lagi dalam '
                    . ceil($this->detikTunggu / 60) . ' menit.',
            ]);
        }

        $this->pastikanAkunAktif($pengguna);

        if (! $pengguna->pinCocok($this->pin)) {
            RateLimiter::hit($kunciPin, $this->kunciPinDetik());

            AktivitasMasuk::catat($pengguna, $this->identitas, false, 'PIN salah');

            $this->reset('pin');

            // Enam angka terlalu sedikit untuk dibiarkan ditebak terus: begitu
            // batasnya tercapai, PIN dimatikan dan pemiliknya diberi tahu.
            // Masuk tetap bisa lewat kata sandi.
            if (RateLimiter::attempts($kunciPin) >= $batas) {
                $pengguna->matikanPin();
                $this->beriTahuPerubahanPin($pengguna, 'dinonaktifkan-otomatis');
                $this->simpanIngatan($ingatan['identitas'], 'sandi', $ingatan['ingat'], $pengguna->getKey());
                $this->mode = 'sandi';

                throw ValidationException::withMessages([
                    'pin' => 'PIN dinonaktifkan karena terlalu banyak percobaan salah. Silakan masuk dengan kata '
                        . 'sandi, lalu aktifkan PIN baru dari halaman profil.',
                ]);
            }

            $sisa = max(0, $batas - RateLimiter::attempts($kunciPin));

            throw ValidationException::withMessages([
                'pin' => 'PIN tidak cocok. Sisa ' . $sisa . ' percobaan sebelum PIN dinonaktifkan.',
            ]);
        }

        RateLimiter::clear($kunciPin);

        $perangkatBaru = $this->perangkatBaru($pengguna);

        Auth::login($pengguna, $ingatan['ingat']);

        AktivitasMasuk::catat($pengguna, $this->identitas, true, 'masuk dengan PIN');

        if ($perangkatBaru) {
            $this->beriTahuPerangkatBaru($pengguna, 'PIN');
        }

        return $this->selesaikanMasuk('pin');
    }

    // ------------------------------------------------------------------ bantu

    /** Langkah penutup yang sama untuk kedua cara masuk. */
    private function selesaikanMasuk(string $mode)
    {
        $ingatan = IngatanMasuk::baca();
        $pengguna = Auth::user();

        // Ingatan PIN milik akun yang sama tidak boleh hilang hanya karena
        // kali ini masuknya memakai kata sandi.
        $pinPerangkat = $ingatan !== null
            && $ingatan['mode'] === 'pin'
            && $ingatan['uid'] === $pengguna->getKey();

        if ($mode === 'pin') {
            $this->simpanIngatan($ingatan['identitas'], 'pin', $ingatan['ingat'], $pengguna->getKey());
        } elseif ($pinPerangkat) {
            $this->simpanIngatan(
                $this->ingatSaya ? $this->identitas : $ingatan['identitas'],
                'pin',
                $this->ingatSaya,
                $pengguna->getKey()
            );
        } elseif ($this->ingatSaya) {
            $this->simpanIngatan($this->identitas, 'sandi', true, $pengguna->getKey());
        } else {
            $this->lupakanIngatan();
        }

        // Cegah session fixation setelah pergantian identitas.
        session()->regenerate();

        return redirect()->intended('/account/dashboard');
    }

    private function pastikanAkunAktif(?User $pengguna): void
    {
        if (! $pengguna || $pengguna->status !== 'nonactive') {
            return;
        }

        AktivitasMasuk::catat($pengguna, $this->identitas, false, 'akun nonaktif');

        throw ValidationException::withMessages([
            'identitas' => 'Akun ini dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.',
        ]);
    }

    /** Akun yang diingat perangkat ini, kalau masih ada. */
    private function penggunaPerangkat(): ?User
    {
        $ingatan = IngatanMasuk::baca();

        if ($ingatan === null) {
            return null;
        }

        if ($ingatan['uid'] !== null) {
            $pengguna = User::find($ingatan['uid']);

            if ($pengguna) {
                return $pengguna;
            }
        }

        return User::where($this->kolomIdentitas($ingatan['identitas']), $ingatan['identitas'])->first();
    }

    /** Username atau email, ditentukan dari isian yang diberikan. */
    private function kolomIdentitas(string $identitas): string
    {
        return filter_var($identitas, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    private function kunciPembatas(): string
    {
        return 'masuk|' . Str::lower($this->identitas) . '|' . request()->ip();
    }

    private function kunciPembatasAkun(): string
    {
        return 'masuk-akun|' . Str::lower($this->identitas);
    }

    /**
     * Pembatas PIN dihitung per akun, bukan per IP, supaya berganti-ganti IP
     * tidak menambah kesempatan menebak.
     */
    private function kunciPembatasPin(User $pengguna): string
    {
        return 'masuk-pin|' . $pengguna->getKey();
    }

    private function panjangPin(): int
    {
        return (int) config('auth.pin.panjang', 6);
    }

    private function batasPinGagal(): int
    {
        return (int) config('auth.pin.batas_gagal', 5);
    }

    private function kunciPinDetik(): int
    {
        return (int) config('auth.pin.kunci_detik', 900);
    }

    // ------------------------------------------------- ingatan di peramban

    private function simpanIngatan(string $identitas, string $mode, bool $ingat, ?int $uid): void
    {
        if (IngatanMasuk::simpan($identitas, $mode, $ingat, $uid)) {
            $this->akunPerangkat = trim($identitas);
            $this->identitasTersimpan = $ingat;
        }
    }

    private function lupakanIngatan(): void
    {
        IngatanMasuk::lupakan();

        $this->identitasTersimpan = false;
        $this->akunPerangkat = '';
    }

    public function render()
    {
        return view('livewire.auth.masuk', [
            'panjangPin' => $this->panjangPin(),
        ]);
    }

    /**
     * Beri tahu pemilik akun saat akunnya dikunci karena percobaan beruntun.
     * Dikirim sekali per periode kunci supaya tidak jadi banjir surat.
     */
    private function beriTahuPemilikAkun(): void
    {
        $pengguna = User::where($this->kolomIdentitas($this->identitas), $this->identitas)->first();

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

    /**
     * Peramban ini belum pernah dipakai masuk ke akun tersebut. Akun yang
     * belum pernah dipakai masuk sama sekali tidak dihitung, supaya orang
     * yang baru mendaftar tidak langsung dikirimi peringatan.
     */
    private function perangkatBaru(User $pengguna): bool
    {
        $berhasilSebelumnya = AktivitasMasuk::where('user_id', $pengguna->getKey())
            ->where('berhasil', true);

        if ((clone $berhasilSebelumnya)->count() === 0) {
            return false;
        }

        return ! (clone $berhasilSebelumnya)
            ->where('perangkat', PenandaPerangkat::ambil())
            ->exists();
    }

    private function beriTahuPerangkatBaru(User $pengguna, string $cara): void
    {
        try {
            Mail::to($pengguna->email)->send(new MasukPerangkatBaruMail(
                $pengguna,
                (string) request()->ip(),
                mb_substr((string) request()->userAgent(), 0, 180),
                $cara
            ));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pemberitahuan perangkat baru: ' . $e->getMessage());
        }
    }

    private function beriTahuPerubahanPin(User $pengguna, string $aksi): void
    {
        try {
            Mail::to($pengguna->email)->send(
                new PemberitahuanPinMail($pengguna, $aksi, (string) request()->ip())
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim pemberitahuan PIN: ' . $e->getMessage());
        }
    }
}
