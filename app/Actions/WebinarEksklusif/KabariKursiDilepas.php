<?php

namespace App\Actions\WebinarEksklusif;

use App\KategoriLayanan;
use App\Mail\WebinarEksklusifGagalBayarMail;
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
     * Menguras yang statusnya sudah kedaluwarsa tetapi kabarnya belum
     * berangkat.
     *
     * Jaring penampung untuk dua kebocoran yang keduanya nyata:
     *
     *   1. kabar yang terlewat batas per panggilan di
     *      lepaskanYangKedaluwarsa();
     *   2. kiriman yang gagal karena peladen suratnya sedang mati —
     *      penandanya dikembalikan, jadi barisnya muncul lagi di sini.
     *
     * Tanpa penguras ini keduanya hilang tanpa tanda: barisnya sudah keluar
     * dari bisaDilepas() begitu statusnya berpindah, jadi tidak ada satu pun
     * sapuan lain yang akan melihatnya lagi.
     *
     * @param  int  $batas  0 berarti tanpa batas
     * @return int banyaknya surat yang berangkat
     */
    public function sapu(int $batas = 0): int
    {
        $kueri = WebinarEksklusifPendaftaran::belumDikabariKedaluwarsa()
            // Yang paling lama menunggu didahulukan; kalau batasnya menggigit
            // lagi, yang tertinggal tetap yang paling baru.
            ->orderBy('kedaluwarsa_pada');

        if ($batas > 0) {
            $kueri->limit($batas);
        }

        $terkirim = 0;

        foreach ($kueri->get() as $p) {
            try {
                if ($this->jalankan($p)) {
                    $terkirim++;
                }
            } catch (\Throwable $e) {
                // Satu surat gagal tidak boleh menghentikan sisanya.
                Log::error('Kabar kursi dilepas gagal saat menyapu.', [
                    'nomor' => $p->id_transaksi,
                    'galat' => $e->getMessage(),
                ]);
            }
        }

        return $terkirim;
    }

    /**
     * @return bool benar kalau suratnya baru saja terkirim oleh pemanggil ini
     */
    public function jalankan(WebinarEksklusifPendaftaran $pendaftaran): bool
    {
        /*
         * 'expired' DAN 'cancel'. Keduanya berarti pendaftarannya berakhir
         * tanpa pembayaran, dan keduanya berhak dikabari.
         *
         * Dulu hanya 'expired', dengan alasan "kalimat batas waktunya lewat
         * akan salah untuk yang dibatalkan" — alasan yang benar, tetapi
         * jalan keluarnya salah: yang dibatalkan jadi tidak menerima apa-apa
         * sama sekali. Yang benar memilih KALIMATNYA, bukan mendiamkannya.
         */
        if (! in_array($pendaftaran->status, ['expired', 'cancel'], true)) {
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
        $segar = $pendaftaran->refresh();

        /*
         * Suratnya dipilih dari BAYAR_STATUS, bukan dari status pendaftaran.
         *
         * Status pendaftaran hanya mengenal 'expired' dan 'cancel', dan
         * jawaban "failed" dari gerbang — kartu ditolak, saldo kurang,
         * transaksi dihentikan bank — dipetakan ke 'expired'. Dibaca dari
         * sana, orang yang kartunya baru saja ditolak menerima surat yang
         * mengatakan ia TERLAMBAT: ia menyalahkan dirinya atas sesuatu yang
         * sebenarnya bisa diulang sekarang juga.
         *
         * bayar_status menyimpan jawaban gerbangnya apa adanya, jadi di
         * situlah bedanya masih utuh.
         */
        $gagalBayar = in_array(
            (string) $segar->bayar_status,
            ['failed', 'cancel', 'canceled', 'deny', 'denied'],
            true
        );

        $surat = $gagalBayar
            ? new WebinarEksklusifGagalBayarMail($segar, $sesi)
            : new WebinarEksklusifKedaluwarsaMail($segar, $sesi);

        try {
            Mail::to($pendaftaran->email)->send($surat);
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

        /*
         * WhatsApp-nya di sini juga, SESUDAH suratnya berhasil.
         *
         * Di sini, bukan di pemanggilnya: tiga jalur memanggil aksi ini,
         * dan dipasang di masing-masing, ketiganya akan berbeda perlahan.
         *
         * Memakai penanda yang SAMA dengan suratnya — aksi ini sudah
         * merebutnya di atas — jadi WhatsApp pun tidak terkirim dua kali.
         * Kegagalannya tidak mengubah hasil: suratnya sudah berangkat, dan
         * mengembalikan false di sini akan membuat pemanggilnya mengira
         * orangnya belum dikabari sama sekali.
         */
        try {
            $wa = app(KabariWhatsApp::class);

            $gagalBayar
                ? $wa->gagalBayar($segar, $sesi)
                : $wa->kursiDilepas($segar, $sesi);
        } catch (\Throwable $e) {
            Log::warning('Kabar WhatsApp penutup gagal.', [
                'nomor' => $pendaftaran->id_transaksi,
                'pesan' => $e->getMessage(),
            ]);
        }

        return true;
    }
}
