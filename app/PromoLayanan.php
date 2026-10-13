<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Satu promo, berlaku untuk angkatan mana pun dari layanan mana pun.
 *
 * Menggantikan dua cara memberi promo yang sebelumnya tidak saling tahu:
 * tabel `clinikscopus_promo` yang cuma melayani Clinik Scopus, dan kolom
 * diskon yang menempel di tiap baris `kategori_layanan` — yang terakhir tidak
 * bisa dipakai ulang, jadi satu promo untuk enam angkatan harus diketik enam
 * kali dan dihentikan enam kali.
 *
 * Promo di sini berlaku OTOMATIS: tidak ada kode yang diketik pendaftar.
 * Diminta begitu, dan memang lebih sedikit yang bisa salah — admin cukup
 * menyetel potongan, tanggal, dan angkatan mana saja yang kena.
 *
 * Keadaannya (belum mulai / berjalan / sudah selesai) DIHITUNG dari
 * tanggalnya setiap kali dibaca, tidak disimpan di kolom dan tidak dinaikkan
 * penjadwal. Cron di hosting ini belum pernah terpasang — status yang
 * bergantung penjadwal akan tertinggal berhari-hari tanpa ada yang tahu,
 * dan promo yang sudah lewat tetap memotong harga.
 */
class PromoLayanan extends Model
{
    protected $table = 'promo_layanan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nama',
        'tipe',
        'persen',
        'nominal',
        'batas_potongan',
        'kuota',
        'mulai',
        'selesai',
        'aktif',
        'catatan',
        'penginput_id',
    ];

    protected $casts = [
        'mulai' => 'date',
        'selesai' => 'date',
        'aktif' => 'boolean',
        'persen' => 'integer',
        'nominal' => 'integer',
        'batas_potongan' => 'integer',
        'kuota' => 'integer',
    ];

    /** Potongan sekian persen dari harga. */
    public const PERSEN = 'persen';

    /** Potongan sejumlah rupiah, berapa pun harganya. */
    public const RUPIAH = 'rupiah';

    /** Dibaca borang dan validator; satu daftar, bukan dua yang harus sama. */
    public const TIPE = [
        self::PERSEN => 'Potong sekian persen',
        self::RUPIAH => 'Potong sejumlah rupiah',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }

            // Siapa yang membuat dicatat otomatis, bukan diminta dari borang:
            // isian yang bisa dilewati bukan jejak.
            if (! $model->penginput_id && auth()->check()) {
                $model->penginput_id = auth()->id();
            }
        });

        /*
         * Jejak ditulis dari kait model, BUKAN dari pengendali.
         *
         * Promo diubah dari borang, dari saklar di layar daftar, dan dari
         * tombol Gandakan — mencatat di pengendali berarti tiga tempat yang
         * harus diingat, dan yang keempat pasti terlewat.
         */
        static::created(fn ($m) => PromoJejak::catat($m, 'dibuat', $m->ringkasIsi()));

        static::updated(function ($m) {
            $berubah = $m->ringkasPerubahan();

            // Penyimpanan yang tidak mengubah apa pun tidak dicatat; kalau
            // tidak, jejaknya penuh baris "diubah" tanpa isi.
            if ($berubah === '') {
                return;
            }

            /*
             * Menyalakan dan mematikan dapat aksinya sendiri.
             *
             * Keduanya peristiwa yang paling sering dicari di jejak — "sejak
             * kapan promo ini mati?" — dan terkubur di antara baris "diubah"
             * ia harus dicari dengan membaca ringkasan satu per satu.
             */
            $kunci = array_keys($m->getChanges());
            $hanyaSaklar = array_diff($kunci, ['aktif', 'updated_at']) === [];

            PromoJejak::catat(
                $m,
                $hanyaSaklar ? ($m->aktif ? 'dinyalakan' : 'dimatikan') : 'diubah',
                $berubah
            );
        });

        /*
         * Salinan utuh barisnya ikut disimpan. Dicatat di `deleted`, bukan
         * `deleting`: kalau penghapusannya gagal, tidak boleh ada jejak yang
         * mengaku sudah terhapus.
         */
        static::deleted(fn ($m) => PromoJejak::catat(
            $m, 'dihapus', $m->ringkasIsi(), $m->getOriginal()
        ));
    }

    /** Jejak perubahan promo ini, terbaru dulu. */
    public function jejak()
    {
        /*
         * Diurutkan dari kunci berurutnya, BUKAN dari created_at.
         *
         * Dua peristiwa bisa jatuh di milidetik yang sama — dibuat lalu
         * langsung dimatikan oleh kode yang sama — dan saat itu urutan menurut
         * waktu ditentukan apa pun yang kebetulan dikembalikan MySQL.
         * Terukur: ujinya hijau sendirian, merah di rangkaian penuh.
         */
        return $this->hasMany(PromoJejak::class, 'promo_id')->orderByDesc('id');
    }

    /**
     * Isi promo dalam satu kalimat, untuk jejak "dibuat" dan "dihapus".
     *
     * Tanpa ini, baris "dibuat" di jejak tidak menyatakan promo seperti apa
     * yang dibuat — dan itu justru baris yang paling sering dibaca saat
     * menelusuri harga yang tidak terduga.
     */
    public function ringkasIsi(): string
    {
        return 'potongan ' . $this->potongan_ringkas
            . ', ' . $this->periode_terbaca
            . ($this->kuota ? ', kuota ' . $this->kuota . ' pendaftaran' : '');
    }

    /**
     * Kalimat pendek berisi apa saja yang berubah pada penyimpanan terakhir.
     *
     * Hanya kolom yang BERARTI bagi orang. `updated_at` selalu berubah dan
     * menyebutkannya cuma membuat tiap jejak berbunyi sama.
     */
    public function ringkasPerubahan(): string
    {
        $nama = [
            'nama' => 'nama',
            'tipe' => 'jenis potongan',
            'persen' => 'potongan persen',
            'nominal' => 'potongan rupiah',
            'batas_potongan' => 'maksimal potongan',
            'kuota' => 'kuota',
            'mulai' => 'tanggal mulai',
            'selesai' => 'tanggal selesai',
            'aktif' => 'keadaan',
            'catatan' => 'catatan',
        ];

        $bagian = [];

        foreach ($this->getChanges() as $kolom => $baru) {
            if (! array_key_exists($kolom, $nama)) {
                continue;
            }

            $lama = $this->getOriginal($kolom);

            // Catatan bisa ratusan huruf; yang berguna cuma "berubah".
            if ($kolom === 'catatan') {
                $bagian[] = 'catatan diganti';

                continue;
            }

            if ($kolom === 'aktif') {
                $bagian[] = 'keadaan: ' . ($lama ? 'menyala' : 'mati') . ' → ' . ($baru ? 'menyala' : 'mati');

                continue;
            }

            $bagian[] = $nama[$kolom] . ': ' . self::terbaca($kolom, $lama) . ' → ' . self::terbaca($kolom, $baru);
        }

        return implode(', ', $bagian);
    }

    /** @param mixed $nilai */
    private static function terbaca(string $kolom, $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '(kosong)';
        }

        // Tanggal disimpan sebagai objek sesudah cast; yang dibaca orang
        // harinya saja, bukan "2026-10-13 00:00:00".
        if (in_array($kolom, ['mulai', 'selesai'], true)) {
            return \Carbon\Carbon::parse((string) $nilai)->format('d/m/Y');
        }

        if (in_array($kolom, ['nominal', 'batas_potongan'], true)) {
            return 'Rp ' . number_format((int) $nilai, 0, ',', '.');
        }

        return (string) $nilai;
    }

    // --------------------------------------------------------------- tautan

    /** Angkatan yang kena promo ini. */
    public function angkatan()
    {
        return $this->belongsToMany(
            KategoriLayanan::class,
            'promo_layanan_angkatan',
            'promo_id',
            'kategori_id'
        )->withTimestamps();
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'penginput_id');
    }

    // ----------------------------------------------------------------- kueri

    /**
     * Promo yang BENAR-BENAR sedang memotong harga hari ini.
     *
     * Tiga syarat sekaligus, dan ketiganya harus di satu tempat: saklarnya
     * menyala, hari ini sudah lewat tanggal mulai, dan belum lewat tanggal
     * selesai. Diperiksa terpisah-pisah di beberapa layar, cepat atau lambat
     * ada satu yang lupa salah satunya — dan promo yang sudah selesai
     * diam-diam tetap memotong.
     *
     * `whereDate` pada kedua ujungnya: kolomnya `date`, jadi `selesai` berarti
     * sampai HABIS hari itu, bukan sampai tengah malam awal harinya.
     */
    public function scopeBerjalan(Builder $kueri, ?Carbon $pada = null): Builder
    {
        $hari = ($pada ?? Carbon::now())->toDateString();

        return $kueri->where('aktif', true)
            ->whereDate('mulai', '<=', $hari)
            ->whereDate('selesai', '>=', $hari);
    }

    public function scopeCari(Builder $kueri, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $kueri;
        }

        return $kueri->where(fn ($q) => $q
            ->where('nama', 'like', '%' . $kata . '%')
            ->orWhere('catatan', 'like', '%' . $kata . '%'));
    }

    /**
     * Menyaring menurut keadaan yang dibaca orang.
     *
     * Keadaannya tidak ada di kolom mana pun, jadi saringannya harus
     * merangkai ulang syaratnya di SQL. Dikerjakan di PHP setelah data
     * diambil, penomoran halamannya langsung salah: halaman pertama berisi
     * 10 baris lalu tersaring jadi 3, dan jumlah totalnya ikut berbohong.
     */
    public function scopeKeadaan(Builder $kueri, ?string $keadaan): Builder
    {
        $hari = Carbon::now()->toDateString();

        return match ($keadaan) {
            'berjalan' => $kueri->berjalan(),
            'belum-mulai' => $kueri->where('aktif', true)->whereDate('mulai', '>', $hari),
            'selesai' => $kueri->where('aktif', true)->whereDate('selesai', '<', $hari),
            'nonaktif' => $kueri->where('aktif', false),
            default => $kueri,
        };
    }

    // ----------------------------------------------------------- hitungannya

    /**
     * Berapa rupiah yang dipotong dari satu harga.
     *
     * SATU-SATUNYA tempat potongan promo dihitung. Rumusnya dipakai borang
     * pendaftaran panitia, halaman umum, dan pratinjau di layar promo
     * sendiri; ditulis tiga kali, cepat atau lambat ketiganya menjawab
     * berbeda untuk harga yang sama.
     *
     * Selalu dibatasi harganya: potongan yang melebihi harga menghasilkan
     * total negatif, dan nominal transfer negatif tidak berarti apa-apa.
     */
    public function potonganUntuk(int $harga): int
    {
        if ($harga <= 0) {
            return 0;
        }

        $potongan = $this->tipe === self::PERSEN
            ? (int) round($harga * max(0, min(100, (int) $this->persen)) / 100)
            : max(0, (int) $this->nominal);

        // Batasnya hanya berarti untuk promo persen — potongan rupiah sudah
        // berupa angka pasti, membatasinya lagi cuma membingungkan.
        if ($this->tipe === self::PERSEN && (int) $this->batas_potongan > 0) {
            $potongan = min($potongan, (int) $this->batas_potongan);
        }

        return min($harga, $potongan);
    }

    /** Harga sesudah promo ini, untuk pratinjau dan tampilan. */
    public function hargaSetelah(int $harga): int
    {
        return max(0, $harga - $this->potonganUntuk($harga));
    }

    /** Apakah promo ini sedang memotong harga hari ini. */
    public function getSedangBerjalanAttribute(): bool
    {
        return $this->keadaan === 'berjalan';
    }

    /**
     * Keadaan promo, dihitung dari saklar dan tanggalnya.
     *
     * Urutannya disengaja: saklar mati menang atas tanggal apa pun. Promo
     * yang dimatikan tangan di tengah periodenya harus terbaca "dimatikan",
     * bukan "berjalan" — kalau tidak, admin yang baru saja mematikannya
     * melihat layar yang bilang promonya masih jalan.
     */
    public function getKeadaanAttribute(): string
    {
        if (! $this->aktif) {
            return 'nonaktif';
        }

        $hari = Carbon::now()->startOfDay();

        if ($this->mulai && $this->mulai->gt($hari)) {
            return 'belum-mulai';
        }

        if ($this->selesai && $this->selesai->lt($hari)) {
            return 'selesai';
        }

        /*
         * Kuota habis diperiksa PALING AKHIR, dan sengaja.
         *
         * Ia satu-satunya keadaan yang menuntut membaca tabel pendaftaran.
         * Diperiksa lebih dulu, tiap promo yang sudah mati atau belum mulai
         * pun ikut menghitung pendaftaran — pekerjaan yang hasilnya pasti
         * dibuang.
         */
        if ($this->kuotaHabis()) {
            return 'kuota-habis';
        }

        return 'berjalan';
    }

    /**
     * Apakah jatah pendaftarannya sudah terpakai semua.
     *
     * null berarti tanpa batas; 0 berarti habis sejak detik pertama, dan itu
     * memang dua hal yang berbeda.
     */
    public function kuotaHabis(): bool
    {
        if ($this->kuota === null) {
            return false;
        }

        return \App\Support\DampakPromo::terpakai((string) $this->getKey()) >= (int) $this->kuota;
    }

    /** Berapa pendaftaran yang sudah memakai promo ini. */
    public function getTerpakaiAttribute(): int
    {
        return \App\Support\DampakPromo::terpakai((string) $this->getKey());
    }

    /** Sisa jatah, atau null kalau promonya memang tanpa batas. */
    public function getSisaKuotaAttribute(): ?int
    {
        return $this->kuota === null ? null : max(0, (int) $this->kuota - $this->terpakai);
    }

    /** Kalimat keadaan yang dibaca orang. */
    public function getKeadaanTerbacaAttribute(): string
    {
        return [
            'berjalan' => 'Sedang berjalan',
            'belum-mulai' => 'Belum mulai',
            'selesai' => 'Sudah selesai',
            'kuota-habis' => 'Kuota habis',
            'nonaktif' => 'Dimatikan',
        ][$this->keadaan] ?? 'Dimatikan';
    }

    /**
     * Warna lencana keadaan.
     *
     * Dipetakan di PHP, bukan dirangkai `@if` di Blade: tiga layar memajang
     * lencana yang sama, dan tiga rangkaian `@if` cepat atau lambat memberi
     * warna berbeda untuk keadaan yang sama.
     */
    public function getKeadaanWarnaAttribute(): string
    {
        return [
            'berjalan' => 'hijau',
            'belum-mulai' => 'biru',
            'selesai' => 'abu',
            'kuota-habis' => 'kuning',
            'nonaktif' => 'merah',
        ][$this->keadaan] ?? 'abu';
    }

    /** Potongannya dalam satu kata pendek: "40%" atau "Rp 75.000". */
    public function getPotonganRingkasAttribute(): string
    {
        return $this->tipe === self::PERSEN
            ? ((int) $this->persen) . '%'
            : 'Rp ' . number_format((int) $this->nominal, 0, ',', '.');
    }

    /**
     * Periodenya dalam bahasa Indonesia.
     *
     * `translatedFormat` dengan locale 'id' dipaksa di sini karena
     * APP_LOCALE=en di .env — tanpa itu bulannya tertulis "October" di layar
     * yang seluruh tulisannya bahasa Indonesia.
     */
    public function getPeriodeTerbacaAttribute(): string
    {
        if (! $this->mulai || ! $this->selesai) {
            return '-';
        }

        $mulai = $this->mulai->locale('id');
        $selesai = $this->selesai->locale('id');

        // Tahun di ujung depan dibuang kalau sama — "1 Feb – 28 Feb 2026"
        // terbaca jauh lebih cepat daripada dua tanggal penuh.
        if ($mulai->year === $selesai->year) {
            return $mulai->translatedFormat('j M') . ' – ' . $selesai->translatedFormat('j M Y');
        }

        return $mulai->translatedFormat('j M Y') . ' – ' . $selesai->translatedFormat('j M Y');
    }

    /**
     * Berapa hari lagi promonya habis, atau null kalau tidak sedang berjalan.
     *
     * Nol berarti habis hari ini. Dipakai menandai promo yang hampir selesai
     * di layar daftarnya.
     */
    public function getSisaHariAttribute(): ?int
    {
        if ($this->keadaan !== 'berjalan' || ! $this->selesai) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->selesai->copy()->startOfDay(), false);
    }
}
