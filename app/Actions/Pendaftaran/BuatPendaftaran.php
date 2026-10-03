<?php

namespace App\Actions\Pendaftaran;

use App\KategoriLayanan;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * Mendaftarkan seseorang dari sisi panitia, untuk layanan mana pun.
 *
 * Sebelum ini TIDAK ADA jalurnya sama sekali: yang mendaftar lewat WhatsApp,
 * datang langsung, atau membayar di tempat tidak bisa dimasukkan ke sistem,
 * sehingga daftar pendaftar tidak pernah lengkap dan kuota angkatan tidak
 * mencerminkan kursi yang sebenarnya terpakai.
 *
 * Barisnya dibuat SERUPA dengan yang datang dari jalur publik — nomor
 * mengikuti pola layanan yang sama, status awal yang sama, kode unik dalam
 * rentang yang sama — supaya tidak ada dua jenis pendaftaran yang harus
 * diperlakukan berbeda di layar mana pun.
 *
 * Yang DIKERJAKAN sendiri oleh sistem, bukan diketik panitia: nomor
 * pendaftaran, status awal, kode unik, dan total bayar untuk layanan
 * berangkatan. Itu inti "minim isian" — yang tersisa diketik hanya nama,
 * email, nomor telepon, dan pilihan angkatannya.
 */
class BuatPendaftaran
{
    /**
     * @param  array<string, mixed>  $isian
     * @return array{berhasil:bool, pesan:string, model:mixed}
     */
    public function jalankan(string $layanan, array $isian, ?string $olehSiapa = null): array
    {
        $sumber = Pendaftaran::sumber($layanan);

        if ($sumber === null) {
            return ['berhasil' => false, 'pesan' => 'Layanan itu tidak dikenali.', 'model' => null];
        }

        $jumlah = max(1, (int) ($isian['jumlah'] ?? 1));
        $angkatan = null;
        $galat = null;
        $dibuat = null;

        DB::transaction(function () use (
            $layanan, $sumber, $isian, $jumlah, $olehSiapa, &$angkatan, &$galat, &$dibuat
        ) {
            /*
             * Angkatannya DIKUNCI sebelum kuotanya dibaca.
             *
             * Tanpa lockForUpdate, dua panitia yang mendaftarkan orang pada
             * detik yang sama sama-sama membaca sisa kuota yang belum
             * dikurangi, dan keduanya lolos walau kursinya tinggal satu.
             */
            if (Pendaftaran::berangkatan($layanan)) {
                $angkatan = KategoriLayanan::whereKey($isian['kategori_id'] ?? null)
                    ->lockForUpdate()->first();

                if ($angkatan === null) {
                    $galat = 'Angkatan yang dipilih tidak ditemukan.';

                    return;
                }

                if ($angkatan->layanan !== $layanan) {
                    // Angkatan milik layanan lain membuat kuota keduanya salah
                    // tanpa ada yang menolak.
                    $galat = 'Angkatan itu bukan milik layanan yang dipilih.';

                    return;
                }

                if ($angkatan->total_kuota !== null && (int) $angkatan->sisa_kuota < $jumlah) {
                    $galat = 'Kursinya tidak cukup — tersisa ' . (int) $angkatan->sisa_kuota
                        . ' dari ' . (int) $angkatan->total_kuota . '.';

                    return;
                }
            }

            $subtotal = $this->hitungTotal($layanan, $angkatan, $jumlah, $isian);

            /*
             * Potongan khusus, DI ATAS potongan bawaan angkatannya.
             *
             * Angkatan sudah punya diskonnya sendiri dan itu sudah terhitung
             * di `total_biaya`; yang ini untuk hal yang tidak bisa diketahui
             * angkatan — peserta yang disponsori, harga mitra, atau
             * kesepakatan di tempat. Dibatasi subtotalnya: potongan yang
             * melebihi tagihan menghasilkan total negatif, dan nominal
             * transfer negatif tidak berarti apa-apa.
             */
            $potongan = min($subtotal, max(0, (int) preg_replace('/\D+/', '', (string) ($isian['potongan'] ?? 0))));
            $total = $subtotal - $potongan;

            $kodeUnik = $this->kodeUnikBebas($layanan, $isian['kategori_id'] ?? null, $total);

            $baris = $this->rakitKolom(
                $layanan, $sumber, $isian, $jumlah, $total, $kodeUnik, $olehSiapa, $potongan
            );

            $model = $sumber['model'];
            $dibuat = $model::create($baris);

            if ($angkatan !== null && $angkatan->total_kuota !== null) {
                $angkatan->forceFill([
                    'sisa_kuota' => (string) max(0, (int) $angkatan->sisa_kuota - $jumlah),
                ])->save();
            }
        });

        if ($galat !== null) {
            return ['berhasil' => false, 'pesan' => $galat, 'model' => null];
        }

        return [
            'berhasil' => true,
            'model' => $dibuat,
            'pesan' => 'Pendaftaran ' . $dibuat->{Pendaftaran::kolomNomor($layanan)}
                . ' atas nama ' . ($isian['nama'] ?? '-') . ' tersimpan.',
        ];
    }

    /**
     * Total bayar: dari angkatannya kalau ada, dari ketikan panitia kalau tidak.
     *
     * Scopus Kafe dan Clinik Scopus tidak berangkatan — tarifnya per sesi dan
     * berbeda-beda — jadi nominalnya memang harus diketik. Tiga layanan
     * lainnya mengambilnya dari angkatan, dan itu yang membuat borangnya
     * minim isian: panitia memilih angkatan, harganya ikut.
     */
    private function hitungTotal(string $layanan, ?KategoriLayanan $angkatan, int $jumlah, array $isian): int
    {
        if ($angkatan === null) {
            return max(0, (int) preg_replace('/\D+/', '', (string) ($isian['total'] ?? 0)));
        }

        // total_biaya sudah memperhitungkan diskon angkatannya; biaya adalah
        // harga sebelum potongan. Yang dipakai yang sudah berdiskon, sebab
        // itulah yang ditagihkan ke orangnya.
        $satuan = (int) ($angkatan->total_biaya ?: $angkatan->biaya);

        return $satuan * $jumlah;
    }

    /**
     * Kode unik yang membuat TOTAL-nya belum terpakai.
     *
     * Gunanya mencocokkan mutasi rekening: dua orang yang membayar nominal
     * yang sama persis tidak bisa dibedakan. Jadi yang harus unik bukan
     * kodenya melainkan HASIL PENJUMLAHANNYA — dan itu yang diperiksa.
     *
     * Dibandingkan hanya terhadap pendaftaran yang masih menunggu: yang sudah
     * lunas uangnya sudah masuk dan tidak perlu dicocokkan lagi, dan
     * membandingkan terhadap seluruh riwayat akan kehabisan kode.
     */
    private function kodeUnikBebas(string $layanan, $kategoriId, int $total): int
    {
        [$min, $maks] = Pendaftaran::rentangKodeUnik($layanan);

        $terpakai = Pendaftaran::kueri()
            ->where('layanan', $layanan)
            ->when($kategoriId, fn ($q) => $q->where('angkatan_id', $kategoriId))
            ->whereIn('status', Pendaftaran::KEADAAN['menunggu']['nilai'])
            ->selectRaw('CAST(total AS UNSIGNED) + CAST(kode_unik AS UNSIGNED) as jumlahnya')
            ->pluck('jumlahnya')
            ->map(fn ($x) => (int) $x)
            ->all();

        $terpakai = array_flip($terpakai);

        // Empat puluh lemparan acak dulu supaya kodenya tidak berurutan —
        // kode yang bisa ditebak membuat orang mengarang bukti transfer.
        for ($i = 0; $i < 40; $i++) {
            $kode = random_int($min, $maks);

            if (! isset($terpakai[$total + $kode])) {
                return $kode;
            }
        }

        // Baru menyisir berurutan kalau yang acak gagal terus.
        for ($kode = $min; $kode <= $maks; $kode++) {
            if (! isset($terpakai[$total + $kode])) {
                return $kode;
            }
        }

        throw new \RuntimeException(
            'Seluruh kode unik ' . $min . '-' . $maks . ' sudah terpakai untuk nominal itu.'
        );
    }

    /**
     * Kolom yang ditulis, dipetakan dari nama seragam ke nama kolom aslinya.
     *
     * @return array<string, mixed>
     */
    private function rakitKolom(
        string $layanan,
        array $sumber,
        array $isian,
        int $jumlah,
        int $total,
        int $kodeUnik,
        ?string $olehSiapa,
        int $potongan = 0
    ): array {
        $kolom = $sumber['kolom'];
        $baris = [
            $sumber['kolom_nomor'] => Pendaftaran::nomorBaru($layanan),
            'status' => Pendaftaran::statusAwal($layanan),
        ];

        // Nama kolom berbeda di tiap tabel — nama vs nama_pemesan, telp vs
        // telp_pemesan — jadi dipetakan lewat katalog, bukan ditulis lima kali.
        foreach (['nama_orang' => 'nama', 'email' => 'email', 'telp' => 'telp', 'affiliasi' => 'affiliasi'] as $seragam => $dariIsian) {
            if (isset($kolom[$seragam]) && ($isian[$dariIsian] ?? null) !== null) {
                $baris[$kolom[$seragam]] = $isian[$dariIsian];
            }
        }

        if (isset($kolom['angkatan_id']) && ! empty($isian['kategori_id'])) {
            $baris[$kolom['angkatan_id']] = $isian['kategori_id'];
        }

        // jumlah_pendaftar hanya dipunyai tiga tabel; dua lainnya memang
        // selalu satu orang per baris.
        if (isset($kolom['jumlah'])) {
            $baris[$kolom['jumlah']] = $jumlah;
        }

        $baris[$kolom['total']] = $total;

        if (isset($kolom['kode_unik'])) {
            $baris[$kolom['kode_unik']] = $kodeUnik;
        }

        /*
         * Potongan khusus hanya ditulis ke tabel yang punya kolomnya —
         * Scopus Kafe tidak punya, dan di sana nominalnya memang diketik
         * langsung sehingga potongannya sudah termasuk di dalamnya.
         *
         * Kodenya ikut disimpan: tanpa keterangan, potongan Rp 500.000 pada
         * satu pendaftaran tidak bisa dijelaskan siapa pun enam bulan
         * kemudian.
         */
        if ($potongan > 0 && isset($kolom['nominal_diskon'])) {
            $baris[$kolom['nominal_diskon']] = $potongan;

            if (isset($kolom['kode_diskon'])) {
                $baris[$kolom['kode_diskon']] = trim((string) ($isian['kode_potongan'] ?? '')) ?: 'KHUSUS';
            }
        }

        $catatan = trim((string) ($isian['note'] ?? ''));
        $kolomCatatan = Pendaftaran::kolomCatatan($layanan);

        if ($kolomCatatan !== null) {
            // Jejak SIAPA yang mendaftarkan: baris yang dibuat panitia tidak
            // punya alamat IP maupun jejak peramban seperti yang dari jalur
            // publik, jadi tanpa ini tidak ada keterangan asal-usulnya.
            $jejak = 'Didaftarkan panitia' . ($olehSiapa !== null ? ' oleh ' . $olehSiapa : '')
                . ' pada ' . now()->format('d M Y H:i')
                . ($potongan > 0
                    ? ' dengan potongan khusus Rp ' . number_format($potongan, 0, ',', '.')
                        . ' (' . (trim((string) ($isian['kode_potongan'] ?? '')) ?: 'tanpa keterangan') . ')'
                    : '');

            $baris[$kolomCatatan] = $catatan !== '' ? $catatan . ' | ' . $jejak : $jejak;
        }

        return $baris + $this->kolomWajibKhusus($layanan, $isian);
    }

    /**
     * Kolom NOT NULL tanpa nilai bawaan yang hanya dipunyai satu tabel.
     *
     * Clinik Scopus menuntut sesi, trainer, dan pelanggannya; tanpa ketiganya
     * MySQL menolak barisnya dengan galat yang hanya menyebut nama kolomnya.
     *
     * @return array<string, mixed>
     */
    private function kolomWajibKhusus(string $layanan, array $isian): array
    {
        if ($layanan !== 'clinik_scopus') {
            return [];
        }

        return [
            'clinikscopus_id' => $isian['clinikscopus_id'] ?? null,
            'trainer_id' => $isian['trainer_id'] ?? null,
            'customer_id' => $isian['customer_id'] ?? null,
            'kode_booking' => $isian['kode_booking'] ?? ('BOOK-' . now()->format('dmYHis')),
            'sesi' => $isian['sesi'] ?? null,
            'jam_sesi' => $isian['jam_sesi'] ?? null,
        ];
    }
}
