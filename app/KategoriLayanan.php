<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu angkatan (kategori) dari layanan jasa mana pun.
 *
 * Dulu ini dua tabel yang isinya sama persis — `scopus_camp_kategori` dan
 * `categories_analisis_bibliometrik` — dengan 21 kolom yang identik. Dua tabel
 * untuk satu bentuk berarti dua pengendali, dua layar, dan dua tempat yang
 * harus diubah setiap kali aturannya berubah.
 *
 * Kolom `layanan` memakai kunci yang SAMA dengan katalog di
 * katalog Layanan, supaya satu angkatan bisa langsung
 * menemukan tarif dan fasilitas induknya tanpa peta perantara.
 *
 * `lokasi` dan `best_price` hanya terpakai Scopus Camp; keduanya boleh NULL
 * dan dibiarkan kosong oleh layanan lain.
 */
class KategoriLayanan extends Model
{
    /** @var array<string, int>|null */
    private static ?array $pendaftar = null;

    /** Dibuang uji yang mengubah pendaftarnya di tengah jalan. */
    public static function lupakanPendaftar(): void
    {
        self::$pendaftar = null;
    }

    protected $table = 'kategori_layanan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'token',
        'layanan',
        'varian',
        'nama',
        'nama_ke',
        'mulai',
        'selesai',
        'total_kuota',
        'sisa_kuota',
        'desc',
        'best_price',
        'lokasi',
        'biaya',
        'ppn',
        'tipe_diskon',
        'diskon_persentase',
        'nominal_diskon',
        'kode_diskon',
        'total_biaya',
        'status',
        'group_wa',
        'gambar',
        'created_at',
        'updated_at',
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

    // ----------------------------------------------------------------- kueri

    public function scopeLayanan(Builder $kueri, string $layanan): Builder
    {
        return $kueri->where('layanan', $layanan);
    }

    // -------------------------------------------------------------- tautan

    /** Tarif induk yang jadi harga patokan angkatan ini. */
    public function tarif(): ?ClinikScopusBiayaPersesi
    {
        return ClinikScopusBiayaPersesi::berlaku($this->layanan, $this->varian);
    }

    /**
     * Jumlah pendaftar SEMUA angkatan sekaligus, dalam dua kueri berkelompok.
     *
     * Bukan satu kueri per baris: daftarnya berhalaman sepuluh dan jumlahnya
     * dipakai di tiga tempat (lencana kuota, penjaga kuota, tautan pendaftar),
     * jadi dibaca satu per satu ia jadi tiga puluh kueri.
     *
     * Dua tabel karena pendaftarannya memang dua: Scopus Camp dan Bibliometrik
     * menyimpan pendaftarnya sendiri-sendiri, keduanya menunjuk `kategori_id`.
     *
     * @return array<string, int>
     */
    public static function hitungPendaftar(): array
    {
        if (self::$pendaftar !== null) {
            return self::$pendaftar;
        }

        $hasil = [];

        foreach (['scopus_camp_pendaftaran', 'analisis_bibliometrik'] as $tabel) {
            $baris = DB::table($tabel)
                ->whereNotNull('kategori_id')
                ->selectRaw('kategori_id, count(*) as n')
                ->groupBy('kategori_id')
                ->pluck('n', 'kategori_id');

            foreach ($baris as $id => $n) {
                $hasil[$id] = ($hasil[$id] ?? 0) + (int) $n;
            }
        }

        return self::$pendaftar = $hasil;
    }

    /** Berapa orang yang sudah mendaftar di angkatan ini. */
    public function getJumlahPendaftarAttribute(): int
    {
        return self::hitungPendaftar()[$this->getKey()] ?? 0;
    }

    /** Kuotanya sudah habis. */
    public function getKuotaHabisAttribute(): bool
    {
        return $this->total_kuota !== null && (int) $this->sisa_kuota < 1;
    }

    /**
     * Aktif padahal tanggalnya sudah lewat.
     *
     * Tidak ada apa pun yang menutup angkatan otomatis, jadi ia bisa terpajang
     * sebagai "Aktif" berbulan-bulan sesudah acaranya selesai.
     */
    public function getSudahLewatAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $akhir = $this->selesai ?: $this->mulai;

        return $akhir && \Carbon\Carbon::parse($akhir)->endOfDay()->isPast();
    }

    /**
     * Folder tempat sampul angkatan disimpan, menurut layanannya.
     *
     * Kebiasaannya sudah terlanjur berbeda per layanan — halaman publik Scopus
     * Camp membaca dari `ScopusCamp/`, Bibliometrik dari `bibliometrik/` —
     * dan keduanya memakai basename(), jadi yang penting nama berkasnya ada di
     * folder yang benar. Layanan yang belum punya halaman publik ditaruh di
     * folder sendiri daripada menumpang salah satu di atas.
     */
    public const FOLDER_SAMPUL = [
        'scopus_camp' => 'ScopusCamp',
        'bibliometrik' => 'bibliometrik',
    ];

    public function folderSampul(): string
    {
        return self::FOLDER_SAMPUL[$this->layanan] ?? 'angkatan';
    }

    /** Alamat sampul untuk ditampilkan; null kalau belum ada. */
    public function getAlamatSampulAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        $berkas = public_path($this->folderSampul() . '/' . basename($this->gambar));

        return is_file($berkas) ? asset($this->folderSampul() . '/' . basename($this->gambar)) : null;
    }

    // ------------------------------------------------------------- tampilan

    public function getNamaLayananAttribute(): string
    {
        return Layanan::katalog()[$this->layanan]['nama']
            ?? Str::title(str_replace('_', ' ', (string) $this->layanan));
    }

    /**
     * Fasilitas angkatan ini — miliknya sendiri kalau ada, kalau tidak
     * mengikuti tarif induk.
     *
     * Angkatan menyimpan salinannya sendiri karena peserta yang sudah
     * mendaftar berhak atas fasilitas yang dijanjikan saat itu, bukan yang
     * berlaku sekarang.
     *
     * @return array<int, string>
     */
    public function getDaftarFasilitasAttribute(): array
    {
        return $this->tarif()?->daftar_fasilitas ?? [];
    }
}
