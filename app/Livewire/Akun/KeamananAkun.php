<?php

namespace App\Livewire\Akun;

use App\AktivitasMasuk;
use App\Mail\PemberitahuanKeluarPerangkatMail;
use App\PerangkatPin;
use App\Support\PenandaPerangkat;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    /** Tampilkan seluruh perangkat, bukan hanya beberapa yang teratas. */
    public bool $semuaPerangkat = false;

    /** Berapa perangkat yang tampil sebelum daftarnya dilipat. */
    private const PERANGKAT_TAMPIL = 3;

    /** Tampilkan riwayat yang lebih panjang, bukan hanya yang terbaru. */
    public bool $riwayatPanjang = false;

    /**
     * Sembunyikan baris yang berhasil.
     *
     * Halaman ini gunanya menjawab satu pertanyaan: "ada yang bukan saya?".
     * Baris yang gagal adalah yang paling mungkin menjawabnya, dan pada akun
     * yang sering dipakai baris itu tenggelam di antara puluhan baris masuk
     * yang wajar.
     */
    public bool $hanyaGagal = false;

    /** Berapa banyak baris riwayat yang ditampilkan. */
    private const JUMLAH_RIWAYAT = 15;

    /** Batas kedua, sesudah pemiliknya minta melihat lebih banyak. */
    private const JUMLAH_RIWAYAT_PANJANG = 60;

    private const BATAS_SANDI_SALAH = 5;

    private const LAMA_KUNCI = 900;

    /** Akhiri sesi di semua perangkat lain, sesi ini tetap hidup. */
    public function keluarkanPerangkatLain(): void
    {

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

        /*
         * Kabari pemiliknya, seperti perubahan besar lainnya.
         *
         * Mengakhiri sesi di semua perangkat lain memang tindakan pengamanan,
         * tetapi ia juga bisa dipakai orang yang sudah masuk untuk mengusir
         * pemilik aslinya dari perangkatnya sendiri. Sampai sekarang tindakan
         * itu tidak meninggalkan kabar apa pun ke kotak masuk.
         */
        try {
            Mail::to($pengguna->email)->send(
                new PemberitahuanKeluarPerangkatMail($pengguna, (string) request()->ip())
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengabari keluar dari perangkat lain: ' . $e->getMessage());
        }

        $this->reset('kataSandi');

        $this->toast('berhasil', 'Sesi di perangkat lain sudah diakhiri. Perangkat ini tetap masuk.');
    }

    /** Akhiri satu sesi tertentu (perangkat lain) tanpa menyentuh sesi ini. */
    public function akhiriSesi(string $id): void
    {

        if ($id === session()->getId()) {
        $this->toast('gagal', 'Itu perangkat yang sedang Anda pakai. Pakai tombol Keluar di pojok kanan atas.');

            return;
        }

        $pengguna = $this->pengguna();

        $terhapus = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $pengguna->getKey())
            ->delete();

        if (! $terhapus) {
        $this->toast('gagal', 'Sesi itu sudah tidak ada.');

            return;
        }

        // Menghapus baris sesi saja tidak cukup: perangkat yang mencentang
        // "Ingat saya" akan memakai kue pengingatnya dan masuk lagi begitu
        // halaman dibuka. Penanda ingat-saya karena itu ikut diputar.
        $pengguna->forceFill(['remember_token' => Str::random(60)])->save();

        $this->toast('berhasil', 'Sesi di perangkat itu sudah diakhiri. Perangkat yang memakai "Ingat saya" juga harus masuk ulang.');
    }

    /**
     * Daftar sesi yang masih hidup milik pengguna ini.
     *
     * Hanya bisa dibaca bila sesi disimpan di basis data; dengan driver
     * 'file' daftar ini kosong dan bagiannya tidak ditampilkan.
     */
    private function daftarSesi()
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        $umur = (int) config('session.lifetime', 120);

        return DB::table('sessions')
            ->where('user_id', $this->pengguna()->getKey())
            ->where('last_activity', '>=', now()->subMinutes($umur)->getTimestamp())
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(function ($sesi) {
                $sesi->ini = $sesi->id === session()->getId();
                $sesi->waktu = \Illuminate\Support\Carbon::createFromTimestamp($sesi->last_activity);

                return $sesi;
            })
            /*
             * Perangkat yang sedang dipakai selalu di urutan pertama.
             *
             * Urutan menurut keaktifan saja tidak menjamin itu: perangkat lain
             * yang halamannya baru saja terbuka bisa lebih baru. Padahal justru
             * baris inilah yang TIDAK boleh diakhiri, jadi ia tidak boleh
             * tersembunyi di balik lipatan daftar.
             */
            ->sortByDesc(fn ($sesi) => $sesi->ini ? PHP_INT_MAX : $sesi->last_activity)
            ->values();
    }

    private function pengguna(): User
    {
        return auth()->user();
    }

    /**
     * Perangkat yang boleh masuk dengan PIN; yang sedang dipakai lebih dulu.
     *
     * Daftarnya pindah ke tab ini dari tab PIN. Dua daftar perangkat di dua
     * tab berbeda membuat orang harus tahu lebih dulu bedanya "sedang masuk"
     * dan "boleh pakai PIN" hanya untuk menemukan yang dicarinya — padahal
     * keduanya menjawab satu pertanyaan yang sama: perangkat apa saja yang
     * bisa membuka akun saya.
     */
    public function daftarPerangkatPin()
    {
        return PerangkatPin::where('user_id', $this->pengguna()->getKey())
            ->get()
            ->sortByDesc(fn ($p) => $p->ini ? PHP_INT_MAX : (optional($p->terakhir_dipakai_pada)->getTimestamp() ?? 0))
            ->values();
    }

    /**
     * Cabut izin PIN sebuah perangkat LAIN.
     *
     * Perangkat yang sedang dipakai sengaja tidak bisa dicabut dari sini:
     * jalannya ada di tab PIN sebagai "Lupakan perangkat", yang sekalian
     * membereskan kue ingatan di peramban ini — sesuatu yang tidak bisa
     * dikerjakan dari jarak jauh.
     */
    public function cabutIzinPin(int $id): void
    {
        $pengguna = $this->pengguna();

        $perangkat = PerangkatPin::where('user_id', $pengguna->getKey())->find($id);

        if ($perangkat === null) {
            $this->toast('gagal', 'Perangkat itu sudah tidak terdaftar.');

            return;
        }

        if ($perangkat->ini) {
            $this->toast('gagal', 'Itu perangkat yang sedang Anda pakai. Cabut lewat tab PIN masuk.');

            return;
        }

        $perangkat->delete();

        $this->toast('berhasil', 'Perangkat itu tidak bisa lagi masuk dengan PIN.');
    }

    /** Batas baris riwayat yang berlaku sekarang. */
    private function batasRiwayat(): int
    {
        return $this->riwayatPanjang ? self::JUMLAH_RIWAYAT_PANJANG : self::JUMLAH_RIWAYAT;
    }

    public function render()
    {
        $pengguna = $this->pengguna();

        return view('livewire.akun.keamanan-akun', [
            'pengguna' => $pengguna,
            'perangkatIni' => PenandaPerangkat::ambil(),
            'sesi' => $sesi = $this->daftarSesi(),
            // Yang tampil dibatasi sampai daftarnya dibuka; 20 perangkat
            // berjajar memakan seluruh layar dan mengubur riwayat di bawahnya.
            'sesiTampil' => $this->semuaPerangkat ? $sesi : $sesi->take(self::PERANGKAT_TAMPIL),
            'sisaPerangkat' => max(0, $sesi->count() - self::PERANGKAT_TAMPIL),
            /*
             * Riwayatnya dipotong, dan pemotongan itu HARUS terlihat.
             *
             * Sebelumnya 15 baris teratas diambil begitu saja tanpa penanda
             * apa pun: orang tidak tahu ada yang lebih lama, dan tidak punya
             * cara melihatnya. Pada halaman yang gunanya menjawab "ada yang
             * bukan saya?", diam soal data yang disembunyikan itu menyesatkan.
             */
            'riwayat' => AktivitasMasuk::where('user_id', $pengguna->getKey())
                ->when($this->hanyaGagal, fn ($k) => $k->where('berhasil', false))
                ->latest('id')
                ->limit($this->batasRiwayat())
                ->get(),
            'totalRiwayat' => $total = AktivitasMasuk::where('user_id', $pengguna->getKey())
                ->when($this->hanyaGagal, fn ($k) => $k->where('berhasil', false))
                ->count(),
            'riwayatTerpotong' => $total > $this->batasRiwayat(),
            'batasRiwayat' => $this->batasRiwayat(),
            'gagalTerakhir' => AktivitasMasuk::where('user_id', $pengguna->getKey())
                ->where('berhasil', false)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
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
