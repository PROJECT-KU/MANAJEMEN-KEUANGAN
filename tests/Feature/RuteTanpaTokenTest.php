<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Menjaga alamat tidak kembali memakai token hiasan.
 *
 * Sampai 3 Okt 2026 ada 20 rute berparameter {token}. Dari semuanya, hanya
 * SATU yang benar-benar memakainya (testimoni peserta, dicari ke kolom
 * token_update); 19 sisanya menerima nilainya lalu mengabaikannya — alamat
 * dua kali lebih panjang tanpa menjaga apa pun.
 *
 * Yang bentuknya {id}{token} tanpa pemisah bahkan jalan hanya karena
 * kebetulan: "/gaji/edit/5AbCdEf123456" terurai jadi id="5AbCdEf12345" dan
 * token="6", lalu MySQL diam-diam memotong "5AbCdEf12345" jadi 5. Di basis
 * data yang lebih ketat, atau MySQL bermodus ketat, itu gagal.
 *
 * Kerusakan seperti ini tidak menampakkan diri — alamatnya tetap terbuka —
 * jadi dijaga uji, bukan diingat.
 */
class RuteTanpaTokenTest extends TestCase
{
    /**
     * Rute yang ruas tokennya memang dipakai, dengan alasannya.
     *
     * Daftar putih, bukan pengecualian diam-diam: menambah satu di sini
     * memaksa penambahnya menuliskan alasan mengapa nilainya perlu ada di
     * alamat.
     */
    private const BOLEH = [
        // Token reset kata sandi — rahasia sungguhan, sekali pakai.
        'password.atur-ulang' => 'token',

        // Dicari ke kolom token_update untuk menolak testimoni ganda.
        'account.peserta.testimoni' => 'token_update',
    ];

    #[Test]
    public function tidak_ada_rute_yang_memakai_token_hiasan(): void
    {
        $nakal = [];

        foreach (Route::getRoutes() as $rute) {
            $nama = $rute->getName();

            foreach ($rute->parameterNames() as $param) {
                if (! str_contains(strtolower($param), 'token')) {
                    continue;
                }

                if (($nama !== null) && array_key_exists($nama, self::BOLEH)
                    && self::BOLEH[$nama] === $param) {
                    continue;
                }

                $nakal[] = ($nama ?: '(tanpa nama)') . ' — {' . $param . '} di ' . $rute->uri();
            }
        }

        $this->assertSame([], $nakal,
            "Rute berikut memakai token di alamatnya. Kalau nilainya memang\n"
            . "diperiksa, daftarkan di RuteTanpaTokenTest::BOLEH beserta\n"
            . "alasannya; kalau tidak, buang dari alamatnya:\n  "
            . implode("\n  ", $nakal));
    }

    /**
     * Pola {id}{token} tanpa pemisah tidak boleh muncul lagi dalam bentuk
     * apa pun: dua parameter yang dempet membuat Laravel membelahnya di
     * tempat yang salah, dan hasilnya hanya tertolong oleh pemaksaan jenis
     * data yang diam.
     */
    #[Test]
    public function tidak_ada_dua_parameter_yang_dempet_di_alamat(): void
    {
        $dempet = [];

        foreach (Route::getRoutes() as $rute) {
            if (preg_match('/\}\{/', $rute->uri())) {
                $dempet[] = ($rute->getName() ?: '(tanpa nama)') . ' — ' . $rute->uri();
            }
        }

        $this->assertSame([], $dempet,
            "Dua parameter dempet tanpa pemisah:\n  " . implode("\n  ", $dempet));
    }
}
