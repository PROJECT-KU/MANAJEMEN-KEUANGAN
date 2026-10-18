<?php

namespace App\Actions\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifKedaluwarsaMail;
use App\WebinarEksklusifPendaftaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mengabari satu orang bahwa batas bayarnya lewat dan kursinya dilepas.
 *
 * SATU pintu untuk tiga jalur yang sama-sama bisa mengedaluwarsakan satu
 * pendaftaran, kadang dalam hitungan detik yang sama:
 *
 *   1. perintah terjadwal yang menyapu berkala,
 *   2. halaman sesi yang melepas kursi secara oportunis saat dibuka orang
 *      lain — jadi pemicunya orang ketiga, bukan yang punya pendaftaran,
 *   3. pemberitahuan "expired" dari DOKU.
 *
 * Ditulis terpisah di tiga tempat, ketiganya akan berbeda perlahan, dan
 * yang menanggung orangnya: tiga surat berbunyi sama pada menit yang sama.
 */
class KabariKursiDilepas
{
    /**
     * @return bool benar kalau suratnya baru saja terkirim oleh pemanggil ini
     */
    public function jalankan(WebinarEksklusifPendaftaran $pendaftaran): bool
    {
        /*
         * HANYA 'expired'. Yang dibatalkan ('cancel') tidak menerima surat
         * ini: pembatalan datang dari keputusan — orangnya sendiri atau
         * panitia — dan kalimat "batas waktunya lewat" akan salah di situ.
         */
        if ($pendaftaran->status !== 'expired') {
            return false;
        }

        if (trim((string) $pendaftaran->email) === '') {
            return false;
        }

        /*
         * PENANDANYA DIREBUT DULU, di dalam transaksi berkunci baris.
         *
         * Diperiksa di luar lalu dikirim, dua proses yang berjalan
         * bersamaan sama-sama melihat penanda kosong, sama-sama lolos, dan
         * sama-sama mengirim. Pemeriksaan dan penulisannya harus satu
         * langkah yang tidak bisa disela.
         */
        $direbut = DB::transaction(function () use ($pendaftaran) {
            $segar = WebinarEksklusifPendaftaran::whereKey($pendaftaran->getKey())
                ->lockForUpdate()
                ->first();

            if ($segar === null || $segar->kedaluwarsa_dikabari_pada !== null) {
                return false;
            }

            $segar->forceFill(['kedaluwarsa_dikabari_pada' => now()])->save();

            return true;
        });

        if (! $direbut) {
            return false;
        }

        $sesi = KategoriLayanan::whereKey($pendaftaran->kategori_id)->first();

        try {
            Mail::to($pendaftaran->email)->send(
                new WebinarEksklusifKedaluwarsaMail($pendaftaran->refresh(), $sesi)
            );
        } catch (\Throwable $e) {
            /*
             * PENANDANYA DIKEMBALIKAN saat pengirimannya gagal.
             *
             * Dibiarkan tertulis, peladen surat yang sedang mati berarti
             * orang ini TIDAK AKAN PERNAH dikabari — sapuan berikutnya
             * melihat penandanya terisi dan melewatinya. Padahal justru
             * tidak adanya kabar yang sedang kita perbaiki di sini.
             *
             * Risiko yang diterima sadar: proses yang mati persis di antara
             * gagal-kirim dan baris ini meninggalkan penanda yang terlanjur
             * tertulis. Satu kabar yang hilang karena kebetulan langka lebih
             * ringan daripada semua kabar hilang tiap kali SMTP-nya batuk.
             */
            WebinarEksklusifPendaftaran::whereKey($pendaftaran->getKey())
                ->update(['kedaluwarsa_dikabari_pada' => null]);

            Log::error('Kabar kursi dilepas gagal dikirim.', [
                'nomor' => $pendaftaran->id_transaksi,
                'galat' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
