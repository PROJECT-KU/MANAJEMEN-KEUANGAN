<?php

namespace App;

use App\Support\AlamatGambar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Satu foto galeri milik sebuah layanan.
 *
 * Berkasnya selalu WebP di storage — yang menjaganya App\Services\Gambar,
 * satu-satunya pintu masuk unggahan galeri.
 */
class GaleriLayanan extends Model
{
    protected $table = 'galeri_layanan';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'layanan', 'kategori_id', 'berkas', 'keterangan', 'urutan', 'aktif', 'penginput_id',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    /** Folder tempat berkas galeri layanan ini disimpan di storage. */
    public static function folder(string $layanan): string
    {
        return 'galeri/' . $layanan;
    }

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
                $model->urutan = (int) self::where('layanan', $model->layanan)->max('urutan') + 1;
            }
        });
    }

    public function angkatan(): BelongsTo
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_id');
    }

    /**
     * Foto yang tampil untuk satu angkatan.
     *
     * Yang diambil: foto milik angkatan itu DAN foto umum layanannya (yang
     * kategori_id-nya kosong). Dokumentasi sesi itu sendiri didahulukan —
     * kalau ada, itu yang paling meyakinkan; foto umum jadi pelengkap supaya
     * sesi yang baru dijadwalkan galerinya tidak kosong.
     */
    public function scopeUntukAngkatan(Builder $kueri, KategoriLayanan $angkatan): Builder
    {
        return $kueri
            ->where('layanan', $angkatan->layanan)
            ->where('aktif', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('kategori_id')
                ->orWhere('kategori_id', $angkatan->getKey()))
            // Milik angkatan ini lebih dulu, lalu ikut urutan yang disetel.
            ->orderByRaw('case when kategori_id is null then 1 else 0 end')
            ->orderBy('urutan')
            ->orderBy('created_at');
    }

    public function scopeLayanan(Builder $kueri, string $layanan): Builder
    {
        return $kueri->where('layanan', $layanan)->orderBy('urutan')->orderBy('created_at');
    }

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

        if ($isi !== '') {
            return $isi;
        }

        // Cadangan yang masih berarti bagi pembaca layar, bukan "gambar".
        return 'Dokumentasi kegiatan ' . (Layanan::katalog()[$this->layanan]['nama'] ?? 'Rumah Scopus');
    }
}
