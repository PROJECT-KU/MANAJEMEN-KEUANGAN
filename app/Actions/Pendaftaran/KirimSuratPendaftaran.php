<?php

namespace App\Actions\Pendaftaran;

use App\PendaftaranJejak;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Yang dikerjakan SAMA oleh setiap surat pendaftaran, apa pun isinya.
 *
 * Ada dua pengirim sekarang — surat status dan surat pengingat pembayaran —
 * dan keduanya harus: memeriksa alamatnya dulu, membungkus pengirimannya
 * supaya peladen surat yang bermasalah tidak menggagalkan pekerjaan yang
 * sudah tersimpan, lalu MENCATAT hasilnya di jejak baik berhasil maupun
 * gagal. Disalin, keduanya akan berbeda perlahan: yang satu dapat perbaikan,
 * yang satunya tertinggal, dan selisihnya baru ketahuan dari pendaftar yang
 * bilang suratnya tidak sampai.
 *
 * Yang dibedakan anaknya hanya tiga: surat apa yang dirakit, dan nama aksi
 * jejaknya saat berhasil dan saat gagal.
 */
abstract class KirimSuratPendaftaran
{
    /**
     * Mengirim satu surat dan mencatat hasilnya.
     *
     * @param  callable():Mailable  $rakit  dipanggil hanya kalau alamatnya sah
     * @return array{terkirim: bool, pesan: string}
     */
    protected function kirim(
        string $layanan,
        $pendaftaran,
        callable $rakit,
        string $aksiBerhasil,
        string $aksiGagal,
        ?string $olehNama = null,
    ): array {
        $alamat = $this->alamat($pendaftaran);

        if ($alamat === null) {
            /*
             * Alamat yang tidak sah DICATAT juga. Ini sebab kegagalan yang
             * paling sering dan paling mudah dibetulkan panitia — tetapi
             * hanya kalau ia tahu, dan sebelum ini ia tidak pernah tahu.
             */
            $mentah = trim((string) ($pendaftaran->email ?? $pendaftaran->email_pemesan ?? ''));
            $sebab = $mentah === '' ? 'alamat emailnya kosong' : 'alamat emailnya tidak sah';

            $this->catat($layanan, $pendaftaran, $aksiGagal, $olehNama, $sebab);

            Log::warning('Surat pendaftaran tidak dikirim: alamatnya tidak sah.', [
                'layanan' => $layanan, 'id' => $pendaftaran->getKey(), 'alamat' => $mentah,
            ]);

            return [
                'terkirim' => false,
                'pesan' => $mentah === ''
                    ? 'Pendaftar ini belum punya alamat email, jadi suratnya tidak bisa dikirim.'
                    : 'Alamat emailnya tidak sah, jadi suratnya tidak bisa dikirim.',
            ];
        }

        try {
            Mail::to($alamat)->send($rakit());
        } catch (\Throwable $e) {
            /*
             * Dibungkus try/catch: peladen surat yang bermasalah TIDAK boleh
             * menggagalkan perubahan yang sudah tersimpan — panitia akan
             * mengulang, dan statusnya berpindah dua kali.
             */
            $this->catat($layanan, $pendaftaran, $aksiGagal, $olehNama, Str::limit($e->getMessage(), 120));

            Log::error('Surat pendaftaran gagal dikirim.', [
                'layanan' => $layanan, 'id' => $pendaftaran->getKey(), 'galat' => $e->getMessage(),
            ]);

            return ['terkirim' => false, 'pesan' => 'Suratnya gagal dikirim. Coba lagi beberapa saat.'];
        }

        $this->catat($layanan, $pendaftaran, $aksiBerhasil, $olehNama, $alamat);

        return ['terkirim' => true, 'pesan' => 'Suratnya terkirim ke ' . $alamat . '.'];
    }

    /** Alamat email pendaftar yang sah, atau null. */
    protected function alamat($pendaftaran): ?string
    {
        // Dua nama kolom: empat layanan memakai `email`, Clinik Scopus
        // `email_pemesan`.
        $alamat = trim((string) ($pendaftaran->email ?? $pendaftaran->email_pemesan ?? ''));

        return filter_var($alamat, FILTER_VALIDATE_EMAIL) ? $alamat : null;
    }

    /** Satu baris jejak untuk tiap percobaan kirim. */
    protected function catat(string $layanan, $pendaftaran, string $aksi, ?string $olehNama, string $ke): void
    {
        PendaftaranJejak::create([
            'layanan' => $layanan,
            'pendaftaran_id' => (string) $pendaftaran->getKey(),
            'aksi' => $aksi,
            // Status saat suratnya dikirim disimpan di `dari` supaya jejaknya
            // bisa menyebutkan surat MANA yang dikirim, bukan sekadar
            // "ada surat".
            'dari' => (string) $pendaftaran->status,
            'ke' => $ke,
            'oleh_id' => Auth::id(),
            'oleh_nama' => $olehNama,
        ]);
    }
}
