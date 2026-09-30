<?php

namespace Tests\Feature\Kategori;

use App\CategoriesAnalisisBibliometrik;
use App\CategoriesScopusCamp;
use App\ClinikScopusBiayaPersesi;
use App\KategoriLayanan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kategori (angkatan) seluruh layanan, sesudah dua tabelnya disatukan.
 *
 * Bahaya utama penyatuan ini bukan kehilangan data, melainkan KEBOCORAN antar
 * layanan: kueri yang dulu otomatis khusus satu layanan — karena tabelnya
 * memang cuma berisi itu — kini mengembalikan semuanya kalau lupa disaring.
 */
class KategoriLayananTest extends TestCase
{
    use DatabaseTransactions;

    private function kategori(string $layanan, array $lain = []): KategoriLayanan
    {
        /*
         * Varian diambil dari katalog, bukan ditulis tetap di sini: layanan
         * bervarian yang dibuat tanpa varian tidak akan pernah menemukan tarif
         * induknya, dan ujinya gagal dengan sebab yang jauh dari penyebabnya.
         */
        $varian = ClinikScopusBiayaPersesi::LAYANAN[$layanan]['varian'] ?? [];

        return KategoriLayanan::create(array_merge([
            'layanan' => $layanan,
            'varian' => $varian === [] ? null : array_key_first($varian),
            'token' => 'uji' . uniqid(),
            'nama' => 'Angkatan Uji ' . $layanan,
            'biaya' => '1000000',
            'status' => 'draft',
        ], $lain));
    }

    // ------------------------------------------------------- pemisahan layanan

    #[Test]
    public function model_khusus_layanan_hanya_melihat_layanannya_sendiri(): void
    {
        $camp = $this->kategori('scopus_camp');
        $biblio = $this->kategori('bibliometrik');

        $this->assertNotNull(CategoriesScopusCamp::find($camp->getKey()));
        $this->assertNull(
            CategoriesScopusCamp::find($biblio->getKey()),
            'Angkatan Bibliometrik tidak boleh terlihat lewat model Scopus Camp.'
        );

        $this->assertNotNull(CategoriesAnalisisBibliometrik::find($biblio->getKey()));
        $this->assertNull(CategoriesAnalisisBibliometrik::find($camp->getKey()));
    }

    #[Test]
    public function membuat_lewat_model_khusus_mengisi_layanannya_sendiri(): void
    {
        // Tanpa ini, angkatan baru tersimpan tanpa layanan dan hilang dari
        // layar mana pun — termasuk layar tempat ia baru saja dibuat.
        $camp = CategoriesScopusCamp::create([
            'token' => 'uji' . uniqid(),
            'nama' => 'Angkatan tanpa layanan disebut',
            'status' => 'draft',
        ]);

        $this->assertSame('scopus_camp', $camp->refresh()->layanan);
    }

    #[Test]
    public function menghitung_lewat_model_khusus_tidak_mencampur(): void
    {
        $sebelumCamp = CategoriesScopusCamp::count();
        $sebelumBiblio = CategoriesAnalisisBibliometrik::count();

        $this->kategori('bibliometrik');

        $this->assertSame($sebelumCamp, CategoriesScopusCamp::count());
        $this->assertSame($sebelumBiblio + 1, CategoriesAnalisisBibliometrik::count());
    }

    /**
     * Penjaga sumber kode.
     *
     * Dulu `DB::table('scopus_camp_kategori')` otomatis khusus Scopus Camp
     * karena tabelnya memang cuma berisi itu. Sesudah disatukan, kueri yang
     * sama mengembalikan 58 baris campuran — dan layar Scopus Camp memajang
     * angkatan Bibliometrik tanpa galat apa pun, jadi tidak ada yang
     * memberitahu bahwa itu salah.
     */
    #[Test]
    public function setiap_kueri_tabel_kategori_menyaring_layanannya(): void
    {
        $lengah = [];

        foreach ($this->berkasPhp(base_path('app')) as $berkas) {
            foreach (file($berkas) as $no => $baris) {
                if (! str_contains($baris, "DB::table('kategori_layanan')")) {
                    continue;
                }

                if (str_contains($baris, "where('layanan'")) {
                    continue;
                }

                $lengah[] = str_replace(base_path() . '/', '', $berkas) . ':' . ($no + 1);
            }
        }

        $this->assertSame([], $lengah,
            "Kueri tabel kategori tanpa saringan layanan:\n" . implode("\n", $lengah));
    }

    /** @return array<int, string> */
    private function berkasPhp(string $akar): array
    {
        $hasil = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar));

        foreach ($iterator as $berkas) {
            if ($berkas->isFile() && $berkas->getExtension() === 'php') {
                $hasil[] = $berkas->getPathname();
            }
        }

        return $hasil;
    }

    // --------------------------------------------------------- tautan tarif

    #[Test]
    public function kategori_menemukan_tarif_induknya_lewat_kunci_layanan_yang_sama(): void
    {
        /*
         * Kolom `layanan` memakai kunci yang sama dengan katalog tarif, jadi
         * angkatan bisa menemukan harga dan fasilitas patokannya tanpa peta
         * perantara. Peta perantara itulah yang selama ini tidak ada, dan
         * karenanya harga diketik ulang tiap angkatan.
         */
        $camp = $this->kategori('scopus_camp');

        $tarif = $camp->tarif();

        $this->assertNotNull($tarif, 'Angkatan Scopus Camp harus menemukan tarif induknya.');
        $this->assertSame('scopus_camp', $tarif->layanan);
        $this->assertNotSame([], $camp->daftar_fasilitas);
    }

    #[Test]
    public function layanan_tanpa_tarif_tidak_meledak(): void
    {
        // Online Training belum punya tarif; angkatannya tetap harus bisa
        // dibuka, cuma tanpa harga patokan.
        DB::table('clinikscopus_biaya_persesi')
            ->where('layanan', 'online_training')
            ->update(['status' => 'non active']);

        $angkatan = $this->kategori('online_training');

        $this->assertNull($angkatan->tarif());
        $this->assertSame([], $angkatan->daftar_fasilitas);
    }

    // ------------------------------------------------------------ keutuhan

    #[Test]
    public function seluruh_pendaftaran_masih_menunjuk_kategori_yang_ada(): void
    {
        /*
         * Penyatuannya memindahkan 10 baris Bibliometrik DENGAN id aslinya,
         * supaya 92 pendaftaran yang menunjuknya tetap cocok. Kalau id-nya
         * dibuat ulang, tautannya putus tanpa suara.
         */
        $adaId = KategoriLayanan::pluck('id');

        foreach (['analisis_bibliometrik', 'scopus_camp_pendaftaran'] as $tabel) {
            $yatim = DB::table($tabel)
                ->whereNotNull('kategori_id')
                ->whereNotIn('kategori_id', $adaId)
                ->count();

            $this->assertSame(0, $yatim, "Ada pendaftaran $tabel yang kategorinya hilang.");
        }
    }
}
