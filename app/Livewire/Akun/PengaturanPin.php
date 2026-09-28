<?php

namespace App\Livewire\Akun;

use App\Mail\PemberitahuanPinMail;
use App\PerangkatPin;
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

    /**
     * Pemiliknya menyatakan lupa PIN dan ingin menggantinya dengan yang baru.
     *
     * Bedanya dengan sekadar mengetik angka baru: ini pernyataan MAKSUD yang
     * disengaja. Pengaman "perangkat asing tidak boleh mengganti PIN" ada
     * untuk mencegah orang tidak sadar menimpa PIN semua perangkat; kalau
     * maksudnya memang itu dan kata sandinya benar, tidak ada gunanya
     * memaksanya lewat dua langkah (matikan PIN dulu, lalu buat lagi).
     */
    public bool $aturUlang = false;



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
            && $this->ingatanMilikSaya($ingatan, $this->pengguna())
            // Catatan di peladen ikut menentukan. Tanpa syarat ini, perangkat
            // yang izinnya sudah dicabut dari jauh masih melapor "terdaftar"
            // selama kuenya utuh.
            && PerangkatPin::perangkatIniTerdaftar($this->pengguna());
    }

    /**
     * Daftarkan perangkat yang sedang dipakai memakai PIN yang sudah ada.
     * Inilah jalan untuk memakai PIN di HP setelah PIN dibuat di komputer:
     * masuk sekali dengan kata sandi di HP, lalu masukkan PIN di sini.
     */
    public function aktifkanDiPerangkat(): void
    {

        $pengguna = $this->pengguna();

        if (! $pengguna->pinAktif()) {
        $this->toast('gagal', 'PIN belum aktif pada akun ini. Buat PIN dulu di bawah.');

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

        PerangkatPin::daftarkanPerangkatIni($pengguna);

        /*
         * Kabari pemilik akun.
         *
         * Mendaftarkan perangkat memberi ia jalan masuk PERMANEN dengan enam
         * angka, tanpa kata sandi — sama besarnya dengan mengubah PIN, yang
         * sejak dulu memang dikabari. Sampai sekarang justru pendaftaran
         * perangkat yang lolos tanpa kabar apa pun.
         */
        $this->beriTahu($pengguna, 'perangkat-didaftarkan');

        $this->reset('pinPerangkat');

        $this->toast('berhasil', 'Perangkat ini sekarang bisa dipakai masuk dengan PIN.');
    }

    /** Nyalakan mode "lupa PIN", atau batalkan. */
    public function ubahAturUlang(): void
    {
        $this->aturUlang = ! $this->aturUlang;

        $this->reset('kataSandi', 'pin', 'pinKonfirmasi');
        $this->resetErrorBag();
    }

    /** Perangkat ini tidak lagi boleh memakai PIN (akun tetap berPIN). */
    public function lupakanPerangkat(): void
    {

        $ingatan = IngatanMasuk::baca();

        if ($this->ingatanMilikSaya($ingatan, $this->pengguna())) {
            IngatanMasuk::lupakan();
        }

        PerangkatPin::lupakanPerangkatIni($this->pengguna());

        $this->toast('berhasil', 'Perangkat ini dilupakan. PIN Anda tetap aktif dan bisa dipakai di perangkat lain.');
    }


    /**
     * Apakah PIN boleh diganti dari perangkat yang sedang dipakai.
     *
     * Boleh kalau akun ini memang belum punya PIN, atau kalau peramban ini
     * sudah terdaftar — artinya pemakainya pernah membuktikan tahu PIN yang
     * sekarang. Dipakai tampilan untuk memberi peringatan lebih dulu, bukan
     * membiarkan orang mentok di pesan galat.
     */
    public function bolehGantiPin(): bool
    {
        return ! $this->pengguna()->pinAktif() || $this->perangkatSiap() || $this->aturUlang;
    }

    /** Aktifkan PIN, atau ganti PIN yang sudah ada. */
    public function simpan(): void
    {

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

        /*
         * Pengaman: perangkat yang belum terdaftar tidak boleh MENGGANTI PIN.
         *
         * PIN milik akun, bukan milik perangkat — kolomnya cuma satu. Jadi
         * mengetik angka baru di sini bukan "mendaftarkan HP", melainkan
         * menimpa PIN yang dipakai semua perangkat. Orang mudah salah kira:
         * di HP ia mengetik angka lain, lalu PIN di laptopnya mati tanpa ia
         * sadari — dan kalau di laptop angka lama dicoba sampai lima kali,
         * PIN-nya dimatikan sistem.
         *
         * Karena itu dari perangkat asing PIN hanya bisa diganti oleh orang
         * yang tahu PIN sekarang. Yang benar-benar lupa tetap punya jalan
         * keluar: matikan PIN dulu (cukup kata sandi), lalu buat yang baru.
         */
        if ($sudahAda && ! $this->perangkatSiap() && ! $this->aturUlang) {
            if (! $this->pinSekarangTerbukti($pengguna)) {
                return;
            }

            // Angkanya sama dengan PIN yang sekarang. Yang sebenarnya diminta
            // orang ini bukan mengganti PIN, melainkan memakai PIN-nya di
            // perangkat ini — jadi itu yang dikerjakan, tanpa menyentuh PIN.
            $this->ingatPerangkat($pengguna);

            $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

            $this->toast('berhasil', 'Perangkat ini sekarang bisa dipakai masuk dengan PIN. PIN Anda tidak diubah.');

            return;
        }

        $pengguna->aturPin($this->pin);

        // Perangkat yang dipakai mengaktifkan PIN langsung diingat, supaya di
        // halaman masuk cukup mengetik PIN tanpa username dan kata sandi.
        // Penanda "Ingat saya" dibiarkan apa adanya: mengaktifkan PIN tidak
        // ikut membuat isian username terisi otomatis kalau tidak diminta.
        /*
         * Atur ulang karena lupa: izin SEMUA perangkat dicabut lebih dulu.
         *
         * Orang yang lupa PIN-nya tidak bisa menjamin perangkat mana yang
         * masih pantas punya izin — dan kalau PIN-nya lupa karena akunnya
         * memang sedang dipegang orang lain, membiarkan izin lama berlaku
         * dengan PIN baru justru memberi jalan masuk yang segar.
         */
        if ($this->aturUlang) {
            PerangkatPin::lupakanSemua($pengguna);
        }

        $this->ingatPerangkat($pengguna);
        PerangkatPin::daftarkanPerangkatIni($pengguna);

        $this->aturUlang = false;

        $this->beriTahu($pengguna, $sudahAda ? 'diubah' : 'diaktifkan');

        $this->reset('kataSandi', 'pin', 'pinKonfirmasi');

        $this->toast('berhasil', $sudahAda
            ? 'PIN berhasil diubah. PIN lama sudah tidak berlaku.'
            : 'PIN berhasil diaktifkan. Di halaman masuk, pilih tab PIN lalu masukkan ' . $this->panjangPin() . ' angka PIN Anda.');
    }

    /**
     * Benarkah angka yang diketik sama dengan PIN yang sekarang?
     *
     * Jatah salahnya satu kantong dengan aktifkanDiPerangkat(), supaya
     * formulir ini tidak bisa dipakai menebak PIN setelah jatah di kotak
     * pendaftaran habis.
     */
    private function pinSekarangTerbukti(User $pengguna): bool
    {
        $kunci = 'pin-perangkat|' . $pengguna->getKey();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PIN_SALAH)) {
            $menit = ceil(RateLimiter::availableIn($kunci) / 60);

            $this->reset('kataSandi', 'pin', 'pinKonfirmasi');
            $this->addError('pin', 'Terlalu banyak PIN salah. Coba lagi dalam ' . $menit . ' menit.');
            $this->toast('gagal', 'PIN tidak diganti. Terlalu banyak percobaan salah — coba lagi dalam ' . $menit . ' menit.');

            return false;
        }

        if ($pengguna->pinCocok($this->pin)) {
            RateLimiter::clear($kunci);

            return true;
        }

        RateLimiter::hit($kunci, self::LAMA_KUNCI);

        $this->reset('kataSandi', 'pin', 'pinKonfirmasi');
        $this->addError('pin', 'Isikan PIN yang sekarang dipakai, bukan PIN baru.');
        // Pendek saja: kotak peringatan kuning di layar sudah menerangkan
        // kedua jalan keluarnya, dan toast setinggi lima baris justru
        // menutupi kotak itu.
        $this->toast('gagal', 'PIN tidak diganti — perangkat ini belum terdaftar.');

        return false;
    }

    /** Ingat perangkat ini supaya halaman masuk langsung meminta PIN. */
    private function ingatPerangkat(User $pengguna): void
    {
        $ingatan = IngatanMasuk::baca();
        $milikSaya = $this->ingatanMilikSaya($ingatan, $pengguna);

        IngatanMasuk::simpan(
            $milikSaya ? $ingatan['identitas'] : (string) $pengguna->username,
            'pin',
            $milikSaya ? $ingatan['ingat'] : false,
            (int) $pengguna->getKey()
        );
    }

    /** Matikan PIN; masuk kembali hanya dengan kata sandi. */
    public function nonaktifkan(): void
    {

        $this->validateOnly('kataSandi', ['kataSandi' => ['required', 'string']]);

        $pengguna = $this->pengguna();

        if (! $pengguna->pinAktif()) {
        $this->toast('gagal', 'PIN memang belum aktif pada akun ini.');

            return;
        }

        $this->pastikanKataSandiBenar($pengguna);

        // matikanPin() sendiri yang mencabut izin semua perangkat, supaya
        // ketiga jalan yang memanggilnya berperilaku sama.
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

        $this->toast('berhasil', 'PIN dinonaktifkan. Masuk kini hanya bisa memakai kata sandi.');
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

    /**
     * Pemberitahuan singkat lewat toast bersama.
     *
     * Dulu komponen ini menyimpan $pesan di dalam keadaannya sendiri lalu
     * menggambar kotak .alert Bootstrap. Toast tidak menggeser tata letak,
     * tidak ikut terbawa saat komponen digambar ulang, dan rupanya sama di
     * seluruh sistem — lihat misToast() di mis-ui.js.
     */
    private function toast(string $jenis, string $pesan): void
    {
        $this->dispatch('toast', jenis: $jenis, pesan: $pesan);
    }
}
