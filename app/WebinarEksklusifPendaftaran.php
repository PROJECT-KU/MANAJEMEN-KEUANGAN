<?php

namespace App;

use Illuminate\Support\Facades\DB;

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
        // Tali ke promo yang memotong harganya; dibaca layar dampak promo.
        'promo_id',
        'gambar', 'cara_bayar', 'bayar_rujukan', 'bayar_url', 'bayar_status', 'bayar_kanal',
        'bayar_pada', 'kedaluwarsa_pada', 'kedaluwarsa_dikabari_pada', 'status', 'note',
    ];

    /**
     * Nama kedua untuk kolom `bayar_kanal`.
     *
     * Kueri gabungan layar panitia menamainya `kanal_bayar` — nama kolomnya
     * sendiri berbeda-beda antar lima tabel layanan, jadi yang dipakai di
     * sana nama bersama. Layar rincian memakai model mentah ini, dan tanpa
     * nama kedua tampilan yang sama harus ditulis dua kali dengan dua nama
     * berbeda untuk satu hal yang sama.
     *
     * Model layanan lain tidak punya atribut ini, dan Eloquent memulangkan
     * null untuk atribut yang tidak dikenal — jadi tampilan bersamanya aman
     * dipakai kelima-limanya.
     */
    public function getKanalBayarAttribute(): ?string
    {
        return $this->attributes['bayar_kanal'] ?? null;
    }

    protected $casts = [
        'jumlah_pendaftar' => 'integer',
        'bayar_pada' => 'datetime',
        'kedaluwarsa_pada' => 'datetime',
        'kedaluwarsa_dikabari_pada' => 'datetime',
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
    /**
     * Pendaftaran yang kursinya BOLEH dilepas kembali.
     *
     * Satu tempat saja. Aturan ini dipakai perintah penjadwal maupun
     * pelepasan yang berjalan saat kuota dibaca; disalin jadi dua, keduanya
     * akan berbeda perlahan dan yang satu melepas kursi yang satunya tahan.
     *
     * Yang BUKTINYA SUDAH MASUK dikecualikan. Peserta yang mengunggah bukti
     * di jam ke-23 sudah membayar — yang tersisa cuma panitia mencocokkannya,
     * dan itu pekerjaan jam kerja. Dilepas juga, uangnya sudah pindah tetapi
     * kursinya hilang, dan halaman statusnya terlanjur menjanjikan "bukti
     * sudah masuk, tunggu konfirmasi".
     *
     * Akibat yang diterima sadar: pendaftaran bermodal bukti palsu menahan
     * kursinya sampai panitia menolaknya. Itu menukar kursi tertahan dengan
     * uang yang hilang — dan yang kedua jauh lebih mahal.
     */
    public function scopeBisaDilepas($kueri)
    {
        return $kueri->where('status', 'pending')
            ->whereNotNull('kedaluwarsa_pada')
            ->where('kedaluwarsa_pada', '<', now())
            ->where(function ($q) {
                $q->whereNull('gambar')->orWhere('gambar', '');
            });
    }

    /** Buktinya sudah diunggah peserta. */
    public function getAdaBuktiAttribute(): bool
    {
        return trim((string) $this->gambar) !== '';
    }

    public static function lepaskanYangKedaluwarsa(?string $kategoriId = null): int
    {
        $kueri = static::bisaDilepas();

        if ($kategoriId !== null) {
            $kueri->where('kategori_id', $kategoriId);
        }

        $lewat = $kueri->get();

        if ($lewat->isEmpty()) {
            return 0;
        }

        $kursi = 0;

        /*
         * Yang BENAR-BENAR berpindah dikumpulkan, bukan seluruh $lewat.
         *
         * Dua proses bisa menyapu bersamaan, dan yang kalah merebut kunci
         * baris keluar tanpa mengubah apa pun. Dikabari dari $lewat,
         * orangnya menerima surat dari kedua proses itu.
         */
        $berpindah = [];

        foreach ($lewat as $p) {
            /*
             * Status dan kuota diubah dalam SATU transaksi. Terpisah, proses
             * yang gagal di tengah meninggalkan pendaftaran yang sudah
             * kedaluwarsa tetapi kursinya belum kembali — dan tidak ada yang
             * akan mengulangnya karena statusnya sudah bukan 'pending'.
             */
            \Illuminate\Support\Facades\DB::transaction(function () use ($p, &$kursi, &$berpindah) {
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

                $berpindah[] = $segar;

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

        /*
         * Kabarnya dikirim SESUDAH semua transaksinya selesai, bukan di
         * dalamnya.
         *
         * Di dalam transaksi, surat untuk transaksi yang ternyata batal
         * sudah terlanjur terbang — dan peladen surat tidak ikut
         * di-rollback. Lagi pula menahan kunci baris selama SMTP menjawab
         * berarti seluruh sapuan ini menunggu jaringan.
         *
         * Kegagalan satu surat TIDAK boleh menghentikan sisanya: kursinya
         * sudah telanjur dilepas, dan sapuan yang berhenti di tengah akan
         * meninggalkan sisanya tanpa kabar selamanya.
         */
        $kabari = app(\App\Actions\WebinarEksklusif\KabariKursiDilepas::class);

        foreach ($berpindah as $p) {
            try {
                $kabari->jalankan($p);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Kabar kursi dilepas gagal.', [
                    'nomor' => $p->id_transaksi,
                    'galat' => $e->getMessage(),
                ]);
            }
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

        /*
         * Nomor yang pernah DIHAPUS ikut diperhitungkan, dan ini bukan soal
         * kerapian.
         *
         * Nomor ini dipakai sebagai invoice_number di gerbang pembayaran.
         * Dihitung dari tabel hidup saja, menghapus satu pendaftaran
         * membebaskan nomornya — dan pendaftaran BERIKUTNYA memakai nomor
         * yang sama. Akibatnya berantai, dan ketiganya terjadi 17 Okt 2026:
         *
         *   1. gerbang menolak tagihannya sebab nomor itu sudah terpakai;
         *   2. saat statusnya ditanyakan, gerbang menjawab SUCCESS — untuk
         *      pembayaran milik pendaftaran yang sudah dihapus;
         *   3. pendaftaran baru itu tertandai LUNAS tanpa ada uang masuk,
         *      dan pendaftarnya menerima surat "SUDAH DIBAYAR".
         *
         * Terukur: WE-20261017-0015 dihapus pukul 21:49:10, dipakai ulang
         * pukul 21:49:57, dan langsung mewarisi pembayaran Rp 130.239 milik
         * pendahulunya.
         */
        $terakhirDihapus = DB::table('pendaftaran_dihapus')
            ->where('layanan', 'webinar_eksklusif')
            ->where('nomor', 'like', $awalan . '%')
            ->orderByDesc('nomor')->value('nomor');

        $urut = max(
            $terakhir ? (int) substr($terakhir, -4) : 0,
            $terakhirDihapus ? (int) substr($terakhirDihapus, -4) : 0
        ) + 1;

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
        /*
         * Yang buktinya sudah masuk TIDAK terbaca kedaluwarsa, sejalan dengan
         * scopeBisaDilepas(): kursinya memang ditahan sampai panitia
         * memeriksanya. Tanpa ini halaman statusnya akan berkata "batas
         * waktunya lewat, kursinya dilepas" kepada orang yang kursinya justru
         * sedang ditahan untuknya.
         */
        return $this->status === 'pending'
            && ! $this->ada_bukti
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

    /**
     * Sisa waktu membayar dalam DETIK; 0 kalau sudah lewat atau tidak berbatas.
     *
     * Dipakai hitung mundur di halaman status untuk menggambar angka awalnya
     * di peladen — tanpa itu, yang tergambar sebelum skripnya jalan tiga kotak
     * kosong. Dihitung di sini, bukan di dalam Blade: perhitungannya
     * bersarang kurung, dan @php(...) sebaris di Blade salah mengurainya
     * dengan galat yang menunjuk tempat lain sama sekali.
     */
    public function getSisaDetikAttribute(): int
    {
        if ($this->kedaluwarsa_pada === null) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->kedaluwarsa_pada, false));
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
