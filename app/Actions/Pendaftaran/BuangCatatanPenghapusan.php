<?php

namespace App\Actions\Pendaftaran;

use App\PendaftaranDihapus;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\Log;

/**
 * Membuang satu catatan penghapusan untuk selamanya.
 *
 * INI yang benar-benar tidak bisa diurungkan — bukan tombol Hapus di layar
 * pendaftaran, yang sejak ada tombol Kembalikan cuma memindahkan barisnya
 * ke sini.
 *
 * Gunanya dua: membuang catatan yang memang sudah tidak relevan, dan
 * membebaskan bukti bayar yang menunggu di parkiran. Tanpa jalan ini
 * arsipnya hanya bertambah selamanya, dan berkas bukti pendaftaran yang
 * dihapus bertahun-tahun lalu tetap duduk di folder yang dilayani web.
 */
class BuangCatatanPenghapusan
{
    /**
     * @return array{berhasil:bool, pesan:string}
     */
    public function jalankan(string $arsipId): array
    {
        $arsip = PendaftaranDihapus::find($arsipId);

        if ($arsip === null) {
            return ['berhasil' => false, 'pesan' => 'Catatan penghapusannya tidak ditemukan.'];
        }

        $nomor = (string) $arsip->nomor;

        /*
         * Berkasnya dibuang DULU, sebelum barisnya. Barisnya dibuang lebih
         * dulu lalu penghapusan berkasnya gagal, tidak ada lagi yang
         * menyimpan jalur parkirannya — berkasnya jadi yatim selamanya dan
         * tidak ada cara menemukannya kembali selain membaca folder dengan
         * tangan.
         */
        $this->buangParkiran($arsip);

        try {
            $arsip->delete();
        } catch (\Throwable $e) {
            Log::error('Catatan penghapusan gagal dibuang.', [
                'arsip' => $arsipId, 'galat' => $e->getMessage(),
            ]);

            return [
                'berhasil' => false,
                'pesan' => 'Catatan ' . $nomor . ' tidak bisa dibuang.',
            ];
        }

        return [
            'berhasil' => true,
            'pesan' => 'Catatan penghapusan ' . $nomor . ' dibuang permanen.',
        ];
    }

    /** Bukti bayar yang menunggu di parkiran ikut dibuang. */
    public function buangParkiran(PendaftaranDihapus $arsip): bool
    {
        $parkir = trim((string) $arsip->bukti_parkir);
        $folder = Pendaftaran::sumber($arsip->layanan)['bukti_folder'] ?? null;

        if ($parkir === '' || $folder === null) {
            return false;
        }

        $jalur = public_path(
            $folder . '/' . HapusPendaftaran::FOLDER_PARKIR . '/' . basename($parkir)
        );

        // @unlink: berkas yang sudah tidak ada bukan kegagalan.
        $hasil = is_file($jalur) && @unlink($jalur);

        $arsip->forceFill(['bukti_parkir' => null])->save();

        return $hasil;
    }
}
