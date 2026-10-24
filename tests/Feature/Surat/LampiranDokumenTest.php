<?php

namespace Tests\Feature\Surat;

use App\Support\LampiranDokumen;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Faktur DAN kwitansi untuk semua layanan, bukan Webinar Eksklusif saja.
 *
 * Diminta pemiliknya 24 Okt 2026. Sebelumnya hanya Webinar yang punya
 * keduanya; yang lain timpang:
 *
 *   Scopus Camp    faktur saja — pembayarannya diterima tanpa satu pun
 *                  kwitansi, padahal surat penerimaannya menyebut nominal
 *   Bibliometrik   faktur saja, sama
 *   Scopus Kafe    TIDAK ADA LAMPIRAN SAMA SEKALI; yang ikut terkirim justru
 *                  logo 121.370 bita yang tidak dirujuk isi suratnya
 *
 * Akibatnya yang harus menyerahkan bukti ke bendahara kantornya tidak punya
 * apa pun untuk diserahkan — dan itu justru pemesan rombongan, yang
 * nominalnya paling besar.
 */
class LampiranDokumenTest extends TestCase
{
    use DatabaseTransactions;

    private function campPendaftaran(): array
    {
        $a = \App\CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Lampiran ' . Str::random(5),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(12)->toDateString(),
            'selesai' => now()->addDays(14)->toDateString(),
            'lokasi' => 'Yogyakarta',
            'total_kuota' => 10, 'sisa_kuota' => 10,
            'biaya' => 5500000, 'status' => 'active',
        ]);

        $p = \App\PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => 'CAMP-' . now()->format('Ymd') . '-7001',
            'kategori_id' => $a->getKey(),
            'nama' => 'Pendaftar Lampiran',
            'email' => 'lampiran' . Str::random(5) . '@contoh.test',
            'telp' => '6281234500011',
            'jumlah_pendaftar' => 2,
            'kode_unik' => '777',
            'total_pembayaran' => (string) (2 * 5500000 + 777),
            'status' => 'diproses',
        ]);

        return [$p, $a];
    }

    #[Test]
    public function faktur_dan_kwitansi_punya_nama_berkas_yang_berbeda(): void
    {
        /*
         * Terukur 24 Okt 2026 saat perakit ini pertama dicoba: nomor lama
         * Scopus Kafe tidak berawalan apa pun — lima aksara acak seperti
         * "HUQEZ" — jadi penukaran awalannya tidak menemukan apa-apa dan
         * KEDUA dokumen bernama HUQEZ.pdf. Yang mengunduh keduanya menimpa
         * yang pertama, lalu memegang satu berkas yang ia kira dua.
         */
        [$p, $a] = $this->campPendaftaran();

        $faktur = LampiranDokumen::rakit('scopus_camp', 'faktur', $p, $a);
        $kwitansi = LampiranDokumen::rakit('scopus_camp', 'kwitansi', $p, $a);

        $this->assertNotSame($faktur['nama'], $kwitansi['nama'],
            'Faktur dan kwitansinya bernama berkas sama; yang mengunduh keduanya '
            . 'hanya memegang satu.');

        $this->assertStringStartsWith('INV-', $faktur['nama']);
        $this->assertStringStartsWith('KW-', $kwitansi['nama']);
    }

    #[Test]
    public function awalan_ditambahkan_saat_nomornya_tidak_punya(): void
    {
        // Nomor gaya LAMA Scopus Kafe: lima aksara acak, tanpa awalan.
        $id = (string) Str::uuid();

        DB::table('pendaftaran_scopus_kafe')->insert([
            'id' => $id,
            'id_pemesanan' => 'HUQEZ',
            'nama' => 'Pemesan Lama',
            'email' => 'lama' . Str::random(5) . '@contoh.test',
            'telp' => '6281234500012',
            'sesi' => 'Sesi Uji',
            'biaya' => '150000',
            'total_keseluruhan_pembayaran' => '150000',
            'status' => 'menunggu verifikasi',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $p = \App\PendaftaranScopusKafe::find($id);

        $this->assertSame('INV-HUQEZ.pdf',
            LampiranDokumen::rakit('scopus_kafe', 'faktur', $p)['nama']);

        $this->assertSame('KW-HUQEZ.pdf',
            LampiranDokumen::rakit('scopus_kafe', 'kwitansi', $p)['nama']);
    }

    #[Test]
    public function kwitansi_menyatakan_lunas_dan_faktur_tidak(): void
    {
        /*
         * Keduanya dokumen yang SAMA dengan dua keadaan. Kalau cetakannya
         * tidak membedakannya, kwitansi akan menyebut "Belum Dibayar" untuk
         * uang yang sudah masuk — dan yang memegangnya tidak punya bukti
         * apa pun bahwa ia sudah membayar.
         *
         * Diperiksa dari HTML-nya, bukan dari PDF-nya: aliran PDF terkompresi,
         * jadi mencari kata di dalamnya bisa memberi jawaban "tidak ada" untuk
         * kata yang sebenarnya tercetak.
         */
        [$p, $a] = $this->campPendaftaran();

        $faktur = view('public.scopus_camp.dokumen_pdf', [
            'jenis' => 'faktur', 'pendaftaran' => $p, 'angkatan' => $a, 'terbilang' => 'uji',
        ])->render();

        $kwitansi = view('public.scopus_camp.dokumen_pdf', [
            'jenis' => 'kwitansi', 'pendaftaran' => $p, 'angkatan' => $a, 'terbilang' => 'uji',
        ])->render();

        $this->assertStringContainsString('BELUM DIBAYAR', $faktur);
        $this->assertStringContainsString('FAKTUR', $faktur);

        $this->assertStringContainsString('LUNAS', $kwitansi);
        $this->assertStringContainsString('KWITANSI', $kwitansi);
        $this->assertStringNotContainsString('BELUM DIBAYAR', $kwitansi,
            'Kwitansinya masih menyatakan belum dibayar.');
    }

    #[Test]
    public function rincian_kwitansi_menjumlah_tepat_ke_totalnya(): void
    {
        /*
         * Rinciannya yang tidak menjumlah ke totalnya adalah dokumen keuangan
         * yang menyebut dua jumlah sekaligus — dan yang mencocokkannya ke
         * bendahara harus menjelaskan selisih yang sebenarnya tidak ada.
         */
        [$p, $a] = $this->campPendaftaran();

        $html = view('public.scopus_camp.dokumen_pdf', [
            'jenis' => 'kwitansi', 'pendaftaran' => $p, 'angkatan' => $a, 'terbilang' => 'uji',
        ])->render();

        preg_match_all('/<td class="k-jumlah">([\d.\-]+)<\/td>/', $html, $m);

        $this->assertNotEmpty($m[1], 'Tabel rinciannya tidak punya satu pun baris.');

        $jumlah = array_sum(array_map(
            fn ($n) => (int) str_replace(['.', '-'], ['', '-'], $n),
            $m[1]
        ));

        $this->assertSame((int) $p->total_pembayaran, $jumlah,
            'Rincian dokumennya tidak berjumlah sama dengan total yang tersimpan.');
    }

    /** @return array<string, array{string}> */
    public static function layananBercetakan(): array
    {
        return [
            'Webinar Eksklusif' => ['webinar_eksklusif'],
            'Scopus Camp' => ['scopus_camp'],
            'Analisis Bibliometrik' => ['bibliometrik'],
            'Scopus Kafe' => ['scopus_kafe'],
        ];
    }

    #[Test]
    #[DataProvider('layananBercetakan')]
    public function setiap_layanan_punya_cetakannya(string $layanan): void
    {
        $this->assertTrue(LampiranDokumen::punyaCetakan($layanan),
            'Layanan ' . $layanan . ' tidak punya cetakan faktur/kwitansi, jadi '
            . 'suratnya terkirim tanpa lampiran apa pun.');
    }

    #[Test]
    public function layanan_yang_tidak_dikenali_ditolak_terang_terangan(): void
    {
        /*
         * Dilempar, bukan memulangkan berkas kosong: lampiran 0 bita yang
         * terkirim diam-diam jauh lebih sulit ditemukan daripada galat.
         */
        $this->expectException(\InvalidArgumentException::class);

        LampiranDokumen::rakit('layanan_karangan', 'faktur', (object) []);
    }
}
