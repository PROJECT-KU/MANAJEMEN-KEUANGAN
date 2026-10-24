<?php

namespace Tests\Feature\PendaftaranLayanan;

use App\Support\PendaftaranSemuaLayanan as Pendaftaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Nomor pendaftaran yang pernah DIHAPUS tidak pernah dipakai lagi.
 *
 * Nomor ini dipakai sebagai invoice_number di gerbang pembayaran. Dihitung
 * dari tabel hidup saja, menghapus satu pendaftaran MEMBEBASKAN nomornya —
 * dan pendaftaran berikutnya memakai nomor yang sama. Akibatnya berantai:
 *
 *   1. gerbang menolak tagihannya dengan "INVOICE ALREADY USED", jadi
 *      pendaftarnya TIDAK BISA MEMBAYAR SAMA SEKALI;
 *   2. kalau nomornya pernah dibayar, gerbang menjawab SUCCESS saat
 *      statusnya ditanyakan — untuk pembayaran milik pendaftaran yang sudah
 *      dihapus;
 *   3. pendaftaran baru itu tertandai LUNAS tanpa ada uang masuk.
 *
 * Webinar Eksklusif sudah memperbaikinya di pembuat nomornya sendiri 17 Okt
 * 2026, sesudah WE-20261017-0015 dihapus pukul 21:49:10 dan dipakai ulang 47
 * detik kemudian. Pembuat BERSAMA — yang dipakai empat layanan lain — tidak
 * ikut diperbaiki.
 *
 * Akibat nomor 1 langsung terjadi begitu Scopus Camp memakai DOKU: terukur 24
 * Okt 2026, CAMP-20261024-0001 ditolak DUA KALI dengan "INVOICE ALREADY
 * USED", dan pendaftarnya dialihkan ke halaman status tanpa satu pun cara
 * membayar.
 */
class NomorTidakDipakaiUlangTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Keempat layanan yang memakai pembuat nomor BERSAMA.
     *
     * Webinar Eksklusif tidak ikut: ia punya pembuatnya sendiri di modelnya,
     * dan sudah dijaga NomorTidakDiwariskanTest.
     *
     * @return array<string, array{string, string}>
     */
    public static function layanan(): array
    {
        return [
            'Scopus Camp' => ['scopus_camp', 'CAMP'],
            'Analisis Bibliometrik' => ['bibliometrik', 'BIB'],
            'Scopus Kafe' => ['scopus_kafe', 'KAFE'],
            // KLINIK, bukan CLINIK: awalannya memang dieja begitu di katalog,
            // dan awalan yang salah membuat uji ini memeriksa nomor layanan
            // yang bukan miliknya — lalu hijau tanpa menjaga apa pun.
            'Clinik Scopus' => ['clinik_scopus', 'KLINIK'],
        ];
    }

    #[Test]
    #[DataProvider('layanan')]
    public function nomor_yang_pernah_dihapus_tidak_diberikan_lagi(string $layanan, string $awalan): void
    {
        /*
         * 8000, bukan 9999.
         *
         * Nomornya dibantali empat angka dan dibaca dari empat angka
         * terakhir, jadi 9999 + 1 jadi "10000" dan substr(-4) membacanya
         * sebagai 0000 — ujinya merah karena batas pembacaan nomor, bukan
         * karena yang hendak dijaga. (Batas itu sendiri nyata, tetapi ia
         * baru tercapai pada pendaftaran ke-10.000 dalam SATU hari.)
         *
         * 8000 tetap jauh di depan nomor mana pun yang mungkin sudah ada
         * hari ini, jadi ia pasti jadi yang terbesar.
         */
        $nomorDihapus = $awalan . '-' . now()->format('Ymd') . '-8000';

        DB::table('pendaftaran_dihapus')->insert([
            'id' => (string) Str::uuid(),
            'layanan' => $layanan,
            'pendaftaran_id' => (string) Str::uuid(),
            'nomor' => $nomorDihapus,
            'nama' => 'Uji Nomor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $baru = Pendaftaran::nomorBaru($layanan);

        $this->assertNotSame($nomorDihapus, $baru,
            'Nomor ' . $nomorDihapus . ' pernah dihapus tetapi diberikan lagi. '
            . 'Gerbang pembayaran akan menolaknya dengan "INVOICE ALREADY USED", '
            . 'dan pendaftarnya tidak bisa membayar sama sekali.');

        $this->assertGreaterThan(8000, (int) substr($baru, -4),
            'Nomor barunya tidak melompati nomor terhapus yang lebih besar, '
            . 'jadi nomor-nomor sesudahnya akan bertabrakan satu per satu.');
    }

    /*
     * CATATAN JUJUR tentang apa yang BELUM dijaga di berkas ini.
     *
     * nomorBaru() punya dua lapis: hitungan `max()` atas nomor terbesar —
     * hidup maupun terhapus — dan pemeriksaan per-nomor di dalam perulangan
     * sesudahnya. Uji di sini hanya membuktikan LAPIS PERTAMA: mencabut
     * hitungan max-nya membuat uji ini merah, tetapi mencabut pemeriksaan
     * per-nomornya TIDAK.
     *
     * Sudah dibuktikan dengan memunculkan kembali keduanya satu per satu.
     * Sebabnya memang begitu: begitu max-nya benar, perulangannya mulai di
     * atas nomor terbesar dan tidak pernah melewati nomor yang sudah
     * terpakai. Lapis kedua baru berguna saat ada dua proses mendaftar
     * bersamaan — keadaan yang tidak bisa dibuat ulang dari satu uji — jadi
     * ia dipertahankan tanpa penjaga, dan itu disebut di sini supaya tidak
     * dikira sudah terjaga.
     */

    #[Test]
    public function nomor_yang_masih_hidup_juga_tidak_diberikan_lagi(): void
    {
        /*
         * Sisi sebaliknya, dan ia yang memastikan perbaikan di atas tidak
         * dilakukan dengan mengabaikan tabel hidupnya.
         */
        $a = \App\CategoriesScopusCamp::create([
            'layanan' => 'scopus_camp',
            'token' => Str::random(30),
            'nama' => 'UJI Camp Nomor ' . Str::random(5),
            'nama_ke' => (string) random_int(8000, 8999),
            'mulai' => now()->addDays(9)->toDateString(),
            'total_kuota' => 5, 'sisa_kuota' => 5,
            'biaya' => 100000, 'status' => 'active',
        ]);

        $nomor = Pendaftaran::nomorBaru('scopus_camp');

        \App\PendaftaranScopusCamp::create([
            'token' => Str::random(30),
            'id_transaksi' => $nomor,
            'kategori_id' => $a->getKey(),
            'nama' => 'Uji Hidup',
            'email' => 'hidup' . Str::random(5) . '@contoh.test',
            'telp' => '6281200000009',
            'jumlah_pendaftar' => 1,
            'total_pembayaran' => '1',
            'status' => 'diproses',
        ]);

        $this->assertNotSame($nomor, Pendaftaran::nomorBaru('scopus_camp'),
            'Nomor yang barisnya masih ada diberikan dua kali.');
    }
}
