<?php

namespace App;

use App\Support\AlamatGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu foto galeri, boleh dipakai beberapa layanan sekaligus.
 *
 * Berkasnya selalu WebP di storage — yang menjaganya App\Services\Gambar,
 * satu-satunya pintu masuk unggahan galeri.
 */
class Galeri extends Model
{
    protected $table = 'galeri';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'berkas', 'keterangan', 'semua_layanan', 'kategori_id',
        'urutan', 'aktif', 'penginput_id',
    ];

    protected $casts = [
        'semua_layanan' => 'boolean',
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    /** Folder tempat berkas galeri disimpan di storage. */
    public const FOLDER = 'galeri';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->penginput_id)) {
                $model->penginput_id = Auth::id();
            }

            // Foto baru diletakkan DI BELAKANG yang sudah ada. Diberi 0,
            // semuanya berebut tempat pertama dan urutannya jadi acak.
            if ($model->urutan === null || (int) $model->urutan === 0) {
                $model->urutan = (int) self::max('urutan') + 1;
            }
        });

        // Baris sambungannya ikut terbuang lewat cascade pada kunci asing;
        // yang di sini hanya penjaga kalau cascade-nya tidak aktif.
        static::deleted(fn ($m) => DB::table('galeri_layanan')->where('galeri_id', $m->getKey())->delete());
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_id');
    }

    // ------------------------------------------------------------ layanan

    /**
     * Kode layanan yang memakai foto ini.
     *
     * @return array<int, string>
     */
    public function getDaftarLayananAttribute(): array
    {
        return DB::table('galeri_layanan')->where('galeri_id', $this->getKey())
            ->orderBy('layanan')->pluck('layanan')->all();
    }

    /**
     * Mengganti daftar layanan foto ini.
     *
     * Dihapus lalu ditulis ulang, bukan dibandingkan satu per satu: daftarnya
     * paling banyak selusin, dan membandingkan dua himpunan kecil lebih banyak
     * kodenya daripada gunanya.
     *
     * @param  array<int, string>  $kode
     */
    public function setLayanan(array $kode): void
    {
        $sah = array_values(array_unique(array_filter(
            $kode,
            fn ($k) => array_key_exists($k, Layanan::katalog())
        )));

        DB::transaction(function () use ($sah) {
            DB::table('galeri_layanan')->where('galeri_id', $this->getKey())->delete();

            if ($sah === []) {
                return;
            }

            DB::table('galeri_layanan')->insert(array_map(
                fn ($k) => ['galeri_id' => $this->getKey(), 'layanan' => $k],
                $sah
            ));
        });
    }

    /** Nama layanan yang terbaca orang, untuk ditampilkan di layar admin. */
    public function getSebutLayananAttribute(): string
    {
        if ($this->semua_layanan) {
            return 'Semua layanan';
        }

        $katalog = Layanan::katalog();
        $nama = array_map(fn ($k) => $katalog[$k]['nama'] ?? $k, $this->daftar_layanan);

        return $nama === [] ? 'Belum dipilihkan layanan' : implode(', ', $nama);
    }

    // -------------------------------------------------------------- kueri

    /**
     * Foto yang tampil untuk satu angkatan.
     *
     * Tiga lapis penyaringan, dari yang paling luas:
     *
     *   1. layanannya cocok — lewat `semua_layanan` atau baris sambungan
     *   2. angkatannya cocok — milik angkatan itu, atau umum (kategori_id NULL)
     *   3. aktif
     *
     * Dokumentasi sesi itu sendiri didahulukan: kalau ada, itu yang paling
     * meyakinkan; foto umum jadi pelengkap supaya sesi yang baru dijadwalkan
     * galerinya tidak kosong.
     */
    public function scopeUntukAngkatan(Builder $kueri, KategoriLayanan $angkatan): Builder
    {
        return $kueri
            ->where('aktif', true)
            ->where(fn (Builder $q) => $q
                ->where('semua_layanan', true)
                ->orWhereExists(fn ($x) => $x->from('galeri_layanan')
                    ->whereColumn('galeri_layanan.galeri_id', 'galeri.id')
                    ->where('galeri_layanan.layanan', $angkatan->layanan)))
            ->where(fn (Builder $q) => $q
                ->whereNull('kategori_id')
                ->orWhere('kategori_id', $angkatan->getKey()))
            ->orderByRaw('case when kategori_id is null then 1 else 0 end')
            ->orderBy('urutan')
            ->orderBy('created_at');
    }

    /** Foto yang terdaftar pada satu layanan; `semua_layanan` ikut terjaring. */
    public function scopeLayanan(Builder $kueri, ?string $layanan): Builder
    {
        if ($layanan === null || $layanan === '') {
            return $kueri->orderBy('urutan')->orderBy('created_at');
        }

        return $kueri
            ->where(fn (Builder $q) => $q
                ->where('semua_layanan', true)
                ->orWhereExists(fn ($x) => $x->from('galeri_layanan')
                    ->whereColumn('galeri_layanan.galeri_id', 'galeri.id')
                    ->where('galeri_layanan.layanan', $layanan)))
            ->orderBy('urutan')
            ->orderBy('created_at');
    }

    // ----------------------------------------------------------- tampilan

    /** Alamat berkasnya; null kalau berkasnya sudah tidak ada di cakram. */
    public function getAlamatAttribute(): ?string
    {
        return AlamatGambar::url($this->berkas);
    }

    /** Berkasnya tercatat tetapi sudah tidak ada di cakram. */
    public function getBerkasHilangAttribute(): bool
    {
        return trim((string) $this->berkas) !== '' && $this->alamat === null;
    }

    /** Teks pengganti gambar yang selalu ada isinya. */
    public function getKeteranganTampilAttribute(): string
    {
        $isi = trim((string) $this->keterangan);

        // Cadangan yang masih berarti bagi pembaca layar, bukan "gambar".
        return $isi !== '' ? $isi : 'Dokumentasi kegiatan Rumah Scopus';
    }
}
