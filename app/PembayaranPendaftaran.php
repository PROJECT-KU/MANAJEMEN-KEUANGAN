<?php

namespace App;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kali uang masuk untuk sebuah pesanan rombongan — DP, cicilan, atau
 * pelunasan.
 *
 * Sebelum ini uang hanya punya dua keadaan: "Menunggu bayar" atau "Lunas".
 * Lembaga yang sudah mentransfer DP 30% tercatat sama persis dengan yang
 * belum bayar sepeser pun.
 *
 * Induknya dua jenis, dan keduanya memang berbeda bentuk — lihat migrasinya.
 */
class PembayaranPendaftaran extends Model
{
    use HasUuids;

    protected $table = 'pembayaran_pendaftaran';

    /** Induknya satu baris pemesanan_lembaga. */
    public const LEMBAGA = 'lembaga';

    /** Induknya satu baris pendaftaran berisi banyak kursi. */
    public const PENDAFTARAN = 'pendaftaran';

    protected $fillable = [
        'jenis', 'induk_id', 'layanan', 'urutan', 'nominal',
        'tanggal', 'cara_bayar', 'bukti', 'catatan', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
        'urutan' => 'integer',
    ];

    /** @return \Illuminate\Database\Eloquent\Builder<self> */
    public function scopeMilik($kueri, string $jenis, string $indukId)
    {
        return $kueri->where('jenis', $jenis)->where('induk_id', $indukId);
    }

    /** Terurut seperti terminnya, bukan seperti dicatatnya. */
    public function scopeTerurut($kueri)
    {
        return $kueri->orderBy('urutan');
    }

    /**
     * Sebutan termin yang dibaca orang.
     *
     * Termin pertama disebut "DP" dan yang terakhir "Pelunasan" — itu kata
     * yang dipakai orang keuangan lembaga di telepon. Tetapi "Pelunasan"
     * hanya kalau tagihannya memang sudah tertutup: termin terakhir yang
     * masih menyisakan tagihan bukan pelunasan, dan menyebutnya begitu
     * membuat kwitansinya berbohong.
     */
    public function sebutan(bool $lunasSesudahnya, int $dariBerapa): string
    {
        /*
         * Satu-satunya pembayaran: bukan DP kalau ia menutup tagihannya.
         * Menyebutnya "DP" pada kwitansi yang sudah melunasi membuat orang
         * keuangan lembaga mengira masih ada sisa.
         */
        if ($dariBerapa <= 1) {
            return $lunasSesudahnya ? 'Pembayaran penuh' : 'DP';
        }

        // Yang pertama selalu DP, lunas atau belum — itu memang uang muka,
        // dan namanya tidak berubah belakangan karena sisanya sudah dibayar.
        if ($this->urutan <= 1) {
            return 'DP';
        }

        /*
         * "Pelunasan" hanya kalau tagihannya memang sudah tertutup. Termin
         * terakhir yang masih menyisakan tagihan bukan pelunasan, dan
         * menyebutnya begitu membuat kwitansinya berbohong.
         */
        if ($lunasSesudahnya && $this->urutan >= $dariBerapa) {
            return 'Pelunasan';
        }

        return 'Termin ' . $this->urutan;
    }

    /** Cara bayarnya dalam kata, mengikuti katalog yang sama dengan borang. */
    public function getCaraBayarTerbacaAttribute(): string
    {
        $cara = Pendaftaran::caraBayar($this->cara_bayar);

        return $cara['ringkas'] ?? $cara['label'] ?? '—';
    }

    /**
     * Ringkasan uang untuk satu induk: tagihan, terbayar, sisa.
     *
     * Tagihannya DIHITUNG dari pendaftaran yang terikat, tidak disimpan.
     * Disimpan, ia akan berselisih dengan jumlah barisnya begitu satu
     * pendaftaran ditambahkan ke pesanan yang sama — dan selisihnya baru
     * ketahuan saat menagih.
     *
     * @return array{tagihan: int, terbayar: int, sisa: int, lunas: bool, jumlah: int}
     */
    public static function ringkas(string $jenis, string $indukId, int $tagihan): array
    {
        $bayar = static::milik($jenis, $indukId)->get(['nominal']);
        $terbayar = (int) $bayar->sum('nominal');

        return [
            'tagihan' => $tagihan,
            'terbayar' => $terbayar,
            // Tidak pernah negatif di layar: kelebihan bayar memang terjadi
            // (lembaga membulatkan ke atas), dan "sisa -Rp 15.000" terbaca
            // seperti galat hitung, bukan seperti kelebihan.
            'sisa' => max(0, $tagihan - $terbayar),
            'lunas' => $tagihan > 0 && $terbayar >= $tagihan,
            'jumlah' => $bayar->count(),
        ];
    }

    /**
     * Induk mana yang menanggung pembayaran sebuah pendaftaran — atau null
     * kalau pendaftaran itu memang bukan pesanan rombongan.
     *
     * SATU aturan di satu tempat, dipakai layar rincian, penyimpan, dan
     * ujinya. Kalau ketiganya memutuskan sendiri-sendiri, panel pembayaran
     * bisa muncul di layar sementara penyimpannya menolak — dan panitia
     * melihat tombol yang tidak bekerja tanpa penjelasan apa pun.
     *
     * Urutannya penting: pesanan lembaga MENANG atas jumlah kursi. Pendaftaran
     * yang terikat pesanan lembaga dibayar oleh lembaganya, bukan sendiri,
     * walau barisnya berisi sepuluh kursi — mencatat pembayaran di barisnya
     * akan memecah uang satu pesanan ke beberapa tempat.
     *
     * Pendaftar satu kursi TIDAK punya induk: yang dibuka ke pembayaran
     * bertermin hanya rombongan, dan satu orang tetap membayar penuh di muka
     * seperti sebelumnya.
     *
     * @return array{jenis: string, induk_id: string, layanan: string|null, tagihan: int}|null
     */
    public static function indukUntuk(
        string $layanan,
        string $pendaftaranId,
        ?PemesananLembaga $lembaga,
        int $jumlahOrang,
        int $total
    ): ?array {
        if ($lembaga !== null) {
            return [
                'jenis' => self::LEMBAGA,
                'induk_id' => (string) $lembaga->getKey(),
                'layanan' => null,
                'tagihan' => $lembaga->tagihan(),
            ];
        }

        if ($jumlahOrang > 1) {
            return [
                'jenis' => self::PENDAFTARAN,
                'induk_id' => $pendaftaranId,
                'layanan' => $layanan,
                'tagihan' => $total,
            ];
        }

        return null;
    }

    /** Nomor termin berikutnya untuk satu induk. */
    public static function urutanBerikut(string $jenis, string $indukId): int
    {
        return (int) static::milik($jenis, $indukId)->max('urutan') + 1;
    }
}
