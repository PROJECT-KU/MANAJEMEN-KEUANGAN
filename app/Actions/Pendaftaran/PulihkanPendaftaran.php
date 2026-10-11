<?php

namespace App\Actions\Pendaftaran;

use App\ClinikScopusTestimoni;
use App\PembayaranPendaftaran;
use App\PemesananLembagaBaris;
use App\PendaftaranDihapus;
use App\PendaftaranJejak;
use App\PendaftaranPengembalian;
use App\PendaftaranPeserta;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mengembalikan satu pendaftaran dari catatan penghapusan.
 *
 * Kebalikan dari HapusPendaftaran, dan sengaja dibaca berdampingan
 * dengannya: tiap hal yang dibuang di sana harus ada pasangannya di sini,
 * kalau tidak yang kembali cuma sebagian dan tidak ada yang tahu bagian
 * mana yang hilang.
 *
 * Sumbernya POTRET, bukan tabel aslinya — barisnya memang sudah tidak ada.
 * Potretnya diambil di dalam transaksi penghapusan, jadi ia memotret
 * keadaan tepat sebelum baris itu hilang.
 *
 * Yang TIDAK ikut kembali, dan ini disebut di layarnya:
 *
 *   - Jejak perubahan lamanya memang tidak pernah dihapus, jadi ia muncul
 *     kembali sendiri begitu pendaftarannya ada lagi.
 *   - Bukti bayarnya kembali HANYA kalau berkasnya berhasil diparkir saat
 *     dihapus. Penghapusan dari sebelum parkiran ada membuang berkasnya,
 *     dan tidak ada yang bisa mengembalikannya.
 */
class PulihkanPendaftaran
{
    /** Kursi yang diambil melebihi sisa kuota angkatannya; 0 kalau cukup. */
    private int $kuotaKurang = 0;

    /**
     * @return array{berhasil:bool, pesan:string, layanan?:string, id?:string}
     */
    public function jalankan(string $arsipId): array
    {
        $arsip = PendaftaranDihapus::find($arsipId);

        if ($arsip === null) {
            return ['berhasil' => false, 'pesan' => 'Catatan penghapusannya tidak ditemukan.'];
        }

        if ($arsip->dipulihkan_at !== null) {
            return [
                'berhasil' => false,
                'pesan' => 'Pendaftaran ' . $arsip->nomor . ' sudah dikembalikan sebelumnya.',
            ];
        }

        $sumber = Pendaftaran::sumber($arsip->layanan);

        if ($sumber === null) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali lagi.'];
        }

        $potret = $arsip->potret ?? [];
        $baris = $potret['pendaftaran'] ?? null;

        if (! is_array($baris) || $baris === []) {
            return [
                'berhasil' => false,
                'pesan' => 'Catatan ini tidak memuat potret barisnya, jadi tidak ada '
                    . 'yang bisa dikembalikan.',
            ];
        }

        /*
         * Diperiksa DULU apakah barisnya sudah ada lagi.
         *
         * Kuncinya UUID, jadi tabrakan hampir mustahil — tetapi pendaftaran
         * yang sudah dibuat ulang dengan tangan sesudah dihapus memang
         * mungkin, dan menimpanya diam-diam akan menghapus pekerjaan orang
         * lain tanpa ada yang tahu.
         */
        $model = $sumber['model'];

        if ($model::whereKey($arsip->pendaftaran_id)->exists()) {
            return [
                'berhasil' => false,
                'pesan' => 'Pendaftaran ' . $arsip->nomor . ' sudah ada lagi di daftar, '
                    . 'jadi tidak dikembalikan supaya tidak menimpa yang sekarang.',
            ];
        }

        try {
            DB::transaction(function () use ($arsip, $sumber, $baris, $potret, $model) {
                $model::query()->insert($this->bersihkan($baris, $model));

                $this->kembalikanAnak($arsip, $potret);

                if (Pendaftaran::berangkatan($arsip->layanan)) {
                    $this->kuotaKurang = $this->ambilKuota($arsip->layanan, $baris);
                }

                $orang = Auth::user();

                /*
                 * Jejaknya dicatat ATAS NAMA pendaftarannya, bukan hanya di
                 * arsipnya: yang membuka rinciannya nanti perlu tahu baris
                 * ini pernah hilang dan siapa yang mengembalikannya.
                 */
                PendaftaranJejak::create([
                    'layanan' => $arsip->layanan,
                    'pendaftaran_id' => (string) $arsip->pendaftaran_id,
                    'aksi' => 'pulih',
                    'ringkasan' => 'Dikembalikan dari catatan penghapusan.',
                    'oleh_id' => Auth::id(),
                    'oleh_nama' => $orang?->full_name ?? $orang?->username,
                ]);

                $arsip->forceFill([
                    'dipulihkan_at' => now(),
                    'dipulihkan_oleh' => $orang?->full_name ?? $orang?->username,
                ])->save();
            });
        } catch (\Throwable $e) {
            Log::error('Pendaftaran gagal dikembalikan.', [
                'arsip' => $arsipId, 'galat' => $e->getMessage(),
            ]);

            return [
                'berhasil' => false,
                'pesan' => 'Pendaftaran ' . $arsip->nomor . ' tidak bisa dikembalikan. '
                    . 'Hubungi pengembang dengan menyebut nomor itu.',
            ];
        }

        $buktiKembali = $this->kembalikanBukti($arsip);

        return [
            'berhasil' => true,
            'layanan' => $arsip->layanan,
            'id' => (string) $arsip->pendaftaran_id,
            'pesan' => 'Pendaftaran ' . $arsip->nomor . ' dikembalikan.'
                . ($buktiKembali ? '' : ' Bukti bayarnya tidak ikut kembali — berkasnya '
                    . 'tidak ada lagi di server.')
                /*
                 * Disebut, bukan didiamkan. Kuotanya terlanjur dijepit di nol,
                 * jadi layar angkatan akan menampilkan "sisa 0" yang terlihat
                 * pas — dan tidak ada tempat lain yang memberi tahu bahwa
                 * kursinya sebenarnya kelebihan.
                 */
                . ($this->kuotaKurang > 0
                    ? ' Angkatannya kelebihan ' . $this->kuotaKurang . ' kursi; '
                        . 'periksa kuotanya.'
                    : ''),
        ];
    }

    /**
     * Baris anak yang ikut dihapus, dimasukkan kembali apa adanya.
     *
     * insert() massal, bukan create() satu per satu: potretnya sudah memuat
     * id dan waktu aslinya, dan create() akan menerbitkan id baru sehingga
     * rujukan di tempat lain menunjuk baris yang sudah tidak ada.
     */
    private function kembalikanAnak(PendaftaranDihapus $arsip, array $potret): void
    {
        $peta = [
            'peserta' => PendaftaranPeserta::class,
            'pembayaran' => PembayaranPendaftaran::class,
            'pengembalian' => PendaftaranPengembalian::class,
            'testimoni' => ClinikScopusTestimoni::class,
            'lembaga_baris' => PemesananLembagaBaris::class,
        ];

        foreach ($peta as $kunci => $kelas) {
            $isi = $potret[$kunci] ?? [];

            if (! is_array($isi) || $isi === []) {
                continue;
            }

            $bersih = [];

            foreach ($isi as $satu) {
                if (is_array($satu) && $satu !== []) {
                    $bersih[] = $this->bersihkan($satu, $kelas);
                }
            }

            if ($bersih !== []) {
                $kelas::query()->insert($bersih);
            }
        }
    }

    /**
     * Membuang kunci yang BUKAN kolom tabelnya.
     *
     * toArray() ikut membawa accessor dan relasi yang sempat dimuat — nilai
     * yang tidak punya kolom. Diserahkan apa adanya ke insert(), MySQL
     * menolak seluruh barisnya dengan pesan "Unknown column", dan
     * pemulihannya batal karena hal yang sama sekali bukan datanya.
     */
    private function bersihkan(array $baris, string $kelas): array
    {
        $contoh = new $kelas;
        $kolom = \Illuminate\Support\Facades\Schema::getColumnListing($contoh->getTable());

        $bersih = array_intersect_key($baris, array_flip($kolom));

        return array_map([$this, 'tanggalMysql'], $bersih);
    }

    /**
     * Tanggal ISO-8601 dikembalikan ke bentuk yang dimengerti MySQL.
     *
     * toArray() menulis tanggal sebagai "2026-10-11T14:51:47.000000Z" — UTC,
     * berhuruf T dan Z. insert() massal TIDAK melewati pengecoran Eloquent,
     * jadi untaian itu sampai apa adanya ke MySQL dan ditolak:
     *
     *     SQLSTATE[22007]: Incorrect datetime value
     *
     * Seluruh pemulihannya batal karena bentuk tulisan tanggal, bukan karena
     * datanya. Zonanya ikut dikembalikan ke zona aplikasi; dibiarkan UTC,
     * tiap baris yang dipulihkan mundur tujuh jam tanpa ada yang menyadarinya.
     */
    private function tanggalMysql($nilai)
    {
        if (! is_string($nilai)
            || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $nilai) !== 1) {
            return $nilai;
        }

        try {
            return \Illuminate\Support\Carbon::parse($nilai)
                ->setTimezone(config('app.timezone') ?: 'UTC')
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return $nilai;
        }
    }

    /**
     * Kuota angkatannya diambil lagi sejumlah pendaftarnya.
     *
     * Kebalikan persis dari kembalikanKuota() milik penghapusnya. Dibatasi
     * nol di bawah: angkatan yang kuotanya sempat dikecilkan sesudah
     * penghapusan bisa membuat hasilnya minus, dan sisa kuota minus tidak
     * berarti apa-apa di layar mana pun.
     */
    private function ambilKuota(string $layanan, array $baris): int
    {
        $angkatanModel = Pendaftaran::angkatanModel($layanan);

        if ($angkatanModel === null) {
            return 0;
        }

        $angkatan = $angkatanModel::whereKey($baris['kategori_id'] ?? null)
            ->lockForUpdate()->first();

        if ($angkatan === null || $angkatan->total_kuota === null) {
            return 0;
        }

        $perlu = (int) ($baris['jumlah_pendaftar'] ?? 1);
        $sisa = (int) $angkatan->sisa_kuota;

        $angkatan->forceFill(['sisa_kuota' => (string) max(0, $sisa - $perlu)])->save();

        /*
         * Berapa kursi yang DIAMBIL MELEBIHI yang tersedia.
         *
         * Sisanya dijepit di nol — sisa kuota minus tidak berarti apa-apa di
         * layar mana pun — tetapi penjepitan itu menyembunyikan kelebihan
         * pesanannya. Angkatan yang sudah penuh lalu dipulihi dua kursi
         * tampak pas-pasan, padahal dua orang tidak punya tempat. Dikembalikan
         * supaya pemanggilnya bisa mengatakannya.
         */
        return max(0, $perlu - $sisa);
    }

    /**
     * Berkas bukti bayarnya dipindahkan kembali dari parkiran.
     *
     * Di LUAR transaksi, sama seperti saat memarkirnya: cakram tidak ikut
     * di-rollback, jadi memindahkannya di dalam transaksi berarti berkasnya
     * sudah pindah walau barisnya batal kembali.
     */
    private function kembalikanBukti(PendaftaranDihapus $arsip): bool
    {
        $parkir = trim((string) $arsip->bukti_parkir);
        $folder = Pendaftaran::sumber($arsip->layanan)['bukti_folder'] ?? null;

        if ($parkir === '' || $folder === null) {
            return false;
        }

        $asal = public_path($folder . '/' . HapusPendaftaran::FOLDER_PARKIR . '/' . basename($parkir));

        if (! is_file($asal)) {
            return false;
        }

        $nilai = trim((string) ($arsip->potret['pendaftaran']['gambar'] ?? ''));

        if ($nilai === '') {
            return false;
        }

        $tujuan = public_path($folder . '/' . basename($nilai));

        if (@rename($asal, $tujuan)) {
            $arsip->forceFill(['bukti_parkir' => null])->save();

            return true;
        }

        return false;
    }
}
