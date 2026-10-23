<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendaftaranScopusCamp extends Model
{
    /**
     * @var string
     */
    protected $table = 'scopus_camp_pendaftaran';

    /**
     * @var array
     */
    protected $fillable = [
        'token',
        'id_transaksi',
        'kategori_id',
        'email',
        'nama',
        'telp',
        'affiliasi',
        'ppn',
        'kode_unik',
        'gambar',
        'jumlah_pendaftar',
        'kode_diskon',
        'nominal_diskon',
        // Tali ke promo yang memotong harganya; dibaca layar dampak promo.
        'promo_id',
        'total_pembayaran',
        'status',
        'tanggal_reschedule',
        'group_wa',
        'note',
        'created_at',
        'updated_at',
        'cara_bayar',

        /*
         * Tagihan di gerbang pembayaran (DOKU).
         *
         * Namanya MENYALIN Webinar Eksklusif persis, sebab layar Pendaftar
         * Layanan membaca kelima layanan lewat satu UNION. Yang hilang dari
         * daftar ini tidak akan pernah tersimpan dan tidak ada galat apa pun
         * yang memberi tahu — nilainya cuma menguap saat create().
         */
        'bayar_rujukan',
        'bayar_permintaan_id',
        'bayar_url',
        'bayar_status',
        'bayar_kanal',
        'bayar_pada',
        'kedaluwarsa_pada',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
    ];
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
     * Peserta kedua dan seterusnya dalam satu pendaftaran rombongan.
     *
     * Peserta PERTAMA tidak ada di sini — ia di kolom nama/email pendaftaran
     * ini sendiri, sebab dialah yang dihubungi dan yang membayar.
     *
     * Tabelnya BERSAMA kelima layanan, bukan tabel sendiri: panitia sudah
     * mencatat nama rombongan ke sana lewat layarnya, dan dua tabel untuk
     * satu hal yang sama berarti dua tempat yang harus diubah bersamaan
     * selamanya. Terukur 23 Okt 2026: 7 baris scopus_camp sudah ada di sana,
     * seluruhnya ditulis jalur panitia.
     */
    public function pesertaLain(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        /*
         * withAttributes(), BUKAN where(): keduanya menyaring sama, tetapi
         * where() tidak menyetel kolomnya saat baris dibuat lewat relasi ini
         * — barisnya ditolak MySQL dengan "Field 'layanan' doesn't have a
         * default value".
         */
        return $this->hasMany(PendaftaranPeserta::class, 'pendaftaran_id')
            ->withAttributes(['layanan' => 'scopus_camp'])
            // Terurut seperti yang diketik; created_at tidak bisa dipakai,
            // beberapa baris lahir di detik yang sama.
            ->orderBy('urutan');
    }

    /**
     * Semua nama peserta, pendaftar utama lebih dulu.
     *
     * @return list<array{nama:string, email:?string, utama:bool}>
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

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
