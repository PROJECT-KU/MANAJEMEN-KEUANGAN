<?php

namespace App\Http\Controllers\Publict\Concerns;

use App\Support\PendaftaranSemuaLayanan as Katalog;
use Illuminate\Support\Facades\Log;

/**
 * Bagian alur gerbang pembayaran yang SAMA untuk tiap layanan.
 *
 * Diangkat jadi satu perkakas saat Scopus Kafe menyusul Scopus Camp ke
 * gerbang, 26 Okt 2026. Disalin, keduanya akan berbeda perlahan — dan yang
 * paling mudah berbeda justru yang paling mahal kalau salah: penjagaan
 * terhadap pembayaran milik pendaftaran LAIN yang diwariskan karena nomor
 * tagihannya terpakai ulang.
 *
 * Yang dibedakan tiap layanan cuma empat hal, dan keempatnya diminta sebagai
 * metode: kunci layanannya di katalog, kelas modelnya, cara menandai lunas,
 * dan cara memeriksa sudah lunas. Sisanya sama persis.
 */
trait AlurGerbangPembayaran
{
    /** Kunci layanan ini di PendaftaranSemuaLayanan. */
    abstract protected function layananKunci(): string;

    /** Menandai satu pendaftaran lunas; isinya khas tiap layanan. */
    abstract protected function tandaiLunas($pendaftaran, string $status, ?string $kanal): void;

    /** Pendaftaran ini sudah lunas atau belum. */
    abstract protected function sudahLunas($pendaftaran): bool;

    /** Nomor pendaftarannya, apa pun nama kolomnya di layanan ini. */
    protected function nomorPendaftaran($pendaftaran): string
    {
        $kolom = Katalog::kolomPeran($this->layananKunci(), 'nomor') ?? 'id_transaksi';

        return (string) ($pendaftaran->{$kolom} ?? '');
    }

    /**
     * Menanyakan keadaan pembayaran ke gerbang, lalu menyusulkan hasilnya.
     *
     * Ada karena pemberitahuan balik dari gerbang TIDAK SELALU SAMPAI: ke
     * localhost tidak pernah, dan di peladen bergantung pada alamat yang
     * disetel di dasbor mereka. Tanpa penyusulan ini, pembayaran yang sudah
     * masuk tidak pernah tercatat dan orangnya tetap melihat "menunggu".
     */
    protected function susulkanDariGerbang($pendaftaran)
    {
        // Yang transfer manual tidak punya tagihan di sana untuk ditanyakan.
        if ($pendaftaran->cara_bayar !== 'doku' || $this->sudahLunas($pendaftaran)) {
            return $pendaftaran;
        }

        /*
         * Yang ditanyakan nomor TAGIHANNYA, bukan nomor pendaftarannya.
         * Sesudah tagihannya dibuat ulang keduanya berbeda, dan menanyakan
         * nomor pendaftaran memulangkan keadaan tagihan LAMA — yang memang
         * gagal — sehingga pembayaran yang baru saja berhasil tidak terbaca.
         */
        $kabar = $this->doku->periksaStatus(
            (string) ($pendaftaran->bayar_nomor ?: $this->nomorPendaftaran($pendaftaran))
        );

        // tahu=false berarti gerbang tidak menjawab atau belum mengenal
        // nomornya — BUKAN berarti belum dibayar.
        if (! $kabar['tahu']) {
            return $pendaftaran;
        }

        /*
         * Kegagalannya IKUT DICATAT, bukan dibuang.
         *
         * Dibuang, halaman statusnya tetap berbunyi "tinggal satu langkah
         * lagi" lengkap dengan tombol bayar yang mengantar ke tagihan mati.
         */
        if ($kabar['gagal']) {
            $baru = (string) $kabar['status'];

            if ($baru !== '') {
                $pendaftaran->forceFill(['bayar_status' => $baru])->save();
            }

            return $pendaftaran;
        }

        if (! $kabar['lunas']) {
            return $pendaftaran;
        }

        /*
         * Pembayaran yang LEBIH TUA daripada pendaftarannya TIDAK diterima.
         *
         * Nomor tagihan bisa terpakai ulang, dan gerbang menjawab SUCCESS
         * untuk nomor itu sebab memang pernah dibayar — oleh yang membayar
         * pendahulunya. Tanpa penjagaan ini pendaftaran baru tertandai lunas
         * tanpa ada uang masuk, dan pendaftarnya menerima surat penerimaan.
         *
         * Sepuluh menit kelonggaran terhadap selisih jam antara peladen kita
         * dan peladen gerbang; pembayaran yang sah selalu SESUDAH
         * pendaftarannya.
         */
        if ($kabar['waktu'] !== null
            && $pendaftaran->created_at !== null
            && $kabar['waktu']->lt($pendaftaran->created_at->copy()->subMinutes(10))) {
            Log::warning('Pembayaran di gerbang lebih tua daripada pendaftarannya; tidak diwariskan', [
                'layanan' => $this->layananKunci(),
                'nomor' => $this->nomorPendaftaran($pendaftaran),
                'bayar_pada' => (string) $kabar['waktu'],
                'pendaftaran_dibuat' => (string) $pendaftaran->created_at,
            ]);

            return $pendaftaran;
        }

        $this->tandaiLunas($pendaftaran, (string) $kabar['status'], $kabar['kanal']);

        return $pendaftaran->refresh();
    }

    /**
     * Nomor tagihan untuk percobaan pembayaran BERIKUTNYA.
     *
     * Percobaan pertama memakai nomor pendaftarannya apa adanya — itu yang
     * paling mudah dicocokkan panitia dengan mutasi. Sesudah itu ditambahkan
     * -R2, -R3, sebab gerbang menolak nomor yang sudah pernah dipakai dengan
     * "INVOICE ALREADY USED".
     *
     * Akhirannya memakai huruf R, bukan hanya angka: nomor pendaftaran kita
     * sendiri berakhir dengan tanda hubung dan empat angka, jadi akhiran yang
     * cuma angka tidak bisa dibedakan dari bagian nomornya sendiri oleh mata
     * siapa pun yang menelusuri pembayaran.
     */
    protected function nomorTagihanBaru($pendaftaran): string
    {
        $pokok = $this->nomorPendaftaran($pendaftaran);
        $sebelumnya = trim((string) $pendaftaran->bayar_nomor);

        if ($sebelumnya !== '' && $sebelumnya !== $pokok) {
            $ke = preg_match('/-R(\d+)$/', $sebelumnya, $cocok) === 1
                ? ((int) $cocok[1]) + 1
                : 2;

            return $pokok . '-R' . $ke;
        }

        /*
         * Belum tercatat TIDAK berarti belum pernah ada tagihannya: kolom
         * `bayar_nomor` baru ada belakangan, dan pendaftaran yang tagihannya
         * dibuat sebelum itu memakai nomor pendaftarannya apa adanya tanpa
         * mencatatnya. Nomor itu sudah terpakai di gerbang.
         *
         * Satu pun kolom tagihan terisi sudah cukup: lebih baik melewati satu
         * nomor yang sebenarnya masih bersih daripada memakai nomor mati.
         */
        $pernahAda = $sebelumnya === $pokok
            || trim((string) $pendaftaran->bayar_rujukan) !== ''
            || trim((string) $pendaftaran->bayar_permintaan_id) !== ''
            || trim((string) $pendaftaran->bayar_url) !== ''
            || trim((string) $pendaftaran->bayar_status) !== '';

        return $pernahAda ? $pokok . '-R2' : $pokok;
    }

    /**
     * Kembali ke halaman status pendaftaran ini, membawa pesannya.
     *
     * BUKAN back(). Jalur-jalur ini bisa dibuka langsung dari alamatnya —
     * tautan di surat, tautan yang disalin, tombol yang ditekan ulang sesudah
     * tab lain ditutup — dan permintaan tanpa kepala Referer membuat back()
     * jatuh ke halaman muka situs, tempat pesannya tidak digambar.
     */
    protected function kembaliKeStatus($pendaftaran, string $kunci, string $pesan)
    {
        $rute = Katalog::sumber($this->layananKunci())['rute_status'] ?? null;

        if ($rute === null) {
            return back()->with($kunci, $pesan);
        }

        return redirect()->route($rute, $pendaftaran->getKey())->with($kunci, $pesan);
    }

    /** Alamat email yang disebutkan kembali ke pengirimnya, sebagian ditutup. */
    protected function samarkanEmail(string $email): string
    {
        [$nama, $ranah] = array_pad(explode('@', $email, 2), 2, '');

        $tampak = mb_substr($nama, 0, min(2, max(1, mb_strlen($nama) - 1)));

        return $tampak . str_repeat('*', max(1, mb_strlen($nama) - mb_strlen($tampak)))
            . ($ranah !== '' ? '@' . $ranah : '');
    }
}
