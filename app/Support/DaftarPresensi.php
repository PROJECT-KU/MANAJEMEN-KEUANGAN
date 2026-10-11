<?php

namespace App\Support;

use App\PendaftaranPeserta;
use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Support\Collection;

/**
 * Satu baris per ORANG, bukan per pendaftaran.
 *
 * Ekspor yang sudah ada membawa satu baris per pendaftaran, jadi rombongan
 * lima orang muncul sebagai satu nama. Terukur: 109 pendaftaran rombongan
 * mewakili 296 orang, sementara berkasnya cuma memuat 109 nama — dan yang
 * memegangnya di meja registrasi tidak punya nama untuk 187 kursi.
 */
class DaftarPresensi
{
    /**
     * @param  Collection  $baris  hasil Pendaftaran::kueri()
     * @return list<array{nomor:string, nama:string, peran:string, telp:string,
     *                    email:string, afiliasi:string, terisi:bool}>
     */
    public static function orang(Collection $baris): array
    {
        $peserta = self::pesertaSekaligus($baris);

        $hasil = [];

        foreach ($baris as $b) {
            $afiliasi = trim((string) ($b->affiliasi ?? ''));

            // Pendaftarnya sendiri selalu orang pertama rombongannya.
            $hasil[] = [
                'nomor' => (string) $b->nomor,
                'nama' => trim((string) $b->nama_orang),
                'peran' => 'Pendaftar',
                'telp' => self::telp($b->telp),
                'email' => trim((string) ($b->email ?? '')),
                'afiliasi' => $afiliasi,
                'terisi' => true,
            ];

            $lain = $peserta[$b->layanan . '|' . $b->id] ?? collect();

            foreach ($lain as $orang) {
                $hasil[] = [
                    'nomor' => (string) $b->nomor,
                    'nama' => trim((string) $orang->nama) ?: '(tanpa nama)',
                    'peran' => 'Peserta',
                    'telp' => self::telp($orang->telp),
                    'email' => trim((string) ($orang->email ?? '')),
                    /*
                     * Afiliasi menurun dari PENDAFTARNYA: tabel peserta tidak
                     * punya kolomnya, dan rombongan memang datang dari satu
                     * lembaga yang sama. Dikosongkan, separuh lembar presensi
                     * jadi kolom kosong tanpa sebab yang terlihat.
                     */
                    'afiliasi' => $afiliasi,
                    'terisi' => true,
                ];
            }

            /*
             * Kursi yang dibayar tetapi namanya belum diisi TETAP dibariskan.
             * Dibuang, lembarnya terbaca seperti daftar yang sudah lengkap,
             * dan yang memegangnya di meja registrasi tidak tahu ada orang
             * yang namanya belum pernah ditanyakan.
             */
            $kurang = max(0, (int) $b->jumlah - 1 - $lain->count());

            for ($i = 0; $i < $kurang; $i++) {
                $hasil[] = [
                    'nomor' => (string) $b->nomor,
                    'nama' => '(belum diisi)',
                    'peran' => 'Peserta',
                    'telp' => '',
                    'email' => '',
                    'afiliasi' => $afiliasi,
                    'terisi' => false,
                ];
            }
        }

        return $hasil;
    }

    /**
     * Peserta seluruh baris dibaca dalam SATU kueri.
     *
     * Dibaca per pendaftaran di dalam gelung, lembar berisi 290 pendaftaran
     * berarti 290 kueri tambahan.
     *
     * @return array<string, Collection>
     */
    private static function pesertaSekaligus(Collection $baris): array
    {
        if ($baris->isEmpty()) {
            return [];
        }

        return PendaftaranPeserta::query()
            ->whereIn('pendaftaran_id', $baris->map(fn ($b) => (string) $b->id)->all())
            ->orderBy('urutan')
            ->get()
            ->groupBy(fn ($p) => $p->layanan . '|' . $p->pendaftaran_id)
            ->all();
    }

    /** Nomor dalam bentuk lokal yang bisa dibaca panitia, bukan +62. */
    private static function telp(?string $nomor): string
    {
        $rapi = trim((string) $nomor);

        return $rapi === ''
            ? ''
            : DaftarPeserta::bentukLokal(NomorTelepon::rapikan($rapi));
    }

    /**
     * Keterangan angkatan untuk kepala lembarnya.
     *
     * Dibaca dari BARIS PERTAMA, bukan dari saringan: saringannya cuma
     * menyimpan id angkatan, sedangkan yang perlu tercetak nama, nomor,
     * tanggal, dan lokasinya.
     *
     * @return array{nama:string, nomor:?string, layanan:string, mulai:?string,
     *               selesai:?string, lokasi:string}|null
     */
    public static function angkatan(Collection $baris): ?array
    {
        $pertama = $baris->first();

        if ($pertama === null) {
            return null;
        }

        $a = Pendaftaran::angkatanBaris($pertama);

        if ($a === null) {
            return null;
        }

        return [
            'nama' => (string) $a['nama'],
            'nomor' => $a['nomor'],
            'layanan' => Pendaftaran::katalog()[$pertama->layanan]['nama'] ?? (string) $pertama->layanan,
            'mulai' => self::tanggal($a['mulai'] ?? null),
            'selesai' => self::tanggal($a['selesai'] ?? null),
            'lokasi' => (string) ($a['lokasi'] ?? ''),
        ];
    }

    /** Tanggal berbahasa Indonesia; APP_LOCALE masih en. */
    private static function tanggal($nilai): ?string
    {
        return $nilai === null || $nilai === ''
            ? null
            : \Illuminate\Support\Carbon::parse($nilai)->locale('id')->translatedFormat('d F Y');
    }
}
