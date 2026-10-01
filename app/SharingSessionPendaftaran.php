<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Satu pendaftaran Sharing Session.
 *
 * Bentuknya sengaja mengikuti `scopus_camp_pendaftaran` sampai ke nama
 * kolomnya, supaya penghitung peserta, penjaga hapus angkatan, dan layar
 * daftar pendaftar tidak perlu bercabang per layanan.
 *
 * Bedanya satu: layanan ini dibayar lewat gerbang pembayaran, jadi ada kolom
 * `bayar_*` yang mencatat rujukan dan statusnya.
 */
class SharingSessionPendaftaran extends Model
{
    protected $table = 'sharing_session_pendaftaran';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'token', 'id_transaksi', 'kategori_id',
        'nama', 'email', 'telp', 'affiliasi', 'jumlah_pendaftar',
        'ppn', 'kode_unik', 'kode_diskon', 'nominal_diskon', 'total_pembayaran',
        'gambar', 'cara_bayar', 'bayar_rujukan', 'bayar_status',
        'bayar_pada', 'kedaluwarsa_pada', 'status', 'note',
    ];

    protected $casts = [
        'jumlah_pendaftar' => 'integer',
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
    ];

    /** Status pendaftaran; kuncinya tersimpan, nilainya yang dibaca orang. */
    public const STATUS = [
        'pending' => 'Menunggu pembayaran',
        'paid' => 'Sudah dibayar',
        'cancel' => 'Batal',
        'expired' => 'Kedaluwarsa',
    ];

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
     * Nomor transaksi yang terbaca orang: SS-20261121-0007.
     *
     * Dipakai sebagai rujukan ke gerbang pembayaran dan disebut peserta saat
     * bertanya lewat WhatsApp, jadi ia harus bisa dibacakan lewat telepon —
     * UUID tidak bisa.
     */
    public static function nomorBaru(): string
    {
        $awalan = 'SS-' . now()->format('Ymd') . '-';

        $terakhir = self::where('id_transaksi', 'like', $awalan . '%')
            ->orderByDesc('id_transaksi')->value('id_transaksi');

        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
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
