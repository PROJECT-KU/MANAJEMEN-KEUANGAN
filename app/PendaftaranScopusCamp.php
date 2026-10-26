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

        // Nomor tagihan di gerbang, yang BISA berbeda dari nomor
        // pendaftarannya begitu tagihannya dibuat ulang; lihat migrasi
        // 2026_10_25_090000.
        'bayar_nomor',
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

    /**
     * Melepas kursi pendaftaran yang tenggat bayarnya sudah lewat.
     *
     * ---------------------------------------------------------------------
     * Kenapa ini ada di MODEL, bukan hanya di perintah terjadwal
     * ---------------------------------------------------------------------
     *
     * Sampai 26 Okt 2026 satu-satunya yang melepas kursi Scopus Camp adalah
     * `scopus-camp:kedaluwarsakan`, yang berjalan lewat cron. Cron hPanel di
     * peladen ini BELUM TERPASANG — jadi tenggat 60 menit yang dipajang
     * halaman status tidak pernah ditegakkan siapa pun: kursinya tertahan
     * selamanya, dan angkatan yang terlihat penuh sebenarnya kosong.
     *
     * Dipanggil juga dari jalur web, kursinya kembali sendiri begitu ada
     * orang membuka halaman yang memakainya — dan di halaman daftar
     * angkatan, itu terjadi berkali-kali sehari tanpa cron sama sekali.
     * Pola ini disalin dari Webinar Eksklusif, yang sudah memakainya.
     *
     * ---------------------------------------------------------------------
     * Yang TIDAK dikerjakan di sini
     * ---------------------------------------------------------------------
     *
     * TIDAK mengirim surat maupun WhatsApp. Antreannya 'sync', jadi
     * mengirimnya di sini berarti permintaan web menunggu peladen surat atau
     * Fonnte — sampai 15 detik untuk satu nomor yang tidak menjawab, pada
     * halaman yang sedang dibuka orang. Pengabarannya tetap milik perintah
     * terjadwal, yang memang punya waktu dan jeda antar pesan.
     *
     * Yang membuka halaman statusnya sendiri TIDAK kehilangan kabar: begitu
     * barisnya berpindah, halaman yang sedang ia buka itu juga yang
     * menampilkan "kursinya sudah kami lepas".
     *
     * @param  array<int, string>  $angkatanId  batasi ke angkatan tertentu
     * @param  string|null  $pendaftaranId  batasi ke satu pendaftaran
     * @return int  banyaknya pendaftaran yang berpindah
     */
    public static function lepaskanYangKedaluwarsa(
        array $angkatanId = [],
        ?string $pendaftaranId = null,
        int $batas = 20
    ): int {
        $statusAwal = \App\Support\PendaftaranSemuaLayanan::statusAwal('scopus_camp');

        $kueri = static::query()
            ->where('cara_bayar', 'doku')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where('status', $statusAwal)
            ->whereNull('bayar_pada')
            ->orderBy('kedaluwarsa_pada')

            /*
             * Pemutus seri: 'kedaluwarsa_pada' seri untuk pendaftaran yang
             * dibuat dalam detik yang sama, dan urutan tanpa pemecah seri
             * tidak ditentukan — barisnya bisa berpindah antar jalankan.
             */
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

        $lewat = $kueri->get();

        if ($lewat->isEmpty()) {
            return 0;
        }

        $berpindah = 0;

        foreach ($lewat as $p) {
            /*
             * Status dan kuota diubah dalam SATU transaksi, dan barisnya
             * dibaca ulang DI DALAM transaksi sambil dikunci.
             *
             * Dua proses bisa menyapu bersamaan — perintah terjadwal dan
             * orang yang sedang membuka halaman — dan yang kalah merebut
             * kunci akan melihat status yang sudah berpindah lalu keluar
             * tanpa mengubah apa pun. Tanpa itu, kursinya dihitung ulang dua
             * kali dan pendaftarnya dikabari dua kali.
             */
            $pindah = \Illuminate\Support\Facades\DB::transaction(
                function () use ($p, $statusAwal) {
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

                    $angkatan = \App\CategoriesScopusCamp::whereKey($segar->kategori_id)
                        ->lockForUpdate()
                        ->first();

                    if ($angkatan !== null) {
                        /*
                         * DIHITUNG ULANG, bukan ditambahkan.
                         *
                         * Penambahan yang terjadi dua kali membuka kursi yang
                         * sebenarnya tidak ada; hitung ulang memulangkan
                         * angka yang sama berapa kali pun dijalankan. Rumusnya
                         * dipakai bersama jalur pendaftaran — satu tempat.
                         */
                        $benar = \App\Http\Controllers\Publict\PublicScopusCampController
                            ::hitungUlangSisaKursi($angkatan);

                        if ($benar !== null) {
                            \Illuminate\Support\Facades\DB::table('kategori_layanan')
                                ->where('layanan', 'scopus_camp')
                                ->where('id', $angkatan->getKey())
                                ->update(['sisa_kuota' => (string) $benar]);
                        }
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
