<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Satu pendaftaran Webinar Eksklusif.
 *
 * Bentuknya sengaja mengikuti `scopus_camp_pendaftaran` sampai ke nama
 * kolomnya, supaya penghitung peserta, penjaga hapus angkatan, dan layar
 * daftar pendaftar tidak perlu bercabang per layanan.
 *
 * Bedanya satu: layanan ini dibayar lewat gerbang pembayaran, jadi ada kolom
 * `bayar_*` yang mencatat rujukan dan statusnya.
 */
class WebinarEksklusifPendaftaran extends Model
{
    protected $table = 'webinar_eksklusif_pendaftaran';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'token', 'id_transaksi', 'kategori_id',
        'user_id',
        'nama', 'email', 'telp', 'affiliasi', 'disetujui_pada', 'pengingat_pada', 'jumlah_pendaftar',
        'ppn', 'kode_unik', 'kode_diskon', 'nominal_diskon', 'total_pembayaran',
        'gambar', 'cara_bayar', 'bayar_rujukan', 'bayar_status',
        'bayar_pada', 'kedaluwarsa_pada', 'status', 'note',
    ];

    protected $casts = [
        'jumlah_pendaftar' => 'integer',
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
        'disetujui_pada' => 'datetime',
        'pengingat_pada' => 'datetime',
    ];

    /** Status pendaftaran; kuncinya tersimpan, nilainya yang dibaca orang. */
    public const STATUS = [
        'pending' => 'Menunggu pembayaran',
        'paid' => 'Sudah dibayar',
        'cancel' => 'Batal',
        'expired' => 'Kedaluwarsa',
    ];

    /**
     * Melepas kembali kursi dari pendaftaran yang batas bayarnya sudah lewat.
     *
     * DIPAKAI BERSAMA oleh perintah terjadwal dan oleh halaman pendaftaran.
     * Semula hanya perintah terjadwal yang melakukannya, dan itu satu titik
     * kegagalan yang diam: kalau penjadwalnya tidak jalan, kursi yang
     * ditinggalkan menahan tempatnya SELAMANYA — terukur di lokal, 2
     * pendaftaran lewat batas menahan 50 kursi — sementara halaman status
     * sudah memberitahu orangnya "kursinya sudah dilepas kembali".
     *
     * Dengan dipanggil juga saat kuota dibaca dan saat orang mendaftar,
     * kursinya kembali tepat ketika dibutuhkan, tanpa bergantung penjadwal.
     *
     * @param  string|null  $kategoriId  batasi ke satu angkatan; null = semua
     * @return int  jumlah kursi yang dilepas
     */
    public static function lepaskanYangKedaluwarsa(?string $kategoriId = null): int
    {
        $kueri = static::where('status', 'pending')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now());

        if ($kategoriId !== null) {
            $kueri->where('kategori_id', $kategoriId);
        }

        $lewat = $kueri->get();

        if ($lewat->isEmpty()) {
            return 0;
        }

        $kursi = 0;

        foreach ($lewat as $p) {
            /*
             * Status dan kuota diubah dalam SATU transaksi. Terpisah, proses
             * yang gagal di tengah meninggalkan pendaftaran yang sudah
             * kedaluwarsa tetapi kursinya belum kembali — dan tidak ada yang
             * akan mengulangnya karena statusnya sudah bukan 'pending'.
             */
            \Illuminate\Support\Facades\DB::transaction(function () use ($p, &$kursi) {
                /*
                 * Dibaca ulang DI DALAM transaksi dan dikunci. Dua proses yang
                 * menyapu bersamaan — perintah terjadwal dan orang yang sedang
                 * membuka halaman — sama-sama melihat 'pending' dan sama-sama
                 * mengembalikan kursinya, jadi kursinya bertambah dua kali.
                 */
                $segar = static::whereKey($p->getKey())->lockForUpdate()->first();

                if ($segar === null || $segar->status !== 'pending') {
                    return;
                }

                $segar->forceFill(['status' => 'expired', 'bayar_status' => 'kedaluwarsa'])->save();

                $kursi += (int) $segar->jumlah_pendaftar;

                $sesi = KategoriLayanan::whereKey($segar->kategori_id)->lockForUpdate()->first();

                if ($sesi === null || $sesi->total_kuota === null) {
                    return;
                }

                $sesi->forceFill([
                    // Tidak boleh melebihi totalnya: pengembalian ganda akan
                    // membuka kursi yang sebenarnya tidak ada.
                    'sisa_kuota' => (string) min(
                        (int) $sesi->total_kuota,
                        (int) $sesi->sisa_kuota + (int) $segar->jumlah_pendaftar
                    ),
                ])->save();
            });
        }

        return $kursi;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->token)) {
                $model->token = Str::random(40);
            }

            if (empty($model->id_transaksi)) {
                $model->id_transaksi = self::nomorBaru();
            }
        });
    }

    /**
     * Nomor transaksi yang terbaca orang: WE-20261121-0007.
     *
     * Dipakai sebagai rujukan ke gerbang pembayaran dan disebut peserta saat
     * bertanya lewat WhatsApp, jadi ia harus bisa dibacakan lewat telepon —
     * UUID tidak bisa.
     */
    public static function nomorBaru(): string
    {
        $awalan = 'WE-' . now()->format('Ymd') . '-';

        $terakhir = self::where('id_transaksi', 'like', $awalan . '%')
            ->orderByDesc('id_transaksi')->value('id_transaksi');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Peserta kedua dan seterusnya.
     *
     * Peserta pertama TIDAK ada di sini — ia di kolom nama/email pendaftaran
     * ini sendiri, sebab dialah yang dihubungi dan yang membayar.
     */
    public function pesertaLain(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        /*
         * Diarahkan ke tabel peserta BERSAMA, bukan webinar_eksklusif_peserta.
         *
         * Tabel lama itu nol baris dan tidak pernah ditulis dari mana pun —
         * satu-satunya pemakainya relasi ini. Sejak panitia bisa mencatat
         * nama rombongan untuk layanan mana pun, dua tabel untuk satu hal
         * yang sama berarti dua tempat yang harus diubah bersamaan selamanya.
         *
         * Tetap terurut seperti yang diketik; created_at tidak bisa dipakai,
         * beberapa baris lahir di detik yang sama.
         */
        return $this->hasMany(PendaftaranPeserta::class, 'pendaftaran_id')
            /*
             * withAttributes(), BUKAN where(): keduanya menyaring sama, tetapi
             * where() tidak menyetel kolomnya saat baris dibuat lewat relasi
             * ini — barisnya ditolak MySQL dengan "Field 'layanan' doesn't
             * have a default value".
             */
            ->withAttributes(['layanan' => 'webinar_eksklusif'])
            ->orderBy('urutan');
    }

    /**
     * Akun yang dipakai saat mendaftar, kalau memang sedang masuk.
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Semua nama peserta, pendaftar utama lebih dulu.
     *
     * @return list<array{nama:string,email:?string,utama:bool}>
     */
    public function semuaPeserta(): array
    {
        $daftar = [[
            'nama' => (string) $this->nama,
            'email' => (string) $this->email,
            'utama' => true,
        ]];

        foreach ($this->pesertaLain as $p) {
            $daftar[] = ['nama' => (string) $p->nama, 'email' => $p->email, 'utama' => false];
        }

        return $daftar;
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_id');
    }

    /** Sudah dibayar dan terverifikasi. */
    public function getLunasAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /** Batas bayarnya sudah lewat tetapi belum dibayar. */
    public function getSudahKedaluwarsaAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->kedaluwarsa_pada !== null
            && $this->kedaluwarsa_pada->isPast();
    }

    public function getStatusTulisAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Tanggal dalam bahasa Indonesia.
     *
     * translatedFormat, bukan format: APP_LOCALE di .env masih 'en', jadi
     * format() menulis "November" di halaman berbahasa Indonesia — kebetulan
     * sama, tetapi "October" dan "December" tidak.
     */
    public function getDibuatTulisAttribute(): string
    {
        return $this->created_at?->locale('id')->translatedFormat('j F Y, H:i') ?? '—';
    }

    /** Sisa waktu membayar, dalam kalimat. */
    public function getSisaWaktuAttribute(): ?string
    {
        if ($this->status !== 'pending' || $this->kedaluwarsa_pada === null) {
            return null;
        }

        if ($this->kedaluwarsa_pada->isPast()) {
            return 'Waktunya sudah habis';
        }

        return $this->kedaluwarsa_pada->locale('id')->diffForHumans(
            Carbon::now(), ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 2]
        ) . ' lagi';
    }
}
