<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Tarif satu sesi Clinik Scopus, beserta persentase PPN-nya.
 *
 * Tabel ini menyimpan RIWAYAT tarif, bukan daftar pilihan: hanya satu baris
 * yang berlaku pada satu waktu, dan baris lama dipertahankan karena sesi yang
 * sudah terlanjur dipesan menunjuk ke barisnya lewat
 * clinikscopus.biaya_persesi_id. Menghapus baris lama berarti memutus riwayat
 * harga pesanan yang sudah terjadi.
 *
 * Sebelumnya tidak ada apa pun yang menjaga "hanya satu yang berlaku". Layar
 * lamanya berbentuk CRUD biasa, jadi dua baris berstatus active sekaligus
 * mungkin terjadi — dan pemakainya memilih dengan first(), yang berarti
 * SEMBARANG satu di antaranya. PublicClinikScopusController::cekPpn() bahkan
 * memanggil first() tanpa menyaring status sama sekali, sehingga PPN dari
 * tarif yang sudah tidak berlaku bisa ikut ditagihkan ke pelanggan.
 */
class ClinikScopusBiayaPersesi extends Model
{
    protected $table = 'clinikscopus_biaya_persesi';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'biaya_persesi',
        'ppn',
        'status',
    ];

    public const AKTIF = 'active';

    public const NONAKTIF = 'non active';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function clinikScopus()
    {
        return $this->hasMany(\App\ClinikScopus::class, 'biaya_persesi_id');
    }

    // ----------------------------------------------------------------- kueri

    public function scopeAktif($kueri)
    {
        return $kueri->where('status', self::AKTIF);
    }

    /**
     * Tarif yang sedang berlaku — SATU-SATUNYA pintu untuk mengambilnya.
     *
     * Dipakai layar admin maupun pemesanan publik, supaya tidak ada lagi
     * pemanggil yang menyaring dengan caranya sendiri. Terbaru didahulukan
     * sebagai jaring pengaman: kalau entah bagaimana ada dua yang aktif,
     * yang dipakai adalah yang paling belakangan disetel, bukan sembarang.
     */
    public static function berlaku(): ?self
    {
        return static::aktif()->latest('updated_at')->first();
    }

    // ------------------------------------------------------------- perubahan

    /**
     * Menjadikan tarif ini satu-satunya yang berlaku.
     *
     * Dibungkus transaksi: kalau penonaktifan yang lain berhasil tetapi
     * pengaktifan baris ini gagal, sistem akan kehilangan tarif berlaku sama
     * sekali — dan borang pemesanan publik menampilkan "-" sebagai harga.
     */
    public function jadikanBerlaku(): void
    {
        DB::transaction(function () {
            static::where('id', '!=', $this->getKey())
                ->where('status', self::AKTIF)
                ->update(['status' => self::NONAKTIF]);

            $this->forceFill(['status' => self::AKTIF])->save();
        });
    }

    // -------------------------------------------------------------- tampilan

    public function getTarifTerbacaAttribute(): string
    {
        return 'Rp ' . number_format((int) $this->biaya_persesi, 0, ',', '.');
    }

    /**
     * PPN sebagai bilangan bulat persen.
     *
     * Kolomnya varchar dan boleh kosong — pada data yang ada nilainya NULL,
     * yang berarti "tanpa PPN", bukan nol yang sengaja disetel.
     */
    public function getPpnPersenAttribute(): int
    {
        return (int) $this->ppn;
    }

    public function getBerlakuAttribute(): bool
    {
        return $this->status === self::AKTIF;
    }

    /** Berapa sesi yang harganya mengacu ke tarif ini. */
    public function getDipakaiSesiAttribute(): int
    {
        return $this->clinikScopus()->count();
    }
}
