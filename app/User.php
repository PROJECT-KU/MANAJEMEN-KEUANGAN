<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Mail\TautanResetPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'level',
        'peran',
        'jenis',
        'telp',
        'company',
        'alamat_company',
        'telp_company',
        'email_company',
        'logo_company',
        'pj_company',
        'level',
        'tanggal_lahir',
        'norek',
        'bank',
        'gambar',
        'jobdesk',
        'total_hadir',
        'code_verified_mail',
        'code_verified_mail_sent_at',
    ];

    /**
     * Tiga peran yang dikenal sistem.
     *
     * Peran menjawab "boleh membuka apa", jabatan menjawab "posisinya apa".
     * Keduanya dulu ditumpuk di kolom `level`, sehingga menambah jabatan baru
     * berarti menyentuh kontrol akses. Sekarang jabatan tinggal di `jobdesk`
     * dan tidak berpengaruh sama sekali terhadap hak akses.
     */
    public const PERAN_ADMINISTRATOR = 'administrator';

    public const PERAN_KARYAWAN = 'karyawan';

    public const PERAN_PELANGGAN = 'user';

    /** Urutan sengaja dari yang paling berhak ke yang paling sedikit. */
    public const SEMUA_PERAN = [
        self::PERAN_ADMINISTRATOR,
        self::PERAN_KARYAWAN,
        self::PERAN_PELANGGAN,
    ];

    /** Pengelola: boleh membuka pengaturan, keuangan, dan data orang lain. */
    public function adalahAdministrator(): bool
    {
        return $this->peran === self::PERAN_ADMINISTRATOR;
    }

    /** Pegawai: boleh membuka pekerjaannya sendiri, bukan data orang lain. */
    public function adalahKaryawan(): bool
    {
        return $this->peran === self::PERAN_KARYAWAN;
    }

    /** Pelanggan dari luar; hanya layanan yang dipesannya sendiri. */
    public function adalahPelanggan(): bool
    {
        return $this->peran === self::PERAN_PELANGGAN;
    }

    /**
     * Orang dalam — administrator maupun karyawan.
     *
     * Ditulis sebagai "bukan pelanggan", bukan "administrator atau karyawan":
     * kalau nanti ada peran internal baru, ia otomatis ikut terhitung di sini
     * tanpa perlu menyisir ulang seluruh pemeriksaan.
     */
    public function adalahOrangDalam(): bool
    {
        return $this->peran !== self::PERAN_PELANGGAN;
    }

    /**
     * Nama peran dalam bahasa Indonesia, untuk ditampilkan.
     *
     * Nilai yang tersimpan sengaja tetap bahasa Inggris — ia dipakai sebagai
     * kunci di puluhan tempat, dan menerjemahkan yang tersimpan berarti
     * migrasi beserta seluruh pembandingnya. Yang diterjemahkan hanya yang
     * terbaca orang: sebelum ini layar data pelanggan menuliskan "User", satu-
     * satunya kata Inggris di halaman berbahasa Indonesia.
     */
    public function peranTerbaca(): string
    {
        return [
            self::PERAN_ADMINISTRATOR => 'Administrator',
            self::PERAN_KARYAWAN => 'Karyawan',
            self::PERAN_PELANGGAN => 'Pelanggan',
        ][$this->peran] ?? \Illuminate\Support\Str::title((string) $this->peran);
    }

    /** Cocok dengan salah satu peran yang disebut. */
    public function punyaPeran(string ...$peran): bool
    {
        return in_array($this->peran, $peran, true);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'pin_aktif' => 'boolean',
        'pin_diubah_pada' => 'datetime',
    ];

    /**
     * PIN masuk sengaja TIDAK ada di $fillable: nilainya hanya boleh disetel
     * lewat aturPin() supaya selalu teracak dan tidak pernah bisa ikut
     * terisi dari data permintaan.
     */
    /**
     * Akun baru selalu dapat UUID.
     *
     * Alamat halaman profil memakai UUID, bukan id berurutan, supaya
     * pengguna yang sudah masuk tidak bisa menelusuri akun orang lain
     * sekadar dengan menaikkan angka di alamatnya.
     */
    protected static function booted(): void
    {
        static::creating(function (self $akun) {
            if (blank($akun->uuid)) {
                $akun->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /** Cari akun dari UUID-nya; null kalau tidak ada atau UUID-nya kosong. */
    public static function cariUuid(?string $uuid): ?self
    {
        return blank($uuid) ? null : static::where('uuid', $uuid)->first();
    }

    /**
     * Alamat foto profil yang siap dipasang di src.
     *
     * Satu pintu untuk seluruh sistem: sebelumnya tiap tampilan merangkai
     * sendiri asset('assets/img/profil/' . $user->gambar), jadi memindahkan
     * berkasnya ke storage berarti menyunting lima belas tempat dan pasti
     * ada yang terlewat. Lihat App\Support\FotoProfil.
     */
    public function getFotoUrlAttribute(): string
    {
        return \App\Support\FotoProfil::url($this->gambar);
    }

    /** Punya foto sendiri, bukan gambar bawaan. */
    public function getPunyaFotoAttribute(): bool
    {
        return \App\Support\FotoProfil::punyaFoto($this->gambar);
    }

    public function pinAktif(): bool
    {
        return (bool) $this->pin_aktif && ! empty($this->pin);
    }

    /** Simpan PIN baru dalam bentuk teracak dan langsung aktifkan. */
    public function aturPin(string $pin): void
    {
        $this->forceFill([
            'pin' => Hash::make($pin),
            'pin_aktif' => true,
            'pin_diubah_pada' => now(),
        ])->save();
    }

    /** Matikan PIN dan buang nilainya. */
    public function matikanPin(): void
    {
        $this->forceFill([
            'pin' => null,
            'pin_aktif' => false,
            'pin_diubah_pada' => null,
        ])->save();

        /*
         * Izin perangkat ikut dicabut, dan sengaja di SINI, bukan di
         * pemanggilnya: matikanPin() dipanggil dari tiga tempat — halaman
         * profil, halaman masuk saat PIN salah berulang, dan penggantian kata
         * sandi. Kalau pembersihannya ditaruh di salah satu pemanggil, dua
         * jalan lain meninggalkan catatan lama yang membuat perangkatnya
         * langsung terdaftar kembali begitu PIN diaktifkan lagi.
         */
        \App\PerangkatPin::lupakanSemua($this);
    }

    public function pinCocok(string $pin): bool
    {
        return $this->pinAktif() && Hash::check($pin, (string) $this->pin);
    }

    /**
     * Kirim tautan atur ulang kata sandi memakai surat milik aplikasi ini,
     * bukan notifikasi bawaan Laravel, supaya tampilannya seragam.
     */
    public function sendPasswordResetNotification($token)
    {
        $tautan = route('password.atur-ulang', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ]);

        Mail::to($this->getEmailForPasswordReset())->send(
            new TautanResetPasswordMail($this, $tautan, (int) config('auth.passwords.users.expire', 60))
        );
    }
}
