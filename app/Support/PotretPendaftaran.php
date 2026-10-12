<?php

namespace App\Support;

use App\PendaftaranDihapus;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Carbon;

/**
 * Membaca potret penghapusan jadi keterangan yang bisa dibaca orang.
 *
 * Potretnya tersimpan apa adanya dari toArray() — 22 kolom berisi token,
 * UUID, created_at, dan belasan nama kolom basis data. Dicetak mentah ke
 * layar, yang terpampang JSON di kotak hitam: format untuk pengembang,
 * sementara yang membukanya panitia yang sedang menelusuri ke mana
 * perginya satu pendaftaran.
 *
 * Datanya sendiri WAJIB tetap utuh — PulihkanPendaftaran membacanya untuk
 * menyusun ulang barisnya, dan tidak ada salinan lain. Yang diubah di sini
 * cuma cara membacanya.
 */
class PotretPendaftaran
{
    /**
     * Keterangan pokok: label => nilai, sudah berformat.
     *
     * Sumbernya DUA. Nomor, nama, email, status, dan total dibaca dari
     * kolom arsipnya sendiri; sisanya dari potret lewat peta kolom tiap
     * layanan. Peta itu tidak selalu bisa dipakai: sebagian nilainya
     * ungkapan SQL (COALESCE, CONCAT_WS) yang hanya berarti di dalam
     * kueri, dan di potret yang tersimpan nama kolom aslinya.
     *
     * @return array<string, string>
     */
    public static function pokok(PendaftaranDihapus $arsip): array
    {
        $baris = $arsip->potret['pendaftaran'] ?? [];
        $kolom = Pendaftaran::sumber($arsip->layanan)['kolom'] ?? [];

        $ambil = function (string $kunci) use ($baris, $kolom) {
            /*
             * Ungkapan SQL dilewati secara tegas.
             *
             * Sebagian pemetaannya bukan nama kolom melainkan ungkapan —
             * COALESCE(...) untuk nomor Clinik, CONCAT_WS(...) untuk sesinya.
             *
             * Penjagaan ini SABUK PENGAMAN, bukan penutup cacat yang pernah
             * ada: dicoba mencabutnya, ungkapan itu cuma jadi kunci larik
             * yang tidak ketemu, jadi nilainya hilang dan teks SQL-nya tetap
             * tidak pernah tercetak. Uji yang menjaganya karena itu dibuang
             * — uji yang tidak bisa gagal memberi rasa aman yang palsu.
             * Yang tersisa cuma niatnya, tertulis di sini.
             */
            if (($kolom[$kunci . '_mentah'] ?? false) === true) {
                return null;
            }

            $nama = $kolom[$kunci] ?? null;

            if ($nama === null) {
                return null;
            }

            $nilai = $baris[$nama] ?? null;

            return ($nilai === null || $nilai === '') ? null : $nilai;
        };

        $hasil = array_filter([
            'Nomor pendaftaran' => strtoupper((string) $arsip->nomor) ?: null,
            'Layanan' => $arsip->layanan_nama,
            'Nama' => $arsip->nama ?: null,
            'Email' => $arsip->email ?: null,
            'Telepon' => ($t = $ambil('telp')) ? DaftarPeserta::bentukLokal(
                NomorTelepon::rapikan((string) $t)
            ) : null,
            'Afiliasi' => $ambil('affiliasi'),
            'Angkatan' => self::angkatan($arsip->layanan, $ambil('angkatan_id')),
            'Jumlah orang' => ($j = $ambil('jumlah')) ? ((int) $j) . ' orang' : null,
            'Status terakhir' => $arsip->status ?: null,
            'Cara bayar' => ($c = $ambil('cara_bayar'))
                ? (Pendaftaran::caraBayar((string) $c)['label'] ?? $c)
                : null,
            'Tagihan' => $arsip->total > 0 ? $arsip->total_tulis : null,
            /*
             * Kode uniknya ikut: inilah yang dicocokkan panitia dengan mutasi
             * rekening, dan penghapusan yang dipersoalkan hampir selalu soal
             * uang yang tidak ketemu induknya.
             */
            'Kode unik' => ($k = $ambil('kode_unik')) && (int) $k > 0
                ? number_format((int) $k, 0, ',', '.')
                : null,
            'Catatan panitia' => $ambil('catatan'),
        ], fn ($n) => $n !== null && $n !== '');

        return array_map(fn ($n) => (string) $n, $hasil);
    }

    /** Nama angkatannya, bukan UUID-nya. */
    private static function angkatan(string $layanan, $id): ?string
    {
        if ($id === null) {
            return null;
        }

        $a = Pendaftaran::angkatanLengkap()[$id] ?? null;

        if ($a === null) {
            return null;
        }

        return $a['nomor'] !== null
            ? $a['nama'] . ' (ke-' . $a['nomor'] . ')'
            : $a['nama'];
    }

    /**
     * Nama peserta rombongannya.
     *
     * @return list<array{nama:string, kontak:string}>
     */
    public static function peserta(PendaftaranDihapus $arsip): array
    {
        $hasil = [];

        foreach ($arsip->potret['peserta'] ?? [] as $p) {
            $hasil[] = [
                'nama' => trim((string) ($p['nama'] ?? '')) ?: '(tanpa nama)',
                'kontak' => trim(implode(' · ', array_filter([
                    trim((string) ($p['telp'] ?? '')),
                    trim((string) ($p['email'] ?? '')),
                ]))),
            ];
        }

        return $hasil;
    }

    /**
     * Termin yang ikut terhapus.
     *
     * @return list<array{label:string, nominal:string}>
     */
    public static function pembayaran(PendaftaranDihapus $arsip): array
    {
        $hasil = [];

        foreach ($arsip->potret['pembayaran'] ?? [] as $p) {
            $tanggal = self::tanggal($p['tanggal'] ?? null);

            $hasil[] = [
                'label' => 'Termin ke-' . (int) ($p['urutan'] ?? 0)
                    . ($tanggal !== null ? ' · ' . $tanggal : ''),
                'nominal' => 'Rp ' . number_format((int) ($p['nominal'] ?? 0), 0, ',', '.'),
            ];
        }

        return $hasil;
    }

    /**
     * Riwayat perubahannya, terbaru di atas.
     *
     * @return list<array{waktu:string, kalimat:string}>
     */
    public static function jejak(PendaftaranDihapus $arsip): array
    {
        $hasil = [];

        foreach ($arsip->potret['jejak'] ?? [] as $j) {
            $kalimat = trim((string) ($j['kalimat'] ?? ''));

            $hasil[] = [
                'waktu' => self::tanggal($j['waktu'] ?? null) ?? '—',
                'kalimat' => $kalimat !== '' ? $kalimat : (string) ($j['aksi'] ?? '—'),
            ];
        }

        return array_reverse($hasil);
    }

    /** Tanggal berbahasa Indonesia; APP_LOCALE masih en. */
    private static function tanggal($nilai): ?string
    {
        $rapi = trim((string) $nilai);

        if ($rapi === '') {
            return null;
        }

        try {
            /*
             * Tanggal MURNI dicetak tanpa jam. Kolom `tanggal` milik termin
             * cuma menyimpan tanggalnya, dan Carbon melengkapinya jadi
             * tengah malam — "20 Agt 2026, 00:00" terbaca seperti pembayaran
             * yang dicatat lewat tengah malam, padahal jamnya memang tidak
             * pernah ada.
             */
            $tanggalSaja = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rapi) === 1;

            return Carbon::parse($rapi)->locale('id')
                ->translatedFormat($tanggalSaja ? 'd M Y' : 'd M Y, H:i');
        } catch (\Throwable $e) {
            return $rapi;
        }
    }
}
