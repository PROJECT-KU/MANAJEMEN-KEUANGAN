<?php

namespace App\Actions\Pendaftaran;

use App\ClinikScopusTestimoni;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menghapus satu pendaftaran, layanan apa pun, beserta ekornya.
 *
 * Tiga hal ikut terhapus dan ketiganya pernah ditulis terpisah di lima
 * pengendali:
 *
 *   1. Kuota angkatannya DIKEMBALIKAN (Scopus Camp, Bibliometrik, Webinar) —
 *      dibatasi total kuotanya supaya tidak melebihi.
 *   2. Berkas bukti bayarnya dibuang dari cakram, memakai aturan folder
 *      tiap layanan. Terukur 72 dari 183 nilai sudah menunjuk berkas yang
 *      tidak ada, jadi ketidakhadirannya normal dan bukan kegagalan.
 *   3. Barisan yang menggantung padanya — untuk Clinik Scopus, testimoninya;
 *      untuk Webinar Eksklusif, nama peserta rombongannya (lewat kunci asing
 *      bercascade, jadi basis datanya yang mengurus).
 *
 * Yang HILANG kalau salah satunya terlewat tidak pernah terlihat sebagai
 * galat: kuota yang tidak dikembalikan membuat angkatan tampak penuh padahal
 * longgar, dan testimoni yatim membuat halaman publik galat saat merujuk
 * pemesanan yang sudah tidak ada.
 */
class HapusPendaftaran
{
    /**
     * @return array{berhasil:bool, pesan:string}
     */
    public function jalankan(string $layanan, string $id): array
    {
        $sumber = Pendaftaran::sumber($layanan);

        if ($sumber === null) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali.'];
        }

        $pendaftaran = Pendaftaran::temukan($layanan, $id);

        if ($pendaftaran === null) {
            return ['berhasil' => false, 'pesan' => 'Pendaftarannya tidak ditemukan.'];
        }

        $nomor = (string) ($pendaftaran->id_transaksi ?? $pendaftaran->id_pemesanan ?? $pendaftaran->getKey());
        $nama = (string) ($pendaftaran->nama ?? $pendaftaran->nama_pemesan ?? '');

        /*
         * Jalur berkasnya dihitung SEBELUM barisnya dihapus, dan dibuang
         * SESUDAH transaksinya berhasil. Membuang berkas di dalam transaksi
         * berarti berkasnya sudah hilang kalau transaksinya batal — dan
         * cakram tidak ikut di-rollback.
         */
        $berkas = $this->jalurBukti($layanan, $pendaftaran);

        try {
            DB::transaction(function () use ($layanan, $pendaftaran) {
                if (Pendaftaran::berangkatan($layanan)) {
                    $this->kembalikanKuota($layanan, $pendaftaran);
                }

                if ($layanan === 'clinik_scopus') {
                    // Testimoni menunjuk pemesanannya tanpa kunci asing, jadi
                    // basis datanya tidak akan menolak maupun membersihkannya
                    // sendiri — ia akan tinggal sebagai baris yatim.
                    ClinikScopusTestimoni::where('clinikscopus_pemesanan_id', $pendaftaran->getKey())->delete();
                }

                /*
                 * Peserta rombongannya menunjuk pendaftarannya lewat pasangan
                 * (layanan, pendaftaran_id) tanpa kunci asing — sasarannya
                 * lima tabel berbeda, dan MySQL tidak bisa menyatakan kendala
                 * seperti itu. Jadi basis datanya tidak akan
                 * membersihkannya sendiri, persis seperti testimoni di atas.
                 */
                \App\PendaftaranPeserta::milik($layanan, (string) $pendaftaran->getKey())->delete();

                $pendaftaran->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Pendaftaran gagal dihapus.', [
                'layanan' => $layanan, 'id' => $id, 'galat' => $e->getMessage(),
            ]);

            /*
             * Pesannya menyebut APA yang menghalangi, bukan nama constraint.
             * Galat kunci asing hanya menyebut nama constraint-nya, dan itu
             * tidak memberi tahu siapa pun apa yang harus dikerjakan.
             */
            return [
                'berhasil' => false,
                'pesan' => 'Pendaftaran ' . $nomor . ' tidak bisa dihapus karena masih '
                    . 'ada data lain yang menunjuknya. Hubungi pengembang dengan '
                    . 'menyebut nomor itu.',
            ];
        }

        if ($berkas !== null && is_file($berkas)) {
            // @unlink: berkas yang sudah tidak ada bukan kegagalan, dan
            // barisnya memang sudah terhapus — tidak ada yang bisa diurungkan.
            @unlink($berkas);
        }

        return [
            'berhasil' => true,
            'pesan' => 'Pendaftaran ' . $nomor . ($nama !== '' ? ' (' . $nama . ')' : '') . ' dihapus.',
        ];
    }

    /**
     * Kuota angkatannya dikembalikan sejumlah pendaftarnya.
     *
     * Dibatasi total kuotanya: tanpa batas itu, menghapus pendaftaran yang
     * dulu dibuat saat kuotanya lebih besar membuat sisa melebihi total, dan
     * angka sisanya jadi tidak berarti.
     */
    private function kembalikanKuota(string $layanan, $pendaftaran): void
    {
        $angkatanModel = Pendaftaran::angkatanModel($layanan);

        if ($angkatanModel === null) {
            return;
        }

        $angkatan = $angkatanModel::whereKey($pendaftaran->kategori_id)->lockForUpdate()->first();

        if ($angkatan === null || $angkatan->total_kuota === null) {
            return;
        }

        $angkatan->forceFill([
            'sisa_kuota' => (string) min(
                (int) $angkatan->total_kuota,
                (int) $angkatan->sisa_kuota + (int) $pendaftaran->jumlah_pendaftar
            ),
        ])->save();
    }

    /**
     * Jalur berkas bukti bayarnya di cakram, atau null kalau tidak ada.
     *
     * Aturannya folder layanan + basename(), sama dengan yang dipakai layar
     * menampilkannya — nilai tersimpannya tidak seragam, sebagian sudah
     * memuat nama folder dan sebagian hanya nama berkasnya.
     */
    private function jalurBukti(string $layanan, $pendaftaran): ?string
    {
        $folder = Pendaftaran::sumber($layanan)['bukti_folder'] ?? null;
        $nilai = trim((string) ($pendaftaran->gambar ?? ''));

        if ($folder === null || $nilai === '') {
            return null;
        }

        return public_path($folder . '/' . basename($nilai));
    }
}
