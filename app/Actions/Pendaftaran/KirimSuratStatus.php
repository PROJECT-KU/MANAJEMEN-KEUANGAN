<?php

namespace App\Actions\Pendaftaran;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;

/**
 * Mengirim surat status ke pendaftarnya, dan MENCATAT hasilnya.
 *
 * Dipisahkan dari UbahStatusPendaftaran karena ada dua pemanggil:
 * perpindahan status dan tombol "kirim ulang" di layar rincian. Keduanya
 * harus mengirim surat yang sama persis dan mencatatnya dengan cara yang
 * sama.
 *
 * Pemeriksaan alamat, pembungkus galat, dan penulisan jejaknya ada di
 * KirimSuratPendaftaran — dipakai bersama surat pengingat pembayaran.
 *
 * HASILNYA DICATAT DI JEJAK, berhasil maupun gagal. Sebelum ini kegagalan
 * surat hanya muncul di log peladen — yang tidak dibaca panitia dan, dengan
 * LOG_LEVEL yang ketat, kadang tidak tertulis sama sekali. Pendaftar yang
 * bilang "saya tidak terima email" tidak bisa dijawab dari layar mana pun.
 */
class KirimSuratStatus extends KirimSuratPendaftaran
{
    /**
     * @param  string|null  $olehNama  nama panitia; null berarti sistem
     * @return array{terkirim: bool, pesan: string}
     */
    public function jalankan(string $layanan, $pendaftaran, ?string $olehNama = null): array
    {
        $kelas = Pendaftaran::suratUntuk($layanan, (string) $pendaftaran->status);

        if ($kelas === null) {
            return ['terkirim' => false, 'pesan' => 'Status ini memang tidak mengirim surat.'];
        }

        return $this->kirim(
            $layanan,
            $pendaftaran,
            fn () => $this->rakit($kelas, $layanan, $pendaftaran),
            'surat',
            'surat-gagal',
            $olehNama,
        );
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
