<?php

namespace App;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Pesanan atas nama lembaga, mengikat beberapa pendaftaran jadi satu.
 *
 * Kuota tiap angkatan 20 kursi sedangkan lembaga rutin memesan lebih — jadi
 * pesanannya terpaksa dipecah, dan tanpa pengikat ini hasil pecahannya tidak
 * saling tahu bahwa mereka satu pesanan.
 */
class PemesananLembaga extends Model
{
    use HasUuids;

    protected $table = 'pemesanan_lembaga';

    public const TERBUKA = 'terbuka';
    public const SELESAI = 'selesai';

    /*
     * `no_po` menyimpan NOMOR SURAT PESANAN lembaganya (purchase order).
     * Nama kolomnya dibiarkan apa adanya: mengganti nama kolom menuntut
     * migrasi tersendiri, sedangkan yang membingungkan hanya kata di layar —
     * dan itu sudah diganti.
     */
    protected $fillable = [
        'kode', 'nama_lembaga', 'alamat', 'npwp', 'no_po',
        'pic_nama', 'pic_email', 'pic_telp', 'catatan', 'dibuat_oleh', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if ((string) $m->kode === '') {
                $m->kode = self::kodeBaru();
            }
        });
    }

    /**
     * Kode pesanan: PL-20261004-001.
     *
     * Berurut per HARI, bukan acak: yang menyebutnya di telepon saat menagih
     * adalah orang keuangan lembaga, dan lima huruf acak jauh lebih mudah
     * salah didengar daripada tanggal plus nomor urut.
     */
    public static function kodeBaru(): string
    {
        $awalan = 'PL-' . now()->format('Ymd') . '-';

        $terakhir = static::where('kode', 'like', $awalan . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $urut = $terakhir === null ? 1 : ((int) substr((string) $terakhir, -3)) + 1;

        return $awalan . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    /** Baris pengikatnya; satu baris per pendaftaran. */
    public function baris()
    {
        return $this->hasMany(PemesananLembagaBaris::class, 'pemesanan_id');
    }

    /**
     * Seluruh pendaftaran yang terikat, lewat kueri gabungan.
     *
     * Dibaca dari Pendaftaran::kueri(), bukan lima kueri terpisah: keadaan,
     * bukti, dan sesinya dirakit sekali di satu tempat, jadi faktur memakai
     * keterangan yang SAMA dengan daftar dan rinciannya.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function pendaftaran()
    {
        $pasangan = $this->baris()->get(['layanan', 'pendaftaran_id']);

        if ($pasangan->isEmpty()) {
            return collect();
        }

        return Pendaftaran::kueri()
            ->where(function ($q) use ($pasangan) {
                foreach ($pasangan as $p) {
                    $q->orWhere(fn ($sub) => $sub
                        ->where('layanan', $p->layanan)
                        ->where('id', $p->pendaftaran_id));
                }
            })
            ->orderBy('waktu')
            ->get();
    }

    /** Mengikat satu pendaftaran ke pesanan ini; aman dipanggil dua kali. */
    public function ikat(string $layanan, string $pendaftaranId): void
    {
        PemesananLembagaBaris::firstOrCreate([
            'pemesanan_id' => $this->getKey(),
            'layanan' => $layanan,
            'pendaftaran_id' => $pendaftaranId,
        ]);
    }

    /** Pesanan satu pendaftaran, atau null kalau ia berdiri sendiri. */
    public static function untukPendaftaran(string $layanan, string $pendaftaranId): ?self
    {
        $id = DB::table('pemesanan_lembaga_baris')
            ->where('layanan', $layanan)
            ->where('pendaftaran_id', $pendaftaranId)
            ->value('pemesanan_id');

        return $id === null ? null : static::find($id);
    }
}
