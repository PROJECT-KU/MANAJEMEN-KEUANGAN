<?php

namespace App\Actions\Pendaftaran;

use App\PendaftaranJejak;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mengirim surat status ke pendaftarnya, dan MENCATAT hasilnya.
 *
 * Dipisahkan dari UbahStatusPendaftaran karena kini ada tiga pemanggil:
 * perpindahan status, tombol "kirim ulang" di layar rincian, dan perintah
 * pengingat terjadwal. Ketiganya harus mengirim surat yang sama persis dan
 * mencatatnya dengan cara yang sama; disalin, ketiganya akan berbeda
 * perlahan.
 *
 * HASILNYA DICATAT DI JEJAK, berhasil maupun gagal. Sebelum ini kegagalan
 * surat hanya muncul di log peladen — yang tidak dibaca panitia dan, dengan
 * LOG_LEVEL yang ketat, kadang tidak tertulis sama sekali. Pendaftar yang
 * bilang "saya tidak terima email" tidak bisa dijawab dari layar mana pun.
 */
class KirimSuratStatus
{
    /**
     * @param  string|null  $olehNama  nama panitia; null berarti sistem
     * @return array{terkirim: bool, pesan: string}
     */
    public function jalankan(string $layanan, $pendaftaran, ?string $olehNama = null): array
    {
        $status = (string) $pendaftaran->status;
        $kelas = Pendaftaran::suratUntuk($layanan, $status);

        if ($kelas === null) {
            return ['terkirim' => false, 'pesan' => 'Status ini memang tidak mengirim surat.'];
        }

        $alamat = trim((string) ($pendaftaran->email ?? $pendaftaran->email_pemesan ?? ''));

        if ($alamat === '' || ! filter_var($alamat, FILTER_VALIDATE_EMAIL)) {
            /*
             * Alamat yang tidak sah DICATAT juga. Ini sebab kegagalan yang
             * paling sering dan paling mudah dibetulkan panitia — tetapi
             * hanya kalau ia tahu, dan sebelum ini ia tidak pernah tahu.
             */
            $this->catat($layanan, $pendaftaran, 'surat-gagal', $olehNama,
                $alamat === '' ? 'alamat emailnya kosong' : 'alamat emailnya tidak sah');

            Log::warning('Surat status pendaftaran tidak dikirim: alamatnya tidak sah.', [
                'layanan' => $layanan, 'id' => $pendaftaran->getKey(), 'alamat' => $alamat,
            ]);

            return [
                'terkirim' => false,
                'pesan' => $alamat === ''
                    ? 'Pendaftar ini belum punya alamat email, jadi suratnya tidak bisa dikirim.'
                    : 'Alamat emailnya tidak sah, jadi suratnya tidak bisa dikirim.',
            ];
        }

        try {
            Mail::to($alamat)->send($this->rakit($kelas, $layanan, $pendaftaran));
        } catch (\Throwable $e) {
            /*
             * Dibungkus try/catch: peladen surat yang bermasalah TIDAK boleh
             * menggagalkan perubahan status yang sudah tersimpan — panitia
             * akan mengulang, dan statusnya berpindah dua kali.
             */
            $this->catat($layanan, $pendaftaran, 'surat-gagal', $olehNama,
                \Illuminate\Support\Str::limit($e->getMessage(), 120));

            Log::error('Surat status pendaftaran gagal dikirim.', [
                'layanan' => $layanan, 'id' => $pendaftaran->getKey(), 'galat' => $e->getMessage(),
            ]);

            return ['terkirim' => false, 'pesan' => 'Suratnya gagal dikirim. Coba lagi beberapa saat.'];
        }

        $this->catat($layanan, $pendaftaran, 'surat', $olehNama, $alamat);

        return ['terkirim' => true, 'pesan' => 'Suratnya terkirim ke ' . $alamat . '.'];
    }

    /** Satu baris jejak untuk tiap percobaan kirim. */
    private function catat(string $layanan, $pendaftaran, string $aksi, ?string $olehNama, string $ke): void
    {
        PendaftaranJejak::create([
            'layanan' => $layanan,
            'pendaftaran_id' => (string) $pendaftaran->getKey(),
            'aksi' => $aksi,
            // Status yang disuratkan disimpan di `dari` supaya jejaknya bisa
            // menyebutkan surat MANA yang dikirim, bukan sekadar "ada surat".
            'dari' => (string) $pendaftaran->status,
            'ke' => $ke,
            'oleh_id' => Auth::id(),
            'oleh_nama' => $olehNama,
        ]);
    }

    /**
     * Merakit surat sesuai tanda tangan masing-masing.
     *
     * Surat umum cukup tahu layanan dan barisnya. Surat khusus yang lebih tua
     * menuntut model ANGKATAN bertipe subkelasnya sendiri
     * (CategoriesScopusCamp, CategoriesAnalisisBibliometrik) — menyerahkan
     * KategoriLayanan apa adanya melempar TypeError saat dirakit, bukan saat
     * dikirim.
     */
    private function rakit(string $kelas, string $layanan, $pendaftaran)
    {
        $namaAplikasi = 'Rumah Scopus Foundation';

        if ($kelas === \App\Mail\PerubahanStatusPendaftaranMail::class) {
            return new $kelas($layanan, $pendaftaran);
        }

        if ($layanan === 'scopus_kafe') {
            // Dua argumen pertama memang model yang sama; begitu pula di
            // pengendali lamanya.
            return new $kelas($pendaftaran, $pendaftaran, $namaAplikasi, true);
        }

        $angkatanModel = Pendaftaran::angkatanModel($layanan);
        $angkatan = $angkatanModel === null ? null : $angkatanModel::find($pendaftaran->kategori_id);

        if ($angkatan === null) {
            throw new \RuntimeException(
                'Angkatan pendaftaran ' . $pendaftaran->getKey() . ' tidak ditemukan, '
                . 'jadi suratnya tidak bisa dirakit.'
            );
        }

        return new $kelas($pendaftaran, $angkatan, $namaAplikasi);
    }
}
