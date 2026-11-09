<?php

namespace Tests\Feature\Blog;

use App\Artikel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Sampul artikel yang barisnya menunjuk berkas hilang.
 *
 * Terukur di produksi 9 Nov 2026: 17 dari 24 kolom gambar menunjuk berkas
 * yang sudah tidak ada, dan delapan dari dua belas tulisan kehilangan
 * sampulnya di /blog.
 *
 * Sebabnya `artikel:kecilkan-sampul` mengerjakan tiga hal sekaligus —
 * mengubah ke WebP, memperbarui baris, menghapus berkas lama — sementara
 * hasil .webp-nya IKUT TERCATAT DI GIT dan berkas aslinya ikut terhapus dari
 * repositori pada commit yang sama. Saat commit itu sampai ke peladen,
 * `git pull` menulis .webp-nya dan menghapus aslinya, sedangkan basis data
 * produksi tidak pernah ikut diperbarui.
 *
 * KEGAGALANNYA DIAM. Halaman blognya tetap 200; yang 404 cuma gambarnya,
 * jadi tidak ada satu pun galat yang terbit di log. Satu-satunya yang
 * melihatnya pengunjung. Itu sebabnya dijaga uji, bukan diandalkan terlihat.
 */
class SampulHilangDisambungkanTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<string> berkas yang dibuat uji ini, dibuang lagi di akhir */
    private array $sampah = [];

    protected function tearDown(): void
    {
        /*
         * HANYA berkas yang dibuat uji ini. Menyapu folder public/images
         * akan membuang 694 sampul sungguhan — dan uji di proyek ini sudah
         * pernah menghapus bukti pembayaran orang dengan sapuan tearDown.
         */
        foreach ($this->sampah as $jalur) {
            if (is_file($jalur)) {
                @unlink($jalur);
            }
        }

        $this->sampah = [];

        parent::tearDown();
    }

    private function berkas(string $nama): string
    {
        $jalur = public_path('images/' . $nama);

        if (! is_dir(dirname($jalur))) {
            mkdir(dirname($jalur), 0775, true);
        }

        // Satu piksel PNG — isinya tidak penting, keberadaannya yang diuji.
        file_put_contents($jalur, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $this->sampah[] = $jalur;

        return $nama;
    }

    private function artikel(string $depan, string $cover): Artikel
    {
        /*
         * Kunci asing DIAMBIL dari basis data, bukan ditulis 1.
         *
         * Tabel artikel punya foreign key ke users dan categories_artikel,
         * dan id 1 tidak ada di keduanya di basis data ini — ujinya gagal
         * dengan galat SQL, bukan dengan kegagalan yang ia maksud.
         */
        return Artikel::create([
            'user_id' => \DB::table('users')->min('id'),
            'categories_artikel_id' => \DB::table('categories_artikel')->min('id'),
            'token' => Str::random(30),
            'judul' => 'Uji Sampul ' . Str::upper(Str::random(5)),
            'kata_kunci' => 'uji',
            'gambar_depan' => $depan,
            'gambar_cover' => $cover,
            'isi' => '<p>uji</p>',
            'dilihat' => 0,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function baris_yang_berkasnya_hilang_ditunjuk_ulang_ke_webp_yang_ada(): void
    {
        $dasar = 'ujisampul' . random_int(100000, 999999) . '_GambarDepan';

        // Keadaan produksi dibuat ulang: .webp ADA di cakram, barisnya masih
        // menunjuk .png yang sudah tidak ada.
        $this->berkas($dasar . '.webp');
        $artikel = $this->artikel($dasar . '.png', $dasar . '.png');

        $this->artisan('artikel:perbaiki-sampul-hilang')->assertSuccessful();

        $artikel->refresh();

        $this->assertSame($dasar . '.webp', $artikel->gambar_depan);
        $this->assertSame($dasar . '.webp', $artikel->gambar_cover,
            'Kedua kolom gambar harus ikut disambungkan, bukan hanya yang depan.');
    }

    #[Test]
    public function baris_yang_berkasnya_masih_ada_tidak_disentuh(): void
    {
        $dasar = 'ujiutuh' . random_int(100000, 999999) . '_GambarDepan';

        // Keduanya ada. Perintah ini tidak boleh "merapikan" apa pun yang
        // sudah benar — mengganti .jpg yang hidup jadi .webp adalah mengubah
        // gambar yang sedang tampil, dan itu bukan tugasnya.
        $this->berkas($dasar . '.jpg');
        $this->berkas($dasar . '.webp');
        $artikel = $this->artikel($dasar . '.jpg', $dasar . '.jpg');

        $this->artisan('artikel:perbaiki-sampul-hilang')->assertSuccessful();

        $artikel->refresh();

        $this->assertSame($dasar . '.jpg', $artikel->gambar_depan,
            'Sampul yang berkasnya masih ada tidak boleh ditukar.');
    }

    #[Test]
    public function yang_tidak_punya_pengganti_dibiarkan_dan_dilaporkan(): void
    {
        $dasar = 'ujiyatim' . random_int(100000, 999999) . '_GambarDepan';

        // Tidak ada berkas apa pun bernama dasar ini.
        $artikel = $this->artikel($dasar . '.png', $dasar . '.png');

        $this->artisan('artikel:perbaiki-sampul-hilang')
            ->expectsOutputToContain('TANPA PENGGANTI')
            ->assertSuccessful();

        $artikel->refresh();

        /*
         * Dibiarkan apa adanya, BUKAN dikosongkan. Nama berkasnya satu-
         * satunya petunjuk yang tersisa untuk mencarinya di cadangan;
         * mengosongkan kolomnya membuang petunjuk itu untuk selamanya demi
         * layar yang kelihatan lebih rapi.
         */
        $this->assertSame($dasar . '.png', $artikel->gambar_depan);
    }

    #[Test]
    public function kering_melaporkan_tanpa_mengubah_apa_pun(): void
    {
        $dasar = 'ujikering' . random_int(100000, 999999) . '_GambarDepan';

        $this->berkas($dasar . '.webp');
        $artikel = $this->artikel($dasar . '.png', $dasar . '.png');

        $this->artisan('artikel:perbaiki-sampul-hilang', ['--kering' => true])
            ->assertSuccessful();

        $artikel->refresh();

        $this->assertSame($dasar . '.png', $artikel->gambar_depan,
            '--kering harus benar-benar kering: ia dipakai untuk melihat apa '
            . 'yang AKAN terjadi di produksi sebelum terjadi.');
    }

    #[Test]
    public function aman_dijalankan_dua_kali(): void
    {
        $dasar = 'ujiulang' . random_int(100000, 999999) . '_GambarDepan';

        $this->berkas($dasar . '.webp');
        $artikel = $this->artikel($dasar . '.png', $dasar . '.png');

        $this->artisan('artikel:perbaiki-sampul-hilang')->assertSuccessful();
        $this->artisan('artikel:perbaiki-sampul-hilang')->assertSuccessful();

        $artikel->refresh();

        $this->assertSame($dasar . '.webp', $artikel->gambar_depan,
            'Jalan kedua tidak boleh mengubah apa pun: barisnya sudah '
            . 'menunjuk berkas yang ada.');
    }

    #[Test]
    public function waktu_sunting_tulisan_tidak_ikut_bergerak(): void
    {
        $dasar = 'ujiwaktu' . random_int(100000, 999999) . '_GambarDepan';

        $this->berkas($dasar . '.webp');
        $artikel = $this->artikel($dasar . '.png', $dasar . '.png');

        $artikel->forceFill(['updated_at' => now()->subMonths(6)])->saveQuietly();
        $sebelum = $artikel->fresh()->updated_at;

        $this->artisan('artikel:perbaiki-sampul-hilang')->assertSuccessful();

        /*
         * Ini pembetulan tata data, bukan penyuntingan tulisan. Kalau
         * updated_at ikut bergerak, dua belas tulisan lama akan terbaca
         * "baru diperbarui hari ini" di mana pun kolom itu dipakai.
         */
        $this->assertEquals(
            $sebelum->format('Y-m-d H:i:s'),
            $artikel->fresh()->updated_at->format('Y-m-d H:i:s'),
            'saveQuietly() harus menahan updated_at.'
        );
    }
}
