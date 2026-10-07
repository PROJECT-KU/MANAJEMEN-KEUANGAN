<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Menjaga ejaan huruf besar-kecil pada rujukan kelas.
 *
 * Satu golongan kerusakan yang TIDAK PERNAH menampakkan diri di komputer
 * pengembang, dan selalu menampakkan diri di peladen.
 *
 * `Kelas::class` tidak memeriksa kelasnya ada atau tidak — ia cuma merangkai
 * teks nama. Jadi `\App\ClinikScopus::class` tetap sah ditulis walau yang ada
 * `App\Clinikscopus`. Galatnya baru muncul saat barisnya benar-benar
 * dijalankan.
 *
 * Dan di macOS ia TIDAK PERNAH dijalankan sampai gagal: berkas sistemnya
 * tidak peka huruf besar-kecil, jadi `app/ClinikScopus.php` ketemu di
 * `app/Clinikscopus.php`. Di Linux berkasnya tidak ketemu.
 *
 * Terjadi 5 Okt 2026: halaman Biaya Per Sesi 500 di peladen beberapa menit
 * sesudah dideploy, padahal seluruh uji hijau dan halamannya normal di
 * komputer. Pesannya `Class "App\ClinikScopus" not found`.
 */
class RujukanKelasPekaHurufTest extends TestCase
{
    /** Folder yang dipindai; vendor dan berkas bikinan mesin dilewati. */
    private const DIPINDAI = ['app', 'config', 'database', 'routes', 'tests'];

    #[Test]
    public function setiap_rujukan_kelas_app_ejaan_hurufnya_tepat(): void
    {
        $nyata = $this->kelasYangBenarBenarAda();

        $this->assertNotEmpty($nyata, 'prasyarat ujinya: harus ada kelas App yang terbaca');

        $salah = [];

        foreach ($this->berkasPhp() as $berkas) {
            $isi = $this->tanpaKomentar(file_get_contents($berkas));

            foreach (explode("\n", $isi) as $nomor => $baris) {
                preg_match_all('/\\\\?(App(?:\\\\\\\\|\\\\)[A-Za-z0-9_\\\\]+)::class/', $baris, $cocok);

                foreach ($cocok[1] as $rujukan) {
                    $nama = str_replace('\\\\', '\\', $rujukan);

                    if (isset($nyata[$nama])) {
                        continue;
                    }

                    /*
                     * Hanya dilaporkan kalau ada kelas yang SAMA PERSIS selain
                     * hurufnya. Nama yang tidak ada sama sekali bukan urusan
                     * uji ini — itu tertangkap saat kodenya dijalankan, di
                     * sistem mana pun.
                     */
                    foreach ($nyata as $benar => $_) {
                        if (strcasecmp($benar, $nama) === 0) {
                            $salah[] = sprintf(
                                '%s:%d  ditulis %s, seharusnya %s',
                                $berkas,
                                $nomor + 1,
                                $nama,
                                $benar
                            );
                        }
                    }
                }
            }
        }

        $this->assertSame([], $salah, implode("\n", array_merge(
            ['Rujukan kelas yang ejaan hurufnya tidak tepat. Jalan di macOS, 500 di peladen Linux:'],
            $salah
        )));
    }

    /**
     * Membuang komentar, menyisakan kode — dan nomor barisnya dijaga tetap.
     *
     * Versi pertama uji ini memindai berkasnya mentah-mentah, lalu MERAH pada
     * berkas yang justru benar: komentar penjelas di uji ini sendiri menyebut
     * nama yang salah itu sebagai contoh, dan ikut terbaca sebagai rujukan.
     *
     * Dipakai token_get_all, bukan pola teks: pola teks tidak bisa tahu
     * sebuah "//" ada di dalam kode atau di dalam teks biasa.
     */
    private function tanpaKomentar(string $isi): string
    {
        $hasil = '';

        foreach (token_get_all($isi) as $token) {
            if (is_array($token)) {
                // Komentar diganti barisan kosong supaya nomor barisnya tidak bergeser.
                if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    $hasil .= str_repeat("\n", substr_count($token[1], "\n"));

                    continue;
                }

                $hasil .= $token[1];

                continue;
            }

            $hasil .= $token;
        }

        return $hasil;
    }

    /**
     * Nama kelas yang benar-benar ada, dirakit dari namespace + nama kelas di
     * dalam berkasnya — BUKAN dari nama berkasnya.
     *
     * @return array<string, string>
     */
    #[Test]
    public function nama_kelas_sama_dengan_nama_berkasnya(): void
    {
        /*
         * Lubang yang terlewat sampai 7 Okt 2026.
         *
         * Uji di atas menyusun daftar "kelas yang benar-benar ada" dari nama
         * yang DIDEKLARASIKAN di dalam berkasnya. Tetapi PSR-4 memuat kelas
         * berdasarkan nama BERKASNYA — jadi kelas `ClinikscopusPromo` yang
         * tinggal di `app/ClinikScopusPromo.php` dianggap ada oleh uji itu,
         * padahal autoloader Linux tidak akan pernah menemukannya.
         *
         * Akibatnya terukur di produksi: `App\ClinikscopusPromo` TIDAK ADA
         * di peladen sementara `App\ClinikScopusPromo` ada. Delapan rujukan
         * memakai ejaan yang salah — seluruh layar admin Promo dan perintah
         * terjadwal `promo:expire`, yang menulis satu galat ke log tiap menit
         * begitu penjadwalnya diperbaiki.
         *
         * Di macOS tidak satu pun dari itu menampakkan diri.
         */
        /*
         * Dua berkas MATI yang sudah ada sebelum uji ini dipasang.
         *
         * Keduanya tidak dirujuk satu rute pun maupun satu baris kode pun —
         * ditelusuri ke seluruh app/, routes/, resources/, tests/, config/.
         * Keduanya juga memang tidak bisa dimuat: yang pertama berisi kelas
         * bernama lain, yang kedua berakhiran ganda `.php.php`.
         *
         * Didaftarkan di sini, BUKAN dihapus diam-diam: menghapus berkas
         * orang bukan bagian dari pekerjaan yang diminta. Keduanya pantas
         * dibuang, dan itu keputusan pemiliknya.
         *
         * Daftar tertutup: berkas baru yang namanya tidak cocok tetap
         * ditangkap.
         */
        $matiSejakAwal = [
            'app/Http/Controllers/account/PesananController.php',
            'app/Http/Controllers/account/ArtikelKomentarController.php.php',
        ];

        $melenceng = [];

        foreach ($this->berkasPhp(['app']) as $berkas) {
            $relatif = str_replace(base_path() . '/', '', $berkas);

            if (in_array($relatif, $matiSejakAwal, true)) {
                continue;
            }

            $isi = file_get_contents($berkas);

            if (! preg_match('/^(?:final\s+|abstract\s+)?class\s+(\w+)/m', $isi, $kelas)) {
                continue;
            }

            $namaBerkas = pathinfo($berkas, PATHINFO_FILENAME);

            if ($kelas[1] !== $namaBerkas) {
                $melenceng[] = str_replace(base_path() . '/', '', $berkas)
                    . ' berisi class ' . $kelas[1];
            }
        }

        $this->assertSame([], $melenceng,
            "Nama kelas berikut tidak sama dengan nama berkasnya. PSR-4 memuat "
            . "berdasarkan nama berkas, jadi di Linux kelasnya tidak akan ketemu "
            . "— dan di macOS semuanya terlihat baik-baik saja:\n- "
            . implode("\n- ", $melenceng) . "\n");
    }

    private function kelasYangBenarBenarAda(): array
    {
        $hasil = [];

        foreach ($this->berkasPhp(['app']) as $berkas) {
            $isi = file_get_contents($berkas);

            if (! preg_match('/^namespace\s+([^;]+);/m', $isi, $ns)) {
                continue;
            }

            if (! preg_match('/^(?:final\s+|abstract\s+)?class\s+(\w+)/m', $isi, $kelas)) {
                continue;
            }

            $hasil[trim($ns[1]) . '\\' . $kelas[1]] = $berkas;
        }

        return $hasil;
    }

    /**
     * @param  array<int, string>|null  $folder
     * @return array<int, string>
     */
    private function berkasPhp(?array $folder = null): array
    {
        $hasil = [];

        foreach ($folder ?? self::DIPINDAI as $f) {
            $jalur = base_path($f);

            if (! is_dir($jalur)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($jalur, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $berkas) {
                if ($berkas->isFile() && $berkas->getExtension() === 'php') {
                    $hasil[] = $berkas->getPathname();
                }
            }
        }

        return $hasil;
    }
}
