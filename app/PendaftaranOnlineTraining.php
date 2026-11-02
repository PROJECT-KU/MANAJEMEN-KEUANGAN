<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Pendaftaran Class Online (Online Training).
 *
 * Bentuknya MENYALIN PendaftaranScopusCamp, dan itu disengaja: seluruh
 * lapisan bersama — PendaftaranSemuaLayanan, DampakPromo, PotonganKode,
 * ekspor, layar panitia — membaca tabel pendaftaran lewat peta kolom yang
 * sama. Model yang bentuknya berbeda berarti tiap lapisan itu butuh cabang
 * khususnya sendiri.
 *
 * Yang TIDAK ikut disalin: kolom `gambar`. Pembayaran layanan ini seluruhnya
 * lewat gerbang sejak hari pertama, jadi tidak pernah ada bukti transfer
 * yang diunggah — dan kolom yang tidak pernah diisi hanya mengundang jalur
 * unggah dihidupkan lagi tanpa sengaja. Jalur itu sudah pernah dicabut dari
 * Scopus Camp dan Scopus Kafe justru karena berbahaya.
 */
class PendaftaranOnlineTraining extends Model
{
    protected $table = 'online_training_pendaftaran';

    /*
     * KUNCINYA UNTAIAN DAN TIDAK BERURUT, dan ini WAJIB dinyatakan.
     *
     * Bawaan Eloquent `int` + `incrementing`, dan dengan itu find() MENCOR
     * UUID-nya jadi angka: terukur, find('9f3a-…') memulangkan 0 dan
     * barisnya tidak pernah ketemu. Akibatnya halaman rincian panitia
     * menjawab 404 untuk baris yang jelas-jelas ada di tabelnya, dan tidak
     * ada galat apa pun yang menyebut pengecoran itu sebagai sebabnya.
     */
    protected $keyType = 'string';

    public $incrementing = false;

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
        'jumlah_pendaftar',
        'kode_diskon',
        'nominal_diskon',

        // Tali ke promo yang memotong harganya; dibaca layar Dampak Promo
        // dan penjaga kuota promo. Yang hilang dari daftar ini tidak akan
        // pernah tersimpan, dan tidak ada galat apa pun yang memberi tahu —
        // nilainya cuma menguap saat create().
        'promo_id',

        'total_pembayaran',
        'status',
        'tanggal_reschedule',
        'group_wa',
        'note',
        'created_at',
        'updated_at',
        'cara_bayar',
        'bayar_rujukan',
        'bayar_nomor',
        'bayar_permintaan_id',
        'bayar_url',
        'bayar_status',
        'bayar_kanal',
        'bayar_pada',
        'kedaluwarsa_pada',
    ];

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
     * Tabelnya BERSAMA layanan lain, bukan tabel sendiri: dua tabel untuk
     * satu hal yang sama berarti dua tempat yang harus diubah bersamaan
     * selamanya.
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
            ->withAttributes(['layanan' => 'online_training'])
            // Terurut seperti yang diketik; created_at tidak bisa dipakai,
            // beberapa baris lahir di detik yang sama.
            ->orderBy('urutan');
    }

    public function angkatan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_id');
    }

    /**
     * Melepas kursi dari tagihan yang sudah lewat tenggat 60 menit.
     *
     * MENYALIN PendaftaranScopusCamp::lepaskanYangKedaluwarsa() termasuk
     * alasannya: cron hPanel di peladen ini belum terpasang, jadi tenggatnya
     * ditegakkan dari jalur web yang paling sering dibuka orang. Tanpa itu,
     * kursi yang tagihannya tidak pernah dibayar tertahan selamanya dan
     * angkatan terlihat penuh padahal kosong.
     *
     * Kuotanya DIHITUNG ULANG, bukan ditambahkan: penambahan yang terjadi
     * dua kali membuka kursi yang sebenarnya tidak ada, sementara hitung
     * ulang memulangkan angka yang sama berapa kali pun dijalankan.
     */
    public static function lepaskanYangKedaluwarsa(
        array $angkatanId = [],
        ?string $pendaftaranId = null,
        int $batas = 20
    ): int {
        $statusAwal = \App\Support\PendaftaranSemuaLayanan::statusAwal('online_training');

        $kueri = static::query()
            ->where('cara_bayar', 'doku')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where('status', $statusAwal)
            ->whereNull('bayar_pada')
            ->orderBy('kedaluwarsa_pada')

            // Pemutus seri: tenggat yang sama persis tidak punya urutan yang
            // ditentukan tanpa kunci kedua, dan barisnya bisa berpindah
            // antar jalankan.
            ->orderBy('id');

        if ($angkatanId !== []) {
            $kueri->whereIn('kategori_id', $angkatanId);
        }

        if ($pendaftaranId !== null) {
            $kueri->whereKey($pendaftaranId);
        }

        if ($batas > 0) {
            $kueri->limit($batas);
        }

        $berpindah = 0;

        foreach ($kueri->get() as $p) {
            $pindah = \Illuminate\Support\Facades\DB::transaction(
                function () use ($p, $statusAwal) {
                    /*
                     * Dibaca ulang DI DALAM transaksi sambil dikunci: dua
                     * proses bisa menyapu bersamaan — poller halaman status
                     * dan orang yang membuka daftar — dan yang kalah merebut
                     * kunci akan melihat status yang sudah berpindah lalu
                     * keluar tanpa mengabari orangnya dua kali.
                     *
                     * TIDAK ADA UJI YANG MENJAGA BARIS INI, dan itu disebut
                     * supaya tidak ada yang mengira ada: lombanya menuntut
                     * dua proses sungguhan, sementara rangkaian ujinya satu
                     * utas. Yang menanggung akibatnya tetap terjaga dari sisi
                     * lain — kuotanya dihitung ulang, bukan ditambahkan.
                     */
                    $segar = static::whereKey($p->getKey())->lockForUpdate()->first();

                    if ($segar === null || (string) $segar->status !== $statusAwal) {
                        return false;
                    }

                    $segar->forceFill([
                        'status' => 'Pendaftaran Dibatalkan',
                        'bayar_status' => 'expired',

                        // Alamat bayarnya dibuang: tagihannya sudah mati di
                        // gerbang, dan tombol yang menunjuk ke sana mengantar
                        // orangnya ke halaman galat yang menyalahkan dirinya.
                        'bayar_url' => null,
                    ])->save();

                    $angkatan = KategoriLayanan::whereKey($segar->kategori_id)
                        ->lockForUpdate()->first();

                    if ($angkatan !== null && $angkatan->total_kuota !== null) {
                        $terpakai = (int) static::where('kategori_id', $angkatan->getKey())
                            ->whereNotIn('status', [
                                'Pendaftaran Dibatalkan',
                                'Pendaftaran Ditolak',
                                'Pendaftaran Refund',
                            ])
                            ->sum('jumlah_pendaftar');

                        \Illuminate\Support\Facades\DB::table('kategori_layanan')
                            ->where('layanan', 'online_training')
                            ->where('id', $angkatan->getKey())
                            ->update([
                                'sisa_kuota' => (string) max(0, (int) $angkatan->total_kuota - $terpakai),
                            ]);
                    }

                    return true;
                }
            );

            if ($pindah) {
                $berpindah++;
            }
        }

        return $berpindah;
    }
}
