<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranScopusKafe extends Model
{
    /**
     * @var string
     */
    protected $table = 'pendaftaran_scopus_kafe';

    /**
     * @var array
     */
    protected $fillable = [
        'id_pemesanan',

        // detail data diri
        'nama',
        'tanggal_pemesanan',
        'email',
        'telp',

        // form sesi 1
        'sesi',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'biaya',
        'kode_unik_pembayaran',
        'subtotal_pembayaran',

        // form sesi 2
        'sesi_kedua',
        'waktu_mulai_kedua',
        'waktu_selesai_kedua',
        'lokasi_kedua',
        'biaya_kedua',
        'kode_unik_pembayaran_kedua',
        'subtotal_pembayaran_kedua',

        // form sesi 3
        'sesi_ketiga',
        'waktu_mulai_ketiga',
        'waktu_selesai_ketiga',
        'lokasi_ketiga',
        'biaya_ketiga',
        'kode_unik_pembayaran_ketiga',
        'subtotal_pembayaran_ketiga',

        'total_keseluruhan_pembayaran',
        'status',
        'gambar',
        'created_at',
        'updated_at',
        'cara_bayar',

        // Kolom tagihan gerbang; lihat migrasi 2026_10_26_090000.
        'bayar_nomor',
        'bayar_rujukan',
        'bayar_permintaan_id',
        'bayar_url',
        'bayar_status',
        'bayar_kanal',
        'bayar_pada',
        'kedaluwarsa_pada',
        'varian',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    /**
     * Waktu dibaca sebagai Carbon, bukan untaian.
     *
     * Tanpa ini `kedaluwarsa_pada->isFuture()` di halaman status melempar
     * "Call to a member function on string" — galat yang menyebut baris
     * tampilan, bukan baris yang lupa mendaftarkan castnya.
     */
    protected $casts = [
        'tanggal_pemesanan' => 'datetime',
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
    ];

    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Melepas pemesanan yang tenggat bayarnya sudah lewat.
     *
     * Sama alasannya dengan Scopus Camp: cron hPanel di peladen ini belum
     * terpasang, jadi tanpa pemanggilan dari jalur web tenggat yang dipajang
     * halaman status tidak pernah ditegakkan siapa pun — tagihannya mati,
     * tetapi barisnya tetap terbaca "menunggu pembayaran" selamanya.
     *
     * TIDAK mengirim surat maupun WhatsApp: antreannya 'sync', jadi
     * mengirimnya di sini berarti permintaan web menunggu peladen surat.
     * Pengabarannya milik perintah terjadwal.
     *
     * Layanan ini TIDAK BERKUOTA — tidak ada kursi yang dikembalikan, jadi
     * tidak ada hitung ulang seperti di Scopus Camp. Yang dikerjakan cuma
     * menutup tagihannya supaya halamannya berhenti menawarkan tombol bayar
     * yang mengantar ke halaman galat.
     */
    public static function lepaskanYangKedaluwarsa(?string $pemesananId = null, int $batas = 20): int
    {
        $kueri = static::query()
            ->where('cara_bayar', 'doku')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where('status', \App\Support\PendaftaranSemuaLayanan::statusAwal('scopus_kafe'))
            ->whereNull('bayar_pada')
            ->whereNotIn('bayar_status', ['expired'])
            ->orderBy('kedaluwarsa_pada')

            // Pemutus seri: tenggat yang sama persis tidak punya urutan yang
            // ditentukan tanpa kunci kedua.
            ->orderBy('id');

        if ($pemesananId !== null) {
            $kueri->whereKey($pemesananId);
        }

        if ($batas > 0) {
            $kueri->limit($batas);
        }

        $berpindah = 0;

        foreach ($kueri->get() as $p) {
            $pindah = \Illuminate\Support\Facades\DB::transaction(function () use ($p) {
                /*
                 * Dibaca ulang DI DALAM transaksi dan dikunci: poller halaman
                 * status dan perintah terjadwal bisa menyapu bersamaan, dan
                 * yang kalah merebut kunci akan melihat keadaan yang sudah
                 * berpindah lalu keluar tanpa mengabari orangnya untuk kedua
                 * kalinya.
                 *
                 * Baris ini TIDAK TERJAGA UJI — lombanya menuntut dua proses
                 * sungguhan, sementara rangkaian ujinya satu utas. Disebut di
                 * sini supaya tidak ada yang mengira ada penjaganya.
                 */
                $segar = static::whereKey($p->getKey())->lockForUpdate()->first();

                if ($segar === null || (string) $segar->bayar_status === 'expired') {
                    return false;
                }

                $segar->forceFill([
                    'bayar_status' => 'expired',

                    // Alamat bayarnya dibuang: tagihannya sudah mati di
                    // gerbang, dan tombol yang menunjuk ke sana mengantar
                    // orangnya ke halaman galat yang menyalahkan dirinya.
                    'bayar_url' => null,
                ])->save();

                return true;
            });

            if ($pindah) {
                $berpindah++;
            }
        }

        return $berpindah;
    }
}
