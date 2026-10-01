<?php

namespace Tests\Feature\ClinikScopus;

use App\ClinikScopusBiayaPersesi;
use App\Layanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penjaga bahasa pengumuman yang dibaca pelanggan.
 *
 * Yang dijaga BUKAN gaya bahasanya — itu urusan pemiliknya — melainkan dua hal
 * yang pernah salah dan tidak kelihatan dari layar mana pun:
 *
 *   1. Istilah dalam yang tidak diterjemahkan. Orang yang baru pertama
 *      mendengar Scopus tidak bisa menebak apa itu "Seminar Kit", "E-Materi",
 *      atau "handling jurnal".
 *   2. Angka yang ditulis mati di cetakan. "Selama 3 hari 2 malam" ikut
 *      tercetak di angkatan yang tanggalnya cuma dua hari.
 */
class BahasaPengumumanTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Layanan::lupakanKatalog();
        ClinikScopusBiayaPersesi::lupakanPemeriksaanJadwal();
    }

    /** @return \Illuminate\Support\Collection<string, ClinikScopusBiayaPersesi> */
    private function tarif()
    {
        return ClinikScopusBiayaPersesi::semuaYangBerlaku();
    }

    #[Test]
    public function tidak_ada_lagi_istilah_dalam_yang_tidak_diterjemahkan(): void
    {
        /*
         * Daftarnya istilah yang MEMANG pernah ada di naskahnya, bukan daftar
         * kata terlarang umum. "Scopus" sendiri tidak dilarang: itu nama
         * pangkalan datanya, dan cetakannya kini menjelaskan artinya sekali.
         */
        $terlarang = [
            'Target Training', 'Seminar Kit', 'E-Materi', 'handling jurnal',
            'penajaman hasil riset', 'Biaya Investasi', 'tools & aplikasi',
        ];

        $kedapatan = [];

        foreach ($this->tarif() as $kunci => $t) {
            $naskah = implode("\n", array_merge(
                [(string) $t->template_deskripsi],
                $t->daftar_kegiatan,
                $t->daftar_fasilitas
            ));

            foreach ($terlarang as $kata) {
                if (stripos($naskah, $kata) !== false) {
                    $kedapatan[] = $kunci . ': "' . $kata . '"';
                }
            }
        }

        $this->assertSame([], $kedapatan,
            "Istilah dalam muncul lagi di naskah pengumuman:\n" . implode("\n", $kedapatan));
    }

    #[Test]
    public function lama_acara_tidak_ditulis_mati_di_cetakan(): void
    {
        /*
         * Angka hari di dalam cetakan tidak ikut berubah saat tanggalnya
         * berubah. Yang benar memakai penanda {durasi}, yang dihitung dari
         * tanggal angkatannya.
         */
        $mati = [];

        foreach ($this->tarif() as $kunci => $t) {
            $cetakan = (string) $t->template_deskripsi;

            if (preg_match('/\b\d+\s*(hari|malam)\b/i', $cetakan, $m)) {
                $mati[] = $kunci . ': "' . $m[0] . '"';
            }
        }

        $this->assertSame([], $mati,
            "Lama acara ditulis mati, bukan lewat {durasi}:\n" . implode("\n", $mati));
    }

    #[Test]
    public function cetakan_yang_menyebut_lama_acara_memakai_penandanya(): void
    {
        // Scopus Camp menginap, jadi lamanya memang perlu disebut.
        foreach (['scopus_camp|jawa', 'scopus_camp|luar_jawa'] as $kunci) {
            $this->assertStringContainsString('{durasi}',
                (string) ($this->tarif()->get($kunci)?->template_deskripsi ?? ''), $kunci);
        }
    }

    #[Test]
    public function penanda_yang_dipakai_cetakan_semuanya_dikenali(): void
    {
        // Penanda salah ketik dibiarkan kelihatan oleh perakitnya — tetapi di
        // naskah yang sudah terbit, yang kelihatan itu "{tangal}" di tengah
        // pengumuman yang dibaca calon pembeli.
        $dikenal = array_keys(\App\Support\PerakitDeskripsi::PENANDA);
        $asing = [];

        foreach ($this->tarif() as $kunci => $t) {
            preg_match_all('/\{[a-z_]+\}/', (string) $t->template_deskripsi, $ada);

            foreach (array_unique($ada[0]) as $p) {
                if (! in_array($p, $dikenal, true)) {
                    $asing[] = $kunci . ': ' . $p;
                }
            }
        }

        $this->assertSame([], $asing, 'Penanda tidak dikenali: ' . implode(', ', $asing));
    }
}
