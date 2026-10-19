<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Blog: /blog dan halaman kategorinya.
 *
 * Dirombak 19 Okt 2026 mengikuti rupa beranda. Yang dijaga di sini bukan
 * seleranya — rupa boleh berubah kapan saja — melainkan empat hal yang
 * kalau rusak tidak mengeluarkan satu pun galat:
 *
 *   1. tulisan yang BELUM terbit ikut terkirim ke peramban;
 *   2. tulisan yang sudah terbit TIDAK muncul di daftarnya;
 *   3. keripik kategori mengantar ke halaman yang kosong;
 *   4. halaman kategori berganti rupa dari /blog, sehingga perpindahannya
 *      terbaca seperti keluar dari situsnya.
 */
class BlogTest extends TestCase
{
    use DatabaseTransactions;

    private const GAYA = 'resources/views/public/article/partials/gaya.blade.php';

    /** Satu tulisan buatan uji; judulnya acak supaya tidak bentrok data nyata. */
    private function tulisan(array $ganti = []): object
    {
        $kategori = DB::table('categories_artikel')->value('id');
        $penulis = DB::table('users')->value('id');

        $this->assertNotNull($kategori, 'Tidak ada kategori artikel untuk diuji.');

        $data = array_merge([
            'user_id' => $penulis,
            'categories_artikel_id' => $kategori,
            'token' => Str::random(16),
            'judul' => 'UJI Blog ' . Str::random(8),
            'kata_kunci' => 'uji',
            'gambar_depan' => 'tidak-ada.png',
            'gambar_cover' => 'tidak-ada.png',
            'isi' => '<p>' . str_repeat('kata ', 60) . '</p>',
            'dilihat' => 0,
            'status' => 'publish',
            'created_at' => now(),
            'updated_at' => now(),
        ], $ganti);

        $id = DB::table('artikel')->insertGetId($data);

        return (object) ($data + ['id' => $id]);
    }

    #[Test]
    public function blognya_terbuka(): void
    {
        $this->get('/blog')->assertOk();
    }

    #[Test]
    public function tulisan_yang_belum_terbit_tidak_pernah_sampai_ke_peramban(): void
    {
        /*
         * Kuerinya dulu mengambil SELURUH baris artikel apa pun statusnya;
         * yang menyaring 'publish' cuma tampilannya, di dalam @php per
         * kategori.
         *
         * Artinya draf yang belum selesai tetap terkirim utuh ke peramban —
         * tersembunyi dari mata, tetapi ada di sumber halaman BESERTA
         * ISINYA. Siapa pun yang menekan "lihat sumber" membacanya, dan
         * penulisnya tidak akan pernah tahu.
         */
        $draf = $this->tulisan(['status' => 'draft', 'judul' => 'RAHASIA Draf ' . Str::random(6)]);

        $isi = (string) $this->get('/blog')->assertOk()->getContent();

        $this->assertStringNotContainsString($draf->judul, $isi,
            'Tulisan berstatus draft ikut terkirim ke peramban. Isinya terbaca '
            . 'lewat "lihat sumber" meski tidak tergambar di layar.');
    }

    #[Test]
    public function semua_tulisan_terbit_muncul_di_daftarnya(): void
    {
        /*
         * Tampilannya dulu mengelompokkan per kategori lalu take(3). Kategori
         * Journal punya lima tulisan, jadi DUA di antaranya tidak pernah
         * muncul di /blog sama sekali — terukur 10 kartu untuk 12 tulisan
         * terbit.
         *
         * Hilangnya tidak bergejala: halamannya tetap penuh dan rapi, dan
         * yang menulisnya mengira tulisannya sudah tayang.
         */
        $baru = collect(range(1, 4))->map(fn () => $this->tulisan());

        $isi = (string) $this->get('/blog')->assertOk()->getContent();

        $hilang = $baru->pluck('judul')->reject(fn ($j) => str_contains($isi, $j))->values();

        $this->assertSame([], $hilang->all(),
            "Tulisan terbit ini tidak muncul di /blog:\n- " . $hilang->implode("\n- "));
    }

    #[Test]
    public function keripik_kategori_tidak_pernah_mengantar_ke_halaman_kosong(): void
    {
        /*
         * Kategori boleh dibuat admin sebelum tulisannya ada. Menawarkannya
         * di /blog berarti mengantar orang ke daftar kosong — dan halaman
         * kosong terbaca sebagai situs yang rusak, bukan sebagai kategori
         * yang memang belum diisi.
         */
        $kosong = DB::table('categories_artikel')->insertGetId([
            'user_id' => DB::table('users')->value('id'),
            'token' => Str::random(16),
            'kategori' => 'UJI Kategori Kosong ' . Str::random(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $isi = (string) $this->get('/blog')->assertOk()->getContent();

        $this->assertStringNotContainsString(
            route('blog.topic.kategori', ['categories_artikel_id' => $kosong]), $isi,
            'Keripik kategori yang belum punya tulisan terbit tetap ditawarkan di '
            . '/blog. Yang menekannya mendarat di halaman tanpa isi.');
    }

    #[Test]
    public function halaman_kategori_memakai_rupa_yang_sama_dengan_blog(): void
    {
        /*
         * Keripik di /blog mengantar ke sini, jadi halaman ini yang pertama
         * dibuka orang sesudah melihat rupa barunya. Dibiarkan memakai
         * gradien merah lama, perpindahannya terbaca seperti keluar dari
         * situsnya.
         *
         * Dijaga dari KEDUA halaman memuat partial gaya yang sama, bukan dari
         * warna tertentu: warnanya boleh berganti, yang tidak boleh cuma
         * salah satunya yang berganti.
         */
        $kategori = DB::table('artikel')
            ->where('status', 'publish')
            ->whereNotNull('categories_artikel_id')
            ->value('categories_artikel_id');

        $this->assertNotNull($kategori, 'Tidak ada kategori berisi untuk diuji.');

        $blog = (string) $this->get('/blog')->assertOk()->getContent();
        $halamanKategori = (string) $this->get(
            route('blog.topic.kategori', ['categories_artikel_id' => $kategori])
        )->assertOk()->getContent();

        foreach ([['blog' => $blog, 'nama' => '/blog'], ['blog' => $halamanKategori, 'nama' => 'halaman kategori']] as $h) {
            $this->assertStringContainsString('class="blg"', $h['blog'],
                $h['nama'] . ' tidak lagi memakai pembungkus .blg milik rupa blog.');

            $this->assertStringContainsString('Fraunces', $h['blog'],
                $h['nama'] . ' tidak lagi memuat huruf judulnya; ia akan memakai huruf '
                . 'bawaan templat sementara halaman blog yang satunya memakai Fraunces.');

            $this->assertStringContainsString('--kertas: #fdf5e8', $h['blog'],
                $h['nama'] . ' kehilangan palet blognya.');
        }
    }

    #[Test]
    public function layar_pertama_blog_tidak_tertutup_kepala_situs(): void
    {
        /*
         * Kepala situs berposisi fixed setinggi 82px dan TIDAK memesan ruang
         * untuk dirinya sendiri — beranda mengatasinya dengan padding-top
         * 124px di hero-nya.
         *
         * Blog sempat memakai 70px, dan terukur dua belas piksel teratas
         * stikernya tertutup: yang terlihat cuma potongan bawah lencana yang
         * seperti tergunting salah. Tidak ada galat, dan di layar lebar
         * sekilas tampak disengaja.
         */
        $sumber = (string) preg_replace(
            ['#/\*.*?\*/#s', '#\{\{--.*?--\}\}#s'], '',
            (string) file_get_contents(base_path(self::GAYA))
        );

        $cocok = preg_match('/\.blg-kepala \{[^}]*padding:\s*(\d+)px/s', $sumber, $tangkap);

        $this->assertSame(1, $cocok, 'Aturan padding .blg-kepala tidak ditemukan.');

        $this->assertGreaterThanOrEqual(100, (int) $tangkap[1],
            'Jarak atas layar pertama blog tinggal ' . $tangkap[1] . 'px. Kepala situs '
            . 'setinggi 82px mengambang di atasnya dan akan menutupi isi teratasnya.');
    }
}
