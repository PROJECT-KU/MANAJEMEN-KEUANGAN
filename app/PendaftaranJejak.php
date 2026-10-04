<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu jejak perubahan pada satu pendaftaran.
 *
 * Terpisah dari catatan panitia dengan sengaja — lihat migrasinya.
 */
class PendaftaranJejak extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_jejak';

    /** Hanya created_at; jejak tidak pernah disunting. */
    public const UPDATED_AT = null;

    /*
     * created_at IKUT fillable.
     *
     * Bukan kelalaian: jejak lama yang dipindahkan dari kolom catatan membawa
     * waktunya sendiri, dan tanpa ini Eloquent membuangnya diam-diam lalu
     * menggantinya dengan waktu pemindahan. Terukur saat pertama dicoba:
     * lima jejak yang aslinya pukul 20:45-20:46 semuanya tercatat 22:46.
     */
    protected $fillable = [
        'layanan', 'pendaftaran_id', 'aksi', 'dari', 'ke',
        'ringkasan', 'oleh_id', 'oleh_nama', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    /** @return \Illuminate\Database\Eloquent\Builder */
    public function scopeMilik($kueri, string $layanan, string $id)
    {
        return $kueri->where('layanan', $layanan)->where('pendaftaran_id', $id);
    }

    /** Terbaru di bawah: jejak dibaca sebagai cerita dari awal. */
    public function scopeTerurut($kueri)
    {
        return $kueri->orderBy('created_at')->orderBy('id');
    }

    /**
     * Satu kalimat yang bisa dibaca panitia.
     *
     * Dirakit di sini, bukan disimpan jadi untaian: kalimatnya boleh
     * diperbaiki kapan saja tanpa menyentuh baris yang sudah tersimpan.
     */
    public function getKalimatAttribute(): string
    {
        if ($this->ringkasan) {
            return $this->ringkasan;
        }

        $siapa = $this->oleh_nama ? ' oleh ' . $this->oleh_nama : '';

        if ($this->aksi === 'status') {
            return 'Status "' . $this->dari . '" → "' . $this->ke . '"' . $siapa;
        }

        return ucfirst($this->aksi) . $siapa;
    }
}
