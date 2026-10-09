<?php

namespace App\Actions\Pendaftaran;

use App\Mail\PengingatPembayaranMail;

/**
 * Mengirim satu surat pengingat pembayaran, dan mencatatnya di jejak.
 *
 * Jejaknya ('ingat-bayar') bukan cuma catatan: perintah terjadwalnya membaca
 * baris itu untuk tahu siapa yang sudah diingatkan dan kapan. Tanpa penanda,
 * pendaftar yang sama menerima surat tiap hari.
 *
 * Penandanya di tabel jejak, BUKAN kolom baru di lima tabel pendaftaran —
 * yang berarti lima migrasi, dan empat dari kelima tabel itu kolom
 * waktunya bertipe varchar.
 */
class KirimSuratPengingat extends KirimSuratPendaftaran
{
    public const AKSI = 'ingat-bayar';

    public const AKSI_GAGAL = 'ingat-bayar-gagal';

    /** @return array{terkirim: bool, pesan: string} */
    public function jalankan(string $layanan, $pendaftaran, int $hariBerlalu = 0): array
    {
        return $this->kirim(
            $layanan,
            $pendaftaran,
            fn () => new PengingatPembayaranMail($layanan, $pendaftaran, $hariBerlalu),
            self::AKSI,
            self::AKSI_GAGAL,
            null, // dikirim sistem, bukan panitia
        );
    }
}
