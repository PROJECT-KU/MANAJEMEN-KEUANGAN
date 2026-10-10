<?php

namespace App\Actions\Pendaftaran;

use App\ClinikScopusTestimoni;
use App\PendaftaranDihapus;
use App\PendaftaranJejak;
use App\PendaftaranPengembalian;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\Auth;
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
                /*
                 * POTRETNYA DIAMBIL DULU, di dalam transaksi yang sama.
                 *
                 * Di luar transaksi, arsipnya bisa tertulis untuk penghapusan
                 * yang ternyata batal — dan arsip yang menyebut pendaftaran
                 * yang sebenarnya masih ada lebih menyesatkan daripada tidak
                 * ada arsip sama sekali.
                 */
                $this->arsipkan($layanan, $pendaftaran);

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

                // Pengikat pesanan lembaganya ikut dibuang; alasannya sama
                // persis seperti pesertanya — tidak ada kunci asing yang bisa
                // membersihkannya sendiri.
                \App\PemesananLembagaBaris::where('layanan', $layanan)
                    ->where('pendaftaran_id', (string) $pendaftaran->getKey())
                    ->delete();

                /*
                 * Termin yang menempel pada BARIS INI, alasannya sama lagi.
                 *
                 * Hanya yang berjenis 'pendaftaran' — rombongan yang dibayar
                 * atas satu nama. Termin milik pesanan lembaga TIDAK ikut
                 * terhapus walau salah satu pendaftarannya dibuang: uangnya
                 * memang diterima untuk pesanannya, bukan untuk orang itu,
                 * dan menghapusnya akan membuat sisa tagihan lembaganya
                 * melonjak tanpa ada yang mengerti kenapa.
                 */
                \App\PembayaranPendaftaran::milik(
                    \App\PembayaranPendaftaran::PENDAFTARAN,
                    (string) $pendaftaran->getKey()
                )->delete();

                /*
                 * Catatan pengembalian dananya ikut, alasannya sama seperti
                 * pesertanya: induknya ditunjuk pasangan (layanan,
                 * pendaftaran_id) tanpa kunci asing, jadi basis datanya tidak
                 * akan membersihkannya sendiri. Angkanya sudah masuk potret
                 * arsip di atas, jadi yang hilang cuma barisnya.
                 */
                PendaftaranPengembalian::milik($layanan, (string) $pendaftaran->getKey())->delete();

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
     * Potret lengkap pendaftaran ini sebelum ia dibuang.
     *
     * Yang dijaga bukan kenangan melainkan PEMBUKUAN: baris pembayaran atas
     * nama pendaftaran ini ikut dihapus beberapa baris di bawah, dan tanpa
     * potret ini angka itu keluar dari pembukuan tanpa menyisakan apa pun.
     * `uang_terhapus` menjumlahkan yang memang sudah diterima — berbeda dari
     * `total`, yang cuma tagihan, dan tagihan yang belum dibayar sepeser pun
     * tidak meninggalkan lubang.
     *
     * Jejaknya ikut dipotret, bukan cuma dihitung. Barisnya sendiri memang
     * tetap tinggal di pendaftaran_jejak, tetapi tidak ada lagi halaman yang
     * bisa membukanya begitu pendaftarannya hilang — jadi secara praktis ia
     * lenyap. Di dalam potret, riwayatnya masih bisa dibaca.
     */
    private function arsipkan(string $layanan, $pendaftaran): void
    {
        $id = (string) $pendaftaran->getKey();

        $pembayaran = \App\PembayaranPendaftaran::milik(
            \App\PembayaranPendaftaran::PENDAFTARAN, $id
        )->get();

        $jejak = PendaftaranJejak::milik($layanan, $id)->terurut()->get();
        $peserta = \App\PendaftaranPeserta::milik($layanan, $id)->get();
        $pengembalian = PendaftaranPengembalian::milik($layanan, $id)->get();

        $orang = Auth::user();

        PendaftaranDihapus::create([
            'layanan' => $layanan,
            'pendaftaran_id' => $id,
            'nomor' => (string) ($pendaftaran->id_transaksi ?? $pendaftaran->id_pemesanan ?? $id),
            'nama' => (string) ($pendaftaran->nama ?? $pendaftaran->nama_pemesan ?? ''),
            'email' => (string) ($pendaftaran->email ?? $pendaftaran->email_pemesan ?? ''),
            'status' => (string) ($pendaftaran->status ?? ''),
            'total' => (int) ($pendaftaran->total_pembayaran
                ?? $pendaftaran->total_keseluruhan_pembayaran ?? 0),
            'uang_terhapus' => (int) $pembayaran->sum('nominal'),
            'jumlah_pembayaran' => $pembayaran->count(),
            'jumlah_jejak' => $jejak->count(),
            'potret' => [
                'pendaftaran' => $pendaftaran->toArray(),
                'pembayaran' => $pembayaran->toArray(),
                'pengembalian' => $pengembalian->toArray(),
                'peserta' => $peserta->toArray(),
                'jejak' => $jejak->map(fn ($j) => [
                    'aksi' => $j->aksi,
                    'kalimat' => $j->kalimat,
                    'waktu' => optional($j->created_at)->toDateTimeString(),
                ])->all(),
            ],
            'oleh_id' => $orang?->getKey(),
            'oleh_nama' => $orang?->full_name ?? $orang?->username,
        ]);
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
