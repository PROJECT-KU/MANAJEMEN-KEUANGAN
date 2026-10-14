<?php

namespace Tests\Feature;

use App\ClinikScopusBiayaPersesi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Daftar harga PDF: kertas yang dibagikan KE LUAR.
 *
 * Panduan UI sudah mencatatnya begitu, dan dari situ lahir dua kewajiban yang
 * saling berlawanan arah:
 *
 *   1. Apa pun yang menguntungkan PEMBACANYA harus ada di sana. Potongan
 *      alumni dan rombongan sempat hanya ada di lembar kerja yang dipakai ke
 *      dalam — terukur: Scopus Camp Jawa memberi potongan alumni 10% yang
 *      tidak pernah muncul di kertas yang sampai ke orangnya.
 *
 *   2. Apa pun yang urusan KE DALAM tidak boleh ikut. Siapa yang menyetel
 *      tarifnya tidak ada gunanya bagi pembacanya.
 *
 *      Tanggal berlakunya dulu ikut dilarang di sini, karena cetakan pertama
 *      layar ini pernah membocorkan rencana kenaikan harga internal. Sejak
 *      14 Okt 2026 tanggalnya DIMINTA ada di kertasnya, dan yang menjaga
 *      kebocoran itu bukan lagi larangan mencetak tanggal melainkan asal
 *      barisnya: hanya tarif yang SUDAH berlaku yang sampai ke sini. Rencana
 *      kenaikan harga berstatus TERJADWAL dan tidak pernah ikut tercetak —
 *      dijaga di bawah.
 *
 * Dijaga dari HASIL RENDER-nya, bukan dari sumbernya: yang menentukan apa yang
 * sampai ke kertas adalah apa yang benar-benar tercetak.
 */
class DaftarHargaDibagikanTest extends TestCase
{
    /*
     * Satu uji di bawah menyimpan tarif TERJADWAL sungguhan — tanpa ini
     * barisnya tertinggal di basis data dan ikut terhitung di layar tarif.
     */
    use DatabaseTransactions;

    /** @param array<string, mixed> $ubah */
    private function render(array $ubah = []): string
    {
        $tarif = new ClinikScopusBiayaPersesi(array_merge([
            'layanan' => 'scopus_camp',
            'biaya_persesi' => '5500000',
            'ppn' => null,
            'diskon_alumni_persen' => '10',
            'diskon_rombongan_min' => '5',
            'diskon_rombongan_persen' => '15',
            'fasilitas' => ['Makan dan minum selama acara'],
        ], $ubah));

        $html = view('account.clinik_scopus_biaya_persesi.cetak-pdf', [
            'baris' => [[
                'nama' => 'Scopus Camp',
                'namaVarian' => 'Pulau Jawa',
                'satuan' => 'per peserta',
                'tarif' => $tarif,
            ]],
        ])->render();

        /*
         * Blok <style> DIBUANG sebelum diperiksa.
         *
         * Komentar CSS ikut terkirim bersama halamannya, dan komentar di
         * berkas ini memuat kata "PPN" saat menjelaskan kapan kartunya
         * menguning. Diperiksa apa adanya, penjaga "tanpa PPN tidak ada baris
         * PPN" merah karena KOMENTARNYA, bukan karena ada baris PPN tercetak.
         * Hal yang sama terjadi pada "0%", yang cocok dengan `width: 100%`.
         */
        return (string) preg_replace('#<style.*?</style>#s', '', $html);
    }

    /** @test */
    public function potongan_alumni_dan_rombongan_sampai_ke_kertasnya(): void
    {
        $html = $this->render();

        $this->assertStringContainsString('Alumni', $html,
            'Potongan alumni tidak tercetak; pembaca kertas ini tidak akan tahu ia berhak.');
        $this->assertStringContainsString('10%', $html);

        $this->assertStringContainsString('5+ orang', $html,
            'Ambang rombongannya tidak tercetak; angka 15% tanpa ambangnya tidak bisa dipakai siapa pun.');
        $this->assertStringContainsString('15%', $html);
    }

    /** @test */
    public function potongan_yang_belum_disetel_tidak_dicetak_sebagai_nol(): void
    {
        /*
         * Nol persen bukan "tidak ada potongan" — ia janji potongan yang
         * besarnya nol. Di kertas yang dibagikan, "Alumni -0%" lebih buruk
         * daripada tidak ada barisnya sama sekali.
         */
        $html = $this->render([
            'diskon_alumni_persen' => null,
            'diskon_rombongan_min' => null,
            'diskon_rombongan_persen' => null,
        ]);

        // Yang dijaga ketiadaan BARIS potongannya, bukan ketiadaan kata "0%" —
        // yang terakhir cocok juga dengan lebar tabel `width="100%"`.
        $this->assertStringNotContainsString('class="potongan"', $html);
        $this->assertStringNotContainsString('Alumni', $html);
    }

    /** @test */
    public function ppn_dan_total_dibayarnya_tercetak_saat_memang_kena(): void
    {
        $html = $this->render(['ppn' => '11']);

        $this->assertStringContainsString('PPN 11%', $html);
        // 5.500.000 + 11% = 6.105.000
        $this->assertStringContainsString('6.105.000', $html);
    }

    /** @test */
    public function tanpa_ppn_tidak_ada_baris_ppn_sama_sekali(): void
    {
        /*
         * "PPN 0%" pada kertas harga membuat pembacanya mengira ada pajak yang
         * sedang dibebaskan, lalu bertanya sampai kapan.
         */
        $this->assertStringNotContainsString('+ PPN', $this->render(['ppn' => null]));
    }

    /** @test */
    public function keterangan_ke_dalam_tidak_ikut_ke_kertasnya(): void
    {
        /*
         * "Berlaku sejak" TIDAK lagi di daftar ini — sejak 14 Okt 2026 ia
         * memang diminta tercetak, dan penjaganya pindah ke uji di bawah.
         * Siapa yang menyetel tarifnya tetap tinggal di lembar kerja.
         */
        $html = $this->render(['berlaku_mulai' => now()->toDateString()]);

        foreach (['Disetel oleh', 'Terjadwal', 'penginput'] as $keDalam) {
            $this->assertStringNotContainsString($keDalam, $html,
                'Keterangan ke dalam "' . $keDalam . '" ikut tercetak di kertas yang dibagikan ke luar.');
        }
    }

    /** @test */
    public function tanggal_berlakunya_tercetak_dan_yang_kosong_bertanda_pisah(): void
    {
        $this->assertStringContainsString('Berlaku sejak',
            $this->render(['berlaku_mulai' => '2026-10-01']),
            'Kepala kolom "Berlaku sejak" tidak ada di kertasnya.');

        $this->assertStringContainsString('01/10/2026',
            $this->render(['berlaku_mulai' => '2026-10-01']),
            'Tanggal berlakunya tidak sampai ke kertas; kolomnya ada tetapi isinya tidak diambil.');

        /*
         * Enam dari delapan tarif yang berlaku saat kolom ini dibuat belum
         * pernah diisi tanggalnya. Sel kosong di tengah tabel terbaca seperti
         * data yang gagal dimuat, jadi yang kosong ditulis tanda pisah.
         */
        $kosong = $this->render(['berlaku_mulai' => null]);

        preg_match('/<tbody>(.*?)<\/tbody>/s', $kosong, $isi);

        $this->assertStringContainsString('—', $isi[1],
            'Tarif tanpa tanggal berlaku mengosongkan selnya, bukan menandainya.');
    }

    /** @test */
    public function tarif_yang_baru_dijadwalkan_tidak_ikut_ke_daftar_yang_dibagikan(): void
    {
        /*
         * INI yang sebenarnya menjaga kebocoran rencana kenaikan harga,
         * sekarang setelah tanggalnya tercetak.
         *
         * Diuji dari SUMBER barisnya, bukan dari hasil render: yang
         * menentukan tarif mana yang sampai ke kertas adalah kueri ini, dan
         * kalau suatu saat ia ikut memulangkan yang TERJADWAL, tanggal
         * kenaikan harga yang belum diumumkan langsung tercetak di kertas
         * yang dibagikan ke luar.
         */
        ClinikScopusBiayaPersesi::create([
            'layanan' => 'scopus_camp',
            'varian' => 'jawa',
            'biaya_persesi' => '9900000',
            'berlaku_mulai' => now()->addMonths(2)->toDateString(),
            'status' => ClinikScopusBiayaPersesi::TERJADWAL,
        ]);

        $berlaku = ClinikScopusBiayaPersesi::semuaYangBerlaku();

        foreach ($berlaku as $kunci => $tarif) {
            $this->assertSame(ClinikScopusBiayaPersesi::AKTIF, $tarif->status,
                'Tarif "' . $kunci . '" berstatus ' . $tarif->status . ' tetapi ikut '
                . 'ke daftar yang dibagikan; tanggal berlakunya akan tercetak.');

            $this->assertNotSame('9900000', (string) $tarif->biaya_persesi,
                'Tarif yang baru dijadwalkan ikut ke daftar yang dibagikan ke luar.');
        }
    }
}
