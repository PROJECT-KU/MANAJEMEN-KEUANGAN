<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tugas terjadwal harus jalan DI DALAM proses yang sama.
 *
 * $schedule->command('x') menjalankan "php artisan x" sebagai PROSES BARU,
 * dan untuk itu Laravel memakai proc_open. Di peladen produksi fungsi itu
 * dimatikan — diperiksa langsung 5 Okt 2026: proc_open, exec, shell_exec,
 * passthru, dan popen semuanya dimatikan.
 *
 * Yang membuatnya berbahaya bukan kegagalannya, melainkan DIAMNYA: yang
 * gagal proses anak, sementara schedule:run sendiri melaporkan sukses. Tidak
 * ada galat yang terbit, tidak ada yang berubah, dan tidak ada yang tahu
 * sampai seseorang menyadari angkatannya tidak pernah tertutup.
 *
 * $schedule->call() menjalankannya di dalam proses yang sama.
 */
class PenjadwalTanpaProsesAnakTest extends TestCase
{
    #[Test]
    public function tidak_ada_tugas_yang_menyalakan_proses_baru(): void
    {
        $sumber = file_get_contents(app_path('Console/Kernel.php'));

        // Komentar dibuang dulu; penjelasan di berkas ini menyebut command()
        // sebagai contoh yang TIDAK boleh dipakai.
        $kode = $this->tanpaKomentar($sumber);

        $this->assertSame(0, preg_match('/->command\s*\(/', $kode),
            'Ada tugas terjadwal yang memakai command(); di peladen ia butuh proc_open '
            . 'yang dimatikan, dan gagalnya tidak menerbitkan galat apa pun.');

        $this->assertMatchesRegularExpression('/->call\s*\(/', $kode,
            'Tidak ada satu pun tugas yang dijadwalkan.');
    }

    #[Test]
    public function tiap_tugas_punya_nama_yang_terbaca(): void
    {
        /*
         * Tanpa nama, `schedule:list` menyebut semuanya "Closure at
         * Kernel.php:37" — daftar seperti itu tidak bisa dipakai saat mencari
         * tugas mana yang tidak jalan. Nama juga yang dipakai
         * withoutOverlapping() sebagai kunci.
         */
        $kode = $this->tanpaKomentar(file_get_contents(app_path('Console/Kernel.php')));

        $this->assertMatchesRegularExpression('/->name\s*\(/', $kode,
            'Tugas terjadwalnya tidak dinamai; schedule:list tidak akan bisa dibaca.');
    }

    /** Membuang komentar, menyisakan kode. */
    private function tanpaKomentar(string $isi): string
    {
        $hasil = '';

        foreach (token_get_all($isi) as $token) {
            if (is_array($token)) {
                if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }

                $hasil .= $token[1];

                continue;
            }

            $hasil .= $token;
        }

        return $hasil;
    }
}
